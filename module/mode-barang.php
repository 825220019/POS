<?php
if (userLogin()['level'] == 'kasir') {
    header("location:" . $main_url . "error-page.php");
    exit();
}

function generateId()
{
    global $koneksi;
    $queryId = mysqli_query($koneksi, "SELECT MAX(id_barang) as maxid FROM tbl_barang");
    $data = mysqli_fetch_array($queryId);
    $maxid = $data['maxid'];
    $noUrut = (int) substr($maxid, 4, 3);
    $noUrut++;
    return "BRG-" . sprintf("%03s", $noUrut);
}

function insert($post)
{
    global $koneksi;
    $id_barang = generateId();

    $nama_barang = mysqli_real_escape_string($koneksi, $post['name']);
    $supplier = mysqli_real_escape_string($koneksi, $post['supplier']);
    $stock_minimal = mysqli_real_escape_string($koneksi, $post['stock_minimal']);
    $harga_beli = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_beli']));

    if ($harga_beli < 0) {
        echo "<script>alert('Harga beli tidak valid! Tidak boleh bernilai negatif.');history.back();</script>";
        exit();
    }


    // ambil harga jual utama dari baris pertama
    $harga_jual = is_array($post['harga_jual'])
        ? mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][0]))
        : mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual']));
    if ($harga_jual < 0) {
        echo "<script>alert('Harga jual tidak valid! Tidak boleh bernilai negatif.');history.back();</script>";
        exit();
    }

    // ambil satuan terakhir (dasar)
    // Ambil satuan dari form
    $satuans = array_filter($post['satuan']);
    $satuan_tertinggi = mysqli_real_escape_string($koneksi, $satuans[0]); // baris pertama
    $satuan_dasar = mysqli_real_escape_string($koneksi, end($satuans)); // baris terakhir

    // tbl_barang
    $sqlBarang = "INSERT INTO tbl_barang 
    (id_barang, nama_barang, satuan_dasar, satuan_tertinggi, stok, id_supplier, harga_beli, harga_jual, stock_minimal, created_at) 
    VALUES 
    ('$id_barang', '$nama_barang', '$satuan_dasar', '$satuan_tertinggi', 0, '$supplier', '$harga_beli', '$harga_jual', '$stock_minimal', NOW())";
    mysqli_query($koneksi, $sqlBarang);


    // simpan varian & satuan
    $varians = array_filter($post['varian']);
    $jumlahs = $post['jumlah'];
    $harga_juals = $post['harga_jual'];

    if (!empty($varians)) {
        foreach ($varians as $v) {
            $nama_varian = mysqli_real_escape_string($koneksi, $v);
            mysqli_query($koneksi, "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id_barang', '$nama_varian')");
            $id_varian = mysqli_insert_id($koneksi);

            for ($i = 0; $i < count($satuans); $i++) {
                if (!empty($satuans[$i])) {
                    $satuan = mysqli_real_escape_string($koneksi, $satuans[$i]);
                    $jumlah = mysqli_real_escape_string($koneksi, $jumlahs[$i]);
                    $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $harga_juals[$i]));
                    mysqli_query($koneksi, "INSERT INTO tbl_satuan (id_barang, id_varian, satuan, jumlah_isi, harga_jual) 
                        VALUES ('$id_barang', '$id_varian', '$satuan', '$jumlah', '$harga_jual_satuan')");
                }
            }
        }
    } else {
        for ($i = 0; $i < count($satuans); $i++) {
            if (!empty($satuans[$i])) {
                $satuan = mysqli_real_escape_string($koneksi, $satuans[$i]);
                $jumlah = mysqli_real_escape_string($koneksi, $jumlahs[$i]);
                $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $harga_juals[$i]));
                mysqli_query($koneksi, "INSERT INTO tbl_satuan (id_barang, satuan, jumlah_isi, harga_jual) 
                    VALUES ('$id_barang', '$satuan', '$jumlah', '$harga_jual_satuan')");
            }
        }
    }

    // sinkron ke konversi
    syncKonversiSatuan($id_barang);

    return $id_barang;
}

