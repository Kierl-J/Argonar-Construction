<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';

$user = require_login();
require_access();

// Whitelist of allowed filenames (prevents path traversal)
$allowedFiles = [
    'boq_template.xlsx',
    'cost_estimate_template.xlsx',
    'project_schedule_template.xlsx',
    'daily_report_template.xlsx',
    'material_requisition_template.xlsx',
];

$file = $_GET['file'] ?? '';

if (!in_array($file, $allowedFiles, true)) {
    flash('danger', 'Invalid template file.');
    redirect('templates/index.php');
}

$filepath = __DIR__ . '/files/' . $file;

if (!file_exists($filepath)) {
    flash('danger', 'Template file not found.');
    redirect('templates/index.php');
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: max-age=0');
readfile($filepath);
exit;
