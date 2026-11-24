<?php

namespace App\Models;

use App\Core\Model;

class Assignment extends Model
{
    protected string $table = 'assignments';

    public function create(array $data): int
    {
        $cols = array_keys($data);
        foreach ($cols as $c) {
            $this->assertValidIdentifier($c);
        }
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO assignments (" . implode(',', $cols) . ")
                VALUES (" . implode(',', $placeholders) . ")";
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function activeForIncident(int $incidentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, t.team_name, t.unit_type, t.contact_no,
                    u.name AS assigned_by_name
             FROM assignments a
             JOIN response_teams t ON t.id = a.team_id
             LEFT JOIN users u ON u.id = a.assigned_by
             WHERE a.incident_id = :incident_id AND a.status = 'active'
             ORDER BY a.assigned_at DESC LIMIT 1"
        );
        $stmt->execute(['incident_id' => $incidentId]);
        return $stmt->fetch() ?: null;
    }

    public function allForIncident(int $incidentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, t.team_name, t.unit_type,
                    u.name AS assigned_by_name
             FROM assignments a
             JOIN response_teams t ON t.id = a.team_id
             LEFT JOIN users u ON u.id = a.assigned_by
             WHERE a.incident_id = :incident_id
             ORDER BY a.assigned_at ASC"
        );
        $stmt->execute(['incident_id' => $incidentId]);
        return $stmt->fetchAll();
    }

    public function missionsForTeam(int $teamId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id AS assignment_id, a.*, i.code, i.hazard_type, i.triage_level, i.triage_score,
                    i.address, i.location_lat, i.location_lng, i.status AS incident_status,
                    i.description,
                    (SELECT COUNT(*) FROM response_updates ru
                     WHERE ru.incident_id = a.incident_id
                       AND ru.support_requested = 1
                       AND ru.support_acknowledged_at IS NULL) AS open_support
             FROM assignments a
             JOIN incidents i ON i.id = a.incident_id
             WHERE a.team_id = :team_id AND a.status = 'active'
             ORDER BY FIELD(i.triage_level,'critical','high','moderate','low','pending_reassessment'), a.assigned_at DESC"
        );
        $stmt->execute(['team_id' => $teamId]);
        return $stmt->fetchAll();
    }

    public function closeActive(int $incidentId, string $newStatus = 'reassigned'): void
    {
        $this->db->prepare(
            "UPDATE assignments SET status = :s WHERE incident_id = :iid AND status = 'active'"
        )->execute(['s' => $newStatus, 'iid' => $incidentId]);
    }

    public function setAcknowledged(int $id): void
    {
        $this->db->prepare(
            "UPDATE assignments SET acknowledged_at = NOW() WHERE id = :id AND acknowledged_at IS NULL"
        )->execute(['id' => $id]);
    }

    public function setResolved(int $id): void
    {
        $this->db->prepare(
            "UPDATE assignments SET resolved_at = NOW(), status = 'completed' WHERE id = :id"
        )->execute(['id' => $id]);
    }

    /** All active assignments for the monitoring board */
    public function activeBoard(): array
    {
        $sql = "SELECT a.*,
                    i.code, i.hazard_type, i.triage_level, i.triage_score,
                    i.address, i.status AS incident_status, i.is_escalated,
                    i.location_lat, i.location_lng,
                    t.team_name, t.unit_type,
                    ru.message AS last_update_message,
                    ru.action_code AS last_update_action,
                    ru.created_at AS last_update_at,
                    ru.support_requested AS last_support_requested,
                    (SELECT COUNT(*) FROM response_updates rx
                     WHERE rx.incident_id = a.incident_id AND rx.support_requested = 1
                       AND rx.support_acknowledged_at IS NULL) AS open_support_count
                FROM assignments a
                JOIN incidents i ON i.id = a.incident_id
                JOIN response_teams t ON t.id = a.team_id
                LEFT JOIN response_updates ru ON ru.id = (
                    SELECT id FROM response_updates
                    WHERE incident_id = a.incident_id
                    ORDER BY created_at DESC LIMIT 1
                )
                WHERE a.status = 'active'
                ORDER BY
                    (SELECT COUNT(*) FROM response_updates rx2
                     WHERE rx2.incident_id = a.incident_id AND rx2.support_requested = 1
                       AND rx2.support_acknowledged_at IS NULL) DESC,
                    FIELD(i.triage_level,'critical','high','moderate','low','pending_reassessment'),
                    a.assigned_at ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Today's resolved for the board */
    public function resolvedToday(): array
    {
        $sql = "SELECT a.*,
                    i.code, i.hazard_type, i.triage_level, i.status AS incident_status,
                    i.address,
                    t.team_name, t.unit_type
                FROM assignments a
                JOIN incidents i ON i.id = a.incident_id
                JOIN response_teams t ON t.id = a.team_id
                WHERE a.status = 'completed' AND DATE(a.resolved_at) = CURDATE()
                ORDER BY a.resolved_at DESC";
        return $this->db->query($sql)->fetchAll();
    }
}
