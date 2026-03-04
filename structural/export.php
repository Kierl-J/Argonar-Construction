<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/subscription.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$user = require_login();
require_access();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM structural_estimates WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$estimate = $stmt->fetch();

if (!$estimate) {
    flash('danger', 'Structural estimate not found.');
    redirect('structural/index.php');
}

// Fetch items
$stmt = $db->prepare('SELECT * FROM structural_estimate_items WHERE estimate_id = ? ORDER BY item_no');
$stmt->execute([$estimate['id']]);
$items = $stmt->fetchAll();

// Build spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Structural Estimate');

// Column widths
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(14);
$sheet->getColumnDimension('C')->setWidth(22);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(16);
$sheet->getColumnDimension('G')->setWidth(18);
$sheet->getColumnDimension('H')->setWidth(28);

// Title
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', $estimate['title']);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Info rows
$row = 3;
if ($estimate['project_name']) {
    $sheet->setCellValue("A{$row}", 'Project:');
    $sheet->setCellValue("B{$row}", $estimate['project_name']);
    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
}
if ($estimate['location']) {
    $sheet->setCellValue("A{$row}", 'Location:');
    $sheet->setCellValue("B{$row}", $estimate['location']);
    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
}
$sheet->setCellValue("A{$row}", 'Prepared By:');
$sheet->setCellValue("B{$row}", $estimate['prepared_by'] ?: '-');
$sheet->setCellValue("D{$row}", 'Date:');
$sheet->setCellValue("E{$row}", $estimate['date_prepared'] ? date('M d, Y', strtotime($estimate['date_prepared'])) : '-');
$sheet->getStyle("A{$row}")->getFont()->setBold(true);
$sheet->getStyle("D{$row}")->getFont()->setBold(true);
$row++;
$sheet->setCellValue("A{$row}", 'Checked By:');
$sheet->setCellValue("B{$row}", $estimate['checked_by'] ?: '-');
$sheet->setCellValue("D{$row}", 'Status:');
$sheet->setCellValue("E{$row}", ucfirst($estimate['status']));
$sheet->getStyle("A{$row}")->getFont()->setBold(true);
$sheet->getStyle("D{$row}")->getFont()->setBold(true);
$row += 2;

// Header row
$headerRow = $row;
$headers = ['#', 'Category', 'Member Name', 'Qty', 'Unit', 'Unit Cost (₱)', 'Amount (₱)', 'Description'];
foreach ($headers as $col => $header) {
    $cell = chr(65 + $col) . $row;
    $sheet->setCellValue($cell, $header);
}

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$sheet->getStyle("A{$headerRow}:H{$headerRow}")->applyFromArray($headerStyle);

$row++;

// Items
foreach ($items as $item) {
    $sheet->setCellValue("A{$row}", $item['item_no']);
    $sheet->setCellValue("B{$row}", ucfirst($item['category']));
    $sheet->setCellValue("C{$row}", $item['member_name']);
    $sheet->setCellValue("D{$row}", $item['quantity']);
    $sheet->setCellValue("E{$row}", $item['unit']);
    $sheet->setCellValue("F{$row}", $item['unit_cost']);
    $sheet->setCellValue("G{$row}", $item['amount']);
    $sheet->setCellValue("H{$row}", $item['description'] ?? '');

    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0.000');
    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle("A{$row}:H{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $row++;
}

// Summary section
$row += 1;
$sheet->setCellValue("F{$row}", 'Concrete:');
$sheet->setCellValue("G{$row}", $estimate['total_concrete']);
$sheet->getStyle("F{$row}")->getFont()->setBold(true);
$sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
$row++;

$sheet->setCellValue("F{$row}", 'Steel:');
$sheet->setCellValue("G{$row}", $estimate['total_steel']);
$sheet->getStyle("F{$row}")->getFont()->setBold(true);
$sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
$row++;

$sheet->setCellValue("F{$row}", 'Formwork:');
$sheet->setCellValue("G{$row}", $estimate['total_formwork']);
$sheet->getStyle("F{$row}")->getFont()->setBold(true);
$sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
$row++;

$sheet->setCellValue("F{$row}", 'SUBTOTAL:');
$sheet->setCellValue("G{$row}", $estimate['subtotal']);
$sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
$sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
$row++;

$sheet->setCellValue("F{$row}", 'Contingency (' . number_format($estimate['contingency_percentage'], 2) . '%):');
$sheet->setCellValue("G{$row}", $estimate['contingency_amount']);
$sheet->getStyle("F{$row}")->getFont()->setBold(true);
$sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
$row++;

$sheet->setCellValue("F{$row}", 'GRAND TOTAL:');
$sheet->setCellValue("G{$row}", $estimate['grand_total']);
$sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true)->setSize(12);
$sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

// Download
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $estimate['title']) . '_Structural_Estimate.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
