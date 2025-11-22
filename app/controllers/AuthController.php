<?php
// FILE: /app/controllers/AuthController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Tenant.php';

/**
 * AuthController
 * Handles authentication: login, register, logout
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login');
    }

    /**
     * Process login
     */
    public function login()
    {
        // Validate CSRF token
        if (!CSRF::validateToken($this->post('csrf_token'))) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/login');
        }

        $email = Validator::sanitizeEmail($this->post('email'));
        $password = $this->post('password');

        // Validate input
        $validator = new Validator(['email' => $email, 'password' => $password]);
        if (!$validator->validate(['email' => 'required|email', 'password' => 'required'])) {
            Session::flash('error', 'Please provide valid credentials');
            $this->redirect('/login');
        }

        // Attempt login
        if (Auth::attempt($email, $password)) {
            $this->redirect('/dashboard');
        } else {
            Session::flash('error', 'Invalid credentials or account inactive');
            $this->redirect('/login');
        }
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/register');
    }

    /**
     * Process registration
     */
    public function register()
    {
        // Validate CSRF token
        if (!CSRF::validateToken($this->post('csrf_token'))) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/register');
        }

        $data = [
            'company_name' => Validator::sanitize($this->post('company_name')),
            'name' => Validator::sanitize($this->post('name')),
            'email' => Validator::sanitizeEmail($this->post('email')),
            'password' => $this->post('password'),
        ];

        // Validate input
        $validator = new Validator($data);
        if (!$validator->validate([
            'company_name' => 'required|min:2',
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ])) {
            Session::flash('error', 'Please fix validation errors');
            $this->redirect('/register');
        }

        // Create tenant
        $tenantModel = new Tenant();
        $tenantId = $tenantModel->create([
            'name' => $data['company_name'],
            'status' => 'active'
        ]);

        // Create user
        $userModel = new User();
        $userId = $userModel->create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'tenant_admin',
            'status' => 'active'
        ]);

        // Auto-login
        Auth::attempt($data['email'], $data['password']);

        Session::flash('success', 'Account created successfully!');
        $this->redirect('/dashboard');
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
