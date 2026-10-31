<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/private/bootstrap.php';
use UniBook\Payment\Gateway;
$gateway = new Gateway('TESTCODE', 'unit-test-secret', 'https://shop.example/payment/vnpay-return', '127.0.0.1');
$entry = dirname(__DIR__) . '/index.php';
function request(string $route, string $query): string {
    global $entry;
    $code = '$_SERVER=' . var_export(['REQUEST_URI' => '/payment/' . $route, 'REQUEST_METHOD' => 'GET', 'QUERY_STRING' => $query], true) . '; require ' . var_export($entry, true) . ';';
    // The intentionally unavailable DSN proves Return does not require a DB.
    $env = array_merge(getenv(), ['vnp_TmnCode' => 'TESTCODE', 'vnp_HashSecret' => 'unit-test-secret',
        'VNP_RETURN_URL' => 'https://shop.example/payment/vnpay-return', 'VNP_SERVER_IP' => '127.0.0.1',
        'PAYMENT_DB_DSN' => 'unavailable:', 'PAYMENT_DB_USER' => '', 'PAYMENT_DB_PASSWORD' => '']);
    $process = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes, null, $env);
    if (!is_resource($process)) throw new RuntimeException('Cannot start endpoint test');
    $result = stream_get_contents($pipes[1]); stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
    return $result;
}
function expect(bool $ok): void { if (!$ok) throw new RuntimeException('Endpoint assertion failed'); }
$params = ['vnp_TmnCode' => 'TESTCODE', 'vnp_TxnRef' => '<script>alert(1)</script>', 'vnp_ResponseCode' => '00', 'vnp_TransactionStatus' => '00'];
$query = Gateway::canonical($params) . '&vnp_SecureHash=' . $gateway->sign($params);
$page = request('vnpay-return', $query);
expect(strpos($page, 'VNPAY báo thanh toán thành công') !== false);
expect(strpos($page, '<script>') === false && strpos($page, '&lt;script&gt;') !== false);
$page = request('vnpay-return', $query . 'wrong');
expect(strpos($page, 'Không xác minh được kết quả') !== false);
expect(json_decode(request('vnpay-ipn', 'vnp_SecureHash=wrong'), true)['RspCode'] === '97');
expect(json_decode(request('vnpay-ipn', $query), true)['RspCode'] === '01');
echo "PASS: 5 endpoint checks (Return without DB, escaping, invalid signature, IPN verification before DB).\n";
