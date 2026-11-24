<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';

    public function createForUsers(array $userIds, string $title, ?string $message = null, ?string $link = null): void
    {
        $this->assertValidIdentifier($this->table);
        if (empty($userIds)) {
            return;
        }
        $sql = "INSERT INTO {$this->table} (user_id, title, message, link) VALUES (:user_id, :title, :message, :link)";
        $stmt = $this->db->prepare($sql);
        foreach ($userIds as $uid) {
            $stmt->execute([
                'user_id' => (int) $uid,
                'title'   => $title,
                'message' => $message,
                'link'    => $link,
            ]);
        }
    }

    public function create(int $userId, string $title, ?string $message = null, ?string $link = null): void
    {
        $this->createForUsers([$userId], $title, $message, $link);
    }

    public function unreadCount(int $userId): int
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function forUser(int $userId, int $limit = 50): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "SELECT id, title, message, link, is_read, created_at
             FROM {$this->table}
             WHERE user_id = :uid
             ORDER BY created_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markRead(int $id, int $userId): void
    {
        $this->assertValidIdentifier($this->table);
        $this->db->prepare(
            "UPDATE {$this->table} SET is_read = 1 WHERE id = :id AND user_id = :uid"
        )->execute(['id' => $id, 'uid' => $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $this->assertValidIdentifier($this->table);
        $this->db->prepare(
            "UPDATE {$this->table} SET is_read = 1 WHERE user_id = :uid AND is_read = 0"
        )->execute(['uid' => $userId]);
    }
}
