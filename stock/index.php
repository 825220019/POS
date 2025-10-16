<?php
session_start();

if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: ../auth/login.php");
    exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-barang.php";

$title = "Laporan - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

$stockBrg = getData("
    SELECT 
        b.id_barang,
        b.nama_barang,
        b.satuan_tertinggi,
        b.stok,
        b.stock_minimal,
        COALESCE(k.jumlah_isi, 1) AS jumlah_isi
    FROM tbl_barang b
    LEFT JOIN tbl_satuan k 
        ON k.id_barang = b.id_barang 
        AND k.satuan = b.satuan_tertinggi
    GROUP BY b.id_barang, b.nama_barang, b.satuan_tertinggi, b.stok, b.stock_minimal, k.jumlah_isi
");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Stok</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Stock</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list fa-sm"></i> Stock</h3>
                    <a href="<?= $main_url ?>report/r-stock.php" class="btn btn-sm btn-outline-primary float-right" target="_blank">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                </div>
                <div class="card-body table-responsive p-3">
                    <table class="table table-hover text-nowrap" id="tblData">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah Stok</th>
                                <th>Stock Minimal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($stockBrg as $stock) { ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $stock['id_barang']; ?></td>
                                    <td><?= $stock['nama_barang']; ?></td>
                                    <td><?= $stock['satuan_tertinggi'] ?: $stock['satuan_tertinggi']; ?></td>
                                    <td class="text-center"><?= round($stock['stok'] / $stock['jumlah_isi'], 2); ?></td>
                                    <td class="text-center"><?= $stock['stock_minimal']; ?></td>
                                    <td>
                                        <?php if ($stock['stok'] / $stock['jumlah_isi'] < $stock['stock_minimal']) {
                                            echo '<span class="text-danger">Stok Kurang</span>';
                                        } else {
                                            echo '<span class="text-success">Stok Aman</span>';
                                        } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require "../template/footer.php"; ?>
