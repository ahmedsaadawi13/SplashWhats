<?php
// FILE: /app/controllers/ApiController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/Message.php';

/**
 * ApiController
 * Handles REST API endpoints for messaging
 */
class ApiController extends Controller
{
    /**
     * Send a message via API
     * POST /api/send-message
     * Headers: X-API-KEY
     * Body: {phone, message, template_id (optional)}
     */
    public function sendMessage()
    {
        // Get API key from header
        $apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : null;

        if (!$apiKey) {
            $this->json(['error' => 'API key required'], 401);
        }

        // Verify API key
        $tenant = Auth::verifyApiKey($apiKey);

        if (!$tenant) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Validate required fields
        if (empty($input['phone']) || empty($input['message'])) {
            $this->json(['error' => 'Phone and message are required'], 400);
        }

        $phone = Validator::sanitize($input['phone']);
        $message = Validator::sanitize($input['message']);
        $tenantId = $tenant['id'];

        // Find or create contact
        $contactModel = new Contact();
        $contact = $contactModel->findByPhone($phone, $tenantId);

        if (!$contact) {
            $contactId = $contactModel->create([
                'tenant_id' => $tenantId,
                'phone' => $phone,
                'name' => $phone, // Use phone as name if contact doesn't exist
                'status' => 'active'
            ]);
            $contact = $contactModel->findById($contactId);
        }

        // Find or create conversation
        $conversationModel = new Conversation();
        $conversation = $conversationModel->findOrCreate($contact['id'], $tenantId);

        // Create message
        $messageModel = new Message();
        $messageId = $messageModel->create([
            'conversation_id' => $conversation['id'],
            'direction' => 'outbound',
            'body' => $message,
            'status' => 'sent'
        ]);

        // Return success response
        $this->json([
            'success' => true,
            'message_id' => $messageId,
            'contact_id' => $contact['id'],
            'conversation_id' => $conversation['id'],
            'status' => 'sent'
        ], 200);
    }

    /**
     * Get API documentation
     */
    public function docs()
    {
        $docs = [
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/api/send-message',
                    'description' => 'Send a message to a contact',
                    'headers' => [
                        'X-API-KEY' => 'Your tenant API key'
                    ],
                    'body' => [
                        'phone' => 'Contact phone number (required)',
                        'message' => 'Message body (required)',
                        'template_id' => 'Template ID (optional)'
                    ],
                    'response' => [
                        'success' => true,
                        'message_id' => 123,
                        'contact_id' => 456,
                        'conversation_id' => 789,
                        'status' => 'sent'
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/webhook/incoming',
                    'description' => 'Receive incoming messages (webhook)',
                    'body' => [
                        'phone' => 'Contact phone number',
                        'message' => 'Message body',
                        'direction' => 'inbound'
                    ],
                    'response' => [
                        'success' => true,
                        'message' => 'Message received'
                    ]
                ]
            ],
            'note' => 'This is a simulated integration and not an official WhatsApp Business API client.'
        ];

        $this->json($docs);
    }
}
