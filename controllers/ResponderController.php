<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Incident;
use App\Models\Assignment;
use App\Models\ResponseTeam;
use App\Models\ResponseUpdate;
use App\Models\Notification;
use App\Models\AuditLog;

class ResponderController extends Controller
{
    // Valid action codes for in-progress updates
    private const ACTION_CODES = [
        'extinguishing'       => 'Extinguishing fire',
        'rescue_in_progress'  => 'Rescue in progress',
        'first_aid'           => 'Administering first aid',
        'evacuating'          => 'Evacuating residents',
        'clearing_area'       => 'Clearing the area',
        'transporting_patient'=> 'Transporting patient',
        'scene_secure'        => 'Scene secured',
        'incident_controlled' => 'Incident controlled',
    ];

    public function index(): void
    {
        $this->requireRole(['responder']);
        $user = auth();

        $teamModel = new ResponseTeam($this->db);
        $teams = $teamModel->teamsForUser((int) $user['id']);

        $missions = [];
        foreach ($teams as $team) {
            $assignModel = new Assignment($this->db);
            $teamMissions = $assignModel->missionsForTeam((int) $team['id']);
            foreach ($teamMissions as $m) {
                $m['team_name'] = $team['team_name'];
                $m['team_id']   = $team['id'];
                $missions[]     = $m;
            }
        }

        // Sort: active first, then by triage severity
        usort($missions, function ($a, $b) {
            $triageOrder = ['critical' => 0, 'high' => 1, 'moderate' => 2, 'low' => 3, 'pending_reassessment' => 4];
            return ($triageOrder[$a['triage_level']] ?? 9) <=> ($triageOrder[$b['triage_level']] ?? 9);
        });

        $this->view('responder/index', [
            'pageTitle'   => 'My Missions',
            'missions'    => $missions,
            'teams'       => $teams,
            'actionCodes' => self::ACTION_CODES,
            'flash'       => $this->flash(),
        ]);
    }

    public function acknowledge(string $assignmentId): void
    {
        $this->requireRole(['responder']);
        $this->guardCsrf();

        [$assignment, $incident, $team] = $this->loadMissionOrFail((int) $assignmentId);
        $user = auth();

        $this->db->beginTransaction();
        try {
            // Response update row (required for every action)
            $updateModel = new ResponseUpdate($this->db);
            $updateModel->create([
                'incident_id'  => (int) $incident['id'],
                'team_id'      => (int) $team['id'],
                'responder_id' => (int) $user['id'],
                'update_type'  => 'status_change',
                'action_code'  => 'acknowledged',
                'message'      => 'Mission acknowledged by ' . $user['name'] . ' (' . $team['team_name'] . ').',
            ]);

            $assignModel = new Assignment($this->db);
            $assignModel->setAcknowledged((int) $assignmentId);

            $incidentModel = new Incident($this->db);
            $incidentModel->changeStatus(
                (int) $incident['id'], $incident['status'], 'acknowledged',
                (int) $user['id'],
                'Acknowledged by ' . $user['name'] . ' (' . $team['team_name'] . ')'
            );

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.acknowledged', 'incident', (int) $incident['id'],
                json_encode(['code' => $incident['code'], 'team' => $team['team_name']])
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Error: ' . $e->getMessage());
        }

        $this->redirect('/responder');
    }

    public function enRoute(string $assignmentId): void
    {
        $this->requireRole(['responder']);
        $this->guardCsrf();

        [$assignment, $incident, $team] = $this->loadMissionOrFail((int) $assignmentId);
        $user = auth();

        $updateModel = new ResponseUpdate($this->db);
        $updateModel->create([
            'incident_id'  => (int) $incident['id'],
            'team_id'      => (int) $team['id'],
            'responder_id' => (int) $user['id'],
            'update_type'  => 'en_route',
            'action_code'  => 'en_route',
            'message'      => $user['name'] . ' (' . $team['team_name'] . ') is en route to the scene.',
        ]);

        (new AuditLog($this->db))->write(
            (int) $user['id'], 'incident.en_route', 'incident', (int) $incident['id'],
            json_encode(['code' => $incident['code']])
        );

        $this->setFlash('success', 'En route update logged.');
        $this->redirect('/responder');
    }

