<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; Hospital Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">🏥</div>
        <h1>HospitalMS</h1>
        <p>Manage appointments, prescriptions, queue and billing from one place.</p>
        <ul class="feature-list">
            <li>✓ <strong>Patient</strong> — book doctors & pay invoices</li>
            <li>✓ <strong>Doctor</strong> — prescriptions & availability</li>
            <li>✓ <strong>Receptionist</strong> — queue, invoices & patients</li>
            <li>✓ <strong>Admin</strong> — users, revenue & logs</li>
        </ul>
        <p class="side-note">Demo password for sample accounts: <code>password</code></p>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Sign in</h2>
            <p class="muted">Welcome back — enter your credentials</p>

            <?php $flash = get_flash(); if ($flash): ?>
                <div class="alert alert-<?= ($flash['type']==='danger'||$flash['type']==='error') ? 'error' : esc($flash['type']) ?>">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" class="form" novalidate
                  onsubmit="return validateForm(this);">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" data-label="Username"
                           placeholder="e.g. admin or patient1" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" data-label="Password"
                           placeholder="••••••••" required>
                </div>

                <div class="field" style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember" style="margin:0;">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-switch">
                New here? <a href="index.php?page=register">Create an account</a>
            </p>

            <div class="alert alert-info" style="margin-top:1rem;font-size:0.85rem;">
                <strong>Demo accounts</strong><br>
                admin · dr.smith · reception · patient1<br>
                Password: <code>password</code>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
