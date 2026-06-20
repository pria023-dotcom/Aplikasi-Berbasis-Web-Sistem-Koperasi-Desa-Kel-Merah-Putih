-- Create database if not exists
CREATE DATABASE IF NOT EXISTS kprmp;
USE kprmp;

-- 1. members Table
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    join_date DATE NOT NULL,
    status ENUM('Aktif', 'Non-Aktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. retail_goods Table
CREATE TABLE IF NOT EXISTS retail_goods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    stock INT DEFAULT 0,
    buy_price DOUBLE DEFAULT 0,
    sell_price DOUBLE DEFAULT 0,
    supplier VARCHAR(100),
    branch VARCHAR(100) DEFAULT 'Pusat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. logistics Table
CREATE TABLE IF NOT EXISTS logistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL,
    cargo VARCHAR(255) NOT NULL,
    sender VARCHAR(100) NOT NULL,
    receiver VARCHAR(100) NOT NULL,
    status ENUM('Pending', 'Diproses', 'Dalam Perjalanan', 'Diterima') DEFAULT 'Pending',
    ship_date DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. warehouse Table
CREATE TABLE IF NOT EXISTS warehouse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commodity VARCHAR(100) NOT NULL,
    quantity DOUBLE DEFAULT 0,
    grade ENUM('A', 'B', 'C') DEFAULT 'A',
    incoming_date DATE NOT NULL,
    warehouse_location VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. capital Table
CREATE TABLE IF NOT EXISTS capital (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(100) NOT NULL,
    amount DOUBLE DEFAULT 0,
    date DATE NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. sal_funding Table
CREATE TABLE IF NOT EXISTS sal_funding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allocation_name VARCHAR(100) NOT NULL,
    amount DOUBLE DEFAULT 0,
    disbursement_date DATE NOT NULL,
    status ENUM('Direncanakan', 'Disalurkan', 'Selesai') DEFAULT 'Direncanakan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. village_guarantees Table
CREATE TABLE IF NOT EXISTS village_guarantees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL,
    amount DOUBLE DEFAULT 0,
    guarantee_date DATE NOT NULL,
    status ENUM('Aktif', 'Selesai', 'Batal') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. savings_loans Table
CREATE TABLE IF NOT EXISTS savings_loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela', 'Pinjaman'
    amount DOUBLE DEFAULT 0,
    interest_rate DOUBLE DEFAULT 0,
    tenor INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Aktif',
    created_at DATE NOT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. shu_settings Table
CREATE TABLE IF NOT EXISTS shu_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT UNIQUE NOT NULL,
    reserve_percentage DOUBLE DEFAULT 20,
    member_capital_percentage DOUBLE DEFAULT 40,
    member_transaction_percentage DOUBLE DEFAULT 30,
    education_percentage DOUBLE DEFAULT 5,
    board_percentage DOUBLE DEFAULT 5,
    total_income DOUBLE DEFAULT 0,
    total_expense DOUBLE DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. shu_history Table
CREATE TABLE IF NOT EXISTS shu_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    member_id INT NOT NULL,
    capital_share DOUBLE DEFAULT 0,
    transaction_share DOUBLE DEFAULT 0,
    total_shu DOUBLE DEFAULT 0,
    calculated_at DATETIME NOT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. village_info Table
CREATE TABLE IF NOT EXISTS village_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('Pengumuman', 'Berita', 'Kegiatan') DEFAULT 'Berita',
    published_at DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. users Table (NEW)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'pengawas', 'anggota', 'ritel', 'gudang', 'developer') NOT NULL,
    member_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 13. retail_sales Table
CREATE TABLE IF NOT EXISTS retail_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(50) UNIQUE NOT NULL,
    member_id INT NULL,
    total_amount DOUBLE DEFAULT 0,
    payment_amount DOUBLE DEFAULT 0,
    change_amount DOUBLE DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(100) NOT NULL,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. retail_sale_details Table
CREATE TABLE IF NOT EXISTS retail_sale_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    goods_id INT NOT NULL,
    quantity INT NOT NULL,
    price DOUBLE NOT NULL,
    subtotal DOUBLE NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES retail_sales(id) ON DELETE CASCADE,
    FOREIGN KEY (goods_id) REFERENCES retail_goods(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ===================================================
-- SEED INITIAL DATA
-- ===================================================

-- Seed Members
INSERT INTO members (id, member_code, name, nik, phone, address, join_date, status) VALUES
(1, 'KPR-0001', 'Budi Santoso', '3171012345670001', '081234567890', 'RT 01 RW 02, Desa Merah Putih', '2024-01-15', 'Aktif'),
(2, 'KPR-0002', 'Siti Aminah', '3171012345670002', '081298765432', 'RT 03 RW 02, Desa Merah Putih', '2024-02-10', 'Aktif'),
(3, 'KPR-0003', 'Joko Widodo', '3171012345670003', '081345678901', 'RT 02 RW 01, Desa Merah Putih', '2024-03-01', 'Aktif'),
(4, 'KPR-0004', 'Dewi Lestari', '3171012345670004', '081456789012', 'RT 04 RW 03, Desa Merah Putih', '2024-03-12', 'Aktif'),
(5, 'KPR-0005', 'Ahmad Fauzi', '3171012345670005', '081567890123', 'RT 01 RW 01, Desa Merah Putih', '2024-04-05', 'Non-Aktif');

-- Seed Users (Passwords are crypted with default Bcrypt for corresponding username + '123'. E.g. 'admin123', 'pengawas123')
INSERT INTO users (id, username, password, name, role, member_id) VALUES
(1, 'admin', '$2y$10$N4H5fG0ywDMpNfe67tvvhurtA3eMZDqRet9F2HQtlFHKitj5enX3S', 'Admin Koperasi', 'admin', NULL),
(2, 'pengawas', '$2y$10$URo2bFWLz0CtOPaWPJB0SunNNZ56zk.nJex/7c2nSp.TVqS1kAvd.', 'Bapak Suprapto (Kepala Pengawas)', 'pengawas', NULL),
(3, 'budi', '$2y$10$zCl0QgyLqI8xh5Qmos5iAOPxdv7bmddIxHw2U3/UR30Czgg4DBNZG', 'Budi Santoso', 'anggota', 1),
(4, 'ritel', '$2y$10$c6ziW73ac6kgyHJOBoMXEuF1X1m8XRSU5KfsTdMdWdk1gOMHQlGE.', 'Staff Unit Ritel', 'ritel', NULL),
(5, 'gudang', '$2y$10$r/ktRw21dC2MGaJ31Bx2Q.cKoNx1T/Nj/zx14/q3tvcpwE7JMX3AG', 'Staff Unit Gudang', 'gudang', NULL),
(6, 'developer', '$2y$10$A3OUbw9QjeVFhDYF71gJB.Vl6oxJBng742pBvdhqi2yNbpJFKpw1e', 'Developer Utama', 'developer', NULL);

-- Seed Retail Goods (With branch division: Toko Pusat vs Toko Cabang Dusun I & II)
INSERT INTO retail_goods (id, code, name, category, stock, buy_price, sell_price, supplier, branch) VALUES
(1, 'BRG001', 'Pupuk Urea Subsidi 50kg', 'Pertanian', 150, 110000, 125000, 'PT Pupuk Sriwidjaja', 'Pusat'),
(2, 'BRG002', 'Bibit Padi Unggul Ciherang 5kg', 'Pertanian', 80, 55000, 65000, 'Balai Benih Tanaman', 'Pusat'),
(3, 'BRG003', 'Minyak Goreng Kita 1L', 'Sembako', 200, 13500, 15000, 'Distributor Sembako Jaya', 'Pusat'),
(4, 'BRG004', 'Beras Medium Desa 10kg', 'Sembako', 60, 115000, 130000, 'Gudang Tani KPRMP', 'Pusat'),
(5, 'BRG005', 'Pestisida Hama 500ml', 'Pertanian', 45, 42000, 50000, 'PT Syngenta Indonesia', 'Pusat'),
(6, 'BRG-C1-001', 'Minyak Goreng Kita 1L (Cabang)', 'Sembako', 75, 13500, 15500, 'Distributor Sembako Jaya', 'Cabang Dusun I'),
(7, 'BRG-C1-002', 'Gula Pasir Lokal 1kg', 'Sembako', 120, 14000, 16000, 'Koperasi Tebu Indah', 'Cabang Dusun I'),
(8, 'BRG-C2-001', 'Beras Medium Desa 10kg (Cabang)', 'Sembako', 40, 115000, 132000, 'Gudang Tani KPRMP', 'Cabang Dusun II'),
(9, 'BRG-C2-002', 'Pupuk Urea Subsidi 50kg (Cabang)', 'Pertanian', 30, 110000, 128000, 'PT Pupuk Sriwidjaja', 'Cabang Dusun II');

-- Seed Logistics
INSERT INTO logistics (id, tracking_number, cargo, sender, receiver, status, ship_date) VALUES
(1, 'TRK20260501', '100 Karung Pupuk Urea', 'Gudang Ritel KPRMP', 'Kelompok Tani Makmur', 'Diterima', '2026-05-10'),
(2, 'TRK20260502', '500kg Hasil Panen Padi', 'Petani Budi RT01', 'Gudang Utama KPRMP', 'Dalam Perjalanan', '2026-05-24'),
(3, 'TRK20260503', 'Sembako & Minyak Goreng', 'Distributor Surabaya', 'Toko Kelontong Koperasi', 'Diproses', '2026-05-25');

-- Seed Warehouse
INSERT INTO warehouse (id, commodity, quantity, grade, incoming_date, warehouse_location) VALUES
(1, 'Gabah Kering Giling', 12.5, 'A', '2026-05-18', 'Silo Barat - Gudang 1'),
(2, 'Jagung Pipil Kering', 8.2, 'B', '2026-05-20', 'Hangar Timur - Gudang 2'),
(3, 'Kacang Tanah Kupas', 1.5, 'A', '2026-05-22', 'Ruang Dingin - Gudang 1');

-- Seed Capital
INSERT INTO capital (id, source, amount, date, description) VALUES
(1, 'Simpanan Pokok Awal', 50000000, '2024-01-01', 'Modal awal dari simpanan pokok pendiri koperasi'),
(2, 'Bantuan Hibah Kemendes', 150000000, '2025-03-10', 'Dana Hibah Program Pengembangan BUMDes/Koperasi Desa'),
(3, 'Penyertaan Modal Pemdes', 75000000, '2025-06-15', 'Investasi modal bergulir APBDes Desa Merah Putih');

-- Seed SAL Funding
INSERT INTO sal_funding (id, allocation_name, amount, disbursement_date, status) VALUES
(1, 'Pembiayaan Bibit Musim Tanam Gadu', 30000000, '2026-05-01', 'Disalurkan'),
(2, 'Revitalisasi Mesin Pengering Padi', 45000000, '2026-06-15', 'Direncanakan'),
(3, 'Stok Operasional Ritel Sembako', 15000000, '2026-04-10', 'Selesai');

-- Seed Village Guarantees
INSERT INTO village_guarantees (id, project_name, amount, guarantee_date, status) VALUES
(1, 'Pembangunan Saluran Irigasi Subak', 80000000, '2026-04-01', 'Aktif'),
(2, 'Perkerasan Jalan Usaha Tani Dusun II', 120000000, '2025-09-12', 'Selesai');

-- Seed Savings & Loans
INSERT INTO savings_loans (id, member_id, type, amount, interest_rate, tenor, status, created_at) VALUES
(1, 1, 'Simpanan Pokok', 100000, 0, 0, 'Aktif', '2024-01-15'),
(2, 1, 'Simpanan Wajib', 240000, 0, 0, 'Aktif', '2024-12-15'),
(3, 2, 'Simpanan Pokok', 100000, 0, 0, 'Aktif', '2024-01-15'),
(4, 2, 'Simpanan Wajib', 240000, 0, 0, 'Aktif', '2024-12-15'),
(5, 3, 'Simpanan Pokok', 100000, 0, 0, 'Aktif', '2024-01-15'),
(6, 3, 'Simpanan Wajib', 240000, 0, 0, 'Aktif', '2024-12-15'),
(7, 4, 'Simpanan Pokok', 100000, 0, 0, 'Aktif', '2024-01-15'),
(8, 4, 'Simpanan Wajib', 240000, 0, 0, 'Aktif', '2024-12-15'),
(9, 1, 'Simpanan Sukarela', 500000, 0, 0, 'Aktif', '2025-03-20'),
(10, 2, 'Simpanan Sukarela', 1200000, 0, 0, 'Aktif', '2025-06-10'),
(11, 2, 'Pinjaman', 5000000, 1.5, 10, 'Aktif', '2026-01-10'),
(12, 1, 'Pinjaman', 2000000, 1.5, 5, 'Lunas', '2025-08-01'),
(13, 3, 'Pinjaman', 3000000, 1.5, 12, 'Menunggu', '2026-05-20');

-- Seed SHU Settings for 2026
INSERT INTO shu_settings (id, year, reserve_percentage, member_capital_percentage, member_transaction_percentage, education_percentage, board_percentage, total_income, total_expense) VALUES
(1, 2026, 20, 40, 30, 5, 5, 45000000, 15000000);

-- Seed Village Info
INSERT INTO village_info (id, title, content, category, published_at) VALUES
(1, 'Rapat Anggota Tahunan (RAT) Tahun Buku 2025 Selesai Diselenggarakan', 'Koperasi Desa Merah Putih sukses mengadakan RAT Tahun Buku 2025 pada 20 Februari 2026 kemarin. Pembagian SHU telah disepakati dan langsung ditransfer ke rekening tabungan sukarela masing-masing anggota aktif. Pengurus mengucapkan terima kasih atas partisipasi seluruh warga.', 'Berita', '2026-02-21'),
(2, 'Penyaluran Pupuk Subsidi Sektor Pertanian Musim Tanam Gadu 2026', 'Diberitahukan kepada seluruh anggota kelompok tani yang terdaftar di Koperasi Merah Putih, bahwa alokasi pupuk subsidi urea dan NPK telah tiba di Gudang Utama. Tebus pupuk dapat dilakukan mulai hari Senin dengan membawa kartu anggota dan KTP asli.', 'Pengumuman', '2026-05-02'),
(3, 'KPRMP Membuka Pendaftaran Unit Usaha Ritel Sembako Baru', 'Dalam rangka memperluas jangkauan pelayanan logistik pangan desa, Koperasi Merah Putih mengundang warga yang berminat menjadi mitra ritel (Warung Koperasi) di tingkat RT/RW. Koperasi akan menyediakan pasokan barang dan sistem kasir digital gratis.', 'Kegiatan', '2026-05-15');
