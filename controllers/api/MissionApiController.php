<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Assignment;
use App\Models\ResponseTeam;
use App\Models\ResponseUpdate;
use App\Models\Incident;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;

class MissionApiController extends Controller
{
    private const ACTION_CODES = [
        'extinguishing', 'rescue_in_progress', 'first_aid', 'evacuating',
        'clearing_area', 'transporting_patient', 'scene_secure', 'incident_controlled',
    ];
    private const SUPPORT_TYPES = ['backup', 'ambulance', 'fire_truck', 'additional_personnel'];

    public function acknowledge(string $assignmentId): void
    {
        $user = $this->authResponder();
        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        [$assignment, $incident, $team] = $this->loadMission((int) $assignmentId, (int) $user['id']);

        $this->db->beginTransaction();
        try {
            (new ResponseUpdate($this->db))->create([
                'incident_id'  => (int) $incident['id'],
                'team_id'      => (int) $team['id'],
                'responder_id' => (int) $user['id'],
                'update_type'  => 'status_change',
                'action_code'  => 'acknowledged',
                'message'      => $user['name'] . ' (' . $team['team_name'] . ') acknowledged via API.',
            ]);

            (new Assignment($this->db))->setAcknowledged((int) $assignmentId);

            (new Incident($this->db))->changeStatus(
                (int) $incident['id'], $incident['status'], 'acknowledged',
                (int) $user['id'],
                'Acknowledged by ' . $user['name'] . ' (API)'
            );

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.acknowledged', 'incident', (int) $incident['id'],
                json_encode(['code' => $incident['code'], 'source' => 'api'])
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->json(['error' => $e->getMessage()], 500);
        }

        $this->json(['status' => 'acknowledged', 'assignment_id' => (int) $assignmentId]);
    }

    public function update(string $assignmentId): void
    {
        $user = $this->authResponder();
        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        [$assignment, $incident, $team] = $this->loadMission((int) $assignmentId, (int) $user['id']);

        $updateType = trim((string) ($body['update_type'] ?? 'note'));
        $actionCode = trim((string) ($body['action_code'] ?? ''));
        $message    = trim((string) ($body['message'] ?? ''));

        if ($message === '') {
            $this->json(['error' => 'message is required'], 422);
        }

        // If action_code provided, validate it
        $validCodes = array_merge(self::ACTION_CODES, [
            'acknowledged', 'en_route', 'on_scene', 'arrived_on_scene',
            'in_progress', 'incident_resolved',
        ]);
        if ($actionCode !== '' && !in_array($actionCode, $validCodes, true)) {
            $this->json(['error' => 'Invalid action_code'], 422);
        }

        $updateModel = new ResponseUpdate($this->db);
        $updateId = $updateModel->create([
            'incident_id'  => (int) $incident['id'],
            'team_id'      => (int) $team['id'],
            'responder_id' => (int) $user['id'],
            'update_type'  => $updateType,
            'action_code'  => $actionCode ?: null,
            'message'      => $message,
        ]);

        // Advance incident status for known action codes
        // Note: en_route is NOT a valid incident status — it's a movement log only
        $statusMap = [
            'on_scene'         => 'on_scene',
            'arrived_on_scene' => 'on_scene',
            'in_progress'      => 'in_progress',
        ];
        if ($actionCode !== '' && isset($statusMap[$actionCode])) {
            $newStatus = $statusMap[$actionCode];
            // Only advance status (never go backwards)
            $statusOrder = ['assigned'=>1,'acknowledged'=>2,'on_scene'=>3,'in_progress'=>4,'resolved'=>5];
            $currentOrder = $statusOrder[$incident['status']] ?? 0;
            $newOrder     = $statusOrder[$newStatus] ?? 0;
            if ($newOrder > $currentOrder) {
                (new Incident($this->db))->changeStatus(
                    (int) $incident['id'], $incident['status'], $newStatus,
                    (int) $user['id'], $message
                );
            }
        }

        $this->json(['update_id' => $updateId, 'status' => 'logged'], 201);
    }

