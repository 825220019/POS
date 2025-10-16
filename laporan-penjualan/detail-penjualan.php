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
$penjualan = getData("
    SELECT 
        d.id,
        d.no_jual,
        b.nama_barang,
        IFNULL(v.nama_varian, '-') AS nama_varian,
        s.satuan,
        d.qty,
        d.harga_jual,
        d.jml_harga
    FROM tbl_jual_detail d
    JOIN tbl_satuan s ON d.id_satuan = s.id_satuan
    JOIN tbl_barang b ON d.id_barang = b.id_barang
    LEFT JOIN tbl_varian v ON d.id_varian = v.id_varian
    WHERE d.no_jual = '$id'
");
    ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Penjualan</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>laporan-penjualan/index.php">Penjualan</a></li>
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
                                <th>Varian</th>
                                <th class="text-center">Qty</th>
                                <th>Satuan</th>
                                <th class="text-center">Harga Jual</th>
                                <th class="text-center">Jumlah Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($penjualan as $jual) { ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $jual['nama_barang']; ?></td>
                                    <td><?= $jual['nama_varian']; ?></td>
                                    <td class="text-center"><?= $jual['qty']?></td>
                                    <td><?= $jual['satuan']; ?></td>
                                    <td class="text-center"><?= number_format($jual['harga_jual'], 0, ',', '.'); ?></td>
                                    <td class="text-center"><?= number_format($jual['jml_harga'], 0, ',', '.'); ?></td>
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