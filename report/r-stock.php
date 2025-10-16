<?php
require "../config/config.php";
require "../config/functions.php";
require('../asset/fpdf/vendor/autoload.php');

$stockBrg = getData("
    SELECT 
        b.id_barang,
        b.nama_barang,
        b.stok / COALESCE(s.jumlah_isi, 1) AS stok_konversi,
        COALESCE(s.satuan, b.satuan_tertinggi) AS satuan_tertinggi
    FROM tbl_barang b
    LEFT JOIN (
        SELECT s1.id_barang, s1.satuan, s1.jumlah_isi
        FROM tbl_satuan s1
        INNER JOIN (
            SELECT id_barang, MAX(jumlah_isi) AS max_jumlah
            FROM tbl_satuan
            GROUP BY id_barang
        ) s2 ON s1.id_barang = s2.id_barang AND s1.jumlah_isi = s2.max_jumlah
        GROUP BY s1.id_barang  -- pastikan hanya 1 row per id_barang
    ) s ON s.id_barang = b.id_barang
    ORDER BY b.id_barang
");
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Laporan Stok Barang', 0, 1, 'C');

$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 10, 'No', 1, 0, 'C');
$pdf->Cell(40, 10, 'Kode Barang', 1, 0);
$pdf->Cell(80, 10, 'Nama Barang', 1, 0);
$pdf->Cell(30, 10, 'Stok', 1, 0, 'C');
$pdf->Cell(30, 10, 'Satuan', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$no = 1;
foreach ($stockBrg as $stock) {
    $pdf->Cell(10, 8, $no++, 1, 0, 'C');
    $pdf->Cell(40, 8, $stock['id_barang'], 1, 0);
    $pdf->Cell(80, 8, $stock['nama_barang'], 1, 0);
    $pdf->Cell(30, 8, round($stock['stok_konversi'], 2), 1, 0, 'C');
    $pdf->Cell(30, 8, $stock['satuan_tertinggi'], 1, 1, 'C');
}

$pdf->Output();
?>
