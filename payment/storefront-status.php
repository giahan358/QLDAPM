<?php
declare(strict_types=1);
ini_set('display_errors','0');
require __DIR__.'/private/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    $config=paymentConfig();$gateway=paymentGateway($config);
    $data=$gateway->verifiedQuery($_SERVER['QUERY_STRING'] ?? '');
    if ($_SERVER['REQUEST_METHOD']!=='GET' || !$data || ($data['vnp_TmnCode'] ?? '')!==$gateway->tmnCode) {
        http_response_code(403);echo '{"status":"invalid"}';exit;
    }
    // The signed Return query only authorizes this read. It never changes payment/cart/stock.
    $db=new PDO($config['PAYMENT_DB_DSN'],$config['PAYMENT_DB_USER'],$config['PAYMENT_DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $q=$db->prepare('SELECT p.status,p.amount,c.invoice_id FROM checkout_payment_attempts p JOIN checkout_sessions c ON c.id=p.checkout_id WHERE p.txn_ref=?');
    $q->execute([$data['vnp_TxnRef'] ?? '']);$payment=$q->fetch(PDO::FETCH_ASSOC);
    if (!$payment || (string)($data['vnp_Amount'] ?? '')!==(string)((int)$payment['amount']*100)) {echo '{"status":"not_found"}';exit;}
    $status=$payment['status']==='PAID' && $payment['invoice_id'] ? 'confirmed' : strtolower($payment['status']);
    echo json_encode(['status'=>$status]);
} catch(Throwable $e) { http_response_code(503);echo '{"status":"unavailable"}'; }
