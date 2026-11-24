<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    private AnalyticsService $svc;

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->svc = new AnalyticsService($db);
    }

    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $filters = $this->parseFilters();

        $kpis        = $this->svc->kpiSummary($filters);
        $daily       = $this->fillDateRange($this->svc->dailyCounts($filters), $filters);
        $byHazard    = $this->svc->byHazard($filters);
        $byTriage    = $this->svc->byTriageLevel($filters);
        $byStatus    = $this->svc->byStatus($filters);
        $topAreas    = $this->svc->topAreas($filters);
        $dailyTable  = $this->fillDateRangeTable($this->svc->dailyTable($filters), $filters);
        $hazardSummary = $this->svc->hazardSummary($filters);
        $barangays   = $this->svc->allBarangays();

        // All-time summary (not filtered) for the Overall Summary banner
        $atRow = $this->db->query(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) AS resolved,
                    SUM(CASE WHEN status IN ('assigned','acknowledged','on_scene','in_progress') THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status='submitted' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN triage_level='critical' THEN 1 ELSE 0 END) AS critical,
                    SUM(CASE WHEN is_emergency=1 THEN 1 ELSE 0 END) AS emergency
             FROM incidents"
        )->fetch();
        $topHazardRow = $this->db->query(
            "SELECT hazard_type, COUNT(*) AS cnt FROM incidents GROUP BY hazard_type ORDER BY cnt DESC LIMIT 1"
        )->fetch();
        $topAreaRow = $this->db->query(
            "SELECT TRIM(SUBSTRING_INDEX(address,',',1)) AS area, COUNT(*) AS cnt
             FROM incidents WHERE address IS NOT NULL AND address != ''
             GROUP BY area ORDER BY cnt DESC LIMIT 1"
        )->fetch();
        $overallSummary = [
            'total'      => (int)($atRow['total']    ?? 0),
            'resolved'   => (int)($atRow['resolved'] ?? 0),
            'active'     => (int)($atRow['active']   ?? 0),
            'pending'    => (int)($atRow['pending']  ?? 0),
            'critical'   => (int)($atRow['critical'] ?? 0),
            'emergency'  => (int)($atRow['emergency']?? 0),
            'top_hazard' => $topHazardRow ?: null,
            'top_area'   => $topAreaRow   ?: null,
        ];

        // Pre-encode chart data for inline JSON — done in controller so view
        // stays logic-free and the SQL method is traceable via comments.
        $chartDaily = [
            'dates'  => array_column($daily, 'date'),
            'counts' => array_map('intval', array_column($daily, 'count')),
        ];
        $chartHazard = [
            'labels' => array_column($byHazard, 'label'),
            'counts' => array_map('intval', array_column($byHazard, 'count')),
        ];
        $chartTriage = [
            'labels' => array_column($byTriage, 'label'),
            'counts' => array_map('intval', array_column($byTriage, 'count')),
        ];
        $chartStatus = [
            'labels' => array_column($byStatus, 'label'),
            'counts' => array_map('intval', array_column($byStatus, 'count')),
        ];

        $this->view('analytics/index', [
            'pageTitle'      => 'Analytics',
            'filters'        => $filters,
            'kpis'           => $kpis,
            'chartDaily'     => $chartDaily,
            'chartHazard'    => $chartHazard,
            'chartTriage'    => $chartTriage,
            'chartStatus'    => $chartStatus,
            'topAreas'       => $topAreas,
            'dailyTable'     => $dailyTable,
            'hazardSummary'  => $hazardSummary,
            'barangays'      => $barangays,
            'overallSummary' => $overallSummary,
            'flash'          => $this->flash(),
        ]);
    }

    public function export(): void
    {
        $this->requireRole(['admin', 'personnel']);
        $user = auth();

        // Rate-limit: max 10 exports per minute per user
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM audit_logs
             WHERE user_id = :uid AND action = 'export_analytics'
               AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
        );
        $stmt->execute(['uid' => $user['id']]);
        $recentExports = (int) $stmt->fetchColumn();

        if ($recentExports >= 10) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Rate limit: max 10 exports per minute.']);
            exit;
        }

        $filters = $this->parseFilters();

        // Validate date range (max 1 year)
        $from = $filters['date_from'];
        $to   = $filters['date_to'];
        $df   = \DateTime::createFromFormat('Y-m-d', $from);
        $dt   = \DateTime::createFromFormat('Y-m-d', $to);
        if (!$df || !$dt) {
            http_response_code(422);
            echo 'Invalid date format.';
            exit;
        }
        if ($dt < $df) {
            http_response_code(422);
            echo 'date_to must be after date_from.';
            exit;
        }
        $diffDays = $df->diff($dt)->days;
        if ($diffDays > 366) {
            http_response_code(422);
            echo 'Date range cannot exceed 1 year.';
            exit;
        }

        $rows = $this->svc->exportRows($filters);
        $filename = 'resqnow-reports-' . ($filters['date_from'] ?? date('Y-m-d')) . '_to_' . ($filters['date_to'] ?? date('Y-m-d')) . '.csv';

        // Audit log
        (new AuditLog($this->db))->write(
            (int) $user['id'],
            'export_analytics',
            'analytics',
            null,
            json_encode([
                'filters'    => $filters,
                'row_count'  => count($rows),
                'filename'   => $filename,
            ])
        );

        // Stream CSV
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');

        // UTF-8 BOM so Excel opens Filipino characters correctly
        fwrite($out, "\xEF\xBB\xBF");

        // Header row
        fputcsv($out, [
            'Report Code', 'Submitted At', 'Hazard Type', 'Emergency',
            'Triage Level', 'Triage Score', 'Status',
            'Barangay', 'Incident Address', 'Assigned Team',
            'Verified At', 'Assigned At', 'Resolved At',
            'Submitter', 'Submitter Phone',
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['report_code']       ?? '',
                $row['created_at']        ?? '',
                $row['hazard_type']       ?? '',
                $row['is_emergency']      ?? '',
                $row['triage_level']      ?? '',
                $row['triage_score']      ?? '',
                $row['status']            ?? '',
                $row['barangay']          ?? '',
                $row['incident_address']  ?? '',
                $row['assigned_team']     ?? '',
                $row['verified_at']       ?? '',
                $row['assigned_at']       ?? '',
                $row['resolved_at']       ?? '',
                $row['submitter']         ?? '',
                $row['submitter_phone']   ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────

    /**
     * Fill every date in [date_from, date_to] with count=0 if no incident existed.
     * This ensures the daily chart shows the full selected range, not just active days.
     */
    private function fillDateRange(array $rows, array $filters): array
    {
        $from = $filters['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to   = $filters['date_to']   ?? date('Y-m-d');

        // Index existing rows by date string
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['date']] = (int) $r['count'];
        }

        $result = [];
        $cur = new \DateTime($from);
        $end = new \DateTime($to);
        $end->modify('+1 day'); // inclusive end

        while ($cur < $end) {
            $d = $cur->format('Y-m-d');
            $result[] = ['date' => $d, 'count' => $indexed[$d] ?? 0];
            $cur->modify('+1 day');
        }

        return $result;
    }

    /**
     * Fill every date in range for the daily table (submitted/resolved/active columns).
     */
    private function fillDateRangeTable(array $rows, array $filters): array
    {
        $from = $filters['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to   = $filters['date_to']   ?? date('Y-m-d');

        // Index existing rows by date string
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[$r['date']] = $r;
        }

        $result = [];
        $cur = new \DateTime($from);
        $end = new \DateTime($to);
        $end->modify('+1 day');

        while ($cur < $end) {
            $d = $cur->format('Y-m-d');
            $result[] = $indexed[$d] ?? [
                'date'      => $d,
                'submitted' => 0,
                'resolved'  => 0,
                'active'    => 0,
            ];
            $cur->modify('+1 day');
        }

        return $result;
    }

    private function parseFilters(): array
    {
        $dateFrom   = trim((string) ($this->input('date_from') ?? ''));
        $dateTo     = trim((string) ($this->input('date_to')   ?? ''));
        $hazardType = trim((string) ($this->input('hazard_type') ?? ''));
        $status     = trim((string) ($this->input('status')     ?? ''));
        $triage     = trim((string) ($this->input('triage_level') ?? ''));
        $barangayId = (int) ($this->input('barangay_id') ?? 0);

        // Default date range: last 30 days
        if ($dateFrom === '' || !\DateTime::createFromFormat('Y-m-d', $dateFrom)) {
            $dateFrom = date('Y-m-d', strtotime('-30 days'));
        }
        if ($dateTo === '' || !\DateTime::createFromFormat('Y-m-d', $dateTo)) {
            $dateTo = date('Y-m-d');
        }
        // Clamp: to >= from
        if ($dateTo < $dateFrom) {
            $dateTo = $dateFrom;
        }

        return array_filter([
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'hazard_type' => $hazardType  ?: null,
            'status'      => $status      ?: null,
            'triage_level'=> $triage      ?: null,
            'barangay_id' => $barangayId  ?: null,
        ], fn($v) => $v !== null);
    }
}
