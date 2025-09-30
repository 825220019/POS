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
    $nama_barang = mysqli_real_escape_string($koneksi, $post['name']);
    $supplier = mysqli_real_escape_string($koneksi, $post['supplier']);
    $stock_minimal = mysqli_real_escape_string($koneksi, $post['stock_minimal']);

    $sqlBarang = "INSERT INTO tbl_barang (id_barang, nama_barang, id_supplier, 
    stock_minimal, created_at) VALUES ('$id_barang', '$nama_barang', '$supplier', 
    '$stock_minimal', NOW())";
    mysqli_query($koneksi, $sqlBarang);

    if (!empty($post['varian'])) {
        for (
            $i = 0;
            $i < count($post['varian']);
            $i++
        ) {
            if (!empty($post['varian'][$i])) {
                $nama_varian = mysqli_real_escape_string(
                    $koneksi,
                    $post['varian'][$i]
                );

                $sqlVarian = "INSERT INTO tbl_varian (id_barang, nama_varian) 
    VALUES ('$id_barang', '$nama_varian')";
                mysqli_query($koneksi, $sqlVarian);
                $id_varian = mysqli_insert_id($koneksi);

                if (!empty($post['satuan'][$i])) {
                    $satuan = mysqli_real_escape_string($koneksi, $post['satuan'][$i]);
                    $jumlah = mysqli_real_escape_string($koneksi, $post['jumlah'][$i]);
                    $harga_beli = mysqli_real_escape_string($koneksi, $post['harga_beli'][$i]);
                    $harga_jual = mysqli_real_escape_string($koneksi, $post['harga_jual'][$i]);
                    $stock = 0;

                    $sqlSatuan = "INSERT INTO tbl_satuan (id_varian, satuan, 
    jumlah_isi, harga_beli, harga_jual, stock) VALUES 
    ('$id_varian', '$satuan', '$jumlah', '$harga_beli', '$harga_jual', 
    '$stock')";
                    mysqli_query($koneksi, $sqlSatuan);
                }
            }
        }
    }
    return true;
}

function delete($id)
{
    global $koneksi;

    mysqli_query($koneksi, "DELETE FROM tbl_barang WHERE id_barang = '$id'");

    return mysqli_affected_rows($koneksi);
}

function update($data)
{
    global $koneksi;
    $id = mysqli_real_escape_string($koneksi, $data['kode']);
    $name = mysqli_real_escape_string($koneksi, $data['name']);
    $stockmin = mysqli_real_escape_string($koneksi, $data['stock_minimal']);

    mysqli_query($koneksi, "UPDATE tbl_barang SET 
    nama_barang = '$name', 
    stock_minimal = '$stockmin' 
    WHERE id_barang = '$id'");

    $varian = mysqli_query($koneksi, "SELECT id_varian 
    FROM tbl_varian WHERE id_barang = '$id'");
    while ($row = mysqli_fetch_assoc($varian)) {
        mysqli_query($koneksi, "DELETE FROM tbl_satuan 
        WHERE id_varian = '" . $row['id_varian'] . "'");
    }
    mysqli_query($koneksi, "DELETE FROM tbl_varian WHERE id_barang = '$id'");

    if (!empty($data['varian'])) {
        for (
            $i = 0;
            $i < count($data['varian']);
            $i++
        ) {
            if (!empty($data['varian'][$i])) {
                $nama_varian = mysqli_real_escape_string($koneksi, $data['varian'][$i]);
                $sqlVarian = "INSERT INTO tbl_varian (id_barang, nama_varian) VALUES ('$id', '$nama_varian')";
                mysqli_query($koneksi, $sqlVarian);
                $id_varian = mysqli_insert_id($koneksi);

                if (!empty($data['satuan'][$i])) {
                    $satuan = mysqli_real_escape_string($koneksi, $data['satuan'][$i]);
                    $jumlah = mysqli_real_escape_string($koneksi, $data['jumlah'][$i]);
                    $harga_beli = mysqli_real_escape_string($koneksi, $data['harga_beli'][$i]);
                    $harga_jual = mysqli_real_escape_string($koneksi, $data['harga_jual'][$i]);
                    $stock = 0;
                    $sqlSatuan = "INSERT INTO tbl_satuan (id_varian, satuan, jumlah_isi, harga_beli, harga_jual, stock) 
        VALUES ('$id_varian', '$satuan', '$jumlah', '$harga_beli', '$harga_jual', '$stock')";
                    mysqli_query($koneksi, $sqlSatuan);
                }
            }
        }
    }
    return mysqli_affected_rows($koneksi);
} ?>