<?php
// FILE: /app/controllers/UserController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/User.php';

/**
 * UserController
 * Handles user management (agents, admins)
 */
class UserController extends Controller
{
    /**
     * List users
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'platform_admin']);

        $tenantId = $this->currentTenantId();
        $userModel = new User();

        $users = $userModel->getByTenant($tenantId);

        $this->render('users/index', ['users' => $users]);
    }

    /**
     * Create new user
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole('tenant_admin');

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $userModel = new User();

        $data = [
            'tenant_id' => $tenantId,
            'name' => Validator::sanitize($this->post('name')),
            'email' => Validator::sanitizeEmail($this->post('email')),
            'password' => $this->post('password'),
            'role' => Validator::sanitize($this->post('role', 'agent')),
            'status' => 'active'
        ];

        // Validate
        $validator = new Validator($data);
        if (!$validator->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ])) {
            Session::flash('error', 'Validation failed');
            $this->redirect('/users');
        }

        $userModel->create($data);

        Session::flash('success', 'User created successfully');
        $this->redirect('/users');
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole('tenant_admin');

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $userModel = new User();

        $user = $userModel->findById($id);

        // Verify tenant ownership
        if (!$user || $user['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $data = [
            'name' => Validator::sanitize($this->post('name')),
            'email' => Validator::sanitizeEmail($this->post('email')),
            'role' => Validator::sanitize($this->post('role')),
            'status' => Validator::sanitize($this->post('status')),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update password only if provided
        $password = $this->post('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);

        Session::flash('success', 'User updated successfully');
        $this->redirect('/users');
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole('tenant_admin');

        $tenantId = $this->currentTenantId();
        $userModel = new User();

        $user = $userModel->findById($id);

        // Verify tenant ownership
        if (!$user || $user['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent deleting yourself
        if ($user['id'] == Auth::id()) {
            Session::flash('error', 'Cannot delete your own account');
            $this->redirect('/users');
        }

        $userModel->delete($id);

        Session::flash('success', 'User deleted successfully');
        $this->redirect('/users');
    }
}
