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
    function insert($data)
    {
        global $koneksi;

        $nobeli     = mysqli_real_escape_string($koneksi, $data['nobeli']);
        $idSatuan   = mysqli_real_escape_string($koneksi, $data['satuan']);
        $qty        = (int)$data['qty'];
        $harga      = (int)$data['harga'];
        $jmlHarga   = $qty * $harga;
        $idSupplier = mysqli_real_escape_string($koneksi, $data['supplier']);

        if ($idSatuan == '' || $qty <= 0) return false;

        // Pastikan header ada
        $cekHead = mysqli_query($koneksi, "SELECT no_beli FROM tbl_beli_head WHERE no_beli='$nobeli'");
        if (mysqli_num_rows($cekHead) == 0) {
            mysqli_query($koneksi, "INSERT INTO tbl_beli_head (no_beli, tgl_beli, id_supplier, total, created_at) 
                                    VALUES ('$nobeli', CURDATE(), '$idSupplier', 0, NOW())");
        }

        // Cek detail
        $cek = mysqli_query($koneksi, "SELECT * FROM tbl_beli_detail 
                                    WHERE no_beli='$nobeli' AND id_satuan='$idSatuan'");
        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($koneksi, "UPDATE tbl_beli_detail 
                                    SET qty=qty+$qty, subtotal=subtotal+$jmlHarga 
                                    WHERE no_beli='$nobeli' AND id_satuan='$idSatuan'");
        } else {
            $sql = "INSERT INTO tbl_beli_detail (no_beli, id_satuan, qty, harga, subtotal) 
                    VALUES ('$nobeli', '$idSatuan', $qty, $harga, $jmlHarga)";
            if (!mysqli_query($koneksi, $sql)) {
                die("Error insert detail: " . mysqli_error($koneksi));
            }
        }

        // Update harga_beli di tbl_barang
        $barangQ = mysqli_query($koneksi, "SELECT id_barang FROM tbl_satuan WHERE id_satuan='$idSatuan'");
        $row = mysqli_fetch_assoc($barangQ);
        if ($row) {
            $idBarang = $row['id_barang'];
            mysqli_query($koneksi, "UPDATE tbl_barang SET harga_beli=$harga WHERE id_barang='$idBarang'");
        }

        // Update stok di tbl_satuan
        mysqli_query($koneksi, "UPDATE tbl_satuan SET stock=stock+$qty WHERE id_satuan='$idSatuan'");

        return true;
    }

function simpanPembelian($data)
{
    global $koneksi;

    $nobeli    = mysqli_real_escape_string($koneksi, $data['nobeli']);
    $tgl       = mysqli_real_escape_string($koneksi, $data['tglNota']);
    $supplier  = mysqli_real_escape_string($koneksi, $data['supplier']);
    $total     = mysqli_real_escape_string($koneksi, $data['total']);

    // Update total di header
    $sqlUpdate = "UPDATE tbl_beli_head SET total='$total', tgl_beli='$tgl', id_supplier='$supplier' 
                  WHERE no_beli='$nobeli'";
    if (!mysqli_query($koneksi, $sqlUpdate)) {
        die("Error update header: " . mysqli_error($koneksi));
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
