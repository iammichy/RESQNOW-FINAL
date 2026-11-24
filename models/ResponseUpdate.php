<?php

namespace App\Models;

use App\Core\Model;

class ResponseUpdate extends Model
{
    protected string $table = 'response_updates';

    public function create(array $data): int
    {
        $cols = array_keys($data);
        foreach ($cols as $c) {
            $this->assertValidIdentifier($c);
        }
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO response_updates (" . implode(',', $cols) . ")
                VALUES (" . implode(',', $placeholders) . ")";
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function forIncident(int $incidentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ru.*,
                    u.name AS responder_name, u.role AS responder_role,
                    t.team_name,
                    su.name AS support_ack_by_name
             FROM response_updates ru
             LEFT JOIN users u ON u.id = ru.responder_id
             LEFT JOIN response_teams t ON t.id = ru.team_id
             LEFT JOIN users su ON su.id = ru.support_acknowledged_by
             WHERE ru.incident_id = :incident_id
             ORDER BY ru.created_at ASC"
        );
        $stmt->execute(['incident_id' => $incidentId]);
        return $stmt->fetchAll();
    }

    public function pendingSupportRequests(): array
    {
        $sql = "SELECT ru.*,
                    u.name AS responder_name,
                    t.team_name,
                    i.code AS incident_code,
                    i.hazard_type,
                    i.triage_level
                FROM response_updates ru
                JOIN incidents i ON i.id = ru.incident_id
                LEFT JOIN users u ON u.id = ru.responder_id
                LEFT JOIN response_teams t ON t.id = ru.team_id
                WHERE ru.support_requested = 1
                  AND ru.support_acknowledged_at IS NULL
                ORDER BY ru.created_at ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function acknowledgeSupportRequest(int $id, int $byUserId): void
    {
        $this->db->prepare(
            "UPDATE response_updates
             SET support_acknowledged_by = :uid, support_acknowledged_at = NOW()
             WHERE id = :id"
        )->execute(['uid' => $byUserId, 'id' => $id]);
    }

    public function countPendingSupport(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM response_updates
             WHERE support_requested = 1 AND support_acknowledged_at IS NULL"
        )->fetchColumn();
    }
}
