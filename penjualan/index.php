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

$user_id = userLogin()["user_id"];
$msg = $_GET['msg'] ?? '';

// hapus item dari cart
if (isset($_GET['hapus'])) {
    $key = $_GET['hapus'];
    unset($_SESSION['cart'][$key]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    echo "<script>document.location='?tgl={$_GET['tgl']}&pelanggan={$_GET['pelanggan']}'</script>";
    exit;
}

// buat jika belum ada
if (!isset($_SESSION['cart']))
    $_SESSION['cart'] = [];

// tambah barang ke cart
if (isset($_POST['addbrg'])) {
    $idSatuan = $_POST['satuan'] ?? null;
    $qty = (int) $_POST['qty'];
    $harga = (int) $_POST['harga_jual'];
    $tgl = $_POST['tglNota'];
    $pelanggan = $_POST['pelanggan'];

    if (empty($idSatuan)) {
        echo "<script>alert('Silakan pilih satuan terlebih dahulu!');history.back();</script>";
        exit;
    }

    $qStok = mysqli_query($koneksi, "
        SELECT b.stok AS stok_dasar, s.jumlah_isi AS faktor_konversi
        FROM tbl_satuan s
        JOIN tbl_barang b ON s.id_barang = b.id_barang
        WHERE s.id_satuan = '$idSatuan'
    ");
    $dataStok = mysqli_fetch_assoc($qStok);
    $stok = floor($dataStok['stok_dasar'] / max(1, $dataStok['faktor_konversi']));

    if ($stok <= 0) {
        echo "<script>alert('Stok habis!');document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
        exit;
    }
    if ($qty <= 0) {
        echo "<script>alert('Qty tidak boleh 0!');document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
        exit;
    }
    if ($qty > $stok) {
        echo "<script>alert('Qty melebihi stok ($stok tersedia)!');document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
        exit;
    }

    foreach ($_SESSION['cart'] as $item) {
        if ($item['id_satuan'] == $idSatuan) {
            echo "<script>alert('Barang sudah ada di keranjang!');</script>";
            echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
            exit;
        }
    }

    $_SESSION['cart'][] = [
        'id_satuan' => $idSatuan,
        'qty' => $qty,
        'harga_jual' => $harga,
        'subtotal' => $qty * $harga
    ];

    echo "<script>document.location='?tgl=$tgl&pelanggan=$pelanggan'</script>";
    exit;
}

// simpan transaksi
if (isset($_POST['simpan'])) {
    if (empty($_SESSION['cart'])) {
        echo "<script>alert('Tidak ada barang dalam transaksi!');history.back();</script>";
        exit;
    }

    $nojual = $_POST['nojual'];
    $tgl = $_POST['tglNota'];
    $id_pelanggan = $_POST['pelanggan'];
    $hutang = (int) ($_POST['hutang'] ?? 0);
    $jml_bayar = (int) ($_POST['jml_bayar'] ?? 0);
    $kembalian = (int) ($_POST['kembalian'] ?? 0);

    $total = array_sum(array_column($_SESSION['cart'], 'subtotal')) + $hutang;

    if (mysqli_num_rows(mysqli_query($koneksi, "SELECT no_jual FROM tbl_jual_head WHERE no_jual='$nojual'")) > 0) {
        echo "<script>alert('Nomor jual sudah digunakan!');</script>";
        exit;
    }

    mysqli_query($koneksi, "INSERT INTO tbl_jual_head 
        (no_jual, tgl_jual, total, hutang, jml_bayar, kembalian, user_id, id_pelanggan)
        VALUES ('$nojual', '$tgl', '$total', '$hutang', '$jml_bayar', '$kembalian', '$user_id', '$id_pelanggan')
    ");

    foreach ($_SESSION['cart'] as $item) {
        $idSatuan = $item['id_satuan'];
        $qty = (int) $item['qty'];
        $harga = (int) $item['harga_jual'];
        $subtotal = (int) $item['subtotal'];

        $qBarang = mysqli_query($koneksi, "
            SELECT b.id_barang, s.jumlah_isi AS faktor_konversi, s.id_varian
            FROM tbl_satuan s
            JOIN tbl_barang b ON s.id_barang = b.id_barang
            WHERE s.id_satuan = '$idSatuan'
        ");
        $barang = mysqli_fetch_assoc($qBarang);
        $idBarang = $barang['id_barang'];
        $idVarian = $barang['id_varian'] ?? null;
        $faktor = max(1, $barang['faktor_konversi']);
        $qtyDasar = $qty * $faktor;

        mysqli_query($koneksi, "UPDATE tbl_barang SET stok = stok - $qtyDasar WHERE id_barang = '$idBarang'");

        $namaBarang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_barang FROM tbl_barang WHERE id_barang='$idBarang'"))['nama_barang'] ?? '';

        mysqli_query($koneksi, "
            INSERT INTO tbl_jual_detail (no_jual, tgl_jual, id_barang, id_varian, nama_brg, qty, harga_jual, jml_harga, id_satuan)
            VALUES ('$nojual', '$tgl', '$idBarang', " . ($idVarian ? "'$idVarian'" : "NULL") . ", '$namaBarang', $qty, $harga, $subtotal, '$idSatuan')
        ");
    }

    unset($_SESSION['cart']);
    echo "<script>
        alert('Data penjualan berhasil disimpan!');
        window.open('../report/r-struk.php?nojual=$nojual', 'Struk', 'width=800,height=400');
        setTimeout(() => window.location='index.php', 2000);
    </script>";
    exit;
}

$noJual = generateNo();
$total = array_sum(array_column($_SESSION['cart'], 'subtotal')) ?? 0;
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
                                    <select name="pelanggan" id="pelanggan" class="form-select form-select-sm" required>
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
                            <div class="form-group mb-3  text-center">
                                <input type="text" class="form-control text-right font-weight-bold" name="total"
                                    id="total" style="font-size: 30pt; height: 70px;" placeholder="0"
                                    value="<?= number_format((float) ($total + ((int) ($_POST['hutang'] ?? 0))), 0, ',', '.') ?>"
                                    readonly>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input type="number" name="hutang" id="hutang" oninput="hitungTotal()"
                                            class="form-control form-control-lg" placeholder="Hutang">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input type="number" name="jml_bayar" id="jml_bayar"
                                            class="form-control form-control-lg" placeholder="Bayar">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input type="text" name="kembalian" id="kembalian"
                                            class="form-control form-control-lg" readonly>
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
                                <select name="namaBrg" id="namaBrg" class="form-control form-control-sm">
                                    <option value="">-- Pilih Barang --</option>
                                    <?php
                                    $barangQ = mysqli_query($koneksi, "SELECT * FROM tbl_barang ORDER BY nama_barang ASC");
                                    while ($b = mysqli_fetch_assoc($barangQ)) {
                                        echo "<option value='{$b['id_barang']}'>{$b['nama_barang']}</option>";
                                    }
                                    ?>
                                </select>
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
                        Tambah Barang
                    </button>
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
    let namaBrg = document.getElementById('namaBrg');
    let kodeBrg = document.getElementById('kodeBrg');
    let harga = document.getElementById('harga_jual');
    let stok = document.getElementById('stok');
    let qty = document.getElementById('qty');
    let jmlHarga = document.getElementById('jmlHarga');
    let satuan = document.getElementById('satuan');
    let addbrg = document.getElementById('addbrg');

    // Select2 untuk pelanggan
    $(document).ready(function () {
        $('#pelanggan').select2({
            placeholder: "-- Pilih atau cari pelanggan --",
            allowClear: true,
            width: '100%',
            dropdownCssClass: 'text-lg',
        });
        $('.select2-selection--single').css({
            'height': '48px',       // tinggi kolom
        });

        // Biar panah dropdown sejajar
        $('.select2-selection__arrow').css('height', '46px');
    });

    // Select2 untuk barang
    $(document).ready(function () {
        $('#namaBrg').select2({
            placeholder: "-- Pilih atau cari barang --",
            allowClear: true,
            width: '100%'
        });
    });

    // Ketika pilih satuan
    satuan.addEventListener('change', function () {
        let selected = this.options[this.selectedIndex];
        let stokSelected = parseInt(selected.getAttribute('data-stok')) || 0;
        let hargaSelected = parseFloat(selected.getAttribute('data-harga')) || 0;

        if (stokSelected <= 0) {
            alert('Stok untuk satuan ini sudah habis!');
            this.value = '';
            harga.value = '';
            stok.value = 0;
            jmlHarga.value = '';
            return;
        }

        harga.value = hargaSelected;
        stok.value = stokSelected;
        hitungSubTotal();
    });

    // Hitung subtotal
    function hitungSubTotal() {
        let q = parseFloat(qty.value) || 0;
        let h = parseFloat(harga.value) || 0;
        jmlHarga.value = q * h;
    }

    // Ambil data satuan dari barang
    // Ketika barang dipilih
    $('#namaBrg').on('change', function () {
        let idBarang = $(this).val();
        if (!idBarang) {
            $('#satuan').html('<option value="">-- Pilih Satuan --</option>');
            $('#stok').val('');
            $('#harga_jual').val('');
            return;
        }
        fetch('get_satuan.php?id_barang=' + idBarang)
            .then(res => res.json())
            .then(data => {
                let satuanSelect = document.getElementById('satuan');
                satuanSelect.innerHTML = '<option value="">-- Pilih Satuan --</option>';
                data.forEach(item => {
                    let opt = document.createElement('option');
                    opt.value = item.id_satuan;
                    opt.text = item.satuan + (item.nama_varian ? " - " + item.nama_varian : "");
                    opt.setAttribute('data-harga', item.harga_jual);
                    opt.setAttribute('data-stok', item.stok);
                    satuanSelect.appendChild(opt);
                });
            }
        );
    });


    qty.addEventListener('input', hitungSubTotal);
    harga.addEventListener('input', hitungSubTotal);

    // Hitung total dan hutang
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

    // Hitung kembalian otomatis
    function hitungKembalian() {
        let totalText = document.getElementById('total').value.replace(/\./g, '');
        let total = parseFloat(totalText) || 0;
        let jmlBayar = parseFloat(document.getElementById('jml_bayar').value) || 0;
        let hasil = jmlBayar - total;
        let kembalianField = document.getElementById('kembalian');

        if (hasil < 0) {
            kembalianField.value = `Hutang ${Math.abs(hasil)}`;
            kembalianField.style.color = 'red';
        } else {
            kembalianField.value = hasil;
            kembalianField.style.color = 'green';
        }
    }

    document.getElementById('jml_bayar').addEventListener('input', hitungKembalian);

    // Navigasi antar kolom: Enter & Panah Kiri–Kanan
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = [namaBrg, satuan, qty];

        inputs.forEach((input, i) => {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    if (inputs[i + 1]) inputs[i + 1].focus();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    if (inputs[i - 1]) inputs[i - 1].focus();
                }
            });
        });

        qty.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                hitungSubTotal();

                // Pastikan field terisi lengkap sebelum submit otomatis
                const idSatuan = satuan.value;
                const q = parseFloat(qty.value) || 0;
                const h = parseFloat(harga.value) || 0;

                if (!idSatuan) {
                    alert('Silakan pilih satuan terlebih dahulu!');
                    satuan.focus();
                    return;
                }
                if (q <= 0) {
                    alert('Quantity tidak boleh 0!');
                    qty.focus();
                    return;
                }
                if (h <= 0) {
                    alert('Harga jual belum terisi!');
                    harga.focus();
                    return;
                }

                // Simulasi klik tombol Tambah Barang
                document.querySelector('button[name="addbrg"]').click();
            }
        });
    });
</script>


<?php
require "../template/footer.php";
?>