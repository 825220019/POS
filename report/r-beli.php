<?php
require "../config/config.php";
require "../config/functions.php";
require('../asset/fpdf/vendor/autoload.php');

// Validasi tanggal
$tgl1 = isset($_GET['tgl1']) ? $_GET['tgl1'] : date('Y-m-d');
$tgl2 = isset($_GET['tgl2']) ? $_GET['tgl2'] : date('Y-m-d');
$supplier = isset($_GET['supplier']) ? $_GET['supplier'] : '';

$where = "h.tgl_beli BETWEEN '$tgl1' AND '$tgl2'";

if (!empty($supplier)) {
    // Tambahkan filter supplier jika ada
    $where .= " AND h.id_supplier = '$supplier'";
}

// Query data pembelian sesuai periode
$dataBeli = getData("
    SELECT 
        h.no_beli, 
        h.tgl_beli, 
        s.nama AS nama_supplier, 
        h.total
    FROM tbl_beli_head h
    JOIN tbl_supplier s ON h.id_supplier = s.id_supplier
    WHERE $where
    ORDER BY h.tgl_beli ASC
");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Laporan Pembelian', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(190, 8, 'Periode: ' . in_date($tgl1) . ' s/d ' . in_date($tgl2), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 10, 'No', 1, 0, 'C');
$pdf->Cell(40, 10, 'Tgl Pembelian', 1, 0, 'C');
$pdf->Cell(50, 10, 'No Pembelian', 1, 0, 'C');
$pdf->Cell(60, 10, 'Supplier', 1, 0, 'C');
$pdf->Cell(30, 10, 'Total', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$no = 1;
$totalSemua = 0;
foreach ($dataBeli as $beli) {
    $pdf->Cell(10, 8, $no++, 1, 0, 'C');
    $pdf->Cell(40, 8, in_date($beli['tgl_beli']), 1, 0, 'C');
    $pdf->Cell(50, 8, $beli['no_beli'], 1, 0, 'C');
    $pdf->Cell(60, 8, $beli['nama_supplier'], 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($beli['total'], 0, ',', '.'), 1, 1, 'R');
    $totalSemua += $beli['total'];
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(160, 10, 'Total Keseluruhan', 1, 0, 'R');
$pdf->Cell(30, 10, number_format($totalSemua, 0, ',', '.'), 1, 1, 'R');

$pdf->Output();
?>
