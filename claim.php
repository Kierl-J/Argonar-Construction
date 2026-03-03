<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$user = require_login();

// Not a guest? No need to claim
if (empty($user['is_guest'])) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name)     $errors[] = 'Name is required.';
    if (!$email)    $errors[] = 'Email is required.';
    if (!$password) $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (!$errors) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $errors[] = 'This email is already taken.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, password = ?, is_guest = 0 WHERE id = ?');
        $stmt->execute([$name, $email, $hash, $user['id']]);

        flash('success', 'Account claimed! You can now log in with your email and password.');
        redirect('index.php');
    }
}

$pageTitle = 'Claim Your Account';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Claim Your Account</h5>
                <p class="text-muted small mb-4">Set your email and password so you can log in again later and keep your subscription.</p>

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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-check me-1"></i> Claim Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
