<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\User;
use App\Models\AuditLog;

/**
 * AppController — handles shared PWA routes:
 * /app            → redirect by role after login
 * /app/login
 * /app/register
 * /app/forgot-password
 * /app/offline
 */
class AppController extends Controller
{
    public function entry(): void
    {
        if (!check()) {
            $this->redirect('/app/login');
            return;
        }
        $user = auth();
        match ($user['role'] ?? '') {
            'resident'  => $this->redirect('/app/resident'),
            'responder' => $this->redirect('/app/responder'),
            default     => $this->redirect('/dashboard'),   // admin/personnel → desktop
        };
    }

    public function login(): void
    {
        if (check()) {
            $this->entry();
            return;
        }

        if ($this->isPost()) {
            $this->handleLogin();
            return;
        }

        $this->view('app/login', [
            'pageTitle' => 'Sign In',
            'errors'    => [],
            'loginError'=> '',
        ], 'mobile-auth');
    }

    public function register(): void
    {
        if (check()) {
            $this->entry();
            return;
        }

        if ($this->isPost()) {
            $this->handleRegister();
            return;
        }

        $this->view('app/register', [
            'pageTitle' => 'Create Account',
            'errors'    => [],
        ], 'mobile-auth');
    }

    public function forgotPassword(): void
    {
        if ($this->isPost()) {
            $token = $this->input('csrf_token');
            if (!Csrf::verify($token)) {
                $this->view('app/forgot-password', [
                    'pageTitle' => 'Forgot Password',
                    'errors'    => ['csrf' => 'Invalid session.'],
                    'sent'      => false,
                ], 'mobile-auth');
                return;
            }
            $this->view('app/forgot-password', [
                'pageTitle' => 'Forgot Password',
                'errors'    => [],
                'sent'      => true,
            ], 'mobile-auth');
            return;
        }
        $this->view('app/forgot-password', [
            'pageTitle' => 'Forgot Password',
            'errors'    => [],
            'sent'      => false,
        ], 'mobile-auth');
    }

    public function notifications(): void
    {
        $this->requireAuth();
        $user = auth();

        $notifModel = new \App\Models\Notification($this->db);
        $notifications = $notifModel->forUser((int) $user['id'], 50);

        // Mark all as read
        $notifModel->markAllRead((int) $user['id']);

        $layout = in_array($user['role'], ['resident','responder']) ? 'mobile' : 'mobile-auth';

        $this->view('app/notifications', [
            'pageTitle'     => 'Notifications',
            'notifications' => $notifications,
        ], $layout);
    }

    public function markNotificationsRead(): void
    {
        $this->requireAuth();
        if (!\App\Core\Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/app/notifications');
            return;
        }
        (new \App\Models\Notification($this->db))->markAllRead((int) auth()['id']);
        $this->redirect('/app/notifications');
    }

    public function offline(): void
    {
        http_response_code(200); // Must be 200 so SW can cache it
        $this->view('app/offline', ['pageTitle' => 'Offline'], 'mobile-auth');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect('/app/login');
    }

    // ──────────────────────────────────────────────────────────────────

