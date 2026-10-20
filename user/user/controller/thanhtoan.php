<?php
declare(strict_types=1);
ini_set('display_errors', '0');
require_once __DIR__ . '/../../model/connectDB.php';
require_once dirname(__DIR__, 3) . '/payment/private/bootstrap.php';
require_once dirname(__DIR__, 3) . '/payment/private/StoreCheckout.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); throw new DomainException('Chỉ chấp nhận POST.'); }
    session_start();
    $customer=(int)($_SESSION['idkhachhang'] ?? 0);
    if (!$customer) { http_response_code(401); throw new DomainException('Vui lòng đăng nhập để đặt hàng.'); }
    $input = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === 0
        ? json_decode(file_get_contents('php://input'), true, 16, JSON_THROW_ON_ERROR) : $_POST;
    if (!is_array($input)) throw new DomainException('Dữ liệu không hợp lệ.');
    $token=$input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || empty($_SESSION['payment_csrf']) || !hash_equals($_SESSION['payment_csrf'], $token)) {
        http_response_code(403); throw new DomainException('Phiên thanh toán đã hết hạn. Vui lòng tải lại trang.');
    }
    session_write_close();
    if (($input['action'] ?? '')==='check-payment') {
        require_once dirname(__DIR__,3).'/payment/private/StoreReconciler.php';
        $id=filter_var($input['checkout_id'] ?? null,FILTER_VALIDATE_INT);
        $db=connectdb();$q=$db->prepare('SELECT p.id FROM checkout_payment_attempts p JOIN checkout_sessions c ON c.id=p.checkout_id WHERE c.id=? AND c.customer_id=? ORDER BY p.attempt_no DESC LIMIT 1');$q->execute([$id,$customer]);
        $attempt=$q->fetchColumn();if(!$attempt)throw new DomainException('Không tìm thấy giao dịch.');
        $result=(new StoreReconciler($db,paymentGateway(paymentConfig())))->run((int)$attempt);
        echo json_encode(['status'=>'success','retry_after'=>$result['retry_after'] ?? 0,'message'=>in_array($result['RspCode'],['00','02'],true)?'Đã kiểm tra trạng thái thanh toán.':'Chưa có kết quả cuối cùng. Hệ thống sẽ tự kiểm tra lại.'],JSON_UNESCAPED_UNICODE);exit;
    }
    if (($input['action'] ?? '')==='retry') {
        $id=filter_var($input['checkout_id'] ?? null,FILTER_VALIDATE_INT);
        if(!$id || $id<1) throw new DomainException('Mã đơn thanh toán không hợp lệ.');
        echo json_encode((new StoreCheckout(connectdb(),paymentGateway(paymentConfig())))->retry($customer,$id),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        exit;
    }
    $method=$input['phuongthuctt'] ?? '';
    if (!is_string($method)) throw new DomainException('Phương thức thanh toán không hợp lệ.');
    $method=['Thanh toán khi nhận hàng'=>'cod','Chuyển khoản ngân hàng'=>'bank','VNPAY'=>'vnpay'][$method] ?? $method;
    $key=$input['request_key'] ?? '';
    if (!is_string($key)) throw new DomainException('Yêu cầu không hợp lệ.');
    $address=filter_var($input['iddiachi'] ?? null, FILTER_VALIDATE_INT);
    if (!$address) throw new DomainException('Vui lòng chọn địa chỉ nhận hàng.');
    $gateway=$method==='vnpay' ? paymentGateway(paymentConfig()) : null;
    $service=new StoreCheckout(connectdb(), $gateway);
    $buyNow=null;
    if (isset($input['buy_now_book_id'])) {
        $book=filter_var($input['buy_now_book_id'],FILTER_VALIDATE_INT);
        $quantity=filter_var($input['buy_now_quantity'] ?? null,FILTER_VALIDATE_INT);
        if (!$book || $book<1 || !$quantity || $quantity<1) throw new DomainException('Sản phẩm mua ngay không hợp lệ.');
        $buyNow=['book_id'=>$book,'quantity'=>$quantity];
    }
    // Customer, amount, date and invoice status never come from browser-submitted values.
    echo json_encode($service->place($customer,$address,$method,$key,$buyNow),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
} catch (DomainException $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('UniBook checkout failed: '.get_class($e));
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Không thể khởi tạo đơn hàng hoặc thanh toán. Vui lòng thử lại; giỏ hàng được giữ nguyên.'],JSON_UNESCAPED_UNICODE);
}
