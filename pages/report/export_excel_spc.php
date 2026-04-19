<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend;

$data = json_decode($_POST['data'], true);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


// ==========================
// HELPER BORDER
// ==========================
function setBorder($sheet, $range)
{
    $sheet->getStyle($range)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ],
            'outline' => [
                'borderStyle' => Border::BORDER_MEDIUM
            ]
        ]
    ]);
}

$rp = "Report " . $data['site_label'];
// ==========================
// TITLE
// ==========================
$sheet->setCellValue('A1', $rp);
$sheet->mergeCells('A1:AD1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


$rp_judul = "Report_" . $data['site_label'] . "_" . date("d M Y", strtotime($data['production_date']));

use PhpOffice\PhpSpreadsheet\Shared\Date;

// convert ke Excel date
$excelDate = Date::PHPToExcel(strtotime($data['production_date']));

$sheet->setCellValue('A2', $excelDate);

$sheet->getStyle('A2')->getFont()->setSize(14);
$sheet->mergeCells('A2:AD2');
// format tampilannya
$sheet->getStyle('A2')
    ->getNumberFormat()
    ->setFormatCode('dd mmm yyyy');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
// ==========================
// LEFT PANEL
// ==========================
$row = 3;

$rows = [
    ['Standard Upper', $data['usl']],
    ['Standard Lower', $data['lsl']],
    ['Lower Boundary', $data['debug_lower_boundary']],
    ['Interval Width', $data['debug_interval_width']],
    ['Quantity', $data['debug_total_data']],
    ['Max', $data['max_val']],
    ['Min', $data['min_val']],
    ['Average', $data['rata_rata']],
    ['Stdev', $data['standar_deviasi']],
    ['Cp', $data['cp']],
    ['Cpk', $data['cpk']],
    ['NG Estimation', $data['estimated_defect_rate']],
    ['NG Actual', $data['out_of_control_percent']],
];

foreach ($rows as $r) {
    $sheet->setCellValue("A$row", $r[0]);
    $sheet->setCellValue("B$row", $r[1]);
    if ($r[0] == 'NG Estimation' || $r[0] == 'NG Actual') {
        $sheet->getStyle("A$row:B$row")->getFill()
            ->getStartColor()->setRGB('F8CBAD'); // orange soft
    }

    $sheet->getStyle("A$row")->getFont()->setBold(true);

    $sheet->getStyle("A$row:B$row")->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FFF2CC');

    $row++;
}

setBorder($sheet, "A3:B" . ($row - 1));


// ==========================
// RIGHT PANEL
// ==========================
$sheet->setCellValue("C3", "CSV");
$sheet->setCellValue("D3", $data['debug_total_data']);

$sheet->setCellValue("C4", "PROD");
$sheet->setCellValue("D4", "-");

$sheet->setCellValue("C5", "DIFF");
$sheet->setCellValue("D5", $data['debug_total_data']);

$sheet->getStyle("C3:C5")->getFont()->setBold(true);

$sheet->getStyle("C3:D5")->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('D9E1F2');

setBorder($sheet, "C3:D5");


// ===== CP =====
$sheet->mergeCells("C7:D7");
$sheet->setCellValue("C7", "CP");

$sheet->mergeCells("C8:D8");
$sheet->setCellValue("C8", "Std > " . $data["std_limit_cp"]);

$sheet->mergeCells("C9:D9");
$sheet->setCellValue("C9", number_format($data['cp'], 3));

$statusCP = ($data['cp'] >= $data["std_limit_cp"]) ? 'OK' : 'NG';

$sheet->mergeCells("C10:D10");
$sheet->setCellValue("C10", $statusCP);

$sheet->getStyle("C7:C10")->getFont()->setBold(true);
$sheet->getStyle("C10")->getFont()->setBold(true);

// warna
$sheet->getStyle("C10")->getFont()->getColor()->setRGB($statusCP == 'OK' ? '008000' : 'FF0000');

$sheet->getStyle("C7:D10")->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('FCE4D6');

setBorder($sheet, "C7:D10");


// ===== CPK =====
$sheet->mergeCells("C12:D12");
$sheet->setCellValue("C12", "CPK");

$sheet->mergeCells("C13:D13");
$sheet->setCellValue("C13", "Std > " . $data["std_limit_cp"]);

$sheet->mergeCells("C14:D14");
$sheet->setCellValue("C14", number_format($data['cpk'], 3));

$statusCPK = ($data['cpk'] >= $data["std_limit_cp"]) ? 'OK' : 'NG';

$sheet->mergeCells("C15:D15");
$sheet->setCellValue("C15", $statusCPK);

$sheet->getStyle("C12:C15")->getFont()->setBold(true);
$sheet->getStyle("C15")->getFont()->setBold(true);

$sheet->getStyle("C15")->getFont()->getColor()->setRGB($statusCPK == 'OK' ? '008000' : 'FF0000');

$sheet->getStyle("C12:D15")->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('E2EFDA');

setBorder($sheet, "C12:D15");


// ==========================
// TABLE
// ==========================
$startRow = 3;
$startCol = 'G';

$sheet->setCellValue("F$startRow", "Interval");

$style = $sheet->getStyle("F$startRow");
$style->getFont()->setBold(true);
$style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$col = $startCol;

foreach ($data['series_data'] as $i => $d) {

    $cell = $col . $startRow;

    $sheet->setCellValue($cell, $i);

    $style = $sheet->getStyle($cell);
    $style->getFont()->setBold(true);
    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $col++;
}

$labels = ['Mid', 'Min', 'Max', 'Observed', 'Predicted'];

for ($i = 0; $i < 5; $i++) {

    $labelCell = "F" . ($startRow + $i + 1);
    $sheet->setCellValue($labelCell, $labels[$i]);

    // bold
    $sheet->getStyle($labelCell)
        ->getFont()
        ->setBold(true);

    // align center
    $sheet->getStyle($labelCell)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $col = $startCol;

    foreach ($data['series_data'] as $index => $d) {

        if ($i == 0) $val = $d[0];
        if ($i == 1) $val = $data['lsl'];
        if ($i == 2) $val = $data['usl'];
        if ($i == 3) $val = $d[1];
        if ($i == 4) $val = $data['normal_curve'][$index][1] ?? 0;

        $cell = $col . ($startRow + $i + 1);

        $sheet->setCellValue($cell, $val);

        $sheet->getStyle($cell)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $col++;
    }
}

// observed highlight
$obsRow = $startRow + 4;

$sheet->getStyle("F$obsRow:$col$obsRow")
    ->getFill()->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('FFFF00');

$sheet->getStyle("F$obsRow:$col$obsRow")->getFont()->setBold(true);

setBorder($sheet, "F3:$col" . ($startRow + 5));


// ==========================
// CHART
// ==========================
$chartStart = 11;

$sheet->setCellValue("H$chartStart", "Mid");
$sheet->setCellValue("I$chartStart", "Observed");

$r = $chartStart + 1;

foreach ($data['series_data'] as $d) {
    $sheet->setCellValue("H$r", $d[0]);
    $sheet->setCellValue("I$r", $d[1]);
    $r++;
}

$lastRow = $r - 1;

$categories = [new DataSeriesValues('String', "Worksheet!\$H\$" . ($chartStart + 1) . ":\$H\$$lastRow")];
$values = [new DataSeriesValues('Number', "Worksheet!\$I\$" . ($chartStart + 1) . ":\$I\$$lastRow")];

$series = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,
    range(0, count($values) - 1),
    [],
    $categories,
    $values
);

$plotArea = new PlotArea(null, [$series]);
$legend = new Legend(Legend::POSITION_TOP, null, false);

$chart = new Chart('Histogram', null, $legend, $plotArea);

$chart->setTopLeftPosition('K11');
$chart->setBottomRightPosition('S35');

$sheet->addChart($chart);


// ==========================
// AUTO SIZE
// ==========================
foreach (range('A', 'Z') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}


// ==========================
// OUTPUT
// ==========================
$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
$h = 'Content-Disposition: attachment;filename="' . $rp_judul . '.xlsx"';
header($h);

$writer->save('php://output');
exit;
