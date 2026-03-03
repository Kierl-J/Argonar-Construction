<?php
// Redirect to login page (register tab is built-in)
require __DIR__ . '/includes/db.php';
header('Location: ' . url('login.php#registerTab'));
exit;
