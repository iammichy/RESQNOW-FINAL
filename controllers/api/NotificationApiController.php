<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Notification;

class NotificationApiController extends Controller
{
    public function unreadCount(): void
    {
        $user = $this->requireApiAuth();
        $model = new Notification($this->db);
        // key name is 'unread_count' — mobile layout polls this
        $this->json(['unread_count' => $model->unreadCount((int) $user['id'])]);
    }

    public function index(): void
    {
        $user = $this->requireApiAuth();
        $model = new Notification($this->db);
        $this->json(['notifications' => $model->forUser((int) $user['id'])]);
    }

    public function markRead(string $id): void
    {
        $user = $this->requireApiAuth();
        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return; // BUG FIX: must return after json() to stop execution
        }
        $model = new Notification($this->db);
        $model->markRead((int) $id, (int) $user['id']);
        $this->json(['ok' => true]);
    }

    public function markAllRead(): void
    {
        $user = $this->requireApiAuth();
        if ($this->authenticateSession() && !$this->verifyCsrfForApi()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return; // BUG FIX: must return after json() to stop execution
        }
        $model = new Notification($this->db);
        $model->markAllRead((int) $user['id']);
        $this->json(['ok' => true]);
    }
}
