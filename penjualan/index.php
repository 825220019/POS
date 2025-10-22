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

$userLogin = userLogin();
$user_id = $userLogin["user_id"];


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
    $harga = (int) $_POST['harga_jual'];
    $subtotal = $qty * $harga;
    $tgl = $_POST['tglNota'];
    $pelanggan = $_POST['pelanggan'];


    $idSatuan = $_POST['satuan'] ?? null;

    if (empty($idSatuan)) {
        echo "<script>alert('Silakan pilih satuan terlebih dahulu!');</script>";
        echo "<script>history.back();</script>";
        exit;
    }

    if ($idSatuan) {
        $qStok = mysqli_query($koneksi, "
        SELECT b.stok, s.jumlah_isi AS faktor_konversi, v.nama_varian
FROM tbl_satuan s
JOIN tbl_barang b ON s.id_barang = b.id_barang
LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
WHERE s.id_satuan = '$idSatuan'
    ");
        $dataStok = mysqli_fetch_assoc($qStok);
    }

    // 🔍 Ambil data stok barang dari database
    // Ambil data stok barang dari database
    $stokBarang = (int) $dataStok['stok'];
    $faktor_konversi = (int) ($dataStok['faktor_konversi'] ?? 1);

    // 🔹 Cek faktor_konversi supaya tidak 0
    if ($faktor_konversi <= 0) {
        $faktor_konversi = 1; // default aman
    }

    // stok dalam satuan terpilih
    $stok = floor($stokBarang / $faktor_konversi);


    // 🚫 Validasi stok kosong
    if ($stok <= 0) {
        echo "<script>alert('❌ Stok barang ini sudah habis, tidak bisa ditambahkan!');</script>";
        echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
        exit;
    }

    // 🚫 Validasi qty kosong / nol
    if ($qty <= 0) {
        echo "<script>alert('❌ Qty belum diisi atau tidak boleh 0!');</script>";
        echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
        exit;
    }

    // 🚫 Validasi qty melebihi stok
    if ($qty > $stok) {
        echo "<script>alert('❌ Qty melebihi stok yang tersedia! (Stok saat ini: $stok)');</script>";
        echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
        exit;
    }

    // 🔍 Cek apakah barang sudah ada di keranjang
    $sudahAda = false;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id_satuan'] == $idSatuan) {
            $sudahAda = true;
            break;
        }
    }

    // 🚫 Barang duplikat
    if ($sudahAda) {
        echo "<script>alert('⚠️ Barang sudah ada di keranjang. Hapus dulu jika ingin ubah qty.');</script>";
    }
    // ✅ Barang valid, tambah ke keranjang
    else {
        $_SESSION['cart'][] = [
            'id_satuan' => $idSatuan,
            'qty' => $qty,
            'harga_jual' => $harga,
            'subtotal' => $subtotal
        ];
    }

    // 🔄 Reload halaman dengan parameter tanggal & pelanggan
    echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
}



