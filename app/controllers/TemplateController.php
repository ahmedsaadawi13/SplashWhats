<?php
// FILE: /app/controllers/TemplateController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/Template.php';

/**
 * TemplateController
 * Handles message templates
 */
class TemplateController extends Controller
{
    /**
     * List templates
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $templateModel = new Template();

        $templates = $templateModel->getByTenant($tenantId);

        $this->render('templates/index', ['templates' => $templates]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->requireAuth();

        $this->render('templates/create');
    }

    /**
     * Store new template
     */
    public function store()
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $templateModel = new Template();

        $data = [
            'tenant_id' => $tenantId,
            'name' => Validator::sanitize($this->post('name')),
            'category' => Validator::sanitize($this->post('category')),
            'language' => Validator::sanitize($this->post('language', 'en')),
            'body' => Validator::sanitize($this->post('body')),
        ];

        // Validate
        $validator = new Validator($data);
        if (!$validator->validate([
            'name' => 'required|min:2',
            'category' => 'required',
            'body' => 'required|min:10',
        ])) {
            Session::flash('error', 'Validation failed');
            $this->redirect('/templates/create');
        }

        $templateModel->create($data);

        Session::flash('success', 'Template created successfully');
        $this->redirect('/templates');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $templateModel = new Template();

        $template = $templateModel->findById($id);

        // Verify tenant ownership
        if (!$template || $template['tenant_id'] != $tenantId) {
            $this->redirect('/templates');
        }

        $this->render('templates/edit', ['template' => $template]);
    }

    /**
     * Update template
     */
    public function update($id)
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $templateModel = new Template();

        $template = $templateModel->findById($id);

        // Verify tenant ownership
        if (!$template || $template['tenant_id'] != $tenantId) {
            $this->redirect('/templates');
        }

        $data = [
            'name' => Validator::sanitize($this->post('name')),
            'category' => Validator::sanitize($this->post('category')),
            'language' => Validator::sanitize($this->post('language')),
            'body' => Validator::sanitize($this->post('body')),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $templateModel->update($id, $data);

        Session::flash('success', 'Template updated successfully');
        $this->redirect('/templates');
    }

    /**
     * Delete template
     */
    public function delete($id)
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $templateModel = new Template();

        $template = $templateModel->findById($id);

        // Verify tenant ownership
        if (!$template || $template['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $templateModel->delete($id);

        Session::flash('success', 'Template deleted successfully');
        $this->redirect('/templates');
    }
}
