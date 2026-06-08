<?php
// includes/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'anggota';
$name = $_SESSION['name'] ?? 'Guest';

// Helper to check if a role has access to a specific tab
function hasTabAccess($tab, $role) {
    $permissions = [
        'admin' => ['dashboard', 'keanggotaan', 'ritel', 'logistik', 'gudang', 'permodalan', 'pendanaan_sal', 'jaminan_desa', 'simpan_pinjam', 'shu', 'info_desa'],
        'pengawas' => ['dashboard', 'keanggotaan', 'ritel', 'logistik', 'gudang', 'permodalan', 'pendanaan_sal', 'jaminan_desa', 'simpan_pinjam', 'shu', 'info_desa'],
        'developer' => ['dashboard', 'keanggotaan', 'ritel', 'logistik', 'gudang', 'permodalan', 'pendanaan_sal', 'jaminan_desa', 'simpan_pinjam', 'shu', 'info_desa'],
        'ritel' => ['dashboard', 'ritel', 'logistik', 'info_desa'],
        'gudang' => ['dashboard', 'gudang', 'logistik', 'info_desa'],
        'anggota' => ['dashboard', 'simpan_pinjam', 'shu', 'info_desa']
    ];
    
    return in_array($tab, $permissions[$role] ?? []);
}

$roleLabels = [
    'admin' => 'Administrator',
    'pengawas' => 'Kepala Pengawas',
    'developer' => 'System Developer',
    'ritel' => 'Satuan Unit Ritel',
    'gudang' => 'Satuan Unit Gudang',
    'anggota' => 'Anggota Koperasi'
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-toggle" id="sidebar-toggle">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
        </svg>
    </div>
    
    <div class="sidebar-brand" style="padding: 16px 20px;">
        <img src="assets/images/logo.png" alt="Logo KPRMP" style="height: 40px; width: auto; object-fit: contain; flex-shrink: 0; transition: var(--transition); border-radius: 6px;">
        <div class="brand-info">
            <span class="brand-title">KPR-MP</span>
            <span class="brand-subtitle" style="font-size: 0.55rem; white-space: normal; line-height: 1.1; max-width: 170px;">Kopdeskel Merah Putih</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <?php if (hasTabAccess('dashboard', $role)): ?>
        <a href="#dashboard" class="nav-item active" data-tab="dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                <rect x="3" y="16" width="7" height="5" rx="1"></rect>
            </svg>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>
        
        <!-- Keanggotaan -->
        <?php if (hasTabAccess('keanggotaan', $role)): ?>
        <a href="#keanggotaan" class="nav-item" data-tab="keanggotaan">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Keanggotaan</span>
        </a>
        <?php endif; ?>
        
        <!-- Ritel -->
        <?php if (hasTabAccess('ritel', $role)): ?>
        <a href="#ritel" class="nav-item" data-tab="ritel">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span>Usaha Ritel</span>
        </a>
        <?php endif; ?>
        
        <!-- Logistik -->
        <?php if (hasTabAccess('logistik', $role)): ?>
        <a href="#logistik" class="nav-item" data-tab="logistik">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
            <span>Logistik</span>
        </a>
        <?php endif; ?>
        
        <!-- Gudang -->
        <?php if (hasTabAccess('gudang', $role)): ?>
        <a href="#gudang" class="nav-item" data-tab="gudang">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                <line x1="12" y1="11" x2="12" y2="17"></line>
                <line x1="9" y1="14" x2="15" y2="14"></line>
            </svg>
            <span>Gudang & Tani</span>
        </a>
        <?php endif; ?>
        
        <!-- Permodalan -->
        <?php if (hasTabAccess('permodalan', $role)): ?>
        <a href="#permodalan" class="nav-item" data-tab="permodalan">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                <line x1="6" y1="6" x2="6.01" y2="6"></line>
                <line x1="6" y1="18" x2="6.01" y2="18"></line>
            </svg>
            <span>Permodalan</span>
        </a>
        <?php endif; ?>
        
        <!-- Pendanaan SAL -->
        <?php if (hasTabAccess('pendanaan_sal', $role)): ?>
        <a href="#pendanaan_sal" class="nav-item" data-tab="pendanaan_sal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <span>Pendanaan SAL</span>
        </a>
        <?php endif; ?>
        
        <!-- Jaminan Desa -->
        <?php if (hasTabAccess('jaminan_desa', $role)): ?>
        <a href="#jaminan_desa" class="nav-item" data-tab="jaminan_desa">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <span>Jaminan Desa</span>
        </a>
        <?php endif; ?>
        
        <!-- Simpan Pinjam -->
        <?php if (hasTabAccess('simpan_pinjam', $role)): ?>
        <a href="#simpan_pinjam" class="nav-item" data-tab="simpan_pinjam">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                <circle cx="12" cy="12" r="10" stroke-dasharray="4 4"></circle>
            </svg>
            <span>Simpan Pinjam</span>
        </a>
        <?php endif; ?>
        
        <!-- SHU -->
        <?php if (hasTabAccess('shu', $role)): ?>
        <a href="#shu" class="nav-item" data-tab="shu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <span>Bagi Hasil SHU</span>
        </a>
        <?php endif; ?>
        
        <!-- Sistem Informasi Desa -->
        <?php if (hasTabAccess('info_desa', $role)): ?>
        <a href="#info_desa" class="nav-item" data-tab="info_desa">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <span>Info Desa</span>
        </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <div class="avatar" style="text-transform: uppercase;"><?= substr($name, 0, 2) ?></div>
        <div class="user-info">
            <span class="user-name" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; display: inline-block; white-space: nowrap;"><?= htmlspecialchars($name) ?></span>
            <span class="user-role"><?= $roleLabels[$role] ?? 'Anggota' ?></span>
        </div>
    </div>
</aside>
