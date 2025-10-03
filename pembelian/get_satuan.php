<?php
require "../config/config.php";
require "../config/functions.php";  // tambahkan ini

$id_barang = $_GET['id_barang'];
$data = getSatuanByBarang($id_barang);

echo json_encode($data);

header('Content-Type: application/json');

$sql = "SELECT s.satuan, s.harga_jual, s.stock 
        FROM tbl_satuan s
        LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
        WHERE s.id_barang = '$id_barang' OR v.id_barang = '$id_barang'";


$result = getData($sql);
if (!$result || count($result) === 0) {
    echo json_encode(["error" => "Data satuan tidak ditemukan", "id_barang" => $id_barang, "sql" => $sql]);
    exit;
}


if (!$result || count($result) === 0) {
    echo json_encode(["error" => "Data satuan tidak ditemukan", "id_barang" => $id_barang]);
    exit;
}

echo json_encode($result);
