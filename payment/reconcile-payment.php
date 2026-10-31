<?php
declare(strict_types=1);
ini_set('display_errors','0');
require __DIR__.'/private/bootstrap.php';
require __DIR__.'/private/StoreReconciler.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    if ($_SERVER['REQUEST_METHOD']!=='POST') {http_response_code(405);exit;}
    $body=json_decode(file_get_contents('php://input',false,null,0,20000),true,8,JSON_THROW_ON_ERROR);
    $config=paymentConfig();$gateway=paymentGateway($config);
    $raw=$body['return_query'] ?? null;
    if (!is_string($raw) || !($data=$gateway->verifiedQuery($raw)) || ($data['vnp_TmnCode'] ?? '')!==$gateway->tmnCode) {
        http_response_code(403);echo '{"status":"invalid"}';exit;
    }
    $db=new PDO($config['PAYMENT_DB_DSN'],$config['PAYMENT_DB_USER'],$config['PAYMENT_DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $q=$db->prepare('SELECT id,amount FROM checkout_payment_attempts WHERE txn_ref=?');$q->execute([$data['vnp_TxnRef'] ?? '']);$payment=$q->fetch(PDO::FETCH_ASSOC);
    if (!$payment || (string)($data['vnp_Amount'] ?? '')!==(string)((int)$payment['amount']*100)) {http_response_code(404);echo '{"status":"not_found"}';exit;}
    // Signed Return only identifies the payment. A NEW server-to-server signed query decides settlement.
    $result=(new StoreReconciler($db,$gateway))->run((int)$payment['id']);
    echo json_encode(['status'=>in_array($result['RspCode'],['00','02'],true)?'processed':'pending','retry_after'=>$result['retry_after'] ?? 0]);
} catch(Throwable $e) {
    error_log('UniBook reconciliation failed: '.get_class($e));http_response_code(503);echo '{"status":"unavailable"}';
}
