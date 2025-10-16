<?php

function getData($sql){
    global $koneksi;

    $result = mysqli_query($koneksi, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
}
return $rows;
}

function userLogin() {
    global $koneksi;

    if (!isset($_SESSION["ssLoginPOS"])) {
        return null;
    }

    $userActive = $_SESSION["ssLoginPOS"]["username"];
    $result = mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE username = '$userActive'");

    if (mysqli_num_rows($result) === 0) {
        return null;
    }

    return mysqli_fetch_assoc($result);
}



function userMenu(){
    $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', $uri_path);
    $menu = $uri_segments[2];
    return $menu;
}

function menuHome(){
    if (userMenu() == 'dashboard.php'){
        return 'active';
    }else{
        return null;
    }
}

function menuSetting(){
    {
       if (userMenu() == 'user'){
        $result = 'menu-is-opening menu-open';
    } else {
        $result = null;
    }
    return $result;
}
}

function menuMaster() {
    if (userMenu() == 'supplier' || userMenu() == 'pelanggan' || userMenu() == 'barang') {
        $result = 'menu-is-opening menu-open';
    } else {
        $result = null;
    }
    return $result;
}


function menuUser(){
    if (userMenu() == 'user'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}
function menuSupplier(){
    if (userMenu() == 'supplier'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}

function menuPelanggan(){
    if (userMenu() == 'pelanggan'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}

function menuBarang(){
    if (userMenu() == 'barang'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}
function menuBeli(){
    if (userMenu() == 'pembelian'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}
function menuJual(){
    if (userMenu() == 'penjualan'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}
function laporanBeli(){
    if (userMenu() == 'laporan-pembelian'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}
function laporanJual(){
    if (userMenu() == 'laporan-penjualan'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}
function LaporanStock(){
    if (userMenu() == 'stock'){
        $result = 'active';
    }else{
        $result = null;
    }
    return $result;
}

function in_date($tgl){
    $tg = substr($tgl, 8, 2);
    $bl = substr($tgl, 5, 2);
    $thn = substr($tgl, 0, 4);

    return $tg . '-' . $bl . '-' . $thn;
}

function omzet(){
    global $koneksi;
    $queryOmzet = mysqli_query($koneksi, "SELECT SUM(total) as omzet FROM tbl_jual_head");
    $data = mysqli_fetch_assoc($queryOmzet);
    $omzet = number_format($data['omzet'],0,',','.');
    
    return $omzet;
}

?>