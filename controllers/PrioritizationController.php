<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Incident;

class PrioritizationController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $model     = new Incident($this->db);
        $incidents = $model->prioritizationQueue();
        $counts    = $model->prioritizationCounts();

        $this->view('prioritization/index', [
            'pageTitle' => 'Dispatch Queue',
            'incidents' => $incidents,
            'counts'    => $counts,
            'flash'     => $this->flash(),
        ]);
    }
}
