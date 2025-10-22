<?php

session_start();

if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: ../auth/login.php");
    exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-beli.php";

$title = "Transaksi Pembelian - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";


if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
} else {
    $msg = '';
}

if (isset($_GET['hapus'])) {
    $key = $_GET['hapus'];
    unset($_SESSION['cart'][$key]);
    // reindex array biar rapih
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    $tgl = $_POST['tglNota'];
    $supplier = $_POST['supplier'];
    echo "<script>document.location='?tgl=$tgl&supplier=$supplier'</script>";

}


if (isset($_GET['id_barang'])) {
    $id_barang = $_GET['id_barang'];
    $kode = isset($_GET['pilihbrg']) ? $_GET['pilihbrg'] : '';
    $satuanList = [];
    if ($kode) {
        $satuanList = getData("
    SELECT s.satuan, s.harga_jual
    FROM tbl_satuan s
    JOIN tbl_varian v ON s.id_varian = v.id_varian
    WHERE v.id_barang = '$kode'
");
    }
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['subtotal'];
    }
}

if (isset($_POST['addbrg'])) {
    $idSatuan = $_POST['satuan'];
    $qty = (int) $_POST['qty'];
    $harga = (int) $_POST['harga'];
    $subtotal = $qty * $harga;

    // simpan ke session cart
    $_SESSION['cart'][] = [
        'id_satuan' => $idSatuan,
        'qty' => $qty,
        'harga' => $harga,
        'subtotal' => $subtotal
    ];

    // biar reload tetap ke tanggal yang sama
    $tgl = $_POST['tglNota'];
    $supplier = $_POST['supplier'];
    echo "<script>document.location='?tgl=$tgl&supplier=$supplier'</script>";

}

