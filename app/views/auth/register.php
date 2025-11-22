<!-- FILE: /app/views/auth/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SplashWhats</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Start your SplashWhats journey</p>

            <?php if (Session::has('flash_error')): ?>
                <div class="alert alert-error">
                    <?php echo View::escape(Session::getFlash('error')); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register" class="auth-form">
                <?php echo CSRF::field(); ?>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required class="form-control" minlength="6">
                    <small>Minimum 6 characters</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-footer">
                Already have an account? <a href="/login">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
