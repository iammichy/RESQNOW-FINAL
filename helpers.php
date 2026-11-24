<?php

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $path = ltrim($path, '/');
        if ($path === '') {
            return $base === '' ? '/' : $base . '/';
        }
        return $base === '' ? '/' . $path : $base . '/' . $path;
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        return $_POST[$key] ?? $default;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return App\Core\Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return App\Core\Csrf::token();
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('check')) {
    function check(): bool
    {
        return isset($_SESSION['user']);
    }
}

if (!function_exists('current_route')) {
    function current_route(): string
    {
        $url = $_GET['url'] ?? '';
        return '/' . ltrim($url, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return base_url('public/' . ltrim($path, '/'));
    }
}
