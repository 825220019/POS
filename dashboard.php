<?php

session_start();

if (!isset($_SESSION["ssLoginPOS"])) {
  header("location: auth/login.php");
  exit();
}


require "config/config.php";
require "config/functions.php";

$title = "Dashboard - CAngelline POS";
require "template/header.php";
require "template/navbar.php";
require "template/sidebar.php";

$suppliers = getData("SELECT * FROM tbl_supplier");
$supplierNum = count($suppliers);

$pelanggans = getData("SELECT * FROM tbl_pelanggan");
$pelangganNum = count($pelanggans);

$barang = getData("SELECT * FROM tbl_barang");
$brgNum = count($barang);

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Dashboard</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?=
              $main_url ?>dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $supplierNum ?></h3>
              <p>Supplier</p>
            </div>
            <div class="icon">
              <i class="ion ion-android-bus"></i>
            </div>
            <a href="<?= $main_url ?>supplier" class="small-box-footer">More info
              <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $pelangganNum ?></h3>
              <p>Pelanggan</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-stalker"></i>
            </div>
            <a href="<?= $main_url ?>pelanggan" class="small-box-footer">More info
              <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?= $brgNum ?></h3>
              <p>Barang</p>
            </div>
            <div class="icon">
              <i class="ion ion-android-cart"></i>
            </div>
            <a href="<?= $main_url ?>barang" class="small-box-footer">More info
              <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6">
          <div class="card card-outline card-warning">
            <div class="card-header text-info">
              <h5 class="card-title">Info Stock Barang</h5>
              <h5><a href="stock" class="float-right" title="laporan stok"><i class="fas fa-arrow-right"></i></a></h5>
            </div>
            <table class="table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Barang</th>
                  <th>Jumlah Stock</th>
                  <th>Stock Minimal</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                $stockMin = getData("SELECT * FROM tbl_barang WHERE stok < stock_minimal");
                foreach ($stockMin as $min) { ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $min['nama_barang']; ?></td>
                    <td class="text-center"><?= $min['stok']; ?></td>
                    <td class="text-center"><?= $min['stock_minimal']; ?></td>
                    <td class="text-danger">Stok Kurang</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card card-outline card-success">
            <div class="card-header text-info">
              <h5>Omzet Penjualan</h5>
            </div>
            <div class="card-body text-primary">
              <h2><span class="h4">Rp </span><?= omzet()?></h2>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
require "template/footer.php";
?>