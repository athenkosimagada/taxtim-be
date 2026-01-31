<?php

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $paramNames = [];
            $pattern = $this->convertPathToRegex($route['path'], $paramNames);

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                $params = array_combine($paramNames, $matches) ?: [];

                call_user_func_array(
                    $route['handler'],
                    array_values($params)
                );

                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    private function convertPathToRegex(string $path, array &$paramNames): string
    {
        $pattern = preg_replace_callback(
            '#\{(\w+)(?::([^}]+))?\}#',
            function ($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                $regex = $matches[2] ?? '[^/]+';
                return '(' . $regex . ')';
            },
            $path
        );

        return '#^' . $pattern . '$#';
    }
}
