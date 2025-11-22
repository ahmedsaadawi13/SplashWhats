<?php
// FILE: /public/index.php

/**
 * SplashWhats - Multi-tenant WhatsApp SaaS Platform
 * Application Entry Point
 */

// Start session
session_start();

// Set timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load core classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Validator.php';
require_once __DIR__ . '/../app/core/CSRF.php';

// Initialize session
Session::start();

// Create router instance
$router = new Router();

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

// Authentication routes
$router->get('/', 'AuthController', 'showLogin');
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'showRegister');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');

// API documentation
$router->get('/api/docs', 'ApiController', 'docs');

// ============================================================================
// API ROUTES (API key authentication)
// ============================================================================

// Send message API
$router->post('/api/send-message', 'ApiController', 'sendMessage');

// Webhook for incoming messages
$router->post('/api/webhook/incoming', 'WebhookController', 'incoming');

// ============================================================================
// AUTHENTICATED ROUTES (Require login)
// ============================================================================

// Dashboard
$router->get('/dashboard', 'DashboardController', 'index');

// Contacts
$router->get('/contacts', 'ContactController', 'index');
$router->get('/contacts/create', 'ContactController', 'create');
$router->post('/contacts/store', 'ContactController', 'store');
$router->get('/contacts/edit/{id}', 'ContactController', 'edit');
$router->post('/contacts/update/{id}', 'ContactController', 'update');
$router->post('/contacts/delete/{id}', 'ContactController', 'delete');
$router->get('/contacts/import', 'ContactController', 'showImport');
$router->post('/contacts/import', 'ContactController', 'import');

// Tags
$router->get('/tags', 'TagController', 'index');
$router->post('/tags/store', 'TagController', 'store');
$router->post('/tags/update/{id}', 'TagController', 'update');
$router->post('/tags/delete/{id}', 'TagController', 'delete');

// Conversations (Inbox)
$router->get('/conversations', 'ConversationController', 'index');
$router->get('/conversations/{id}', 'ConversationController', 'show');
$router->post('/conversations/{id}/send', 'ConversationController', 'sendMessage');
$router->post('/conversations/{id}/assign', 'ConversationController', 'assign');
$router->post('/conversations/{id}/status', 'ConversationController', 'updateStatus');

// Templates
$router->get('/templates', 'TemplateController', 'index');
$router->get('/templates/create', 'TemplateController', 'create');
$router->post('/templates/store', 'TemplateController', 'store');
$router->get('/templates/edit/{id}', 'TemplateController', 'edit');
$router->post('/templates/update/{id}', 'TemplateController', 'update');
$router->post('/templates/delete/{id}', 'TemplateController', 'delete');

// Campaigns
$router->get('/campaigns', 'CampaignController', 'index');
$router->get('/campaigns/create', 'CampaignController', 'create');
$router->post('/campaigns/store', 'CampaignController', 'store');
$router->get('/campaigns/{id}', 'CampaignController', 'show');
$router->post('/campaigns/{id}/start', 'CampaignController', 'start');

// Subscriptions
$router->get('/subscriptions', 'SubscriptionController', 'index');
$router->post('/subscriptions/change-plan', 'SubscriptionController', 'changePlan');

// Billing
$router->get('/billing', 'BillingController', 'index');
$router->post('/billing/simulate-payment', 'BillingController', 'simulatePayment');

// Users (Agents/Admins)
$router->get('/users', 'UserController', 'index');
$router->post('/users/store', 'UserController', 'store');
$router->post('/users/update/{id}', 'UserController', 'update');
$router->post('/users/delete/{id}', 'UserController', 'delete');

// Dispatch the request
$router->dispatch();
