<?php
// register.php
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'anggota';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $name = '';
    $member_id = null;

    if (!empty($username) && !empty($password) && !empty($role)) {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username sudah terdaftar. Silakan pilih username lain.';
            } else {
                if ($role === 'anggota') {
                    $nik_or_phone = trim($_POST['nik_or_phone'] ?? '');
                    if (empty($nik_or_phone)) {
                        $error = 'Harap isi NIK atau Nomor Telepon yang terdaftar.';
                    } else {
                        // Find matching active member
                        $stmt = $pdo->prepare("SELECT * FROM members WHERE nik = ? OR phone = ?");
                        $stmt->execute([$nik_or_phone, $nik_or_phone]);
                        $member = $stmt->fetch();

                        if (!$member) {
                            $error = 'NIK atau Nomor Telepon tidak ditemukan di data keanggotaan. Harap hubungi pengurus koperasi.';
                        } else if ($member['status'] !== 'Aktif') {
                            $error = 'Data keanggotaan Anda saat ini sedang Non-Aktif. Harap hubungi pengurus.';
                        } else {
                            // Check if member already has a login account
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE member_id = ?");
                            $stmt->execute([$member['id']]);
                            if ($stmt->fetchColumn() > 0) {
                                $error = 'Data anggota tersebut sudah terikat dengan akun lain.';
                            } else {
                                $member_id = $member['id'];
                                $name = $member['name']; // Automatically resolve name from official record
                            }
                        }
                    }
                } else {
                    $name = trim($_POST['name'] ?? '');
                    if (empty($name)) {
                        $error = 'Harap isi nama lengkap Anda.';
                    }
                }

                if (empty($error)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, role, member_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $hashedPassword, $name, $role, $member_id]);
                    
                    $success = 'Registrasi berhasil! Mengalihkan ke login...';
                    header('refresh:2;url=login.php');
                }
            }
        } catch (PDOException $e) {
            $error = 'Gagal mendaftar akun: ' . $e->getMessage();
        }
    } else {
        $error = 'Harap lengkapi semua kolom.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun - Koperasi Desa/Kelurahan Merah Putih</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        .auth-card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            width: 100%;
            max-width: 480px;
            padding: 40px;
            box-shadow: var(--shadow-lg);
            box-shadow: 0 15px 35px rgba(143, 14, 14, 0.08);
            animation: cardEntrance 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            text-align: center;
            margin-bottom: 25px;
        }

        .auth-brand .logo-box {
            background: linear-gradient(135deg, var(--primary), #FF5E5E);
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 16px rgba(201, 42, 42, 0.3);
        }

        .auth-brand .logo-box svg {
            width: 28px;
            height: 28px;
            fill: #FFFFFF;
        }

        .auth-brand h2 {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-top: 5px;
        }

        .auth-brand p {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .alert-error {
            background: var(--danger-light);
            color: var(--danger);
            border: 1px solid rgba(240, 62, 62, 0.2);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid rgba(12, 166, 120, 0.2);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auth-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .auth-footer a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <img src="assets/images/logo.png" alt="Logo KPRMP" style="height: 85px; width: auto; object-fit: contain; margin-bottom: 8px;">
            <h2>Daftar Akun Baru</h2>
            <p>Sistem Informasi Koperasi Desa/Kelurahan Merah Putih</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <span>⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-success">
                <span>✅</span>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" style="display:flex; flex-direction:column; gap:15px;">
            <div class="form-group">
                <label for="role">Pilih Role / Unit Kerja</label>
                <select class="form-control" id="role" name="role" onchange="toggleMemberField(this.value)" required>
                    <option value="anggota" selected>Anggota Koperasi</option>
                    <option value="pengawas">Kepala Pengawas</option>
                    <option value="ritel">Satuan Unit Ritel (Staff Toko)</option>
                    <option value="gudang">Satuan Unit Gudang & Logistik (Staff)</option>
                </select>
            </div>

            <!-- Shown for Non-Anggota roles -->
            <div class="form-group" id="name-group" style="display:none;">
                <label for="name">Nama Lengkap Pengguna</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nama lengkap Anda">
            </div>

            <!-- NIK / Phone search (Only shown for Anggota) -->
            <div class="form-group" id="member-link-group">
                <label for="nik_or_phone">Masukkan NIK atau No Telepon Terdaftar</label>
                <input type="text" class="form-control" id="nik_or_phone" name="nik_or_phone" placeholder="Masukkan NIK 16 digit atau nomor HP" required>
                <p style="font-size:0.75rem; color:var(--text-light); margin-top:4px;">Nama akun Anda akan langsung disesuaikan dengan data keanggotaan resmi yang didaftarkan oleh admin.</p>
            </div>

            <div class="form-group">
                <label for="username">Username Baru</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Buat username unik" required>
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi (Password)</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Buat password minimal 6 karakter" required>
            </div>

            <button type="submit" class="btn-primary" style="justify-content:center; width:100%; padding:14px; border-radius:12px; margin-top:10px;">
                Daftar Akun
            </button>
        </form>

        <div class="auth-footer">
            Sudah terdaftar? <a href="login.php">Masuk Disini</a>
        </div>
    </div>
</div>

<script>
function toggleMemberField(role) {
    const memberGroup = document.getElementById('member-link-group');
    const memberInput = document.getElementById('nik_or_phone');
    const nameGroup = document.getElementById('name-group');
    const nameInput = document.getElementById('name');
    
    if (role === 'anggota') {
        memberGroup.style.display = 'flex';
        memberInput.required = true;
        nameGroup.style.display = 'none';
        nameInput.required = false;
        nameInput.value = '';
    } else {
        memberGroup.style.display = 'none';
        memberInput.required = false;
        memberInput.value = '';
        nameGroup.style.display = 'flex';
        nameInput.required = true;
    }
}
</script>
</body>
</html>
