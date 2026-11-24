<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Incident;
use App\Models\IncidentAnswer;
use App\Models\IncidentStatusHistory;
use App\Models\VerificationRecord;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TriageService;

class IncidentController extends Controller
{
    private TriageService $triage;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->triage = new TriageService();
    }

    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'hazard_type'  => trim((string) ($_GET['hazard_type'] ?? '')),
            'status'       => trim((string) ($_GET['status'] ?? '')),
            'triage_level' => trim((string) ($_GET['triage_level'] ?? '')),
            'is_emergency' => isset($_GET['is_emergency']) && $_GET['is_emergency'] !== '' ? (int) $_GET['is_emergency'] : '',
            'date_from'    => trim((string) ($_GET['date_from'] ?? '')),
            'date_to'      => trim((string) ($_GET['date_to'] ?? '')),
            'search'       => trim((string) ($_GET['search'] ?? '')),
        ];

        $model = new Incident($this->db);
        $result = $model->paginated(array_filter($filters, fn($v) => $v !== '' && $v !== null), $page, 15);

        // Overall summary (all-time, ignores filters)
        $summary = $this->db->query(
            "SELECT
                COUNT(*)                                                        AS total_reports,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END)           AS resolved_count,
                SUM(CASE WHEN status IN ('assigned','acknowledged','on_scene','in_progress') THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END)          AS pending_count
             FROM incidents"
        )->fetch();

        // Most reported hazard type
        $topHazard = $this->db->query(
            "SELECT hazard_type, COUNT(*) AS cnt
             FROM incidents GROUP BY hazard_type ORDER BY cnt DESC LIMIT 1"
        )->fetch();

        // Most affected area (by address, using first part before comma)
        $topArea = $this->db->query(
            "SELECT
                TRIM(SUBSTRING_INDEX(address, ',', 1)) AS area,
                COUNT(*) AS cnt
             FROM incidents
             WHERE address IS NOT NULL AND address != ''
             GROUP BY area ORDER BY cnt DESC LIMIT 1"
        )->fetch();

        // Hazard breakdown for summary
        $hazardBreakdown = $this->db->query(
            "SELECT hazard_type, COUNT(*) AS cnt
             FROM incidents GROUP BY hazard_type ORDER BY cnt DESC"
        )->fetchAll();

        $this->view('reports/index', [
            'pageTitle'      => 'All Reports',
            'incidents'      => $result['data'],
            'pagination'     => $result,
            'filters'        => $filters,
            'flash'          => $this->flash(),
            'summary'        => [
                'total'           => (int)($summary['total_reports'] ?? 0),
                'resolved'        => (int)($summary['resolved_count'] ?? 0),
                'active'          => (int)($summary['active_count'] ?? 0),
                'pending'         => (int)($summary['pending_count'] ?? 0),
                'top_hazard'      => $topHazard ?: null,
                'top_area'        => $topArea ?: null,
                'hazard_breakdown'=> $hazardBreakdown,
            ],
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = auth();

        $model = new Incident($this->db);

        if ($this->isPost()) {
            $step = (int) $this->input('step', 1);
            if ($step === 4) {
                $this->store();
                return;
            }
            $this->handleStep($step, $user);
            return;
        }

        $step = max(1, min(4, (int) ($_GET['step'] ?? 1)));
        $form = $_SESSION['report_form'] ?? [];

        if ($step === 1) {
            $form = [];
            $_SESSION['report_form'] = $form;
        }

        $hazardType = $form['hazard_type'] ?? '';
        $questions = $hazardType ? $this->triage->getQuestions($hazardType) : [];
        $vulnQuestion = $this->triage->getVulnerabilityQuestion();

        $this->view('reports/create', [
            'pageTitle'     => 'New Report',
            'step'          => $step,
            'form'          => $form,
            'hazardTypes'   => $this->triage->getHazardTypes(),
            'questions'     => $questions,
            'vulnQuestion'  => $vulnQuestion,
            'user'          => $user,
            'errors'        => $_SESSION['report_errors'] ?? [],
        ]);
        unset($_SESSION['report_errors']);
    }

    private function handleStep(int $step, ?array $user): void
    {
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            $this->view('reports/create', [
                'pageTitle' => 'New Report',
                'step'      => 1,
                'form'      => [],
                'hazardTypes' => $this->triage->getHazardTypes(),
                'questions'   => [],
                'vulnQuestion' => $this->triage->getVulnerabilityQuestion(),
                'user'        => $user,
                'errors'      => ['csrf' => 'Invalid session. Please refresh and try again.'],
            ]);
            return;
        }

        $form = $_SESSION['report_form'] ?? [];

        if ($step === 1) {
            $hazardType = trim((string) $this->input('hazard_type', ''));
            $isEmergency = $this->input('is_emergency') ? 1 : 0;
            $errors = [];
            if (!in_array($hazardType, $this->triage->getHazardTypes(), true)) {
                $errors['hazard_type'] = 'Please select a hazard type.';
            }
            if (!empty($errors)) {
                $_SESSION['report_errors'] = $errors;
                $this->redirect('/reports/new?step=1');
                return;
            }
            $form['hazard_type'] = $hazardType;
            $form['is_emergency'] = $isEmergency;
            $form['answers'] = [];
            $_SESSION['report_form'] = $form;
            $this->redirect('/reports/new?step=2');
            return;
        }

        if ($step === 2) {
            $answers = $this->input('answers', []);
            if (!is_array($answers)) {
                $answers = [];
            }

            // Save answers to session FIRST so they repopulate on validation failure
            $form['answers'] = $answers;
            $_SESSION['report_form'] = $form;

            // Validate all questions are answered
            $hazardType = $form['hazard_type'] ?? '';
            $questions = $this->triage->getQuestions($hazardType);
            $vulnQuestion = $this->triage->getVulnerabilityQuestion();
            $allQuestions = array_merge($questions, [$vulnQuestion]);

            $errors = [];
            foreach ($allQuestions as $q) {
                if (empty($answers[$q['key']])) {
                    $errors['answers'] = 'Please answer all triage questions before continuing.';
                    break;
                }
            }

            if (!empty($errors)) {
                $_SESSION['report_errors'] = $errors;
                $this->redirect('/reports/new?step=2');
                return;
            }

            $this->redirect('/reports/new?step=3');
            return;
        }

        if ($step === 3) {
            $form['incident_address'] = trim((string) $this->input('incident_address', ''));
            $form['incident_lat'] = $this->input('incident_lat', '');
            $form['incident_lng'] = $this->input('incident_lng', '');
            $form['description'] = trim((string) $this->input('description', ''));

            $errors = [];
            if (empty($form['incident_address'])) {
                $errors['incident_address'] = 'Incident address is required.';
            }

            // Photo required for non-emergency reports
            $hasNewUpload = !empty($_FILES['media']['name'][0]) && $_FILES['media']['error'][0] === UPLOAD_ERR_OK;
            $hasExistingMedia = !empty($form['media_temp']);
            if (($form['is_emergency'] ?? 1) === 0 && !$hasNewUpload && !$hasExistingMedia) {
                $errors['media'] = 'At least one photo is required for non-emergency reports.';
            }

            if (!empty($errors)) {
                // Save partial data so address/description repopulate on redirect
                $_SESSION['report_form'] = $form;
                $_SESSION['report_errors'] = $errors;
                $this->redirect('/reports/new?step=3');
                return;
            }

            // Handle media uploads — save to public/uploads/temp/ for preview
            if ($hasNewUpload) {
                if (!isset($form['media_temp']) || !is_array($form['media_temp'])) {
                    $form['media_temp'] = [];
                }
                $uploadDir = APP_ROOT . '/uploads/temp/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['media']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['media']['name'][$key], PATHINFO_EXTENSION);
                        $name = bin2hex(random_bytes(8)) . '.' . $ext;
                        $targetPath = $uploadDir . $name;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $form['media_temp'][] = [
                                'name' => $_FILES['media']['name'][$key],
                                'temp_name' => $name,
                                'type' => $_FILES['media']['type'][$key]
                            ];
                        }
                    }
                }
            }

            $_SESSION['report_form'] = $form;
            $this->redirect('/reports/new?step=4');
            return;
        }
    }

    public function store(): void
    {
        $this->requireAuth();
        $user = auth();

        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            $_SESSION['report_errors'] = ['csrf' => 'Invalid session. Please refresh and try again.'];
            $this->redirect('/reports/new?step=4');
            return;
        }

        // Rate limit: 10 submissions per minute per user
        if ($this->isRateLimited('incident_submit_' . ($user['id'] ?? 'anon'), 10, 60)) {
            $_SESSION['report_errors'] = ['rate' => 'Too many submissions. Please wait a moment.'];
            $this->redirect('/reports/new?step=4');
            return;
        }

        $form = $_SESSION['report_form'] ?? [];

        $errors = [];
        if (empty($form['hazard_type'])) {
            $errors['hazard_type'] = 'Hazard type is required.';
        }

        $lat = isset($form['incident_lat']) && $form['incident_lat'] !== '' ? (float) $form['incident_lat'] : null;
        $lng = isset($form['incident_lng']) && $form['incident_lng'] !== '' ? (float) $form['incident_lng'] : null;

        if ($lat !== null && ($lat < -90 || $lat > 90)) {
            $errors['incident_lat'] = 'Latitude must be between -90 and 90.';
        }
        if ($lng !== null && ($lng < -180 || $lng > 180)) {
            $errors['incident_lng'] = 'Longitude must be between -180 and 180.';
        }

        if (!empty($errors)) {
            $_SESSION['report_errors'] = $errors;
            $this->redirect('/reports/new?step=3');
            return;
        }

        $answers = $form['answers'] ?? [];
        $triage = $this->triage->compute($answers, $form['hazard_type']);

        $code = $this->generateReportCode();

        $source = ($user['role'] ?? '') === 'admin' ? 'admin_manual' : 'resident_web';

        // Admin-created reports are auto-verified (skip verification queue)
        $isAdmin = ($user['role'] ?? '') === 'admin';
        $status = $isAdmin ? 'verified' : 'submitted';

        $incidentModel = new Incident($this->db);
        $incidentId = $incidentModel->create([
            'code'           => $code,
            'hazard_type'    => $form['hazard_type'],
            'description'    => $form['description'] ?? null,
            'is_emergency'   => $form['is_emergency'] ?? 1,
            'source'         => $source,
            'location_lat'   => $lat,
            'location_lng'   => $lng,
            'address'        => $form['incident_address'] ?? null,
            'triage_level'   => $triage['level'],
            'triage_score'   => $triage['score'],
            'unsure_ratio'   => $triage['unsure_ratio'],
            'status'         => $status,
            'reported_by'    => $user['id'] ?? null,
        ]);

        $answerModel = new IncidentAnswer($this->db);
        $answerModel->createMany($incidentId, $triage['breakdown']);

        // Log auto-verification for admin reports
        if ($isAdmin) {
            $vrModel = new VerificationRecord($this->db);
            $vrModel->create([
                'incident_id'        => $incidentId,
                'action'             => 'verified',
                'original_triage_level' => $triage['level'],
                'new_triage_level'   => $triage['level'],
                'reason'             => 'Auto-verified (admin manual report)',
                'verified_by'        => $user['id'],
            ]);
            // Log status history
            $historyModel = new IncidentStatusHistory($this->db);
            $historyModel->record($incidentId, 'submitted', 'verified', $user['id'], 'Auto-verified (admin manual report)');
        }

        $audit = new AuditLog($this->db);
        $audit->write(
            $user['id'] ?? null,
            'incident.created',
            'incident',
            $incidentId,
            json_encode([
                'code'         => $code,
                'hazard_type'  => $form['hazard_type'],
                'triage_level' => $triage['level'],
                'triage_score' => $triage['score'],
            ])
        );

        // Only notify admins if a resident submitted the report
        if (!$isAdmin) {
            $userModel = new User($this->db);
            $admins = $userModel->allByRole(['admin', 'personnel']);
            $adminIds = array_column($admins, 'id');
            $notif = new Notification($this->db);
            $notif->createForUsers(
                $adminIds,
                'New Incident Report: ' . $code,
                'A new ' . $form['hazard_type'] . ' report was submitted. Triage: ' . $triage['level'] . ' (score ' . $triage['score'] . ').',
                '/reports/' . $code
            );
        }

        unset($_SESSION['report_form'], $_SESSION['report_errors']);
        $this->setFlash('success', 'Report ' . $code . ' submitted successfully. Triage: ' . ucfirst($triage['level']) . ' (score ' . $triage['score'] . ').' . ($isAdmin ? ' Auto-verified.' : ''));
        $this->redirect('/reports/' . $code);
    }

    public function show(string $code): void
    {
        $this->requireRole(['admin', 'personnel', 'resident']);

        $incidentModel = new Incident($this->db);
        $incident = $incidentModel->findByCode($code);

        if (!$incident) {
            http_response_code(404);
            echo '404 - Report not found';
            return;
        }

        $user = auth();
        $isResident = ($user['role'] ?? '') === 'resident';
        if ($isResident && (int) ($incident['reported_by'] ?? 0) !== (int) ($user['id'] ?? 0)) {
            http_response_code(403);
            die('403 Forbidden');
        }

        $answerModel = new IncidentAnswer($this->db);
        $answers = $answerModel->forIncident((int) $incident['id']);

        $reporter = null;
        if (!empty($incident['reported_by'])) {
            $userModel = new User($this->db);
            $reporter = $userModel->find((int) $incident['reported_by']);
        }

        $histModel = new IncidentStatusHistory($this->db);
        $statusHistory = $histModel->forIncident((int) $incident['id']);

        $verifModel = new VerificationRecord($this->db);
        $verificationRecords = $verifModel->forIncident((int) $incident['id']);

        // M4: assignment + response updates for timeline
        $assignModel  = new \App\Models\Assignment($this->db);
        $updateModel  = new \App\Models\ResponseUpdate($this->db);
        $teamModel    = new \App\Models\ResponseTeam($this->db);

        $activeAssignment   = $assignModel->activeForIncident((int) $incident['id']);
        $assignmentHistory  = $assignModel->allForIncident((int) $incident['id']);
        $responseUpdates    = $updateModel->forIncident((int) $incident['id']);

        $isStaff = in_array($user['role'] ?? '', ['admin', 'personnel'], true);
        $availableTeams = $isStaff ? $teamModel->availableFor($incident['hazard_type'] ?? 'other') : [];

        $hazardQuestions = $this->triage->getQuestions($incident['hazard_type'] ?? 'other');
        $vulnQuestion    = $this->triage->getVulnerabilityQuestion();

        $this->view('reports/show', [
            'pageTitle'           => 'Report ' . $code,
            'incident'            => $incident,
            'answers'             => $answers,
            'hazardQuestions'     => $hazardQuestions,
            'vulnQuestion'        => $vulnQuestion,
            'reporter'            => $reporter,
            'statusHistory'       => $statusHistory,
            'verificationRecords' => $verificationRecords,
            'responseUpdates'     => $responseUpdates,
            'assignmentHistory'   => $assignmentHistory,
            'activeAssignment'    => $activeAssignment,
            'availableTeams'      => $availableTeams,
            'canVerify'           => $isStaff,
            'canAssign'           => $isStaff,
            'flash'               => $this->flash(),
        ]);
    }

    // ------------------------------------------------------------------
    // M3: Verification actions
    // ------------------------------------------------------------------

    public function verify(string $code): void
    {
        $this->requireRole(['admin', 'personnel']);
        $user = auth();
        $this->guardCsrf();

        $incident = $this->loadIncidentOr404($code);
        if ($incident['triage_level'] === 'pending_reassessment') {
            $this->setFlash('danger', 'Cannot verify: triage level is pending reassessment. Reassess first.');
            $this->redirect('/reports/' . $code);
            return;
        }
        if ($incident['status'] !== 'submitted') {
            $this->setFlash('danger', 'Only submitted reports can be verified.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->db->beginTransaction();
        try {
            $model = new Incident($this->db);
            $model->changeStatus((int) $incident['id'], $incident['status'], 'verified', (int) $user['id'], 'Verified by ' . $user['name']);

            $verif = new VerificationRecord($this->db);
            $verif->create([
                'incident_id'         => (int) $incident['id'],
                'action'              => 'verified',
                'original_triage_level' => $incident['triage_level'],
                'new_triage_level'    => $incident['triage_level'],
                'verified_by'         => (int) $user['id'],
            ]);

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.verified', 'incident', (int) $incident['id'],
                json_encode(['code' => $code, 'triage_level' => $incident['triage_level']])
            );

            $this->notifyResident($code, (int) ($incident['reported_by'] ?? 0),
                'Report ' . $code . ' verified',
                'Your report has been verified and is being processed.');

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Verification failed: ' . $e->getMessage());
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->setFlash('success', 'Report ' . $code . ' verified successfully.');
        $this->redirect('/reports/' . $code);
    }

    public function reassess(string $code): void
    {
        $this->requireRole(['admin', 'personnel']);
        $user = auth();
        $this->guardCsrf();

        $incident = $this->loadIncidentOr404($code);

        $answers = $this->input('answers', []);
        $reason = trim((string) $this->input('reason', ''));

        if (!is_array($answers) || empty($answers)) {
            $this->setFlash('danger', 'Please complete the verification form.');
            $this->redirect('/reports/' . $code);
            return;
        }

        if ($reason === '') {
            $this->setFlash('danger', 'Verification notes are required.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $originalLevel = $incident['triage_level'];
        
        // Re-calculate triage based on new verification answers
        $triage = $this->triage->compute($answers, $incident['hazard_type']);
        $newLevel = $triage['level'];
        $newScore = $triage['score'];

        $this->db->beginTransaction();
        try {
            $model = new Incident($this->db);
            $model->updateTriageLevel((int) $incident['id'], $newLevel, $newScore);
            
            // Update unsure ratio as well
            $stmt = $this->db->prepare("UPDATE incidents SET unsure_ratio = :ratio WHERE id = :id");
            $stmt->execute(['ratio' => $triage['unsure_ratio'], 'id' => $incident['id']]);

            // Replace old answers with new verified answers
            $answerModel = new IncidentAnswer($this->db);
            $answerModel->deleteForIncident((int) $incident['id']);
            $answerModel->createMany((int) $incident['id'], $triage['breakdown']);

            $model->changeStatus((int) $incident['id'], $incident['status'], $incident['status'], (int) $user['id'],
                'Situation verified & reassessed: ' . $originalLevel . ' → ' . $newLevel . ' (Score: ' . $newScore . '). Notes: ' . $reason);

            $verif = new VerificationRecord($this->db);
            $verif->create([
                'incident_id'         => (int) $incident['id'],
                'action'              => 'verified_situation',
                'original_triage_level' => $originalLevel,
                'new_triage_level'    => $newLevel,
                'reason'              => $reason,
                'verified_by'         => (int) $user['id'],
            ]);

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.situation_verified', 'incident', (int) $incident['id'],
                json_encode(['code' => $code, 'from' => $originalLevel, 'to' => $newLevel, 'score' => $newScore, 'reason' => $reason])
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Verification failed: ' . $e->getMessage());
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->setFlash('success', 'Situation verified. Triage level updated to ' . ucfirst($newLevel) . ' (Score: ' . $newScore . ').');
        $this->redirect('/reports/' . $code);
    }

    public function reject(string $code): void
    {
        $this->requireRole(['admin', 'personnel']);
        $user = auth();
        $this->guardCsrf();

        $incident = $this->loadIncidentOr404($code);
        $reason = trim((string) $this->input('reason', ''));

        if ($reason === '') {
            $this->setFlash('danger', 'A rejection reason is required.');
            $this->redirect('/reports/' . $code);
            return;
        }
        if ($incident['status'] !== 'submitted') {
            $this->setFlash('danger', 'Only submitted reports can be rejected.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->db->beginTransaction();
        try {
            $model = new Incident($this->db);
            $model->changeStatus((int) $incident['id'], $incident['status'], 'rejected', (int) $user['id'], 'Rejected: ' . $reason);

            $verif = new VerificationRecord($this->db);
            $verif->create([
                'incident_id'         => (int) $incident['id'],
                'action'              => 'rejected',
                'original_triage_level' => $incident['triage_level'],
                'new_triage_level'    => $incident['triage_level'],
                'reason'              => $reason,
                'verified_by'         => (int) $user['id'],
            ]);

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.rejected', 'incident', (int) $incident['id'],
                json_encode(['code' => $code, 'reason' => $reason])
            );

            $this->notifyResident($code, (int) ($incident['reported_by'] ?? 0),
                'Report ' . $code . ' rejected',
                'Your report was rejected. Reason: ' . $reason);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Rejection failed: ' . $e->getMessage());
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->setFlash('success', 'Report ' . $code . ' rejected.');
        $this->redirect('/reports/' . $code);
    }

    public function markDuplicate(string $code): void
    {
        $this->requireRole(['admin', 'personnel']);
        $user = auth();
        $this->guardCsrf();

        $incident = $this->loadIncidentOr404($code);
        $originalCode = trim((string) $this->input('original_code', ''));

        if ($originalCode === '') {
            $this->setFlash('danger', 'Please provide the original report code.');
            $this->redirect('/reports/' . $code);
            return;
        }
        if ($incident['status'] !== 'submitted') {
            $this->setFlash('danger', 'Only submitted reports can be marked duplicate.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->db->beginTransaction();
        try {
            $model = new Incident($this->db);
            $model->changeStatus((int) $incident['id'], $incident['status'], 'duplicate', (int) $user['id'],
                'Marked duplicate of ' . $originalCode);

            $verif = new VerificationRecord($this->db);
            $verif->create([
                'incident_id'         => (int) $incident['id'],
                'action'              => 'duplicate',
                'original_triage_level' => $incident['triage_level'],
                'new_triage_level'    => $incident['triage_level'],
                'original_report_code' => $originalCode,
                'reason'              => 'Duplicate of ' . $originalCode,
                'verified_by'         => (int) $user['id'],
            ]);

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.duplicate', 'incident', (int) $incident['id'],
                json_encode(['code' => $code, 'original_code' => $originalCode])
            );

            $this->notifyResident($code, (int) ($incident['reported_by'] ?? 0),
                'Report ' . $code . ' marked duplicate',
                'Your report was marked as a duplicate of ' . $originalCode . '.');

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Failed to mark duplicate: ' . $e->getMessage());
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->setFlash('success', 'Report ' . $code . ' marked as duplicate of ' . $originalCode . '.');
        $this->redirect('/reports/' . $code);
    }

    public function escalate(string $code): void
    {
        $this->requireRole(['admin', 'personnel']);
        $user = auth();
        $this->guardCsrf();

        $incident = $this->loadIncidentOr404($code);

        if (!empty($incident['is_escalated'])) {
            $this->setFlash('info', 'Report is already escalated.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->db->beginTransaction();
        try {
            $model = new Incident($this->db);
            $model->setEscalated((int) $incident['id'], true);
            $model->changeStatus((int) $incident['id'], $incident['status'], $incident['status'], (int) $user['id'],
                'Escalated for priority attention.');

            $verif = new VerificationRecord($this->db);
            $verif->create([
                'incident_id'         => (int) $incident['id'],
                'action'              => 'escalated',
                'original_triage_level' => $incident['triage_level'],
                'new_triage_level'    => $incident['triage_level'],
                'reason'              => 'Escalated by ' . $user['name'],
                'verified_by'         => (int) $user['id'],
            ]);

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.escalated', 'incident', (int) $incident['id'],
                json_encode(['code' => $code])
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Escalation failed: ' . $e->getMessage());
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->setFlash('success', 'Report ' . $code . ' escalated.');
        $this->redirect('/reports/' . $code);
    }

    public function reopen(string $code): void
    {
        $this->requireRole(['admin']);
        $user = auth();
        $this->guardCsrf();

        $incident = $this->loadIncidentOr404($code);
        $allowed = ['verified', 'rejected', 'duplicate', 'resolved', 'closed'];
        if (!in_array($incident['status'], $allowed, true)) {
            $this->setFlash('danger', 'Only verified/rejected/duplicate/resolved/closed reports can be reopened.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $reason = trim((string) $this->input('reason', ''));
        if (empty($reason)) {
            $reason = 'No reason provided';
        }

        $this->db->beginTransaction();
        try {
            $model = new Incident($this->db);
            $model->changeStatus((int) $incident['id'], $incident['status'], 'submitted', (int) $user['id'], $reason);

            $verif = new VerificationRecord($this->db);
            $verif->create([
                'incident_id'         => (int) $incident['id'],
                'action'              => 'reopened',
                'original_triage_level' => $incident['triage_level'],
                'new_triage_level'    => $incident['triage_level'],
                'reason'              => $reason,
                'verified_by'         => (int) $user['id'],
            ]);

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.reopened', 'incident', (int) $incident['id'],
                json_encode(['code' => $code, 'from_status' => $incident['status'], 'reason' => $reason])
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Reopen failed: ' . $e->getMessage());
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->setFlash('success', 'Report ' . $code . ' reopened and returned to the verification queue.');
        $this->redirect('/reports/' . $code);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function guardCsrf(): void
    {
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            $this->setFlash('danger', 'Invalid session. Please refresh and try again.');
            $this->redirect('/reports');
            exit;
        }
    }

    private function loadIncidentOr404(string $code): array
    {
        $model = new Incident($this->db);
        $incident = $model->findByCode($code);
        if (!$incident) {
            http_response_code(404);
            die('404 - Report not found');
        }
        return $incident;
    }

    private function notifyResident(string $code, int $residentId, string $title, string $message): void
    {
        if ($residentId <= 0) {
            return;
        }
        $notif = new Notification($this->db);
        $notif->createForUsers([$residentId], $title, $message, '/reports/' . $code);
    }
}
