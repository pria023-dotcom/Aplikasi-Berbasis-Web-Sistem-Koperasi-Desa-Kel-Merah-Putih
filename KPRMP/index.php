<?php
// index.php
session_start();
require_once 'config/database.php';

// 1. SESSION CHECK (Redirect if not logged in)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['role'] ?? 'anggota';
$userMemberId = $_SESSION['member_id'] ?? null;
$userName = $_SESSION['name'] ?? 'User';
$pdo = getDBConnection();

// ==========================================
// 2. DATA FETCHING WITH ROLE FILTERING
// ==========================================

// Dashboard Metrics
$totalMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'Aktif'")->fetchColumn();
$totalGoods = $pdo->query("SELECT COUNT(*) FROM retail_goods")->fetchColumn();
$totalWarehouseQty = $pdo->query("SELECT SUM(quantity) FROM warehouse")->fetchColumn() ?? 0;
$totalCapital = $pdo->query("SELECT SUM(amount) FROM capital")->fetchColumn() ?? 0;
$totalSAL = $pdo->query("SELECT SUM(amount) FROM sal_funding")->fetchColumn() ?? 0;
$totalGuarantees = $pdo->query("SELECT SUM(amount) FROM village_guarantees WHERE status = 'Aktif'")->fetchColumn() ?? 0;

// Savings & Loans Metrics (Personalized for Anggota, Total for Admin/Staff)
if ($userRole === 'anggota') {
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM savings_loans WHERE member_id = ? AND type IN ('Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela') AND status IN ('Aktif', 'Disetujui')");
    $stmt->execute([$userMemberId]);
    $totalSavings = $stmt->fetchColumn() ?? 0;

    $stmt = $pdo->prepare("SELECT SUM(amount) FROM savings_loans WHERE member_id = ? AND type = 'Pinjaman' AND status IN ('Aktif', 'Disetujui')");
    $stmt->execute([$userMemberId]);
    $totalLoans = $stmt->fetchColumn() ?? 0;
} else {
    $totalSavings = $pdo->query("SELECT SUM(amount) FROM savings_loans WHERE type IN ('Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela') AND status IN ('Aktif', 'Disetujui')")->fetchColumn() ?? 0;
    $totalLoans = $pdo->query("SELECT SUM(amount) FROM savings_loans WHERE type = 'Pinjaman' AND status IN ('Aktif', 'Disetujui')")->fetchColumn() ?? 0;
}

// SHU Settings for 2026
$shuQuery = $pdo->query("SELECT * FROM shu_settings WHERE year = 2026");
$shuSettings = $shuQuery->fetch();
if (!$shuSettings) {
    // Insert default values if not exists
    $pdo->exec("INSERT INTO shu_settings (year, reserve_percentage, member_capital_percentage, member_transaction_percentage, education_percentage, board_percentage, total_income, total_expense) VALUES (2026, 20, 40, 30, 5, 5, 0, 0)");
    $shuSettings = $pdo->query("SELECT * FROM shu_settings WHERE year = 2026")->fetch();
}

$netShu = $shuSettings['total_income'] - $shuSettings['total_expense'];

// 3. Lists Querying with RBAC
$membersList = $pdo->query("SELECT * FROM members ORDER BY id DESC")->fetchAll();

// Branch filtering for Toko Cabang
$selectedBranch = $_GET['branch'] ?? 'Semua';
if ($selectedBranch === 'Semua') {
    $goodsList = $pdo->query("SELECT * FROM retail_goods ORDER BY id DESC")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM retail_goods WHERE branch = ? ORDER BY id DESC");
    $stmt->execute([$selectedBranch]);
    $goodsList = $stmt->fetchAll();
}

