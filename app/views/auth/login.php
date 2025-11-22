<!-- FILE: /app/views/auth/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SplashWhats</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">SplashWhats</h1>
            <p class="auth-subtitle">Multi-tenant WhatsApp SaaS Platform</p>

            <?php if (Session::has('flash_error')): ?>
                <div class="alert alert-error">
                    <?php echo View::escape(Session::getFlash('error')); ?>
                </div>
            <?php endif; ?>

            <?php if (Session::has('flash_success')): ?>
                <div class="alert alert-success">
                    <?php echo View::escape(Session::getFlash('success')); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="auth-form">
                <?php echo CSRF::field(); ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-footer">
                Don't have an account? <a href="/register">Register here</a>
            </p>

            <div class="demo-credentials">
                <strong>Demo Credentials:</strong><br>
                Tenant 1: admin@company1.com / password123<br>
                Tenant 2: admin@company2.com / password123
            </div>
        </div>
    </div>
</body>
</html>
