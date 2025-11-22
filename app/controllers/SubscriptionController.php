<?php
// FILE: /app/controllers/SubscriptionController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/UsageTracker.php';

/**
 * SubscriptionController
 * Handles subscription management
 */
class SubscriptionController extends Controller
{
    /**
     * Show subscription details
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->currentTenantId();
        $subscriptionModel = new Subscription();
        $planModel = new Plan();
        $usageModel = new UsageTracker();

        $subscription = $subscriptionModel->getActiveByStan($tenantId);
        $plans = $planModel->getActivePlans();
        $usage = $usageModel->getCurrentMonthUsage($tenantId);

        $this->render('subscriptions/index', [
            'subscription' => $subscription,
            'plans' => $plans,
            'usage' => $usage
        ]);
    }

    /**
     * Upgrade/change plan
     */
    public function changePlan()
    {
        $this->requireAuth();
        $this->requireRole('tenant_admin');

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $tenantId = $this->currentTenantId();
        $planId = (int)$this->post('plan_id');

        $subscriptionModel = new Subscription();

        // Create new subscription
        $subscriptionModel->create([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime('+1 month'))
        ]);

        Session::flash('success', 'Subscription updated successfully');
        $this->redirect('/subscriptions');
    }
}
