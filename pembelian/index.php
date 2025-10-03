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

if ($msg == 'deleted') {
    $idbrg = $_GET['idbrg'];
    $idbeli = $_GET['idbeli'];
    $qty = $_GET['qty'];
    $tgl = $_GET['tgl'];
    delete($idbrg, $idbeli, $qty);
    echo "<script>
            document.location = '?tgl=$tgl'
        </script>";
}

if (isset($_GET['id_barang'])) {
    $id_barang = $_GET['id_barang'];
    $kode = isset($_GET['pilihbrg']) ? $_GET['pilihbrg'] : '';
    $satuanList = [];
    if ($kode) {
        $satuanList = getData("
        SELECT s.satuan, s.harga_jual, s.stock 
        FROM tbl_satuan s
        JOIN tbl_varian v ON s.id_varian = v.id_varian
        WHERE v.id_barang = '$kode'
    ");
    }
}

if (isset($_POST['addbrg'])) {
    $tgl = $_POST['tglNota'];
    if (insert($_POST)) {
        echo "<script>
            document.location = '?tgl=$tgl'
        </script>";
    }
}

if (isset($_POST['simpan'])) {
    if (simpan($_POST)) {
        echo "<script>
            alert ('data pembelian berhasil disimpan')
            document.location = 'index.php'
        </script>";
    }
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
                                    <select name="supplier" id="suppplier" class="form-control form-control-sm">
                                        <option value="">-- Pilih Supplier --</option>
                                        <?php
                                        $suppliers = getData("SELECT * FROM tbl_supplier");
                                        foreach ($suppliers as $supplier) { ?>
                                            <option value="<?= $supplier['id_supplier'] ?>" <?= ($msg != '' && $barang['id_supplier'] == $supplier['id_supplier']) ? 'selected' : '' ?>>
                                                <?= $supplier['nama'] . " | " . $supplier['deskripsi'] ?>
                                            </option>
                                            <?php
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-outline card-danger pt-3 px-3 pb-2">
                            <h6 class="font-weight-bold text-right">Total Pembelian</h6>
                            <h1 class="font-weight-bold text-right" style="font-size:40pt;">
                                <input type="hidden" name="total" value="<?= totalBeli($noBeli) ?>">
                                <?= number_format(totalBeli($noBeli), 0, ',', '.') ?></h6>
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
                    <button type="submit" class="btn btn-sm btn-info btn-block" name="addbrg"><i
                            class="fas fa-cart-plus fa-sm"></i> Tambah Barang</button>
                </div>
                <div class="card card-outline card-success table-responsive px-2">
                    <table class="table-sm table-hover text-mowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">Jumlah Harga</th>
                                <th class="text-center" width="10%">Operasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $brgDetail = getData("SELECT * FROM tbl_beli_detail WHERE no_beli = '$noBeli'");
                            foreach ($brgDetail as $detail) { ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $detail['kode_brg'] ?></td>
                                    <td><?= $detail['nama_brg'] ?></td>
                                    <td class="text-right"><?= number_format($detail['harga_beli'], 0, ',', '.') ?></td>
                                    <td class="text-right"><?= $detail['qty'] ?></td>
                                    <td class="text-right"><?= number_format($detail['jml_harga'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <a href="?idbrg=<?= $detail['kode_brg'] ?>
                                        &idbeli=<?= $detail['no_beli'] ?>&qty=<?=
                                              $detail['qty'] ?>&tgl=<?= $detail['tgl_beli'] ?>
                                        &msg=deleted" class="btn btn-sm btn-danger" title="hapus barang"
                                            onclick="return confirm('Mau hapus barang ?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php
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
    let stok = document.getElementById('stok');
    let qty = document.getElementById('qty');
    let jmlHarga = document.getElementById('jmlHarga');
    let satuan = document.getElementById('satuan'); // dropdown satuan

    namaBrg.addEventListener('input', function () {
        let val = this.value;
        let opts = document.querySelectorAll('#listBarang option');

        let found = false;
        opts.forEach(option => {
            if (option.value === val) {
                let idBarang = option.getAttribute('data-id');
                kodeBrg.value = idBarang; // isi hidden input

                console.log("ID barang:", idBarang);

                // fetch data satuan
                fetch('get_satuan.php?id_barang=' + idBarang)
                    .then(res => res.json())
                    .then(data => {
                        console.log("Response dari get_satuan.php:", data);

                        satuan.innerHTML = '<option value="">-- Pilih Satuan --</option>';

                        // kalau data berupa array langsung
                        if (Array.isArray(data)) {
                            data.forEach(item => {
                                let opt = document.createElement('option');
                                opt.value = item.satuan;
                                opt.text = item.satuan;
                                opt.setAttribute('data-harga', item.harga_jual);
                                opt.setAttribute('data-stok', item.stock);
                                satuan.appendChild(opt);
                            });
                        }

                        // kalau response ternyata object dengan key "data"
                        else if (data.data && Array.isArray(data.data)) {
                            data.data.forEach(item => {
                                let opt = document.createElement('option');
                                opt.value = item.satuan;
                                opt.text = item.satuan;
                                opt.setAttribute('data-harga', item.harga_jual);
                                opt.setAttribute('data-stok', item.stock);
                                satuan.appendChild(opt);
                            });
                        }

                    })
                    .catch(err => console.error("Fetch error:", err));

            }
        });

        if (!found) {
            kodeBrg.value = ""; // reset kalau barang tidak cocok
        }
    });


    satuan.addEventListener('change', function () {
        let selected = this.options[this.selectedIndex];
        harga.value = selected.getAttribute('data-harga') || '';
        stok.value = selected.getAttribute('data-stok') || '';
    });

    qty.addEventListener('input', function () {
        jmlHarga.value = qty.value * harga.value;
    })

</script>
<?php
require "../template/footer.php";
?>