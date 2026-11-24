<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Incident;

class VerificationController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $filters = [
            'triage_level' => trim((string) ($_GET['triage_level'] ?? '')),
            'hazard_type'  => trim((string) ($_GET['hazard_type']  ?? '')),
            'search'       => trim((string) ($_GET['search']       ?? '')),
        ];
        $filters = array_filter($filters); // remove empty

        $model  = new Incident($this->db);
        $result = $model->verificationQueuePaginated($filters, $page, $perPage);

        // Count by triage for stat pills
        $allCounts = $this->db->query(
            "SELECT triage_level, COUNT(*) AS cnt
             FROM incidents WHERE status='submitted'
             GROUP BY triage_level"
        )->fetchAll();
        $counts = [];
        foreach ($allCounts as $row) {
            $counts[$row['triage_level']] = (int) $row['cnt'];
        }

        $this->view('verification/index', [
            'pageTitle'    => 'Verification Queue',
            'incidents'    => $result['data'],
            'pagination'   => $result,
            'pendingCount' => $model->pendingVerificationCount(),
            'counts'       => $counts,
            'filters'      => $filters,
            'flash'        => $this->flash(),
        ]);
    }
}
