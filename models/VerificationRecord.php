<?php

namespace App\Models;

use App\Core\Model;

class VerificationRecord extends Model
{
    protected string $table = 'verification_records';

    public function create(array $data): int
    {
        $this->assertValidIdentifier($this->table);
        $cols = array_keys($data);
        foreach ($cols as $c) {
            $this->assertValidIdentifier($c);
        }
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function forIncident(int $incidentId): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "SELECT v.*, u.name AS verified_by_name
             FROM {$this->table} v
             LEFT JOIN users u ON u.id = v.verified_by
             WHERE v.incident_id = :incident_id
             ORDER BY v.created_at ASC, v.id ASC"
        );
        $stmt->execute(['incident_id' => $incidentId]);
        return $stmt->fetchAll();
    }
}
