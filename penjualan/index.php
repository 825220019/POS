<?php
session_start();
if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: ../auth/login.php");
    exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-jual.php";

$title = "Transaksi Penjualan - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

$msg = $_GET['msg'] ?? '';

// Hapus item dari keranjang
if (isset($_GET['hapus'])) {
    $key = $_GET['hapus'];
    unset($_SESSION['cart'][$key]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    $tgl = $_GET['tgl'] ?? date('Y-m-d');
    $pelanggan = $_GET['pelanggan'] ?? '';
    echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
}

// Buat session cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tambah barang ke keranjang
if (isset($_POST['addbrg'])) {
    $idSatuan = $_POST['satuan'];
    $qty = (int) $_POST['qty'];
    $harga = (int) $_POST['harga'];
    $subtotal = $qty * $harga;

    $_SESSION['cart'][] = [
        'id_satuan' => $idSatuan,
        'qty' => $qty,
        'harga' => $harga,
        'subtotal' => $subtotal
    ];

    $tgl = $_POST['tglNota'];
    $pelanggan = $_POST['pelanggan'];
    echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
}

// Simpan transaksi penjualan
if (isset($_POST['simpan'])) {
    $nojual = $_POST['nojual'];
    $tgl = $_POST['tglNota'];
    $pelanggan = $_POST['pelanggan'];
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['subtotal'];
        }
    }

    $hutang = $_POST['hutang'] ?? 0;
    $jml_bayar = $_POST['jml_bayar'] ?? 0;
    $kembalian = $_POST['kembalian'] ?? 0;

    // Simpan ke tabel penjualan head
    mysqli_query($koneksi, "INSERT INTO tbl_jual_head (no_jual, tgl_jual, pelanggan, total, hutang, jml_bayar, kembalian)
                            VALUES ('$nojual', '$tgl', '$pelanggan', '$total', '$hutang', '$jml_bayar', '$kembalian')");

    // Simpan ke tabel detail penjualan
    foreach ($_SESSION['cart'] as $item) {
        $idSatuan = $item['id_satuan'];
        $qty = $item['qty'];
        $harga = $item['harga'];
        $subtotal = $item['subtotal'];

        // Ambil data barang
        $qBarang = mysqli_query($koneksi, "SELECT b.id_barang, b.nama_barang, b.harga_beli 
                                           FROM tbl_satuan s
                                           JOIN tbl_barang b ON s.id_barang = b.id_barang
                                           WHERE s.id_satuan = '$idSatuan'");
        $barang = mysqli_fetch_assoc($qBarang);

        $kodeBrg = $barang['id_barang'];
        $namaBrg = $barang['nama_barang'];
        $hargaBeli = $barang['harga_beli'];

        mysqli_query($koneksi, "INSERT INTO tbl_jual_detail (no_jual, tgl_jual, kode_brg, nama_brg, qty, harga_beli, jml_harga)
                                VALUES ('$nojual', '$tgl', '$kodeBrg', '$namaBrg', '$qty', '$hargaBeli', '$subtotal')");

        // Kurangi stok
        mysqli_query($koneksi, "UPDATE tbl_satuan SET stock = stock - $qty WHERE id_satuan='$idSatuan'");
    }

    unset($_SESSION['cart']);
    echo "<script>alert('Data penjualan berhasil disimpan'); document.location='index.php';</script>";
}

$noJual = generateNo();

$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['subtotal'];
    }
}


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
                        <li class="breadcrumb-item active">Add Penjualan</li>
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
                        Penjualan Barang
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
                            <div class="form-group row mb-2 mt-3">
                                <label for="noNota" class="col-sm-2 col-form-label">No Nota</label>
                                <div class="col-sm-4">
                                    <input type="text" name="nojual" class="form-control" id="noNote"
                                        value="<?= $noJual ?>" readonly>
                                </div>
                                <label for="tglNota" class="col-sm-2 col-form-label">Tgl Nota</label>
                                <div class="col-sm-4">
                                    <input type="date" name="tglNota" class="form-control" id="tglNota"
                                        value="<?= @$_GET['tgl'] ? $_GET['tgl'] : date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="form-group row mb-2 mt-4">
                                <label for="pelanggan" class="col-sm-2 col-form-label">Pelanggan</label>
                                <div class="col-sm-10">
                                    <select name="pelanggan" id="pelanggan" class="form-control form-control-sm"
                                        required>
                                        <option value="">-- Pilih Pelanggan --</option>
                                        <?php
                                        $pelangganQ = mysqli_query($koneksi, "SELECT * FROM tbl_pelanggan");
                                        while ($p = mysqli_fetch_assoc($pelangganQ)) {
                                            $selected = (isset($_GET['pelanggan']) && $_GET['pelanggan'] == $p['id_pelanggan']) ? 'selected' : '';
                                            echo "<option value='{$p['id_pelanggan']}' $selected>
                        {$p['nama']} | {$p['deskripsi']}
                      </option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-outline card-warning p-3">
                            <div class="form-group mb-3 text-center">
                                <input type="number" class="form-control text-right font-weight-bold" name="total"
                                    id="total" style="font-size: 30pt; height: 70px;" placeholder="0"
                                    value="<?= number_format((float) $total, 0, ',', '.') ?>" readonly>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input type="number" name="hutang" id="hutang"
                                            class="form-control form-control-sm" placeholder="Masukkan jumlah hutang">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input type="number" name="jml_bayar" id="jml_bayar"
                                            class="form-control form-control-sm" placeholder="Masukkan jumlah bayar">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input type="text" name="kembalian" id="kembalian"
                                            class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
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
                                        echo "<option value='{$b['nama_barang']}' data-id='{$b['id_barang']}'></option>";
                                    }
                                    ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <div class="form-group">
                                <label for="satuan">Satuan</label>
                                <select name="satuan" id="satuan" class="form-control form-control-sm">
                                    <option value="">-- Pilih Satuan --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <div class="form-group">
                                <label for="stok">Stok</label>
                                <input type="number" name="stok" id="stok" class="form-control form-control-sm"
                                    value="<?= $selectBrg['stock'] ?? '' ?>" readonly>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="harga">Harga</label>
                                <input type="number" name="harga" id="harga" class="form-control form-control-sm"
                                    value="<?= $selectBrg['harga_jual'] ?? '' ?>">
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
                                    value="<?= $selectBrg['harga_jual'] ?? '' ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-info btn-block" name="addbrg" onclick="updateTotalKeseluruhan()"><i
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
                                    $q = mysqli_query($koneksi, "SELECT b.nama_barang, s.satuan 
                                        FROM tbl_satuan s 
                                        JOIN tbl_barang b ON s.id_barang=b.id_barang 
                                        WHERE s.id_satuan='{$item['id_satuan']}'");
                                    $row = mysqli_fetch_assoc($q);
                                    $total += $item['subtotal'];
                                    echo "
<tr>
    <td>$no</td>
    <td>{$row['nama_barang']} ({$row['satuan']})</td>
    <td>" . number_format($item['harga'], 0, ',', '.') . "</td>
    <td>{$item['qty']}</td>
    <td>" . number_format($item['subtotal'], 0, ',', '.') . "</td>
    <td><a href='?hapus={$key}&tgl={$_GET['tgl']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Hapus barang ini?')\"><i class='fas fa-trash'></i></a></td>
