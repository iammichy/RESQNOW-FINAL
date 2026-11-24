<?php

namespace App\Services;

use PDO;

/**
 * AnalyticsService — all analytics queries flow through here.
 *
 * $filters keys (all optional):
 *   date_from     string  'Y-m-d'  (default: 30 days ago)
 *   date_to       string  'Y-m-d'  (default: today)
 *   hazard_type   string
 *   status        string
 *   triage_level  string
 *   barangay_id   int
 */
class AnalyticsService
{
    private PDO $db;

    // Valid enum values — used to whitelist filter inputs
    private const VALID_HAZARDS = [
        'fire', 'flood', 'earthquake', 'medical',
        'accident', 'evacuation', 'security', 'other',
    ];
    private const VALID_STATUSES = [
        'submitted', 'verified', 'rejected', 'duplicate',
        'triaged', 'assigned', 'acknowledged', 'on_scene',
        'in_progress', 'resolved', 'closed',
    ];
    private const VALID_TRIAGE = [
        'critical', 'high', 'moderate', 'low', 'pending_reassessment',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ──────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ──────────────────────────────────────────────────────────────────

    /**
     * KPI summary card values.
     * SQL: COUNT/AVG aggregates on incidents + assignments.
     */
    public function kpiSummary(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');

        // total_reports in range
        $total = $this->scalar(
            "SELECT COUNT(*) FROM incidents i {$where}",
            $params
        );

        // submitted_today (ignores date_from/to filter — always today)
        $today = $this->scalar(
            "SELECT COUNT(*) FROM incidents i WHERE DATE(i.created_at) = CURDATE()"
        );

        // pending_verification
        $pending = $this->scalar(
            "SELECT COUNT(*) FROM incidents i WHERE i.status = 'submitted'"
        );

        // active_missions
        $active = $this->scalar(
            "SELECT COUNT(*) FROM incidents i
             WHERE i.status IN ('assigned','acknowledged','on_scene','in_progress')"
        );

        // resolved_today
        $resolvedToday = $this->scalar(
            "SELECT COUNT(*) FROM incidents i
             WHERE i.status = 'resolved' AND DATE(i.resolved_at) = CURDATE()"
        );

        // resolved_total in range
        [$whereR, $paramsR] = $this->buildWhere(array_merge($filters, ['status' => 'resolved']), 'i');
        $resolvedTotal = $this->scalar(
            "SELECT COUNT(*) FROM incidents i {$whereR}",
            $paramsR
        );

        // avg_acknowledge_minutes: assigned_at → acknowledged_at
        $avgAck = $this->scalarFloat(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, a.assigned_at, a.acknowledged_at))
             FROM assignments a
             WHERE a.acknowledged_at IS NOT NULL
               AND a.assigned_at >= :dfrom AND a.assigned_at <= :dto",
            [
                'dfrom' => ($filters['date_from'] ?? date('Y-m-d', strtotime('-30 days'))) . ' 00:00:00',
                'dto'   => ($filters['date_to']   ?? date('Y-m-d')) . ' 23:59:59',
            ]
        );

