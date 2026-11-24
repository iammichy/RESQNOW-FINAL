<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Core\Csrf;

class PersonnelController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin']);

        $stmt = $this->db->query("SELECT * FROM users WHERE role IN ('admin', 'personnel', 'responder') ORDER BY role, name");
        $users = $stmt->fetchAll();

        // Determine which responders are currently on an active mission
        $onDutyRaw = $this->db->query(
            "SELECT DISTINCT tm.user_id
             FROM team_members tm
             JOIN assignments a ON a.team_id = tm.team_id
             WHERE a.status = 'active'"
        )->fetchAll(\PDO::FETCH_COLUMN);
        $onDutyIds = array_map('intval', $onDutyRaw);

        $this->view('personnel/index', [
            'pageTitle' => 'Personnel Management',
            'users'     => $users,
            'onDutyIds' => $onDutyIds,
            'flash'     => $this->flash(),
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['admin']);

        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) {
                $this->redirect('/personnel');
                return;
            }

            $name = trim((string) $this->input('name', ''));
            $email = trim((string) $this->input('email', ''));
            $phone = trim((string) $this->input('phone', ''));
            $role = $this->input('role', 'personnel');
            $password = (string) $this->input('password', '');

            if (!$name || !$email || !$password) {
                $this->setFlash('danger', 'Name, email, and password are required.');
                $this->redirect('/personnel/new');
                return;
            }

            if ($phoneError = $this->validatePhone($phone)) {
                $this->setFlash('danger', $phoneError);
                $this->redirect('/personnel/new');
                return;
            }

            if ($passError = $this->validatePassword($password)) {
                $this->setFlash('danger', $passError);
                $this->redirect('/personnel');
                return;
            }

            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $stmt = $this->db->prepare(
                "INSERT INTO users (name, email, phone, password, role, status) VALUES (:name, :email, :phone, :password, :role, 'approved')"
            );
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hash,
                'role' => $role
            ]);

            $id = $this->db->lastInsertId();
            (new AuditLog($this->db))->write(
                auth()['id'], 'personnel.created', 'user', (int) $id,
                json_encode(['name' => $name, 'role' => $role])
            );

            $this->setFlash('success', 'Personnel account created.');
            $this->redirect('/personnel');
            return;
        }

        $this->view('personnel/form', [
            'pageTitle' => 'Add Personnel',
            'user' => null,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireRole(['admin']);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int) $id]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->redirect('/personnel');
            return;
        }

        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) {
                $this->redirect('/personnel');
                return;
            }

            $name = trim((string) $this->input('name', ''));
            $email = trim((string) $this->input('email', ''));
            $phone = trim((string) $this->input('phone', ''));
            $role = $this->input('role', 'personnel');
            $status = $this->input('status', 'approved');

            if ($phoneError = $this->validatePhone($phone)) {
                $this->setFlash('danger', $phoneError);
                $this->redirect('/personnel/' . $id . '/edit');
                return;
            }

            $stmt = $this->db->prepare(
                "UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status WHERE id = :id"
            );
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'status' => $status,
                'id' => (int) $id
            ]);

            (new AuditLog($this->db))->write(
                auth()['id'], 'personnel.updated', 'user', (int) $id,
                json_encode(['name' => $name, 'role' => $role, 'status' => $status])
            );

            $this->setFlash('success', 'Account updated.');
            $this->redirect('/personnel');
            return;
        }

        $this->view('personnel/form', [
            'pageTitle' => 'Edit Personnel',
            'user' => $user,
        ]);
    }

    public function resetPassword(string $id): void
    {
        $this->requireRole(['admin']);

        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/personnel');
            return;
        }

        $password = (string) $this->input('password', '');
        if ($passError = $this->validatePassword($password)) {
            $this->setFlash('danger', $passError);
            $this->redirect('/personnel/edit/' . $id);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("UPDATE users SET password = :pass WHERE id = :id");
        $stmt->execute(['pass' => $hash, 'id' => (int) $id]);

        (new AuditLog($this->db))->write(
            auth()['id'], 'personnel.password_reset', 'user', (int) $id,
            null
        );

        $this->setFlash('success', 'Password reset successfully.');
        $this->redirect('/personnel');
    }

    public function toggleStatus(string $id): void
    {
        $this->requireRole(['admin']);

        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/personnel');
            return;
        }

        $stmt = $this->db->prepare("SELECT status FROM users WHERE id = :id");
        $stmt->execute(['id' => (int) $id]);
        $current = $stmt->fetchColumn();

        $newStatus = ($current === 'approved') ? 'suspended' : 'approved';
        $suspendedAt = ($newStatus === 'suspended') ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare("UPDATE users SET status = :status, suspended_at = :sat WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'sat' => $suspendedAt, 'id' => (int) $id]);

        (new AuditLog($this->db))->write(
            auth()['id'], 'personnel.status_toggle', 'user', (int) $id,
            json_encode(['new_status' => $newStatus])
        );

        $this->setFlash('success', 'Account ' . ($newStatus === 'approved' ? 'activated' : 'deactivated') . '.');
        $this->redirect('/personnel');
    }
}
