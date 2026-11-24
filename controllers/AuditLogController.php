<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin']);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'action' => $_GET['action'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $where = [];
        $params = [];

        if ($filters['user_id']) {
            $where[] = "a.user_id = :uid";
            $params['uid'] = $filters['user_id'];
        }
        if ($filters['action']) {
            $where[] = "a.action LIKE :action";
            $params['action'] = '%' . $filters['action'] . '%';
        }
        if ($filters['date_from']) {
            $where[] = "a.created_at >= :from";
            $params['from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to']) {
            $where[] = "a.created_at <= :to";
            $params['to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['search']) {
            $where[] = "(a.details LIKE :search OR a.entity LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $count = $this->db->prepare("SELECT COUNT(*) FROM audit_logs a $whereSql");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT a.*, u.name AS user_name, u.role AS user_role
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             $whereSql
             ORDER BY a.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll();

        // Get users for filter
        $users = $this->db->query("SELECT id, name FROM users WHERE role != 'resident' ORDER BY name")->fetchAll();

        $this->view('audit_logs/index', [
            'pageTitle' => 'Audit Logs',
            'logs' => $logs,
            'users' => $users,
            'filters' => $filters,
            'pagination' => [
                'total' => $total,
                'perPage' => $perPage,
                'current' => $page,
                'last' => ceil($total / $perPage)
            ]
        ]);
    }

    public function export(): void
    {
        $this->requireRole(['admin']);

        // Apply the same filters as the index view
        $filters = [
            'user_id'   => $_GET['user_id']   ?? '',
            'action'    => $_GET['action']     ?? '',
            'date_from' => $_GET['date_from']  ?? '',
            'date_to'   => $_GET['date_to']    ?? '',
            'search'    => $_GET['search']     ?? '',
        ];

        $where = [];
        $params = [];

        if ($filters['user_id']) {
            $where[] = "a.user_id = :uid";
            $params['uid'] = $filters['user_id'];
        }
        if ($filters['action']) {
            $where[] = "a.action LIKE :action";
            $params['action'] = '%' . $filters['action'] . '%';
        }
        if ($filters['date_from']) {
            $where[] = "a.created_at >= :from";
            $params['from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to']) {
            $where[] = "a.created_at <= :to";
            $params['to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['search']) {
            $where[] = "(a.details LIKE :search OR a.entity LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT a.created_at, u.name AS user, u.role, a.action, a.entity, a.entity_id, a.details, a.ip_address
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             $whereSql
             ORDER BY a.created_at DESC
             LIMIT 5000"
        );
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Human-readable action labels (same as view)
        $actionLabels = [
            'auth.login'                => 'Signed in',
            'auth.login_mobile'         => 'Signed in (mobile)',
            'auth.register'             => 'Registered account',
            'incident.created'          => 'Submitted report',
            'incident.verified'         => 'Verified report',
            'incident.reassessed'       => 'Reassessed triage',
            'incident.rejected'         => 'Rejected report',
            'incident.duplicate'        => 'Marked as duplicate',
            'incident.reopened'         => 'Reopened report',
            'incident.assigned'         => 'Assigned team',
            'incident.acknowledged'     => 'Responder acknowledged',
            'incident.on_scene'         => 'Responder on scene',
            'incident.in_progress'      => 'Response in progress',
            'incident.support_requested'=> 'Support requested',
            'incident.resolved'         => 'Resolved incident',
            'incident.escalated'        => 'Escalated to admin',
            'personnel.created'         => 'Created personnel account',
            'personnel.updated'         => 'Updated personnel account',
            'personnel.password_reset'  => 'Reset password',
            'personnel.status_toggle'   => 'Toggled account status',
            'team.created'              => 'Created response team',
            'team.updated'              => 'Updated response team',
            'team.deleted'              => 'Deleted response team',
            'resident.approved'         => 'Approved resident account',
            'resident.rejected'         => 'Rejected resident account',
            'announcement.created'      => 'Posted announcement',
            'announcement.deleted'      => 'Deleted announcement',
            'broadcast.sent'            => 'Sent broadcast notification',
            'export_analytics'          => 'Exported analytics CSV',
        ];

        $filename = 'resqnow-audit-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        fputcsv($out, ['Timestamp', 'User', 'Role', 'Action', 'Action (Technical)', 'Entity', 'Entity ID', 'Summary', 'IP Address']);

        foreach ($logs as $log) {
            $actionLabel = $actionLabels[$log['action']] ?? ucwords(str_replace(['.','_'], [' → ',' '], $log['action']));

            // Parse details to summary
            $details = $log['details'] ? json_decode($log['details'], true) : [];
            $parts = [];
            if (!empty($details['code']))         $parts[] = 'Report: ' . $details['code'];
            if (!empty($details['team_name']))    $parts[] = 'Team: ' . $details['team_name'];
            if (!empty($details['name']))         $parts[] = $details['name'];
            if (!empty($details['title']))        $parts[] = '"' . mb_strimwidth($details['title'], 0, 40, '…') . '"';
            if (!empty($details['role']))         $parts[] = 'Role: ' . $details['role'];
            if (!empty($details['new_status']))   $parts[] = 'Status → ' . $details['new_status'];
            if (!empty($details['reason']))       $parts[] = 'Reason: ' . mb_strimwidth($details['reason'], 0, 60, '…');
            if (!empty($details['triage_level'])) $parts[] = 'Triage: ' . ucfirst($details['triage_level']);
            if (!empty($details['row_count']))    $parts[] = $details['row_count'] . ' rows';
            $summary = implode(' · ', array_slice($parts, 0, 4)) ?: ($log['details'] ?? '—');

            fputcsv($out, [
                $log['created_at'],
                $log['user'] ?? 'System',
                $log['role'] ?? '',
                $actionLabel,
                $log['action'],
                $log['entity'] ?? '',
                $log['entity_id'] ?? '',
                $summary,
                $log['ip_address'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }
}
