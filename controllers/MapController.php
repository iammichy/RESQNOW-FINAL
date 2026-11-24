<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Incident;
use App\Models\AuditLog;

class MapController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $this->view('map/index', [
            'pageTitle' => 'Incident Map',
        ]);
    }

    public function data(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $filters = [
            'status'       => $_GET['status'] ?? 'active',
            'hazard_type'  => $_GET['hazard_type'] ?? '',
            'triage_level' => $_GET['triage_level'] ?? '',
            'date_from'    => $_GET['date_from'] ?? '',
            'date_to'      => $_GET['date_to'] ?? '',
        ];

        $where = [];
        $params = [];

        if ($filters['status'] === 'active') {
            $where[] = "i.status NOT IN ('resolved', 'cancelled', 'closed', 'rejected', 'duplicate')";
        } elseif ($filters['status'] !== 'all') {
            $where[] = "i.status = :status";
            $params['status'] = $filters['status'];
        }

        if ($filters['hazard_type']) {
            $where[] = "i.hazard_type = :hazard";
            $params['hazard'] = $filters['hazard_type'];
        }

        if ($filters['triage_level']) {
            $where[] = "i.triage_level = :triage";
            $params['triage'] = $filters['triage_level'];
        }

        if ($filters['date_from']) {
            $where[] = "i.created_at >= :from";
            $params['from'] = $filters['date_from'] . ' 00:00:00';
        }

        if ($filters['date_to']) {
            $where[] = "i.created_at <= :to";
            $params['to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // GPS clause — append correctly regardless of whether other WHERE conditions exist
        $gpsCond = 'i.location_lat IS NOT NULL AND i.location_lng IS NOT NULL';
        if (!empty($where)) {
            $gpsSql = 'AND ' . $gpsCond;
        } else {
            $gpsSql = 'WHERE ' . $gpsCond;
        }

        $sql = "SELECT i.id, i.code, i.hazard_type, i.status, i.triage_level, i.assigned_team,
                       i.location_lat, i.location_lng, i.address, i.created_at,
                       u.name AS reporter_name
                FROM incidents i
                LEFT JOIN users u ON u.id = i.reported_by
                {$whereSql}
                {$gpsSql}
                ORDER BY
                    FIELD(i.triage_level,'critical','high','moderate','low','pending_reassessment'),
                    i.created_at DESC
                LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $incidents = $stmt->fetchAll();

        $this->json(['incidents' => $incidents]);
    }
}
