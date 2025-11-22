<?php
// FILE: /app/controllers/WebhookController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Tenant.php';

/**
 * WebhookController
 * Handles incoming webhooks (simulated WhatsApp messages)
 */
class WebhookController extends Controller
{
    /**
     * Handle incoming message webhook
     * POST /api/webhook/incoming
     */
    public function incoming()
    {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Validate required fields
        if (empty($input['phone']) || empty($input['message']) || empty($input['tenant_api_key'])) {
            $this->json(['error' => 'Phone, message, and tenant_api_key are required'], 400);
        }

        $phone = Validator::sanitize($input['phone']);
        $message = Validator::sanitize($input['message']);
        $apiKey = $input['tenant_api_key'];

        // Find tenant by API key
        $tenantModel = new Tenant();
        $tenant = $tenantModel->findByApiKey($apiKey);

        if (!$tenant) {
            $this->json(['error' => 'Invalid tenant API key'], 401);
        }

        $tenantId = $tenant['id'];

        // Find or create contact
        $contactModel = new Contact();
        $contact = $contactModel->findByPhone($phone, $tenantId);

        if (!$contact) {
            $contactId = $contactModel->create([
                'tenant_id' => $tenantId,
                'phone' => $phone,
                'name' => $phone,
                'status' => 'active'
            ]);
            $contact = $contactModel->findById($contactId);
        }

        // Find or create conversation
        $conversationModel = new Conversation();
        $conversation = $conversationModel->findOrCreate($contact['id'], $tenantId);

        // Create inbound message
        $messageModel = new Message();
        $messageId = $messageModel->create([
            'conversation_id' => $conversation['id'],
            'direction' => 'inbound',
            'body' => $message,
            'status' => 'unread'
        ]);

        // Return success response
        $this->json([
            'success' => true,
            'message' => 'Message received',
            'message_id' => $messageId
        ], 200);
    }
}
