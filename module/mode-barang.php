 <?php
if (userLogin()['level'] == 2){
    header("location:" .$main_url."error-page.php");
    exit();
}

function generateId(){
    global $koneksi;

    $queryId = mysqli_query($koneksi,"SELECT max(id_barang) as maxid FROM tbl_barang");
    $data = mysqli_fetch_array($queryId);
    $maxid = $data['maxid'];

    $noUrut = (int) substr($maxid, 4, 3);
    $noUrut++;
    $maxid = "BRG-" . sprintf("%03s", $noUrut);
    return $maxid;
}

function insert($data){
    global $koneksi;

    $id   = mysqli_real_escape_string($koneksi,$data['kode']);
    $name   = mysqli_real_escape_string($koneksi,$data['name']);
    $satuan = mysqli_real_escape_string ($koneksi,$data['satuan']);
    $harga_beli = mysqli_real_escape_string ($koneksi,$data['harga_beli']);
    $harga_jual = mysqli_real_escape_string ($koneksi,$data['harga_jual']);
    $stockmin = mysqli_real_escape_string ($koneksi,$data['stock_minimal']);

    $sqlBrg = "INSERT INTO tbl_barang VALUES ('$id', '$name', $harga_beli, $harga_jual, 0, '$satuan', $stockmin)";

    mysqli_query($koneksi, $sqlBrg);

    return mysqli_affected_rows($koneksi);
}

function delete($id)
{
    global $koneksi;

    $sqlDel = "DELETE FROM tbl_barang WHERE id_barang = '$id'";
    mysqli_query($koneksi, $sqlDel);

    return mysqli_affected_rows($koneksi);
}

function update($data)
{
    global $koneksi;

    $id   = mysqli_real_escape_string($koneksi,$data['kode']);
    $name   = mysqli_real_escape_string($koneksi,$data['name']);
    $satuan = mysqli_real_escape_string ($koneksi,$data['satuan']);
    $harga_beli = mysqli_real_escape_string ($koneksi,$data['harga_beli']);
    $harga_jual = mysqli_real_escape_string ($koneksi,$data['harga_jual']);
    $stockmin = mysqli_real_escape_string ($koneksi,$data['stock_minimal']);
    
    //cek username sekarang
    mysqli_query ($koneksi, "UPDATE tbl_barang SET 
        nama_barang = '$name',
        satuan = '$satuan',
        harga_beli = '$harga_beli',
        harga_jual = '$harga_jual',
        stock_minimal = '$stockmin'
        WHERE id_barang = '$id'");
    

    return mysqli_affected_rows($koneksi);
}

?>
