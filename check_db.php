<?php
// Test actual registration
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/db.php';

echo "Upload dir exists: " . (is_dir(__DIR__ . '/uploads/payment_proofs') ? 'YES' : 'NO') . "\n";
echo "Upload dir writable: " . (is_writable(__DIR__ . '/uploads') ? 'YES' : 'NO') . "\n";
echo "Base dir writable: " . (is_writable(__DIR__) ? 'YES' : 'NO') . "\n";
echo "Max upload: " . ini_get('upload_max_filesize') . "\n";
echo "Post max: " . ini_get('post_max_size') . "\n";

// Try a test insert
try {
    $stmt = $pdo->prepare("INSERT INTO teams (game, team_name, team_logo, ref_code, contact_number, facebook_link, member_1, member_2, member_3, member_4, member_5, members_ranks, payment_proof, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['dota2', 'TEST_DELETE_ME', '', 'TEST-0000', '', '', 'a', 'b', 'c', 'd', 'e', '', '', 'pending']);
    $id = $pdo->lastInsertId();
    echo "Test insert OK, id=$id\n";
    $pdo->prepare("DELETE FROM teams WHERE id = ?")->execute([$id]);
    echo "Test delete OK\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
