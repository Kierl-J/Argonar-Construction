<?php
$pdo = new PDO("mysql:host=localhost;dbname=argonar_construction;charset=utf8mb4", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== TEAMS COLUMNS ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM teams")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n\n";

echo "=== SOLO_PLAYERS COLUMNS ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM solo_players")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n";
