<?php
session_start();
if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: ../auth/login.php");
    exit();
}

require "../config/config.php";
require "../config/functions.php";
require "../module/mode-barang.php";

$title = "Laporan - CAngelline POS";
require "../template/header.php";
require "../template/navbar.php";
require "../template/sidebar.php";

// Ambil semua data barang
$stockBrg = getData("
    SELECT 
        b.id_barang,
        b.nama_barang,
        b.satuan_dasar,
        b.satuan_tertinggi,
        b.stok,
        b.stock_minimal
    FROM tbl_barang b
");

// Buat array konversi satuan
$konversi = getData("SELECT id_barang, satuan, jumlah_isi FROM tbl_satuan");

$konversiMap = [];
foreach ($konversi as $k) {
    $konversiMap[$k['id_barang']][$k['satuan']] = $k['jumlah_isi'];
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Stok</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= $main_url ?>dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Stock</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list fa-sm"></i> Stock</h3>
                    <a href="#" class="btn btn-sm btn-outline-primary float-right" data-toggle="modal"
                        data-target="#mdlFilterStock">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                </div>
                <div class="card-body table-responsive p-3">
                    <table class="table table-hover text-nowrap" id="tblData">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Jumlah Stok</th>
                                <th>Stock Minimal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($stockBrg as $stock) {
                                $id = $stock['id_barang'];
                                $stokDasar = $stock['stok'];

                                // cari faktor konversi total
                                $faktor = 1;
                                if (isset($konversiMap[$id])) {
                                    foreach ($konversiMap[$id] as $satuan => $jumlah_isi) {
                                        if ($jumlah_isi > 0) {   // hanya kalikan jika >0
                                            $faktor *= $jumlah_isi;
                                        }
                                    }
                                }

                                // hindari pembagian dengan nol
                                $stokTertinggi = ($faktor > 0) ? $stokDasar / $faktor : 0;

                                $status = ($stokTertinggi <= $stock['stock_minimal'])
                                    ? '<span class="text-danger">Stok Kurang</span>'
                                    : '<span class="text-success">Stok Aman</span>';

                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $stock['id_barang']; ?></td>
                                    <td><?= $stock['nama_barang']; ?></td>
                                    <td><?= $stock['satuan_tertinggi']; ?></td>
                                    <td class="text-center"><?= floor($stokTertinggi); ?></td>
                                    <td class="text-center"><?= $stock['stock_minimal']; ?></td>
                                    <td><?= $status ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="mdlFilterStock">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Filter Laporan Stok</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih Filter:</label>
                        <select id="filterStok" class="form-control">
                            <option value="">-- Semua Barang --</option>
                            <option value="kosong">Stok Kosong</option>
                            <option value="aman">Stok Aman</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="printStock()">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function printStock() {
    let filter = document.getElementById('filterStok').value;
    let url = "<?= $main_url ?>report/r-stock.php";
    if (filter) {
        url += "?stok=" + filter;
    }
    window.open(url, "_blank");
}
</script>
<?php require "../template/footer.php"; ?>