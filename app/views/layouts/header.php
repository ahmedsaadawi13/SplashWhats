<!-- FILE: /app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? View::escape($pageTitle) . ' - ' : ''; ?>SplashWhats</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php if (Auth::check()): ?>
        <div class="layout">
            <?php require __DIR__ . '/sidebar.php'; ?>
            <div class="main-content">
                <header class="top-header">
                    <div class="header-left">
                        <h1><?php echo isset($pageTitle) ? View::escape($pageTitle) : 'Dashboard'; ?></h1>
                    </div>
                    <div class="header-right">
                        <span class="user-name">
                            <?php echo View::escape(Auth::user()['name']); ?>
                            <small>(<?php echo View::escape(Auth::user()['role']); ?>)</small>
                        </span>
                        <a href="/logout" class="btn btn-secondary btn-sm">Logout</a>
                    </div>
                </header>

                <div class="content-wrapper">
                    <?php if (Session::has('flash_success')): ?>
                        <div class="alert alert-success">
                            <?php echo View::escape(Session::getFlash('success')); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Session::has('flash_error')): ?>
                        <div class="alert alert-error">
                            <?php echo View::escape(Session::getFlash('error')); ?>
                        </div>
                    <?php endif; ?>
