<?php
// FILE: /app/core/Controller.php

/**
 * Base Controller Class
 * All controllers extend this class
 */
class Controller
{
    protected $view;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Render a view
     * @param string $viewPath
     * @param array $data
     */
    protected function render($viewPath, $data = [])
    {
        $this->view->render($viewPath, $data);
    }

    /**
     * Redirect to a URL
     * @param string $url
     */
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    /**
     * Return JSON response
     * @param mixed $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get POST data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Get GET data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    protected function isAuthenticated()
    {
        return Auth::check();
    }

    /**
     * Require authentication - redirect to login if not authenticated
     */
    protected function requireAuth()
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    /**
     * Get current authenticated user
     * @return array|null
     */
    protected function currentUser()
    {
        return Auth::user();
    }

    /**
     * Get current tenant ID
     * @return int|null
     */
    protected function currentTenantId()
    {
        $user = Auth::user();
        return $user ? $user['tenant_id'] : null;
    }

    /**
     * Check if current user has role
     * @param string|array $roles
     * @return bool
     */
    protected function hasRole($roles)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($user['role'], $roles);
        }

        return $user['role'] === $roles;
    }

    /**
     * Require specific role(s)
     * @param string|array $roles
     */
    protected function requireRole($roles)
    {
        if (!$this->hasRole($roles)) {
            $this->json(['error' => 'Unauthorized'], 403);
        }
    }
}
