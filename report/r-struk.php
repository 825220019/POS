<?php

$nota = $_GET['nojual'];
$dataJual = getData("SELECT * FROM tbl_jual_head WHERE no_jual = '$nota'")[0];
$itemJual = getData("SELECT * FROM tbl_jual_detail WHERE no_jual = '$nota'");


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
        <td><?= 'No Nota : ' . $nota?></td>
    </tr>
    <tr>
        <td><?= date('d-m-Y H:i:s')?></td>
    </tr>
    <tr>
        <td><?= userLogin()['username']?></td>
    </tr>
</table>
</body>
</html>