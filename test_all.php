<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#111;color:#0f0;padding:20px;font-family:monospace;font-size:13px;'>";
echo "=== ARGONAR DEEP TEST ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Database connection
echo "--- DATABASE ---\n";
try {
    require_once __DIR__ . '/includes/db.php';
    echo "[OK] Database connection\n";
} catch (Exception $e) {
    echo "[FAIL] Database: " . $e->getMessage() . "\n";
}

// 2. Check all tables exist
$tables = ['teams', 'solo_players', 'matches', 'tournament_results'];
foreach ($tables as $t) {
    try {
        $pdo->query("SELECT 1 FROM $t LIMIT 1");
        echo "[OK] Table '$t' exists\n";
    } catch (Exception $e) {
        echo "[FAIL] Table '$t': " . $e->getMessage() . "\n";
    }
}

// 3. Check all required columns
echo "\n--- COLUMNS ---\n";
$expected = [
    'teams' => ['id','game','team_name','team_logo','ref_code','contact_number','facebook_link','member_1','member_2','member_3','member_4','member_5','members_ranks','payment_proof','status','created_at'],
    'solo_players' => ['id','game','real_name','player_name','contact_number','facebook_link','rank_tier','preferred_role','ref_code','admin_rating','payment_proof','status','created_at'],
    'matches' => ['id','game','bracket_side','round','match_order','team1_name','team2_name','team1_score','team2_score','winner','status','scheduled_at','created_at'],
    'tournament_results' => ['id','game','season','placement','team_name','prize','created_at'],
];
foreach ($expected as $table => $cols) {
    $actual = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($cols as $col) {
        if (in_array($col, $actual)) {
            echo "[OK] $table.$col\n";
        } else {
            echo "[FAIL] $table.$col MISSING!\n";
        }
    }
}

// 4. Check all PHP files for syntax errors
echo "\n--- PHP SYNTAX ---\n";
$php_files = glob(__DIR__ . '/*.php');
$php_files = array_merge($php_files, glob(__DIR__ . '/includes/*.php'));
$php_files = array_merge($php_files, glob(__DIR__ . '/admin/*.php'));
foreach ($php_files as $file) {
    $output = [];
    $code = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $code);
    $name = str_replace(__DIR__ . '/', '', $file);
    if ($code === 0) {
        echo "[OK] $name\n";
    } else {
        echo "[FAIL] $name: " . implode(' ', $output) . "\n";
    }
}

// 5. Check uploads directory
echo "\n--- FILE SYSTEM ---\n";
$dirs = [
    __DIR__ . '/uploads' => 'uploads/',
    __DIR__ . '/uploads/payment_proofs' => 'uploads/payment_proofs/',
    __DIR__ . '/uploads/team_logos' => 'uploads/team_logos/',
    __DIR__ . '/images' => 'images/',
    __DIR__ . '/css' => 'css/',
];
foreach ($dirs as $path => $name) {
    if (is_dir($path)) {
        echo "[OK] $name exists" . (is_writable($path) ? " (writable)" : " (NOT WRITABLE!)") . "\n";
    } else {
        echo "[WARN] $name does not exist\n";
    }
}

// 6. Check .htaccess
echo "\n--- SERVER CONFIG ---\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo ".htaccess exists: " . (file_exists(__DIR__ . '/.htaccess') ? 'YES' : 'NO') . "\n";

// 7. Test all page URLs (internal include test)
echo "\n--- PAGE LOAD TEST ---\n";
$pages = [
    '/' => 'index.php',
    '/register.php?game=valorant' => 'register.php (valorant)',
    '/register.php?game=crossfire' => 'register.php (crossfire)',
    '/register.php?game=dota2' => 'register.php (dota2)',
    '/matchmaking.php?game=valorant' => 'matchmaking.php (valorant)',
    '/matchmaking.php?game=crossfire' => 'matchmaking.php (crossfire)',
    '/matchmaking.php?game=dota2' => 'matchmaking.php (dota2)',
    '/success.php?game=valorant' => 'success.php (team)',
    '/success.php?type=solo&game=dota2' => 'success.php (solo)',
    '/rules.php' => 'rules.php',
    '/contact.php' => 'contact.php',
    '/bracket.php' => 'bracket.php',
    '/bracket.php?game=valorant' => 'bracket.php (valorant)',
    '/leaderboard.php' => 'leaderboard.php',
    '/status.php' => 'status.php',
    '/meta-guide.php' => 'meta-guide.php',
    '/admin/' => 'admin/index.php',
];

