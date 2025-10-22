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
  // ambil varian dari database
  $sqlVarian = "SELECT * FROM tbl_varian WHERE id_barang = '$id'";
  $varians = getData($sqlVarian);

  if (!empty($varians)) {
    $sqlSatuan = "SELECT * FROM tbl_satuan WHERE id_varian IN (SELECT id_varian FROM tbl_varian WHERE id_barang = '$id')";
  } else {
    $sqlSatuan = "SELECT * FROM tbl_satuan WHERE id_barang = '$id'";
  }

  $satuanData = getData($sqlSatuan);


  // ubah ke array biasa agar gampang dipanggil
  $varianValues = [];
  if (!empty($varians)) {
    foreach ($varians as $v) {
      $varianValues[] = $v['nama_varian']; // pakai nama_varian
    }
  }

  // kumpulkan hanya jumlah_isi
  $jumlahValues = [];
  if (!empty($satuanData)) {
    foreach ($satuanData as $s) {
      $jumlahValues[] = $s['jumlah_isi'];
    }
  }

  $satuanValues = [];
  if (!empty($satuanData)) {
    foreach ($satuanData as $s) {
      $satuanValues[] = [
        'jumlah_isi' => $s['jumlah_isi'],
        'satuan' => $s['satuan'],
        'harga_jual' => $s['harga_jual']
      ];
    }
  }

} else {
  $msg = "";
}

$alert = '';


