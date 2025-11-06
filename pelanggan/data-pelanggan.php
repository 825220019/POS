<?php

session_start();
if (!isset($_SESSION["ssLoginPOS"])) {
  header("location: ../auth/login.php");
  exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-pelanggan.php";

$title = "Pelanggan - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

if (isset($_GET['msg'])) {
  $msg = $_GET['msg'];
} else {
  $msg = '';
}

$alert = '';
if ($msg == 'deleted') {
  $alert = '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check"></i> Alert!</h5>
                  Pelanggan berhasil dihapus.
                </div>';
}
if ($msg == 'aborted') {
  $alert = '<div class="alert alert-danger alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-exclamation-triangle"></i> Alert!</h5>
                  Pelanggan gagal dihapus.
                </div>';
}
if ($msg == 'updated') {
  $alert = '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <h5><i class="icon fas fa-check-circle"></i> Alert!</h5>
                  Pelanggan berhasil diperbarui.
                </div>';
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Pelanggan</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?=
              $main_url ?>dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Pelanggan</li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <?php if ($alert != '') {
          echo $alert;
        } ?>
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list fa-sm"></i> Data Pelanggan</h3>
          <div class="card-tools">
            <a href="<?= $main_url ?>pelanggan/add-pelanggan.php" class="btn btn-primary btn-sm"><i
                class="fas fa-plus fa-sm"></i> Add Pelanggan
            </a>
          </div>
        </div>
        <div class="card-body table-responsive p-3">
          <table class="table table-hover text-nowrap" id="tblData">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Deskripsi</th>
                <th width="10%">Operasi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $pelanggans = getData("SELECT * FROM tbl_pelanggan");
              foreach ($pelanggans as $pelanggan): ?>
                <tr>
                  <td><?= $no++; ?></td>
                  <td><?= $pelanggan['nama']; ?></td>
                  <td><?= $pelanggan['telepon']; ?></td>
                  <td><?= $pelanggan['deskripsi']; ?></td>
                  <td>
                    <a href="edit-pelanggan.php?id=<?= $pelanggan['id_pelanggan']; ?>" class="btn btn-warning btn-sm"><i
                        class="fas fa-edit"></i></a>
                    <a href="del-pelanggan.php?id=<?= $pelanggan['id_pelanggan']; ?>"
                      onclick="return confirm('Yakin hapus data?')" class="btn btn-danger btn-sm"><i
                        class="fas fa-trash"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
  </section>
</div>
<?php
require "../template/footer.php";
?>