<?php
// FILE: /app/controllers/CampaignController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/Campaign.php';
require_once __DIR__ . '/../models/CampaignMessage.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/Template.php';

/**
 * CampaignController
 * Handles bulk messaging campaigns
 */
class CampaignController extends Controller
{
    /**
     * List campaigns
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $campaignModel = new Campaign();

        // Pagination
        $page = (int)$this->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $campaigns = $campaignModel->getByTenant($tenantId, $perPage, $offset);
        $total = $campaignModel->countByTenant($tenantId);

        $this->render('campaigns/index', [
            'campaigns' => $campaigns,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $tagModel = new Tag();
        $templateModel = new Template();

        $tags = $tagModel->getByTenant($tenantId);
        $templates = $templateModel->getByTenant($tenantId);

        $this->render('campaigns/create', [
            'tags' => $tags,
            'templates' => $templates
        ]);
    }

    /**
     * Store new campaign
     */
    public function store()
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $campaignModel = new Campaign();
        $campaignMessageModel = new CampaignMessage();
        $contactModel = new Contact();
        $templateModel = new Template();

        $data = [
            'tenant_id' => $tenantId,
            'name' => Validator::sanitize($this->post('name')),
            'message_type' => Validator::sanitize($this->post('message_type')),
            'message_body' => Validator::sanitize($this->post('message_body')),
            'template_id' => $this->post('template_id') ?: null,
            'status' => 'draft',
            'scheduled_at' => $this->post('scheduled_at') ?: null,
        ];

        // Validate
        $validator = new Validator($data);
        if (!$validator->validate([
            'name' => 'required|min:2',
            'message_type' => 'required',
        ])) {
            Session::flash('error', 'Validation failed');
            $this->redirect('/campaigns/create');
        }

        // Create campaign
        $campaignId = $campaignModel->create($data);

        // Get target contacts
        $audience = $this->post('audience');
        $contacts = [];

        if ($audience === 'all') {
            $contacts = $contactModel->getByTenant($tenantId, 10000, 0);
        } elseif ($audience === 'tag') {
            $tagId = $this->post('tag_id');
            $contacts = $contactModel->getByTag($tagId, $tenantId);
        }

        // Prepare message body
        $messageBody = $data['message_body'];

        if ($data['template_id']) {
            $template = $templateModel->findById($data['template_id']);
            if ($template) {
                $messageBody = $template['body'];
            }
        }

        // Create campaign messages for each contact
        foreach ($contacts as $contact) {
            // Replace placeholders
            $personalizedMessage = str_replace('{{name}}', $contact['name'], $messageBody);

            $campaignMessageModel->create([
                'campaign_id' => $campaignId,
                'contact_id' => $contact['id'],
                'message_body' => $personalizedMessage,
                'status' => 'pending'
            ]);
        }

        Session::flash('success', 'Campaign created successfully');
        $this->redirect('/campaigns/' . $campaignId);
    }

    /**
     * Show campaign details
     */
    public function show($id)
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $campaignModel = new Campaign();
        $campaignMessageModel = new CampaignMessage();

        $campaign = $campaignModel->getWithDetails($id);

        // Verify tenant ownership
        if (!$campaign || $campaign['tenant_id'] != $tenantId) {
            $this->redirect('/campaigns');
        }

        // Get campaign messages with pagination
        $page = (int)$this->get('page', 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $messages = $campaignMessageModel->getByCampaign($id, $perPage, $offset);

        $this->render('campaigns/show', [
            'campaign' => $campaign,
            'messages' => $messages,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }

    /**
     * Start campaign execution
     */
    public function start($id)
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $campaignModel = new Campaign();

        $campaign = $campaignModel->findById($id);

        // Verify tenant ownership
        if (!$campaign || $campaign['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        // Update status to running
        $campaignModel->updateStatus($id, 'running');

        // Simulate sending (in real app, this would be queued)
        $this->executeCampaign($id);

        Session::flash('success', 'Campaign started successfully');
        $this->redirect('/campaigns/' . $id);
    }

    /**
     * Execute campaign (simulate sending)
     */
    private function executeCampaign($campaignId)
    {
        $campaignMessageModel = new CampaignMessage();
        $campaignModel = new Campaign();

        // Get pending messages (simulate batch processing)
        $messages = $campaignMessageModel->getPending($campaignId, 100);

        foreach ($messages as $message) {
            // Simulate sending (90% success rate)
            $success = (rand(1, 100) <= 90);

            $status = $success ? 'sent' : 'failed';
            $campaignMessageModel->updateStatus($message['id'], $status);
        }

        // If all messages processed, mark campaign as completed
        $pendingCount = $campaignMessageModel->countByStatus($campaignId, 'pending');

        if ($pendingCount === 0) {
            $campaignModel->updateStatus($campaignId, 'completed');
        }
    }
}
