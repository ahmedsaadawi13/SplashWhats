<!-- FILE: /app/views/layouts/sidebar.php -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2 class="logo">SplashWhats</h2>
    </div>

    <nav class="sidebar-nav">
        <a href="/dashboard" class="nav-item">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="/conversations" class="nav-item">
            <span class="nav-icon">💬</span>
            <span class="nav-label">Inbox</span>
        </a>

        <a href="/contacts" class="nav-item">
            <span class="nav-icon">👥</span>
            <span class="nav-label">Contacts</span>
        </a>

        <a href="/tags" class="nav-item">
            <span class="nav-icon">🏷️</span>
            <span class="nav-label">Tags</span>
        </a>

        <a href="/templates" class="nav-item">
            <span class="nav-icon">📝</span>
            <span class="nav-label">Templates</span>
        </a>

        <a href="/campaigns" class="nav-item">
            <span class="nav-icon">📢</span>
            <span class="nav-label">Campaigns</span>
        </a>

        <?php if (Auth::user()['role'] === 'tenant_admin'): ?>
            <a href="/users" class="nav-item">
                <span class="nav-icon">👤</span>
                <span class="nav-label">Users</span>
            </a>

            <a href="/subscriptions" class="nav-item">
                <span class="nav-icon">💳</span>
                <span class="nav-label">Subscription</span>
            </a>

            <a href="/billing" class="nav-item">
                <span class="nav-icon">💰</span>
                <span class="nav-label">Billing</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>
