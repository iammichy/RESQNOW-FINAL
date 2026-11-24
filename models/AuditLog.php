<?php

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    public function write(
        ?int    $userId,
        string  $action,
        ?string $entity   = null,
        ?int    $entityId = null,
        ?string $details  = null
    ): void {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (user_id, action, entity, entity_id, details, ip_address)
             VALUES (:user_id, :action, :entity, :entity_id, :details, :ip_address)"
        );
        $stmt->execute([
            'user_id'    => $userId,
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'details'    => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function paginated(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $this->assertValidIdentifier($this->table);
        $where  = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[]          = 'al.user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $where[]         = 'al.action LIKE :action';
            $params['action'] = '%' . $filters['action'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[]            = 'al.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[]          = 'al.created_at <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} al {$whereSql}"
        )->execute($params) ? $this->db->query(
            "SELECT COUNT(*) FROM {$this->table} al {$whereSql}"
        )->fetchColumn() : 0;

        // Re-run count properly
        $cStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} al {$whereSql}");
        $cStmt->execute($params);
        $total = (int) $cStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->db->prepare(
            "SELECT al.*, u.name AS user_name
             FROM {$this->table} al
             LEFT JOIN users u ON u.id = al.user_id
             {$whereSql}
             ORDER BY al.created_at DESC
             LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / max(1, $perPage)),
        ];
    }
}
