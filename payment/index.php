<?php
declare(strict_types=1);

use UniBook\Payment\Gateway;
use UniBook\Payment\PaymentError;

ini_set('display_errors', '0');
$storefrontRoute = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($storefrontRoute === 'create-url') {
    require dirname(__DIR__) . '/user/user/controller/thanhtoan.php';
    exit;
}
if ($storefrontRoute === 'vnpay-ipn') {
    $_GET['action'] = 'ipn';
    require dirname(__DIR__) . '/user/user/controller/vnpay.php';
    exit;
}
require __DIR__ . '/private/bootstrap.php';
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

function paymentJson(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

$route = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$method = $_SERVER['REQUEST_METHOD'];
try {
    $methods = ['create-url' => 'POST', 'vnpay-return' => 'GET', 'vnpay-ipn' => 'GET', 'refund' => 'POST', 'csrf' => 'GET'];
    if (!isset($methods[$route])) throw new PaymentError('Không tìm thấy endpoint.', 404);
    if ($method !== $methods[$route]) {
        header('Allow: ' . $methods[$route]);
        throw new PaymentError('Phương thức không được hỗ trợ.', 405);
    }
    $config = paymentConfig();
    $gateway = paymentGateway($config);

    if ($route === 'vnpay-return') {
        // Deliberately no PDO connection, session mutation, or order write in Return.
        $data = $gateway->verifiedQuery($_SERVER['QUERY_STRING'] ?? '');
        $valid = $data !== null && ($data['vnp_TmnCode'] ?? '') === $gateway->tmnCode;
        $success = $valid && ($data['vnp_ResponseCode'] ?? '') === '00' && ($data['vnp_TransactionStatus'] ?? '') === '00';
        $title = $success ? 'VNPAY báo thanh toán thành công' : ($valid ? 'Thanh toán chưa thành công' : 'Không xác minh được kết quả');
        $message = $success ? 'UniBook sẽ xác nhận đơn hàng sau khi nhận thông báo từ VNPAY. Bạn không cần thanh toán lại.' :
            ($valid ? Gateway::paymentMessage($data['vnp_ResponseCode'] ?? '')[1] : 'Thông tin trả về không hợp lệ. Vui lòng kiểm tra đơn hàng hoặc liên hệ hỗ trợ.');
        if (!$success && $valid && ($data['vnp_ResponseCode'] ?? '') === '00') {
            $message = 'Giao dịch chưa có trạng thái thành công. Vui lòng chờ xác nhận từ hệ thống.';
        }
        http_response_code($valid ? 200 : 400);
        header('Content-Type: text/html; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; style-src 'self'; script-src 'self'; connect-src 'self'; base-uri 'none'; frame-ancestors 'none'");
        $escape = static function (string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
        require __DIR__ . '/private/return.php';
        exit;
    }
    if ($route === 'vnpay-ipn') {
        // Verify BEFORE opening a database connection. Service verifies again for direct callers.
        if ($gateway->verifiedQuery($_SERVER['QUERY_STRING'] ?? '') === null) {
            error_log('UniBook payment: IPN_INVALID_SIGNATURE');
            paymentJson(['RspCode' => '97', 'Message' => 'Invalid signature']);
        } else {
            paymentJson(paymentService($config, $gateway)->ipn($_SERVER['QUERY_STRING'] ?? ''));
        }
        exit;
    }

    if ($route === 'refund') {
        // Dedicated server-side operator API; never trust a customer-provided role or user ID.
        $expected = $config['PAYMENT_ADMIN_KEY'] ?? '';
        $provided = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (strlen($expected) < 64 || !hash_equals('Bearer ' . $expected, $provided)) {
            throw new PaymentError('Không có quyền hoàn tiền.', 403);
        }
        $customerId = null;
    } else {
        session_start();
        $customerId = filter_var($_SESSION['idkhachhang'] ?? null, FILTER_VALIDATE_INT);
        if (!$customerId || $customerId < 1) throw new PaymentError('Vui lòng đăng nhập.', 401);
        if ($route === 'csrf') {
            if (empty($_SESSION['payment_csrf'])) $_SESSION['payment_csrf'] = bin2hex(random_bytes(32));
            paymentJson(['csrf_token' => $_SESSION['payment_csrf']]);
            session_write_close();
            exit;
        }
        if (empty($_SESSION['payment_csrf']) ||
            !hash_equals($_SESSION['payment_csrf'], $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
            throw new PaymentError('Phiên yêu cầu không hợp lệ.', 403);
        }
        session_write_close();
    }
    if (strpos(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== 0) {
        throw new PaymentError('Yêu cầu phải là JSON.', 415);
    }
    $raw = file_get_contents('php://input', false, null, 0, 16385);
    if (strlen($raw) > 16384) throw new PaymentError('Yêu cầu quá lớn.', 413);
    $body = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
    if (!is_array($body)) throw new PaymentError('JSON không hợp lệ.', 422);
    $key = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? '';
    $service = paymentService($config, $gateway);
    if ($route === 'create-url') {
        $id = filter_var($body['order_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) throw new PaymentError('order_id không hợp lệ.', 422);
        // REMOTE_ADDR only. Configure trusted proxies at the server, not from arbitrary XFF.
        paymentJson($service->createUrl($id, $customerId, $key, $_SERVER['REMOTE_ADDR']));
    } else {
        $id = filter_var($body['payment_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) throw new PaymentError('payment_id không hợp lệ.', 422);
        $result = $service->refund($id, Gateway::amount($body['amount'] ?? null), $key, 'api-operator');
        paymentJson($result, in_array($result['status'], ['PROCESSING', 'UNKNOWN', 'REVIEW'], true) ? 202 : 200);
    }
} catch (Throwable $e) {
    if ($route === 'vnpay-ipn' && $method === 'GET') {
        error_log('UniBook IPN endpoint: ' . get_class($e));
        paymentJson(['RspCode' => '99', 'Message' => 'Unknown error']);
    } elseif ($e instanceof PaymentError) {
        paymentJson(['error' => $e->getMessage()], $e->httpStatus);
    } elseif ($e instanceof InvalidArgumentException || $e instanceof JsonException) {
        paymentJson(['error' => 'Dữ liệu yêu cầu không hợp lệ.'], 422);
    } else {
        error_log('UniBook payment endpoint: ' . get_class($e));
        paymentJson(['error' => 'Dịch vụ thanh toán tạm thời chưa sẵn sàng.'], 503);
    }
}
