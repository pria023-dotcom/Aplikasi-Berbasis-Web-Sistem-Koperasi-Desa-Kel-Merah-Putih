<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'kprmp');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        // Output friendly error message guide
        echo "<div style='font-family: sans-serif; padding: 30px; background: #fff5f5; border: 1px solid #ffc9c9; border-radius: 8px; max-width: 600px; margin: 50px auto;'>";
        echo "<h2 style='color:#c92a2a; margin-top:0;'>Koneksi Database Gagal</h2>";
        echo "<p>Tidak dapat terhubung ke database MySQL <strong>'" . DB_NAME . "'</strong>.</p>";
        echo "<p><strong>Langkah Perbaikan:</strong></p>";
        echo "<ol style='line-height:1.6;'>";
        echo "<li>Pastikan service <strong>MySQL (MariaDB)</strong> sudah aktif di XAMPP Control Panel.</li>";
        echo "<li>Buka <strong>phpMyAdmin</strong> (<a href='http://localhost/phpmyadmin' target='_blank'>localhost/phpmyadmin</a>).</li>";
        echo "<li>Buat database baru bernama <strong>kprmp</strong>.</li>";
        echo "<li>Pilih database <strong>kprmp</strong>, lalu klik tab <strong>Import</strong> dan pilih file <strong><code>database/kprmp.sql</code></strong> dari folder proyek ini.</li>";
        echo "<li>Refresh halaman ini setelah impor selesai.</li>";
        echo "</ol>";
        echo "<p style='color:#8D949E; font-size:12px;'>Detail error: " . $e->getMessage() . "</p>";
        echo "</div>";
        exit;
    }
}

function initializeDatabase() {
    // Database creation is handled via manual phpMyAdmin import.
    // This is a placeholder to prevent code breaking in other files.
}