    public function onScene(string $assignmentId): void
    {
        $this->requireRole(['responder']);
        $this->guardCsrf();

        [$assignment, $incident, $team] = $this->loadMissionOrFail((int) $assignmentId);
        $user = auth();

        $this->db->beginTransaction();
        try {
            $updateModel = new ResponseUpdate($this->db);
            $updateModel->create([
                'incident_id'  => (int) $incident['id'],
                'team_id'      => (int) $team['id'],
                'responder_id' => (int) $user['id'],
                'update_type'  => 'status_change',
                'action_code'  => 'arrived_on_scene',
                'message'      => $user['name'] . ' (' . $team['team_name'] . ') has arrived on scene.',
            ]);

            $incidentModel = new Incident($this->db);
            $incidentModel->changeStatus(
                (int) $incident['id'], $incident['status'], 'on_scene',
                (int) $user['id'],
                'Arrived on scene — ' . $team['team_name']
            );

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.on_scene', 'incident', (int) $incident['id'],
                json_encode(['code' => $incident['code'], 'team' => $team['team_name']])
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Error: ' . $e->getMessage());
        }

        $this->redirect('/responder');
    }

    public function inProgress(string $assignmentId): void
    {
        $this->requireRole(['responder']);
        $this->guardCsrf();

        [$assignment, $incident, $team] = $this->loadMissionOrFail((int) $assignmentId);
        $user = auth();

        $actionCode = trim((string) $this->input('action_code', ''));
        $remarks    = trim((string) $this->input('remarks', ''));

        if (!array_key_exists($actionCode, self::ACTION_CODES)) {
            $this->setFlash('danger', 'Please select a valid action.');
            $this->redirect('/responder');
            return;
        }

        $label   = self::ACTION_CODES[$actionCode];
        $message = $user['name'] . ' (' . $team['team_name'] . '): ' . $label;
        if ($remarks !== '') {
            $message .= ' — ' . $remarks;
        }

        $this->db->beginTransaction();
        try {
            $updateModel = new ResponseUpdate($this->db);
            $updateModel->create([
                'incident_id'  => (int) $incident['id'],
                'team_id'      => (int) $team['id'],
                'responder_id' => (int) $user['id'],
                'update_type'  => 'status_change',
                'action_code'  => $actionCode,
                'message'      => $message,
            ]);

            // Only advance to in_progress if not already further along
            $advanceable = ['assigned', 'acknowledged', 'on_scene'];
            if (in_array($incident['status'], $advanceable, true)) {
                $incidentModel = new Incident($this->db);
                $incidentModel->changeStatus(
                    (int) $incident['id'], $incident['status'], 'in_progress',
                    (int) $user['id'],
                    $label . ($remarks ? ': ' . $remarks : '')
                );
            }

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.in_progress', 'incident', (int) $incident['id'],
                json_encode(['code' => $incident['code'], 'action_code' => $actionCode])
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Error: ' . $e->getMessage());
        }

        $this->redirect('/responder');
    }

    public function requestSupport(string $assignmentId): void
    {
        $this->requireRole(['responder']);
        $this->guardCsrf();

        [$assignment, $incident, $team] = $this->loadMissionOrFail((int) $assignmentId);
        $user = auth();

        $supportType = trim((string) $this->input('support_type', ''));
        $validTypes  = ['backup', 'ambulance', 'fire_truck', 'additional_personnel'];
        if (!in_array($supportType, $validTypes, true)) {
            $this->setFlash('danger', 'Please select a support type.');
            $this->redirect('/responder');
            return;
        }

        $message = $user['name'] . ' (' . $team['team_name'] . ') requested ' .
                   str_replace('_', ' ', $supportType) . ' support.';

        $updateModel = new ResponseUpdate($this->db);
        $updateModel->create([
            'incident_id'      => (int) $incident['id'],
            'team_id'          => (int) $team['id'],
            'responder_id'     => (int) $user['id'],
            'update_type'      => 'support_request',
            'action_code'      => 'support_requested',
            'message'          => $message,
            'support_requested'=> 1,
            'support_type'     => $supportType,
        ]);

        (new AuditLog($this->db))->write(
            (int) $user['id'], 'incident.support_requested', 'incident', (int) $incident['id'],
            json_encode(['code' => $incident['code'], 'support_type' => $supportType])
        );

        // Notify all admin/personnel
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

        $this->setFlash('success', 'Support request sent.');
        $this->redirect('/responder');
    }