$logisticsList = $pdo->query("SELECT * FROM logistics ORDER BY id DESC")->fetchAll();
$warehouseList = $pdo->query("SELECT * FROM warehouse ORDER BY id DESC")->fetchAll();
$capitalList = $pdo->query("SELECT * FROM capital ORDER BY id DESC")->fetchAll();
$salList = $pdo->query("SELECT * FROM sal_funding ORDER BY id DESC")->fetchAll();
$guaranteeList = $pdo->query("SELECT * FROM village_guarantees ORDER BY id DESC")->fetchAll();
$infoList = $pdo->query("SELECT * FROM village_info ORDER BY id DESC")->fetchAll();

// Savings & Loans List (RBAC: Anggota sees only their own, admin/staff sees all)
if ($userRole === 'anggota') {
    $stmt = $pdo->prepare("SELECT sl.*, m.name as member_name, m.member_code 
                           FROM savings_loans sl 
                           LEFT JOIN members m ON sl.member_id = m.id 
                           WHERE sl.member_id = ?
                           ORDER BY sl.id DESC");
    $stmt->execute([$userMemberId]);
    $slList = $stmt->fetchAll();
} else {
    $slList = $pdo->query("SELECT sl.*, m.name as member_name, m.member_code 
                           FROM savings_loans sl 
                           LEFT JOIN members m ON sl.member_id = m.id 
                           ORDER BY sl.id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPRMP - Koperasi Desa/Kelurahan Merah Putih</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='%23C92A2A' d='M12 2L2 22h20L12 2z'/></svg>">
</head>
<body>

<div class="app-wrapper">
    <!-- Include Sidebar Navigation Drawer (Dynamic based on Role) -->
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-layout">
        <!-- Include Top Header -->
        <?php include 'includes/header.php'; ?>
        
        <main class="content-body">
            
            <!-- =================================================== -->
            <!-- 1. PANEL: DASHBOARD -->
            <!-- =================================================== -->
            <section id="dashboard" class="module-panel active">
                <div class="widget-grid">
                    <!-- Members -->
                    <div class="widget-card w-red">
                        <div class="widget-info">
                            <span class="widget-label">Anggota Aktif</span>
                            <span class="widget-value"><?= number_format($totalMembers) ?> Orang</span>
                            <span class="widget-subtext">Terdaftar di database</span>
                        </div>
                        <div class="widget-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        </div>
                    </div>
                    <!-- Retail stock -->
                    <div class="widget-card w-gold">
                        <div class="widget-info">
                            <span class="widget-label">Produk Ritel</span>
                            <span class="widget-value"><?= number_format($totalGoods) ?> Item</span>
                            <span class="widget-subtext">Tersedia di toko ritel</span>
                        </div>
                        <div class="widget-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line></svg>
                        </div>
                    </div>
                    <!-- Simpanan (Total vs Personal) -->
                    <div class="widget-card w-green">
                        <div class="widget-info">
                            <span class="widget-label"><?= $userRole === 'anggota' ? 'Simpanan Saya' : 'Total Simpanan' ?></span>
                            <span class="widget-value">Rp <?= number_format($totalSavings, 0, ',', '.') ?></span>
                            <span class="widget-subtext"><?= $userRole === 'anggota' ? 'Jumlah saldo tabungan Anda' : 'Dana simpanan seluruh anggota' ?></span>
                        </div>
                        <div class="widget-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8M8 12h8"></path></svg>
                        </div>
                    </div>
                    <!-- Pinjaman (Total vs Personal) -->
                    <div class="widget-card w-danger">
                        <div class="widget-info">
                            <span class="widget-label"><?= $userRole === 'anggota' ? 'Pinjaman Saya' : 'Pinjaman Aktif' ?></span>
                            <span class="widget-value">Rp <?= number_format($totalLoans, 0, ',', '.') ?></span>
                            <span class="widget-subtext"><?= $userRole === 'anggota' ? 'Sisa kredit terutang Anda' : 'Dana kredit beredar anggota' ?></span>
                        </div>
                        <div class="widget-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="dashboard-row">
                    <!-- Left: Financial Chart -->
                    <div class="card-frame">
                        <div class="card-frame-header">
                            <h3 class="card-frame-title">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                Analisis Arus Kas & Finansial Koperasi (2026)
                            </h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="financialChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Right: Subsystem shortcuts / Quick Info -->
                    <div class="card-frame">
                        <div class="card-frame-header">
                            <h3 class="card-frame-title">Status Usaha Koperasi</h3>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:15px;">
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-light); padding-bottom:10px;">
                                <span style="font-weight:600;">Stok Pertanian Gudang:</span>
                                <span class="badge b-info"><?= number_format($totalWarehouseQty, 1) ?> Ton</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-light); padding-bottom:10px;">
                                <span style="font-weight:600;">Modal Koperasi:</span>
                                <span style="color:var(--success); font-weight:700;">Rp <?= number_format($totalCapital, 0, ',', '.') ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-light); padding-bottom:10px;">
                                <span style="font-weight:600;">Alokasi Pendanaan SAL:</span>
                                <span>Rp <?= number_format($totalSAL, 0, ',', '.') ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-light); padding-bottom:10px;">
                                <span style="font-weight:600;">Jaminan Dana Desa:</span>
                                <span class="badge b-warning">Rp <?= number_format($totalGuarantees, 0, ',', '.') ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding-bottom:5px;">
                                <span style="font-weight:600;">Sisa Hasil Usaha (SHU) Bersih:</span>
                                <span style="color:var(--primary); font-weight:700;">Rp <?= number_format($netShu, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- =================================================== -->
            <!-- 2. PANEL: KEANGGOTAAN -->
            <!-- =================================================== -->
            <section id="keanggotaan" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Daftar Keanggotaan Koperasi</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('keanggotaan')">
                            + Tambah Anggota
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Lengkap</th>
                                    <th>NIK</th>
                                    <th>No Telepon</th>
                                    <th>Alamat</th>
                                    <th>Tgl Bergabung</th>
                                    <th>Status</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($membersList as $m): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($m['member_code']) ?></td>
                                    <td><?= htmlspecialchars($m['name']) ?></td>
                                    <td><?= htmlspecialchars($m['nik']) ?></td>
                                    <td><?= htmlspecialchars($m['phone']) ?></td>
                                    <td><?= htmlspecialchars($m['address']) ?></td>
                                    <td><?= htmlspecialchars($m['join_date']) ?></td>
                                    <td>
                                        <span class="badge <?= $m['status'] == 'Aktif' ? 'b-success' : 'b-grey' ?>">
                                            <?= htmlspecialchars($m['status']) ?>
                                        </span>
                                    </td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <button class="btn-icon bi-edit" onclick="editMember(<?= htmlspecialchars(json_encode($m)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('members', <?= $m['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 3. PANEL: UNIT USAHA RITEL -->
            <!-- =================================================== -->
            <section id="ritel" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Inventaris Barang Toko Ritel</h3>
                        
                        <div style="display:flex; gap:12px; align-items:center;">
                            <label style="font-size:0.85rem; font-weight:600; color:var(--text-secondary);">Lokasi/Cabang:</label>
                            <select class="form-control" style="width:160px; padding:6px 10px;" onchange="window.location.href = '?branch=' + encodeURIComponent(this.value) + '#ritel'">
                                <option value="Semua" <?= $selectedBranch === 'Semua' ? 'selected' : '' ?>>Semua Lokasi</option>
                                <option value="Pusat" <?= $selectedBranch === 'Pusat' ? 'selected' : '' ?>>Toko Pusat</option>
                                <option value="Cabang Dusun I" <?= $selectedBranch === 'Cabang Dusun I' ? 'selected' : '' ?>>Cabang Dusun I</option>
                                <option value="Cabang Dusun II" <?= $selectedBranch === 'Cabang Dusun II' ? 'selected' : '' ?>>Cabang Dusun II</option>
                            </select>
                            
                            <?php if ($userRole !== 'anggota'): ?>
                            <button class="btn-primary" onclick="openAddModal('ritel')">
                                + Tambah Barang
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Pemasok</th>
                                    <th>Cabang</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($goodsList as $g): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($g['code']) ?></td>
                                    <td><?= htmlspecialchars($g['name']) ?></td>
                                    <td><?= htmlspecialchars($g['category']) ?></td>
                                    <td>
                                        <span class="badge <?= $g['stock'] > 50 ? 'b-success' : ($g['stock'] > 10 ? 'b-warning' : 'b-danger') ?>">
                                            <?= htmlspecialchars($g['stock']) ?> pcs
                                        </span>
                                    </td>
                                    <td>Rp <?= number_format($g['buy_price'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($g['sell_price'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($g['supplier']) ?></td>
                                    <td>
                                        <span class="badge b-info" style="font-weight:600;"><?= htmlspecialchars($g['branch'] ?? 'Pusat') ?></span>
                                    </td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <button class="btn-icon bi-edit" title="Audit / Update Stok Cepat" style="background:rgba(12, 166, 120, 0.08); color:var(--success);" onclick="auditStock(<?= $g['id'] ?>, <?= $g['stock'] ?>, '<?= htmlspecialchars($g['name'], ENT_QUOTES) ?>')">🔄</button>
                                        <button class="btn-icon bi-edit" onclick="editRetail(<?= htmlspecialchars(json_encode($g)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('retail_goods', <?= $g['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 4. PANEL: LOGISTIK -->
            <!-- =================================================== -->
            <section id="logistik" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Pelacakan Logistik Pengiriman</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('logistik')">
                            + Tambah Pengiriman
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>No Resi/Pelacakan</th>
                                    <th>Muatan Barang</th>
                                    <th>Pengirim</th>
                                    <th>Penerima</th>
                                    <th>Tgl Kirim</th>
                                    <th>Status</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($logisticsList as $l): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($l['tracking_number']) ?></td>
                                    <td><?= htmlspecialchars($l['cargo']) ?></td>
                                    <td><?= htmlspecialchars($l['sender']) ?></td>
                                    <td><?= htmlspecialchars($l['receiver']) ?></td>
                                    <td><?= htmlspecialchars($l['ship_date']) ?></td>
                                    <td>
                                        <span class="badge <?= $l['status'] == 'Diterima' ? 'b-success' : ($l['status'] == 'Dalam Perjalanan' ? 'b-info' : ($l['status'] == 'Diproses' ? 'b-warning' : 'b-grey')) ?>">
                                            <?= htmlspecialchars($l['status']) ?>
                                        </span>
                                    </td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <button class="btn-icon bi-edit" onclick="editLogistics(<?= htmlspecialchars(json_encode($l)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('logistics', <?= $l['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 5. PANEL: GUDANG & HASIL PERTANIAN -->
            <!-- =================================================== -->
            <section id="gudang" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Stok Gudang Pertanian Desa</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('gudang')">
                            + Catat Hasil Pertanian
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Komoditas Hasil Tani</th>
                                    <th>Jumlah (Berat)</th>
                                    <th>Grade/Kualitas</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Lokasi Penyimpanan</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($warehouseList as $w): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($w['commodity']) ?></td>
                                    <td><?= htmlspecialchars($w['quantity']) ?> Ton</td>
                                    <td>
                                        <span class="badge <?= $w['grade'] == 'A' ? 'b-success' : ($w['grade'] == 'B' ? 'b-info' : 'b-warning') ?>">
                                            Grade <?= htmlspecialchars($w['grade']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($w['incoming_date']) ?></td>
                                    <td><?= htmlspecialchars($w['warehouse_location']) ?></td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <button class="btn-icon bi-edit" onclick="editWarehouse(<?= htmlspecialchars(json_encode($w)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('warehouse', <?= $w['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 6. PANEL: PERMODALAN -->
            <!-- =================================================== -->
            <section id="permodalan" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Pencatatan Permodalan Koperasi</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('permodalan')">
                            + Tambah Modal
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Sumber Modal</th>
                                    <th>Jumlah Modal</th>
                                    <th>Tanggal Penerimaan</th>
                                    <th>Keterangan</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($capitalList as $c): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($c['source']) ?></td>
                                    <td style="color:var(--success); font-weight:600;">Rp <?= number_format($c['amount'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($c['date']) ?></td>
                                    <td><?= htmlspecialchars($c['description']) ?></td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <button class="btn-icon bi-edit" onclick="editCapital(<?= htmlspecialchars(json_encode($c)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('capital', <?= $c['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 7. PANEL: PENDANAAN SAL -->
            <!-- =================================================== -->
            <section id="pendanaan_sal" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Penyaluran Pendanaan SAL</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('pendanaan_sal')">
                            + Tambah Penyaluran
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Tujuan / Program Alokasi</th>
                                    <th>Jumlah Pendanaan</th>
                                    <th>Tanggal Penyaluran</th>
                                    <th>Status Program</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($salList as $s): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($s['allocation_name']) ?></td>
                                    <td style="font-weight:600;">Rp <?= number_format($s['amount'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($s['disbursement_date']) ?></td>
                                    <td>
                                        <span class="badge <?= $s['status'] == 'Selesai' ? 'b-success' : ($s['status'] == 'Disalurkan' ? 'b-info' : 'b-warning') ?>">
                                            <?= htmlspecialchars($s['status']) ?>
                                        </span>
                                    </td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <button class="btn-icon bi-edit" onclick="editSal(<?= htmlspecialchars(json_encode($s)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('sal_funding', <?= $s['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 8. PANEL: JAMINAN DANA DESA -->
            <!-- =================================================== -->
            <section id="jaminan_desa" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Jaminan Proyek Dana Desa</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('jaminan_desa')">
                            + Tambah Jaminan Proyek
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Nama Proyek Jaminan</th>
                                    <th>Jumlah Nominal Penjaminan</th>
                                    <th>Tanggal Terbit Jaminan</th>
                                    <th>Status Jaminan</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($guaranteeList as $g): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($g['project_name']) ?></td>
                                    <td style="font-weight:600; color:var(--primary)">Rp <?= number_format($g['amount'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($g['guarantee_date']) ?></td>
                                    <td>
                                        <span class="badge <?= $g['status'] == 'Selesai' ? 'b-success' : ($g['status'] == 'Aktif' ? 'b-warning' : 'b-danger') ?>">
                                            <?= htmlspecialchars($g['status']) ?>
                                        </span>
                                    </td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <button class="btn-icon bi-edit" onclick="editGuarantee(<?= htmlspecialchars(json_encode($g)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('village_guarantees', <?= $g['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 9. PANEL: SIMPAN PINJAM ANGGOTA -->
            <!-- =================================================== -->
            <section id="simpan_pinjam" class="module-panel">
                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Transaksi Simpan Pinjam</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('simpan_pinjam')">
                            + Transaksi Baru
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Kode Anggota</th>
                                    <th>Nama Anggota</th>
                                    <th>Jenis Transaksi</th>
                                    <th>Jumlah Nominal</th>
                                    <th>Bunga</th>
                                    <th>Tenor</th>
                                    <th>Status</th>
                                    <?php if ($userRole !== 'anggota'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($slList as $sl): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($sl['member_code'] ?? 'KPR-DELETED') ?></td>
                                    <td><?= htmlspecialchars($sl['member_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge <?= strpos($sl['type'], 'Simpanan') !== false ? 'b-info' : 'b-danger' ?>">
                                            <?= htmlspecialchars($sl['type']) ?>
                                        </span>
                                    </td>
                                    <td style="font-weight:600;">Rp <?= number_format($sl['amount'], 0, ',', '.') ?></td>
                                    <td><?= $sl['type'] == 'Pinjaman' ? $sl['interest_rate'] . '%' : '-' ?></td>
                                    <td><?= $sl['type'] == 'Pinjaman' ? $sl['tenor'] . ' bln' : '-' ?></td>
                                    <td>
                                        <span class="badge <?= $sl['status'] == 'Aktif' || $sl['status'] == 'Disetujui' || $sl['status'] == 'Lunas' ? 'b-success' : ($sl['status'] == 'Menunggu' ? 'b-warning' : 'b-danger') ?>">
                                            <?= htmlspecialchars($sl['status']) ?>
                                        </span>
                                    </td>
                                    <?php if ($userRole !== 'anggota'): ?>
                                    <td>
                                        <?php if ($sl['status'] == 'Menunggu'): ?>
                                            <button class="btn-primary" style="padding: 5px 10px; font-size:0.75rem; box-shadow:none; background:var(--success)" onclick="approveLoan(<?= $sl['id'] ?>)">Setujui</button>
                                        <?php endif; ?>
                                        <button class="btn-icon bi-edit" onclick="editSimpanPinjam(<?= htmlspecialchars(json_encode($sl)) ?>)">✏️</button>
                                        <button class="btn-icon bi-delete" onclick="deleteRecord('savings_loans', <?= $sl['id'] ?>)">🗑️</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 10. PANEL: SISA HASIL USAHA (SHU) OTOMATIS -->
            <!-- =================================================== -->
            <section id="shu" class="module-panel">
                <div class="dashboard-row">
                    <!-- Left: SHU Configuration & Calculation (Hidden for Members, only shown for admins/supervisors/staff) -->
                    <?php if ($userRole !== 'anggota'): ?>
                    <div class="card-frame">
                        <div class="card-frame-header">
                            <h3 class="card-frame-title">Konfigurasi Pembagian SHU Buku 2026</h3>
                        </div>
                        <form id="form-shu" class="grid-form" onsubmit="saveShuSettings(event)">
                            <input type="hidden" name="action" value="update_shu_settings">
                            
                            <div class="form-group">
                                <label>Total Pendapatan Operasional Koperasi (Rp)</label>
                                <input type="number" class="form-control" name="total_income" value="<?= $shuSettings['total_income'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Total Beban Operasional Koperasi (Rp)</label>
                                <input type="number" class="form-control" name="total_expense" value="<?= $shuSettings['total_expense'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Dana Cadangan Koperasi (%)</label>
                                <input type="number" class="form-control" name="reserve_percentage" value="<?= $shuSettings['reserve_percentage'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Jasa Modal Anggota (%)</label>
                                <input type="number" class="form-control" name="member_capital_percentage" value="<?= $shuSettings['member_capital_percentage'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Jasa Transaksi Usaha Anggota (%)</label>
                                <input type="number" class="form-control" name="member_transaction_percentage" value="<?= $shuSettings['member_transaction_percentage'] ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Dana Pendidikan (%)</label>
                                <input type="number" class="form-control" name="education_percentage" value="<?= $shuSettings['education_percentage'] ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Dana Pengurus & Pengawas (%)</label>
                                <input type="number" class="form-control" name="board_percentage" value="<?= $shuSettings['board_percentage'] ?>" required>
                            </div>

                            <div class="form-group" style="justify-content: flex-end; display: flex; align-items: flex-end; grid-column: span 1;">
                                <button type="submit" class="btn-primary" style="width: 100%">Simpan & Hitung</button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <!-- Member Info Banner -->
                    <div class="card-frame" style="background: rgba(12, 166, 120, 0.03); border-color: rgba(12, 166, 120, 0.15); display: flex; flex-direction: column; justify-content: center; padding: 30px;">
                        <h3 style="font-family: var(--font-heading); color: var(--success); font-size: 1.3rem; font-weight: 700; margin-bottom:10px;">Transparansi SHU Anggota</h3>
                        <p style="font-size:0.9rem; color: var(--text-secondary); line-height: 1.6;">
                            Koperasi Desa/Kelurahan Merah Putih menerapkan azas kekeluargaan secara transparan. Hak Sisa Hasil Usaha (SHU) Anda di bawah ini dihitung secara adil berdasarkan proporsi total simpanan Anda (Jasa Modal) serta kontribusi transaksi aktif pinjaman Anda (Jasa Usaha) di koperasi.
                        </p>
                        <div style="margin-top: 15px; font-size:0.8rem; color:var(--text-light);">
                            *Rumus Perhitungan: (Simpanan Anda / Total Simpanan Koperasi) * Jasa Modal Pool + (Pinjaman Anda / Total Pinjaman Koperasi) * Jasa Usaha Pool.
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Right: Quick SHU Distribution breakdown (Visible to everyone for transparency) -->
                    <div class="card-frame">
                        <div class="card-frame-header">
                            <h3 class="card-frame-title">Distribusi Alokasi SHU</h3>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div style="display:flex; justify-content:space-between;">
                                <span style="font-weight:600;">SHU Bersih (Net):</span>
                                <span style="color:var(--primary); font-weight:700;">Rp <?= number_format($netShu, 0, ',', '.') ?></span>
                            </div>
                            <hr style="border:none; border-top:1px solid var(--border-light)">
                            <div style="display:flex; justify-content:space-between;">
                                <span>Dana Cadangan (<?= $shuSettings['reserve_percentage'] ?>%):</span>
                                <span>Rp <?= number_format($netShu * ($shuSettings['reserve_percentage']/100), 0, ',', '.') ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Jasa Modal (<?= $shuSettings['member_capital_percentage'] ?>%):</span>
                                <span>Rp <?= number_format($netShu * ($shuSettings['member_capital_percentage']/100), 0, ',', '.') ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Jasa Usaha (<?= $shuSettings['member_transaction_percentage'] ?>%):</span>
                                <span>Rp <?= number_format($netShu * ($shuSettings['member_transaction_percentage']/100), 0, ',', '.') ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Dana Pendidikan (<?= $shuSettings['education_percentage'] ?>%):</span>
                                <span>Rp <?= number_format($netShu * ($shuSettings['education_percentage']/100), 0, ',', '.') ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Pengurus & Pengawas (<?= $shuSettings['board_percentage'] ?>%):</span>
                                <span>Rp <?= number_format($netShu * ($shuSettings['board_percentage']/100), 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-frame">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Perhitungan SHU Proporsional Anggota</h3>
                    </div>
                    
                    <div class="table-responsive-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Kode Anggota</th>
                                    <th>Nama Anggota</th>
                                    <th>Total Simpanan</th>
                                    <th>Jasa Modal</th>
                                    <th>Total Pinjaman Aktif</th>
                                    <th>Jasa Usaha</th>
                                    <th>Total Hak SHU</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // 1. Calculate overall sums
                                $sumSavings = $pdo->query("SELECT SUM(amount) FROM savings_loans WHERE type IN ('Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela') AND status IN ('Aktif', 'Disetujui')")->fetchColumn() ?? 1; // avoid div by 0
                                $sumLoans = $pdo->query("SELECT SUM(amount) FROM savings_loans WHERE type = 'Pinjaman' AND status IN ('Aktif', 'Disetujui')")->fetchColumn() ?? 1;

                                // Jasa Modal Pool
                                $jasaModalPool = $netShu * ($shuSettings['member_capital_percentage'] / 100);
                                // Jasa Usaha Pool
                                $jasaUsahaPool = $netShu * ($shuSettings['member_transaction_percentage'] / 100);

                                // Filter activeMembers for anggota role
                                if ($userRole === 'anggota') {
                                    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
                                    $stmt->execute([$userMemberId]);
                                    $activeMembers = $stmt->fetchAll();
                                } else {
                                    $activeMembers = $pdo->query("SELECT * FROM members WHERE status = 'Aktif'")->fetchAll();
                                }

                                foreach ($activeMembers as $am):
                                    // Calculate member's savings
                                    $stmt = $pdo->prepare("SELECT SUM(amount) FROM savings_loans WHERE member_id = ? AND type IN ('Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela') AND status IN ('Aktif', 'Disetujui')");
                                    $stmt->execute([$am['id']]);
                                    $memberSavings = $stmt->fetchColumn() ?? 0;

                                    // Calculate member's loans
                                    $stmt = $pdo->prepare("SELECT SUM(amount) FROM savings_loans WHERE member_id = ? AND type = 'Pinjaman' AND status IN ('Aktif', 'Disetujui')");
                                    $stmt->execute([$am['id']]);
                                    $memberLoans = $stmt->fetchColumn() ?? 0;

                                    // Proportions
                                    $capitalShare = ($memberSavings / $sumSavings) * $jasaModalPool;
                                    $transactionShare = ($memberLoans / $sumLoans) * $jasaUsahaPool;
                                    $totalMemberShu = $capitalShare + $transactionShare;
                                ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($am['member_code']) ?></td>
                                    <td><?= htmlspecialchars($am['name']) ?></td>
                                    <td>Rp <?= number_format($memberSavings, 0, ',', '.') ?></td>
                                    <td style="color:var(--info); font-weight:600;">Rp <?= number_format($capitalShare, 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($memberLoans, 0, ',', '.') ?></td>
                                    <td style="color:var(--info); font-weight:600;">Rp <?= number_format($transactionShare, 0, ',', '.') ?></td>
                                    <td style="color:var(--primary); font-weight:700; font-size:1rem;">Rp <?= number_format($totalMemberShu, 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- =================================================== -->
            <!-- 11. PANEL: SISTEM INFORMASI DESA -->
            <!-- =================================================== -->
            <section id="info_desa" class="module-panel">
                <div class="card-frame" style="margin-bottom: 25px;">
                    <div class="card-frame-header">
                        <h3 class="card-frame-title">Sistem Informasi & Pengumuman Desa</h3>
                        <?php if ($userRole !== 'anggota'): ?>
                        <button class="btn-primary" onclick="openAddModal('info_desa')">
                            + Tambah Publikasi Info
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-grid">
                    <?php foreach ($infoList as $info): ?>
                    <div class="info-card">
                        <div class="info-card-meta">
                            <span class="badge b-info"><?= htmlspecialchars($info['category']) ?></span>
                            <span>📅 <?= htmlspecialchars($info['published_at']) ?></span>
                        </div>
                        <h4 class="info-card-title"><?= htmlspecialchars($info['title']) ?></h4>
                        <p class="info-card-content"><?= nl2br(htmlspecialchars($info['content'])) ?></p>
                        
                        <?php if ($userRole !== 'anggota'): ?>
                        <div class="info-card-actions">
                            <button class="btn-icon bi-edit" onclick="editInfo(<?= htmlspecialchars(json_encode($info)) ?>)">✏️</button>
                            <button class="btn-icon bi-delete" onclick="deleteRecord('village_info', <?= $info['id'] ?>)">🗑️</button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </main>
    </div> <!-- Close main-layout -->
</div> <!-- Close app-wrapper -->


<!-- =================================================== -->
<!-- MODAL WINDOWS FOR CRUD OPERATIONS -->
<!-- =================================================== -->

<div class="modal-overlay" id="crud-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="modal-title">Form Tambah Data</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="modal-form" onsubmit="submitForm(event)">
                <!-- Action & ID -->
                <input type="hidden" name="action" id="form-action" value="">
                <input type="hidden" name="id" id="form-id" value="">
                
                <!-- Dynamic Fields Container -->
                <div id="dynamic-fields-container" class="grid-form">
                    <!-- Loaded dynamically via JavaScript based on the current module -->
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal()">Batal</button>
            <button type="submit" form="modal-form" class="btn-primary">Simpan Data</button>
        </div>
    </div>
</div>

<!-- Scripts CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom Scripts -->
<script src="assets/js/main.js"></script>
<script src="assets/js/charts.js"></script>
</body>
</html>
