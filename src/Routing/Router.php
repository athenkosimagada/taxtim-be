<?php

namespace App\Routing;

class Router
{
    protected array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $method = strtoupper($method);

        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = preg_replace('#\{[\w]+\}#', '([^/]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                preg_match_all('#\{([\w]+)\}#', $route, $paramNames);
                $params = [];

                foreach ($paramNames[1] ?? [] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }

                $this->invokeHandler($handler, $params);
                return;
            }
        }

        $this->notFound();
    }

    private function invokeHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            call_user_func_array([$controller, $method], [$params]);
            return;
        }

        call_user_func($handler, $params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Route not found'
        ]);
    }
}
