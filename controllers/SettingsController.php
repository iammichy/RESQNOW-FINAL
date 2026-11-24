<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\EmergencyContact;
use App\Core\Csrf;

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin']);

        $settings = (new SystemSetting($this->db))->all();
        $contacts = (new EmergencyContact($this->db))->all();

        $this->view('settings/index', [
            'pageTitle' => 'System Settings',
            'settings'  => $settings,
            'contacts'  => $contacts,
            'flash'     => $this->flash(),
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireRole(['admin']);
        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/settings');
            return;
        }

        $fields = ['barangay_name', 'barangay_city', 'barangay_contact'];
        foreach ($fields as $f) {
            $val = trim((string) $this->input($f, ''));
            $this->saveSetting($f, $val);
        }

        // Logo upload
        if (!empty($_FILES['barangay_logo']['name'])) {
            $this->handleLogoUpload();
        }

        (new AuditLog($this->db))->write(auth()['id'], 'settings.profile_updated', 'system_settings');
        $this->setFlash('success', 'Barangay profile updated.');
        $this->redirect('/settings');
    }

    public function updateSystem(): void
    {
        $this->requireRole(['admin']);
        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/settings');
            return;
        }

        $this->saveSetting('registration_enabled', $this->input('registration_enabled') ? '1' : '0');
        $this->saveSetting('maintenance_mode', $this->input('maintenance_mode') ? '1' : '0');

        (new AuditLog($this->db))->write(auth()['id'], 'settings.system_updated', 'system_settings');
        $this->setFlash('success', 'System configuration saved.');
        $this->redirect('/settings');
    }

    public function createContact(): void
    {
        $this->requireRole(['admin']);
        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) {
                $this->redirect('/settings');
                return;
            }

            $name        = trim((string) $this->input('name', ''));
            $phone       = trim((string) $this->input('phone', ''));
            $description = trim((string) $this->input('description', ''));
            $type        = $this->input('unit_type', 'other');

            if (!$name) {
                $this->setFlash('danger', 'Contact name is required.');
                $this->redirect('/settings/contacts/new');
                return;
            }
            if ($phoneError = $this->validatePhone($phone, true)) {
                $this->setFlash('danger', $phoneError);
                $this->redirect('/settings/contacts/new');
                return;
            }

            $stmt = $this->db->prepare(
                "INSERT INTO emergency_contacts (name, phone, description, unit_type, is_active)
                 VALUES (:name, :phone, :desc, :type, 1)"
            );
            $stmt->execute(['name' => $name, 'phone' => $phone, 'desc' => $description, 'type' => $type]);

            (new AuditLog($this->db))->write(auth()['id'], 'contact.created', 'emergency_contacts', (int)$this->db->lastInsertId());
            $this->setFlash('success', 'Emergency contact added.');
            $this->redirect('/settings');
            return;
        }

        $this->view('settings/contact_form', ['pageTitle' => 'Add Contact', 'contact' => null]);
    }

    public function editContact(string $id): void
    {
        $this->requireRole(['admin']);

        $stmt = $this->db->prepare("SELECT * FROM emergency_contacts WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$id]);
        $contact = $stmt->fetch();

        if (!$contact) {
            $this->redirect('/settings');
            return;
        }

        if ($this->isPost()) {
            if (!Csrf::verify($this->input('csrf_token'))) {
                $this->redirect('/settings');
                return;
            }

            $name        = trim((string) $this->input('name', ''));
            $phone       = trim((string) $this->input('phone', ''));
            $description = trim((string) $this->input('description', ''));
            $type        = $this->input('unit_type', 'other');

            if (!$name) {
                $this->setFlash('danger', 'Contact name is required.');
                $this->redirect('/settings/contacts/' . $id . '/edit');
                return;
            }
            if ($phoneError = $this->validatePhone($phone, true)) {
                $this->setFlash('danger', $phoneError);
                $this->redirect('/settings/contacts/' . $id . '/edit');
                return;
            }

            $this->db->prepare(
                "UPDATE emergency_contacts SET name=:name, phone=:phone, description=:desc, unit_type=:type WHERE id=:id"
            )->execute(['name' => $name, 'phone' => $phone, 'desc' => $description, 'type' => $type, 'id' => (int)$id]);

            (new AuditLog($this->db))->write(auth()['id'], 'contact.updated', 'emergency_contacts', (int)$id);
            $this->setFlash('success', 'Contact updated.');
            $this->redirect('/settings');
            return;
        }

        $this->view('settings/contact_form', ['pageTitle' => 'Edit Contact', 'contact' => $contact]);
    }

    public function toggleContact(string $id): void
    {
        $this->requireRole(['admin']);
        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/settings');
            return;
        }

        $stmt = $this->db->prepare("SELECT is_active FROM emergency_contacts WHERE id = :id");
        $stmt->execute(['id' => (int)$id]);
        $current = (int)$stmt->fetchColumn();

        $newActive = $current ? 0 : 1;
        $this->db->prepare("UPDATE emergency_contacts SET is_active = :v WHERE id = :id")
                 ->execute(['v' => $newActive, 'id' => (int)$id]);

        (new AuditLog($this->db))->write(auth()['id'], 'contact.' . ($newActive ? 'activated' : 'deactivated'), 'emergency_contacts', (int)$id);
        $this->setFlash('success', 'Contact ' . ($newActive ? 'activated' : 'deactivated') . '.');
        $this->redirect('/settings');
    }

    public function deleteContact(string $id): void
    {
        $this->requireRole(['admin']);
        if (!$this->isPost() || !Csrf::verify($this->input('csrf_token'))) {
            $this->redirect('/settings');
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM emergency_contacts WHERE id = :id");
        $stmt->execute(['id' => (int) $id]);

        (new AuditLog($this->db))->write(auth()['id'], 'contact.deleted', 'emergency_contacts', (int)$id);
        $this->setFlash('info', 'Contact removed.');
        $this->redirect('/settings');
    }

    private function saveSetting(string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO system_settings (setting_key, setting_value) VALUES (:k, :v) 
             ON DUPLICATE KEY UPDATE setting_value = :v2"
        );
        $stmt->execute(['k' => $key, 'v' => $value, 'v2' => $value]);
    }

    private function handleLogoUpload(): void
    {
        $file = $_FILES['barangay_logo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed) || $file['size'] > 2 * 1024 * 1024) {
            $this->setFlash('danger', 'Invalid logo file. Use JPG/PNG under 2MB.');
            return;
        }

        $storageDir = APP_ROOT . '/storage/logos';
        if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);

        $filename = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $storageDir . '/' . $filename)) {
            $this->saveSetting('barangay_logo', $filename);
        }
    }
}
