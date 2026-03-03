<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$tab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'login';

    if ($action === 'register') {
        $tab = 'register';
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$name)     $errors[] = 'Name is required.';
        if (!$email)    $errors[] = 'Email is required.';
        if (!$password) $errors[] = 'Password is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

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
            $intended = $_SESSION['intended_url'] ?? url('index.php');
            unset($_SESSION['intended_url']);
            header('Location: ' . $intended);
            exit;
        }
    } else {
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

                $intended = $_SESSION['intended_url'] ?? url('index.php');
                unset($_SESSION['intended_url']);
                header('Location: ' . $intended);
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
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

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item flex-fill text-center">
                <a class="nav-link <?= $tab === 'login' ? 'active' : '' ?>" data-bs-toggle="tab" href="#loginTab">Log In</a>
            </li>
            <li class="nav-item flex-fill text-center">
                <a class="nav-link <?= $tab === 'register' ? 'active' : '' ?>" data-bs-toggle="tab" href="#registerTab">Register</a>
            </li>
        </ul>

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

        <div class="tab-content">
            <!-- Login Tab -->
            <div class="tab-pane fade <?= $tab === 'login' ? 'show active' : '' ?>" id="loginTab">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="login">
                    <div class="mb-3">
                        <label class="form-label" for="login-email">Email</label>
                        <input type="email" class="form-control" id="login-email" name="email"
                               value="<?= $tab === 'login' ? h($_POST['email'] ?? '') : '' ?>" required <?= $tab === 'login' ? 'autofocus' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="login-password">Password</label>
                        <input type="password" class="form-control" id="login-password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log In</button>
                </form>
            </div>

            <!-- Register Tab -->
            <div class="tab-pane fade <?= $tab === 'register' ? 'show active' : '' ?>" id="registerTab">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="register">
                    <div class="mb-3">
                        <label class="form-label" for="reg-name">Name</label>
                        <input type="text" class="form-control" id="reg-name" name="name"
                               value="<?= $tab === 'register' ? h($_POST['name'] ?? '') : '' ?>" required <?= $tab === 'register' ? 'autofocus' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reg-email">Email</label>
                        <input type="email" class="form-control" id="reg-email" name="email"
                               value="<?= $tab === 'register' ? h($_POST['email'] ?? '') : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reg-password">Password</label>
                        <input type="password" class="form-control" id="reg-password" name="password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
