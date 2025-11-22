<?php
// FILE: /app/controllers/TagController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/Tag.php';

/**
 * TagController
 * Handles tag management for contact segmentation
 */
class TagController extends Controller
{
    /**
     * List tags
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $tagModel = new Tag();

        $tags = $tagModel->getByTenant($tenantId);

        $this->render('tags/index', ['tags' => $tags]);
    }

    /**
     * Create tag
     */
    public function store()
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $tagModel = new Tag();

        $data = [
            'tenant_id' => $tenantId,
            'name' => Validator::sanitize($this->post('name')),
            'color' => Validator::sanitize($this->post('color', '#3B82F6')),
        ];

        // Validate
        $validator = new Validator($data);
        if (!$validator->validate(['name' => 'required|min:2'])) {
            Session::flash('error', 'Tag name is required');
            $this->redirect('/tags');
        }

        $tagModel->create($data);

        Session::flash('success', 'Tag created successfully');
        $this->redirect('/tags');
    }

    /**
     * Update tag
     */
    public function update($id)
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $tagModel = new Tag();

        $tag = $tagModel->findById($id);

        // Verify tenant ownership
        if (!$tag || $tag['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $data = [
            'name' => Validator::sanitize($this->post('name')),
            'color' => Validator::sanitize($this->post('color')),
        ];

        $tagModel->update($id, $data);

        Session::flash('success', 'Tag updated successfully');
        $this->redirect('/tags');
    }

    /**
     * Delete tag
     */
    public function delete($id)
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $tagModel = new Tag();

        $tag = $tagModel->findById($id);

        // Verify tenant ownership
        if (!$tag || $tag['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $tagModel->delete($id);

        Session::flash('success', 'Tag deleted successfully');
        $this->redirect('/tags');
    }
}
