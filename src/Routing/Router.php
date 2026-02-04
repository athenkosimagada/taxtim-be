<?php

namespace App\Routing;

class Router
{
   protected $routes = [];

    public function get(string $path, callable $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function put(string $path, callable $handler)
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete(string $path, callable $handler)
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function dispatch(string $uri, string $method)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        $method = strtoupper($method);

        if (isset($this->routes[$method][$path])) {
            call_user_func($this->routes[$method][$path]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Route not found']);
        }
    }
}