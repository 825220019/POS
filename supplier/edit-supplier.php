<?php

session_start();

if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: auth/login.php");
    exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-supplier.php";

$title = "Edit Supplier - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

$id = $_GET['id'];
$sqlEdit = "SELECT * FROM tbl_supplier WHERE id_supplier = $id";
$supplier = getData($sqlEdit)[0];

if (isset($_POST['koreksi'])) {
    if (update($_POST)) {
        //die();
        echo "<script>
        document.location.href = 'data-supplier.php?msg=updated';
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
                    <h1 class="m-0">Supplier</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>supplier/data-supplier.php">Supplier</a></li>
                        <li class="breadcrumb-item active">Edit Supplier</li>
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
                        <h3 class="card-title"><i class="fas fa-pen fa-sm"></i> Edit Supplier</h3>
                        <button type="submit" name="koreksi" class="btn btn-primary btn-sm float-right"><i
                                class="fas fa-save"></i> Edit</button>
                        <button type="reset" name="reset" class="btn btn-danger btn-sm float-right mr-1"><i
                                class="fas fa-times"></i> Reset</button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <input type="hidden" name="id" value="<?= $supplier['id_supplier']; ?>">
                            <div class="col-lg-8 mb-3">
                                <div class="form-group">
                                    <label for="name">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        placeholder="nama supplier" autofocus autocomplete="off"
                                        value="<?= $supplier['nama']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="telpon">Telepon</label>
                                    <input type="text" class="form-control" id="telpon" name="telpon"
                                        placeholder="no telepon supplier" pattern="[0-9]{5.}" title="minimal 5 angka"
                                        value="<?= $supplier['telepon']; ?> " required>
                                </div>
                                <div class="form-group">
                                    <label for="ketr">Deskripsi</label>
                                    <textarea class="form-control" id="ketr" name="ketr" rows="1"
                                        placeholder="keterangan supplier"
                                        required><?= $supplier['deskripsi'] ?>
                                    </textarea>
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