<?php
require "../config/config.php";
require "../config/functions.php";
require('../asset/fpdf/vendor/autoload.php');

/*
    LOGIKA SAMA DENGAN index.php:
    - Ambil stok dasar (b.stok)
    - Ambil semua jumlah_isi untuk setiap id_barang
    - Faktor konversi = PERKALIAN semua jumlah_isi
      (di SQL menggunakan exp(SUM(log(jumlah_isi))))
    - stok_konversi = stok_dasar / faktor
*/

// Ambil parameter filter
$stokFilter = isset($_GET['stok']) ? $_GET['stok'] : '';

// Ambil data barang + faktor konversi
$stockBrg = getData("
    SELECT
        b.id_barang,
        b.nama_barang,
        b.stok,
        b.satuan_tertinggi,
        b.stock_minimal,
        COALESCE(EXP(SUM(LOG(s.jumlah_isi))), 1) AS faktor_konversi
    FROM tbl_barang b
    LEFT JOIN tbl_satuan s ON s.id_barang = b.id_barang
    GROUP BY b.id_barang
    ORDER BY b.id_barang
");

// Hitung stok_konversi
foreach ($stockBrg as $key => $row) {
    $faktor = $row['faktor_konversi'];
    if ($faktor <= 0) $faktor = 1;
    $stokKonversi = $row['stok'] / $faktor;
    $stockBrg[$key]['stok_konversi'] = floor($stokKonversi);

    // Tentukan status stok
    if ($stokKonversi <= 0) {
        $stockBrg[$key]['status'] = 'Kosong';
    } elseif ($stokKonversi > $row['stock_minimal']) {
        $stockBrg[$key]['status'] = 'Aman';
    } else {
        $stockBrg[$key]['status'] = 'Kurang';
    }
}

// Filter berdasarkan pilihan user
if ($stokFilter === 'kosong') {
    $stockBrg = array_filter($stockBrg, fn($s) => $s['status'] === 'Kosong');
} elseif ($stokFilter === 'aman') {
    $stockBrg = array_filter($stockBrg, fn($s) => $s['status'] === 'Aman');
}

// … lalu generate PDF sama seperti sebelumnya

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
    $pdf->Cell(30, 8, $stock['stok_konversi'], 1, 0, 'C');
    $pdf->Cell(30, 8, $stock['satuan_tertinggi'], 1, 1, 'C');
}

$pdf->Output();
?>
