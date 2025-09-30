<?php

session_start();
if (!isset($_SESSION["ssLoginPOS"])) {
  header("location: ../auth/login.php");
  exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-barang.php";
$title = "Add Barang - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

$kode = @$_GET["pilihsupplier"] ? $_GET["pilihsupplier"] : '';
if ($kode) {
  $selectSupplier = getData("SELECT * FROM tbl_supplier 
  WHERE id_supplier = '$kode'")[0];
}

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
  if ($msg != '') {
    if (update($_POST)) {
      echo " 
      <script>document.location.href = 'index.php?msg=updated'</script> ";
    } else {
      echo "<script>document.location.href = 'index.php';</script>";
    }
  } else {
    if (insert($_POST)) {
      $alert = '
<div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
  <strong>Berhasil!</strong> Barang berhasil ditambahkan.
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>';

    }
  }
}
?>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const jumlahInputs = document.querySelectorAll("input[name='jumlah[]']");
    const beliInput = document.querySelector("input[name='harga_beli']"); // harga beli utama
    const jualInputs = document.querySelectorAll("input[name='harga_jual[]']");

    const formatter = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    });

    function updatePlaceholders() {
      const hargaBeliUtama = parseFloat(beliInput.value) || 0;
      let hargaPrev = hargaBeliUtama;

      for (let i = 0; i < jumlahInputs.length; i++) {
        const qty = parseFloat(jumlahInputs[i].value) || 0;

        if (i === 0) {
          // baris pertama langsung = harga beli utama
          jualInputs[i].placeholder = hargaPrev > 0 ? formatter.format(Math.round(hargaPrev)) : "Rp 0";
        } else {
          if (hargaPrev > 0 && qty > 0) {
            const harga = Math.round(hargaPrev / qty);
            jualInputs[i].placeholder = formatter.format(harga);
            hargaPrev = harga; // update harga acuan
          } else {
            jualInputs[i].placeholder = "Rp 0";
            hargaPrev = 0;
          }
        }
      }
    }

    // Jalankan saat halaman pertama kali load
    updatePlaceholders();

    // Event listener
    if (beliInput) beliInput.addEventListener('input', updatePlaceholders);
    jumlahInputs.forEach(inp => inp.addEventListener('input', updatePlaceholders));
  });
