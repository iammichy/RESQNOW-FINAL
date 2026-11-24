<?php

namespace App\Models;

use App\Core\Model;

class Incident extends Model
{
    protected string $table = 'incidents';

    public function latest(int $limit = 5): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByCode(string $code): ?array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        return $stmt->fetch() ?: null;
    }

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

    public function paginated(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $this->assertValidIdentifier($this->table);
        $where = [];
        $params = [];

        if (!empty($filters['hazard_type'])) {
            $where[] = "i.hazard_type = :hazard_type";
            $params['hazard_type'] = $filters['hazard_type'];
        }
        if (!empty($filters['status'])) {
            $where[] = "i.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['triage_level'])) {
            $where[] = "i.triage_level = :triage_level";
            $params['triage_level'] = $filters['triage_level'];
        }
        if (isset($filters['is_emergency']) && $filters['is_emergency'] !== '') {
            $where[] = "i.is_emergency = :is_emergency";
            $params['is_emergency'] = (int) $filters['is_emergency'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "i.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = "i.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $where[] = "(i.code LIKE :search OR i.reported_by IN (SELECT id FROM users WHERE name LIKE :search_name))";
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search_name'] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(*) FROM {$this->table} i {$whereSql}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT i.*, u.name AS reporter_name
                FROM {$this->table} i
                LEFT JOIN users u ON u.id = i.reported_by
                {$whereSql}
                ORDER BY i.created_at DESC, FIELD(i.triage_level, 'critical','high','moderate','low','pending_reassessment')
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'        => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Verification queue: submitted incidents needing review.
     * Sort: pending_reassessment FIRST, then triage severity desc, then oldest first.
     */
    public function verificationQueue(): array
    {
        $this->assertValidIdentifier($this->table);
        $sql = "SELECT i.*, u.name AS reporter_name, u.phone AS reporter_phone
                FROM {$this->table} i
                LEFT JOIN users u ON u.id = i.reported_by
                WHERE i.status = 'submitted'
                ORDER BY
                    CASE WHEN i.triage_level = 'pending_reassessment' THEN 0 ELSE 1 END,
                    FIELD(i.triage_level, 'critical','high','moderate','low','pending_reassessment'),
                    i.created_at ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Paginated verification queue with optional triage/hazard filter.
     */
    public function verificationQueuePaginated(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $this->assertValidIdentifier($this->table);
        $where  = ["i.status = 'submitted'"];
        $params = [];

        if (!empty($filters['triage_level'])) {
            $where[]               = 'i.triage_level = :triage_level';
            $params['triage_level'] = $filters['triage_level'];
        }
        if (!empty($filters['hazard_type'])) {
            $where[]              = 'i.hazard_type = :hazard_type';
            $params['hazard_type'] = $filters['hazard_type'];
        }
        if (!empty($filters['search'])) {
            $where[]          = '(i.code LIKE :search OR u.name LIKE :search_name)';
            $params['search']      = '%' . $filters['search'] . '%';
            $params['search_name'] = '%' . $filters['search'] . '%';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} i
             LEFT JOIN users u ON u.id = i.reported_by
             {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);

        $dataStmt = $this->db->prepare(
            "SELECT i.*, u.name AS reporter_name, u.phone AS reporter_phone
             FROM {$this->table} i
             LEFT JOIN users u ON u.id = i.reported_by
             {$whereSql}
             ORDER BY
                 CASE WHEN i.triage_level = 'pending_reassessment' THEN 0 ELSE 1 END,
                 FIELD(i.triage_level,'critical','high','moderate','low','pending_reassessment'),
                 i.created_at ASC
             LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $dataStmt->bindValue($k, $v);
        }
        $dataStmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
        $dataStmt->bindValue(':off', $offset,  \PDO::PARAM_INT);
        $dataStmt->execute();

        return [
            'data'        => $dataStmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function pendingVerificationCount(): int
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = 'submitted'");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Change status + write a status_history row.
     * Note: Does NOT manage its own transaction — call within an existing
     * transaction (controller actions wrap verify/reassess/etc. in one).
     */
    public function changeStatus(int $incidentId, string $fromStatus, string $toStatus, ?int $changedBy, ?string $notes = null): void
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $toStatus, 'id' => $incidentId]);

        $hist = new IncidentStatusHistory($this->db);
        $hist->record($incidentId, $fromStatus, $toStatus, $changedBy, $notes);
    }

    public function updateTriageLevel(int $incidentId, string $newLevel, int $score = 0): void
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET triage_level = :level, triage_score = :score WHERE id = :id");
        $stmt->execute(['level' => $newLevel, 'score' => $score, 'id' => $incidentId]);
    }

    public function setEscalated(int $incidentId, bool $escalated): void
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_escalated = :flag WHERE id = :id");
        $stmt->execute(['flag' => $escalated ? 1 : 0, 'id' => $incidentId]);
    }

    public function setDuplicateOf(int $incidentId, string $originalCode): void
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 'duplicate', assigned_team = :original WHERE id = :id"
        );
        $stmt->execute(['original' => 'Duplicate of ' . $originalCode, 'id' => $incidentId]);
    }

    /**
     * Prioritization queue: verified/triaged incidents awaiting team assignment.
     * Excludes rejected triage level. Sorts by severity (critical first),
     * then oldest first within each tier.
     */
    public function prioritizationQueue(): array
    {
        $this->assertValidIdentifier($this->table);
        $sql = "SELECT i.*, u.name AS reporter_name, u.phone AS reporter_phone
                FROM {$this->table} i
                LEFT JOIN users u ON u.id = i.reported_by
                WHERE i.status IN ('verified', 'triaged')
                  AND i.triage_level != 'rejected'
                ORDER BY
                    FIELD(i.triage_level, 'critical', 'high', 'moderate', 'low', 'pending_reassessment'),
                    i.created_at ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Count verified/triaged incidents awaiting assignment, grouped by triage_level.
     */
    public function prioritizationCounts(): array
    {
        $this->assertValidIdentifier($this->table);
        $stmt = $this->db->query(
            "SELECT triage_level, COUNT(*) AS cnt
             FROM {$this->table}
             WHERE status IN ('verified', 'triaged')
               AND triage_level != 'rejected'
             GROUP BY triage_level"
        );
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['triage_level']] = (int) $row['cnt'];
        }
        return $out;
    }
}
