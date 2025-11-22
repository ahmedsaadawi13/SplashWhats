# SplashWhats

**Multi-tenant WhatsApp-style SaaS Messaging Platform**

SplashWhats is a complete, production-ready multi-tenant SaaS platform for managing WhatsApp-style messaging, built with pure PHP and MySQL. It features a centralized inbox, bulk campaigns, contact management, subscription billing, and a REST API for integrations.

## Important Disclaimer

This system **simulates** WhatsApp-style messaging functionality and is **not** an official WhatsApp Business API client. Integration with a real WhatsApp provider would require additional configuration and external services.

## Features

### Core Functionality
- **Multi-tenant Architecture**: Isolated data and complete tenant separation
- **Contact Management**: Import, export, tag, and segment contacts
- **Conversation Inbox**: Centralized messaging interface for agents
- **Bulk Campaigns**: Send messages to segmented audiences
- **Message Templates**: Reusable templates with placeholder support
- **Quick Replies**: Pre-defined responses for agents
- **User Management**: Role-based access (Platform Admin, Tenant Admin, Agent)

### Subscription & Billing
- **Flexible Plans**: Starter, Professional, and Enterprise tiers
- **Usage Tracking**: Monitor contacts, messages, and campaigns
- **Quota Enforcement**: Automatic limit checking
- **Invoice Management**: Automated billing with payment tracking
- **Simulated Payments**: Demo payment gateway

### API & Integrations
- **REST API**: Send messages programmatically
- **Webhook Support**: Receive incoming messages
- **API Authentication**: Secure key-based access per tenant
- **API Documentation**: Built-in endpoint documentation

### Analytics & Reporting
- **Dashboard Metrics**: Contact counts, message volume, active campaigns
- **Message Analytics**: Daily message charts and trends
- **Tag Statistics**: Top tags by contact count
- **Campaign Reports**: Delivery rates and status tracking

## Technology Stack

- **Backend**: PHP 7.0+ (compatible with PHP 8.x)
- **Database**: MySQL with InnoDB engine
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Architecture**: Custom lightweight MVC framework
- **Security**: CSRF protection, password hashing, prepared statements

## System Requirements

- PHP 7.0 or higher (7.4+ recommended)
- MySQL 5.7+ or MariaDB 10.2+
- Apache or Nginx web server
- PHP Extensions:
  - PDO
  - pdo_mysql
  - mbstring
  - json
  - session

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/splashwhats.git
cd splashwhats
```

### 2. Configure Environment

Copy the environment example file:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```
DB_HOST=localhost
DB_DATABASE=splashwhats
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Import Database

Create the database and import the schema:

```bash
mysql -u root -p < database.sql
```

Or using phpMyAdmin:
1. Create a new database named `splashwhats`
2. Import `database.sql` file

### 4. Set Permissions

Ensure the web server can write to the uploads directory:

```bash
chmod -R 775 storage/uploads
chown -R www-data:www-data storage/uploads
```

### 5. Configure Web Server

#### Apache

The `.htaccess` file in the `public` directory is already configured. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Set document root to `/path/to/splashwhats/public` in your Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName splashwhats.local
    DocumentRoot /path/to/splashwhats/public

    <Directory /path/to/splashwhats/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashwhats-error.log
    CustomLog ${APACHE_LOG_DIR}/splashwhats-access.log combined
