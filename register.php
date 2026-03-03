<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

// Already logged in?
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name)     $errors[] = 'Name is required.';
    if (!$email)    $errors[] = 'Email is required.';
    if (!$password) $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    // Check email uniqueness
    if (!$errors) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);

        $_SESSION['user_id'] = $db->lastInsertId();
        session_regenerate_id(true);

        flash('success', 'Account created! Welcome to Argonar Construction.');
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | <?= APP_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?= asset('images/logo.svg') ?>" alt="<?= APP_NAME ?>">
        </div>
        <h5 class="text-center fw-bold mb-4">Create Account</h5>

        <?php if ($errors): ?>
        <div class="alert alert-danger small">
            <?php foreach ($errors as $e): ?>
            <div><?= h($e) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= h($_POST['name'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= h($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
        </form>
        <p class="text-center text-muted small mb-0">
            Already have an account? <a href="<?= url('login.php') ?>">Log In</a>
        </p>
    </div>
</div>
</body>
</html>
