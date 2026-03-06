<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/subscription.php';
require __DIR__ . '/includes/notifications.php';

$user = require_login();

// Mark single notification as read
if (isset($_GET['mark'])) {
    mark_read($db, (int)$_GET['mark'], $user['id']);
    redirect('notifications.php');
}

// Mark all as read
if (isset($_GET['action']) && $_GET['action'] === 'read_all') {
    mark_all_read($db, $user['id']);
    flash('success', 'All notifications marked as read.');
    redirect('notifications.php');
}

// Handle mark_notif from linked pages (mark as read then continue to link)
if (isset($_GET['mark_notif'])) {
    mark_read($db, (int)$_GET['mark_notif'], $user['id']);
}

// Get all notifications
$stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$user['id']]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Notifications</h5>
        <p class="text-muted small mb-0"><?= unread_count($db, $user['id']) ?> unread</p>
    </div>
    <?php if (unread_count($db, $user['id']) > 0): ?>
    <a href="<?= url('notifications.php?action=read_all') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-check-double me-1"></i> Mark All Read
    </a>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
<div class="empty-state">
    <i class="fas fa-bell-slash"></i>
    <h6>No Notifications</h6>
    <p class="text-muted">You're all caught up.</p>
</div>
<?php else: ?>
<div class="card card-custom">
    <div class="list-group list-group-flush">
        <?php foreach ($notifications as $n): ?>
        <div class="list-group-item <?= $n['is_read'] ? '' : 'bg-light' ?> d-flex align-items-start gap-3 py-3">
            <i class="fas fa-<?= match($n['type']) { 'warning' => 'exclamation-triangle text-warning', 'success' => 'check-circle text-success', 'danger' => 'times-circle text-danger', 'renewal' => 'sync-alt text-primary', default => 'info-circle text-info' } ?> fa-lg mt-1"></i>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <strong class="small"><?= h($n['title']) ?></strong>
                    <span class="text-muted" style="font-size:.7rem;white-space:nowrap;"><?= time_ago($n['created_at']) ?></span>
                </div>
                <p class="text-muted small mb-1"><?= h($n['message']) ?></p>
                <?php if ($n['link']): ?>
                <a href="<?= url($n['link']) ?>" class="small"><?= $n['type'] === 'renewal' ? 'Renew Now' : 'View' ?> &rarr;</a>
                <?php endif; ?>
            </div>
            <?php if (!$n['is_read']): ?>
            <a href="<?= url('notifications.php?mark=' . $n['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Mark read">
                <i class="fas fa-check"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