// Simpan transaksi penjualan
if (isset($_POST['simpan'])) {
    $nojual = $_POST['nojual'];
    $tgl = $_POST['tglNota'];
    $id_pelanggan = $_POST['pelanggan'];

    // Ambil nama pelanggan berdasarkan id
    $pelangganQ = mysqli_query($koneksi, "SELECT nama FROM tbl_pelanggan WHERE id_pelanggan='$id_pelanggan'");
    $pelangganData = mysqli_fetch_assoc($pelangganQ);
    $pelanggan = $pelangganData ? $pelangganData['nama'] : 'Pelanggan Umum';

    // Hitung total dari session cart
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['subtotal'];
        }
    }

    // Ambil nilai tambahan dari form
    $hutang = isset($_POST['hutang']) ? (int) $_POST['hutang'] : 0;
    $jml_bayar = isset($_POST['jml_bayar']) ? (int) $_POST['jml_bayar'] : 0;
    $kembalian = isset($_POST['kembalian']) ? (int) $_POST['kembalian'] : 0;

    // Tambahkan hutang ke total
    $total += $hutang;

    // 🧩 Cek jika nomor jual sudah ada (hindari duplikasi)
    $cekNoJual = mysqli_query($koneksi, "SELECT no_jual FROM tbl_jual_head WHERE no_jual='$nojual'");
    if (mysqli_num_rows($cekNoJual) > 0) {
        echo "<script>alert('Nomor jual sudah digunakan. Silakan gunakan nomor lain.');</script>";
        exit;
    }

    // 🧾 Simpan ke tabel jual head
    // Ambil user_id dari session login
    $user_id = $_SESSION['ssLoginPOS']['user_id'] ?? 0;

    // 🧾 Simpan ke tabel jual head
    $insertHead = mysqli_query($koneksi, "INSERT INTO tbl_jual_head 
(no_jual, tgl_jual, total, hutang, jml_bayar, kembalian, user_id, id_pelanggan)
VALUES 
('$nojual', '$tgl', '$total', '$hutang', '$jml_bayar', '$kembalian', '$user_id', '$id_pelanggan')");
    if (!$insertHead) {
        die('Gagal menyimpan data penjualan head: ' . mysqli_error($koneksi));
    }

    // 💾 Simpan ke tabel detail penjualan
    foreach ($_SESSION['cart'] as $item) {
        $idSatuan = $item['id_satuan'];
        $qty = (int) $item['qty'];
        $harga = (int) $item['harga_jual'];
        $subtotal = (int) $item['subtotal'];

        // Ambil data barang dan faktor konversi
        $qBarang = mysqli_query($koneksi, "
    SELECT b.id_barang, s.jumlah_isi AS faktor_konversi, s.id_varian
    FROM tbl_satuan s
    JOIN tbl_barang b ON s.id_barang = b.id_barang
    WHERE s.id_satuan = '$idSatuan'
");
        $barang = mysqli_fetch_assoc($qBarang);
        $idVarian = $barang['id_varian'] ?? 'NULL';
        $idBarang = $barang['id_barang'];
        $faktor = (int) ($barang['faktor_konversi'] ?? 1);
        $qtyDasar = $qty * $faktor;

        // Update stok di tbl_barang
        mysqli_query($koneksi, "UPDATE tbl_barang SET stok = stok - $qtyDasar WHERE id_barang = '$idBarang'");

        // Insert ke detail jual
        // Ambil nama barang untuk ditampilkan di struk
        $qNama = mysqli_query($koneksi, "SELECT nama_barang FROM tbl_barang WHERE id_barang='$idBarang'");
        $dNama = mysqli_fetch_assoc($qNama);
        $namaBarang = $dNama['nama_barang'] ?? '';

        $insertDetail = mysqli_query($koneksi, "
     INSERT INTO tbl_jual_detail 
(no_jual, tgl_jual, id_barang, id_varian, nama_brg, qty, harga_jual, jml_harga, id_satuan)
VALUES 
('$nojual', '$tgl', '$idBarang', '$idVarian', '$namaBarang', $qty, $harga, $subtotal, '$idSatuan')");
    }


    // Hapus cart setelah tersimpan
    unset($_SESSION['cart']);

    // ✅ Tampilkan pesan sukses dan buka struk
    echo "<script>
alert('Data penjualan berhasil disimpan!');
let win = window.open('../report/r-struk.php?nojual=$nojual', 'Struk Penjualan', 'width=800,height=400,left=10,top=10');
if (win) {
    win.focus();
}
setTimeout(() => {
    window.location='index.php';
}, 2000);
</script>";
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
                                    <input type="text" name="nojual" class="form-control" id="noNota"
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
                                    <select name="pelanggan" id="pelanggan"
                                        class="form-control form-control-sm js-example-basic-single" required>
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
                                <input type="text" class="form-control text-right font-weight-bold" name="total"
                                    id="total" style="font-size: 30pt; height: 70px;" placeholder="0"
                                    value="<?= number_format((float) ($total + ((int) ($_POST['hutang'] ?? 0))), 0, ',', '.') ?>"
                                    readonly>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input type="number" name="hutang" id="hutang" oninput="hitungTotal()"
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
                                    value="<?= $selectBrg['stok'] ?? '' ?>" readonly>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label for="harga_jual">Harga</label>
                                <input type="number" name="harga_jual" id="harga_jual"
                                    class="form-control form-control-sm" value="<?= $selectBrg['harga_jual'] ?? '' ?>">
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
                    <button type="submit" class="btn btn-sm btn-info btn-block" name="addbrg"
                        onclick="updateTotalKeseluruhan(); hitungSubTotal();"><i class="fas fa-cart-plus fa-sm"></i>
                        Tambah Barang</button>
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
                                    $q = mysqli_query($koneksi, "
    SELECT b.nama_barang, s.satuan, v.nama_varian
    FROM tbl_satuan s
    JOIN tbl_barang b ON s.id_barang = b.id_barang
    LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
    WHERE s.id_satuan = '{$item['id_satuan']}'
");
                                    $row = mysqli_fetch_assoc($q);
                                    $total += $item['subtotal'];
                                    echo "
<tr>
    <td>$no</td>
    <td>{$row['nama_barang']}" .
                                        (!empty($row['nama_varian']) ? " - {$row['nama_varian']}" : "") .
                                        " ({$row['satuan']})</td>
    <td>" . number_format($item['harga_jual'], 0, ',', '.') . "</td>
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
    let harga = document.getElementById('harga_jual');
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

    satuan.addEventListener('change', function () {
        let selected = this.options[this.selectedIndex];
        harga.value = selected.getAttribute('data-harga') || 0;
        stok.value = selected.getAttribute('data-stok') || 0;
        hitungSubTotal(); // otomatis hitung ulang
    });

    function hitungSubTotal() {
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
                            opt.setAttribute('data-stok', item.stok);
                            satuan.appendChild(opt);
                        });
                    });
            }
        });
    });

    qty.addEventListener('input', hitungSubTotal);
    harga.addEventListener('input', hitungSubTotal);

    function updateTotalKeseluruhan() {
        let total = 0;
        document.querySelectorAll('tbody tr').forEach(row => {
            let jml = row.children[4]?.innerText?.replace(/\./g, '') || 0;
            total += parseInt(jml);
        });
        document.getElementById('total').value = total.toLocaleString('id-ID');
    }

    function hitungTotal() {
        let totalBarang = 0;
        document.querySelectorAll('tbody tr').forEach(row => {
            let jml = row.children[4]?.innerText?.replace(/\./g, '') || 0;
            totalBarang += parseInt(jml);
        });

        let hutang = parseInt(document.getElementById('hutang').value) || 0;
        let totalAkhir = totalBarang + hutang;

        document.getElementById('total').value = totalAkhir.toLocaleString('id-ID');
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Saat tombol hapus diklik, pastikan total diperbarui setelah reload
        const deleteLinks = document.querySelectorAll('a[href*="hapus="]');
        deleteLinks.forEach(link => {
            link.addEventListener('click', function () {
                localStorage.setItem('hutang', document.getElementById('hutang').value);
            });
        });

        // Setelah reload, kembalikan nilai hutang ke input
        const hutangValue = localStorage.getItem('hutang');
        if (hutangValue) {
            document.getElementById('hutang').value = hutangValue;
            localStorage.removeItem('hutang');
            hitungTotal();
        }
    });

    function hitungKembalian() {
        let totalText = document.getElementById('total').value.replace(/\./g, '');
        let total = parseFloat(totalText) || 0;
        let jmlBayar = parseFloat(document.getElementById('jml_bayar').value) || 0;

        let hasil = jmlBayar - total;
        let kembalianField = document.getElementById('kembalian');

        // Jika hasil negatif → berarti masih hutang
        if (hasil < 0) {
            kembalianField.value = `Hutang ${Math.abs(hasil)}`;
            kembalianField.style.color = 'red';
        } else {
            kembalianField.value = hasil;
            kembalianField.style.color = 'green';
        }
    }

    // Jalankan fungsi setiap kali jumlah bayar berubah
    document.getElementById('jml_bayar').addEventListener('input', hitungKembalian);

</script>

<?php
require "../template/footer.php";
?>