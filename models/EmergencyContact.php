<?php

namespace App\Models;

use App\Core\Model;

class EmergencyContact extends Model
{
    protected string $table = 'emergency_contacts';

    public function getActive(): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
