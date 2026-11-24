<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Incident;
use App\Models\IncidentAnswer;
use App\Models\IncidentStatusHistory;
use App\Models\VerificationRecord;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TriageService;

class IncidentApiController extends Controller
{
    private TriageService $triage;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->triage = new TriageService();
    }

    public function getQuestions(): void
    {
        $this->requireApiAuth();
        $hazardType = $_GET['hazard_type'] ?? '';
        
        if (!$hazardType) {
            $this->json(['error' => 'Hazard type is required'], 400);
            return;
        }

        $questions = $this->triage->getQuestions($hazardType);
        $vuln = $this->triage->getVulnerabilityQuestion();

        $this->json([
            'questions' => array_merge($questions, [$vuln]),
        ]);
    }

    public function store(): void
    {
        $user = $this->requireApiAuth();

        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!is_array($data)) {
            // Also try $_POST for same-origin form submissions
            $data = !empty($_POST) ? $_POST : null;
        }
        if (!is_array($data)) {
            $this->json(['error' => 'Invalid JSON', 'message' => 'Request body must be valid JSON.'], 400);
            return;
        }

        // CSRF check for session-based calls
        if ($this->authenticateSession()) {
            if (!$this->verifyCsrfForApi()) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
                return;
            }
            // Rate limit: 10 per minute per user (session-based only)
            if ($this->isRateLimited('incident_api_' . ($user['id'] ?? 0), 10, 60)) {
                http_response_code(429);
                $this->json(['error' => 'Too many submissions. Please wait a moment.'], 429);
                return;
            }
        }

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'fields' => $errors], 422);
            return;
        }

        $hazardType = $data['hazard_type'];
        $answers = $data['answers'] ?? [];
        $triage = $this->triage->compute($answers, $hazardType);

        $code = $this->generateReportCode();

        $incidentModel = new Incident($this->db);
        $incidentId = $incidentModel->create([
            'code'           => $code,
            'hazard_type'    => $hazardType,
            'description'    => $data['description'] ?? null,
            'is_emergency'   => !empty($data['is_emergency']) ? 1 : 0,
            'source'         => 'resident_mobile',
            'location_lat'   => isset($data['incident_lat']) ? (float) $data['incident_lat'] : null,
            'location_lng'   => isset($data['incident_lng']) ? (float) $data['incident_lng'] : null,
            'address'        => $data['incident_address'] ?? null,
            'triage_level'   => $triage['level'],
            'triage_score'   => $triage['score'],
            'unsure_ratio'   => $triage['unsure_ratio'],
            'status'         => 'submitted',
            'reported_by'    => $user['id'],
        ]);

        $answerModel = new IncidentAnswer($this->db);
        $answerModel->createMany($incidentId, $triage['breakdown']);

        $audit = new AuditLog($this->db);
        $audit->write(
            $user['id'],
            'incident.created',
            'incident',
            $incidentId,
            json_encode([
                'code'         => $code,
                'hazard_type'  => $hazardType,
                'triage_level' => $triage['level'],
                'triage_score' => $triage['score'],
                'source'       => 'resident_mobile',
            ])
        );

        $userModel = new User($this->db);
        $admins = $userModel->allByRole(['admin', 'personnel']);
        $adminIds = array_column($admins, 'id');
        $notif = new Notification($this->db);
        $notif->createForUsers(
            $adminIds,
            'New Incident Report: ' . $code,
            'A new ' . $hazardType . ' report was submitted. Triage: ' . $triage['level'] . ' (score ' . $triage['score'] . ').',
            'reports/' . $code
        );

        $this->json([
            'success'     => true,
            'incident_id' => $incidentId,
            'code'        => $code,
            'triage'      => $triage,
        ]);
    }

    public function show(string $code): void
    {
        $user = $this->requireApiAuth();
        $model = new Incident($this->db);
        $incident = $model->findByCode($code);

        if (!$incident) {
            $this->json(['error' => 'Not found'], 404);
            return;
        }

        // Only allow reporter or admin/personnel/responder
        if ($user['role'] === 'resident' && (int)$incident['reported_by'] !== (int)$user['id']) {
            $this->json(['error' => 'Forbidden'], 403);
            return;
        }

        $this->json(['incident' => $incident]);
    }

    private function validate(array $data): array
    {
        $errors = [];
        $validHazards = $this->triage->getHazardTypes();

        if (empty($data['hazard_type'])) {
            $errors['hazard_type'] = 'hazard_type is required.';
        } elseif (!in_array($data['hazard_type'], $validHazards, true)) {
            $errors['hazard_type'] = 'hazard_type must be one of: ' . implode(', ', $validHazards) . '.';
        }

        if (isset($data['incident_lat'])) {
            $lat = (float) $data['incident_lat'];
            if ($lat < -90 || $lat > 90) {
                $errors['incident_lat'] = 'incident_lat must be between -90 and 90.';
            }
        }
        if (isset($data['incident_lng'])) {
            $lng = (float) $data['incident_lng'];
            if ($lng < -180 || $lng > 180) {
                $errors['incident_lng'] = 'incident_lng must be between -180 and 180.';
            }
        }

        if (isset($data['answers']) && !is_array($data['answers'])) {
            $errors['answers'] = 'answers must be an object/array of question keys to answers.';
        } elseif (!empty($data['hazard_type'])) {
            $answers = $data['answers'] ?? [];
            $questions = $this->triage->getQuestions($data['hazard_type']);
            $vuln = $this->triage->getVulnerabilityQuestion();
            $allQuestions = array_merge($questions, [$vuln]);
            
            foreach ($allQuestions as $q) {
                if (empty($answers[$q['key']])) {
                    $errors['answers'] = 'All triage questions must be answered.';
                    break;
                }
            }
        }

        return $errors;
    }
    // generateReportCode() is inherited from Controller base — safe MAX() version
}
