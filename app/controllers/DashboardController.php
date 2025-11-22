<?php
// FILE: /app/controllers/DashboardController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Campaign.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/Tag.php';

/**
 * DashboardController
 * Displays main dashboard with analytics
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $user = $this->currentUser();

        // Get statistics
        $contactModel = new Contact();
        $messageModel = new Message();
        $campaignModel = new Campaign();
        $conversationModel = new Conversation();
        $tagModel = new Tag();

        $stats = [
            'total_contacts' => $contactModel->countByTenant($tenantId),
            'messages_this_month' => $messageModel->countSentThisMonth($tenantId),
            'active_campaigns' => $campaignModel->countActive($tenantId),
            'open_conversations' => $conversationModel->countByTenant($tenantId, ['status' => 'open']),
        ];

        // Get messages per day for chart (last 30 days)
        $messagesPerDay = $messageModel->getMessagesPerDay($tenantId, 30);

        // Get top tags
        $topTags = $tagModel->getTopTags($tenantId, 5);

        $this->render('dashboard/index', [
            'user' => $user,
            'stats' => $stats,
            'messagesPerDay' => $messagesPerDay,
            'topTags' => $topTags
        ]);
    }
}
