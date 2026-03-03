<?php
// Argonar Construction - Database & Helpers
// Every page requires this file first.

session_start();

// Database config
define('DB_HOST', 'localhost');
define('DB_NAME', 'argonar_construction');
define('DB_USER', 'root');
define('DB_PASS', '');

// App config
define('APP_NAME', 'Argonar Construction');
define('APP_URL', '');

// PayRex config
define('PAYREX_SECRET_KEY', 'sk_live_REPLACE_ME');
define('PAYREX_PUBLIC_KEY', 'pk_live_Y21WRCgDucP26UEpqtctvYp8MySJ4u5V');
define('PAYREX_WEBHOOK_SECRET', 'whsk_REPLACE_ME');

// Subscription plans (amounts in centavos)
define('PLANS', [
    'daily'   => ['name' => '24-Hour Pass', 'amount' => 2000, 'display' => '₱20', 'hours' => 24],
    'monthly' => ['name' => 'Monthly Plan', 'amount' => 50000, 'display' => '₱500', 'hours' => 720],
]);

// PDO connection
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please run setup.sql first.');
}

// --- Helper Functions ---

/** Escape HTML output */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/** Build URL relative to app root */
function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

/** Format as Philippine Peso */
function currency(float $amount): string {
    return '₱' . number_format($amount, 2);
}

/** Format number */
function fmt(float $num, int $dec = 2): string {
    return number_format($num, $dec);
}

/** Asset URL */
function asset(string $path): string {
    return APP_URL . '/' . ltrim($path, '/');
}

// --- CSRF Protection ---

/** Generate CSRF token (stores in session) */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Output hidden CSRF field */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/** Check CSRF token on POST */
function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals(csrf_token(), $token)) {
            $_SESSION['flash'] = ['danger' => 'Invalid security token. Please try again.'];
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// --- Flash Messages ---

/** Set a flash message */
function flash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

/** Get and clear flash messages */
function get_flash(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// --- Redirect ---

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}
