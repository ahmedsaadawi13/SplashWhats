<?php
// FILE: /app/core/Router.php

/**
 * Router Class
 * Handles URL routing and dispatching to controllers
 */
class Router
{
    private $routes = [];

    /**
     * Add a GET route
     * @param string $uri
     * @param string $controller
     * @param string $method
     */
    public function get($uri, $controller, $method)
    {
        $this->routes['GET'][$uri] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Add a POST route
     * @param string $uri
     * @param string $controller
     * @param string $method
     */
    public function post($uri, $controller, $method)
    {
        $this->routes['POST'][$uri] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Dispatch the request to appropriate controller
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash except for root
        if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        // Check for exact match first
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $route = $this->routes[$requestMethod][$requestUri];
            $this->callController($route['controller'], $route['method']);
            return;
        }

        // Check for dynamic routes
        foreach ($this->routes[$requestMethod] as $uri => $route) {
            $pattern = $this->convertToRegex($uri);
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                $this->callController($route['controller'], $route['method'], $matches);
                return;
            }
        }

        // No route found - 404
        http_response_code(404);
        echo "404 - Page Not Found";
    }

    /**
     * Convert route URI to regex pattern
     * @param string $uri
     * @return string
     */
    private function convertToRegex($uri)
    {
        // Convert {param} to regex capture group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Call the controller method
     * @param string $controller
     * @param string $method
     * @param array $params
     */
    private function callController($controller, $method, $params = [])
    {
        $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';

        if (!file_exists($controllerFile)) {
            die("Controller not found: $controller");
        }

        require_once $controllerFile;

        if (!class_exists($controller)) {
            die("Controller class not found: $controller");
        }

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $method)) {
            die("Method not found: $method in controller $controller");
        }

        call_user_func_array([$controllerInstance, $method], $params);
    }
}
