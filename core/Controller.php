<?php

namespace App\Core;

use PDO;

abstract class Controller
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    protected function view(string $view, array $data = [], string $layout = 'admin'): void
    {
        $__authUser = auth();
        if ($__authUser && in_array($__authUser['role'] ?? '', ['admin', 'personnel'], true)) {
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM incidents WHERE status = 'submitted'");
                $stmt->execute();
                $data['pendingVerificationCount'] = (int) $stmt->fetchColumn();
            } catch (\Throwable $e) {
                $data['pendingVerificationCount'] = 0;
            }
            try {
                $stmt2 = $this->db->prepare(
                    "SELECT COUNT(*) FROM response_updates
                     WHERE support_requested = 1 AND support_acknowledged_at IS NULL"
                );
                $stmt2->execute();
                $data['pendingSupportCount'] = (int) $stmt2->fetchColumn();
            } catch (\Throwable $e) {
                $data['pendingSupportCount'] = 0;
            }
            try {
                $stmt3 = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = 'resident' AND status = 'pending'");
                $stmt3->execute();
                $data['pendingResidentsCount'] = (int) $stmt3->fetchColumn();
            } catch (\Throwable $e) {
                $data['pendingResidentsCount'] = 0;
            }
        } else {
            $data['pendingVerificationCount'] = 0;
            $data['pendingSupportCount'] = 0;
            $data['pendingResidentsCount'] = 0;
        }

        $data['extraHead'] = $data['extraHead'] ?? '';
        $data['extraScripts'] = $data['extraScripts'] ?? '';

        extract($data, EXTR_OVERWRITE);

        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View not found: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = APP_ROOT . '/app/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . base_url($url));
        exit;
    }

    protected function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function requireAuth(): void
    {
        if (!check()) {
            $this->redirect('/login');
        }
    }

    protected function requireRole(array $roles): void
    {
        $this->requireAuth();
        $user = auth();
        if (!$user || !in_array($user['role'] ?? '', $roles, true)) {
            http_response_code(403);
            die('403 Forbidden');
        }
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    protected function flash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Generate the next sequential RQN-YYYY-NNNN code safely.
     * Uses MAX on the numeric suffix to avoid COUNT(*)+1 race conditions.
     */
    protected function generateReportCode(): string
    {
        $year   = date('Y');
        $prefix = 'RQN-' . $year . '-';
        $stmt   = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING(code, :offset) AS UNSIGNED))
             FROM incidents WHERE code LIKE :pattern"
        );
        $stmt->execute(['offset' => strlen($prefix) + 1, 'pattern' => $prefix . '%']);
        $max = (int) $stmt->fetchColumn();
        return $prefix . sprintf('%04d', $max + 1);
    }

    /**
     * Authenticate from session (same-origin PWA).
     * Returns the session user array or null.
     */
    protected function authenticateSession(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Simple session-based rate limiter.
     * $key      : unique action name (e.g. 'login', 'register')
     * $max      : max attempts allowed in $windowSeconds
     * $windowSec: sliding window in seconds
     * Returns true if limit exceeded (caller should reject).
     */
    protected function isRateLimited(string $key, int $max, int $windowSec): bool
    {
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $sessKey  = 'rl_' . $key . '_' . md5($ip);
        $now      = time();

        $attempts = $_SESSION[$sessKey] ?? [];
        // Remove attempts outside the window
        $attempts = array_filter($attempts, fn($ts) => ($now - $ts) < $windowSec);
        $attempts = array_values($attempts);

        if (count($attempts) >= $max) {
            return true;
        }

        $attempts[] = $now;
        $_SESSION[$sessKey] = $attempts;
        return false;
    }

    /**
     * Verify CSRF for JSON API calls — checks X-CSRF-Token header
     * (set by the PWA fetch wrapper) OR the form field.
     */
    protected function verifyCsrfForApi(): bool
    {
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $bodyToken   = $_POST['csrf_token'] ?? '';
        $token       = $headerToken ?: $bodyToken;
        return \App\Core\Csrf::verify($token);
    }

    /**
     * Dual auth: accept session cookie (same-origin PWA) OR Bearer token
     * (external mobile app). Returns user array or sends 401 and exits.
     * $requiredRole = null means any authenticated user.
     */
    protected function requireApiAuth(?string $requiredRole = null): array
    {
        // 1. Try session first (same-origin)
        $user = $this->authenticateSession();

        // 2. Fall back to Bearer token
        if (!$user) {
            // Apache may forward Authorization as REDIRECT_HTTP_AUTHORIZATION
            // or via the RewriteRule E=HTTP_AUTHORIZATION passthrough
            $authHeader = $_SERVER['HTTP_AUTHORIZATION']
                       ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                       ?? '';
            if ($authHeader && stripos($authHeader, 'Bearer ') === 0) {
                $token = trim(substr($authHeader, 7));
                if ($token !== '') {
                    $userModel = new \App\Models\User($this->db);
                    $user = $userModel->findByApiToken($token);
                }
            }
        }

        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        if ($requiredRole !== null && ($user['role'] ?? '') !== $requiredRole) {
            $this->json(['error' => 'Forbidden', 'required_role' => $requiredRole], 403);
        }

        return $user;
    }

    /**
     * Validate a phone number.
     * Allows blank (optional). If provided, must be digits, spaces, dashes, +, or ()
     * and between 7 and 20 characters. Returns null if valid, error string if not.
     */
    protected function validatePhone(string $phone, bool $required = false): ?string
    {
        if ($phone === '') {
            return $required ? 'Phone number is required.' : null;
        }
        if (!preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $phone)) {
            return 'Phone number must contain digits only (spaces, dashes, + and parentheses allowed).';
        }
        return null;
    }

    /**
     * Validate password strength based on user requirements.
     * Must be 8+ chars, including one uppercase, one lowercase, one digit, and one special character.
     * Returns null if valid, or an error message if invalid.
     */
    protected function validatePassword(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number.';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return 'Password must contain at least one special character.';
        }
        return null;
    }
}
