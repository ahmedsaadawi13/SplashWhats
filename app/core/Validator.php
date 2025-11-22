<?php
// FILE: /app/core/Validator.php

/**
 * Validator Class
 * Handles input validation
 */
class Validator
{
    private $errors = [];
    private $data = [];

    /**
     * Constructor
     * @param array $data Data to validate
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Validate data against rules
     * @param array $rules
     * @return bool
     */
    public function validate($rules)
    {
        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a single validation rule
     * @param string $field
     * @param string $rule
     */
    private function applyRule($field, $rule)
    {
        $value = isset($this->data[$field]) ? $this->data[$field] : null;

        // Required
        if ($rule === 'required' && empty($value)) {
            $this->errors[$field][] = ucfirst($field) . ' is required';
            return;
        }

        // Email
        if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = ucfirst($field) . ' must be a valid email';
            return;
        }

        // Numeric
        if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
            $this->errors[$field][] = ucfirst($field) . ' must be numeric';
            return;
        }

        // Min length
        if (strpos($rule, 'min:') === 0) {
            $min = (int)substr($rule, 4);
            if (!empty($value) && strlen($value) < $min) {
                $this->errors[$field][] = ucfirst($field) . " must be at least $min characters";
            }
            return;
        }

        // Max length
        if (strpos($rule, 'max:') === 0) {
            $max = (int)substr($rule, 4);
            if (!empty($value) && strlen($value) > $max) {
                $this->errors[$field][] = ucfirst($field) . " must not exceed $max characters";
            }
            return;
        }
    }

    /**
     * Get validation errors
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Check if validation failed
     * @return bool
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Sanitize string
     * @param string $string
     * @return string
     */
    public static function sanitize($string)
    {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     * @param string $email
     * @return string
     */
    public static function sanitizeEmail($email)
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
}