</tr>";
                                    $no++;

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
    // Script untuk ambil data satuan dan hitung harga
    let namaBrg = document.getElementById('namaBrg');
    let kodeBrg = document.getElementById('kodeBrg');
    let harga = document.getElementById('harga');
    let stok = document.getElementById('stok');
    let qty = document.getElementById('qty');
    let jmlHarga = document.getElementById('jmlHarga');
    let satuan = document.getElementById('satuan');

    $(document).ready(function () {
        $('#pelanggan').select2({
            placeholder: "-- Pilih atau cari pelanggan --",
            allowClear: true,
            width: '100%'
        });
    });

    function hitungTotal() {
        let q = parseFloat(qty.value) || 0;
        let h = parseFloat(harga.value) || 0;
        jmlHarga.value = q * h;
    }

    namaBrg.addEventListener('input', function () {
        let val = this.value;
        let opts = document.querySelectorAll('#listBarang option');
        opts.forEach(option => {
            if (option.value === val) {
                let idBarang = option.getAttribute('data-id');
                kodeBrg.value = idBarang;
                fetch('get_satuan.php?id_barang=' + idBarang)
                    .then(res => res.json())
                    .then(data => {
                        satuan.innerHTML = '<option value="">-- Pilih Satuan --</option>';
                        data.forEach(item => {
                            let opt = document.createElement('option');
                            opt.value = item.id_satuan;
                            opt.text = item.satuan + (item.nama_varian ? " - " + item.nama_varian : "");
                            opt.setAttribute('data-harga', item.harga_jual);
                            opt.setAttribute('data-stok', item.stock);
                            satuan.appendChild(opt);
                        });
                    });
            }
        });
    });

    satuan.addEventListener('change', function () {
        let selected = this.options[this.selectedIndex];
        harga.value = selected.getAttribute('data-harga') || '';
        stok.value = selected.getAttribute('data-stok') || '';
        hitungTotal();
    });

    qty.addEventListener('input', hitungTotal);
    harga.addEventListener('input', hitungTotal);

    function updateTotalKeseluruhan() {
        let total = 0;
        document.querySelectorAll('tbody tr').forEach(row => {
            let jml = row.children[4]?.innerText?.replace(/\./g, '') || 0;
            total += parseInt(jml);
        });
        document.getElementById('total').value = total.toLocaleString('id-ID');
    }

</script>

<?php
require "../template/footer.php";
?>