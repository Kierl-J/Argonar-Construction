<?php
$pdo = new PDO("mysql:host=localhost;dbname=argonar_construction;charset=utf8mb4", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$d1 = $pdo->prepare("DELETE FROM teams WHERE team_name = ?");
$d1->execute(['Test Squad Alpha']);
echo "Deleted teams: " . $d1->rowCount() . "\n";

$d2 = $pdo->prepare("DELETE FROM solo_players WHERE player_name IN (?, ?)");
$d2->execute(['xJuanCF', 'MariaVAL']);
echo "Deleted solo players: " . $d2->rowCount() . "\n";

echo "Done.";
