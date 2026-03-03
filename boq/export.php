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

$user = require_login();
require_access();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM boqs WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$boq = $stmt->fetch();

if (!$boq) {
    flash('danger', 'BOQ not found.');
    redirect('boq/index.php');
}

// Fetch items
$stmt = $db->prepare('SELECT * FROM boq_items WHERE boq_id = ? ORDER BY item_no');
$stmt->execute([$boq['id']]);
$items = $stmt->fetchAll();

// Build spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('BOQ');

// Column widths
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(10);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(18);

// Title
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', $boq['title']);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Info rows
$row = 3;
if ($boq['description']) {
    $sheet->mergeCells("A{$row}:F{$row}");
    $sheet->setCellValue("A{$row}", $boq['description']);
    $row++;
}
$sheet->setCellValue("A{$row}", 'Prepared By:');
$sheet->setCellValue("B{$row}", $boq['prepared_by'] ?: '-');
$sheet->setCellValue("D{$row}", 'Date:');
$sheet->setCellValue("E{$row}", $boq['date_prepared'] ? date('M d, Y', strtotime($boq['date_prepared'])) : '-');
$row++;
$sheet->setCellValue("A{$row}", 'Checked By:');
$sheet->setCellValue("B{$row}", $boq['checked_by'] ?: '-');
$sheet->setCellValue("D{$row}", 'Status:');
$sheet->setCellValue("E{$row}", ucfirst($boq['status']));
$row += 2;

// Header row
$headerRow = $row;
$headers = ['#', 'Description', 'Unit', 'Quantity', 'Unit Cost', 'Amount'];
foreach ($headers as $col => $header) {
    $cell = chr(65 + $col) . $row;
    $sheet->setCellValue($cell, $header);
}

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray($headerStyle);

$row++;

// Items
foreach ($items as $item) {
    $sheet->setCellValue("A{$row}", $item['item_no']);
    $sheet->setCellValue("B{$row}", $item['description']);
    $sheet->setCellValue("C{$row}", $item['unit']);
    $sheet->setCellValue("D{$row}", $item['quantity']);
    $sheet->setCellValue("E{$row}", $item['unit_cost']);
    $sheet->setCellValue("F{$row}", $item['amount']);

    $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $row++;
}

// Totals
$row++;
$markup = $boq['total_amount'] * ($boq['markup_percentage'] / 100);
$after_markup = $boq['total_amount'] + $markup;
$vat = $after_markup * ($boq['vat_percentage'] / 100);

$sheet->setCellValue("E{$row}", 'Subtotal:');
$sheet->setCellValue("F{$row}", $boq['total_amount']);
$sheet->getStyle("E{$row}")->getFont()->setBold(true);
$sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
$row++;

if ($boq['markup_percentage'] > 0) {
    $sheet->setCellValue("E{$row}", 'Markup (' . $boq['markup_percentage'] . '%):');
    $sheet->setCellValue("F{$row}", $markup);
    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
    $row++;
}

if ($boq['vat_percentage'] > 0) {
    $sheet->setCellValue("E{$row}", 'VAT (' . $boq['vat_percentage'] . '%):');
    $sheet->setCellValue("F{$row}", $vat);
    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
    $row++;
}

$sheet->setCellValue("E{$row}", 'GRAND TOTAL:');
$sheet->setCellValue("F{$row}", $boq['grand_total']);
$sheet->getStyle("E{$row}:F{$row}")->getFont()->setBold(true)->setSize(12);
$sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

// Download
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $boq['title']) . '_BOQ.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
