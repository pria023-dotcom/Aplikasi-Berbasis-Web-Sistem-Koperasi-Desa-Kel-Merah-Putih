<?php
// api/handler.php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
    exit;
}

// Generic error response helper
function sendError($msg = 'Kesalahan komunikasi dengan server') {
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

$userRole = strtolower($_SESSION['role'] ?? 'anggota');
$userMemberId = $_SESSION['member_id'] ?? null;
$pdo = getDBConnection();

// Determine action from POST, GET, or JSON payload
$rawInput = file_get_contents('php://input');
if ($rawInput) {
    $jsonData = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $_POST = array_merge($_POST, $jsonData);
    }
}
$action = $_POST['action'] ?? $_GET['action'] ?? '';
// Normalize action name
// 1. Convert camelCase to snake_case
$action = preg_replace('/([a-z])([A-Z])/', '$1_$2', $action);
// 2. Replace any non‑alphanumeric characters with underscore
$action = preg_replace('/[^a-zA-Z0-9]+/', '_', $action);
// 3. Lowercase
$action = strtolower($action);

// Fallback action mapping (Indonesian -> English backend actions)
$actionMap = [
    'add_keanggotaan'   => 'add_member',
    'add_ritel'         => 'add_goods',
    'add_logistik'      => 'add_logistics',
    'add_gudang'        => 'add_warehouse',
    'add_permodalan'    => 'add_capital',
    'add_pendanaan_sal' => 'add_sal',
    'add_jaminan_desa'  => 'add_guarantee',
    'add_info_desa'     => 'add_info',
];
if (isset($actionMap[$action])) {
    $action = $actionMap[$action];
}

// Debug log the final action
error_log('Handler action resolved: ' . $action);

if (!$action) {
    sendError('Aksi tidak valid atau tidak ditentukan');
}

// ===================================================
// ROLE-BASED ACCESS CONTROL (RBAC) HELPER
// ===================================================
function hasWriteAccess($action, $userRole) {
    // Admin, Pengawas, and Developer can write/delete everything
    if ($userRole === 'admin' || $userRole === 'pengawas' || $userRole === 'developer') {
        return true;
    }
    
    // Anggota cannot write or edit anything
    if ($userRole === 'anggota') {
        return false;
    }
    
    // Ritel Staff can only manage Ritel and Logistics
    if ($userRole === 'ritel') {
        $allowed = ['add_goods', 'edit_goods', 'quick_update_stock', 'add_logistics', 'edit_logistics', 'delete_record', 'process_sale', 'delete_sale', 'get_sale_details'];
        if ($action === 'delete_record') {
            $table = $_POST['table'] ?? '';
            return in_array($table, ['retail_goods', 'logistics', 'retail_sales']);
        }
        return in_array($action, $allowed);
    }
    
    // Gudang Staff can only manage Gudang and Logistics
    if ($userRole === 'gudang') {
        $allowed = ['add_warehouse', 'edit_warehouse', 'add_logistics', 'edit_logistics', 'delete_record'];
        if ($action === 'delete_record') {
            $table = $_POST['table'] ?? '';
            return in_array($table, ['warehouse', 'logistics']);
        }
        return in_array($action, $allowed);
    }
    
    return false;
}

// Enforce RBAC
if (!hasWriteAccess($action, $userRole)) {
    sendError('Anda tidak memiliki hak akses (otorisasi) untuk melakukan tindakan ini.');
}