</VirtualHost>
```

#### Nginx

Sample Nginx configuration:

```nginx
server {
    listen 80;
    server_name splashwhats.local;
    root /path/to/splashwhats/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 6. Access the Application

Navigate to `http://your-domain.com` or `http://localhost`

## Demo Credentials

The database includes seed data with demo accounts:

### Tenant 1: Tech Solutions Inc
- **Admin**: admin@company1.com / password123
- **Agent 1**: agent1@company1.com / password123
- **Agent 2**: agent2@company1.com / password123
- **API Key**: `sk_demo_key_tech_solutions_2024_abc123xyz`

### Tenant 2: Global Marketing Co
- **Admin**: admin@company2.com / password123
- **Agent**: agent1@company2.com / password123
- **API Key**: `sk_demo_key_global_marketing_2024_def456uvw`

### Platform Admin
- **Email**: admin@splashwhats.com / password123

## Testing

Run the functional test suite:

```bash
php tests/functional_tests.php
```

This will verify:
- Database connectivity and schema
- Model functionality
- Authentication system
- Tenant isolation
- API endpoints
- Subscription features

## API Documentation

### Authentication

All API requests require an `X-API-KEY` header with your tenant's API key.

### Send Message

**Endpoint**: `POST /api/send-message`

**Headers**:
```
X-API-KEY: your_api_key_here
Content-Type: application/json
```

**Request Body**:
```json
{
  "phone": "+14155551234",
  "message": "Hello! This is a test message.",
  "template_id": 1
}
```

**Response** (Success):
```json
{
  "success": true,
  "message_id": 123,
  "contact_id": 456,
  "conversation_id": 789,
  "status": "sent"
}
```

### Incoming Webhook

**Endpoint**: `POST /api/webhook/incoming`

**Request Body**:
```json
{
  "phone": "+14155551234",
  "message": "This is an incoming message",
  "tenant_api_key": "your_api_key_here"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Message received",
  "message_id": 124
}
```

### API Documentation Endpoint

**Endpoint**: `GET /api/docs`

Returns full API documentation in JSON format.

## Project Structure

```
SplashWhats/
├── app/
│   ├── controllers/        # Request handlers
│   ├── models/            # Database models
│   ├── views/             # HTML templates
│   └── core/              # Framework core (Router, Auth, etc.)
├── config/                # Configuration files
├── public/                # Web root
│   ├── index.php         # Application entry point
│   ├── .htaccess         # Apache configuration
│   └── assets/           # CSS, JS, images
├── storage/
│   └── uploads/          # File uploads directory
├── tests/                # Test scripts
├── database.sql          # Database schema & seed data
├── .env.example          # Environment configuration template
├── .gitignore           # Git ignore rules
└── README.md            # This file
```

## Architecture

### MVC Pattern

- **Models** (`app/models/`): Handle database operations
- **Views** (`app/views/`): Render HTML templates
- **Controllers** (`app/controllers/`): Process requests and coordinate models/views

### Core Components

- **Router**: Maps URLs to controller methods
- **Database**: Singleton PDO connection manager
- **Auth**: Authentication and authorization
- **Session**: Session management wrapper
- **Validator**: Input validation and sanitization
- **CSRF**: Cross-site request forgery protection

### Multi-tenancy

Every main table includes `tenant_id` to ensure data isolation. All queries are automatically scoped to the current tenant based on the authenticated user's context.

## Security Features

1. **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
2. **Prepared Statements**: All SQL queries use PDO prepared statements
3. **CSRF Protection**: Token-based CSRF validation on all forms
4. **Input Validation**: Comprehensive validation and sanitization
5. **SQL Injection Prevention**: No raw SQL in controllers
6. **XSS Protection**: All output is escaped using `htmlspecialchars()`
7. **Session Security**: Secure session configuration
8. **API Authentication**: Key-based authentication for API endpoints

## Deployment Guide

### Production Checklist

1. **Disable Error Display**:
   ```php
   // In public/index.php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Enable HTTPS**: Use SSL/TLS certificates (Let's Encrypt recommended)

3. **Secure Database**:
   - Use strong passwords
   - Create dedicated database user with minimal privileges
   - Enable MySQL SSL connections

4. **File Permissions**:
   ```bash
   chmod 644 public/.htaccess
   chmod 644 public/index.php
   chmod 755 public/assets
   chmod 775 storage/uploads
   ```

5. **Environment Variables**: Never commit `.env` to version control

6. **Backup Strategy**:
   - Schedule daily database backups
   - Backup uploaded files regularly
   - Test restore procedures

7. **Monitoring**:
   - Set up error logging
   - Monitor disk space for uploads
   - Track database performance

### Performance Optimization

1. **Enable OPcache** in `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **MySQL Optimization**:
   - Add indexes on frequently queried columns
   - Enable query cache (if using MySQL 5.7)
   - Optimize table structures

3. **Caching**:
   - Implement Redis/Memcached for session storage
   - Cache frequently accessed data

## Scaling Considerations

For high-traffic deployments:

1. **Database**: Use master-slave replication
2. **File Storage**: Move uploads to S3 or CDN
3. **Load Balancing**: Multiple application servers behind load balancer
4. **Queue System**: Implement job queue for campaign processing
5. **Caching Layer**: Redis for session and data caching

## Customization

### Adding New Features

1. Create model in `app/models/`
2. Create controller in `app/controllers/`
3. Add routes in `public/index.php`
4. Create views in `app/views/`
5. Update database schema

### Changing UI Theme

Edit `public/assets/css/style.css` to customize:
- Colors
- Fonts
- Layout
- Components

## Troubleshooting

### Common Issues

**Issue**: 404 errors on all routes
- **Solution**: Enable Apache `mod_rewrite` or check Nginx configuration

**Issue**: Database connection failed
- **Solution**: Verify database credentials in `.env` and ensure MySQL is running

**Issue**: Permission denied on file uploads
- **Solution**: Set correct permissions on `storage/uploads` directory

**Issue**: CSRF token validation failed
- **Solution**: Ensure sessions are working and cookies are enabled

**Issue**: API returns 401 Unauthorized
- **Solution**: Verify X-API-KEY header is set correctly

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Code Review Summary

### Security Strengths
- ✅ Prepared statements throughout
- ✅ Password hashing with bcrypt
- ✅ CSRF protection on forms
- ✅ Input validation and sanitization
- ✅ Session security
- ✅ API key authentication

### Architecture Strengths
- ✅ Clean MVC separation
- ✅ Tenant isolation at database level
- ✅ Beginner-friendly code with comments
- ✅ No framework dependencies
- ✅ PHP 7.0+ compatibility

### Recommended Improvements for Production
- Add rate limiting on API endpoints
- Implement email verification for new accounts
- Add two-factor authentication option
- Implement comprehensive error logging
- Add database migration system
- Create admin panel for platform management

## License

This project is provided as-is for educational and commercial use.

## Support

For issues, questions, or contributions:
- Open an issue on GitHub
- Email: support@splashwhats.com

## Credits

Built with ❤️ as a demonstration of modern PHP SaaS architecture.

---

**Version**: 1.0.0
**Last Updated**: 2024
