<?php
if (userLogin()['level'] == 'kasir') {
    header("location:" . $main_url . "error-page.php");
    exit();
}

function insert($data){
    global $koneksi;

    $nama   = mysqli_real_escape_string($koneksi,$data['nama']);
    $telpon = mysqli_real_escape_string ($koneksi,$data['telpon']);
    $deskripsi = mysqli_real_escape_string ($koneksi,$data['ketr']);

    $sqlSupplier = "INSERT INTO tbl_supplier VALUES (null, '$nama', '$telpon', '$deskripsi')";

    mysqli_query($koneksi, $sqlSupplier);

    return mysqli_affected_rows($koneksi);
}

function delete($id)
{
    global $koneksi;

    $sqlDel = "DELETE FROM tbl_supplier WHERE id_supplier = $id";
    mysqli_query($koneksi, $sqlDel);

    return mysqli_affected_rows($koneksi);
}

function update($data)
{
    global $koneksi;

    $id = mysqli_real_escape_string($koneksi, $data['id']);
    $nama = mysqli_real_escape_string($koneksi, $data['nama']);
    $telpon = mysqli_real_escape_string($koneksi, $data['telpon']);
    $ketr = mysqli_real_escape_string($koneksi, $data['ketr']);
    
    //cek username sekarang
    $sqlSupplier = "UPDATE tbl_supplier SET 
        nama = '$nama',
        telepon = '$telpon',
        deskripsi = '$ketr'
        WHERE id_supplier = $id";
        
    mysqli_query($koneksi, $sqlSupplier);

    return mysqli_affected_rows($koneksi);
}

?>