    public function supportRequest(string $assignmentId): void
    {
        $user = $this->authResponder();
        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        [$assignment, $incident, $team] = $this->loadMission((int) $assignmentId, (int) $user['id']);

        $supportType = trim((string) ($body['support_type'] ?? ''));
        if (!in_array($supportType, self::SUPPORT_TYPES, true)) {
            $this->json(['error' => 'support_type must be one of: ' . implode(', ', self::SUPPORT_TYPES)], 422);
        }

        $message = $user['name'] . ' (' . $team['team_name'] . ') requested ' .
                   str_replace('_', ' ', $supportType) . ' support.';

        (new ResponseUpdate($this->db))->create([
            'incident_id'      => (int) $incident['id'],
            'team_id'          => (int) $team['id'],
            'responder_id'     => (int) $user['id'],
            'update_type'      => 'support_request',
            'action_code'      => 'support_requested',
            'message'          => $message,
            'support_requested'=> 1,
            'support_type'     => $supportType,
        ]);

        $stmt = $this->db->query(
            "SELECT id FROM users WHERE role IN ('admin','personnel') AND status = 'approved'"
        );
        $adminIds = array_column($stmt->fetchAll(), 'id');
        (new Notification($this->db))->createForUsers(
            $adminIds,
            '🆘 Support requested: ' . $incident['code'],
            $message,
            '/monitoring'
        );

        $this->json(['status' => 'support_requested', 'support_type' => $supportType], 201);
    }

    public function resolve(string $assignmentId): void
    {
        $user = $this->authResponder();
        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        [$assignment, $incident, $team] = $this->loadMission((int) $assignmentId, (int) $user['id']);

        $summary = trim((string) ($body['resolution_summary'] ?? ''));
        if ($summary === '') {
            $this->json(['error' => 'resolution_summary is required'], 422);
        }

        $this->db->beginTransaction();
        try {
            (new ResponseUpdate($this->db))->create([
                'incident_id'  => (int) $incident['id'],
                'team_id'      => (int) $team['id'],
                'responder_id' => (int) $user['id'],
                'update_type'  => 'resolved',
                'action_code'  => 'incident_resolved',
                'message'      => $user['name'] . ': ' . $summary,
            ]);

            (new Incident($this->db))->changeStatus(
                (int) $incident['id'], $incident['status'], 'resolved',
                (int) $user['id'],
                'Resolved by ' . $team['team_name'] . '. ' . $summary
            );
            $this->db->prepare("UPDATE incidents SET resolved_at = NOW() WHERE id = :id")
                     ->execute(['id' => $incident['id']]);

            $assignModel = new Assignment($this->db);
            $assignModel->setResolved((int) $assignmentId);

            $teamModel = new ResponseTeam($this->db);
            if ($teamModel->activeMissionCount((int) $team['id']) === 0) {
                $teamModel->setStatus((int) $team['id'], 'available');
            }

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.resolved', 'incident', (int) $incident['id'],
                json_encode(['code' => $incident['code'], 'source' => 'api'])
            );

            if (!empty($incident['reported_by'])) {
                (new Notification($this->db))->createForUsers(
                    [(int) $incident['reported_by']],
                    'Report ' . $incident['code'] . ' resolved',
                    'Your incident has been resolved. Summary: ' . $summary,
                    '/reports/' . $incident['code']
                );
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->json(['error' => $e->getMessage()], 500);
        }

        $this->json(['status' => 'resolved', 'code' => $incident['code']]);
    }

    // ------------------------------------------------------------------

    private function authResponder(): array
    {
        return $this->requireApiAuth('responder');
    }

    private function loadMission(int $assignmentId, int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, i.code, i.hazard_type, i.triage_level, i.status,
                    i.id AS incident_id, i.reported_by,
                    t.id AS team_id_col, t.team_name, t.leader_id
             FROM assignments a
             JOIN incidents i ON i.id = a.incident_id
             JOIN response_teams t ON t.id = a.team_id
             WHERE a.id = :id AND a.status = 'active' LIMIT 1"
        );
        $stmt->execute(['id' => $assignmentId]);
        $row = $stmt->fetch();

        if (!$row) {
            $this->json(['error' => 'Assignment not found or not active'], 404);
        }

        $teamModel = new ResponseTeam($this->db);
        if (!$teamModel->isMember((int) $row['team_id'], $userId)) {
            $this->json(['error' => 'Forbidden — not a member of the assigned team'], 403);
        }

        return [
            ['id' => $row['id'], 'incident_id' => $row['incident_id'], 'team_id' => $row['team_id'],
             'status' => $row['status'], 'acknowledged_at' => $row['acknowledged_at']],
            ['id' => $row['incident_id'], 'code' => $row['code'], 'hazard_type' => $row['hazard_type'],
             'triage_level' => $row['triage_level'], 'status' => $row['status'], 'reported_by' => $row['reported_by']],
            ['id' => $row['team_id'], 'team_name' => $row['team_name'], 'leader_id' => $row['leader_id']],
        ];
    }
}
