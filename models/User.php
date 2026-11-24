<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmailOrPhone(string $identifier): ?array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE email = :email OR phone = :phone LIMIT 1"
        );
        $stmt->execute(['email' => $identifier, 'phone' => $identifier]);
        return $stmt->fetch() ?: null;
    }

    public function findByApiToken(string $token): ?array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE api_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function allByRole(array $roles): array
    {
        $this->assertValidIdentifier($this->table);
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role IN ({$placeholders}) ORDER BY created_at DESC");
        $stmt->execute($roles);
        return $stmt->fetchAll();
    }

    public function getResidentsByStatus(string $status): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = 'resident' AND status = :status ORDER BY created_at DESC");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public function countPendingResidents(): int
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE role = 'resident' AND status = 'pending'");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function suspend(int $id): bool
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'suspended', suspended_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function activate(int $id): bool
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'approved', suspended_at = NULL WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
