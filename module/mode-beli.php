<?php
function generateNo() {
    global $koneksi;
    $queryNo = mysqli_query($koneksi, "SELECT MAX(no_beli) AS maxno FROM tbl_beli_head");
    $row = mysqli_fetch_assoc($queryNo);
    $maxno = $row["maxno"];
    $noUrut = (int) substr($maxno, 2, 4);
    $noUrut++;
    return 'PB' . sprintf("%04s", $noUrut);
}

function getSatuanByBarang($id_barang) {
    $sql = "SELECT s.*, v.nama_varian 
            FROM tbl_satuan s 
            LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian 
            WHERE s.id_barang = '$id_barang'";
    return getData($sql);
}

function getBarangDetail($id_barang) {
    $barang = getData("SELECT * FROM tbl_barang WHERE id_barang = '$id_barang'");
    return $barang[0] ?? null;
}

$barangList = getData("SELECT * FROM tbl_barang ORDER BY nama_barang ASC");

function insertPembelian($data) {
    global $koneksi;

    $nobeli   = mysqli_real_escape_string($koneksi, $data['nobeli']);
    $tgl      = mysqli_real_escape_string($koneksi, $data['tglNota']);
    $supplier = mysqli_real_escape_string($koneksi, $data['supplier']);
    $idSatuan = mysqli_real_escape_string($koneksi, $data['satuan']);
    $qty      = (int) $data['qty'];
    $harga    = (int) $data['harga'];
    $subtotal = $qty * $harga;

    // ✅ Pastikan header pembelian ada
    $cekHead = mysqli_query($koneksi, "SELECT no_beli FROM tbl_beli_head WHERE no_beli='$nobeli'");
    if (mysqli_num_rows($cekHead) == 0) {
        mysqli_query($koneksi, "
            INSERT INTO tbl_beli_head (no_beli, tgl_beli, id_supplier, total, created_at)
            VALUES ('$nobeli', '$tgl', '$supplier', 0, NOW())
        ");
    }

    // ✅ Simpan detail pembelian
    mysqli_query($koneksi, "
        INSERT INTO tbl_beli_detail (no_beli, id_satuan, qty, harga, subtotal)
        VALUES ('$nobeli', '$idSatuan', '$qty', '$harga', '$subtotal')
    ");

    // ✅ Ambil informasi satuan & barang
    $qSatuan = mysqli_query($koneksi, "
        SELECT s.id_barang, s.satuan, s.jumlah_isi, b.satuan_dasar
        FROM tbl_satuan s
        JOIN tbl_barang b ON s.id_barang = b.id_barang
        WHERE s.id_satuan = '$idSatuan'
    ");
    $rowSatuan = mysqli_fetch_assoc($qSatuan);
    if (!$rowSatuan) return false;

    $idBarang = $rowSatuan['id_barang'];
    $satuanBeli = strtolower($rowSatuan['satuan']);
    $satuanDasar = strtolower($rowSatuan['satuan_dasar']);

    // ✅ Ambil semua level satuan untuk barang ini
    $qLevels = mysqli_query($koneksi, "
        SELECT satuan, jumlah_isi
        FROM tbl_satuan
        WHERE id_barang = '$idBarang'
        ORDER BY id_satuan ASC
    ");

    // Simpan semua satuan dan isi-nya
    $levels = [];
    while ($r = mysqli_fetch_assoc($qLevels)) {
        $levels[strtolower($r['satuan'])] = (int)$r['jumlah_isi'];
    }

    // ✅ Hitung faktor konversi dari satuan beli ke satuan dasar
    $jumlahTambah = $qty; // default jika beli dalam satuan dasar

    if ($satuanBeli !== $satuanDasar) {
        $totalFaktor = 1;
        $found = false;

        // Kita iterasi dari atas (level terbesar) ke bawah (dasar)
        $urutan = array_keys($levels);

        // Cari semua faktor hingga ketemu satuan beli
        for ($i = count($urutan) - 1; $i >= 0; $i--) {
            $totalFaktor *= $levels[$urutan[$i]]; // kalikan jumlah isi

            if ($urutan[$i] == $satuanBeli) {
                $found = true;
                break;
            }
        }

        if ($found) {
            $jumlahTambah = $qty * $totalFaktor;
        }
    }

    // ✅ Update stok di tbl_barang
    mysqli_query($koneksi, "
        UPDATE tbl_barang
        SET stok = stok + $jumlahTambah, harga_beli = '$harga'
        WHERE id_barang = '$idBarang'
    ");

    return true;
}

function simpanTotalPembelian($noBeli) {
    global $koneksi;
    $res = mysqli_query($koneksi, "
        SELECT SUM(subtotal) AS total FROM tbl_beli_detail WHERE no_beli='$noBeli'
    ");
    $row = mysqli_fetch_assoc($res);
    $total = $row['total'] ?? 0;

    mysqli_query($koneksi, "
        UPDATE tbl_beli_head SET total='$total' WHERE no_beli='$noBeli'
    ");
    return $total;
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

    return mysqli_affected_rows($koneksi);
}

?>