try {
    switch ($action) {
        // GENERIC DELETE ACTION
        case 'delete_record':
            $table = $_POST['table'] ?? '';
            $id = intval($_POST['id'] ?? 0);
            
            // Whitelist tables to prevent SQL injection
            $allowedTables = [
                'members', 'retail_goods', 'logistics', 
                'warehouse', 'capital', 'sal_funding', 
                'village_guarantees', 'savings_loans', 'village_info'
            ];
            
            if (!in_array($table, $allowedTables) || $id <= 0) {
                sendError('Tabel atau ID tidak valid');
            }
            
            $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
            break;

        // MEMBERS CRUD
        case 'add_member':
            $name = $_POST['name'] ?? '';
            $nik = $_POST['nik'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $join_date = $_POST['join_date'] ?? date('Y-m-d');
            $status = $_POST['status'] ?? 'Aktif';

            // Generate Member Code
            $maxId = $pdo->query("SELECT MAX(id) FROM members")->fetchColumn() ?? 0;
            $code = 'KPR-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("INSERT INTO members (member_code, name, nik, phone, address, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $nik, $phone, $address, $join_date, $status]);
            echo json_encode(['status' => 'success', 'message' => 'Anggota berhasil ditambahkan']);
            break;

        case 'edit_member':
            $id = intval($_POST['id'] ?? 0);
            $name = $_POST['name'] ?? '';
            $nik = $_POST['nik'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $join_date = $_POST['join_date'] ?? '';
            $status = $_POST['status'] ?? 'Aktif';

            $stmt = $pdo->prepare("UPDATE members SET name = ?, nik = ?, phone = ?, address = ?, join_date = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $nik, $phone, $address, $join_date, $status, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Anggota berhasil diperbarui']);
            break;

        // RETAIL GOODS CRUD
        case 'add_goods':
            $code = $_POST['code'] ?? '';
            $name = $_POST['name'] ?? '';
            $category = $_POST['category'] ?? '';
            $stock = intval($_POST['stock'] ?? 0);
            $buy_price = floatval($_POST['buy_price'] ?? 0);
            $sell_price = floatval($_POST['sell_price'] ?? 0);
            $supplier = $_POST['supplier'] ?? '';
            $branch = $_POST['branch'] ?? 'Pusat';

            $stmt = $pdo->prepare("INSERT INTO retail_goods (code, name, category, stock, buy_price, sell_price, supplier, branch) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $category, $stock, $buy_price, $sell_price, $supplier, $branch]);
            echo json_encode(['status' => 'success', 'message' => 'Barang ritel berhasil ditambahkan']);
            break;

        case 'edit_goods':
            $id = intval($_POST['id'] ?? 0);
            $code = $_POST['code'] ?? '';
            $name = $_POST['name'] ?? '';
            $category = $_POST['category'] ?? '';
            $stock = intval($_POST['stock'] ?? 0);
            $buy_price = floatval($_POST['buy_price'] ?? 0);
            $sell_price = floatval($_POST['sell_price'] ?? 0);
            $supplier = $_POST['supplier'] ?? '';
            $branch = $_POST['branch'] ?? 'Pusat';

            $stmt = $pdo->prepare("UPDATE retail_goods SET code = ?, name = ?, category = ?, stock = ?, buy_price = ?, sell_price = ?, supplier = ?, branch = ? WHERE id = ?");
            $stmt->execute([$code, $name, $category, $stock, $buy_price, $sell_price, $supplier, $branch, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Barang ritel berhasil diperbarui']);
            break;

        // QUICK STOCK UPDATE ACTION (NEW)
        case 'quick_update_stock':
            $id = intval($_POST['id'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID barang tidak valid']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE retail_goods SET stock = ? WHERE id = ?");
            $stmt->execute([$stock, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Stok berhasil diperbarui']);
            break;

        // LOGISTICS CRUD
        case 'add_logistics':
            $tracking_number = $_POST['tracking_number'] ?? '';
            $cargo = $_POST['cargo'] ?? '';
            $sender = $_POST['sender'] ?? '';
            $receiver = $_POST['receiver'] ?? '';
            $ship_date = $_POST['ship_date'] ?? date('Y-m-d');
            $status = $_POST['status'] ?? 'Pending';

            if (empty($tracking_number)) {
                $tracking_number = 'TRK' . date('Ymd') . rand(10, 99);
            }

            $stmt = $pdo->prepare("INSERT INTO logistics (tracking_number, cargo, sender, receiver, ship_date, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tracking_number, $cargo, $sender, $receiver, $ship_date, $status]);
            echo json_encode(['status' => 'success', 'message' => 'Pengiriman logistik berhasil didaftarkan']);
            break;

        case 'edit_logistics':
            $id = intval($_POST['id'] ?? 0);
            $tracking_number = $_POST['tracking_number'] ?? '';
            $cargo = $_POST['cargo'] ?? '';
            $sender = $_POST['sender'] ?? '';
            $receiver = $_POST['receiver'] ?? '';
            $ship_date = $_POST['ship_date'] ?? '';
            $status = $_POST['status'] ?? '';

            $stmt = $pdo->prepare("UPDATE logistics SET tracking_number = ?, cargo = ?, sender = ?, receiver = ?, ship_date = ?, status = ? WHERE id = ?");
            $stmt->execute([$tracking_number, $cargo, $sender, $receiver, $ship_date, $status, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Data logistik berhasil diperbarui']);
            break;

        // WAREHOUSE CRUD
        case 'add_warehouse':
            $commodity = $_POST['commodity'] ?? '';
            $quantity = floatval($_POST['quantity'] ?? 0);
            $grade = $_POST['grade'] ?? 'A';
            $incoming_date = $_POST['incoming_date'] ?? date('Y-m-d');
            $warehouse_location = $_POST['warehouse_location'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO warehouse (commodity, quantity, grade, incoming_date, warehouse_location) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$commodity, $quantity, $grade, $incoming_date, $warehouse_location]);
            echo json_encode(['status' => 'success', 'message' => 'Hasil pertanian berhasil dicatat']);
            break;

        case 'edit_warehouse':
            $id = intval($_POST['id'] ?? 0);
            $commodity = $_POST['commodity'] ?? '';
            $quantity = floatval($_POST['quantity'] ?? 0);
            $grade = $_POST['grade'] ?? 'A';
            $incoming_date = $_POST['incoming_date'] ?? '';
            $warehouse_location = $_POST['warehouse_location'] ?? '';

            $stmt = $pdo->prepare("UPDATE warehouse SET commodity = ?, quantity = ?, grade = ?, incoming_date = ?, warehouse_location = ? WHERE id = ?");
            $stmt->execute([$commodity, $quantity, $grade, $incoming_date, $warehouse_location, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Data gudang berhasil diperbarui']);
            break;

        // CAPITAL (PERMODALAN) CRUD
        case 'add_capital':
            $source = $_POST['source'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $date = $_POST['date'] ?? date('Y-m-d');
            $description = $_POST['description'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO capital (source, amount, date, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$source, $amount, $date, $description]);
            echo json_encode(['status' => 'success', 'message' => 'Modal berhasil dicatat']);
            break;

        case 'edit_capital':
            $id = intval($_POST['id'] ?? 0);
            $source = $_POST['source'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $date = $_POST['date'] ?? '';
            $description = $_POST['description'] ?? '';

            $stmt = $pdo->prepare("UPDATE capital SET source = ?, amount = ?, date = ?, description = ? WHERE id = ?");
            $stmt->execute([$source, $amount, $date, $description, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Modal berhasil diperbarui']);
            break;

        // SAL FUNDING CRUD
        case 'add_sal':
            $allocation_name = $_POST['allocation_name'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $disbursement_date = $_POST['disbursement_date'] ?? date('Y-m-d');
            $status = $_POST['status'] ?? 'Direncanakan';

            $stmt = $pdo->prepare("INSERT INTO sal_funding (allocation_name, amount, disbursement_date, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$allocation_name, $amount, $disbursement_date, $status]);
            echo json_encode(['status' => 'success', 'message' => 'Penyaluran SAL berhasil ditambahkan']);
            break;

        case 'edit_sal':
            $id = intval($_POST['id'] ?? 0);
            $allocation_name = $_POST['allocation_name'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $disbursement_date = $_POST['disbursement_date'] ?? '';
            $status = $_POST['status'] ?? '';

            $stmt = $pdo->prepare("UPDATE sal_funding SET allocation_name = ?, amount = ?, disbursement_date = ?, status = ? WHERE id = ?");
            $stmt->execute([$allocation_name, $amount, $disbursement_date, $status, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Penyaluran SAL berhasil diperbarui']);
            break;

        // VILLAGE GUARANTEE CRUD
        case 'add_guarantee':
            $project_name = $_POST['project_name'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $guarantee_date = $_POST['guarantee_date'] ?? date('Y-m-d');
            $status = $_POST['status'] ?? 'Aktif';

            $stmt = $pdo->prepare("INSERT INTO village_guarantees (project_name, amount, guarantee_date, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$project_name, $amount, $guarantee_date, $status]);
            echo json_encode(['status' => 'success', 'message' => 'Jaminan proyek berhasil ditambahkan']);
            break;

        case 'edit_guarantee':
            $id = intval($_POST['id'] ?? 0);
            $project_name = $_POST['project_name'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $guarantee_date = $_POST['guarantee_date'] ?? '';
            $status = $_POST['status'] ?? '';

            $stmt = $pdo->prepare("UPDATE village_guarantees SET project_name = ?, amount = ?, guarantee_date = ?, status = ? WHERE id = ?");
            $stmt->execute([$project_name, $amount, $guarantee_date, $status, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Data jaminan berhasil diperbarui']);
            break;

        // SIMPAN PINJAM CRUD
        case 'add_simpan_pinjam':
            $member_id = intval($_POST['member_id'] ?? 0);
            $type = $_POST['type'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $interest_rate = floatval($_POST['interest_rate'] ?? 0);
            $tenor = intval($_POST['tenor'] ?? 0);
            $status = $_POST['status'] ?? 'Aktif';
            $created_at = $_POST['created_at'] ?? date('Y-m-d');

            if ($type == 'Pinjaman' && $status == 'Aktif') {
                $status = 'Menunggu'; // Loans start pending approval
            }

            $stmt = $pdo->prepare("INSERT INTO savings_loans (member_id, type, amount, interest_rate, tenor, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$member_id, $type, $amount, $interest_rate, $tenor, $status, $created_at]);
            echo json_encode(['status' => 'success', 'message' => 'Transaksi simpan pinjam berhasil dicatat']);
            break;

        case 'edit_simpan_pinjam':
            $id = intval($_POST['id'] ?? 0);
            $member_id = intval($_POST['member_id'] ?? 0);
            $type = $_POST['type'] ?? '';
            $amount = floatval($_POST['amount'] ?? 0);
            $interest_rate = floatval($_POST['interest_rate'] ?? 0);
            $tenor = intval($_POST['tenor'] ?? 0);
            $status = $_POST['status'] ?? '';
            $created_at = $_POST['created_at'] ?? '';

            $stmt = $pdo->prepare("UPDATE savings_loans SET member_id = ?, type = ?, amount = ?, interest_rate = ?, tenor = ?, status = ?, created_at = ? WHERE id = ?");
            $stmt->execute([$member_id, $type, $amount, $interest_rate, $tenor, $status, $created_at, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Transaksi simpan pinjam berhasil diperbarui']);
            break;

        case 'approve_loan':
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE savings_loans SET status = 'Disetujui' WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Pinjaman disetujui dan aktif']);
            break;

        // SHU SETTINGS UPDATE
        case 'update_shu_settings':
            $total_income = floatval($_POST['total_income'] ?? 0);
            $total_expense = floatval($_POST['total_expense'] ?? 0);
            $reserve_percentage = floatval($_POST['reserve_percentage'] ?? 0);
            $member_capital_percentage = floatval($_POST['member_capital_percentage'] ?? 0);
            $member_transaction_percentage = floatval($_POST['member_transaction_percentage'] ?? 0);
            $education_percentage = floatval($_POST['education_percentage'] ?? 0);
            $board_percentage = floatval($_POST['board_percentage'] ?? 0);

            // Verify totals
            $totalPercent = $reserve_percentage + $member_capital_percentage + $member_transaction_percentage + $education_percentage + $board_percentage;
            if (abs($totalPercent - 100.0) > 0.01) {
                echo json_encode(['status' => 'error', 'message' => 'Total persentase alokasi SHU harus berjumlah 100% (Saat ini: ' . $totalPercent . '%)']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE shu_settings SET 
                total_income = ?, 
                total_expense = ?, 
                reserve_percentage = ?, 
                member_capital_percentage = ?, 
                member_transaction_percentage = ?, 
                education_percentage = ?, 
                board_percentage = ? 
                WHERE year = 2026");
            $stmt->execute([$total_income, $total_expense, $reserve_percentage, $member_capital_percentage, $member_transaction_percentage, $education_percentage, $board_percentage]);
            
            echo json_encode(['status' => 'success', 'message' => 'Konfigurasi SHU berhasil diperbarui dan dikalkulasi']);
            break;

        // VILLAGE INFO CRUD
        case 'add_info':
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $category = $_POST['category'] ?? 'Berita';
            $published_at = $_POST['published_at'] ?? date('Y-m-d');

            $stmt = $pdo->prepare("INSERT INTO village_info (title, content, category, published_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $content, $category, $published_at]);
            echo json_encode(['status' => 'success', 'message' => 'Publikasi informasi berhasil diterbitkan']);
            break;

        case 'edit_info':
            $id = intval($_POST['id'] ?? 0);
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $category = $_POST['category'] ?? 'Berita';
            $published_at = $_POST['published_at'] ?? '';

            $stmt = $pdo->prepare("UPDATE village_info SET title = ?, content = ?, category = ?, published_at = ? WHERE id = ?");
            $stmt->execute([$title, $content, $category, $published_at, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Publikasi informasi berhasil diperbarui']);
            break;

        // RETAIL SALES ENDPOINTS
        case 'process_sale':
            $items_json = $_POST['items'] ?? '';
            $items = json_decode($items_json, true);
            if (empty($items) || !is_array($items)) {
                sendError('Keranjang belanja kosong atau tidak valid');
            }

            $todayStr = date('Ymd');
            $likeStr = "TRX-" . $todayStr . "-%";
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM retail_sales WHERE transaction_code LIKE ?");
            $stmt->execute([$likeStr]);
            $count = $stmt->fetchColumn();
            $transaction_code = "TRX-" . $todayStr . "-" . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $pdo->beginTransaction();
            try {
                $member_id = !empty($_POST['member_id']) ? intval($_POST['member_id']) : null;
                $total_amount = floatval($_POST['total_amount'] ?? 0);
                $payment_amount = floatval($_POST['payment_amount'] ?? 0);
                $change_amount = floatval($_POST['change_amount'] ?? 0);
                $created_by = $_SESSION['name'] ?? 'Staff';

                $stmt = $pdo->prepare("INSERT INTO retail_sales (transaction_code, member_id, total_amount, payment_amount, change_amount, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$transaction_code, $member_id, $total_amount, $payment_amount, $change_amount, $created_by]);
                $sale_id = $pdo->lastInsertId();

                foreach ($items as $item) {
                    $goods_id = intval($item['goods_id']);
                    $qty = intval($item['quantity']);
                    $price = floatval($item['price']);
                    $subtotal = $qty * $price;

                    $goodsStmt = $pdo->prepare("SELECT name, stock FROM retail_goods WHERE id = ?");
                    $goodsStmt->execute([$goods_id]);
                    $goods = $goodsStmt->fetch();
                    if (!$goods) {
                        throw new Exception("Barang dengan ID $goods_id tidak ditemukan.");
                    }
                    if ($goods['stock'] < $qty) {
                        throw new Exception("Stok untuk '" . $goods['name'] . "' tidak mencukupi (Tersedia: " . $goods['stock'] . " pcs, Diminta: " . $qty . " pcs).");
                    }

                    $updateStmt = $pdo->prepare("UPDATE retail_goods SET stock = stock - ? WHERE id = ?");
                    $updateStmt->execute([$qty, $goods_id]);

                    $detailStmt = $pdo->prepare("INSERT INTO retail_sale_details (sale_id, goods_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $detailStmt->execute([$sale_id, $goods_id, $qty, $price, $subtotal]);
                }

                $pdo->commit();
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Transaksi berhasil diproses', 
                    'transaction_code' => $transaction_code, 
                    'sale_id' => $sale_id
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'delete_sale':
            $sale_id = intval($_POST['id'] ?? 0);
            if ($sale_id <= 0) {
                sendError('ID transaksi tidak valid');
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("SELECT * FROM retail_sale_details WHERE sale_id = ?");
                $stmt->execute([$sale_id]);
                $details = $stmt->fetchAll();

                foreach ($details as $detail) {
                    $goods_id = $detail['goods_id'];
                    $qty = $detail['quantity'];

                    $updateStmt = $pdo->prepare("UPDATE retail_goods SET stock = stock + ? WHERE id = ?");
                    $updateStmt->execute([$qty, $goods_id]);
                }

                $deleteStmt = $pdo->prepare("DELETE FROM retail_sales WHERE id = ?");
                $deleteStmt->execute([$sale_id]);

                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil dibatalkan dan stok dikembalikan']);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_sale_details':
            $sale_id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            if ($sale_id <= 0) {
                sendError('ID transaksi tidak valid');
            }

            $stmt = $pdo->prepare("SELECT s.*, m.name as member_name, m.member_code 
                                   FROM retail_sales s 
                                   LEFT JOIN members m ON s.member_id = m.id 
                                   WHERE s.id = ?");
            $stmt->execute([$sale_id]);
            $sale = $stmt->fetch();

            if (!$sale) {
                sendError('Transaksi tidak ditemukan');
            }

            $stmt = $pdo->prepare("SELECT d.*, g.name as goods_name, g.code as goods_code 
                                   FROM retail_sale_details d 
                                   JOIN retail_goods g ON d.goods_id = g.id 
                                   WHERE d.sale_id = ?");
            $stmt->execute([$sale_id]);
            $details = $stmt->fetchAll();

            echo json_encode([
                'status' => 'success',
                'sale' => $sale,
                'details' => $details
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenal']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}
