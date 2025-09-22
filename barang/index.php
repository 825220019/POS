<?php

session_start();

if (!isset($_SESSION["ssLoginPOS"])){
    header("location: auth/login.php");
    exit();
}


require "../config/config.php";
require "../config/functions.php";
require "../module/mode-barang.php";

$title = "Barang - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
} else {
    $msg = '';
}

$alert = '';
//jalankan fungsi hapus barang
if ($msg == 'deleted'){
  $id = $_GET['id'];
  delete($id);
  $alert = "<script>
    $(document).ready(function(){
      $(document).Toasts('create', {
        class: 'bg-success', 
        title: 'Success',
        icon: 'fas fa-check-circle',
        body: 'Barang berhasil dihapus.'
      })
    });
  </script>";
}

if ($msg == 'updated'){
  $alert = "<script>
    $(document).ready(function(){
      $(document).Toasts('create', {
        class: 'bg-success', 
        title: 'Success',
        icon: 'fas fa-check-circle',
        body: 'Barang berhasil diperbarui.',
        autohide: true,
        delay: 5000,
      })
    });
  </script>";
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
              <li class="breadcrumb-item active">Barang</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <?php if ($alert != ''){
                echo $alert;
            }?>
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list fa-sm"></i> Barang</h3>
                <div class="card-tools">
                    <a href="<?= $main_url ?>barang/form-barang.php" class="btn btn-primary btn-sm"><i
                            class="fas fa-plus fa-sm"></i> Add Barang</a>
                </div>
            </div>
            <div class="card-body table-responsive p-3">
                <table class="table table-hover text-nowrap" id="tblData">
                    <thead>
                        <tr>
                            <th>ID Barang</th>
                            <th>Nama Barang</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th width="10%">Operasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        $barang = getData("SELECT * FROM tbl_barang");
                        foreach ($barang as $brg)  : ?>
                            <tr>
                            <td><?= $brg['id_barang']; ?></td>
                            <td><?= $brg['nama_barang']; ?></td>
                            <td class="text-center"><?= number_format ($brg['harga_beli'],0,',','.')?></td>
                            <td class="text-center"><?= number_format ($brg['harga_jual'],0,',','.')?></td>
                            <td>
                                <a href="form-barang.php?id=<?= $brg['id_barang']?>&msg=editing"
                                    class="btn btn-warning btn-sm" title="edit barang"><i class="fas fa-pen"></i></a>
                                <a href="?id=<?= $brg['id_barang']?>&msg=deleted" title="hapus barang"
                                    onclick="return confirm('Yakin hapus data?')"
                                    class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

</section>

</div>
<?php
require "../template/footer.php";
?>