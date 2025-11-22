<?php
// FILE: /app/core/CSRF.php

/**
 * CSRF Protection Class
 * Generates and validates CSRF tokens
 */
class CSRF
{
    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateToken()
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    /**
     * Get CSRF token
     * @return string
     */
    public static function getToken()
    {
        return self::generateToken();
    }

    /**
     * Validate CSRF token
     * @param string $token
     * @return bool
     */
    public static function validateToken($token)
    {
        $sessionToken = Session::get('csrf_token');

        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Generate CSRF input field
     * @return string
     */
    public static function field()
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
