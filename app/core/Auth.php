<?php
// FILE: /app/core/Auth.php

/**
 * Auth Class
 * Handles user authentication and authorization
 */
class Auth
{
    /**
     * Attempt to log in a user
     * @param string $email
     * @param string $password
     * @return bool
     */
    public static function attempt($email, $password)
    {
        require_once __DIR__ . '/../models/User.php';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            return false;
        }

        // Store user in session
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('tenant_id', $user['tenant_id']);

        // Update last login
        $userModel->updateLastLogin($user['id']);

        return true;
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    public static function check()
    {
        return Session::has('user_id');
    }

    /**
     * Get current authenticated user
     * @return array|null
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        require_once __DIR__ . '/../models/User.php';

        $userModel = new User();
        return $userModel->findById(Session::get('user_id'));
    }

    /**
     * Get user ID
     * @return int|null
     */
    public static function id()
    {
        return Session::get('user_id');
    }

    /**
     * Logout user
     */
    public static function logout()
    {
        Session::destroy();
    }

    /**
     * Verify API key
     * @param string $apiKey
     * @return array|false Tenant data or false
     */
    public static function verifyApiKey($apiKey)
    {
        require_once __DIR__ . '/../models/Tenant.php';

        $tenantModel = new Tenant();
        return $tenantModel->findByApiKey($apiKey);
    }
}