if (isset($_POST['simpan'])) {
  if ($msg != '') {
    if (update($_POST)) {
      echo "<script>document.location.href = 'index.php?msg=updated'</script>";
    } else {
      echo "<script>document.location.href = 'index.php';</script>";
    }
  } else {
    $idBarang = insert($_POST);
    if ($idBarang) {
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
    const jualInputs = document.querySelectorAll("input[name='harga_jual[]']"); // kolom bawah (array)
    const hargaJualUtama = document.querySelector("input[name='harga_jual']");   // kolom kiri atas (single)

    const formatter = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    });

    function updatePlaceholders() {
      const hargaBeli = parseFloat(beliInput.value.replace(/\D/g, "")) || 0;
      const hargaJualAtas = parseFloat(hargaJualUtama.value.replace(/\D/g, "")) || 0;

      // 🔹 Baris pertama = harga jual utama
      if (jualInputs[0]) {
        jualInputs[0].placeholder = hargaJualAtas > 0
          ? formatter.format(Math.round(hargaJualAtas))
          : "Rp 0";
      }

      // 🔹 Baris kedua = harga beli ÷ jumlah isi baris pertama
      let hargaBaris2 = 0;
      if (jualInputs[1]) {
        const qtyPertama = parseFloat(jumlahInputs[0].value) || 0;
        if (hargaBeli > 0 && qtyPertama > 0) {
          hargaBaris2 = Math.round(hargaBeli / qtyPertama);
          jualInputs[1].placeholder = formatter.format(hargaBaris2);
        } else {
          jualInputs[1].placeholder = "Rp 0";
          hargaBaris2 = 0;
        }
      }

      // 🔹 Baris ketiga = harga baris kedua ÷ jumlah isi baris kedua
      if (jualInputs[2]) {
        const qtyKedua = parseFloat(jumlahInputs[1].value) || 0;
        if (hargaBaris2 > 0 && qtyKedua > 0) {
          const hargaBaris3 = Math.round(hargaBaris2 / qtyKedua);
          jualInputs[2].placeholder = formatter.format(hargaBaris3);
        } else {
          jualInputs[2].placeholder = "Rp 0";
        }
      }

      // 🔹 Baris ke-4 dst tetap placeholder 0
      for (let i = 3; i < jualInputs.length; i++) {
        jualInputs[i].placeholder = "Rp 0";
      }
    }

    // Format input menjadi rupiah otomatis saat mengetik
    document.querySelectorAll('.harga_jual').forEach(input => {
      input.addEventListener('input', function (e) {
        let value = this.value.replace(/\D/g, ''); // hapus semua non-digit
        if (value) {
          value = new Intl.NumberFormat('id-ID').format(value);
        }
        this.value = value;
      });
    });

    // Jalankan saat load
    updatePlaceholders();

    // Event listener
    if (beliInput) beliInput.addEventListener('input', updatePlaceholders);
    if (hargaJualUtama) hargaJualUtama.addEventListener('input', updatePlaceholders);
    jumlahInputs.forEach(inp => inp.addEventListener('input', updatePlaceholders));
  });

  document.addEventListener("DOMContentLoaded", function () {
    const hargaBeliInput = document.querySelector("input[name='harga_beli']");

    function validasiHargaJual(input) {
      const hargaBeli = parseFloat(hargaBeliInput.value.replace(/\D/g, "")) || 0;
      const hargaJual = parseFloat(input.value.replace(/\D/g, "")) || 0;
      if (hargaJual > 0 && hargaJual < hargaBeli) {
        alert("⚠️ Harga jual tidak boleh lebih kecil dari harga beli!");
        input.value = ""; // kosongkan kolom biar user isi ulang
        input.focus();
        return false;
      }
      return true;
    }

    // cek setiap kali user keluar dari kolom harga jual
    document.querySelectorAll(".harga_jual").forEach(input => {
      input.addEventListener("blur", () => validasiHargaJual(input));
    });

    // cegah submit form kalau ada yang salah
    document.querySelector("form").addEventListener("submit", function (e) {
      const hargaJualInputs = document.querySelectorAll(".harga_jual");
      for (const input of hargaJualInputs) {
        if (!validasiHargaJual(input)) {
          e.preventDefault();
          return false;
        }
      }
    });
  });


  // 🔸 Cek input harga beli & harga jual agar tidak negatif
  document.querySelector("form").addEventListener("submit", function (e) {
    const hargaBeliInput = document.querySelector("input[name='harga_beli']");
    const hargaJualInputs = document.querySelectorAll("input[name='harga_jual'], input[name='harga_jual[]']");

    // Hapus semua non-digit dan tanda minus
    const hargaBeli = parseFloat(hargaBeliInput.value.replace(/[^\d-]/g, '')) || 0;
    if (hargaBeli < 0) {
      alert("Harga beli tidak valid! Tidak boleh bernilai negatif.");
      e.preventDefault();
      return false;
    }

    for (const input of hargaJualInputs) {
      const hargaJual = parseFloat(input.value.replace(/[^\d-]/g, '')) || 0;
      if (hargaJual < 0) {
        alert("Harga jual tidak valid! Tidak boleh bernilai negatif.");
        e.preventDefault();
        return false;
      }
    }
  });

  // 🔸 Cek harga jual tidak boleh lebih kecil dari harga beli
  document.querySelector("form").addEventListener("submit", function (e) {
    const hargaBeliInput = document.querySelector("input[name='harga_beli']");
    const hargaJualUtamaInput = document.querySelector("input[name='harga_jual']");
    const hargaJualArray = document.querySelectorAll("input[name='harga_jual[]']");

    // ubah format ke angka
    const hargaBeli = parseFloat(hargaBeliInput.value.replace(/\D/g, "")) || 0;

    // cek harga jual utama
    if (hargaJualUtamaInput) {
      const hargaJualUtama = parseFloat(hargaJualUtamaInput.value.replace(/\D/g, "")) || 0;
      if (hargaJualUtama < hargaBeli) {
        alert("Harga jual utama tidak boleh lebih kecil dari harga beli!");
        hargaJualUtamaInput.focus();
        e.preventDefault();
        return false;
      }
    }

    // cek semua harga jual varian
    for (const input of hargaJualArray) {
      const hargaJual = parseFloat(input.value.replace(/\D/g, "")) || 0;
      if (hargaJual > 0 && hargaJual < hargaBeli) {
        alert("Harga jual tidak boleh lebih kecil dari harga beli!");
        input.focus();
        e.preventDefault();
        return false;
      }
    }
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
      <?php if ($alert != '') {
        echo $alert;
      } ?>
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

                    <?php if ($msg != ''): ?>
                      <input type="hidden" name="id_barang" value="<?= $barang['id_barang'] ?>">
                    <?php endif; ?>

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
                    <input type="number" class="form-control" id="stock_minimal" name="stock_minimal"
                      value="<?= $msg != '' ? $barang['stock_minimal'] : null ?>" placeholder="0" required>
                  </div>
                </div>
                <div class="form-group row mb-2"> <label for="harga_beli" class="col-sm-3 col-form-label">Harga
                    Beli</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="harga_beli" name="harga_beli"
                      value="<?= $msg != '' ? number_format($barang['harga_beli'], 0, ',', '.') : null ?>"
                      placeholder="Rp 0" required>
                  </div>
                </div>
                <div class="form-group row mb-2">
                  <label for="harga_jual" class="col-sm-3 col-form-label">Harga Jual</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="harga_jual" name="harga_jual"
                      value="<?= $msg != '' ? number_format($barang['harga_jual'], 0, ',', '.') : null ?>"
                      placeholder="Rp 0">
                  </div>
                </div>
                <div class="form-group row mb-2">
                  <label for="supplier" class="col-sm-3 col-form-label">Supplier</label>
                  <div class="col-sm-9">
                    <select name="supplier" id="supplier" class="form-control" required>
                      <option value="">-- Pilih Supplier --</option>
                      <?php
                      $suppliers = getData("SELECT * FROM tbl_supplier");
                      foreach ($suppliers as $supplier) { ?>
                        <option value="<?= $supplier['id_supplier'] ?>" <?= ($msg != '' && $barang['id_supplier'] == $supplier['id_supplier']) ? 'selected' : '' ?>>
                          <?= $supplier['nama'] . " | " . $supplier['deskripsi'] ?>
                        </option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

              </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-6">
              <div class="card card-outline card-warning p-3" style="max-height: 310px; overflow-y: auto;">
                <div class="row">
                  <label for="varian">Varian</label>
                  <?php
                  // Loop selalu 6 input
                  for ($i = 0; $i < 6; $i++) {
                    $value = isset($varianValues[$i]) ? $varianValues[$i] : '';
                    ?>
                    <input type="text" class="form-control mb-2" name="varian[]" value="<?= $value ?>"
                      placeholder="varian barang" autocomplete="off">
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
      </div>

      <!-- Jumlah -->
      <div class="card px-3 col-lg-12">
        <div class="row">
          <div class="col-lg-1">
            <label for="jumlah" class="mt-3">Jumlah</label>
          </div>
          <?php
          for ($i = 0; $i < 3; $i++) {
            $value = isset($jumlahValues[$i]) ? $jumlahValues[$i] : '';
            ?>
            <div class="col-lg-3 mb-2">
              <input type="text" class="form-control mt-2" name="jumlah[]" value="<?= $value ?>"
                placeholder="isi satuan <?= $i + 1 ?>" <?= $i == 0 ? 'required' : '' ?>>
            </div>
          <?php } ?>
        </div>
      </div>

      <div class="card px-3 pt-2 col-lg-12">
        <div class="row">
          <!-- Kolom Satuan -->
          <div class="col-lg-6">
            <div class="form-group">
              <label for="satuan">Satuan</label>
              <input type="text" class="form-control" name="satuan[]" placeholder="satuan brg 1 (Dus)"
                value="<?= isset($satuanValues[0]['satuan']) ? $satuanValues[0]['satuan'] : '' ?>" required>

              <input type="text" class="form-control mt-2" name="satuan[]" placeholder="satuan brg 2 (Pak)"
                value="<?= isset($satuanValues[1]['satuan']) ? $satuanValues[1]['satuan'] : '' ?>">

              <input type="text" class="form-control mt-2" name="satuan[]" placeholder="satuan brg 3 (Bks)"
                value="<?= isset($satuanValues[2]['satuan']) ? $satuanValues[2]['satuan'] : '' ?>">
            </div>
          </div>

          <!-- Kolom Harga Jual -->
          <div class="col-lg-6">
            <div class="form-group">
              <label for="harga_jual">Harga Jual</label>
              <input type="text" class="form-control harga_jual" name="harga_jual[]" placeholder="Rp 0"
                value="<?= isset($satuanValues[0]['harga_jual']) ? number_format($satuanValues[0]['harga_jual'], 0, ',', '.') : '' ?>"
                required>

              <input type="text" class="form-control mt-2 harga_jual" name="harga_jual[]" placeholder="Rp 0"
                value="<?= isset($satuanValues[1]['harga_jual']) ? number_format($satuanValues[1]['harga_jual'], 0, ',', '.') : '' ?>">

              <input type="text" class="form-control mt-2 harga_jual" name="harga_jual[]" placeholder="Rp 0"
                value="<?= isset($satuanValues[2]['harga_jual']) ? number_format($satuanValues[2]['harga_jual'], 0, ',', '.') : '' ?>">
            </div>
          </div>
        </div>
      </div>
      </form>
    </div>
  </section>
</div>
<?php require "../template/footer.php"; ?>