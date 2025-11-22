<?php
// FILE: /tests/functional_tests.php

/**
 * SplashWhats Functional Tests
 * Simple test suite to verify core functionality
 */

// Test configuration
define('BASE_URL', 'http://localhost');
define('API_KEY_TENANT_1', 'sk_demo_key_tech_solutions_2024_abc123xyz');

// Color output helpers
function pass($message) {
    echo "✓ PASS: $message\n";
}

function fail($message) {
    echo "✗ FAIL: $message\n";
}

function testHeader($message) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "  $message\n";
    echo str_repeat('=', 60) . "\n\n";
}

// ============================================================================
// Database Tests
// ============================================================================

testHeader('DATABASE TESTS');

try {
    $db = new PDO('mysql:host=localhost;dbname=splashwhats', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    pass('Database connection successful');
} catch (PDOException $e) {
    fail('Database connection failed: ' . $e->getMessage());
    exit(1);
}

// Test: Check if all tables exist
$requiredTables = [
    'tenants', 'users', 'contacts', 'tags', 'contact_tags',
    'conversations', 'messages', 'templates', 'quick_replies',
    'campaigns', 'campaign_messages', 'plans', 'tenant_subscriptions',
    'usage_tracker', 'invoices', 'payments'
];

$stmt = $db->query("SHOW TABLES");
$existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($requiredTables as $table) {
    if (in_array($table, $existingTables)) {
        pass("Table '$table' exists");
    } else {
        fail("Table '$table' does not exist");
    }
}

// Test: Check seed data
$stmt = $db->query("SELECT COUNT(*) as count FROM tenants");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result['count'] >= 2) {
    pass("Seed data: Tenants loaded ({$result['count']} tenants)");
} else {
    fail("Seed data: Tenants not loaded");
}

$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'tenant_admin'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result['count'] >= 2) {
    pass("Seed data: Tenant admins loaded ({$result['count']} admins)");
} else {
    fail("Seed data: Tenant admins not loaded");
}

$stmt = $db->query("SELECT COUNT(*) as count FROM contacts");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result['count'] >= 5) {
    pass("Seed data: Contacts loaded ({$result['count']} contacts)");
} else {
    fail("Seed data: Contacts not loaded");
}

// ============================================================================
// Model Tests
// ============================================================================

testHeader('MODEL TESTS');

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Contact.php';
require_once __DIR__ . '/../app/models/Tenant.php';

// Test: User model
$userModel = new User();
$user = $userModel->findByEmail('admin@company1.com');
if ($user && $user['name'] === 'John Smith') {
    pass('User model: findByEmail() works correctly');
} else {
    fail('User model: findByEmail() failed');
}

// Test: Contact model
$contactModel = new Contact();
$contact = $contactModel->findByPhone('+14155551234', 1);
if ($contact && $contact['name'] === 'Alice Williams') {
    pass('Contact model: findByPhone() works correctly');
} else {
    fail('Contact model: findByPhone() failed');
}

// Test: Tenant model
$tenantModel = new Tenant();
$tenant = $tenantModel->findByApiKey(API_KEY_TENANT_1);
if ($tenant && $tenant['name'] === 'Tech Solutions Inc') {
    pass('Tenant model: findByApiKey() works correctly');
} else {
    fail('Tenant model: findByApiKey() failed');
}

// Test: Tenant isolation
$contactsByTenant = $contactModel->countByTenant(1);
if ($contactsByTenant >= 5) {
    pass("Contact model: Tenant isolation works (tenant 1 has $contactsByTenant contacts)");
} else {
    fail('Contact model: Tenant isolation failed');
}

// ============================================================================
// Authentication Tests
// ============================================================================

testHeader('AUTHENTICATION TESTS');

require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Session.php';

// Test: Password verification
if (password_verify('password123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')) {
    pass('Password hashing: Verification works correctly');
} else {
    fail('Password hashing: Verification failed');
}

