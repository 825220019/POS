<?php
require "../config/config.php";
require "../config/functions.php";

header('Content-Type: application/json');

$id_barang = $_GET['id_barang'] ?? '';
if ($id_barang == '') {
    echo json_encode(["error" => "id_barang kosong"]);
    exit;
}

$sql = "
SELECT 
    s.id_satuan, 
    s.satuan, 
    s.harga_jual, 
    s.jumlah_isi, 
    b.harga_beli, 
    v.nama_varian
FROM tbl_satuan s
LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
JOIN tbl_barang b ON s.id_barang = b.id_barang
WHERE s.id_barang = '$id_barang'
ORDER BY s.id_satuan ASC
";

$result = getData($sql);

if (!$result || count($result) === 0) {
    echo json_encode(["error" => "Data satuan tidak ditemukan"]);
    exit;
}

echo json_encode($result);
