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

        if (!isset($this->routes[$method])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Route not found']);
            return;
        }

        foreach ($this->routes[$method] as $route => $callback) {
            $pattern = preg_replace('#\{[\w]+\}#', '([^/]+)', $route); 
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); 

                preg_match_all('#\{([\w]+)\}#', $route, $paramNames);
                $params = [];
                if (!empty($paramNames[1])) {
                    foreach ($paramNames[1] as $index => $name) {
                        $params[$name] = $matches[$index] ?? null;
                    }
                }

                call_user_func($callback, $params);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Route not found']);
    }
}