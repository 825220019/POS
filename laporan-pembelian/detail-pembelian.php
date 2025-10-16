<?php

session_start();

if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: ../auth/login.php");
    exit();
}


require "../config/config.php";
require "../config/functions.php";

$title = "Laporan - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

$id = $_GET['id'];
$tgl = $_GET['tgl'];
$pembelian = getData("
    SELECT 
        d.id,
        d.no_beli,
        b.nama_barang,
        s.satuan,
        d.qty,
        d.harga,
        d.subtotal
    FROM tbl_beli_detail d
    JOIN tbl_satuan s ON d.id_satuan = s.id_satuan
    JOIN tbl_barang b ON s.id_barang = b.id_barang
    WHERE d.no_beli = '$id'
");

    ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Pembelian</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>laporan-pembelian/index.php">Pembelian</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list fa-sm"></i> Rincian Barang</h3>
                    <button type="button" class="btn btn-sm btn-warning float-right"><?= in_date($tgl)?></button>
                    <button type="button" class="btn btn-sm btn-success float-right mr-1"><?= $id?></button>
                </div>
                <div class="card-body table-responsive p-3">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th class="text-center">Qty</th>
                                <th>Satuan</th>
                                <th class="text-center">Harga Beli</th>
                                <th class="text-center">Jumlah Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($pembelian as $beli) { ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $beli['nama_barang']; ?></td>
                                    <td class="text-center"><?= $beli['qty']?></td>
                                    <td><?= $beli['satuan']; ?></td>
                                    <td class="text-center"><?= number_format($beli['harga'], 0, ',', '.'); ?></td>
                                    <td class="text-center"><?= number_format($beli['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </section>
</div>
<?php
require "../template/footer.php";
?>