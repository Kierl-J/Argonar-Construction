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
$stmt = $db->prepare('SELECT * FROM documents WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$document = $stmt->fetch();

if (!$document) {
    flash('danger', 'Document not found.');
    redirect('documents/index.php');
}

$stmt = $db->prepare('SELECT * FROM document_items WHERE document_id = ? ORDER BY item_no');
$stmt->execute([$document['id']]);
$items = $stmt->fetchAll();

$docTypeLabels = [
    'scope_of_work'        => 'Scope of Work',
    'material_requisition'  => 'Material Requisition',
    'progress_report'       => 'Progress Report',
    'change_order'          => 'Change Order',
];

$fieldLabels = [
    'contractor'             => 'Contractor',
    'client'                 => 'Client',
    'scope_description'      => 'Scope Description',
    'signatory_1_name'       => 'Signatory 1',
    'signatory_1_title'      => 'Signatory 1 Title',
    'signatory_2_name'       => 'Signatory 2',
    'signatory_2_title'      => 'Signatory 2 Title',
    'requested_by'           => 'Requested By',
    'date_needed'            => 'Date Needed',
    'approved_by'            => 'Approved By',
    'report_date'            => 'Report Date',
    'period_from'            => 'Period From',
    'period_to'              => 'Period To',
    'prepared_by'            => 'Prepared By',
    'weather_conditions'     => 'Weather',
    'issues'                 => 'Issues',
    'change_order_no'        => 'CO No.',
    'date'                   => 'Date',
    'reason'                 => 'Reason',
    'original_contract_amount'=> 'Original Amount',
    'revised_amount'         => 'Revised Amount',
];

$itemColumnConfig = [
    'scope_of_work'        => ['description', 'remarks'],
    'material_requisition'  => ['description', 'quantity', 'unit', 'remarks'],
    'progress_report'       => ['description', 'percentage_complete', 'remarks'],
    'change_order'          => ['description', 'quantity', 'unit', 'unit_cost', 'amount'],
];

$columnHeaders = [
    'description'         => 'Description',
    'quantity'            => 'Qty',
    'unit'                => 'Unit',
    'unit_cost'           => 'Unit Cost (₱)',
    'amount'              => 'Amount (₱)',
    'percentage_complete' => '% Complete',
    'remarks'             => 'Remarks',
];

$type = $document['doc_type'];
$docData = json_decode($document['data'] ?? '{}', true) ?: [];
$columns = $itemColumnConfig[$type] ?? ['description'];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle(substr($docTypeLabels[$type] ?? 'Document', 0, 31));

// Title
$lastCol = chr(64 + count($columns) + 1); // +1 for item_no column
$sheet->mergeCells("A1:{$lastCol}1");
$sheet->setCellValue('A1', $document['title']);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', $docTypeLabels[$type] ?? $type);
$sheet->mergeCells("A2:{$lastCol}2");
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getFont()->setItalic(true);

// Info rows
$row = 4;
if ($document['project_name']) {
    $sheet->setCellValue("A{$row}", 'Project:');
    $sheet->setCellValue("B{$row}", $document['project_name']);
    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
}
$sheet->setCellValue("A{$row}", 'Status:');
$sheet->setCellValue("B{$row}", ucfirst($document['status']));
$sheet->getStyle("A{$row}")->getFont()->setBold(true);
$row++;

foreach ($docData as $key => $val) {
    if (!$val) continue;
    $sheet->setCellValue("A{$row}", ($fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key))) . ':');
    $sheet->setCellValue("B{$row}", $val);
    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
}

$row++;

// Item headers
$headerRow = $row;
$col = 'A';
$sheet->setCellValue("{$col}{$row}", '#');
$sheet->getColumnDimension($col)->setWidth(6);
$col++;

foreach ($columns as $colKey) {
    $sheet->setCellValue("{$col}{$row}", $columnHeaders[$colKey] ?? ucfirst($colKey));
    $width = 20;
    if ($colKey === 'description') $width = 35;
    elseif ($colKey === 'remarks') $width = 30;
    elseif (in_array($colKey, ['quantity', 'percentage_complete'])) $width = 12;
    elseif ($colKey === 'unit') $width = 10;
    elseif (in_array($colKey, ['unit_cost', 'amount'])) $width = 16;
    $sheet->getColumnDimension($col)->setWidth($width);
    $col++;
}

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray($headerStyle);

$row++;

// Item data
foreach ($items as $item) {
    $col = 'A';
    $sheet->setCellValue("{$col}{$row}", $item['item_no']);
    $col++;

    foreach ($columns as $colKey) {
        $val = $item[$colKey] ?? '';
        $sheet->setCellValue("{$col}{$row}", $val);

        if (in_array($colKey, ['unit_cost', 'amount']))
            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        if ($colKey === 'quantity')
            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0.000');
        if ($colKey === 'percentage_complete')
            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('0.00"%"');

        $col++;
    }

    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $row++;
}

// Total row for change_order
if ($type === 'change_order') {
    $totalAmount = array_sum(array_column($items, 'amount'));
    $amtColIndex = array_search('amount', $columns);
    if ($amtColIndex !== false) {
        $amtCol = chr(66 + $amtColIndex); // B + offset
        $sheet->setCellValue("A{$row}", '');
        $prevCol = chr(ord($amtCol) - 1);
        $sheet->setCellValue("{$prevCol}{$row}", 'Total:');
        $sheet->setCellValue("{$amtCol}{$row}", $totalAmount);
        $sheet->getStyle("{$prevCol}{$row}:{$amtCol}{$row}")->getFont()->setBold(true);
        $sheet->getStyle("{$amtCol}{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
    }
}

// Download
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $document['title']) . '_' . str_replace(' ', '_', $docTypeLabels[$type] ?? 'Document') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