if (isset($_POST['simpan'])) {
    $nobeli = $_POST['nobeli'];
    $tgl = $_POST['tglNota'];
    $supplier = $_POST['supplier'];

    // Simpan head dulu
    mysqli_query($koneksi, "
        INSERT INTO tbl_beli_head (no_beli, tgl_beli, id_supplier, total, created_at)
        VALUES ('$nobeli', '$tgl', '$supplier', 0, NOW())
    ");

    foreach ($_SESSION['cart'] as $item) {
        $itemData = [
            'nobeli' => $nobeli,
            'tglNota' => $tgl,
            'supplier' => $supplier,
            'satuan' => $item['id_satuan'],
            'qty' => $item['qty'],
            'harga' => $item['harga']
        ];
        insertPembelian($itemData);
    }

    // Hitung total dan update head
    $total = simpanTotalPembelian($nobeli);

    unset($_SESSION['cart']);

    echo "<script>
        alert('Data pembelian berhasil disimpan!');
        document.location='index.php';
    </script>";
}


$noBeli = generateNo()

    ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Transaksi</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=
                            $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Add Pembelian</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section>
        <div class="container-fluid">
            <form action="" method="post">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-pen fa-sm"></i>
                        Pembelian Barang
                    </h3>
                    <button type="submit" name="simpan" class="btn btn-primary btn-sm float-right">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="reset" class="btn btn-danger btn-sm float-right mr-1">
                        <i class="fas fa-times"></i> Reset
                    </button>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card card-outline card-warning p-3">
                            <div class="form-group row mb-2">
                                <label for="noNota" class="col-sm-2 col-form-label">No Nota</label>
                                <div class="col-sm-4">
                                    <input type="text" name="nobeli" class="form-control" id="noNote"
                                        value="<?= $noBeli ?>">
                                </div>
                                <label for="tglNota" class="col-sm-2 col-form-label">Tgl Nota</label>
                                <div class="col-sm-4">
                                    <input type="date" name="tglNota" class="form-control" id="tglNota"
                                        value="<?= @$_GET['tgl'] ? $_GET['tgl'] : date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label for="supplier" class="col-sm-2 col-form-label">Supplier</label>
                                <div class="col-sm-10">
                                    <select name="supplier" id="supplier" class="form-control form-control-sm js-example-basic-single" required>
                                        <option value="">-- Pilih Supplier --</option>
                                        <?php
                                        $suppliers = mysqli_query($koneksi, "SELECT * FROM tbl_supplier");
                                        while ($s = mysqli_fetch_assoc($suppliers)) {
                                            $selected = (isset($_GET['supplier']) && $_GET['supplier'] == $s['id_supplier']) ? 'selected' : '';
                                            echo "<option value='{$s['id_supplier']}' $selected>
                    {$s['nama']} | {$s['deskripsi']}
                </option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-outline card-danger pt-3 px-3 pb-2">
                            <h6 class="font-weight-bold text-right">Total Pembelian</h6>
                            <h1 class="font-weight-bold text-right" style="font-size:40pt;">
                                <input type="hidden" name="total" value="<?= $total ?? 0 ?>">
                                <?= number_format($total ?? 0, 0, ',', '.') ?>
                            </h1>
                        </div>
                    </div>
                </div>
                <div class="card pt-1 pb-2 px-3">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="hidden" name="kodeBrg" id="kodeBrg">
                                <label for="namaBrg">Nama Barang</label>
                                <input list="listBarang" name="namaBrg" class="form-control form-control-sm"
                                    id="namaBrg">
                                <datalist id="listBarang">
                                    <?php
                                    foreach ($barangList as $b) {
                                        echo "<option value='{$b['nama_barang']}' 
                    data-id='{$b['id_barang']}' 
                    data-harga='{$b['harga_beli']}' 
                    data-stock='{$b['stock_minimal']}'>";
                                    }
                                    ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="satuan">Satuan</label>
                                <select name="satuan" id="satuan" class="form-control form-control-sm">
                                    <option value="">-- Pilih Satuan --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="harga">Harga</label>
                                <input type="number" name="harga" id="harga" class="form-control form-control-sm"
                                    value="<?= $selectBrg['harga_beli'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="qty">Quantity</label>
                                <input type="number" name="qty" id="qty" class="form-control form-control-sm"
                                    value="<?= $selectBrg ? 1 : '' ?>">
                            </div>
                        </div>
                        <div class=" col-lg-2">
                            <div class="form-group">
                                <label for="jmlHarga">Jumlah Harga</label>
                                <input type="number" name="jmlHarga" id="jmlHarga" class="form-control form-control-sm"
                                    value="<?= $selectBrg['harga_beli'] ?? '' ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-info btn-block" name="addbrg"><i
                            class="fas fa-cart-plus fa-sm"></i> Tambah Barang</button>
                </div>
                <div class="card card-outline card-success table-responsive px-2">
                    <table class="table-sm table-hover text-mowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Harga</th>
                                <th>Quantity</th>
                                <th>Jumlah Harga</th>
                                <th>Operasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $total = 0;
                            if (!empty($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $key => $item) {
                                    // Ambil detail barang + satuan buat ditampilkan
                                    $q = mysqli_query($koneksi, "SELECT b.nama_barang, s.satuan 
                                        FROM tbl_satuan s 
                                        JOIN tbl_barang b ON s.id_barang=b.id_barang 
                                        WHERE s.id_satuan='{$item['id_satuan']}'");
                                    $row = mysqli_fetch_assoc($q);

                                    $total += $item['subtotal'];
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $row['nama_barang'] ?> (<?= $row['satuan'] ?>)</td>
                                        <td><?= number_format($item['harga'], 0, ',', '.') ?></td>
                                        <td><?= $item['qty'] ?></td>
                                        <td><?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                        <td>
                                            <a href="?hapus=<?= $key ?>&tgl=<?= $_GET['tgl'] ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Hapus barang ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
    let namaBrg = document.getElementById('namaBrg');
    let kodeBrg = document.querySelector('input[name="kodeBrg"]');
    let harga = document.getElementById('harga');
    let qty = document.getElementById('qty');
    let jmlHarga = document.getElementById('jmlHarga');
    let satuan = document.getElementById('satuan'); // dropdown satuan

    // Fungsi hitung jumlah harga
    function hitungTotal() {
        let q = parseFloat(qty.value) || 0;
        let h = parseFloat(harga.value) || 0;
        jmlHarga.value = q * h;
    }

    namaBrg.addEventListener('change', function () {
        let val = this.value.trim();
        let selectedOption = Array.from(document.querySelectorAll('#listBarang option'))
            .find(option => option.value.trim().toLowerCase() === val.toLowerCase());

        if (selectedOption) {
            let idBarang = selectedOption.getAttribute('data-id');
            kodeBrg.value = idBarang;
            console.log("ID barang:", idBarang);

            fetch('get_satuan.php?id_barang=' + idBarang)
                .then(res => res.json())
                .then(data => {
                    console.log("Response dari get_satuan.php:", data);
                    satuan.innerHTML = '<option value="">-- Pilih Satuan --</option>';

                    if (Array.isArray(data)) {
                        data.forEach((item, index) => {
                            let opt = document.createElement('option');
                            opt.value = item.id_satuan;
                            opt.text = item.satuan + (item.nama_varian ? " - " + item.nama_varian : "");
                            opt.setAttribute('data-harga', item.harga_beli);
                            opt.setAttribute('data-stok', 0);
                            satuan.appendChild(opt);

                            // Pilih item pertama otomatis
                            if (index === 0) {
                                satuan.value = item.id_satuan;      // set dropdown
                                harga.value = item.harga_beli;      // update harga
                                hitungTotal();                      // hitung total
                                qty.focus();                         // arahkan ke quantity
                            }
                        });
                    }
                })
                .catch(err => console.error("Fetch error:", err));
        } else {
            kodeBrg.value = "";
            satuan.innerHTML = '<option value="">-- Pilih Satuan --</option>';
        }
    });


    satuan.addEventListener('change', function () {
        let selected = this.options[this.selectedIndex];
        harga.value = selected.getAttribute('data-harga') || '';
        hitungTotal();
    });

    // Tambahin event di qty dan harga
    qty.addEventListener('input', hitungTotal);
    harga.addEventListener('input', hitungTotal);
</script>

<?php
require "../template/footer.php";
?>