<?php
// login.php
session_start();
require_once __DIR__ . '/config/database.php';
// Support JSON payloads for login
$rawInput = file_get_contents('php://input');
if ($rawInput) {
    $jsonData = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $_POST = array_merge($_POST, $jsonData);
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $pdo = getDBConnection();
        // Case‑insensitive username lookup
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        $loginSuccess = false;
        if ($user) {
            // Try hashed password verification first
            if (password_verify($password, $user['password'])) {
                $loginSuccess = true;
            } elseif ($password === $user['password']) {
                // Fallback for plain‑text stored passwords
                $loginSuccess = true;
            }
        }
        
        if ($loginSuccess) {
            // Success login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['member_id'] = $user['member_id'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Koperasi Desa/Kelurahan Merah Putih</title>
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
            max-width: 440px;
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
            margin-bottom: 35px;
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
            <h2>KPRMP</h2>
            <p>Koperasi Desa/Kelurahan Merah Putih</p>
            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: -5px;">Portal Layanan Administrasi & Keuangan</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <span>⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" style="display:flex; flex-direction:column; gap:20px;">
            <div class="form-group">
                <label for="username">Username Akun</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi (Password)</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary" style="justify-content:center; width:100%; padding:14px; border-radius:12px; margin-top:10px;">
                Masuk Sistem
            </button>
        </form>

        <div class="auth-footer">
            Belum terdaftar? <a href="register.php">Registrasi Akun Baru</a>
        </div>
    </div>
</div>

</body>
</html>
