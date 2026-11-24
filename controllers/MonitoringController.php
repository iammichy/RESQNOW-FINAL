<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Assignment;
use App\Models\ResponseUpdate;

class MonitoringController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $assignModel = new Assignment($this->db);
        $updateModel = new ResponseUpdate($this->db);

        $activeBoard   = $assignModel->activeBoard();
        $resolvedToday = $assignModel->resolvedToday();
        $supportQueue  = $updateModel->pendingSupportRequests();

        $activeMissions = count($activeBoard);
        $pendingSupport = count($supportQueue);

        $avgAck = $this->avgMinutes(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, a.assigned_at, a.acknowledged_at))
             FROM assignments a
             WHERE a.acknowledged_at IS NOT NULL
               AND DATE(a.assigned_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );
        $avgResolve = $this->avgMinutes(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, a.assigned_at, a.resolved_at))
             FROM assignments a
             WHERE a.resolved_at IS NOT NULL
               AND DATE(a.assigned_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );

        $this->view('monitoring/index', [
            'pageTitle'         => 'Monitoring Board',
            'activeBoard'       => $activeBoard,
            'resolvedToday'     => $resolvedToday,
            'supportQueue'      => $supportQueue,
            'activeMissions'    => $activeMissions,
            'pendingSupport'    => $pendingSupport,
            'avgAckMinutes'     => $avgAck !== null ? (int) round($avgAck) : null,
            'avgResolveMinutes' => $avgResolve !== null ? (int) round($avgResolve) : null,
            'flash'             => $this->flash(),
        ]);
    }

    /** JSON endpoint for 30-second auto-refresh */
    public function board(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $assignModel = new Assignment($this->db);
        $updateModel = new ResponseUpdate($this->db);

        $this->json([
            'active'         => $assignModel->activeBoard(),
            'resolved_today' => $assignModel->resolvedToday(),
            'support_queue'  => $updateModel->pendingSupportRequests(),
            'pending_support'=> $updateModel->countPendingSupport(),
            'ts'             => time(),
        ]);
    }

    /** Acknowledge a support request */
    public function ackSupport(string $updateId): void
    {
        $this->requireRole(['admin', 'personnel']);

        if (!\App\Core\Csrf::verify($this->input('csrf_token'))) {
            $this->setFlash('danger', 'Invalid session.');
            $this->redirect('/monitoring');
            return;
        }

        $user = auth();
        (new ResponseUpdate($this->db))->acknowledgeSupportRequest(
            (int) $updateId, (int) $user['id']
        );

        $this->setFlash('success', 'Support request acknowledged.');
        $this->redirect('/monitoring');
    }

    // Use a distinct name to avoid the PHP visibility conflict with
    // Controller::scalar() which is protected with a different signature.
    private function avgMinutes(string $sql): ?float
    {
        try {
            $val = $this->db->query($sql)->fetchColumn();
            return ($val !== false && $val !== null) ? (float) $val : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