    public function resolve(string $assignmentId): void
    {
        $this->requireRole(['responder']);
        $this->guardCsrf();

        [$assignment, $incident, $team] = $this->loadMissionOrFail((int) $assignmentId);
        $user = auth();

        $summary = trim((string) $this->input('resolution_summary', ''));
        if ($summary === '') {
            $this->setFlash('danger', 'A resolution summary is required.');
            $this->redirect('/responder');
            return;
        }

        $this->db->beginTransaction();
        try {
            $updateModel = new ResponseUpdate($this->db);
            $updateModel->create([
                'incident_id'  => (int) $incident['id'],
                'team_id'      => (int) $team['id'],
                'responder_id' => (int) $user['id'],
                'update_type'  => 'resolved',
                'action_code'  => 'incident_resolved',
                'message'      => $user['name'] . ' (' . $team['team_name'] . ') marked resolved. Summary: ' . $summary,
            ]);

            $incidentModel = new Incident($this->db);
            $incidentModel->changeStatus(
                (int) $incident['id'], $incident['status'], 'resolved',
                (int) $user['id'],
                'Resolved by ' . $team['team_name'] . '. ' . $summary
            );
            $this->db->prepare(
                "UPDATE incidents SET resolved_at = NOW() WHERE id = :id"
            )->execute(['id' => $incident['id']]);

            $assignModel = new Assignment($this->db);
            $assignModel->setResolved((int) $assignmentId);

            // Free team if no remaining active missions
            $teamModel = new ResponseTeam($this->db);
            if ($teamModel->activeMissionCount((int) $team['id']) === 0) {
                $teamModel->setStatus((int) $team['id'], 'available');
            }

            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.resolved', 'incident', (int) $incident['id'],
                json_encode(['code' => $incident['code'], 'team' => $team['team_name'], 'summary' => $summary])
            );

            // Notify resident
            if (!empty($incident['reported_by'])) {
                (new Notification($this->db))->createForUsers(
                    [(int) $incident['reported_by']],
                    'Report ' . $incident['code'] . ' resolved',
                    'Your incident report has been resolved. Summary: ' . $summary,
                    '/reports/' . $incident['code']
                );
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Error: ' . $e->getMessage());
            $this->redirect('/responder');
            return;
        }

        $this->setFlash('success', 'Incident ' . $incident['code'] . ' marked as resolved.');
        $this->redirect('/responder');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Load and authorize an assignment for the current responder.
     * Returns [assignment, incident, team] or redirects with error.
     */
    private function loadMissionOrFail(int $assignmentId): array
    {
        $user = auth();

        $stmt = $this->db->prepare(
            "SELECT a.*, i.code, i.hazard_type, i.triage_level, i.status,
                    i.id AS incident_id_col, i.reported_by,
                    t.id AS team_id_col, t.team_name, t.leader_id
             FROM assignments a
             JOIN incidents i ON i.id = a.incident_id
             JOIN response_teams t ON t.id = a.team_id
             WHERE a.id = :id AND a.status = 'active'
             LIMIT 1"
        );
        $stmt->execute(['id' => $assignmentId]);
        $row = $stmt->fetch();

        if (!$row) {
            $this->setFlash('danger', 'Mission not found or no longer active.');
            $this->redirect('/responder');
            exit;
        }

        // Verify this responder belongs to the team
        $teamModel = new ResponseTeam($this->db);
        if (!$teamModel->isMember((int) $row['team_id'], (int) $user['id'])) {
            $this->setFlash('danger', 'You are not a member of the assigned team.');
            $this->redirect('/responder');
            exit;
        }

        $assignment = [
            'id'          => $row['id'],
            'incident_id' => $row['incident_id'],
            'team_id'     => $row['team_id'],
            'status'      => $row['status'],
            'acknowledged_at' => $row['acknowledged_at'],
        ];
        $incident = [
            'id'           => $row['incident_id'],
            'code'         => $row['code'],
            'hazard_type'  => $row['hazard_type'],
            'triage_level' => $row['triage_level'],
            'status'       => $row['status'],
            'reported_by'  => $row['reported_by'],
        ];
        $team = [
            'id'        => $row['team_id'],
            'team_name' => $row['team_name'],
            'leader_id' => $row['leader_id'],
        ];

        return [$assignment, $incident, $team];
    }

    private function guardCsrf(): void
    {
        if (!Csrf::verify($this->input('csrf_token'))) {
            $this->setFlash('danger', 'Invalid session. Please try again.');
            $this->redirect('/responder');
            exit;
        }
    }
}
