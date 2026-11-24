<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Incident;
use App\Models\IncidentAnswer;
use App\Models\IncidentStatusHistory;
use App\Models\VerificationRecord;
use App\Models\ResponseUpdate;
use App\Models\Assignment;

class ResidentApiController extends Controller
{
    /** GET /api/v1/resident/incidents — list my reports */
    public function incidents(): void
    {
        $user = $this->requireApiAuth('resident');

        $stmt = $this->db->prepare(
            "SELECT id, code, hazard_type, triage_level, triage_score,
                    status, address, assigned_team, created_at, resolved_at
             FROM incidents
             WHERE reported_by = :uid
             ORDER BY created_at DESC
             LIMIT 50"
        );
        $stmt->execute(['uid' => $user['id']]);
        $this->json(['incidents' => $stmt->fetchAll()]);
    }

    /** GET /api/v1/resident/incidents/{code} — detail + timeline */
    public function incidentDetail(string $code): void
    {
        $user = $this->requireApiAuth('resident');

        $model = new Incident($this->db);
        $incident = $model->findByCode($code);

        if (!$incident) {
            $this->json(['error' => 'Not Found'], 404);
        }
        if ((int) $incident['reported_by'] !== (int) $user['id']) {
            $this->json(['error' => 'Forbidden'], 403);
        }

        $answers = (new IncidentAnswer($this->db))->forIncident((int) $incident['id']);
        $history = (new IncidentStatusHistory($this->db))->forIncident((int) $incident['id']);
        $verif   = (new VerificationRecord($this->db))->forIncident((int) $incident['id']);
        $updates = (new ResponseUpdate($this->db))->forIncident((int) $incident['id']);
        $assignment = (new Assignment($this->db))->activeForIncident((int) $incident['id']);

        // Build merged timeline
        $timeline = [];
        foreach ($history as $h) {
            $timeline[] = [
                'type'       => 'status',
                'icon'       => '🔄',
                'text'       => ucwords(str_replace('_', ' ', $h['to_status'] ?? '')),
                'note'       => $h['notes'],
                'actor'      => $h['changed_by_name'] ?? 'System',
                'created_at' => $h['created_at'],
            ];
        }
        foreach ($updates as $u) {
            $timeline[] = [
                'type'       => 'update',
                'icon'       => $u['update_type'] === 'support_request' ? '🆘' : ($u['update_type'] === 'resolved' ? '✅' : '💬'),
                'text'       => $u['message'],
                'note'       => null,
                'actor'      => $u['team_name'] ?? $u['responder_name'] ?? 'Team',
                'created_at' => $u['created_at'],
            ];
        }
        usort($timeline, fn($a, $b) => strcmp($a['created_at'], $b['created_at']));

        $rejectionReason = null;
        foreach (array_reverse($verif) as $v) {
            if ($v['action'] === 'rejected') { $rejectionReason = $v['reason']; break; }
        }

        $this->json([
            'report_code'      => $incident['code'],
            'hazard_type'      => $incident['hazard_type'],
            'status'           => $incident['status'],
            'triage_level'     => $incident['triage_level'],
            'triage_score'     => (int) $incident['triage_score'],
            'is_escalated'     => !empty($incident['is_escalated']),
            'address'          => $incident['address'],
            'location_lat'     => $incident['location_lat'],
            'location_lng'     => $incident['location_lng'],
            'description'      => $incident['description'],
            'assigned_team'    => $incident['assigned_team'],
            'assigned_at'      => $incident['assigned_at'],
            'resolved_at'      => $incident['resolved_at'],
            'rejection_reason' => $rejectionReason,
            'assignment'       => $assignment ? [
                'team_name'       => $assignment['team_name'],
                'acknowledged_at' => $assignment['acknowledged_at'],
            ] : null,
            'timeline'  => $timeline,
            'created_at'=> $incident['created_at'],
        ]);
    }
}
