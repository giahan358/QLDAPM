<?php
declare(strict_types=1);
ini_set('display_errors','0');
require_once __DIR__.'/../../model/connectDB.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
try {
    session_start();$customer=(int)($_SESSION['idkhachhang'] ?? 0);
    if(!$customer){http_response_code(401);throw new DomainException('Vui lòng đăng nhập.');}
    session_write_close();
    $db=connectdb();$db->exec("SET time_zone='+07:00'");
    $q=$db->prepare("SELECT c.id,c.amount,c.status,c.created_at,c.items,p.attempt_no,p.response_code,p.expire_date,(p.expire_date<=DATE_FORMAT(NOW(),'%Y%m%d%H%i%s')) AS expired FROM checkout_sessions c JOIN checkout_payment_attempts p ON p.checkout_id=c.id AND p.attempt_no=(SELECT MAX(a.attempt_no) FROM checkout_payment_attempts a WHERE a.checkout_id=c.id) WHERE c.customer_id=? AND c.method='vnpay' AND c.invoice_id IS NULL ORDER BY c.id DESC LIMIT 100");
    $q->execute([$customer]);$rows=$q->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as &$row){$items=json_decode($row['items'],true);$row['quantity']=array_sum(array_column($items,'quantity'));unset($row['items']);}unset($row);
    echo json_encode(['status'=>'success','data'=>$rows],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
}catch(DomainException $e){echo json_encode(['status'=>'error','message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
catch(Throwable $e){http_response_code(500);error_log('Payment history: '.get_class($e));echo json_encode(['status'=>'error','message'=>'Không tải được lịch sử thanh toán.'],JSON_UNESCAPED_UNICODE);}
