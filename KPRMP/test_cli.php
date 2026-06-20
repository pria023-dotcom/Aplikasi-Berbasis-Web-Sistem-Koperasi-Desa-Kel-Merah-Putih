<?php
// test_cli.php
$_SESSION = [
    'user_id' => 1,
    'role' => 'ritel',
    'name' => 'Staff Unit Ritel'
];
ob_start();
include 'index.php';
$html = ob_get_clean();
if (preg_match('/const posGoodsData = (.*?);/', $html, $matches)) {
    echo "posGoodsData found: " . $matches[1] . "\n";
} else {
    echo "posGoodsData NOT found!\n";
}
?>