        // avg_resolve_minutes: assigned_at → resolved_at
        $avgResolve = $this->scalarFloat(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, a.assigned_at, a.resolved_at))
             FROM assignments a
             WHERE a.resolved_at IS NOT NULL
               AND a.assigned_at >= :dfrom AND a.assigned_at <= :dto",
            [
                'dfrom' => ($filters['date_from'] ?? date('Y-m-d', strtotime('-30 days'))) . ' 00:00:00',
                'dto'   => ($filters['date_to']   ?? date('Y-m-d')) . ' 23:59:59',
            ]
        );

        // previous-period delta for "Reports Today" vs yesterday
        $yesterday = $this->scalar(
            "SELECT COUNT(*) FROM incidents i WHERE DATE(i.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)"
        );

        return [
            'total_reports'          => (int) $total,
            'submitted_today'        => (int) $today,
            'yesterday_count'        => (int) $yesterday,
            'today_delta'            => (int) $today - (int) $yesterday,
            'pending_verification'   => (int) $pending,
            'active_missions'        => (int) $active,
            'resolved_today'         => (int) $resolvedToday,
            'resolved_total'         => (int) $resolvedTotal,
            'avg_acknowledge_minutes'=> $avgAck !== null ? (int) round($avgAck) : null,
            'avg_resolve_minutes'    => $avgResolve !== null ? (int) round($avgResolve) : null,
        ];
    }

    /**
     * Daily incident counts grouped by DATE(created_at).
     * SQL: GROUP BY DATE(i.created_at)
     */
    public function dailyCounts(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');
        $stmt = $this->db->prepare(
            "SELECT DATE(i.created_at) AS date, COUNT(*) AS count
             FROM incidents i
             {$where}
             GROUP BY DATE(i.created_at)
             ORDER BY date ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Counts grouped by hazard_type.
     * SQL: GROUP BY i.hazard_type
     */
    public function byHazard(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');
        $stmt = $this->db->prepare(
            "SELECT i.hazard_type AS label, COUNT(*) AS count
             FROM incidents i
             {$where}
             GROUP BY i.hazard_type
             ORDER BY count DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Counts grouped by triage_level.
     * SQL: GROUP BY i.triage_level
     */
    public function byTriageLevel(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');
        $stmt = $this->db->prepare(
            "SELECT i.triage_level AS label, COUNT(*) AS count
             FROM incidents i
             {$where}
             GROUP BY i.triage_level
             ORDER BY FIELD(i.triage_level,'critical','high','moderate','low','pending_reassessment')"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Counts grouped by status.
     * SQL: GROUP BY i.status
     */
    public function byStatus(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');
        $stmt = $this->db->prepare(
            "SELECT i.status AS label, COUNT(*) AS count
             FROM incidents i
             {$where}
             GROUP BY i.status
             ORDER BY count DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Top areas by incident count.
     * SQL: LEFT JOIN barangays, GROUP BY b.name / i.address substring
     */
    public function topAreas(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');

        // By barangay (named)
        $stmt = $this->db->prepare(
            "SELECT COALESCE(b.name, u.barangay, 'Unknown') AS area,
                    COUNT(*) AS count
             FROM incidents i
             LEFT JOIN barangays b ON b.id = i.barangay_id
             LEFT JOIN users u ON u.id = i.reported_by
             {$where}
             GROUP BY area
             ORDER BY count DESC
             LIMIT 10"
        );
        $stmt->execute($params);
        $byBarangay = $stmt->fetchAll();

        // Top streets: first ~30 chars of address as zone grouping
        $stmt2 = $this->db->prepare(
            "SELECT TRIM(SUBSTRING_INDEX(i.address, ',', 1)) AS street,
                    COUNT(*) AS count
             FROM incidents i
             {$where}
             AND i.address IS NOT NULL AND i.address != ''
             GROUP BY street
             ORDER BY count DESC
             LIMIT 10"
        );
        $stmt2->execute($params);
        $byStreet = $stmt2->fetchAll();

        return [
            'by_barangay' => $byBarangay,
            'by_street'   => $byStreet,
        ];
    }

    /**
     * Daily case table: per-day submitted / resolved / still active.
     * SQL: aggregated subqueries per date
     */
    public function dailyTable(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');

        $stmt = $this->db->prepare(
            "SELECT
                DATE(i.created_at)                                       AS date,
                COUNT(*)                                                 AS submitted,
                SUM(CASE WHEN i.status = 'resolved' THEN 1 ELSE 0 END)  AS resolved,
                SUM(CASE WHEN i.status IN ('assigned','acknowledged','on_scene','in_progress')
                         THEN 1 ELSE 0 END)                              AS active
             FROM incidents i
             {$where}
             GROUP BY DATE(i.created_at)
             ORDER BY date DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Per-hazard summary with triage breakdown + avg resolve time.
     * SQL: GROUP BY hazard_type with CASE SUM breakdowns
     */
    public function hazardSummary(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');

        $stmt = $this->db->prepare(
            "SELECT
                i.hazard_type,
                COUNT(*)                                                              AS total,
                SUM(CASE WHEN i.triage_level = 'critical' THEN 1 ELSE 0 END)         AS critical_count,
                SUM(CASE WHEN i.triage_level = 'high'     THEN 1 ELSE 0 END)         AS high_count,
                SUM(CASE WHEN i.triage_level = 'moderate' THEN 1 ELSE 0 END)         AS moderate_count,
                SUM(CASE WHEN i.triage_level = 'low'      THEN 1 ELSE 0 END)         AS low_count,
                SUM(CASE WHEN i.status = 'resolved'       THEN 1 ELSE 0 END)         AS resolved_count,
                ROUND(AVG(CASE WHEN i.resolved_at IS NOT NULL AND i.assigned_at IS NOT NULL
                               THEN TIMESTAMPDIFF(MINUTE, i.assigned_at, i.resolved_at)
                          END))                                                       AS avg_resolve_minutes
             FROM incidents i
             {$where}
             GROUP BY i.hazard_type
             ORDER BY total DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Full row set for CSV export.
     * SQL: LEFT JOIN users, barangays, assignments
     */
    public function exportRows(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters, 'i');

        $stmt = $this->db->prepare(
            "SELECT
                i.code                   AS report_code,
                i.created_at,
                i.hazard_type,
                CASE WHEN i.is_emergency = 1 THEN 'Yes' ELSE 'No' END AS is_emergency,
                i.triage_level,
                i.triage_score,
                i.status,
                COALESCE(b.name, u.barangay, '') AS barangay,
                COALESCE(i.address, '')           AS incident_address,
                COALESCE(i.assigned_team, '')     AS assigned_team,
                (SELECT MIN(h.created_at)
                 FROM incident_status_history h
                 WHERE h.incident_id = i.id AND h.to_status = 'verified')  AS verified_at,
                i.assigned_at,
                i.resolved_at,
                COALESCE(u.name, '')              AS submitter,
                COALESCE(u.phone, '')             AS submitter_phone
             FROM incidents i
             LEFT JOIN users u     ON u.id = i.reported_by
             LEFT JOIN barangays b ON b.id = i.barangay_id
             {$where}
             ORDER BY i.created_at DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Return list of barangays for the filter dropdown.
     */
    public function allBarangays(): array
    {
        return $this->db->query(
            "SELECT id, name FROM barangays ORDER BY name"
        )->fetchAll();
    }

    // ──────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────

    /**
     * Build a safe WHERE clause from $filters.
     * $alias = table alias for incidents (default 'i').
     * Returns [string $whereClause, array $params].
     */
    private function buildWhere(array $filters, string $alias = 'i'): array
    {
        $dateFrom = $this->safeDate($filters['date_from'] ?? null)
                    ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo   = $this->safeDate($filters['date_to'] ?? null)
                    ?? date('Y-m-d');

        $conditions = [
            "{$alias}.created_at >= :date_from",
            "{$alias}.created_at <= :date_to",
        ];
        $params = [
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to'   => $dateTo   . ' 23:59:59',
        ];

        // hazard_type — whitelist
        if (!empty($filters['hazard_type'])
            && in_array($filters['hazard_type'], self::VALID_HAZARDS, true)) {
            $conditions[] = "{$alias}.hazard_type = :hazard_type";
            $params['hazard_type'] = $filters['hazard_type'];
        }

        // status — whitelist
        if (!empty($filters['status'])
            && in_array($filters['status'], self::VALID_STATUSES, true)) {
            $conditions[] = "{$alias}.status = :status";
            $params['status'] = $filters['status'];
        }

        // triage_level — whitelist
        if (!empty($filters['triage_level'])
            && in_array($filters['triage_level'], self::VALID_TRIAGE, true)) {
            $conditions[] = "{$alias}.triage_level = :triage_level";
            $params['triage_level'] = $filters['triage_level'];
        }

        // barangay_id — must be a positive integer
        if (!empty($filters['barangay_id']) && (int) $filters['barangay_id'] > 0) {
            $conditions[] = "{$alias}.barangay_id = :barangay_id";
            $params['barangay_id'] = (int) $filters['barangay_id'];
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }

    /**
     * Validate and return a Y-m-d date string, or null if invalid.
     */
    private function safeDate(?string $raw): ?string
    {
        if (!$raw) return null;
        $d = \DateTime::createFromFormat('Y-m-d', trim($raw));
        return ($d && $d->format('Y-m-d') === trim($raw)) ? trim($raw) : null;
    }

    private function scalar(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function scalarFloat(string $sql, array $params = []): ?float
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $val = $stmt->fetchColumn();
            return $val !== false && $val !== null ? (float) $val : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
