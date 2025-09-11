<?php

function insert($data) {
    global $koneksi;

   $username = strtolower(mysqli_real_escape_string($koneksi, $data['username']));
   $fullname = mysqli_real_escape_string($koneksi, $data['fullname']);
   $password = mysqli_real_escape_string($koneksi, $data['password']);
   $password2 = mysqli_real_escape_string($koneksi, $data['password2']);

   if ($password !== $password2){
    echo "script>
    alert('Konfirmasi password tidak sesuai!');
    </script>";
    return false;
}

$pass = password_hash($password, PASSWORD_DEFAULT);

$cekUsername = mysqli_query($koneksi, "SELECT username FROM tbl_user WHERE username = '$username'");
if (mysqli_num_rows($cekUsername) > 0) {
    echo "<script>
    alert('Username sudah terdaftar!');
    </script>";
    return false;
}

$sqlUser = "INSERT INTO tbl_user VALUES (null, '$username', '$fullname', '$pass')";
mysqli_query($koneksi, $sqlUser);

return mysqli_affected_rows($koneksi);
}

?>