$base = 'https://argonar.co';
foreach ($pages as $path => $label) {
    $url = $base . $path;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_NOBODY => false,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo "[FAIL] $label: curl error - $err\n";
    } elseif ($code >= 500) {
        echo "[FAIL] $label: HTTP $code\n";
    } elseif (stripos($body, 'Fatal error') !== false) {
        echo "[FAIL] $label: PHP Fatal Error found!\n";
    } elseif (stripos($body, 'Warning:') !== false && stripos($body, 'Warning:</') === false) {
        echo "[WARN] $label: PHP Warning found\n";
    } elseif (stripos($body, 'Notice:') !== false) {
        echo "[WARN] $label: PHP Notice found\n";
    } elseif ($code === 200 || $code === 302) {
        echo "[OK] $label (HTTP $code)\n";
    } else {
        echo "[WARN] $label: HTTP $code\n";
    }
}

// 8. Test team registration POST
echo "\n--- REGISTRATION TEST ---\n";
$ch = curl_init($base . '/register.php?game=dota2');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
    CURLOPT_POSTFIELDS => [
        'team_name' => '_DEEP_TEST_TEAM_',
        'contact_number' => '',
        'facebook_link' => '',
        'member_1' => 'Test1', 'member_rank_1' => 'Herald',
        'member_2' => 'Test2', 'member_rank_2' => 'Guardian',
        'member_3' => 'Test3', 'member_rank_3' => 'Crusader',
        'member_4' => 'Test4', 'member_rank_4' => 'Archon',
        'member_5' => 'Test5', 'member_rank_5' => 'Legend',
        'payment_note' => 'Deep test - auto cleanup',
        'agree_terms' => 'on',
    ],
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($code === 302) {
    echo "[OK] Team registration POST works (302 redirect)\n";
} else {
    echo "[FAIL] Team registration POST: HTTP $code\n";
    // Check for error in body
    if (preg_match('/alert-danger.*?<\/div>/s', $resp, $m)) {
        echo "     Error: " . strip_tags($m[0]) . "\n";
    }
}

// 9. Test solo registration POST
$ch = curl_init($base . '/matchmaking.php?game=dota2');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
    CURLOPT_POSTFIELDS => [
        'real_name' => 'Deep Test Player',
        'player_name' => '_DEEP_TEST_SOLO_',
        'contact_number' => '',
        'facebook_link' => '',
        'rank_tier' => 'Ancient',
        'preferred_role' => 'Mid (Pos 2)',
        'payment_note' => 'Deep test - auto cleanup',
        'agree_terms' => 'on',
    ],
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($code === 302) {
    echo "[OK] Solo registration POST works (302 redirect)\n";
} else {
    echo "[FAIL] Solo registration POST: HTTP $code\n";
    if (preg_match('/alert-danger.*?<\/div>/s', $resp, $m)) {
        echo "     Error: " . strip_tags($m[0]) . "\n";
    }
}

// 10. Test status check
$ch = curl_init($base . '/status.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_POSTFIELDS => 'search=_DEEP_TEST_TEAM_',
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($code === 200 && stripos($body, '_DEEP_TEST_TEAM_') !== false) {
    echo "[OK] Status check finds test team\n";
} else {
    echo "[WARN] Status check: HTTP $code, team not found in results\n";
}

// 11. Cleanup test data
echo "\n--- CLEANUP ---\n";
$d1 = $pdo->prepare("DELETE FROM teams WHERE team_name = '_DEEP_TEST_TEAM_'");
$d1->execute();
echo "Deleted test teams: " . $d1->rowCount() . "\n";

$d2 = $pdo->prepare("DELETE FROM solo_players WHERE player_name = '_DEEP_TEST_SOLO_'");
$d2->execute();
echo "Deleted test solos: " . $d2->rowCount() . "\n";

// 12. Data integrity
echo "\n--- DATA INTEGRITY ---\n";
$team_count = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$solo_count = $pdo->query("SELECT COUNT(*) FROM solo_players")->fetchColumn();
$match_count = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
echo "Teams: $team_count\n";
echo "Solo players: $solo_count\n";
echo "Matches: $match_count\n";

// Check for orphan ref codes (duplicates)
$dup_teams = $pdo->query("SELECT ref_code, COUNT(*) c FROM teams WHERE ref_code IS NOT NULL GROUP BY ref_code HAVING c > 1")->fetchAll();
$dup_solos = $pdo->query("SELECT ref_code, COUNT(*) c FROM solo_players WHERE ref_code IS NOT NULL GROUP BY ref_code HAVING c > 1")->fetchAll();
if (empty($dup_teams) && empty($dup_solos)) {
    echo "[OK] No duplicate ref codes\n";
} else {
    echo "[WARN] Duplicate ref codes found!\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "</pre>";
