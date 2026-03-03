<?php
require __DIR__ . '/includes/db.php';

$_SESSION = [];
session_destroy();

header('Location: ' . url('index.php'));
exit;