    private function handleLogin(): void
    {
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            $this->view('app/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Invalid session. Please refresh.',
            ], 'mobile-auth');
            return;
        }

        // Rate limit: 5 per minute per IP
        if ($this->isRateLimited('login_pwa', 5, 60)) {
            $this->view('app/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Too many attempts. Please wait a moment.',
            ], 'mobile-auth');
            return;
        }

        $identifier = trim((string) $this->input('identifier', ''));
        $password   = (string) $this->input('password', '');

        if ($identifier === '' || $password === '') {
            $this->view('app/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Email/phone and password are required.',
            ], 'mobile-auth');
            return;
        }

        $userModel = new User($this->db);
        $user = $userModel->findByEmailOrPhone($identifier);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->view('app/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Invalid credentials.',
            ], 'mobile-auth');
            return;
        }

        if ($user['status'] === 'rejected') {
            $this->view('app/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Your account was rejected. Contact the barangay.',
            ], 'mobile-auth');
            return;
        }

        if ($user['status'] === 'pending') {
            $this->view('app/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Your account is still pending approval by the barangay. Please wait for confirmation.',
            ], 'mobile-auth');
            return;
        }

        if ($user['status'] === 'suspended') {
            $this->view('app/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Your account has been suspended. Contact the barangay for assistance.',
            ], 'mobile-auth');
            return;
        }

        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        Csrf::token();

        $_SESSION['user'] = [
            'id'     => (int) $user['id'],
            'name'   => $user['name'],
            'email'  => $user['email'],
            'phone'  => $user['phone'],
            'role'   => $user['role'],
            'status' => $user['status'],
        ];

        (new AuditLog($this->db))->write(
            (int) $user['id'], 'auth.login_mobile', 'user', (int) $user['id'],
            json_encode(['source' => 'pwa'])
        );

        $this->entry();
    }

    private function handleRegister(): void
    {
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            $this->view('app/register', [
                'pageTitle' => 'Create Account',
                'errors'    => ['csrf' => 'Invalid session.'],
            ], 'mobile-auth');
            return;
        }

        // Rate limit: 3 registrations per hour per IP
        if ($this->isRateLimited('register', 3, 3600)) {
            $this->view('app/register', [
                'pageTitle' => 'Create Account',
                'errors'    => ['name' => 'Too many registrations from your connection. Please try again later.'],
            ], 'mobile-auth');
            return;
        }

        $name     = trim((string) $this->input('name', ''));
        $email    = trim((string) $this->input('email', ''));
        $phone    = trim((string) $this->input('phone', ''));
        $password = (string) $this->input('password', '');
        $confirm  = (string) $this->input('password_confirm', '');
        $barangay = trim((string) $this->input('barangay', ''));

        // Extended fields
        $purok       = trim((string) $this->input('purok', ''));
        $address     = trim((string) $this->input('address', ''));
        $hhCount     = $this->input('household_count') !== '' ? (int) $this->input('household_count') : null;
        $hasSenior   = $this->input('has_senior')   ? 1 : 0;
        $hasChild    = $this->input('has_child')    ? 1 : 0;
        $hasPwd      = $this->input('has_pwd')      ? 1 : 0;
        $hasPregnant = $this->input('has_pregnant') ? 1 : 0;
        $homeLat     = $this->input('home_lat') !== '' ? (float) $this->input('home_lat') : null;
        $homeLng     = $this->input('home_lng') !== '' ? (float) $this->input('home_lng') : null;

        $errors = [];
        if ($name === '')     $errors['name']     = 'Full name is required.';
        if (!preg_match('/^[a-zA-Z\s,.-]{2,150}$/', $name)) $errors['name'] = 'Please enter a valid full name.';

        if ($phone === '')    $errors['phone']     = 'Contact number is required.';
        if (!preg_match('/^(09|\+639)\d{9}$/', $phone)) $errors['phone'] = 'Please enter a valid Philippine mobile number (e.g. 09123456789).';

        if ($purok === '')    $errors['purok']     = 'Purok is required.';
        if ($address === '')  $errors['address']   = 'Address is required.';
        if (strlen($address) < 5) $errors['address'] = 'Please provide a more specific address.';

        if ($email === '')    $errors['email']     = 'Email address is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';

        if ($password === '') {
            $errors['password']  = 'Password is required.';
        } elseif ($passError = $this->validatePassword($password)) {
            $errors['password'] = $passError;
        }

        if ($password !== $confirm)  $errors['password_confirm'] = 'Passwords do not match.';
        if (!$this->input('confirm_terms')) $errors['confirm_terms'] = 'You must confirm the information is true and correct.';

        // Check uniqueness
        if (empty($errors)) {
            $stmt = $this->db->prepare(
                "SELECT id FROM users WHERE phone = :phone OR (email != '' AND email = :email) LIMIT 1"
            );
            $stmt->execute(['phone' => $phone, 'email' => $email ?: '__none__']);
            if ($stmt->fetch()) {
                $errors['phone'] = 'An account with this contact number or email already exists.';
            }
        }

        if (!empty($errors)) {
            $this->view('app/register', [
                'pageTitle' => 'Create Account',
                'errors'    => $errors,
            ], 'mobile-auth');
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            "INSERT INTO users
               (name, email, phone, password, role, status, barangay, purok, address,
                household_count, has_senior, has_child, has_pwd, has_pregnant, home_lat, home_lng)
             VALUES
               (:name, :email, :phone, :password, 'resident', 'pending', :barangay, :purok, :address,
                :hh_count, :has_senior, :has_child, :has_pwd, :has_pregnant, :home_lat, :home_lng)"
        );
        $stmt->execute([
            'name'         => $name,
            'email'        => $email ?: null,
            'phone'        => $phone,
            'password'     => $hash,
            'barangay'     => $barangay ?: null,
            'purok'        => $purok    ?: null,
            'address'      => $address  ?: null,
            'hh_count'     => $hhCount,
            'has_senior'   => $hasSenior,
            'has_child'    => $hasChild,
            'has_pwd'      => $hasPwd,
            'has_pregnant' => $hasPregnant,
            'home_lat'     => $homeLat,
            'home_lng'     => $homeLng,
        ]);
        $newUserId = (int) $this->db->lastInsertId();

        // ── Masterlist matching ──────────────────────────────────────────
        $matchSvc = new \App\Services\MasterlistMatchService($this->db);
        $result = $matchSvc->findMatches($name);

        $matchStatus     = $result['match_type']; // matched/not_found/multiple/flagged_duplicate
        $matchId         = $result['best_match']['id'] ?? null;
        $confidence      = $result['confidence'] ?? 0;
        $finalMatchStatus = match($matchStatus) {
            'exact'              => 'matched',
            'fuzzy'              => 'matched',
            'multiple'           => 'unverified',
            'flagged_duplicate'  => 'flagged_duplicate',
            default              => 'not_found',
        };

        // Update user with match info
        $this->db->prepare(
            "UPDATE users SET masterlist_id = :mid, match_status = :ms, match_confidence = :mc WHERE id = :id"
        )->execute([
            'mid' => $matchId,
            'ms'  => $finalMatchStatus,
            'mc'  => $confidence,
            'id'  => $newUserId,
        ]);

        // Create residency_verification record
        $this->db->prepare(
            "INSERT INTO residency_verifications
               (user_id, masterlist_id, status, match_type, match_details)
             VALUES (:uid, :mid, 'pending', :mtype, :mdetails)"
        )->execute([
            'uid'      => $newUserId,
            'mid'      => $matchId,
            'mtype'    => $result['match_type'],
            'mdetails' => json_encode(array_map(fn($m) => [
                'id'        => $m['id'],
                'name'      => $m['name'],
                'purok'     => $m['purok'],
                'family_no' => $m['family_no'],
                'score'     => $m['score'],
            ], $result['matches'] ?? [])),
        ]);

        // ── Notify admins with match context ────────────────────────────
        $userModel = new \App\Models\User($this->db);
        $admins = $userModel->allByRole(['admin', 'personnel']);
        $adminIds = array_column($admins, 'id');

        $notifTitle = match($finalMatchStatus) {
            'matched'           => "Masterlist Match Found: {$name}",
            'flagged_duplicate' => "Possible Duplicate Registration: {$name}",
            'unverified'        => "Multiple Possible Matches: {$name}",
            default             => "Residency Verification Required: {$name}",
        };
        $notifMessage = match($finalMatchStatus) {
            'matched'           => "New registration matches masterlist record (confidence: {$confidence}%). Please review before approving.",
            'flagged_duplicate' => "Name matches a resident who already has an approved account. Review carefully for duplicate.",
            'unverified'        => "Multiple possible masterlist matches found. Admin must confirm the correct record.",
            default             => "Applicant was not found in the barangay masterlist. Residency must be verified by personnel before approval.",
        };

        $notif = new \App\Models\Notification($this->db);
        $notif->createForUsers($adminIds, $notifTitle, $notifMessage, '/residents');

        (new \App\Models\AuditLog($this->db))->write(
            null, 'auth.register', 'user', $newUserId,
            json_encode(['name' => $name, 'source' => 'pwa', 'match_status' => $finalMatchStatus, 'confidence' => $confidence])
        );

        $this->view('app/register-pending', [
            'pageTitle' => 'Account Pending',
            'name'      => $name,
        ], 'mobile-auth');
    }

    // ──────────────────────────────────────────────────────────────────
    // Resident PWA
    // ──────────────────────────────────────────────────────────────────

    public function residentHome(): void
    {
        $this->requireRole(['resident']);
        $user = auth();

        // Fetch real announcements — max 3, active, not expired, audience = all or residents
        $annStmt = $this->db->prepare(
            "SELECT id, title, body, severity, audience, event_date, created_at
             FROM announcements
             WHERE is_active = 1
               AND (audience = 'all' OR audience = 'residents')
               AND (event_date IS NULL OR event_date > NOW())
             ORDER BY FIELD(severity,'critical','warning','advisory','information'), created_at DESC
             LIMIT 3"
        );
        $annStmt->execute();
        $announcements = $annStmt->fetchAll();

        // My active reports
        $incidentModel = new \App\Models\Incident($this->db);
        $activeReports = $incidentModel->where('reported_by', $user['id']);
        $activeReports = array_filter($activeReports, fn($r) => !in_array($r['status'], ['resolved', 'closed', 'rejected', 'duplicate']));

        $this->view('app/resident/home', [
            'pageTitle'     => 'Home',
            'announcements' => $announcements,
            'activeReports' => array_slice($activeReports, 0, 3),
        ], 'mobile');
    }

    public function residentTrack(): void
    {
        $this->requireRole(['resident']);
        $user = auth();

        $incidentModel = new \App\Models\Incident($this->db);
        $incidents = $incidentModel->where('reported_by', $user['id']);

        $this->view('app/resident/track', [
            'pageTitle' => 'My Reports',
            'incidents' => $incidents,
        ], 'mobile');
    }

    public function residentTrackDetail(string $code): void
    {
        $this->requireRole(['resident']);
        $user = auth();

        $incidentModel = new \App\Models\Incident($this->db);
        $incident = $incidentModel->findByCode($code);

        if (!$incident || (int)$incident['reported_by'] !== (int)$user['id']) {
            $this->redirect('/app/resident/track');
            return;
        }

        $this->view('app/resident/track-detail', [
            'pageTitle' => 'Report ' . $code,
            'code'      => $code,
        ], 'mobile');
    }

    public function residentReport(): void
    {
        $this->requireRole(['resident']);
        $triage = new \App\Services\TriageService();
        $this->view('app/resident/report', [
            'pageTitle'   => 'Report Emergency',
            'hazardTypes' => $triage->getHazardTypes(),
        ], 'mobile');
    }

    public function residentGuides(): void
    {
        $this->requireRole(['resident']);
        $this->view('app/resident/guides', [
            'pageTitle' => 'Safety Guides',
        ], 'mobile');
    }

    public function residentContacts(): void
    {
        $this->requireRole(['resident']);
        $contactModel = new \App\Models\EmergencyContact($this->db);
        $contacts = $contactModel->getActive();

        // Group by unit_type for better display
        $grouped = [];
        foreach ($contacts as $c) {
            $type = $c['unit_type'] ?: 'other';
            $grouped[$type][] = $c;
        }

        $this->view('app/resident/contacts', [
            'pageTitle' => 'Emergency Contacts',
            'contacts'  => $grouped,
        ], 'mobile');
    }

    public function residentProfile(): void
    {
        $this->requireRole(['resident']);
        $user = auth();

        $errors  = [];
        $success = null;

        if ($this->isPost()) {
            if (!\App\Core\Csrf::verify($this->input('csrf_token'))) {
                $errors[] = 'Invalid session. Please try again.';
            } else {
                $action = $this->input('action', '');

                if ($action === 'update_info') {
                    $name  = trim((string) $this->input('name', ''));
                    $phone = trim((string) $this->input('phone', ''));
                    $email = trim((string) $this->input('email', ''));

                    if (!$name) $errors[] = 'Name is required.';
                    if ($phoneErr = $this->validatePhone($phone, true)) $errors[] = $phoneErr;
                    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

                    if (empty($errors)) {
                        $dup = $this->db->prepare(
                            "SELECT id FROM users WHERE (phone=:phone OR (email!='' AND email=:email)) AND id!=:id LIMIT 1"
                        );
                        $dup->execute(['phone'=>$phone,'email'=>$email?:'__none__','id'=>$user['id']]);
                        if ($dup->fetch()) $errors[] = 'Phone or email is already used by another account.';
                    }

                    if (empty($errors)) {
                        $this->db->prepare(
                            "UPDATE users SET name=:name, phone=:phone, email=:email WHERE id=:id"
                        )->execute(['name'=>$name,'phone'=>$phone,'email'=>$email?:null,'id'=>$user['id']]);
                        $_SESSION['user']['name']  = $name;
                        $_SESSION['user']['phone'] = $phone;
                        $_SESSION['user']['email'] = $email;
                        $user    = auth();
                        $success = 'Profile updated successfully.';
                    }
                }

                if ($action === 'change_password') {
                    $current = (string) $this->input('current_password', '');
                    $newPass = (string) $this->input('new_password', '');
                    $confirm = (string) $this->input('confirm_password', '');

                    $row = $this->db->prepare("SELECT password FROM users WHERE id=:id LIMIT 1");
                    $row->execute(['id' => $user['id']]);
                    $stored = $row->fetchColumn();

                    if (!password_verify($current, $stored)) {
                        $errors[] = 'Current password is incorrect.';
                    } elseif ($passErr = $this->validatePassword($newPass)) {
                        $errors[] = $passErr;
                    } elseif ($newPass !== $confirm) {
                        $errors[] = 'New passwords do not match.';
                    } else {
                        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost'=>12]);
                        $this->db->prepare("UPDATE users SET password=:p WHERE id=:id")
                                 ->execute(['p'=>$hash,'id'=>$user['id']]);
                        $success = 'Password changed successfully.';
                    }
                }
            }
        }

        $this->view('app/resident/profile', [
            'pageTitle' => 'My Profile',
            'user'      => $user,
            'errors'    => $errors,
            'success'   => $success,
        ], 'mobile');
    }

    // ──────────────────────────────────────────────────────────────────
    // Responder PWA
    // ──────────────────────────────────────────────────────────────────

    public function responderHome(): void
    {
        $this->requireRole(['responder']);
        $user = auth();

        $teamModel = new \App\Models\ResponseTeam($this->db);
        $teams = $teamModel->teamsForUser((int) $user['id']);

        $missions = [];
        foreach ($teams as $team) {
            $assignModel = new \App\Models\Assignment($this->db);
            $teamMissions = $assignModel->missionsForTeam((int) $team['id']);
            foreach ($teamMissions as $m) {
                $m['team_name'] = $team['team_name'];
                $m['unit_type'] = $team['unit_type'] ?? '';
                $missions[] = $m;
            }
        }

        // Notification unread count
        $unreadCount = (int) $this->scalar(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0",
            ['uid' => (int)$user['id']]
        );

        $this->view('app/responder/home', [
            'pageTitle'    => 'Missions',
            'missions'     => $missions,
            'teams'        => $teams,
            'unreadCount'  => $unreadCount,
        ], 'mobile');
    }

    public function responderMissionDetail(string $id): void
    {
        $this->requireRole(['responder']);

        $unreadCount = (int) $this->scalar(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0",
            ['uid' => (int)auth()['id']]
        );

        $this->view('app/responder/mission-detail', [
            'pageTitle'    => 'Mission Detail',
            'assignmentId' => $id,
            'unreadCount'  => $unreadCount,
        ], 'mobile');
    }

    public function responderContacts(): void
    {
        $this->requireRole(['responder']);
        $contactModel = new \App\Models\EmergencyContact($this->db);
        $contacts = $contactModel->getActive();

        $grouped = [];
        foreach ($contacts as $c) {
            $type = $c['unit_type'] ?: 'other';
            $grouped[$type][] = $c;
        }

        $this->view('app/responder/contacts', [
            'pageTitle' => 'Contacts',
            'contacts'  => $grouped,
        ], 'mobile');
    }

    public function responderProfile(): void
    {
        $this->requireRole(['responder']);
        $user = auth();

        $teamModel = new \App\Models\ResponseTeam($this->db);
        $teams = $teamModel->teamsForUser((int) $user['id']);

        $unreadCount = (int) $this->scalar(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0",
            ['uid' => (int)$user['id']]
        );

        $errors   = [];
        $success  = null;

        if ($this->isPost()) {
            if (!\App\Core\Csrf::verify($this->input('csrf_token'))) {
                $errors[] = 'Invalid session. Please try again.';
            } else {
                $action = $this->input('action', '');

                if ($action === 'update_info') {
                    $name  = trim((string) $this->input('name', ''));
                    $phone = trim((string) $this->input('phone', ''));
                    $email = trim((string) $this->input('email', ''));

                    if (!$name) $errors[] = 'Name is required.';
                    if ($phoneErr = $this->validatePhone($phone, true)) $errors[] = $phoneErr;
                    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

                    if (empty($errors)) {
                        // Check phone/email uniqueness (excluding own record)
                        $dup = $this->db->prepare(
                            "SELECT id FROM users WHERE (phone = :phone OR (email != '' AND email = :email)) AND id != :id LIMIT 1"
                        );
                        $dup->execute(['phone' => $phone, 'email' => $email ?: '__none__', 'id' => $user['id']]);
                        if ($dup->fetch()) {
                            $errors[] = 'Phone or email is already used by another account.';
                        }
                    }

                    if (empty($errors)) {
                        $this->db->prepare(
                            "UPDATE users SET name=:name, phone=:phone, email=:email WHERE id=:id"
                        )->execute(['name' => $name, 'phone' => $phone, 'email' => $email ?: null, 'id' => $user['id']]);

                        // Refresh session
                        $_SESSION['user']['name']  = $name;
                        $_SESSION['user']['phone'] = $phone;
                        $_SESSION['user']['email'] = $email;
                        $user = auth();
                        $success = 'Profile updated successfully.';
                    }
                }

                if ($action === 'change_password') {
                    $current  = (string) $this->input('current_password', '');
                    $newPass  = (string) $this->input('new_password', '');
                    $confirm  = (string) $this->input('confirm_password', '');

                    // Verify current password
                    $row = $this->db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
                    $row->execute(['id' => $user['id']]);
                    $stored = $row->fetchColumn();

                    if (!password_verify($current, $stored)) {
                        $errors[] = 'Current password is incorrect.';
                    } elseif ($passErr = $this->validatePassword($newPass)) {
                        $errors[] = $passErr;
                    } elseif ($newPass !== $confirm) {
                        $errors[] = 'New passwords do not match.';
                    } else {
                        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
                        $this->db->prepare("UPDATE users SET password=:p WHERE id=:id")
                                 ->execute(['p' => $hash, 'id' => $user['id']]);
                        $success = 'Password changed successfully.';
                    }
                }
            }
        }

        $this->view('app/responder/profile', [
            'pageTitle'   => 'Profile',
            'user'        => $user,
            'teams'       => $teams,
            'unreadCount' => $unreadCount,
            'errors'      => $errors,
            'success'     => $success,
        ], 'mobile');
    }
}
