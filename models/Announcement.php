<?php

namespace App\Models;

use App\Core\Model;

class Announcement extends Model
{
    protected string $table = 'announcements';

    public function getActive(int $barangayId = null): array
    {
        $this->assertValidIdentifier($this->table);
        $sql = "SELECT * FROM {$this->table} 
                WHERE (active_from IS NULL OR active_from <= NOW()) 
                AND (active_to IS NULL OR active_to >= NOW())";
        
        $params = [];
        if ($barangayId) {
            $sql .= " AND (barangay_id = :barangay_id OR barangay_id IS NULL)";
            $params['barangay_id'] = $barangayId;
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
