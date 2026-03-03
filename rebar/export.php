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
$stmt = $db->prepare('SELECT * FROM rebar_lists WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$list = $stmt->fetch();

if (!$list) {
    flash('danger', 'Rebar list not found.');
    redirect('rebar/index.php');
}

// Fetch items
$stmt = $db->prepare('SELECT * FROM rebar_items WHERE rebar_list_id = ? ORDER BY item_no');
$stmt->execute([$list['id']]);
$items = $stmt->fetchAll();

// Build spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rebar Cutting List');

// Column widths
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(12);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(14);
$sheet->getColumnDimension('E')->setWidth(16);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(16);
$sheet->getColumnDimension('H')->setWidth(30);

// Title
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', $list['title']);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Info rows
$row = 3;
if ($list['project_name']) {
    $sheet->setCellValue("A{$row}", 'Project:');
    $sheet->setCellValue("B{$row}", $list['project_name']);
    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
}
if ($list['structural_member']) {
    $sheet->setCellValue("A{$row}", 'Structural Member:');
    $sheet->mergeCells("B{$row}:C{$row}");
    $sheet->setCellValue("B{$row}", $list['structural_member']);
    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
}
$sheet->setCellValue("A{$row}", 'Prepared By:');
$sheet->setCellValue("B{$row}", $list['prepared_by'] ?: '-');
$sheet->setCellValue("D{$row}", 'Date:');
$sheet->setCellValue("E{$row}", $list['date_prepared'] ? date('M d, Y', strtotime($list['date_prepared'])) : '-');
$sheet->getStyle("A{$row}")->getFont()->setBold(true);
$sheet->getStyle("D{$row}")->getFont()->setBold(true);
$row++;
$sheet->setCellValue("A{$row}", 'Checked By:');
$sheet->setCellValue("B{$row}", $list['checked_by'] ?: '-');
$sheet->setCellValue("D{$row}", 'Status:');
$sheet->setCellValue("E{$row}", ucfirst($list['status']));
$sheet->getStyle("A{$row}")->getFont()->setBold(true);
$sheet->getStyle("D{$row}")->getFont()->setBold(true);
$row += 2;

// Header row
$headerRow = $row;
$headers = ['#', 'Bar Size', 'No. of Pcs', 'Length/pc (m)', 'Total Length (m)', 'Wt/m (kg)', 'Total Wt (kg)', 'Description'];
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
    $sheet->setCellValue("B{$row}", $item['bar_size']);
    $sheet->setCellValue("C{$row}", $item['no_of_pieces']);
    $sheet->setCellValue("D{$row}", $item['length_per_pc']);
    $sheet->setCellValue("E{$row}", $item['total_length']);
    $sheet->setCellValue("F{$row}", $item['weight_per_meter']);
    $sheet->setCellValue("G{$row}", $item['total_weight']);
    $sheet->setCellValue("H{$row}", $item['description'] ?? '');

    $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle("D{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0.000');
    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.0000');
    $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.000');
    $sheet->getStyle("A{$row}:H{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $row++;
}

// Total
$row++;
$sheet->setCellValue("F{$row}", 'TOTAL WEIGHT:');
$sheet->setCellValue("G{$row}", $list['total_weight']);
$sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true)->setSize(12);
$sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.000');

// Download
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $list['title']) . '_Rebar.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
