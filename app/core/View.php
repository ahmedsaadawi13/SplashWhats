<?php
// FILE: /app/core/View.php

/**
 * View Class
 * Handles rendering of views
 */
class View
{
    /**
     * Render a view file
     * @param string $viewPath Path to view (e.g., 'auth/login')
     * @param array $data Data to pass to the view
     */
    public function render($viewPath, $data = [])
    {
        // Extract data to make variables available in the view
        extract($data);

        // Build the full path to the view file
        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: $viewPath");
        }

        // Include the view file
        require $viewFile;
    }

    /**
     * Escape HTML to prevent XSS
     * @param string $string
     * @return string
     */
    public static function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format date
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        if (empty($date)) {
            return '';
        }
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Format number
     * @param float $number
     * @param int $decimals
     * @return string
     */
    public static function formatNumber($number, $decimals = 2)
    {
        return number_format($number, $decimals);
    }
}
