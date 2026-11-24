<?php

namespace App\Core;

use PDO;

class Router
{
    private array $routes = [];
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function get(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $url, string $method): void
    {
        $url = parse_url($url, PHP_URL_PATH);
        $url = rtrim($url, '/');
        if ($url === '') {
            $url = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->buildPattern($route['path']);
            if (preg_match($pattern, $url, $matches)) {
                array_shift($matches);
                $this->invoke($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function buildPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function invoke(string $handler, array $params): void
    {
        [$controllerName, $action] = explode('@', $handler);
        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            die("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass($this->db);

        if (!method_exists($controller, $action)) {
            http_response_code(500);
            die("Method not found: {$controllerClass}::{$action}");
        }

        call_user_func_array([$controller, $action], array_values($params));
    }
}
