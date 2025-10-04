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

    // ambil semua varian
    $varians = array_filter($post['varian']); // hapus yang kosong
    $satuans = array_filter($post['satuan']); // hapus yang kosong

    // kalau ada varian
    if (!empty($varians)) {
        foreach ($varians as $v) {
            $nama_varian = mysqli_real_escape_string($koneksi, $v);
            mysqli_query($koneksi, "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id_barang', '$nama_varian')");
            $id_varian = mysqli_insert_id($koneksi);

            // loop satuan untuk setiap varian
            for ($i = 0; $i < count($post['satuan']); $i++) {
                if (!empty($post['satuan'][$i])) {
                    $satuan = mysqli_real_escape_string($koneksi, $post['satuan'][$i]);
                    $jumlah = mysqli_real_escape_string($koneksi, $post['jumlah'][$i]);
                    $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][$i]));
                    $stock = 0;

                    $sqlSatuan = "INSERT INTO tbl_satuan (id_barang, id_varian, satuan, jumlah_isi, harga_jual, stock) 
                                  VALUES ('$id_barang', '$id_varian', '$satuan', '$jumlah', '$harga_jual_satuan', '$stock')";
                    mysqli_query($koneksi, $sqlSatuan);
                }
            }
        }
    } else {
        // kalau tidak ada varian → simpan satuan langsung
        for ($i = 0; $i < count($post['satuan']); $i++) {
            if (!empty($post['satuan'][$i])) {
                $satuan = mysqli_real_escape_string($koneksi, $post['satuan'][$i]);
                $jumlah = mysqli_real_escape_string($koneksi, $post['jumlah'][$i]);
                $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][$i]));
                $stock = 0;

                $sqlSatuan = "INSERT INTO tbl_satuan (id_barang, satuan, jumlah_isi, harga_jual, stock) 
                              VALUES ('$id_barang', '$satuan', '$jumlah', '$harga_jual_satuan', '$stock')";
                mysqli_query($koneksi, $sqlSatuan);
            }
        }
    }

    return $id_barang;
}


function delete($id)
{
    global $koneksi;

    // hapus satuan dulu
    $varian = mysqli_query($koneksi, "SELECT id_varian FROM tbl_varian WHERE id_barang = '$id'");
    while ($row = mysqli_fetch_assoc($varian)) {
        mysqli_query($koneksi, "DELETE FROM tbl_satuan WHERE id_varian = '" . $row['id_varian'] . "'");
    }

    // hapus satuan langsung (jika ada)
    mysqli_query($koneksi, "DELETE FROM tbl_satuan WHERE id_barang = '$id'");

    // hapus varian
    mysqli_query($koneksi, "DELETE FROM tbl_varian WHERE id_barang = '$id'");

    // hapus barang
    mysqli_query($koneksi, "DELETE FROM tbl_barang WHERE id_barang = '$id'");

    return mysqli_affected_rows($koneksi);
}

function update($post)
{
    global $koneksi;

    $id_barang    = mysqli_real_escape_string($koneksi, $post['id_barang']);
    $nama_barang  = mysqli_real_escape_string($koneksi, $post['name']);
    $supplier     = mysqli_real_escape_string($koneksi, $post['supplier']);
    $stock_minimal= mysqli_real_escape_string($koneksi, $post['stock_minimal']);
    $harga_beli   = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_beli']));

    if (is_array($post['harga_jual'])) {
        $harga_jual = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][0]));
    } else {
        $harga_jual = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual']));
    }

    // update tbl_barang
    $sqlBarang = "UPDATE tbl_barang 
                  SET nama_barang='$nama_barang', 
                      id_supplier='$supplier', 
                      harga_beli='$harga_beli', 
                      harga_jual='$harga_jual', 
                      stock_minimal='$stock_minimal' 
                  WHERE id_barang='$id_barang'";
    mysqli_query($koneksi, $sqlBarang);

    // Hapus data varian & satuan lama (reset)
    mysqli_query($koneksi, "DELETE FROM tbl_satuan WHERE id_barang='$id_barang'");
    mysqli_query($koneksi, "DELETE FROM tbl_varian WHERE id_barang='$id_barang'");

    // Ambil input baru
    $varians = array_filter($post['varian']);
    $satuans = array_filter($post['satuan']);

    if (!empty($varians)) {
        foreach ($varians as $v) {
            $nama_varian = mysqli_real_escape_string($koneksi, $v);
            mysqli_query($koneksi, "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id_barang', '$nama_varian')");
            $id_varian = mysqli_insert_id($koneksi);

            // loop satuan
            for ($i = 0; $i < count($post['satuan']); $i++) {
                if (!empty($post['satuan'][$i])) {
                    $satuan = mysqli_real_escape_string($koneksi, $post['satuan'][$i]);
                    $jumlah = mysqli_real_escape_string($koneksi, $post['jumlah'][$i]);
                    $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][$i]));
                    $stock = 0; // reset stok (opsional, bisa ambil dari input kalau perlu)

                    $sqlSatuan = "INSERT INTO tbl_satuan (id_barang, id_varian, satuan, jumlah_isi, harga_jual, stock) 
                                  VALUES ('$id_barang', '$id_varian', '$satuan', '$jumlah', '$harga_jual_satuan', '$stock')";
                    mysqli_query($koneksi, $sqlSatuan);
                }
            }
        }
    } else {
        // tanpa varian → langsung ke tbl_satuan
        for ($i = 0; $i < count($post['satuan']); $i++) {
            if (!empty($post['satuan'][$i])) {
                $satuan = mysqli_real_escape_string($koneksi, $post['satuan'][$i]);
                $jumlah = mysqli_real_escape_string($koneksi, $post['jumlah'][$i]);
                $harga_jual_satuan = mysqli_real_escape_string($koneksi, str_replace('.', '', $post['harga_jual'][$i]));
                $stock = 0;

                $sqlSatuan = "INSERT INTO tbl_satuan (id_barang, satuan, jumlah_isi, harga_jual, stock) 
                              VALUES ('$id_barang', '$satuan', '$jumlah', '$harga_jual_satuan', '$stock')";
                mysqli_query($koneksi, $sqlSatuan);
            }
        }
    }

    return true;
}

?>
