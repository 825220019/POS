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

$penjualan = getData("
    SELECT 
        h.no_jual,
        h.tgl_jual,
        p.nama AS pelanggan,
        h.total
    FROM tbl_jual_head h
    LEFT JOIN tbl_pelanggan p ON h.id_pelanggan = p.id_pelanggan
    ORDER BY h.tgl_jual DESC
");
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Penjualan</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Penjualan</li>
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
                    <h3 class="card-title"><i class="fas fa-list fa-sm"></i> Data Penjualan</h3>
                    <a href="#" class="btn btn-sm btn-outline-primary float-right" data-toggle="modal"
                        data-target="#mdlPeriodeJual">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                </div>
                <div class="card-body table-responsive p-3">
                    <table class="table table-hover text-nowrap" id="tblData">
                        <thead>
                            <tr>
                                <th>No Penjualan</th>
                                <th>Tgl Penjualan</th>
                                <th>Pelanggan</th>
                                <th>Total Penjualan</th>
                                <th width="10%">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($penjualan as $jual) { ?>
                                <tr>
                                    <td><?= $jual['no_jual']; ?></td>
                                    <td><?= in_date($jual['tgl_jual']); ?></td>
                                    <td><?= $jual['pelanggan']; ?></td>
                                    <td><?= number_format($jual['total'], 0, ',', '.'); ?></td>
                                    <td><a href="detail-penjualan.php?id=<?= $jual['no_jual'] ?>&tgl=<?= $jual['tgl_jual'] ?>"
                                            class="btn btn-sm btn-info" title="rincian barang">Detail</a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </section>
    <div class="modal fade" id="mdlPeriodeJual">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Periode Penjualan </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="tgl1" class="col-sm-3 col-form-label">Tanggal Awal</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="tgl1" name="tgl1" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="tgl2" class="col-sm-3 col-form-label">Tanggal Akhir</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="tgl2" name="tgl2" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="printDoc()"><i class="fas fa-print"></i>
                        Cetak</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let tgl1 = document.getElementById('tgl1');
    let tgl2 = document.getElementById('tgl2');

    function printDoc() {
        if (tgl1.value !== "" && tgl2.value !== "") {
            window.open("../report/r-jual.php?tgl1=" + tgl1.value + "&tgl2=" + tgl2.value, "_blank");
        } else {
            alert('Tanggal Awal dan Tanggal Akhir harus diisi!');
        }
    }
</script>
<?php
require "../template/footer.php";
?>