<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        $class = substr($class, strlen($prefix));
    }

    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($path)) {
        require_once $path;
        error_log("Loaded class $class from $path");
    } else {
        error_log("Class $class not found at $path");
    }
});
