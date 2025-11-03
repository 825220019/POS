<?php
require "../config/config.php";
require "../config/functions.php";

header('Content-Type: application/json');

$id_barang = $_GET['id_barang'] ?? '';
if ($id_barang == '') {
    echo json_encode(["error" => "id_barang kosong"]);
    exit;
}

// Ambil data satuan + stok barang
$sql = "SELECT 
    s.id_satuan, 
    s.satuan, 
    s.harga_jual, 
    s.jumlah_isi, 
    v.nama_varian,
    b.stok AS stok_dasar
FROM tbl_satuan s
JOIN tbl_barang b ON s.id_barang = b.id_barang
LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
WHERE s.id_barang = '$id_barang'
ORDER BY s.id_satuan DESC";

$result = getData($sql);

if (!$result || count($result) === 0) {
    echo json_encode(["error" => "Data satuan tidak ditemukan", "id_barang" => $id_barang]);
    exit;
}

$stokDasar = (int)$result[0]['stok_dasar'];

// Hitung stok per level satuan
for ($i = 0; $i < count($result); $i++) {
    $jumlahIsi = (int)$result[$i]['jumlah_isi'];
    if ($jumlahIsi <= 0) $jumlahIsi = 1;

    if ($i == 0) {
        // Satuan dasar (a)
        $stok = $stokDasar;
    } else {
        // Konversi stok dasar ke satuan saat ini
        $konversi = 1;
        for ($j = 1; $j <= $i; $j++) {
            $konversi *= (int)$result[$j]['jumlah_isi'];
        }
        $stok = floor($stokDasar / $konversi);
    }

    $result[$i]['stok'] = $stok;
}

echo json_encode($result);
