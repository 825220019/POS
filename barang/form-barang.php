<?php

session_start();

if (!isset($_SESSION["ssLoginPOS"])){
    header("location: auth/login.php");
    exit();
}


require "../config/config.php";
require "../config/functions.php";
require "../module/mode-barang.php";

$title = "Add Barang - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $id = $_GET['id'];
    $sqlEdit = "SELECT * FROM tbl_barang WHERE id_barang = '$id'";
    $barang = getData($sqlEdit)[0];
} else {
    $msg = "";
}

$alert = '';

if (isset($_POST['simpan'])) {
    if($msg != ''){
        if (update($_POST)) {
            echo "
            <script>document.location.href = 'index.php?msg=updated'</script>
            ";
        }else{
             echo "<script>document.location.href = 'index.php';</script>";
        }
    } else {
    if (insert($_POST)) {
        $alert = '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Alert!</h5>
                  Barang berhasil ditambahkan.
                </div>';

    }
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
                    <h1 class="m-0">Barang</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>barang/index.php">Barang</a></li>
                        <li class="breadcrumb-item active"><?= $msg != '' ? 'Edit Barang' : 'Add Barang'?></li>
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
                    <h3 class="card-title"><i class="fas fa-pen fa-sm" ></i> <?= $msg != '' ? 'Edit Barang' : 'Input Barang'?></h3>
                    <button type="submit" name="simpan" class="btn btn-primary btn-sm float-right"><i class="fas fa-save"></i> Simpan</button>
                    <button type="reset" name="reset" class="btn btn-danger btn-sm float-right mr-1"><i
                            class="fas fa-times"></i> Reset</button>
                </div>
                <div class="card-body">
                    <?php if ($alert != ''){
                            echo $alert;
                        }?>
                    <div class="row">
                        <div class="col-lg-8 mb-3">
                            <div class="form-group">
                                <label for="kode">Kode Barang</label>
                                <input type="text" class="form-control" id="kode" name="kode"
                                value="<?= $msg != '' ? $barang['id_barang'] : generateId()?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="name">Nama</label>
                                <input type="text" class="form-control" id="name" name="name"
                                value="<?= $msg != '' ? $barang['nama_barang'] : null ?>"
                                placeholder="nama barang" autocomplete="off" autofocus required> 
                            </div>
                            <div class="form-group">
                                <label for="satuan">Satuan</label>
                                <input type="text" class="form-control" id="satuan" name="satuan"
                                value="<?= $msg != '' ? $barang['satuan'] : null ?>"
                                placeholder="satuan barang" autocomplete="off" autofocus required> 
                            </div>
                            <div class="form-group">
                                <label for="harga_beli">Harga Beli</label>
                                <input type="number" class="form-control" id="harga_beli" name="harga_beli"
                                value="<?= $msg != '' ? $barang['harga_beli'] : null ?>"
                                placeholder="Rp 0" autocomplete="off" required> 
                            </div>
                            <div class="form-group">
                                <label for="harga_jual">Harga Jual</label>
                                <input type="number" class="form-control" id="harga_jual" name="harga_jual"
                                value="<?= $msg != '' ? $barang['harga_jual'] : null ?>"
                                placeholder="Rp 0" autocomplete="off" required> 
                            </div>
                            <div class="form-group">
                                <label for="stock_minimal">Stock Minimal</label>
                                <input type="number" class="form-control" id="stock_minimal" name="stock_minimal"
                                value="<?= $msg != '' ? $barang['stock_minimal'] : null ?>"
                                placeholder="0" autocomplete="off" autofocus required> 
                            </div>
                    </div>
                    <div class="col-lg-4 mb-3">

                    </div>
                </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php
require "../template/footer.php";
?>