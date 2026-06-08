<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$name = $_SESSION['name'] ?? 'Guest';
$initials = strtoupper(substr($name, 0, 2));
?>
<header class="main-header">
    <div class="header-title-section">
        <h1 id="current-page-title">Beranda</h1>
        <p id="current-page-subtitle">Koperasi Desa/Kelurahan Merah Putih - Layanan Terintegrasi & Modern</p>
    </div>
    
    <div class="header-actions">
        <div class="live-time-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span id="live-clock">Senin, 25 Mei 2026 | 00:00:00</span>
        </div>
        
        <div class="avatar" title="<?= htmlspecialchars($name) ?>" style="text-transform: uppercase;">
            <?= $initials ?>
        </div>
        
        <a href="logout.php" class="btn-secondary" style="padding: 8px 14px; gap: 6px; border-radius: 8px; font-size: 0.8rem; border-color: rgba(240, 62, 62, 0.2); color: var(--danger); background: var(--danger-light);">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Keluar
        </a>
    </div>
</header>
