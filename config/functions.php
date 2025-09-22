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

function userLogin(){
    $userActive = $_SESSION["ssUserPOS"];
    $dataUser = getData("SELECT * FROM tbl_user WHERE username =
    '$userActive'")[0];
    return $dataUser;
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

?>