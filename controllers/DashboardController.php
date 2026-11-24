<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Incident;
use App\Services\AnalyticsService;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $svc  = new AnalyticsService($this->db);
        $kpis = $svc->kpiSummary([]);

        $daily    = $svc->dailyCounts([]);
        $byHazard = $svc->byHazard([]);

        $incidentModel = new Incident($this->db);
        $latestReports = $incidentModel->latest(5);

        // Real all-time totals for dashboard KPI cards
        $allTimeTotals = $this->db->query(
            "SELECT
                COUNT(*)                                                        AS total_reports,
                SUM(CASE WHEN is_emergency = 1 THEN 1 ELSE 0 END)              AS emergency_reports,
                SUM(CASE WHEN is_emergency = 0 THEN 1 ELSE 0 END)              AS non_emergency_reports,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END)          AS pending_verification,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END)           AS resolved_reports
             FROM incidents"
        )->fetch();

        // Merge real totals into kpis (overwrite 30-day values with all-time for dashboard)
        $kpis['total_reports']        = (int)($allTimeTotals['total_reports']        ?? 0);
        $kpis['emergency_reports']    = (int)($allTimeTotals['emergency_reports']    ?? 0);
        $kpis['non_emergency_reports']= (int)($allTimeTotals['non_emergency_reports']?? 0);
        $kpis['pending_verification'] = (int)($allTimeTotals['pending_verification'] ?? 0);
        $kpis['resolved_reports']     = (int)($allTimeTotals['resolved_reports']     ?? 0);

        // Active incidents — currently being responded to (assigned → in_progress)
        $activeIncidents = $this->db->query(
            "SELECT i.code, i.hazard_type, i.triage_level, i.status, i.address,
                    i.description, i.created_at, i.assigned_team,
                    t.team_name
             FROM incidents i
             LEFT JOIN response_teams t ON t.id = i.assigned_team_id
             WHERE i.status IN ('assigned','acknowledged','on_scene','in_progress')
             ORDER BY FIELD(i.triage_level, 'critical','high','moderate','low','pending_reassessment'),
                      i.created_at DESC
             LIMIT 5"
        )->fetchAll();

        $activeCount = (int) $this->db->query(
            "SELECT COUNT(*) FROM incidents WHERE status IN ('assigned','acknowledged','on_scene','in_progress')"
        )->fetchColumn();

        // Real live updates — latest 5 response updates with incident info
        $liveUpdates = $this->db->query(
            "SELECT ru.message, ru.created_at, ru.update_type, ru.action_code,
                    i.code AS incident_code, i.hazard_type, i.status AS incident_status,
                    t.team_name
             FROM response_updates ru
             JOIN incidents i ON i.id = ru.incident_id
             LEFT JOIN response_teams t ON t.id = ru.team_id
             ORDER BY ru.created_at DESC
             LIMIT 6"
        )->fetchAll();

        $this->view('dashboard/index', [
            'pageTitle'       => 'Dashboard',
            'kpis'            => $kpis,
            'dailyIncidents'  => $daily,
            'byHazard'        => $byHazard,
            'latestReports'   => $latestReports,
            'liveUpdates'     => $liveUpdates,
            'activeIncidents' => $activeIncidents,
            'activeCount'     => $activeCount,
        ]);
    }
}
