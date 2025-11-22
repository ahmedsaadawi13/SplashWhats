<?php
// FILE: /app/controllers/ContactController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Tag.php';

/**
 * ContactController
 * Handles contact CRUD operations and imports
 */
class ContactController extends Controller
{
    /**
     * List contacts
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $contactModel = new Contact();
        $tagModel = new Tag();

        // Pagination
        $page = (int)$this->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Filters
        $filters = [
            'status' => $this->get('status'),
            'tag_id' => $this->get('tag_id'),
            'search' => $this->get('search'),
        ];

        $contacts = $contactModel->getByTenant($tenantId, $perPage, $offset, $filters);
        $total = $contactModel->countByTenant($tenantId, $filters);
        $tags = $tagModel->getByTenant($tenantId);

        $this->render('contacts/index', [
            'contacts' => $contacts,
            'tags' => $tags,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters
        ]);
    }

    /**
     * Show create contact form
     */
    public function create()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $tagModel = new Tag();
        $tags = $tagModel->getByTenant($tenantId);

        $this->render('contacts/create', ['tags' => $tags]);
    }

    /**
     * Store new contact
     */
    public function store()
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $contactModel = new Contact();

        $data = [
            'tenant_id' => $tenantId,
            'name' => Validator::sanitize($this->post('name')),
            'phone' => Validator::sanitize($this->post('phone')),
            'email' => Validator::sanitizeEmail($this->post('email')),
            'country' => Validator::sanitize($this->post('country')),
            'timezone' => Validator::sanitize($this->post('timezone', 'UTC')),
            'status' => 'active',
            'notes' => Validator::sanitize($this->post('notes')),
        ];

        // Validate
        $validator = new Validator($data);
        if (!$validator->validate([
            'name' => 'required|min:2',
            'phone' => 'required|min:10',
        ])) {
            Session::flash('error', 'Validation failed');
            $this->redirect('/contacts/create');
        }

        // Create contact
        $contactId = $contactModel->create($data);

        // Attach tags
        $tagIds = $this->post('tags', []);
        if (!empty($tagIds) && is_array($tagIds)) {
            foreach ($tagIds as $tagId) {
                $contactModel->attachTag($contactId, $tagId);
            }
        }

        Session::flash('success', 'Contact created successfully');
        $this->redirect('/contacts');
    }

    /**
     * Show edit contact form
     */
    public function edit($id)
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $contactModel = new Contact();
        $tagModel = new Tag();

        $contact = $contactModel->findById($id);

        // Verify tenant ownership
        if (!$contact || $contact['tenant_id'] != $tenantId) {
            $this->redirect('/contacts');
        }

        $tags = $tagModel->getByTenant($tenantId);
        $contactTags = $contactModel->getTags($id);

        $this->render('contacts/edit', [
            'contact' => $contact,
            'tags' => $tags,
            'contactTags' => $contactTags
        ]);
    }

    /**
     * Update contact
     */
    public function update($id)
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $contactModel = new Contact();

        $contact = $contactModel->findById($id);

        // Verify tenant ownership
        if (!$contact || $contact['tenant_id'] != $tenantId) {
            $this->redirect('/contacts');
        }

        $data = [
            'name' => Validator::sanitize($this->post('name')),
            'phone' => Validator::sanitize($this->post('phone')),
            'email' => Validator::sanitizeEmail($this->post('email')),
            'country' => Validator::sanitize($this->post('country')),
            'timezone' => Validator::sanitize($this->post('timezone')),
            'status' => Validator::sanitize($this->post('status')),
            'notes' => Validator::sanitize($this->post('notes')),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $contactModel->update($id, $data);

        Session::flash('success', 'Contact updated successfully');
        $this->redirect('/contacts');
    }

    /**
     * Delete contact
     */
    public function delete($id)
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $contactModel = new Contact();

        $contact = $contactModel->findById($id);

        // Verify tenant ownership
        if (!$contact || $contact['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $contactModel->delete($id);

        Session::flash('success', 'Contact deleted successfully');
        $this->redirect('/contacts');
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        $this->requireAuth();

        $this->render('contacts/import');
    }

    /**
     * Process CSV import
     */
    public function import()
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();

        // Handle file upload
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please upload a valid CSV file');
            $this->redirect('/contacts/import');
        }

        $file = $_FILES['csv_file'];

        // Validate file type
        $allowedTypes = ['text/csv', 'text/plain', 'application/csv'];
        if (!in_array($file['type'], $allowedTypes)) {
            Session::flash('error', 'Invalid file type. Please upload CSV file');
            $this->redirect('/contacts/import');
        }

        // Process CSV
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // Skip header row
        $imported = 0;

        $contactModel = new Contact();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue; // Skip invalid rows

            $data = [
                'tenant_id' => $tenantId,
                'name' => Validator::sanitize($row[0] ?? ''),
                'phone' => Validator::sanitize($row[1] ?? ''),
                'email' => Validator::sanitizeEmail($row[2] ?? ''),
                'country' => Validator::sanitize($row[3] ?? ''),
                'status' => 'active',
            ];

            // Basic validation
            if (empty($data['name']) || empty($data['phone'])) {
                continue;
            }

            $contactModel->create($data);
            $imported++;
        }

        fclose($handle);

        Session::flash('success', "Imported $imported contacts successfully");
        $this->redirect('/contacts');
    }
}
