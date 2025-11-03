<?php
function generateNo()
{
    global $koneksi;
    $queryNo = mysqli_query($koneksi, "SELECT MAX(no_jual) AS maxno FROM tbl_jual_head");
    $row = mysqli_fetch_assoc($queryNo);
    $maxno = $row["maxno"] ?? 'PJ0000';

    $noUrut = (int) substr($maxno, 2);
    $noUrut++;
    $newNo = 'PJ' . sprintf("%04s", $noUrut);
    return $newNo;
}

function getSatuanByBarang($id_barang)
{
    $query = "SELECT s.id_satuan, s.satuan, s.harga_jual, s.jumlah_isi, v.nama_varian, b.stok
FROM tbl_satuan s
JOIN tbl_barang b ON s.id_barang = b.id_barang
LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
WHERE s.id_barang = '$id_barang'";
    return getData($query);
}

function getBarangDetail($id_barang)
{
    $barang = getData("SELECT * FROM tbl_barang WHERE id_barang = '$id_barang'");
    return $barang[0] ?? null;
}

$barangList = getData("SELECT * FROM tbl_barang ORDER BY nama_barang ASC");

$selectBrg = null;
$satuanList = [];
if (isset($_GET['pilihbrg'])) {
    $id_barang = $_GET['pilihbrg'];
    $selectBrg = getBarangDetail($id_barang);
    $satuanList = getSatuanByBarang($id_barang);
}

function insert($data)
{
    global $koneksi;

    $nojual = mysqli_real_escape_string($koneksi, $data['nojual']);
    $idSatuan = mysqli_real_escape_string($koneksi, $data['satuan']);
    $qty = (int) $data['qty'];
    $harga = (int) $data['harga'];
    $jmlHarga = $qty * $harga;
    $idPelanggan = mysqli_real_escape_string($koneksi, $data['pelanggan']);

    if ($idSatuan == '' || $qty <= 0) {
        echo "<script>alert('Data tidak lengkap atau qty tidak valid.');</script>";
        return false;
    }

    // Cek stok barang di tabel satuan
    $stokQuery = mysqli_query($koneksi, "SELECT stock FROM tbl_satuan WHERE id_satuan='$idSatuan'");
    $stokData = mysqli_fetch_assoc($stokQuery);
    $stok = (int) $stokData['stock'];

    if ($qty > $stok) {
        echo "<script>alert('Stok barang tidak cukup. Stok tersedia: $stok');</script>";
        return false;
    }

    // Pastikan header penjualan ada
    $cekHead = mysqli_query($koneksi, "SELECT no_jual FROM tbl_jual_head WHERE no_jual='$nojual'");
    if (mysqli_num_rows($cekHead) == 0) {
        mysqli_query($koneksi, "INSERT INTO tbl_jual_head (no_jual, tgl_jual, id_pelanggan, total, created_at) 
                                VALUES ('$nojual', CURDATE(), '$idPelanggan', 0, NOW())");
    }

    // Cek apakah barang sudah pernah diinput untuk penjualan yang sama
    $cekBrg = mysqli_query($koneksi, "SELECT * FROM tbl_jual_detail 
                                      WHERE no_jual='$nojual' AND id_satuan='$idSatuan'");
    if (mysqli_num_rows($cekBrg) > 0) {
        echo "<script>alert('Barang sudah ada. Hapus dulu jika ingin ubah qty.');</script>";
        return false;
    }

    // Jika belum ada, lakukan insert baru
    $sql = "INSERT INTO tbl_jual_detail (no_jual, id_satuan, qty, harga, subtotal) 
            VALUES ('$nojual', '$idSatuan', $qty, $harga, $jmlHarga)";
    if (!mysqli_query($koneksi, $sql)) {
        die('Error insert detail: ' . mysqli_error($koneksi));
    }

    $qSatuan = mysqli_query($koneksi, "SELECT b.id_barang, s.jumlah_isi, k.faktor
                                   FROM tbl_satuan s
                                   JOIN tbl_barang b ON s.id_barang = b.id_barang
                                   LEFT JOIN tbl_konversi_satuan k 
                                   ON s.id_barang=k.id_barang AND s.satuan=k.satuan_turunan
                                   WHERE s.id_satuan='$idSatuan'");
    $dataSatuan = mysqli_fetch_assoc($qSatuan);

    $idBarang = $dataSatuan['id_barang'];
    $faktor = $dataSatuan['faktor'] ?? $dataSatuan['jumlah_isi']; // pakai faktor jika ada
    $qtyDasar = $qty * $faktor;

    mysqli_query($koneksi, "UPDATE tbl_barang SET stok = stok - $qtyDasar WHERE id_barang='$idBarang'");



    return true;
}

function delete($idSatuan, $noJual, $qty)
{
    global $koneksi;

    // Ambil info barang dan jumlah_isi / faktor konversi
    $q = mysqli_query($koneksi, "
        SELECT b.id_barang, b.stok, s.jumlah_isi, k.faktor
        FROM tbl_satuan s
        JOIN tbl_barang b ON s.id_barang = b.id_barang
        LEFT JOIN tbl_konversi_satuan k 
            ON s.id_barang = k.id_barang AND s.satuan = k.satuan_turunan
        WHERE s.id_satuan = '$idSatuan'
    ");
    $data = mysqli_fetch_assoc($q);

    if (!$data) {
        return 0; // barang tidak ditemukan
    }

    $idBarang = $data['id_barang'];
    $faktor = $data['faktor'] ?? $data['jumlah_isi']; // pakai faktor konversi jika ada
    $qtyDasar = $qty * $faktor;

    // Hapus detail penjualan
    $sqlDel = "DELETE FROM tbl_jual_detail WHERE id_satuan='$idSatuan' AND no_jual='$noJual'";
    mysqli_query($koneksi, $sqlDel);

    // Kembalikan stok di tbl_barang
    mysqli_query($koneksi, "UPDATE tbl_barang SET stok = stok + $qtyDasar WHERE id_barang = '$idBarang'");

    return mysqli_affected_rows($koneksi);
}


