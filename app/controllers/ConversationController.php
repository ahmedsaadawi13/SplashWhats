<?php
// FILE: /app/controllers/ConversationController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/User.php';

/**
 * ConversationController
 * Handles conversation inbox and messaging
 */
class ConversationController extends Controller
{
    /**
     * List conversations (inbox)
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $conversationModel = new Conversation();
        $userModel = new User();

        // Pagination
        $page = (int)$this->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Filters
        $filters = [
            'status' => $this->get('status'),
            'assigned_to' => $this->get('assigned_to'),
            'unread' => $this->get('unread'),
        ];

        $conversations = $conversationModel->getByTenant($tenantId, $perPage, $offset, $filters);
        $total = $conversationModel->countByTenant($tenantId, $filters);
        $agents = $userModel->getAgentsByTenant($tenantId);

        $this->render('conversations/index', [
            'conversations' => $conversations,
            'agents' => $agents,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'filters' => $filters
        ]);
    }

    /**
     * Show single conversation
     */
    public function show($id)
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $conversationModel = new Conversation();
        $messageModel = new Message();
        $userModel = new User();

        $conversation = $conversationModel->findById($id);

        // Verify tenant ownership
        if (!$conversation || $conversation['tenant_id'] != $tenantId) {
            $this->redirect('/conversations');
        }

        // Get messages
        $messages = $messageModel->getByConversation($id);

        // Mark messages as read
        $messageModel->markAsRead($id);

        // Get agents for assignment
        $agents = $userModel->getAgentsByTenant($tenantId);

        $this->render('conversations/show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'agents' => $agents
        ]);
    }

    /**
     * Send message in conversation
     */
    public function sendMessage($id)
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $userId = Auth::id();
        $conversationModel = new Conversation();
        $messageModel = new Message();

        $conversation = $conversationModel->findById($id);

        // Verify tenant ownership
        if (!$conversation || $conversation['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $body = Validator::sanitize($this->post('body'));

        if (empty($body)) {
            $this->json(['error' => 'Message cannot be empty'], 400);
        }

        // Create message
        $messageModel->create([
            'conversation_id' => $id,
            'user_id' => $userId,
            'direction' => 'outbound',
            'body' => $body,
            'status' => 'sent'
        ]);

        // Update conversation timestamp
        $conversationModel->update($id, ['updated_at' => date('Y-m-d H:i:s')]);

        Session::flash('success', 'Message sent successfully');
        $this->redirect('/conversations/' . $id);
    }

    /**
     * Assign conversation to agent
     */
    public function assign($id)
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $conversationModel = new Conversation();

        $conversation = $conversationModel->findById($id);

        // Verify tenant ownership
        if (!$conversation || $conversation['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $agentId = (int)$this->post('agent_id');

        $conversationModel->assignToAgent($id, $agentId);

        Session::flash('success', 'Conversation assigned successfully');
        $this->redirect('/conversations/' . $id);
    }

    /**
     * Update conversation status
     */
    public function updateStatus($id)
    {
        $this->requireAuth();

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $conversationModel = new Conversation();

        $conversation = $conversationModel->findById($id);

        // Verify tenant ownership
        if (!$conversation || $conversation['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $status = $this->post('status');

        $conversationModel->updateStatus($id, $status);

        Session::flash('success', 'Status updated successfully');
        $this->redirect('/conversations/' . $id);
    }
}
