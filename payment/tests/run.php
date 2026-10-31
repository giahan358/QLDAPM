<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/private/bootstrap.php';

use UniBook\Payment\Gateway;
use UniBook\Payment\PaymentService;
use UniBook\Payment\PaymentError;

$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    $checks++;
}
function rejected(callable $fn, int $status = 409): void {
    try { $fn(); } catch (PaymentError $e) { check($e->httpStatus === $status, 'Expected HTTP ' . $status); return; }
    throw new RuntimeException('Expected rejection');
}
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys=ON');
// Reuse the production schema, adapting only MySQL-specific DDL for SQLite.
$sql = file_get_contents(dirname(__DIR__) . '/private/schema.sql');
$sql = str_replace('BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
$sql = str_replace(['CHARACTER SET ascii COLLATE ascii_bin', ' ENGINE=InnoDB'], '', $sql);
$sql = preg_replace('/ENUM\([^)]*\)/', 'TEXT', $sql);
$sql = preg_replace('/^\s*INDEX [^\n]+\n/m', '', $sql);
$sql = preg_replace('/UNIQUE KEY \w+\s*\(/', 'UNIQUE (', $sql);
$sql = preg_replace('/,\s*\)/', ')', $sql);
$db->exec($sql);
$gateway = new Gateway('TESTCODE', 'unit-test-secret', 'https://shop.example/payment/vnpay-return', '127.0.0.1');
$service = new PaymentService($db, $gateway);
function row(string $table, int $id): array {
    global $db;
    return $db->query("SELECT * FROM $table WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
}
function order(): int {
    global $db;
    $q = $db->prepare('INSERT INTO orders(code,customer_id,amount,created_at,updated_at) VALUES(?,7,100000,?,?)');
    $now = Gateway::now()->format('Y-m-d H:i:s');
    $q->execute([bin2hex(random_bytes(8)), $now, $now]);
    return (int)$db->lastInsertId();
}
function keyForTest(): string { return bin2hex(random_bytes(16)); }
function callback(array $payment, string $code = '00', string $status = '00', array $overrides = []): string {
    global $gateway;
    $data = array_merge(['vnp_TmnCode' => 'TESTCODE', 'vnp_TxnRef' => $payment['txn_ref'],
        'vnp_Amount' => '10000000', 'vnp_ResponseCode' => $code, 'vnp_TransactionStatus' => $status,
        'vnp_TransactionNo' => (string)(1000000 + $payment['payment_id']), 'vnp_BankCode' => 'NCB',
        'vnp_PayDate' => Gateway::now()->format('YmdHis')], $overrides);
    return Gateway::canonical($data) . '&vnp_SecureHash=' . $gateway->sign($data);
}
function paid(): array {
    global $service;
    $id = order();
    $p = $service->createUrl($id, 7, keyForTest(), '127.0.0.1');
    check($service->ipn(callback($p))['RspCode'] === '00', 'Payment success');
    return [$id, $p];
}
function refundResponse(array $request, string $status = '00', string $code = '00'): array {
    global $gateway;
    $r = ['vnp_ResponseId' => bin2hex(random_bytes(16)), 'vnp_Command' => 'refund',
        'vnp_ResponseCode' => $code, 'vnp_Message' => 'Result', 'vnp_TmnCode' => $request['vnp_TmnCode'],
        'vnp_TxnRef' => $request['vnp_TxnRef'], 'vnp_Amount' => $request['vnp_Amount'],
        'vnp_BankCode' => 'NCB', 'vnp_PayDate' => Gateway::now()->format('YmdHis'),
        'vnp_TransactionNo' => '9000001', 'vnp_TransactionType' => $request['vnp_TransactionType'],
        'vnp_TransactionStatus' => $status, 'vnp_OrderInfo' => $request['vnp_OrderInfo']];
    $r['vnp_SecureHash'] = $gateway->pipeHash($r, Gateway::RESPONSE_FIELDS);
    return $r;
}

$params = ['vnp_TmnCode' => 'TESTCODE', 'vnp_OrderInfo' => 'Thanh toan + & đ', 'vnp_Amount' => '1000000'];
check(Gateway::canonical($params) === 'vnp_Amount=1000000&vnp_OrderInfo=Thanh+toan+%2B+%26+%C4%91&vnp_TmnCode=TESTCODE', 'Alphabet and RFC1738 encoding');
// Independent Node crypto reference vector, not a sign/verify round-trip alone.
check($gateway->sign($params) === '0657cb7e12f109eec6e2ddc55d898c7782e74a5dcda70b31a40387b8237c62691eb762071cb38d0cbdfb5876b2e1ebf982290a1f53e889cf71ebc00937440576', 'HMAC vector');
foreach ([0, -1, '1.5', 1.5, '1e5', '10000000000', [], null] as $amount) {
    try { Gateway::amount($amount); throw new RuntimeException('Invalid money accepted'); }
    catch (InvalidArgumentException $e) { check(true, 'Invalid amount rejected'); }
}
$id = order(); $key = keyForTest();
$p = $service->createUrl($id, 7, $key, '127.0.0.1');
check($p === $service->createUrl($id, 7, $key, '127.0.0.1'), 'Create replay returns same URL');
check((int)row('orders', $id)['attempt_count'] === 1, 'No duplicate attempt');
rejected(fn() => $service->createUrl($id, 8, keyForTest(), '127.0.0.1'), 404);
rejected(fn() => $service->createUrl($id, 7, keyForTest(), '127.0.0.1'));
$unsigned = callback($p);
check($service->ipn(str_replace('10000000', '10000100', $unsigned))['RspCode'] === '97', 'Tampering rejected');
check($service->ipn($unsigned . '&vnp_Amount=10000000')['RspCode'] === '97', 'Duplicate query key rejected');
check($gateway->verifiedQuery('vnp_Amount[]=1&vnp_SecureHash=' . str_repeat('a', 128)) === null, 'Arrays rejected');
check($service->ipn(callback($p, '00', '00', ['vnp_TmnCode' => 'OTHERCOD']))['RspCode'] === '01', 'Wrong merchant');
check($service->ipn(callback($p, '00', '00', ['vnp_TxnRef' => 'missing']))['RspCode'] === '01', 'Unknown reference');
check($service->ipn(callback($p, '00', '00', ['vnp_Amount' => '1']))['RspCode'] === '04', 'Signed wrong amount');
check(row('orders', $id)['status'] === 'PENDING', 'Invalid IPN cannot mutate order');
check($service->ipn($unsigned)['RspCode'] === '00', 'Success acknowledged');
check($service->ipn($unsigned)['RspCode'] === '02', 'Duplicate acknowledged without reprocessing');
check(row('orders', $id)['status'] === 'PAID', 'Order paid');
check($service->ipn(callback($p, '24', '02'))['RspCode'] === '02', 'Conflicting terminal callback does not overwrite');
check(row('orders', $id)['status'] === 'PAID' && (int)row('orders', $id)['review_required'] === 1, 'Conflicting result flagged');

$id = order(); $previous = null;
for ($i = 0; $i < 6; $i++) {
    $p = $service->createUrl($id, 7, keyForTest(), '127.0.0.1');
    if ($previous) check($p['txn_ref'] !== $previous, 'Retry has new reference');
    $previous = $p['txn_ref'];
    check($service->ipn(callback($p, '24', '02'))['RspCode'] === '00', 'Cancel payment acknowledged');
    check(row('orders', $id)['status'] === 'FAILED', 'Payment cancellation permits retry');
}
rejected(fn() => $service->createUrl($id, 7, keyForTest(), '127.0.0.1'), 429);
foreach ([['00', '02'], ['51', '00'], ['11', '02'], ['07', '07']] as [$code, $status]) {
    $id = order(); $p = $service->createUrl($id, 7, keyForTest(), '127.0.0.1');
    $service->ipn(callback($p, $code, $status));
    check(row('orders', $id)['status'] === 'FAILED', 'Both success codes required');
}
$id = order(); $p = $service->createUrl($id, 7, keyForTest(), '127.0.0.1');
$db->exec("UPDATE payments SET expire_date='20000101000000' WHERE id=" . $p['payment_id']);
rejected(fn() => $service->createUrl($id, 7, keyForTest(), '127.0.0.1'));
check($service->ipn(callback($p))['RspCode'] === '00', 'Valid delayed IPN after expiry still accepted');

$id = order(); $p = $service->createUrl($id, 7, keyForTest(), '127.0.0.1');
$db->exec("UPDATE orders SET status='CANCELLED' WHERE id=$id");
$service->ipn(callback($p));
check(row('orders', $id)['status'] === 'CANCELLED' && row('payments', $p['payment_id'])['status'] === 'PAID', 'Late success records money without resurrecting cancellation');

[$id, $p] = paid(); $key = keyForTest(); $calls = 0;
$transport = function (array $request) use (&$calls): array { $calls++; return refundResponse($request); };
$r = $service->refund($p['payment_id'], 30000, $key, 'operator1', $transport);
check($r['status'] === 'SUCCEEDED' && row('orders', $id)['status'] === 'PAID', 'Partial refund preserves PAID');
check((int)row('orders', $id)['refunded_amount'] === 30000, 'Partial amount tracked');
$service->refund($p['payment_id'], 30000, $key, 'operator1', $transport);
check($calls === 1, 'Refund idempotency prevents second network call');
rejected(fn() => $service->refund($p['payment_id'], 40000, $key, 'operator1', $transport));
rejected(fn() => $service->refund($p['payment_id'], 70001, keyForTest(), 'operator1', $transport), 422);
$r = $service->refund($p['payment_id'], 70000, keyForTest(), 'operator1', $transport);
check(row('orders', $id)['status'] === 'REFUNDED', 'Cumulative full refund');
check(row('refunds', $r['refund_id'])['transaction_type'] === '03', 'Remainder after partial uses type 03');

foreach (['05' => 'PROCESSING', '06' => 'PROCESSING', '07' => 'REVIEW', '09' => 'REJECTED'] as $status => $expected) {
    [$id, $p] = paid();
    $r = $service->refund($p['payment_id'], 100000, keyForTest(), 'operator1', fn($req) => refundResponse($req, (string)$status));
    check($r['status'] === $expected, 'Refund status ' . $status);
    check(row('orders', $id)['status'] === 'PAID', 'Pending/rejected refund is not REFUNDED');
    check((int)row('orders', $id)['refunded_amount'] === 0, 'No unconfirmed refund counted');
}
[$id, $p] = paid(); $request = null;
$r = $service->refund($p['payment_id'], 100000, keyForTest(), 'operator1', function ($req) use (&$request) { $request = $req; return refundResponse($req, '06'); });
$response = refundResponse($request);
$service->reconcileRefund($r['refund_id'], $response);
$service->reconcileRefund($r['refund_id'], $response);
check((int)row('orders', $id)['refunded_amount'] === 100000, 'Reconciliation idempotent');
check($request['vnp_TransactionDate'] === row('payments', $p['payment_id'])['create_date'], 'Refund uses original create date');

foreach (['timeout', 'signature', 'binding'] as $failure) {
    [$id, $p] = paid();
    $r = $service->refund($p['payment_id'], 100000, keyForTest(), 'operator1', function ($req) use ($failure, $gateway) {
        if ($failure === 'timeout') throw new RuntimeException('Simulated timeout');
        $response = refundResponse($req);
        if ($failure === 'signature') $response['vnp_SecureHash'] = str_repeat('0', 128);
        else { $response['vnp_OrderInfo'] = 'Another request'; $response['vnp_SecureHash'] = $gateway->pipeHash($response, Gateway::RESPONSE_FIELDS); }
        return $response;
    });
    check($r['status'] === 'UNKNOWN' && (int)row('orders', $id)['refund_hold'] === 1, 'Ambiguous refund holds reservation');
    rejected(fn() => $service->refund($p['payment_id'], 100000, keyForTest(), 'operator1', $transport));
}
[$id, $p] = paid();
$db->exec("UPDATE orders SET fulfillment_status='SHIPPED' WHERE id=$id");
rejected(fn() => $service->refund($p['payment_id'], 100000, keyForTest(), 'operator1', $transport));
$db->exec("UPDATE orders SET fulfillment_status='PENDING' WHERE id=$id");
$db->exec("UPDATE payments SET pay_date='20000101000000' WHERE id=" . $p['payment_id']);
rejected(fn() => $service->refund($p['payment_id'], 100000, keyForTest(), 'operator1', $transport));

$id = order(); $p = $service->createUrl($id, 7, keyForTest(), '127.0.0.1');
$db->exec("CREATE TRIGGER fail_log BEFORE INSERT ON payment_logs BEGIN SELECT RAISE(ABORT,'test failure'); END");
check($service->ipn(callback($p))['RspCode'] === '99', 'DB failure requests IPN retry');
check(row('orders', $id)['status'] === 'PENDING' && row('payments', $p['payment_id'])['confirmed_at'] === null, 'Atomic rollback');
$db->exec('DROP TRIGGER fail_log');
check($service->ipn(callback($p))['RspCode'] === '00', 'Retry after DB recovery succeeds');
check(strpos(json_encode($db->query('SELECT * FROM payment_logs')->fetchAll()), 'unit-test-secret') === false, 'No secret in audit records');

echo "PASS: $checks checks (SQLite isolated; no live gateway or existing database touched).\n";
