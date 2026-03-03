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

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } else {
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            session_regenerate_id(true);

            // Redirect to intended URL or homepage
            $intended = $_SESSION['intended_url'] ?? url('index.php');
            unset($_SESSION['intended_url']);
            header('Location: ' . $intended);
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$flashMessages = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | <?= APP_NAME ?></title>
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
        <h5 class="text-center fw-bold mb-4">Log In</h5>

        <?php if (!empty($flashMessages)): foreach ($flashMessages as $type => $msg): ?>
        <div class="alert alert-<?= $type ?> small"><?= h($msg) ?></div>
        <?php endforeach; endif; ?>

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
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= h($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Log In</button>
        </form>
        <p class="text-center text-muted small mb-0">
            Don't have an account? <a href="<?= url('register.php') ?>">Register</a>
        </p>
    </div>
</div>
</body>
</html>
