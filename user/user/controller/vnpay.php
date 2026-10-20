<?php
declare(strict_types=1);
ini_set('display_errors', '0');
$root = dirname(__DIR__, 3);
$action = $_GET['action'] ?? '';
if ($action === 'create') { require __DIR__ . '/thanhtoan.php'; exit; }
if ($action === 'return') {
    header('Location: /qlpm/payment/vnpay-return?' . ($_SERVER['QUERY_STRING'] ?? ''), true, 303);
    exit;
}
require_once $root . '/payment/private/bootstrap.php';
require_once $root . '/payment/private/StoreCheckout.php';
require_once __DIR__ . '/../../model/connectDB.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    if ($action !== 'ipn' || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405); echo '{"RspCode":"99","Message":"Unsupported endpoint"}'; exit;
    }
    $gateway=paymentGateway(paymentConfig());
    $raw=$_SERVER['QUERY_STRING'] ?? '';
    // Verify BEFORE connecting to DB. Routing parameters are not signed VNP fields.
    if ($gateway->verifiedQuery($raw) === null) $result=['RspCode'=>'97','Message'=>'Invalid signature'];
    else $result=(new StoreCheckout(connectdb(),$gateway))->ipn($raw);
    echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log('UniBook VNPAY callback failed: '.get_class($e));
    echo '{"RspCode":"99","Message":"Unknown error"}';
}
