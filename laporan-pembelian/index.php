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

$pembelian = getData("
    SELECT h.no_beli, h.tgl_beli, s.nama AS nama_supplier, h.total
    FROM tbl_beli_head h
    JOIN tbl_supplier s ON h.id_supplier = s.id_supplier
    ORDER BY h.tgl_beli DESC
");

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Pembelian</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Pembelian</li>
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
                    <h3 class="card-title"><i class="fas fa-list fa-sm"></i> Data Pembelian</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary float-right" data-toggle="modal"
                        data-target="#mdlPeriodeBeli"><i class="fas fa-print"></i> Cetak</button>
                </div>
                <div class="card-body table-responsive p-3">
                    <table class="table table-hover text-nowrap" id="tblData">
                        <thead>
                            <tr>
                                <th>No Pembelian</th>
                                <th>Tgl Pembelian</th>
                                <th>Supplier</th>
                                <th>Total Pembelian</th>
                                <th width="10%">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($pembelian as $beli) { ?>
                                <tr>
                                    <td><?= $beli['no_beli']; ?></td>
                                    <td><?= in_date($beli['tgl_beli']); ?></td>
                                    <td><?= $beli['nama_supplier']; ?></td>
                                    <td><?= number_format($beli['total'], 0, ',', '.'); ?></td>
                                    <td><a href="detail-pembelian.php?id=<?= $beli['no_beli'] ?>&tgl=<?= $beli['tgl_beli'] ?>"
                                            class="btn btn-sm btn-info" title="rincian barang">Detail</a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </section>
    <div class="modal fade" id="mdlPeriodeBeli">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Periode Pembelian </h4>
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
        window.open("../report/r-beli.php?tgl1=" + tgl1.value + "&tgl2=" + tgl2.value, "_blank");
    } else {
        alert('Tanggal Awal dan Tanggal Akhir harus diisi!');
    }
}
</script>
<?php
require "../template/footer.php";
?>