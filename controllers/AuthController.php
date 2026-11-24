<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\User;

class AuthController extends Controller
{
    public function login(): void
    {
        if ($this->isPost()) {
            $this->handleLogin();
            return;
        }

        if (check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'pageTitle' => 'Sign In',
            'errors'    => [],
            'loginError' => '',
        ], 'auth');
    }

    private function handleLogin(): void
    {
        $token = $this->input('csrf_token');
        if (!Csrf::verify($token)) {
            $this->view('auth/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Invalid session. Please refresh and try again.',
            ], 'auth');
            return;
        }

        // Rate limit: 5 attempts per minute per IP
        if ($this->isRateLimited('login', 5, 60)) {
            $this->view('auth/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Too many login attempts. Please wait a moment and try again.',
            ], 'auth');
            return;
        }

        $identifier = trim((string) $this->input('identifier', ''));
        $password   = (string) $this->input('password', '');
        $remember   = $this->input('remember');

        $errors = [];

        if ($identifier === '') {
            $errors['identifier'] = 'Email or phone is required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if (!empty($errors)) {
            $this->view('auth/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => $errors,
                'loginError' => '',
            ], 'auth');
            return;
        }

        $userModel = new User($this->db);
        $user = $userModel->findByEmailOrPhone($identifier);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->view('auth/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Invalid credentials. Please check your email/phone and password.',
            ], 'auth');
            return;
        }

        if ($user['status'] === 'pending') {
            $this->view('auth/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Your account is pending barangay approval.',
            ], 'auth');
            return;
        }

        if ($user['status'] !== 'approved') {
            $this->view('auth/login', [
                'pageTitle'  => 'Sign In',
                'errors'     => [],
                'loginError' => 'Your account is not active. Contact the barangay administrator.',
            ], 'auth');
            return;
        }

        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        Csrf::token();

        $_SESSION['user'] = [
            'id'     => (int) $user['id'],
            'name'   => $user['name'],
            'email'  => $user['email'],
            'phone'  => $user['phone'],
            'role'   => $user['role'],
            'status' => $user['status'],
        ];

        if ($remember) {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + (86400 * 30), $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        $this->redirect('/dashboard');
    }

    public function forgotPassword(): void
    {
        if ($this->isPost()) {
            $token = $this->input('csrf_token');
            if (!Csrf::verify($token)) {
                $this->view('auth/forgot-password', [
                    'pageTitle' => 'Forgot Password',
                    'errors'    => ['identifier' => 'Invalid session. Please refresh and try again.'],
                    'sent'      => false,
                ], 'auth');
                return;
            }

            $identifier = trim((string) $this->input('identifier', ''));
            $errors = [];

            if ($identifier === '') {
                $errors['identifier'] = 'Email or phone is required.';
            }

            if (!empty($errors)) {
                $this->view('auth/forgot-password', [
                    'pageTitle' => 'Forgot Password',
                    'errors'    => $errors,
                    'sent'      => false,
                ], 'auth');
                return;
            }

            $this->view('auth/forgot-password', [
                'pageTitle' => 'Forgot Password',
                'errors'    => [],
                'sent'      => true,
            ], 'auth');
            return;
        }

        $this->view('auth/forgot-password', [
            'pageTitle' => 'Forgot Password',
            'errors'    => [],
            'sent'      => false,
        ], 'auth');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect('/login');
    }
}
