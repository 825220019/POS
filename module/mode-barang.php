<?php
if (userLogin()['level'] == 2) {
    header("location:" . $main_url . "error-page.php");
    exit();
}

function generateId()
{
    global $koneksi;
    $queryId = mysqli_query($koneksi, "SELECT max(id_barang) as maxid FROM tbl_barang");
    $data = mysqli_fetch_array($queryId);
    $maxid = $data['maxid'];
    $noUrut = (int) substr($maxid, 4, 3);
    $noUrut++;
    $maxid = "BRG-" . sprintf("%03s", $noUrut);
    return $maxid;
}

function insert($post)
{
    global $koneksi;
    $id_barang = generateId();

    $nama_barang   = mysqli_real_escape_string($koneksi, $post['name']);
    $supplier      = mysqli_real_escape_string($koneksi, $post['supplier']);
    $stock_minimal = mysqli_real_escape_string($koneksi, $post['stock_minimal']);
    $harga_beli    = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_beli']));

    // kalau input harga_jual array → ambil baris pertama
    if (is_array($post['harga_jual'])) {
        $harga_jual = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][0]));
    } else {
        $harga_jual = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual']));
    }

    // simpan ke tbl_barang
    $sqlBarang = "INSERT INTO tbl_barang (id_barang, nama_barang, id_supplier, harga_beli, harga_jual, stock_minimal, created_at) 
                  VALUES ('$id_barang', '$nama_barang', '$supplier', '$harga_beli', '$harga_jual', '$stock_minimal', NOW())";
    mysqli_query($koneksi, $sqlBarang);

    // cek varian
    if (!empty($post['varian'])) {
        for ($i = 0; $i < count($post['varian']); $i++) {
            if (!empty($post['varian'][$i])) {
                $nama_varian = mysqli_real_escape_string($koneksi, $post['varian'][$i]);
                $sqlVarian   = "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id_barang', '$nama_varian')";
                mysqli_query($koneksi, $sqlVarian);
                $id_varian = mysqli_insert_id($koneksi);

                if (!empty($post['satuan'][$i])) {
                    $satuan   = mysqli_real_escape_string($koneksi, $post['satuan'][$i]);
                    $jumlah   = mysqli_real_escape_string($koneksi, $post['jumlah'][$i]);
                    $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][$i]));
                    $stock = 0;

                    $sqlSatuan = "INSERT INTO tbl_satuan (id_varian, satuan, jumlah_isi, harga_jual, stock) 
                                  VALUES ('$id_varian', '$satuan', '$jumlah', '$harga_jual_satuan', '$stock')";
                    mysqli_query($koneksi, $sqlSatuan);
                }
            }
        }
    } else {
        // varian default
        $sqlVarian = "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id_barang', 'Default')";
        mysqli_query($koneksi, $sqlVarian);
        $id_varian = mysqli_insert_id($koneksi);

        // satuan default
        $sqlSatuan = "INSERT INTO tbl_satuan (id_varian, satuan, jumlah_isi, harga_jual, stock) 
                      VALUES ('$id_varian', 'pcs', 1, '$harga_jual', 0)";
        mysqli_query($koneksi, $sqlSatuan);
    }

    // return id_barang agar bisa dipakai
    return $id_barang;
}

function delete($id)
{
    global $koneksi;

    // Hapus satuan dulu
    $varian = mysqli_query($koneksi, "SELECT id_varian FROM tbl_varian WHERE id_barang = '$id'");
    while ($row = mysqli_fetch_assoc($varian)) {
        mysqli_query($koneksi, "DELETE FROM tbl_satuan WHERE id_varian = '" . $row['id_varian'] . "'");
    }

    // Hapus varian
    mysqli_query($koneksi, "DELETE FROM tbl_varian WHERE id_barang = '$id'");

    // Hapus barang
    mysqli_query($koneksi, "DELETE FROM tbl_barang WHERE id_barang = '$id'");

    return mysqli_affected_rows($koneksi);
}

function update($data)
{
    global $koneksi;
    $id = mysqli_real_escape_string($koneksi, $data['kode']);
    $name = mysqli_real_escape_string($koneksi, $data['name']);
    $stockmin = mysqli_real_escape_string($koneksi, $data['stock_minimal']);
    $harga_beli = mysqli_real_escape_string($koneksi, str_replace('.', '', $data['harga_beli']));
    $harga_jual = mysqli_real_escape_string($koneksi, str_replace('.', '', $data['harga_jual']));

    // update barang utama
    mysqli_query($koneksi, "UPDATE tbl_barang SET 
        nama_barang = '$name', 
        stock_minimal = '$stockmin',
        harga_beli = '$harga_beli',
        harga_jual = '$harga_jual'
        WHERE id_barang = '$id'");

    // hapus varian & satuan lama
    $varian = mysqli_query($koneksi, "SELECT id_varian FROM tbl_varian WHERE id_barang = '$id'");
    while ($row = mysqli_fetch_assoc($varian)) {
        mysqli_query($koneksi, "DELETE FROM tbl_satuan WHERE id_varian = '" . $row['id_varian'] . "'");
    }
    mysqli_query($koneksi, "DELETE FROM tbl_varian WHERE id_barang = '$id'");

    // tambah varian & satuan baru
    if (!empty($data['varian'])) {
        for ($i = 0; $i < count($data['varian']); $i++) {
            if (!empty($data['varian'][$i])) {
                $nama_varian = mysqli_real_escape_string($koneksi, $data['varian'][$i]);
                $sqlVarian = "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id', '$nama_varian')";
                mysqli_query($koneksi, $sqlVarian);
                $id_varian = mysqli_insert_id($koneksi);

                if (!empty($data['satuan'][$i])) {
                    $satuan = mysqli_real_escape_string($koneksi, $data['satuan'][$i]);
                    $jumlah = mysqli_real_escape_string($koneksi, $data['jumlah'][$i]);
                    $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $data['harga_jual'][$i]));
                    $stock = 0;

                    $sqlSatuan = "INSERT INTO tbl_satuan (id_varian, satuan, jumlah_isi, harga_jual, stock) 
                                  VALUES ('$id_varian', '$satuan', '$jumlah', '$harga_jual_satuan', '$stock')";
                    mysqli_query($koneksi, $sqlSatuan);
                }
            }
        }
    }

    return mysqli_affected_rows($koneksi);
}
?>
