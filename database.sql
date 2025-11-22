-- FILE: /database.sql
-- SplashWhats - Multi-tenant WhatsApp SaaS Platform
-- Database Schema and Seed Data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: splashwhats
--
CREATE DATABASE IF NOT EXISTS `splashwhats` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `splashwhats`;

-- ============================================================================
-- Core Tables
-- ============================================================================

--
-- Table: tenants
-- Stores tenant (company) information
--
CREATE TABLE `tenants` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `api_key` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
  `settings` TEXT,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_api_key` (`api_key`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: users
-- Stores user accounts (platform admins, tenant admins, agents)
--
CREATE TABLE `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'agent') NOT NULL DEFAULT 'agent',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Contact Management
-- ============================================================================

--
-- Table: contacts
-- Stores contact information for each tenant
--
CREATE TABLE `contacts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `timezone` VARCHAR(50) DEFAULT 'UTC',
  `status` ENUM('active', 'blocked') DEFAULT 'active',
  `notes` TEXT,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_status` (`status`),
  UNIQUE KEY `unique_tenant_phone` (`tenant_id`, `phone`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: tags
-- Stores tags for contact segmentation
--
CREATE TABLE `tags` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `color` VARCHAR(7) DEFAULT '#3B82F6',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  UNIQUE KEY `unique_tenant_tag` (`tenant_id`, `name`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: contact_tags
-- Many-to-many relationship between contacts and tags
--
CREATE TABLE `contact_tags` (
  `contact_id` INT(11) UNSIGNED NOT NULL,
  `tag_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`contact_id`, `tag_id`),
  INDEX `idx_tag_id` (`tag_id`),
  FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Messaging
-- ============================================================================

--
-- Table: conversations
-- Stores conversation threads with contacts
--
CREATE TABLE `conversations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `contact_id` INT(11) UNSIGNED NOT NULL,
  `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
  `status` ENUM('open', 'closed') DEFAULT 'open',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_contact_id` (`contact_id`),
  INDEX `idx_assigned_to` (`assigned_to`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: messages
-- Stores individual messages within conversations
--
CREATE TABLE `messages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversation_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED DEFAULT NULL,
  `direction` ENUM('inbound', 'outbound') NOT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('pending', 'sent', 'delivered', 'read', 'failed', 'unread') DEFAULT 'pending',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_conversation_id` (`conversation_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_direction` (`direction`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Templates & Quick Replies
-- ============================================================================

--
-- Table: templates
-- Message templates with placeholders
--
CREATE TABLE `templates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `category` ENUM('marketing', 'support', 'sales', 'notification') DEFAULT 'marketing',
  `language` VARCHAR(10) DEFAULT 'en',
  `body` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_category` (`category`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: quick_replies
-- Quick reply shortcuts for agents
--
CREATE TABLE `quick_replies` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `shortcut` VARCHAR(50) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  UNIQUE KEY `unique_tenant_shortcut` (`tenant_id`, `shortcut`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Campaigns
-- ============================================================================

--
-- Table: campaigns
-- Bulk messaging campaigns
--
CREATE TABLE `campaigns` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `message_type` ENUM('text', 'template') DEFAULT 'text',
  `message_body` TEXT,
  `template_id` INT(11) UNSIGNED DEFAULT NULL,
  `status` ENUM('draft', 'scheduled', 'running', 'completed', 'cancelled') DEFAULT 'draft',
  `scheduled_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_template_id` (`template_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: campaign_messages
-- Individual messages within a campaign
--
CREATE TABLE `campaign_messages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) UNSIGNED NOT NULL,
  `contact_id` INT(11) UNSIGNED NOT NULL,
  `message_body` TEXT NOT NULL,
  `status` ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
  `sent_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_campaign_id` (`campaign_id`),
  INDEX `idx_contact_id` (`contact_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Subscriptions & Billing
-- ============================================================================

--
-- Table: plans
-- Subscription plans with limits
--
CREATE TABLE `plans` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `billing_period` ENUM('monthly', 'yearly') DEFAULT 'monthly',
  `limits` TEXT,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: tenant_subscriptions
-- Tracks tenant subscriptions to plans
--
CREATE TABLE `tenant_subscriptions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `plan_id` INT(11) UNSIGNED NOT NULL,
  `status` ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
  `starts_at` DATETIME NOT NULL,
  `ends_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_plan_id` (`plan_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: usage_tracker
-- Tracks tenant usage against subscription limits
--
CREATE TABLE `usage_tracker` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `month` VARCHAR(7) NOT NULL,
  `contacts_count` INT(11) DEFAULT 0,
  `messages_sent` INT(11) DEFAULT 0,
  `campaigns_count` INT(11) DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_month` (`tenant_id`, `month`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_month` (`month`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: invoices
-- Billing invoices
--
CREATE TABLE `invoices` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `amount` DECIMAL(10, 2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `status` ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
  `period_start` DATE NOT NULL,
  `period_end` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: payments
-- Payment records
--
CREATE TABLE `payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `invoice_id` INT(11) UNSIGNED DEFAULT NULL,
  `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
  `amount` DECIMAL(10, 2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `payment_method` VARCHAR(50) DEFAULT 'simulated',
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_invoice_id` (`invoice_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Seed Data
-- ============================================================================

--
-- Platform Admin User
--
INSERT INTO `users` (`id`, `tenant_id`, `email`, `password`, `name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'admin@splashwhats.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Platform Admin', 'platform_admin', 'active', NOW(), NOW());

--
-- Subscription Plans
--
INSERT INTO `plans` (`id`, `name`, `description`, `price`, `currency`, `billing_period`, `limits`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 'Perfect for small businesses', 29.00, 'USD', 'monthly', '{"max_contacts": 500, "max_messages_per_month": 1000, "max_campaigns": 5, "max_agents": 2}', 'active', NOW(), NOW()),
(2, 'Professional', 'For growing businesses', 99.00, 'USD', 'monthly', '{"max_contacts": 5000, "max_messages_per_month": 10000, "max_campaigns": 20, "max_agents": 10}', 'active', NOW(), NOW()),
(3, 'Enterprise', 'Unlimited everything', 299.00, 'USD', 'monthly', '{"max_contacts": 50000, "max_messages_per_month": 100000, "max_campaigns": 100, "max_agents": 50}', 'active', NOW(), NOW());

--
-- Demo Tenant 1: Tech Solutions Inc
--
INSERT INTO `tenants` (`id`, `name`, `api_key`, `status`, `settings`, `created_at`, `updated_at`) VALUES
(1, 'Tech Solutions Inc', 'sk_demo_key_tech_solutions_2024_abc123xyz', 'active', '{"timezone": "America/New_York", "language": "en"}', NOW(), NOW());

INSERT INTO `users` (`id`, `tenant_id`, `email`, `password`, `name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 'admin@company1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith', 'tenant_admin', 'active', NOW(), NOW()),
(3, 1, 'agent1@company1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', 'agent', 'active', NOW(), NOW()),
(4, 1, 'agent2@company1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Davis', 'agent', 'active', NOW(), NOW());

INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), NOW(), NOW());

INSERT INTO `tags` (`id`, `tenant_id`, `name`, `color`, `created_at`) VALUES
(1, 1, 'VIP Customer', '#F59E0B', NOW()),
(2, 1, 'New Lead', '#3B82F6', NOW()),
(3, 1, 'Support', '#EF4444', NOW()),
(4, 1, 'Newsletter', '#10B981', NOW());

INSERT INTO `contacts` (`id`, `tenant_id`, `name`, `phone`, `email`, `country`, `timezone`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alice Williams', '+14155551234', 'alice@example.com', 'USA', 'America/New_York', 'active', 'Interested in enterprise plan', NOW(), NOW()),
(2, 1, 'Bob Martinez', '+14155555678', 'bob@example.com', 'USA', 'America/Los_Angeles', 'active', 'Premium customer', NOW(), NOW()),
(3, 1, 'Carol Brown', '+14155559012', 'carol@example.com', 'USA', 'America/Chicago', 'active', 'Monthly newsletter subscriber', NOW(), NOW()),
(4, 1, 'David Lee', '+14155553456', 'david@example.com', 'USA', 'America/New_York', 'active', 'Requires technical support', NOW(), NOW()),
(5, 1, 'Emma Wilson', '+14155557890', 'emma@example.com', 'USA', 'America/Denver', 'active', 'Trial user', NOW(), NOW());

INSERT INTO `contact_tags` (`contact_id`, `tag_id`) VALUES
(1, 1), (1, 2),
(2, 1),
(3, 4),
(4, 3),
(5, 2);

INSERT INTO `conversations` (`id`, `tenant_id`, `contact_id`, `assigned_to`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, 'open', NOW(), NOW()),
(2, 1, 2, 3, 'open', NOW(), NOW()),
(3, 1, 4, 4, 'open', NOW(), NOW());

INSERT INTO `messages` (`conversation_id`, `user_id`, `direction`, `body`, `status`, `created_at`) VALUES
(1, NULL, 'inbound', 'Hi, I am interested in your enterprise plan. Can you provide more details?', 'read', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 3, 'outbound', 'Hello Alice! Thank you for your interest. Our enterprise plan includes unlimited contacts, 100k messages per month, and dedicated support. Would you like to schedule a demo?', 'sent', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, NULL, 'inbound', 'Yes, that would be great! What times are available?', 'unread', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(2, NULL, 'inbound', 'Thank you for the excellent service!', 'read', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 3, 'outbound', 'You are very welcome! We appreciate your business.', 'sent', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, NULL, 'inbound', 'I am having trouble logging into my account', 'read', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 4, 'outbound', 'I can help you with that. Can you please confirm your email address?', 'sent', DATE_SUB(NOW(), INTERVAL 45 MINUTE));

INSERT INTO `templates` (`id`, `tenant_id`, `name`, `category`, `language`, `body`, `created_at`, `updated_at`) VALUES
(1, 1, 'Welcome Message', 'marketing', 'en', 'Hi {{name}}! Welcome to Tech Solutions. We are excited to have you with us. If you have any questions, feel free to reach out!', NOW(), NOW()),
(2, 1, 'Order Confirmation', 'notification', 'en', 'Hello {{name}}, your order has been confirmed! Your order number is #{{order_id}}. We will notify you when it ships.', NOW(), NOW()),
(3, 1, 'Support Follow-up', 'support', 'en', 'Hi {{name}}, we wanted to follow up on your recent support ticket. Is everything resolved to your satisfaction?', NOW(), NOW());

INSERT INTO `quick_replies` (`tenant_id`, `shortcut`, `message`, `created_at`) VALUES
(1, '/hello', 'Hello! How can I help you today?', NOW()),
(1, '/thanks', 'Thank you for contacting us! Have a great day!', NOW()),
(1, '/hours', 'Our business hours are Monday-Friday 9 AM - 5 PM EST.', NOW());

INSERT INTO `campaigns` (`id`, `tenant_id`, `name`, `message_type`, `message_body`, `template_id`, `status`, `scheduled_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monthly Newsletter - January', 'template', NULL, 1, 'completed', NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 1, 'Product Launch Announcement', 'text', 'Exciting news! We are launching our new product next week. Stay tuned for more details!', NULL, 'running', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
(3, 1, 'Customer Feedback Survey', 'text', 'We value your opinion! Please take 2 minutes to complete our customer satisfaction survey.', NULL, 'draft', NULL, NOW(), NOW());

INSERT INTO `campaign_messages` (`campaign_id`, `contact_id`, `message_body`, `status`, `sent_at`, `created_at`) VALUES
(1, 1, 'Hi Alice! Welcome to Tech Solutions. We are excited to have you with us. If you have any questions, feel free to reach out!', 'sent', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 2, 'Hi Bob! Welcome to Tech Solutions. We are excited to have you with us. If you have any questions, feel free to reach out!', 'sent', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 3, 'Hi Carol! Welcome to Tech Solutions. We are excited to have you with us. If you have any questions, feel free to reach out!', 'sent', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 1, 'Exciting news! We are launching our new product next week. Stay tuned for more details!', 'sent', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 2, 'Exciting news! We are launching our new product next week. Stay tuned for more details!', 'sent', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 4, 'Exciting news! We are launching our new product next week. Stay tuned for more details!', 'pending', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 1, 'We value your opinion! Please take 2 minutes to complete our customer satisfaction survey.', 'pending', NULL, NOW()),
(3, 2, 'We value your opinion! Please take 2 minutes to complete our customer satisfaction survey.', 'pending', NULL, NOW());

INSERT INTO `invoices` (`tenant_id`, `invoice_number`, `amount`, `currency`, `status`, `period_start`, `period_end`, `due_date`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 'INV-20240101-ABC123', 99.00, 'USD', 'paid', '2024-01-01', '2024-01-31', '2024-01-15', '2024-01-10 14:30:00', '2024-01-01 00:00:00', '2024-01-10 14:30:00'),
(1, 'INV-20240201-DEF456', 99.00, 'USD', 'paid', '2024-02-01', '2024-02-29', '2024-02-15', '2024-02-12 10:20:00', '2024-02-01 00:00:00', '2024-02-12 10:20:00'),
(1, 'INV-20240301-GHI789', 99.00, 'USD', 'pending', '2024-03-01', '2024-03-31', '2024-03-15', NULL, '2024-03-01 00:00:00', '2024-03-01 00:00:00');

INSERT INTO `payments` (`tenant_id`, `invoice_id`, `transaction_id`, `amount`, `currency`, `payment_method`, `status`, `created_at`) VALUES
(1, 1, 'TXN-20240110-XYZ12345', 99.00, 'USD', 'simulated', 'completed', '2024-01-10 14:30:00'),
(1, 2, 'TXN-20240212-ABC67890', 99.00, 'USD', 'simulated', 'completed', '2024-02-12 10:20:00');

INSERT INTO `usage_tracker` (`tenant_id`, `month`, `contacts_count`, `messages_sent`, `campaigns_count`, `created_at`) VALUES
(1, '2024-01', 5, 142, 1, '2024-01-31 00:00:00'),
(1, '2024-02', 5, 198, 1, '2024-02-29 00:00:00'),
(1, '2024-03', 5, 57, 2, NOW());

--
-- Demo Tenant 2: Global Marketing Co
--
INSERT INTO `tenants` (`id`, `name`, `api_key`, `status`, `settings`, `created_at`, `updated_at`) VALUES
(2, 'Global Marketing Co', 'sk_demo_key_global_marketing_2024_def456uvw', 'active', '{"timezone": "Europe/London", "language": "en"}', NOW(), NOW());

INSERT INTO `users` (`id`, `tenant_id`, `email`, `password`, `name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(5, 2, 'admin@company2.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emily Thompson', 'tenant_admin', 'active', NOW(), NOW()),
(6, 2, 'agent1@company2.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James Wilson', 'agent', 'active', NOW(), NOW());

INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(2, 1, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), NOW(), NOW());

INSERT INTO `tags` (`id`, `tenant_id`, `name`, `color`, `created_at`) VALUES
(5, 2, 'Hot Lead', '#F59E0B', NOW()),
(6, 2, 'Customer', '#10B981', NOW());

INSERT INTO `contacts` (`id`, `tenant_id`, `name`, `phone`, `email`, `country`, `timezone`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(6, 2, 'Frank Anderson', '+442071234567', 'frank@example.com', 'UK', 'Europe/London', 'active', 'Potential enterprise client', NOW(), NOW()),
(7, 2, 'Grace Taylor', '+442079876543', 'grace@example.com', 'UK', 'Europe/London', 'active', 'Existing customer', NOW(), NOW());

INSERT INTO `contact_tags` (`contact_id`, `tag_id`) VALUES
(6, 5),
(7, 6);

INSERT INTO `conversations` (`id`, `tenant_id`, `contact_id`, `assigned_to`, `status`, `created_at`, `updated_at`) VALUES
(4, 2, 6, 6, 'open', NOW(), NOW());

INSERT INTO `messages` (`conversation_id`, `user_id`, `direction`, `body`, `status`, `created_at`) VALUES
(4, NULL, 'inbound', 'Hello, I would like to know more about your marketing services.', 'read', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(4, 6, 'outbound', 'Hi Frank! I would be happy to help. Let me send you our service brochure.', 'sent', DATE_SUB(NOW(), INTERVAL 30 MINUTE));

INSERT INTO `templates` (`id`, `tenant_id`, `name`, `category`, `language`, `body`, `created_at`, `updated_at`) VALUES
(4, 2, 'Campaign Launch', 'marketing', 'en', 'Hi {{name}}! We are launching an exciting new marketing campaign. Contact us to learn more!', NOW(), NOW());

INSERT INTO `usage_tracker` (`tenant_id`, `month`, `contacts_count`, `messages_sent`, `campaigns_count`, `created_at`) VALUES
(2, '2024-03', 2, 23, 0, NOW());

-- ============================================================================
-- End of database.sql
-- ============================================================================

-- Note: Default password for all demo users is 'password123'
-- Password hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
