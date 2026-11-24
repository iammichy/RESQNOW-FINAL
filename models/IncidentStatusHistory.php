<?php

namespace App\Models;

use App\Core\Model;

class IncidentStatusHistory extends Model
{
    protected string $table = 'incident_status_history';

    public function record(int $incidentId, ?string $fromStatus, string $toStatus, ?int $changedBy, ?string $notes = null): void
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (incident_id, from_status, to_status, changed_by, notes)
             VALUES (:incident_id, :from_status, :to_status, :changed_by, :notes)"
        );
        $stmt->execute([
            'incident_id' => $incidentId,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'changed_by'  => $changedBy,
            'notes'       => $notes,
        ]);
    }

    public function forIncident(int $incidentId): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "SELECT h.*, u.name AS changed_by_name
             FROM {$this->table} h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.incident_id = :incident_id
             ORDER BY h.created_at ASC, h.id ASC"
        );
        $stmt->execute(['incident_id' => $incidentId]);
        return $stmt->fetchAll();
    }
}
