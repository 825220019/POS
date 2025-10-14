<?php
require "../config/config.php";
require "../config/functions.php";

header('Content-Type: application/json'); // taruh di atas

$id_barang = $_GET['id_barang'] ?? '';

if ($id_barang == '') {
    echo json_encode(["error" => "id_barang kosong"]);
    exit;
}

// Ambil stok dalam bentuk integer (tidak desimal)
$sql = "SELECT s.id_satuan, s.satuan, s.harga_jual, 
       FLOOR(b.stok / s.jumlah_isi) AS stock,
       v.nama_varian
FROM tbl_satuan s
LEFT JOIN tbl_barang b ON s.id_barang = b.id_barang
LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
WHERE s.id_barang = '$id_barang'";


$result = getData($sql);

if (!$result || count($result) === 0) {
    echo json_encode(["error" => "Data satuan tidak ditemukan", "id_barang" => $id_barang]);
    exit;
}

echo json_encode($result);
