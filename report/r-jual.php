<?php
require "../config/config.php";
require "../config/functions.php";
require('../asset/fpdf/vendor/autoload.php');

// Ambil parameter tanggal dengan validasi
$tgl1 = isset($_GET['tgl1']) ? $_GET['tgl1'] : date('Y-m-d');
$tgl2 = isset($_GET['tgl2']) ? $_GET['tgl2'] : date('Y-m-d');
$pelanggan = isset($_GET['pelanggan']) ? $_GET['pelanggan'] : '';

$where = "h.tgl_jual BETWEEN '$tgl1' AND '$tgl2'";
if (!empty($pelanggan)) {
    $where .= " AND h.id_pelanggan = '$pelanggan'";
}

// Ambil data penjualan sesuai periode
$dataJual = getData("
    SELECT 
        h.no_jual, 
        h.tgl_jual, 
        p.nama AS nama_pelanggan, 
        h.total
    FROM tbl_jual_head h
    LEFT JOIN tbl_pelanggan p ON h.id_pelanggan = p.id_pelanggan
    WHERE $where
    ORDER BY h.tgl_jual ASC
");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Laporan Penjualan', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(190, 8, 'Periode: ' . in_date($tgl1) . ' s/d ' . in_date($tgl2), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 10, 'No', 1, 0, 'C');
$pdf->Cell(40, 10, 'Tgl Penjualan', 1, 0, 'C');
$pdf->Cell(50, 10, 'No Penjualan', 1, 0, 'C');
$pdf->Cell(60, 10, 'Pelanggan', 1, 0, 'C');
$pdf->Cell(30, 10, 'Total', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$no = 1;
$totalSemua = 0;
foreach ($dataJual as $jual) {
    $pdf->Cell(10, 8, $no++, 1, 0, 'C');
    $pdf->Cell(40, 8, in_date($jual['tgl_jual']), 1, 0, 'C');
    $pdf->Cell(50, 8, $jual['no_jual'], 1, 0, 'C');
    $pdf->Cell(60, 8, $jual['nama_pelanggan'] ?? 'Umum', 1, 0, 'C');
    $pdf->Cell(30, 8, number_format($jual['total'], 0, ',', '.'), 1, 1, 'R');
    $totalSemua += $jual['total'];
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(160, 10, 'Total Keseluruhan', 1, 0, 'R');
$pdf->Cell(30, 10, number_format($totalSemua, 0, ',', '.'), 1, 1, 'R');

$pdf->Output();
?>
