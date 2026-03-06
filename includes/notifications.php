<?php
// Argonar Construction - Notification Helpers

/** Create a notification for a user */
function notify(PDO $db, int $user_id, string $title, string $message, string $type = 'info', ?string $link = null): int {
    $stmt = $db->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$user_id, $type, $title, $message, $link]);
    return (int)$db->lastInsertId();
}

/** Get unread notification count for a user */
function unread_count(PDO $db, int $user_id): int {
    $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

/** Get recent notifications for a user (for dropdown) */
function get_notifications(PDO $db, int $user_id, int $limit = 10): array {
    $stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

/** Mark a single notification as read */
function mark_read(PDO $db, int $notif_id, int $user_id): void {
    $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$notif_id, $user_id]);
}

/** Mark all notifications as read */
function mark_all_read(PDO $db, int $user_id): void {
    $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0')->execute([$user_id]);
}