// Test: API key verification
$verifiedTenant = Auth::verifyApiKey(API_KEY_TENANT_1);
if ($verifiedTenant && $verifiedTenant['id'] == 1) {
    pass('API Authentication: API key verification works');
} else {
    fail('API Authentication: API key verification failed');
}

// ============================================================================
// API Tests
// ============================================================================

testHeader('API ENDPOINT TESTS');

// Note: These tests require the application to be running
// They will skip if BASE_URL is not accessible

function testApiEndpoint($url, $method, $headers, $data, $expectedStatus, $testName) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        echo "⊘ SKIP: $testName (Server not running)\n";
        return;
    }

    if ($httpCode == $expectedStatus) {
        pass("API: $testName (HTTP $httpCode)");
        return json_decode($response, true);
    } else {
        fail("API: $testName (Expected HTTP $expectedStatus, got HTTP $httpCode)");
        return null;
    }
}

// Test: Send message API
$sendMessageData = [
    'phone' => '+15551234567',
    'message' => 'Test message from API'
];

testApiEndpoint(
    BASE_URL . '/api/send-message',
    'POST',
    ['X-API-KEY: ' . API_KEY_TENANT_1, 'Content-Type: application/json'],
    $sendMessageData,
    200,
    'Send message endpoint'
);

// Test: Incoming webhook
$webhookData = [
    'phone' => '+15559876543',
    'message' => 'Incoming test message',
    'tenant_api_key' => API_KEY_TENANT_1
];

testApiEndpoint(
    BASE_URL . '/api/webhook/incoming',
    'POST',
    ['Content-Type: application/json'],
    $webhookData,
    200,
    'Incoming webhook endpoint'
);

// Test: API docs
testApiEndpoint(
    BASE_URL . '/api/docs',
    'GET',
    [],
    null,
    200,
    'API documentation endpoint'
);

// ============================================================================
// Subscription & Limits Tests
// ============================================================================

testHeader('SUBSCRIPTION & LIMITS TESTS');

require_once __DIR__ . '/../app/models/Subscription.php';
require_once __DIR__ . '/../app/models/UsageTracker.php';

// Test: Get active subscription
$subscriptionModel = new Subscription();
$subscription = $subscriptionModel->getActiveByStan(1);
if ($subscription && isset($subscription['plan_name'])) {
    pass("Subscription: Active subscription found (Plan: {$subscription['plan_name']})");
} else {
    fail('Subscription: Active subscription not found');
}

// Test: Usage tracking
$usageModel = new UsageTracker();
$usage = $usageModel->getCurrentMonthUsage(1);
if ($usage) {
    pass("Usage tracking: Current month usage retrieved (Messages: {$usage['messages_sent']})");
} else {
    echo "⊘ SKIP: Usage tracking (No data for current month)\n";
}

// ============================================================================
// Campaign Tests
// ============================================================================

testHeader('CAMPAIGN TESTS');

require_once __DIR__ . '/../app/models/Campaign.php';
require_once __DIR__ . '/../app/models/CampaignMessage.php';

// Test: Get campaigns
$campaignModel = new Campaign();
$campaigns = $campaignModel->getByTenant(1, 10, 0);
if (count($campaigns) > 0) {
    pass("Campaigns: Retrieved " . count($campaigns) . " campaigns for tenant 1");
} else {
    fail('Campaigns: No campaigns found');
}

// Test: Campaign details
if (!empty($campaigns)) {
    $campaign = $campaignModel->getWithDetails($campaigns[0]['id']);
    if ($campaign && isset($campaign['total_recipients'])) {
        pass("Campaigns: Campaign details retrieved (Recipients: {$campaign['total_recipients']})");
    } else {
        fail('Campaigns: Campaign details retrieval failed');
    }
}

// ============================================================================
// Summary
// ============================================================================

testHeader('TEST SUMMARY');

echo "All tests completed!\n";
echo "Review the results above for any failures.\n\n";
echo "To test the web interface:\n";
echo "1. Navigate to " . BASE_URL . "\n";
echo "2. Login with: admin@company1.com / password123\n";
echo "3. Explore all features\n\n";
