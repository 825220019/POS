<?php
function generateNo()
{
    global $koneksi;

    $queryNo = mysqli_query($koneksi, "SELECT MAX(no_beli) AS maxno FROM tbl_beli_head");
    $row = mysqli_fetch_assoc($queryNo);
    $maxno = $row["maxno"];

    $noUrut = (int) substr($maxno, 2, 4);
    $noUrut++;
    $maxno = 'PB' . sprintf("%04s", $noUrut);

    return $maxno;
}

function getBarangDetailBySatuan($id_satuan) {
    global $koneksi;

    $query = "SELECT b.* 
              FROM tbl_satuan s
              JOIN tbl_barang b ON s.id_barang = b.id_barang
              WHERE s.id_satuan = '$id_satuan'";

    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_assoc($result);
}

function getSatuanByBarang($id_barang)
{
    $query = "SELECT s.*, v.nama_varian 
              FROM tbl_satuan s
              LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
              WHERE s.id_barang = '$id_barang'";
    return getData($query);
}

function getBarangDetail($id_barang)
{
    $barang = getData("SELECT * FROM tbl_barang WHERE id_barang = '$id_barang'");
    if (count($barang) > 0) {
        return $barang[0];
    }
    return null;
}

$barangList = getData("SELECT * FROM tbl_barang ORDER BY nama_barang ASC");

$selectBrg = null;
$satuanList = [];
if (isset($_GET['pilihbrg'])) {
    $id_barang = $_GET['pilihbrg'];
    $selectBrg = getBarangDetail($id_barang);
    $satuanList = getSatuanByBarang($id_barang);
}

function totalBeli($noBeli)
{
    global $koneksi;
    $totalBeli = mysqli_query($koneksi, "SELECT SUM(subtotal) AS total FROM tbl_beli_detail WHERE no_beli = '$noBeli'");
    $data = mysqli_fetch_assoc($totalBeli);
    return $data["total"];
}

/**
 * Simpan header + detail sekaligus
 */
function simpanPembelian($data)
{
    global $koneksi;

    $nobeli    = mysqli_real_escape_string($koneksi, $data['nobeli']);
    $tgl       = mysqli_real_escape_string($koneksi, $data['tglNota']);
    $supplier  = mysqli_real_escape_string($koneksi, $data['supplier']);
    $total     = mysqli_real_escape_string($koneksi, $data['total']);
    $keterangan= isset($data['keterangan']) ? mysqli_real_escape_string($koneksi, $data['keterangan']) : '';

    // 1. Simpan header dulu
    $sqlHeader = "INSERT INTO tbl_beli_head (no_beli, tgl_beli, id_supplier, total, keterangan)
                  VALUES ('$nobeli', '$tgl', '$supplier', $total, '$keterangan')";
    if (!mysqli_query($koneksi, $sqlHeader)) {
        die("Error simpan header: " . mysqli_error($koneksi));
    }

    // 2. Simpan detail
    $idSatuanArr = $data['kodeBrg']; // array id_satuan
    $qtyArr      = $data['qty'];     // array qty
    $hargaArr    = $data['harga'];   // array harga
    $subtotalArr = $data['jmlHarga']; // array subtotal

    foreach ($idSatuanArr as $i => $idSatuan) {
        $idSatuan = mysqli_real_escape_string($koneksi, $idSatuan);
        $qty      = mysqli_real_escape_string($koneksi, $qtyArr[$i]);
        $harga    = mysqli_real_escape_string($koneksi, $hargaArr[$i]);
        $subtotal = mysqli_real_escape_string($koneksi, $subtotalArr[$i]);

        if ($qty <= 0) continue;

        // Cek duplikasi
        $cek = mysqli_query($koneksi, "SELECT * FROM tbl_beli_detail WHERE no_beli='$nobeli' AND id_satuan='$idSatuan'");
        if (mysqli_num_rows($cek)) continue;

        // Insert detail
        $sqlDetail = "INSERT INTO tbl_beli_detail (no_beli, id_satuan, qty, harga, subtotal)
                      VALUES ('$nobeli', '$idSatuan', $qty, $harga, $subtotal)";
        if (!mysqli_query($koneksi, $sqlDetail)) {
            die("Error simpan detail: " . mysqli_error($koneksi));
        }

        // Update harga beli terakhir di tbl_barang
        $barang = mysqli_query($koneksi, "SELECT id_barang FROM tbl_satuan WHERE id_satuan='$idSatuan'");
        $row = mysqli_fetch_assoc($barang);
        $idBarang = $row['id_barang'];

        mysqli_query($koneksi, "UPDATE tbl_barang SET harga_beli='$harga' WHERE id_barang='$idBarang'");

        // Update stok di tbl_satuan
        mysqli_query($koneksi, "UPDATE tbl_satuan SET stock=stock+$qty WHERE id_satuan='$idSatuan'");
    }

    return true;
}

/**
 * Hapus detail pembelian
 */
function delete($idSatuan, $idbeli, $qty)
{
    global $koneksi;

    // Hapus detail
    $sqlDel = "DELETE FROM tbl_beli_detail WHERE id_satuan='$idSatuan' AND no_beli='$idbeli'";
    mysqli_query($koneksi, $sqlDel);

    // Kurangi stok
    mysqli_query($koneksi, "UPDATE tbl_satuan SET stock=stock-$qty WHERE id_satuan='$idSatuan'");

    return mysqli_affected_rows($koneksi);
}
?>
