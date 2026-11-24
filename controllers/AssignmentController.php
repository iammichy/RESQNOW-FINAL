<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Incident;
use App\Models\Assignment;
use App\Models\ResponseTeam;
use App\Models\Notification;
use App\Models\AuditLog;

class AssignmentController extends Controller
{
    public function assign(string $code): void
    {
        $this->requireRole(['admin', 'personnel']);
        $this->guardCsrf();

        $user     = auth();
        $incident = $this->loadIncidentOr404($code);
        $incidentId = (int) $incident['id'];

        $teamId = (int) $this->input('team_id', 0);
        if (!$teamId) {
            $this->setFlash('danger', 'Please select a team.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $teamModel = new ResponseTeam($this->db);
        $team = $teamModel->find($teamId);

        if (!$team) {
            $this->setFlash('danger', 'Team not found.');
            $this->redirect('/reports/' . $code);
            return;
        }
        if ($team['status'] === 'offline') {
            $this->setFlash('danger', 'Cannot assign an offline team.');
            $this->redirect('/reports/' . $code);
            return;
        }
        if ($team['status'] === 'on_mission') {
            $this->setFlash('danger', 'Team "' . $team['team_name'] . '" is already on a mission.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $allowedStatuses = ['verified', 'triaged', 'submitted'];
        if (!in_array($incident['status'], $allowedStatuses, true)) {
            $this->setFlash('danger', 'Cannot assign a team to a ' . $incident['status'] . ' incident.');
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->db->beginTransaction();
        try {
            $assignModel = new Assignment($this->db);

            // Close any existing active assignment (reassign scenario)
            $existing = $assignModel->activeForIncident($incidentId);
            if ($existing) {
                $assignModel->closeActive($incidentId, 'reassigned');
                // Free previous team if no other active missions
                $prevTeam = $teamModel->find((int) $existing['team_id']);
                if ($prevTeam && $teamModel->activeMissionCount((int) $existing['team_id']) === 0) {
                    $teamModel->setStatus((int) $existing['team_id'], 'available');
                }
            }

            // Create new assignment
            $assignId = $assignModel->create([
                'incident_id' => $incidentId,
                'team_id'     => $teamId,
                'assigned_by' => (int) $user['id'],
                'status'      => 'active',
                'notes'       => $this->input('notes') ? trim((string) $this->input('notes')) : null,
            ]);

            // Update incident
            $incidentModel = new Incident($this->db);
            $incidentModel->changeStatus(
                $incidentId, $incident['status'], 'assigned',
                (int) $user['id'],
                'Assigned to ' . $team['team_name']
            );
            $this->db->prepare(
                "UPDATE incidents SET assigned_team = :name, assigned_team_id = :tid,
                 assigned_by = :uid, assigned_at = NOW() WHERE id = :id"
            )->execute([
                'name' => $team['team_name'],
                'tid'  => $teamId,
                'uid'  => $user['id'],
                'id'   => $incidentId,
            ]);

            // Team → on_mission
            $teamModel->setStatus($teamId, 'on_mission');

            // Audit
            (new AuditLog($this->db))->write(
                (int) $user['id'], 'incident.assigned', 'incident', $incidentId,
                json_encode([
                    'code'      => $code,
                    'team_id'   => $teamId,
                    'team_name' => $team['team_name'],
                    'reassign'  => $existing ? true : false,
                ])
            );

            // Notify team leader + all members
            $memberIds = $teamModel->memberIds($teamId);
            if ($team['leader_id']) {
                $memberIds[] = (int) $team['leader_id'];
            }
            $memberIds = array_unique($memberIds);

            $notif = new Notification($this->db);
            $verb = $existing ? 'reassigned' : 'assigned';
            $notif->createForUsers(
                $memberIds,
                'Mission ' . $verb . ': ' . $code,
                'Your team has been ' . $verb . ' to incident ' . $code .
                ' (' . $incident['hazard_type'] . ', triage: ' . $incident['triage_level'] . ').',
                'app/responder'
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->setFlash('danger', 'Assignment failed: ' . $e->getMessage());
            $this->redirect('/reports/' . $code);
            return;
        }

        $this->setFlash('success', 'Team "' . $team['team_name'] . '" assigned to report ' . $code . '.');
        $this->redirect('/reports/' . $code);
    }

    // ------------------------------------------------------------------

    private function loadIncidentOr404(string $code): array
    {
        $model = new Incident($this->db);
        $incident = $model->findByCode($code);
        if (!$incident) {
            http_response_code(404); die('404 - Report not found');
        }
        return $incident;
    }

    private function guardCsrf(): void
    {
        if (!Csrf::verify($this->input('csrf_token'))) {
            $this->setFlash('danger', 'Invalid session. Please try again.');
            $this->redirect('/reports');
            exit;
        }
    }
}
