<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Assignment;
use App\Models\ResponseTeam;
use App\Models\ResponseUpdate;
use App\Models\IncidentStatusHistory;

class ResponderApiController extends Controller
{
    /** GET /api/v1/responder/missions?status=active */
    public function missions(): void
    {
        $user = $this->requireApiAuth('responder');

        $statusFilter = $_GET['status'] ?? 'active';
        $validStatuses = ['active', 'reassigned', 'completed'];
        if (!in_array($statusFilter, $validStatuses, true)) {
            $statusFilter = 'active';
        }

        $teamModel = new ResponseTeam($this->db);
        $teams = $teamModel->teamsForUser((int) $user['id']);

        $missions = [];
        foreach ($teams as $team) {
            $stmt = $this->db->prepare(
                "SELECT a.id AS assignment_id, a.assigned_at, a.acknowledged_at, a.status AS assignment_status,
                        i.code, i.hazard_type, i.triage_level, i.triage_score, i.status AS incident_status,
                        i.address, i.location_lat, i.location_lng, i.description,
                        i.id AS incident_id,
                        (SELECT COUNT(*) FROM response_updates ru
                         WHERE ru.incident_id = i.id AND ru.support_requested = 1
                           AND ru.support_acknowledged_at IS NULL) AS open_support
                 FROM assignments a
                 JOIN incidents i ON i.id = a.incident_id
                 WHERE a.team_id = :tid AND a.status = :s
                 ORDER BY a.assigned_at DESC"
            );
            $stmt->execute(['tid' => $team['id'], 's' => $statusFilter]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) {
                $r['team_name'] = $team['team_name'];
                $r['team_id']   = $team['id'];
                $missions[] = $r;
            }
        }

        $this->json(['missions' => $missions]);
    }

    /** GET /api/v1/responder/missions/{id} — full mission detail */
    public function missionDetail(string $assignmentId): void
    {
        $user = $this->requireApiAuth('responder');

        $stmt = $this->db->prepare(
            "SELECT a.id AS assignment_id, a.assigned_at, a.acknowledged_at, a.status AS assignment_status,
                    i.id AS incident_id, i.code, i.hazard_type, i.triage_level, i.triage_score,
                    i.status AS incident_status, i.address, i.location_lat, i.location_lng,
                    i.description, i.reported_by,
                    t.id AS team_id, t.team_name, t.unit_type,
                    u.name AS reporter_name, u.phone AS reporter_phone
             FROM assignments a
             JOIN incidents i ON i.id = a.incident_id
             JOIN response_teams t ON t.id = a.team_id
             LEFT JOIN users u ON u.id = i.reported_by
             WHERE a.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => (int) $assignmentId]);
        $row = $stmt->fetch();

        if (!$row) {
            $this->json(['error' => 'Not Found'], 404);
        }

        // Verify membership
        $teamModel = new ResponseTeam($this->db);
        if (!$teamModel->isMember((int) $row['team_id'], (int) $user['id'])) {
            $this->json(['error' => 'Forbidden'], 403);
        }

        // Timeline
        $history = (new IncidentStatusHistory($this->db))->forIncident((int) $row['incident_id']);
        $updates = (new ResponseUpdate($this->db))->forIncident((int) $row['incident_id']);

        $timeline = [];
        foreach ($history as $h) {
            $timeline[] = [
                'type'       => 'status',
                'text'       => ucwords(str_replace('_', ' ', $h['to_status'] ?? '')),
                'note'       => $h['notes'],
                'actor'      => $h['changed_by_name'] ?? 'System',
                'created_at' => $h['created_at'],
            ];
        }
        foreach ($updates as $u) {
            $timeline[] = [
                'type'            => 'update',
                'text'            => $u['message'],
                'action_code'     => $u['action_code'],
                'support_type'    => $u['support_type'] ?? null,
                'support_requested' => !empty($u['support_requested']),
                'actor'           => $u['responder_name'] ?? $u['team_name'] ?? '',
                'created_at'      => $u['created_at'],
            ];
        }
        usort($timeline, fn($a, $b) => strcmp($a['created_at'], $b['created_at']));

        $this->json([
            'assignment_id'     => $row['assignment_id'],
            'assignment_status' => $row['assignment_status'],
            'assigned_at'       => $row['assigned_at'],
            'acknowledged_at'   => $row['acknowledged_at'],
            'team_name'         => $row['team_name'],
            'incident'          => [
                'code'           => $row['code'],
                'hazard_type'    => $row['hazard_type'],
                'triage_level'   => $row['triage_level'],
                'triage_score'   => (int) $row['triage_score'],
                'status'         => $row['incident_status'],
                'address'        => $row['address'],
                'location_lat'   => $row['location_lat'],
                'location_lng'   => $row['location_lng'],
                'description'    => $row['description'],
                'reporter_name'  => $row['reporter_name'],
                'reporter_phone' => $row['reporter_phone'],
            ],
            'timeline' => $timeline,
        ]);
    }
}
