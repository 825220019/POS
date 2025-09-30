<?php
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'db_pos';

$koneksi = mysqli_connect($host, $user, $pass, $dbname);

// if (mysqli_connect_errno()) {
//     echo "gagal koneksi ke database";
//     exit();
// } else {
//     echo "koneksi ke database berhasil";
// }

$main_url = "http://localhost/pos/";

?>