<?php

session_start();

if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: auth/login.php");
    exit();
}


require "../config/config.php";
require "../config/functions.php";
require "../module/mode-beli.php";

$title = "Transaksi Pembelian - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

$noBeli = generateNo()

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Pembelian Barang</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Add Pembelian</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section>
        <div class="container-fluid">
            <form action="" method="post">
                <div class="row">
                    <div class="col-lg-6">
                    <div class="card card-outline card-warning p-3">
                        <div class="form-group row mb-2">
                            <label for="noNota" class="col-sm-2 col-form-label">No Nota</label>
                            <div class="col-sm-4">
                                <input type="text" name="nobeli" class="form-control" id="noNote" value="<?= $noBeli ?>">
                            </div>
                            <label for="tglNota" class="col-sm-2 col-form-label">Tgl Nota</label>
                            <div class="col-sm-4">
                                <input type="date" name="tglNota" class="form-control" id="tglNota" value="<?= date ('Y-m-d ') ?>" required>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <label for="kodeBrg" class="col-sm-2 col-form-label">SKU</label>
                            <div class="col-sm-10">
                               <select name="kodeBrg" id="kodeBrg" class="form-control">
                                <option value="">-- Pilih kode Barang --</option>
                                <?php
                                $barang = getData("SELECT * FROM tbl_barang");
                                foreach($barang as $brg){ ?>
                                <option value="<?=$brg['id_barang']?>"><?=$brg['id_barang'] . " | " . $brg['nama_barang']?></option>
                            <?php
                            }?>
                               </select>
                        </div>
                    </div>
                    </div>
                    <div class="col-lg-6">
                        
                    </div>
                </div>
            </form>
        </div>
</section>
</div>
<?php
require "../template/footer.php";
?>