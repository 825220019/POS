<?php

if (userLogin()['level'] != 'admin') {
    header("location: ".$main_url."error-page.php");
    exit();
}

function insert($data)
{
    global $koneksi;

    $username = strtolower(mysqli_real_escape_string($koneksi, $data['username']));
    $fullname = mysqli_real_escape_string($koneksi, $data['fullname']);
    $password = mysqli_real_escape_string($koneksi, $data['password']);
    $password2 = mysqli_real_escape_string($koneksi, $data['password2']);
    $level = mysqli_real_escape_string($koneksi, $data['level']);   

    if ($password !== $password2) {
        echo "<script>
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

    $sqlUser = "INSERT INTO tbl_user VALUES (null, '$username', '$fullname', '$pass', '$level')";
    mysqli_query($koneksi, $sqlUser);

    return mysqli_affected_rows($koneksi);
}


function delete($id)
{
    global $koneksi;

    $sqlDel = "DELETE FROM tbl_user WHERE user_id = $id";
    mysqli_query($koneksi, $sqlDel);

    return mysqli_affected_rows($koneksi);
}

function update($data)
{
    global $koneksi;

    $iduser = mysqli_real_escape_string($koneksi, $data['id']);
    $username = strtolower(mysqli_real_escape_string($koneksi, $data['username']));
    $fullname = mysqli_real_escape_string($koneksi, $data['fullname']);
    $level = mysqli_real_escape_string($koneksi, $data['level']);
    
    //cek username sekarang
    $queryUsername = mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE user_id = $iduser");
    $dataUsername = mysqli_fetch_assoc($queryUsername);
    $curUsername = $dataUsername['username'];

    //cek username baru
    $newUsername = mysqli_query($koneksi, "SELECT username FROM tbl_user WHERE username = '$username'");

    if($username == $curUsername) {
        if (mysqli_num_rows($newUsername) > 1) {
            echo "<script>
            alert('Username sudah terdaftar!');
            </script>";
            return false;
        }
    }

    mysqli_query($koneksi, "UPDATE tbl_user SET 
        username = '$username',
        fullname = '$fullname',
        level = '$level'
        WHERE userid = $iduser");

    return mysqli_affected_rows($koneksi);
}

function selectUser($level, $value){
    return ($level == $value) ? "selected" : "";
}


?>