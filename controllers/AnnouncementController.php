<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;
use App\Core\Csrf;

class AnnouncementController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin', 'personnel']);

        $stmt = $this->db->query("SELECT a.*, u.name AS creator_name FROM announcements a LEFT JOIN users u ON u.id = a.created_by ORDER BY a.created_at DESC");
        $announcements = $stmt->fetchAll();

        $this->view('announcements/index', [
            'pageTitle' => 'Announcements',
            'announcements' => $announcements,
            'flash' => $this->flash(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['admin', 'personnel']);

        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) {
                $this->redirect('/announcements');
                return;
            }

            $title     = trim((string) $this->input('title', ''));
            $body      = trim((string) $this->input('body', ''));
            $severity  = $this->input('severity', 'information');
            $audience  = $this->input('audience', 'all');
            $eventDate = $this->input('event_date') ?: null;

            $validSeverities = ['information', 'advisory', 'warning', 'critical'];
            $validAudiences  = ['all', 'residents', 'responders'];
            if (!in_array($severity, $validSeverities)) $severity = 'information';
            if (!in_array($audience, $validAudiences))  $audience = 'all';

            if (!$title || !$body) {
                $this->setFlash('danger', 'Title and body are required.');
                $this->redirect('/announcements/new');
                return;
            }

            $stmt = $this->db->prepare(
                "INSERT INTO announcements (title, body, severity, audience, event_date, hazard_type, is_active, created_by)
                 VALUES (:title, :body, :severity, :audience, :event_date, NULL, 1, :cb)"
            );
            $stmt->execute([
                'title'      => $title,
                'body'       => $body,
                'severity'   => $severity,
                'audience'   => $audience,
                'event_date' => $eventDate ?: null,
                'cb'         => auth()['id'],
            ]);

            (new AuditLog($this->db))->write(
                auth()['id'], 'announcement.created', 'announcement', (int) $this->db->lastInsertId(),
                json_encode(['title' => $title, 'severity' => $severity, 'audience' => $audience])
            );

            $this->setFlash('success', 'Announcement posted.');
            $this->redirect('/announcements');
            return;
        }

        $this->view('announcements/form', [
            'pageTitle' => 'New Announcement',
            'announcement' => null,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole(['admin', 'personnel']);

        $stmt = $this->db->prepare("SELECT * FROM announcements WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int) $id]);
        $announcement = $stmt->fetch();

        if (!$announcement) {
            $this->setFlash('danger', 'Announcement not found.');
            $this->redirect('/announcements');
            return;
        }

        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) {
                $this->redirect('/announcements');
                return;
            }

            $title     = trim((string) $this->input('title', ''));
            $body      = trim((string) $this->input('body', ''));
            $severity  = $this->input('severity', 'information');
            $audience  = $this->input('audience', 'all');
            $eventDate = $this->input('event_date') ?: null;

            $validSeverities = ['information', 'advisory', 'warning', 'critical'];
            $validAudiences  = ['all', 'residents', 'responders'];
            if (!in_array($severity, $validSeverities)) $severity = 'information';
            if (!in_array($audience, $validAudiences))  $audience = 'all';

            if (!$title || !$body) {
                $this->setFlash('danger', 'Title and body are required.');
                $this->redirect('/announcements/' . $id . '/edit');
                return;
            }

            $stmt = $this->db->prepare(
                "UPDATE announcements SET title=:title, body=:body, severity=:severity,
                 audience=:audience, event_date=:event_date WHERE id=:id"
            );
            $stmt->execute([
                'title'      => $title,
                'body'       => $body,
                'severity'   => $severity,
                'audience'   => $audience,
                'event_date' => $eventDate ?: null,
                'id'         => (int) $id,
            ]);

            (new AuditLog($this->db))->write(
                auth()['id'], 'announcement.updated', 'announcement', (int) $id,
                json_encode(['title' => $title, 'severity' => $severity])
            );

            $this->setFlash('success', 'Announcement updated.');
            $this->redirect('/announcements');
            return;
        }

        $this->view('announcements/form', [
            'pageTitle'     => 'Edit Announcement',
            'announcement'  => $announcement,
        ]);
    }

    public function delete(string $id): void
    {
        $this->requireRole(['admin', 'personnel']);

        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/announcements');
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM announcements WHERE id = :id");
        $stmt->execute(['id' => (int) $id]);

        (new AuditLog($this->db))->write(
            auth()['id'], 'announcement.deleted', 'announcement', (int) $id,
            null
        );

        $this->setFlash('info', 'Announcement removed.');
        $this->redirect('/announcements');
    }

    public function broadcast(): void
    {
        $this->requireRole(['admin', 'personnel']);

        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/announcements');
            return;
        }

        $title = trim((string) $this->input('title', ''));
        $message = trim((string) $this->input('message', ''));
        $target = $this->input('target', 'residents'); // residents or all

        if (!$title || !$message) {
            $this->setFlash('danger', 'Title and message are required.');
            $this->redirect('/announcements');
            return;
        }

        $userModel = new User($this->db);
        $roles = ($target === 'all') ? ['admin', 'personnel', 'resident', 'responder'] : ['resident'];
        $users = $userModel->allByRole($roles);

        $notif = new Notification($this->db);

        // Send with a role-appropriate link so admin/personnel don't hit a 403
        foreach ($users as $u) {
            $role = $u['role'] ?? 'resident';
            if (in_array($role, ['admin', 'personnel'])) {
                $link = 'notifications';
            } elseif ($role === 'responder') {
                $link = 'app/notifications';
            } else {
                $link = 'app/resident';
            }
            $notif->createForUsers([(int)$u['id']], $title, $message, $link);
        }

        $userIds = array_column($users, 'id');

        (new AuditLog($this->db))->write(
            auth()['id'], 'broadcast.sent', 'notification', null,
            json_encode(['title' => $title, 'target' => $target])
        );

        $this->setFlash('success', 'Broadcast sent to ' . count($userIds) . ' users.');
        $this->redirect('/announcements');
    }
}
