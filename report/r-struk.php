<?php
session_start();
if (!isset($_SESSION["ssLoginPOS"])) {
    header("location: ../auth/login.php");
    exit();
}

require "../config/config.php";
require "../config/functions.php";

// ✅ Pastikan parameter nojual dikirim
if (!isset($_GET['nojual']) || empty($_GET['nojual'])) {
    echo "<script>
        alert('Nomor nota tidak ditemukan!');
        window.close();
    </script>";
    exit();
}

$nota = mysqli_real_escape_string($koneksi, $_GET['nojual']);

$dataJualQuery = getData("
    SELECT j.*, p.nama AS nama_pelanggan 
    FROM tbl_jual_head j
    LEFT JOIN tbl_pelanggan p ON j.id_pelanggan = p.id_pelanggan
    WHERE j.no_jual = '$nota'
");

if (empty($dataJualQuery)) {
    echo "<script>
        alert('Data penjualan tidak ditemukan!');
        window.close();
    </script>";
    exit();
}

$dataJual = $dataJualQuery[0];
$itemJual = getData("
    SELECT d.*, b.nama_barang, v.nama_varian, s.satuan
    FROM tbl_jual_detail d
    JOIN tbl_barang b ON d.id_barang = b.id_barang
    LEFT JOIN tbl_satuan s ON d.id_satuan = s.id_satuan
    LEFT JOIN tbl_varian v ON s.id_varian = v.id_varian
    WHERE d.no_jual = '$nota'
");

$selisih = $dataJual['jml_bayar'] - $dataJual['total'];

if ($selisih >= 0) {
    $label = "Kembalian";
    $nilai = $selisih;
} else {
    $label = "Kurang";
    $nilai = abs($selisih); // ubah minus jadi positif
}
?>


<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Belanja</title>
</head>

<body>
    <table style="border-bottom: solid 2px; text-align:center;
    font-size: 14px; width:240px;">
        <tr>
            <td><b>CAngelline POS</b></td>
        </tr>
        <tr>
            <td><?= 'Kasir : ' . userLogin()['username'] ?></td>
        </tr>
        <tr>
            <td><?= 'No Nota : ' . $nota ?></td>
        </tr>
        <tr>
            <td><?= date('d-m-Y H:i:s') ?></td>
        </tr>
        <tr>
            <td><?= !empty($dataJual['nama_pelanggan']) ? $dataJual['nama_pelanggan'] : 'Pelanggan Umum' ?></td>
        </tr>
    </table>
    <table style="border-bottom: dotted 2px; font-size: 14px; width:240px;">
        <?php
        foreach ($itemJual as $item) {
            ?>
            <tr>
                <td colspan="6">
                    <?= $item['nama_barang'] ?>
                    <?php if (!empty($item['nama_varian'])) { ?>
                        - <?= $item['nama_varian'] ?>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:left">
                    <?= $item['qty'] . ' ' . ($item['satuan'] ?? '') ?>
                </td>
                <td style="width: 70px; text-align:right"> x <?= number_format($item['harga_jual'], 0, ',', '.') ?></td>
                <td style="width: 70px; text-align:right" colspan="2"> =
                    <?= number_format($item['jml_harga'], 0, ',', '.') ?>
                </td>
            </tr>
        <?php } ?>
    </table>
    <table style="border-bottom: dotted 2px; font-size: 14px; width:240px;">
        <tr>
            <td colspan="3" style="width: 100px;"></td>
            <td style="width: 50px; text-align: right">Hutang</td>
            <td style="width: 70px; text-align: right" colspan="2">
                <b><?= number_format($dataJual['hutang'], 0, ',', '.') ?></b>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="width: 100px;"></td>
            <td style="width: 50px; text-align: right">Total</td>
            <td style="width: 70px; text-align: right" colspan="2">
                <b><?= number_format($dataJual['total'], 0, ',', '.') ?></b>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="width: 100px;"></td>
            <td style="width: 50px; text-align: right">Bayar</td>
            <td style="width: 70px; text-align: right" colspan="2">
                <b><?= number_format($dataJual['jml_bayar'], 0, ',', '.') ?></b>
            </td>
        </tr>
    </table>
    <table style="border-bottom: solid 2px; font-size: 14px; width:240px;">
        <tr>
            <td colspan="3" style="width: 100px;"></td>
            <td style="width: 50px; text-align: right"><?= $label ?></td>
            <td style="width: 70px; text-align: right" colspan="2">
                <b><?= number_format($nilai, 0, ',', '.') ?></b>
            </td>
        </tr>
    </table>
    <table style="text-align:center; margin-top: 5px; font-size: 14px; width:240px;">
        <tr>
            <td>Terima kasih sudah berbelanja</td>
        </tr>
    </table>

    <script>
        setTimeout(function () {
            window.print();
        }, 1000);
        setTimeout(() => { window.close(); }, 3000);
    </script>
</body>

</html>