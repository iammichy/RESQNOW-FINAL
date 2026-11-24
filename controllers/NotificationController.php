<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);
        $user = auth();

        $notifModel = new Notification($this->db);
        $notifications = $notifModel->forUser((int) $user['id'], 50);

        // All announcements (even expired ones) for the notification history
        $announcements = $this->db->query(
            "SELECT id, title, body, severity, audience, event_date, created_at,
                    CASE WHEN event_date IS NOT NULL AND event_date < NOW() THEN 1 ELSE 0 END AS is_expired
             FROM announcements
             WHERE is_active = 1
               AND (audience = 'all' OR audience = 'residents')
             ORDER BY created_at DESC
             LIMIT 30"
        )->fetchAll();

        // Mark all as read when viewing the full list
        $notifModel->markAllRead((int) $user['id']);

        $this->view('notifications/index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $notifications,
            'announcements' => $announcements,
        ]);
    }

    public function markAllRead(): void
    {
        $this->requireRole(['admin', 'personnel']);
        if (!Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/notifications');
            return;
        }
        (new Notification($this->db))->markAllRead((int) auth()['id']);
        // Redirect back to the tab the user was on
        $tab = $this->input('return_tab', 'notif') === 'ann' ? '#ann' : '';
        $this->redirect('/notifications' . $tab);
    }
}
