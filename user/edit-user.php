<?php

session_start();

if (isset($_SESSION["ssLoginPOS"])){}
else{
    header("location: ../auth/login.php");
    exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-user.php";

$title = "Edit User - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

$id = $_GET['id'];
$sqlEdit = "SELECT * FROM tbl_user WHERE userid = $id";
$user = getData($sqlEdit)[0];

if (isset($_POST['koreksi'])) {
    if (update($_POST)) {
        echo "<script>
        alert('User berhasil diedit!');
        document.location.href = 'data-user.php';
        </script>";
    }
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">User</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>user/data-user.php">User</a></li>
                        <li class="breadcrumb-item active">Edit User</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <form action="" method="post" enctype="multipart/form-data">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-pen fa-sm" ></i> Edit User</h3>
                    <button type="submit" name="koreksi" class="btn btn-primary btn-sm float-right"><i class="fas fa-save"></i> Edit</button>
                    <button type="reset" name="reset" class="btn btn-danger btn-sm float-right mr-1"><i
                            class="fas fa-times"></i> Reset</button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <input type="hidden" name="id" value="<?= $user['userid']; ?>">
                        <div class="col-lg-8 mb-3">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="masukkan username" autofocus autocomplete="off" 
                                    value="<?= $user['username']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="fullname">Fullname</label>
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    placeholder="masukkan nama lengkap" value="<?= $user['fullname']; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3"></div>
                </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php
require "../template/footer.php";
?>