<?php

namespace App\Models;

use App\Core\Model;

class SystemSetting extends Model
{
    protected string $table = 'system_settings';

    public function get(string $key, $default = null): mixed
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT setting_value FROM {$this->table} WHERE setting_key = :key LIMIT 1");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }

    public function set(string $key, string $value): bool
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (setting_key, setting_value) 
             VALUES (:key, :value) 
             ON DUPLICATE KEY UPDATE setting_value = :value"
        );
        return $stmt->execute(['key' => $key, 'value' => $value]);
    }

    public function all(): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[$row['setting_key']] = $row['setting_value'];
        }
        return $results;
    }
}
