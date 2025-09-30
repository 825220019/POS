<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="<?= $main_url ?>dashboard.php" class="brand-link">
    <img src="<?= $main_url ?>asset/image/pos.jpg" alt="Logo" class="brand-image img-circle elevation-3"
      style="opacity: .8">
    <span class="brand-text font-weight-light">CAngelline POS</span>
  </a>
<div class="sidebar">
  <!-- Sidebar Menu -->
  <div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
        <li class="nav-item">
          <a href="<?= $main_url ?>dashboard.php" class="nav-link <?= menuHome() ?>">
            <i class="nav-icon fas fa-tachometer-alt text-sm"></i>
            <p>
              Dashboard
            </p>
          </a>
        </li>
        <?php
        if (userLogin()['level'] != 2) {
          ?>
          <li class="nav-item <?= menuMaster() ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-folder text-sm"></i>
              <p>
                Master
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= $main_url ?>supplier/data-supplier.php" class="nav-link <?= menuSupplier() ?>">
                  <i class="far fa-circle nav-icon text-sm"></i>
                  <p>Supplier</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= $main_url ?>pelanggan/data-pelanggan.php" class="nav-link <?= menuPelanggan() ?>">
                  <i class="far fa-circle nav-icon text-sm"></i>
                  <p>Pelanggan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= $main_url ?>barang/index.php" class="nav-link <?= menuBarang() ?>">
                  <i class="far fa-circle nav-icon text-sm"></i>
                  <p>Barang</p>
                </a>
              </li>
            </ul>
          </li>
        <?php } ?>
        <li class="nav-header">Transaksi</li>
        <li class="nav-item">
          <a href="<?= $main_url ?>pembelian/index.php" class="nav-link">
            <i class="nav-icon fas fa-shopping-cart text-sm"></i>
            <p>
              Pembelian
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-file-invoice text-sm"></i>
            <p>
              Penjualan
            </p>
          </a>
        </li>
        <li class="nav-header">Report</li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-chart-pie text-sm"></i>
            <p>Laporan Pembelian</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-chart-line text-sm"></i>
            <p>Laporan Penjualan</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-warehouse text-sm"></i>
            <p>
              Laporan Stock
            </p>
          </a>
        </li>
        <?php
        if (userLogin()['level'] == 1) {
          ?>
          <li class="nav-item <?= menuSetting() ?>">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-folder text-sm"></i>
              <p>
                Pengaturan
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= $main_url ?>user/data-user.php" class="nav-link <?= menuUser() ?>">
                  <i class="far fa-circle nav-icon text-sm"></i>
                  <p>Users</p>
                </a>
              </li>
            </ul>
          </li>
        <?php } ?>
      </ul>
    </nav>
  </div>
  </div>
</aside>