</script>

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
            <li class="breadcrumb-item"><a href="<?= $main_url ?>dashboard.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= $main_url ?>barang/index.php">Barang</a></li>
            <li class="breadcrumb-item active"><?= $msg != '' ? 'Edit Barang' : 'Add Barang' ?></li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <section class="content">
    <div class="container-fluid">
      <?php if ($alert != '') { echo $alert; } ?>
      <div class="card">
        <form action="" method="post" enctype="multipart/form-data">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-pen fa-sm"></i>
              <?= $msg != '' ? 'Edit Barang' : 'Input Barang' ?>
            </h3>
            <button type="submit" name="simpan" class="btn btn-primary btn-sm float-right">
              <i class="fas fa-save"></i> Simpan
            </button>
            <button type="reset" class="btn btn-danger btn-sm float-right mr-1">
              <i class="fas fa-times"></i> Reset
            </button>
          </div>

          <div class="row ml-2 mr-2">
            <div class="col-lg-6">
              <div class="card card-outline card-warning p-3">
                <div class="form-group row mb-2">
                  <label for="kode" class="col-sm-3 col-form-label">Kode Barang</label>
                  <div class="col-sm-9">
                    <input type="text" name="kode" class="form-control" id="kode"
                      value="<?= $msg != '' ? $barang['id_barang'] : generateId() ?>" readonly>
                  </div>
                </div>

                <div class="form-group row mb-2">
                  <label for="nama" class="col-sm-3 col-form-label">Nama</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="name" name="name"
                      value="<?= $msg != '' ? $barang['nama_barang'] : null ?>" placeholder="nama barang"
                      autocomplete="off" autofocus required>
                  </div>
                </div>

                <div class="form-group row mb-2"> <label for="stock_minimal" class="col-sm-3 col-form-label">Stock
                    Minimal</label>
                  <div class="col-sm-9">
                    <input type="number" class="form-control" id="stock_minimal" name="stock_minimal" placeholder="0"
                      required>
                  </div>
                </div>

                <div class="form-group row mb-2"> <label for="harga_beli" class="col-sm-3 col-form-label">Harga
                    Beli</label>
                  <div class="col-sm-9">
                    <input type="number" class="form-control" id="harga_beli" name="harga_beli" placeholder="Rp 0"
                      required>
                  </div>
                </div>

                <div class="form-group row mb-2"> <label for="supplier" class="col-sm-3 col-form-label">Supplier</label>
                  <div class="col-sm-9"> <select name="supplier" id="supplier" class="form-control" required>
                      <option value="">-- Pilih Supplier --</option>
                      <?php $suppliers = getData("SELECT * FROM tbl_supplier");
                      foreach ($suppliers as $supplier) { ?>
                        <option value="<?= $supplier['id_supplier'] ?>"
                          <?= @$_GET['pilihsupplier'] == $supplier['id_supplier'] ? 'selected' : null ?>>
                          <?= $supplier['id_supplier'] . " | " . $supplier['nama'] ?>
                        </option> <?php } ?>
                    </select> </div>
                </div>
              </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-6">
              <div class="card card-outline card-warning p-3" style="max-height: 270px; overflow-y: auto;">
                <div class="row"> <label for="varian">Varian</label>
                  <input type="text" class="form-control mb-2" name="varian[]" placeholder="varian barang"
                    autocomplete="off">
                  <input type="text" class="form-control mb-2" name="varian[]" placeholder="varian barang"
                    autocomplete="off">
                  <input type="text" class="form-control mb-2" name="varian[]" placeholder="varian barang"
                    autocomplete="off">
                  <input type="text" class="form-control mb-2" name="varian[]" placeholder="varian barang"
                    autocomplete="off">
                  <input type="text" class="form-control mb-2" name="varian[]" placeholder="varian barang"
                    autocomplete="off">
                </div>
              </div>
            </div>
          </div>
      </div>

      <!-- Jumlah -->
      <div class="card px-3 col-lg-12">
        <div class="row">
          <div class="col-lg-1"> <label for="jumlah" class="mt-3">Jumlah</label> </div>
          <div class="col-lg-3"> <input type="text" class="form-control mt-2" name="jumlah[]" placeholder="isi satuan 1"
              required> </div>
          <div class="col-lg-3 mb-2"> <input type="text" class="form-control mt-2" name="jumlah[]"
              placeholder="isi satuan 2"> </div>
          <div class="col-lg-3"> <input type="text" class="form-control mt-2" name="jumlah[]" placeholder="isi satuan 3"
              > </div>
        </div>
      </div>
      <div class="card px-3 pt-2 col-lg-12">
        <div class="row"> <!-- Kolom Satuan -->
          <div class="col-lg-6">
            <div class="form-group"> <label for="satuan">Satuan</label> <input type="text" class="form-control"
                name="satuan[]" placeholder="satuan brg 1 (Dus)" required> <input type="text" class="form-control mt-2"
                name="satuan[]" placeholder="satuan brg 2 (Pak)"> <input type="text" class="form-control mt-2"
                name="satuan[]" placeholder="satuan brg 3 (Bks)"> </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group"> <label for="harga_jual">Harga Jual</label> <input type="number"
                class="form-control" name="harga_jual[]" placeholder="Rp 0" required> <input type="number"
                class="form-control mt-2" name="harga_jual[]" placeholder="Rp 0"> <input type="number"
                class="form-control mt-2" name="harga_jual[]" placeholder="Rp 0">
            </div>
          </div>
        </div>
      </div>
      </form>
    </div>
  </section>
</div>
<?php require "../template/footer.php"; ?>