<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Core\Csrf;

class ResidentManagementController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $search  = trim((string)($_GET['search'] ?? ''));
        $purok   = trim((string)($_GET['purok'] ?? ''));
        $regFilter = trim((string)($_GET['registered'] ?? ''));

        // ── Counts from masterlist + users ──────────────────────────────
        // Total residents = all in masterlist
        $totalResidents = (int)$this->scalar("SELECT COUNT(*) FROM resident_masterlist");
        // Registered = approved accounts in users
        $registeredCount = (int)$this->scalar("SELECT COUNT(*) FROM users WHERE role='resident' AND status='approved'");
        // Pending accounts
        $pendingCount = (int)$this->scalar("SELECT COUNT(*) FROM users WHERE role='resident' AND status='pending'");
        // Not registered = total minus approved (pending are not yet verified)
        $notRegisteredCount = $totalResidents - $registeredCount;

        // ── Family groups from masterlist ───────────────────────────────
        // Get all masterlist families, optionally filtered by purok/search
        $where = [];
        $params = [];
        if ($purok) {
            $where[] = "m.purok = :purok";
            $params['purok'] = (int)$purok;
        }
        if ($search) {
            $where[] = "m.name LIKE :search";
            $params['search'] = '%' . $search . '%';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Load masterlist grouped by family — active members only
        $activeBase = $where ? 'WHERE m.status = \'active\' AND ' . implode(' AND ', $where) : 'WHERE m.status = \'active\'';
        $familySql = "SELECT m.purok, m.family_no,
                             MIN(m.resident_no) AS first_resident_no,
                             COUNT(*) AS member_count
                      FROM resident_masterlist m
                      {$activeBase}
                      GROUP BY m.purok, m.family_no
                      ORDER BY m.purok, m.family_no";
        $famStmt = $this->db->prepare($familySql);
        $famStmt->execute($params);
        $familyRows = $famStmt->fetchAll();

        // For each family, load all active members
        $memberStmt = $this->db->prepare(
            "SELECT id, purok, resident_no, family_no, name FROM resident_masterlist
             WHERE purok = :purok AND family_no = :fam AND status = 'active' ORDER BY resident_no"
        );

        // Build registered user index — indexed by BOTH masterlist_id and normalized name
        $regUsers = $this->db->query(
            "SELECT id, name, purok, status, phone, email, created_at, masterlist_id
             FROM users WHERE role = 'resident'"
        )->fetchAll();

        $regByMasterlistId = [];  // masterlist_id → user
        $regByName         = [];  // normalized name → user

        foreach ($regUsers as $u) {
            if (!empty($u['masterlist_id'])) {
                $regByMasterlistId[(int)$u['masterlist_id']] = $u;
            }
            $regByName[mb_strtolower(trim($u['name']))] = $u;
        }

        // Assemble family data
        $families = [];
        foreach ($familyRows as $fam) {
            $memberStmt->execute(['purok' => $fam['purok'], 'fam' => $fam['family_no']]);
            $members = $memberStmt->fetchAll();

            // Match members against registered users
            // Priority: masterlist_id match > exact name match
            $registeredMembers = 0;
            foreach ($members as &$m) {
                $mid = (int)$m['id'];
                $key = mb_strtolower(trim($m['name']));
                // First try masterlist_id match (most reliable)
                $matchedUser = $regByMasterlistId[$mid] ?? null;
                // Fall back to name match
                if (!$matchedUser) {
                    $matchedUser = $regByName[$key] ?? null;
                }
                $m['user'] = $matchedUser;
                if ($matchedUser && $matchedUser['status'] === 'approved') {
                    $registeredMembers++;
                }
            }
            unset($m);

            $families[] = [
                'purok'             => $fam['purok'],
                'family_no'         => $fam['family_no'],
                'member_count'      => $fam['member_count'],
                'registered_count'  => $registeredMembers,
                'members'           => $members,
                'family_head'       => $members[0]['name'] ?? '',
            ];
        }

        // Apply registration filter after assembly
        if ($regFilter === 'registered') {
            $families = array_filter($families, fn($f) => $f['registered_count'] > 0);
        } elseif ($regFilter === 'not_registered') {
            $families = array_filter($families, fn($f) => $f['registered_count'] === 0);
        } elseif ($regFilter === 'full') {
            $families = array_filter($families, fn($f) => $f['registered_count'] >= $f['member_count']);
        }

        // Family registration summary (all families, unfiltered — for the chip counts)
        // A family has "at least 1 registered" if any member has an approved resident account
        // linked via masterlist_id. We compute this in SQL for accuracy.
        $famSummaryRows = $this->db->query(
            "SELECT m.purok, m.family_no,
                    COUNT(DISTINCT m.id) AS member_count,
                    COUNT(DISTINCT CASE WHEN u.status='approved' THEN u.id END) AS reg_count
             FROM resident_masterlist m
             LEFT JOIN users u ON u.masterlist_id = m.id AND u.role='resident'
             WHERE m.status = 'active'
             GROUP BY m.purok, m.family_no"
        )->fetchAll();

        $famWithReg = 0; $famAllReg = 0; $famNoReg = 0;
        $allFamiliesCount = count($famSummaryRows);
        foreach ($famSummaryRows as $f) {
            $reg = (int)$f['reg_count'];
            $tot = (int)$f['member_count'];
            if ($reg === 0)         $famNoReg++;
            elseif ($reg >= $tot)   $famAllReg++;
            else                    $famWithReg++;
        }
        // "At least 1 registered" chip = families with 1+ registered (partial + full)
        $famAtLeastOne = $famWithReg + $famAllReg;

        // All resident users with verification info for Registration Requests tab
        $regRequests = $this->db->query(
            "SELECT u.id, u.name, u.phone, u.email, u.purok, u.barangay, u.status,
                    u.match_status, u.match_confidence, u.masterlist_id, u.created_at,
                    rv.status AS verif_status, rv.verified_at, vu.name AS verified_by_name
             FROM users u
             LEFT JOIN residency_verifications rv ON rv.user_id = u.id
             LEFT JOIN users vu ON vu.id = rv.verified_by
             WHERE u.role = 'resident'
             ORDER BY FIELD(u.status,'pending','approved','rejected','suspended'),
                      FIELD(u.match_status,'flagged_duplicate','unverified','not_found','matched'), u.created_at DESC"
        )->fetchAll();

        $this->view('residents/index', [
            'pageTitle'          => 'Resident Management',
            'families'           => array_values($families),
            'counts'             => [
                'total'          => $totalResidents,
                'registered'     => $registeredCount,
                'pending'        => $pendingCount,
                'not_registered' => $notRegisteredCount,
                'families_total' => $allFamiliesCount,
            ],
            'famNoReg'           => $famNoReg,
            'famAllReg'          => $famAllReg,
            'famAtLeastOne'      => $famAtLeastOne,
            'filters'            => ['search' => $search, 'purok' => $purok, 'registered' => $regFilter],
            'regRequests'        => $regRequests,
            'db'                 => $this->db,
            'flash'              => $this->flash(),
        ]);
    }

    public function approve(string $id): void
    {
        $this->requireRole(['admin']); // Only admin can approve

        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/residents');
            return;
        }

        $admin = auth();
        $stmt = $this->db->prepare(
            "UPDATE users SET status = 'approved', approved_by = :aid, approved_at = NOW() WHERE id = :id AND role = 'resident'"
        );
        $stmt->execute(['aid' => $admin['id'], 'id' => (int) $id]);

        if ($stmt->rowCount() > 0) {
            // Notify resident
            (new Notification($this->db))->create(
                (int) $id,
                'Account Approved',
                'Your ResQNow account has been approved. You can now submit reports and access all features.',
                'app/resident/profile'
            );

            // Audit log
            (new AuditLog($this->db))->write(
                $admin['id'], 'resident.approved', 'user', (int) $id,
                json_encode(['action' => 'approved'])
            );

            $this->setFlash('success', 'Resident approved successfully.');
        }

        $this->redirect('/residents');
    }

    public function reject(string $id): void
    {
        $this->requireRole(['admin']);

        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/residents');
            return;
        }

        $reason = trim((string) $this->input('reason', ''));
        if (!$reason) {
            $this->setFlash('danger', 'Rejection reason is required.');
            $this->redirect('/residents');
            return;
        }

        $admin = auth();
        $stmt = $this->db->prepare(
            "UPDATE users SET status = 'rejected', rejection_reason = :reason, approved_by = :aid, approved_at = NOW() 
             WHERE id = :id AND role = 'resident'"
        );
        $stmt->execute(['reason' => $reason, 'aid' => $admin['id'], 'id' => (int) $id]);

        if ($stmt->rowCount() > 0) {
            (new Notification($this->db))->create(
                (int) $id,
                'Account Rejected',
                'Your account registration was rejected. Reason: ' . $reason,
                'app/login'
            );

            (new AuditLog($this->db))->write(
                $admin['id'], 'resident.rejected', 'user', (int) $id,
                json_encode(['reason' => $reason])
            );

            $this->setFlash('info', 'Resident rejected.');
        }

        $this->redirect('/residents');
    }

    public function export(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $rows = $this->db->query(
            "SELECT id, name, email, phone, barangay, purok, address,
                    household_count, has_senior, has_child, has_pwd, has_pregnant,
                    status, created_at, approved_at
             FROM users WHERE role = 'resident'
             ORDER BY FIELD(status,'pending','approved','suspended','rejected'), name"
        )->fetchAll();

        $reportCounts = [];
        $rc = $this->db->query(
            "SELECT reported_by, COUNT(*) AS cnt FROM incidents WHERE reported_by IS NOT NULL GROUP BY reported_by"
        )->fetchAll();
        foreach ($rc as $r) { $reportCounts[(int)$r['reported_by']] = (int)$r['cnt']; }

        $filename = 'resqnow-residents-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM
        fputcsv($out, [
            'ID', 'Name', 'Email', 'Phone', 'Barangay', 'Purok', 'Address',
            'Household Members', 'Has Senior', 'Has Child', 'Has PWD', 'Has Pregnant',
            'Status', 'Reports Submitted', 'Registered At', 'Approved At',
        ]);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'], $r['name'], $r['email'] ?? '', $r['phone'] ?? '',
                $r['barangay'] ?? '', $r['purok'] ?? '', $r['address'] ?? '',
                $r['household_count'] ?? '',
                $r['has_senior'] ? 'Yes' : 'No',
                $r['has_child']  ? 'Yes' : 'No',
                $r['has_pwd']    ? 'Yes' : 'No',
                $r['has_pregnant'] ? 'Yes' : 'No',
                ucfirst($r['status']),
                $reportCounts[(int)$r['id']] ?? 0,
                $r['created_at'],
                $r['approved_at'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function show(string $id): void
    {
        $this->requireRole(['admin', 'personnel']);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND role = 'resident' LIMIT 1");
        $stmt->execute(['id' => (int) $id]);
        $resident = $stmt->fetch();

        if (!$resident) {
            $this->redirect('/residents');
            return;
        }

        // Reports history
        $stmt = $this->db->prepare("SELECT * FROM incidents WHERE reported_by = :id ORDER BY created_at DESC");
        $stmt->execute(['id' => (int) $id]);
        $reports = $stmt->fetchAll();

        // Masterlist record if linked
        $masterlistRecord = null;
        if (!empty($resident['masterlist_id'])) {
            $ml = $this->db->prepare("SELECT * FROM resident_masterlist WHERE id = :id LIMIT 1");
            $ml->execute(['id' => (int)$resident['masterlist_id']]);
            $masterlistRecord = $ml->fetch() ?: null;
        }

        // Residency verification record
        $verif = $this->db->prepare(
            "SELECT rv.*, u.name AS verified_by_name
             FROM residency_verifications rv
             LEFT JOIN users u ON u.id = rv.verified_by
             WHERE rv.user_id = :uid ORDER BY rv.created_at DESC LIMIT 1"
        );
        $verif->execute(['uid' => (int)$id]);
        $verifRecord = $verif->fetch() ?: null;

        $this->view('residents/show', [
            'pageTitle'       => 'Resident Detail: ' . $resident['name'],
            'resident'        => $resident,
            'reports'         => $reports,
            'masterlistRecord'=> $masterlistRecord,
            'verifRecord'     => $verifRecord,
        ]);
    }

    // ── Residency Verification ────────────────────────────────────────

    public function verifyResidency(string $id): void
    {
        $this->requireRole(['admin']);
        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/residents'); return;
        }

        $admin = auth();
        $masterlistId = $this->input('masterlist_id') ? (int)$this->input('masterlist_id') : null;
        $notes = trim((string)$this->input('notes', ''));

        // Check if a verification record exists for this user
        $existing = $this->db->prepare("SELECT id FROM residency_verifications WHERE user_id = :uid LIMIT 1");
        $existing->execute(['uid' => (int)$id]);
        $verifId = $existing->fetchColumn();

        if ($verifId) {
            $this->db->prepare(
                "UPDATE residency_verifications SET status='verified', masterlist_id=:mid, verified_by=:vid, verified_at=NOW(), notes=:notes
                 WHERE id = :vid_row"
            )->execute(['mid' => $masterlistId, 'vid' => $admin['id'], 'notes' => $notes ?: null, 'vid_row' => $verifId]);
        } else {
            $this->db->prepare(
                "INSERT INTO residency_verifications (user_id, status, masterlist_id, verified_by, verified_at, notes)
                 VALUES (:uid, 'verified', :mid, :vid, NOW(), :notes)"
            )->execute(['uid' => (int)$id, 'mid' => $masterlistId, 'vid' => $admin['id'], 'notes' => $notes ?: null]);
        }

        // Link user to masterlist record if provided
        if ($masterlistId) {
            $this->db->prepare(
                "UPDATE users SET masterlist_id=:mid, match_status='matched' WHERE id=:id"
            )->execute(['mid' => $masterlistId, 'id' => (int)$id]);
        } else {
            // Not found but verified by barangay
            $this->db->prepare(
                "UPDATE users SET match_status='not_found' WHERE id=:id"
            )->execute(['id' => (int)$id]);
        }

        (new AuditLog($this->db))->write($admin['id'], 'resident.residency_verified', 'user', (int)$id,
            json_encode(['masterlist_id' => $masterlistId, 'notes' => $notes]));

        $this->setFlash('success', 'Residency verified. You can now approve the account.');
        $this->redirect('/residents/' . $id);
    }

    public function rejectResidency(string $id): void
    {
        $this->requireRole(['admin']);
        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/residents'); return;
        }

        $admin = auth();
        $notes = trim((string)$this->input('notes', ''));

        // Check if a verification record exists for this user
        $existing = $this->db->prepare("SELECT id FROM residency_verifications WHERE user_id = :uid LIMIT 1");
        $existing->execute(['uid' => (int)$id]);
        $verifId = $existing->fetchColumn();

        if ($verifId) {
            $this->db->prepare(
                "UPDATE residency_verifications SET status='rejected', verified_by=:vid, verified_at=NOW(), notes=:notes
                 WHERE id = :vid_row"
            )->execute(['vid' => $admin['id'], 'notes' => $notes ?: null, 'vid_row' => $verifId]);
        } else {
            $this->db->prepare(
                "INSERT INTO residency_verifications (user_id, status, verified_by, verified_at, notes)
                 VALUES (:uid, 'rejected', :vid, NOW(), :notes)"
            )->execute(['uid' => (int)$id, 'vid' => $admin['id'], 'notes' => $notes ?: null]);
        }

        // Also reject the user account
        $this->db->prepare(
            "UPDATE users SET status='rejected', rejection_reason=:reason, approved_by=:aid, approved_at=NOW()
             WHERE id=:id AND role='resident'"
        )->execute(['reason' => 'Residency could not be verified. ' . $notes, 'aid' => $admin['id'], 'id' => (int)$id]);

        (new Notification($this->db))->create((int)$id, 'Account Rejected',
            'Your residency could not be verified. Reason: ' . ($notes ?: 'Not a registered resident of Barangay Camunatan.'), 'app/login');
        (new AuditLog($this->db))->write($admin['id'], 'resident.residency_rejected', 'user', (int)$id, json_encode(['notes' => $notes]));

        $this->setFlash('info', 'Residency rejected and account denied.');
        $this->redirect('/residents');
    }

    // ── Masterlist CRUD ───────────────────────────────────────────────

    public function newMasterlistResident(): void
    {
        $this->requireRole(['admin']);

        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) { $this->redirect('/residents'); return; }

            $name     = trim((string)$this->input('name', ''));
            $purok    = (int)$this->input('purok', 0);
            $familyNo = (int)$this->input('family_no', 0);
            $notes    = trim((string)$this->input('notes', ''));

            if (!$name || !$purok) {
                $this->setFlash('danger', 'Name and Purok are required.');
                $this->redirect('/residents/masterlist/new');
                return;
            }

            // Auto-assign resident_no within purok
            $maxNo = (int)$this->db->prepare(
                "SELECT COALESCE(MAX(resident_no),0) FROM resident_masterlist WHERE purok=:p"
            )->execute(['p'=>$purok]) ? $this->scalar(
                "SELECT COALESCE(MAX(resident_no),0) FROM resident_masterlist WHERE purok=:p", ['p'=>$purok]
            ) : 0;

            // If new family, assign next family_no
            if (!$familyNo) {
                $familyNo = (int)$this->scalar(
                    "SELECT COALESCE(MAX(family_no),0)+1 FROM resident_masterlist WHERE purok=:p", ['p'=>$purok]
                );
            }

            $admin = auth();
            $this->db->prepare(
                "INSERT INTO resident_masterlist (purok, resident_no, family_no, name, notes, added_by) VALUES (:p,:rn,:fn,:name,:notes,:by)"
            )->execute(['p'=>$purok,'rn'=>$maxNo+1,'fn'=>$familyNo,'name'=>$name,'notes'=>$notes?:null,'by'=>$admin['id']]);

            (new AuditLog($this->db))->write($admin['id'], 'masterlist.resident_added', 'resident_masterlist', (int)$this->db->lastInsertId(),
                json_encode(['name'=>$name,'purok'=>$purok,'family_no'=>$familyNo]));

            $this->setFlash('success', "Resident \"{$name}\" added to Purok {$purok} masterlist.");
            $this->redirect('/residents?tab=directory&purok=' . $purok . '&highlight_family=' . $familyNo);
            return;
        }

        // Get family options per purok
        $families = $this->db->query(
            "SELECT purok, family_no, MIN(name) AS family_head FROM resident_masterlist WHERE status='active' GROUP BY purok, family_no ORDER BY purok, family_no"
        )->fetchAll();

        $this->view('residents/masterlist_form', [
            'pageTitle' => 'Add Resident to Masterlist',
            'record'    => null,
            'families'  => $families,
            'preset_purok'  => (int)($_GET['purok'] ?? 0),
            'preset_family' => (int)($_GET['family_no'] ?? 0),
        ]);
    }

    public function editMasterlistResident(string $id): void
    {
        $this->requireRole(['admin']);

        $stmt = $this->db->prepare("SELECT * FROM resident_masterlist WHERE id=:id LIMIT 1");
        $stmt->execute(['id'=>(int)$id]);
        $record = $stmt->fetch();
        if (!$record) { $this->redirect('/residents'); return; }

        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) { $this->redirect('/residents'); return; }

            $name     = trim((string)$this->input('name', ''));
            $purok    = (int)$this->input('purok', $record['purok']);
            $familyNo = (int)$this->input('family_no', $record['family_no']);
            $notes    = trim((string)$this->input('notes', ''));

            $admin = auth();
            $this->db->prepare(
                "UPDATE resident_masterlist SET name=:name, purok=:p, family_no=:fn, notes=:notes, updated_by=:by WHERE id=:id"
            )->execute(['name'=>$name,'p'=>$purok,'fn'=>$familyNo,'notes'=>$notes?:null,'by'=>$admin['id'],'id'=>(int)$id]);

            (new AuditLog($this->db))->write($admin['id'], 'masterlist.resident_updated', 'resident_masterlist', (int)$id,
                json_encode(['name'=>$name,'purok'=>$purok,'family_no'=>$familyNo]));

            $this->setFlash('success', 'Masterlist record updated.');
            $this->redirect('/residents?tab=directory&purok=' . $purok . '&highlight_family=' . $familyNo);
            return;
        }

        $families = $this->db->query(
            "SELECT purok, family_no, MIN(name) AS family_head FROM resident_masterlist WHERE status='active' GROUP BY purok, family_no ORDER BY purok, family_no"
        )->fetchAll();

        $this->view('residents/masterlist_form', [
            'pageTitle' => 'Edit Masterlist Resident',
            'record'    => $record,
            'families'  => $families,
        ]);
    }

    public function archiveMasterlistResident(string $id): void
    {
        $this->requireRole(['admin']);
        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/residents'); return;
        }

        $newStatus = $this->input('archive_status', 'archived');
        $validStatuses = ['deceased','transferred','archived'];
        if (!in_array($newStatus, $validStatuses)) $newStatus = 'archived';
        $notes = trim((string)$this->input('notes',''));

        $admin = auth();
        $this->db->prepare(
            "UPDATE resident_masterlist SET status=:s, notes=:n, updated_by=:by WHERE id=:id"
        )->execute(['s'=>$newStatus,'n'=>$notes?:null,'by'=>$admin['id'],'id'=>(int)$id]);

        (new AuditLog($this->db))->write($admin['id'], 'masterlist.resident_archived', 'resident_masterlist', (int)$id,
            json_encode(['status'=>$newStatus,'notes'=>$notes]));

        // Fetch purok/family_no so we can return to the same family
        $row = $this->db->prepare("SELECT purok, family_no FROM resident_masterlist WHERE id=:id LIMIT 1");
        $row->execute(['id'=>(int)$id]);
        $r = $row->fetch();

        $this->setFlash('info', 'Resident marked as ' . $newStatus . '.');
        $this->redirect('/residents?tab=directory' . ($r ? '&purok='.$r['purok'].'&highlight_family='.$r['family_no'] : ''));
    }

    // ── Masterlist Export ─────────────────────────────────────────────

    public function exportMasterlist(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $purok = $this->input('purok') ? (int)$this->input('purok') : null;
        $where = $purok ? "WHERE m.purok = {$purok} AND m.status='active'" : "WHERE m.status='active'";

        $rows = $this->db->query(
            "SELECT m.purok, m.family_no, m.resident_no, m.name, m.status,
                    u.id AS user_id, u.status AS account_status, u.phone, u.email
             FROM resident_masterlist m
             LEFT JOIN users u ON u.masterlist_id = m.id AND u.role='resident'
             {$where}
             ORDER BY m.purok, m.family_no, m.resident_no"
        )->fetchAll();

        // Summary stats
        $totalResidents = count($rows);
        $registered     = count(array_filter($rows, fn($r) => $r['account_status'] === 'approved'));

        $suffix  = $purok ? "-purok{$purok}" : '-all';
        $filename = 'camunatan-masterlist' . $suffix . '-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        // Summary rows first
        fputcsv($out, ['BARANGAY CAMUNATAN — RESIDENT MASTERLIST']);
        fputcsv($out, ['Generated:', date('F j, Y g:i A')]);
        fputcsv($out, ['Purok Filter:', $purok ? 'Purok '.$purok : 'All Puroks']);
        fputcsv($out, ['Total Residents:', $totalResidents]);
        fputcsv($out, ['Registered (Approved):', $registered]);
        fputcsv($out, ['Not Registered:', $totalResidents - $registered]);
        fputcsv($out, []);

        // Headers
        fputcsv($out, ['Purok', 'Family No.', 'Resident No.', 'Name', 'Account Status', 'Phone', 'Email']);

        $curFamily = null;
        foreach ($rows as $r) {
            $famKey = $r['purok'].'-'.$r['family_no'];
            if ($curFamily !== $famKey) {
                if ($curFamily !== null) fputcsv($out, []);  // blank line between families
                $curFamily = $famKey;
            }
            fputcsv($out, [
                'Purok ' . $r['purok'],
                'Family #' . $r['family_no'],
                $r['resident_no'],
                $r['name'],
                $r['account_status'] ? ucfirst($r['account_status']) : 'Not Registered',
                $r['phone'] ?? '',
                $r['email'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

}
