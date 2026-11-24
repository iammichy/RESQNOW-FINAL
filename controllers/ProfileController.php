<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;

class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = auth();

        // Fetch full user record
        $stmt = $this->db->prepare(
            "SELECT id, name, email, phone, role, status, barangay, created_at FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => (int) $user['id']]);
        $profile = $stmt->fetch();

        // Count total reports for this user
        $reportCount = (int) $this->db->prepare(
            "SELECT COUNT(*) FROM incidents WHERE reported_by = :id"
        )->execute(['id' => (int) $user['id']]) ? $this->scalar(
            "SELECT COUNT(*) FROM incidents WHERE reported_by = :id", ['id' => (int) $user['id']]
        ) : 0;

        $this->view('profile/index', [
            'pageTitle'   => 'My Profile',
            'profile'     => $profile,
            'reportCount' => $reportCount,
            'flash'       => $this->flash(),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();

        if (!Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/profile');
            return;
        }

        $user   = auth();
        $action = $this->input('action', 'profile');

        if ($action === 'password') {
            $this->handlePasswordChange($user);
        } else {
            $this->handleProfileUpdate($user);
        }
    }

    private function handleProfileUpdate(array $user): void
    {
        $name  = trim((string) $this->input('name', ''));
        $phone = trim((string) $this->input('phone', ''));
        $email = trim((string) $this->input('email', ''));

        $errors = [];
        if ($name === '') $errors[] = 'Name is required.';
        if ($phone === '') $errors[] = 'Phone is required.';
        if ($phoneErr = $this->validatePhone($phone, true)) $errors[] = $phoneErr;
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }

        // Check uniqueness (skip own record)
        if (!empty($phone)) {
            $dup = $this->db->prepare(
                "SELECT id FROM users WHERE phone = :phone AND id != :id LIMIT 1"
            );
            $dup->execute(['phone' => $phone, 'id' => $user['id']]);
            if ($dup->fetch()) $errors[] = 'Phone number is already in use by another account.';
        }

        if (!empty($errors)) {
            $this->setFlash('danger', implode(' ', $errors));
            $this->redirect('/profile');
            return;
        }

        $this->db->prepare(
            "UPDATE users SET name = :name, phone = :phone, email = :email WHERE id = :id"
        )->execute(['name' => $name, 'phone' => $phone, 'email' => $email ?: null, 'id' => $user['id']]);

        // Update session
        $_SESSION['user']['name']  = $name;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['email'] = $email;

        (new AuditLog($this->db))->write(
            $user['id'], 'profile.updated', 'user', $user['id'],
            json_encode(['name' => $name])
        );

        $this->setFlash('success', 'Profile updated.');
        $this->redirect('/profile');
    }

    private function handlePasswordChange(array $user): void
    {
        $current  = (string) $this->input('current_password', '');
        $new      = (string) $this->input('new_password', '');
        $confirm  = (string) $this->input('confirm_password', '');

        // Verify current password
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $user['id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            $this->setFlash('danger', 'Current password is incorrect.');
            $this->redirect('/profile');
            return;
        }

        if ($passError = $this->validatePassword($new)) {
            $this->setFlash('danger', $passError);
            $this->redirect('/profile');
            return;
        }

        if ($new !== $confirm) {
            $this->setFlash('danger', 'New passwords do not match.');
            $this->redirect('/profile');
            return;
        }

        $this->db->prepare(
            "UPDATE users SET password = :hash WHERE id = :id"
        )->execute(['hash' => password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]), 'id' => $user['id']]);

        (new AuditLog($this->db))->write(
            $user['id'], 'profile.password_changed', 'user', $user['id'], null
        );

        $this->setFlash('success', 'Password changed successfully.');
        $this->redirect('/profile');
    }
}