function update($post)
{
    global $koneksi;

    $id_barang = mysqli_real_escape_string($koneksi, $post['id_barang']);
    $nama_barang = mysqli_real_escape_string($koneksi, $post['name']);
    $supplier = mysqli_real_escape_string($koneksi, $post['supplier']);
    $stock_minimal = mysqli_real_escape_string($koneksi, $post['stock_minimal']);
    $harga_beli = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_beli']));
    if ($harga_beli < 0) {
        echo "<script>alert('Harga beli tidak valid! Tidak boleh bernilai negatif.');history.back();</script>";
        exit();
    }
    $harga_jual = is_array($post['harga_jual'])
        ? mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][0]))
        : mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual']));
    if ($harga_jual < 0) {
        echo "<script>alert('Harga jual tidak valid! Tidak boleh bernilai negatif.');history.back();</script>";
        exit();
    }
    $satuans = array_filter($post['satuan']);
    $satuan_tertinggi = mysqli_real_escape_string($koneksi, $satuans[0]);
    $satuan_dasar = mysqli_real_escape_string($koneksi, end($satuans));

    mysqli_query($koneksi, "UPDATE tbl_barang 
    SET nama_barang='$nama_barang', 
        id_supplier='$supplier', 
        harga_beli='$harga_beli', 
        harga_jual='$harga_jual', 
        satuan_dasar='$satuan_dasar',
        satuan_tertinggi='$satuan_tertinggi',
        stock_minimal='$stock_minimal'
    WHERE id_barang='$id_barang'");


    // reset varian & satuan lama
    mysqli_query($koneksi, "DELETE FROM tbl_satuan WHERE id_barang='$id_barang'");
    mysqli_query($koneksi, "DELETE FROM tbl_varian WHERE id_barang='$id_barang'");

    // ulangi insert
    $varians = array_filter($post['varian']);
    $jumlahs = $post['jumlah'];
    $harga_juals = $post['harga_jual'];

    if (!empty($varians)) {
        foreach ($varians as $v) {
            $nama_varian = mysqli_real_escape_string($koneksi, $v);
            mysqli_query($koneksi, "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id_barang', '$nama_varian')");
            $id_varian = mysqli_insert_id($koneksi);

            for ($i = 0; $i < count($satuans); $i++) {
                if (!empty($satuans[$i])) {
                    $satuan = mysqli_real_escape_string($koneksi, $satuans[$i]);
                    $jumlah = mysqli_real_escape_string($koneksi, $jumlahs[$i]);
                    $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $harga_juals[$i]));
                    mysqli_query($koneksi, "INSERT INTO tbl_satuan (id_barang, id_varian, satuan, jumlah_isi, harga_jual) 
                        VALUES ('$id_barang', '$id_varian', '$satuan', '$jumlah', '$harga_jual_satuan')");
                }
            }
        }
    } else {
        for ($i = 0; $i < count($satuans); $i++) {
            if (!empty($satuans[$i])) {
                $satuan = mysqli_real_escape_string($koneksi, $satuans[$i]);
                $jumlah = mysqli_real_escape_string($koneksi, $jumlahs[$i]);
                $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $harga_juals[$i]));
                mysqli_query($koneksi, "INSERT INTO tbl_satuan (id_barang, satuan, jumlah_isi, harga_jual) 
                    VALUES ('$id_barang', '$satuan', '$jumlah', '$harga_jual_satuan')");
            }
        }
    }

    // sinkron ulang ke konversi
    syncKonversiSatuan($id_barang);

    return true;
}

function delete($id)
{
    global $koneksi;

    mysqli_query($koneksi, "DELETE FROM tbl_satuan WHERE id_barang = '$id'");
    mysqli_query($koneksi, "DELETE FROM tbl_varian WHERE id_barang = '$id'");
    mysqli_query($koneksi, "DELETE FROM tbl_konversi_satuan WHERE id_barang = '$id'");
    mysqli_query($koneksi, "DELETE FROM tbl_barang WHERE id_barang = '$id'");
    return mysqli_affected_rows($koneksi);
}

/**
 * Sinkronisasi satuan dasar ↔ turunan
 */
function syncKonversiSatuan($id_barang)
{
    global $koneksi;

    mysqli_query($koneksi, "DELETE FROM tbl_konversi_satuan WHERE id_barang='$id_barang'");

    $barang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT satuan_dasar FROM tbl_barang WHERE id_barang='$id_barang'"));
    $satuan_dasar = $barang['satuan_dasar'];

    $satuan_turunan = mysqli_query($koneksi, "SELECT satuan FROM tbl_satuan WHERE id_barang='$id_barang'");
    while ($row = mysqli_fetch_assoc($satuan_turunan)) {
        $turunan = $row['satuan'];
        if ($turunan != $satuan_dasar) {
            mysqli_query($koneksi, "INSERT INTO tbl_konversi_satuan (id_barang, satuan_dasar, satuan_turunan, created_at, updated_at)
                VALUES ('$id_barang', '$satuan_dasar', '$turunan', NOW(), NOW())");
        }
    }
}
?>