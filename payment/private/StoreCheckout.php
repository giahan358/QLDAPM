<?php
declare(strict_types=1);

use UniBook\Payment\Gateway;

/** All invoice, detail, inventory and cart mutations use ONE PDO transaction. */
final class StoreCheckout
{
    private PDO $db;
    private ?Gateway $gateway;
    public function __construct(PDO $db, ?Gateway $gateway = null) {
        $this->db = $db;
        $this->gateway = $gateway;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->exec("SET time_zone = '+07:00'");
    }
    private function query(string $sql, array $params = []): PDOStatement {
        $q = $this->db->prepare($sql); $q->execute($params); return $q;
    }
    private function url(array $attempt): string {
        if (!$this->gateway) throw new RuntimeException('VNPAY chưa được cấu hình.');
        return $this->gateway->paymentUrl([
            'amount' => (int)$attempt['amount'], 'order_id' => $attempt['checkout_id'],
            'vnp_txn_ref' => $attempt['txn_ref'], 'create_date' => $attempt['create_date'],
            'expire_date' => $attempt['expire_date']
        ], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }
    private function respond(array $checkout): array {
        if ($checkout['invoice_id']) return ['status'=>'success','idhoadon'=>(int)$checkout['invoice_id']];
        $attempt = $this->query('SELECT * FROM checkout_payment_attempts WHERE checkout_id=? ORDER BY attempt_no DESC LIMIT 1', [$checkout['id']])->fetch(PDO::FETCH_ASSOC);
        if ($attempt && $attempt['status'] === 'PENDING') {
            if ($attempt['expire_date'] <= Gateway::now()->format('YmdHis')) {
                throw new DomainException('Liên kết thanh toán đã hết hạn. Bạn có thể tạo đơn mới nếu chưa bị trừ tiền; nếu đã bị trừ tiền, hãy kiểm tra kết quả giao dịch cũ trước.');
            }
            return ['status'=>'success','checkout_id'=>(int)$checkout['id'],'redirectUrl'=>$this->url($attempt)];
        }
        throw new DomainException('Giao dịch đã kết thúc. Vui lòng tải lại trang để thanh toán lại.');
    }
    public function place(int $customer, int $address, string $method, string $key, ?array $buyNow = null): array {
        if ($buyNow !== null && ((int)($buyNow['book_id'] ?? 0)<1 || (int)($buyNow['quantity'] ?? 0)<1))
            throw new DomainException('Sản phẩm mua ngay không hợp lệ.');
        if (!in_array($method, ['cod','vnpay'], true) || $address < 1 ||
            !preg_match('/^[a-f0-9]{32,64}$/D', $key)) throw new DomainException('Thông tin đặt hàng không hợp lệ.');
        // Validate gateway configuration BEFORE any checkout/invoice write.
        if ($method === 'vnpay' && !$this->gateway) throw new DomainException('Chưa cấu hình VNPAY Sandbox.');
        $this->db->beginTransaction();
        try {
            if (!$this->query('SELECT idkhachhang FROM khachhang WHERE idkhachhang=? FOR UPDATE', [$customer])->fetchColumn())
                throw new DomainException('Tài khoản không tồn tại.');
            $old = $this->query('SELECT * FROM checkout_sessions WHERE customer_id=? AND request_key=? FOR UPDATE', [$customer,$key])->fetch(PDO::FETCH_ASSOC);
            if ($old) {
                if ($old['method'] !== $method || (int)$old['address_id'] !== $address) throw new DomainException('Yêu cầu đã được sử dụng. Vui lòng tải lại trang.');
                $result = $this->respond($old); $this->db->commit(); return $result;
            }
            $shipping = $this->query('SELECT * FROM thongtinnhanhang WHERE iddiachi=? AND idkhachhang=?', [$address,$customer])->fetch(PDO::FETCH_ASSOC);
            if (!$shipping) throw new DomainException('Địa chỉ nhận hàng không thuộc tài khoản của bạn.');
            if ($buyNow !== null) {
                // Direct purchase has its own snapshot and never consumes existing cart rows.
                $cart = $this->query('SELECT 0 AS idgiohang,idsach,? AS soluong,gia,sltonkho,trangthai FROM sach WHERE idsach=? FOR UPDATE', [(int)$buyNow['quantity'],(int)$buyNow['book_id']])->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $cart = $this->query('SELECT g.idgiohang,g.idsach,g.soluong,s.gia,s.sltonkho,s.trangthai FROM giohang g JOIN sach s ON s.idsach=g.idsach WHERE g.idkhachhang=? ORDER BY g.idsach,g.idgiohang FOR UPDATE', [$customer])->fetchAll(PDO::FETCH_ASSOC);
            }
            if (!$cart) throw new DomainException('Giỏ hàng trống.');
            $items = []; $amount = 0; $quantities = [];
            foreach ($cart as $row) {
                $qty = (int)$row['soluong']; $id = (int)$row['idsach'];
                if ($qty < 1 || (int)$row['trangthai'] !== 1 || !preg_match('/^[0-9]+(?:\.00)?$/D', (string)$row['gia']))
                    throw new DomainException('Sản phẩm hoặc giá bán không hợp lệ.');
                $quantities[$id] = ($quantities[$id] ?? 0) + $qty;
                if ($quantities[$id] > (int)$row['sltonkho']) throw new DomainException('Sản phẩm không đủ tồn kho. Vui lòng cập nhật giỏ hàng.');
                $price = (int)$row['gia'];
                if ($price < 1) throw new DomainException('Giá bán không hợp lệ.');
                $amount += $price * $qty;
                $items[] = ['cart_id'=>(int)$row['idgiohang'],'book_id'=>$id,'quantity'=>$qty,'price'=>$price];
            }
            if ($amount < 1 || $amount > 99999999) throw new DomainException('Tổng tiền vượt giới hạn cho phép.');
            $json = json_encode($items, JSON_THROW_ON_ERROR);
            // Only an active payment link or a transaction under review blocks new checkout.
            // Expiry releases this UI restriction, not the financial outcome: late IPNs still settle.
            $pending = $this->query("SELECT c.* FROM checkout_sessions c WHERE c.customer_id=? AND c.method='vnpay' AND (c.status='REVIEW' OR (c.status='PENDING' AND EXISTS (SELECT 1 FROM checkout_payment_attempts p WHERE p.checkout_id=c.id AND p.attempt_no=(SELECT MAX(a.attempt_no) FROM checkout_payment_attempts a WHERE a.checkout_id=c.id) AND p.expire_date>DATE_FORMAT(NOW(),'%Y%m%d%H%i%s')))) ORDER BY c.id DESC LIMIT 1 FOR UPDATE", [$customer])->fetch(PDO::FETCH_ASSOC);
            if ($pending) {
                if ($pending['status'] === 'PENDING' && $method === 'vnpay' && $pending['items'] === $json && (int)$pending['address_id'] === $address) {
                    $result=$this->respond($pending); $this->db->commit(); return $result;
                }
                throw new DomainException('Bạn còn giao dịch VNPAY chờ xác nhận. Vui lòng kiểm tra trước khi tạo đơn mới.');
            }
            $checkout = false;
            if ($method === 'vnpay') {
                $checkout = $this->query("SELECT * FROM checkout_sessions WHERE customer_id=? AND address_id=? AND method='vnpay' AND status='FAILED' AND items=? ORDER BY id DESC LIMIT 1 FOR UPDATE", [$customer,$address,$json])->fetch(PDO::FETCH_ASSOC);
            }
            if (!$checkout) {
                $this->query('INSERT INTO checkout_sessions(customer_id,address_id,request_key,method,amount,items,address_snapshot,created_at) VALUES(?,?,?,?,?,?,?,NOW())', [$customer,$address,$key,$method,$amount,$json,json_encode($shipping, JSON_THROW_ON_ERROR)]);
                $checkout = ['id'=>(int)$this->db->lastInsertId(),'customer_id'=>$customer,'address_id'=>$address,'method'=>$method,'amount'=>$amount,'items'=>$json];
            }
            if ($method === 'vnpay') {
                $number = 1 + (int)$this->query('SELECT COUNT(*) FROM checkout_payment_attempts WHERE checkout_id=?', [$checkout['id']])->fetchColumn();
                if ($number > 6) throw new DomainException('Đã hết 5 lần thanh toán lại cho giỏ hàng này. Vui lòng liên hệ cửa hàng.');
                $now = Gateway::now();
                $attempt = ['checkout_id'=>$checkout['id'],'amount'=>$amount,'txn_ref'=>'UB'.$now->format('YmdHis').bin2hex(random_bytes(8)), 'create_date'=>$now->format('YmdHis'),'expire_date'=>$now->modify('+15 minutes')->format('YmdHis')];
                $url = $this->url($attempt);
                $this->query('INSERT INTO checkout_payment_attempts(checkout_id,attempt_no,txn_ref,amount,create_date,expire_date,created_at) VALUES(?,?,?,?,?,?,NOW())', [$checkout['id'],$number,$attempt['txn_ref'],$amount,$attempt['create_date'],$attempt['expire_date']]);
                $this->query("UPDATE checkout_sessions SET status='PENDING',request_key=? WHERE id=?", [$key,$checkout['id']]);
                $result = ['status'=>'success','checkout_id'=>$checkout['id'],'redirectUrl'=>$url];
            } else {
                $invoice = $this->invoice($checkout, false);
                $this->query('UPDATE checkout_sessions SET invoice_id=? WHERE id=?', [$invoice,$checkout['id']]);
                $result = ['status'=>'success','idhoadon'=>$invoice,'message'=>'Đơn hàng đã được ghi nhận, đang chờ thu tiền.'];
            }
            $this->db->commit(); return $result;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }
    /** Retry the original checkout snapshot, independently of the current cart. */
    public function retry(int $customer, int $id): array {
        if (!$this->gateway) throw new DomainException('Chưa cấu hình VNPAY.');
        $this->db->beginTransaction();
        try {
            $this->query('SELECT idkhachhang FROM khachhang WHERE idkhachhang=? FOR UPDATE',[$customer]);
            $checkout=$this->query('SELECT * FROM checkout_sessions WHERE id=? AND customer_id=? FOR UPDATE',[$id,$customer])->fetch(PDO::FETCH_ASSOC);
            if (!$checkout || $checkout['method']!=='vnpay') throw new DomainException('Không tìm thấy đơn thanh toán.');
            if ($checkout['invoice_id'] || $checkout['status']==='PENDING') {
                $result=$this->respond($checkout);$this->db->commit();return $result;
            }
            if ($checkout['status']!=='FAILED') throw new DomainException('Giao dịch đang cần xác minh, chưa thể thanh toán lại.');
            if ($this->query("SELECT c.id FROM checkout_sessions c WHERE c.customer_id=? AND c.method='vnpay' AND c.id<>? AND (c.status='REVIEW' OR (c.status='PENDING' AND EXISTS (SELECT 1 FROM checkout_payment_attempts p WHERE p.checkout_id=c.id AND p.attempt_no=(SELECT MAX(a.attempt_no) FROM checkout_payment_attempts a WHERE a.checkout_id=c.id) AND p.expire_date>DATE_FORMAT(NOW(),'%Y%m%d%H%i%s'))))",[$customer,$id])->fetchColumn())
                throw new DomainException('Bạn còn giao dịch khác chờ xác nhận. Vui lòng kiểm tra trước khi thanh toán lại.');
            $items=json_decode($checkout['items'],true,32,JSON_THROW_ON_ERROR);
            $quantities=[];
            foreach($items as $item) $quantities[$item['book_id']]=($quantities[$item['book_id']] ?? 0)+(int)$item['quantity'];
            ksort($quantities);
            foreach($quantities as $book=>$quantity){
                $row=$this->query('SELECT sltonkho,trangthai FROM sach WHERE idsach=? FOR UPDATE',[$book])->fetch(PDO::FETCH_ASSOC);
                if(!$row || (int)$row['trangthai']!==1 || (int)$row['sltonkho']<$quantity) throw new DomainException('Sản phẩm trong đơn không còn đủ hàng để thanh toán lại.');
            }
            $number=1+(int)$this->query('SELECT COUNT(*) FROM checkout_payment_attempts WHERE checkout_id=?',[$id])->fetchColumn();
            if($number>6) throw new DomainException('Đã hết 5 lần thanh toán lại cho đơn này. Vui lòng liên hệ cửa hàng.');
            $now=Gateway::now();
            $attempt=['checkout_id'=>$id,'amount'=>(int)$checkout['amount'],'txn_ref'=>'UB'.$now->format('YmdHis').bin2hex(random_bytes(8)),'create_date'=>$now->format('YmdHis'),'expire_date'=>$now->modify('+15 minutes')->format('YmdHis')];
            $url=$this->url($attempt);
            $this->query('INSERT INTO checkout_payment_attempts(checkout_id,attempt_no,txn_ref,amount,create_date,expire_date,created_at) VALUES(?,?,?,?,?,?,NOW())',[$id,$number,$attempt['txn_ref'],$attempt['amount'],$attempt['create_date'],$attempt['expire_date']]);
            $this->query("UPDATE checkout_sessions SET status='PENDING' WHERE id=?",[$id]);
            $this->db->commit();return ['status'=>'success','checkout_id'=>$id,'redirectUrl'=>$url];
        } catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    private function invoice(array $checkout, bool $paid): int {
        $items = json_decode($checkout['items'], true, 32, JSON_THROW_ON_ERROR);
        $staff = $this->query("SELECT idnhanvien FROM nhanvien WHERE trangthai=1 AND chucvu='Nhân viên bán hàng' ORDER BY idnhanvien LIMIT 1")->fetchColumn();
        if (!$staff) throw new DomainException('Chưa có nhân viên tiếp nhận đơn hàng.');
        // Reserve sellable stock when the invoice enters waiting confirmation.
        // Conditional updates serialize competing purchases and roll back with the invoice.
        usort($items, static fn(array $a, array $b): int => $a['book_id'] <=> $b['book_id']);
        foreach ($items as $item) {
            $qty = (int)$item['quantity'];
            if ($qty < 1) throw new DomainException('Số lượng sản phẩm không hợp lệ.');
            $q = $this->query('UPDATE sach SET sltonkho=sltonkho-? WHERE idsach=? AND sltonkho>=? AND trangthai=1', [$qty,$item['book_id'],$qty]);
            if ($q->rowCount() !== 1) throw new DomainException('Sản phẩm không còn đủ tồn kho để xác nhận đơn hàng.');
        }
        $label = ['cod'=>'Thanh toán khi nhận hàng','bank'=>'Chuyển khoản ngân hàng','vnpay'=>'VNPAY'][$checkout['method']];
        $this->query('INSERT INTO hoadon(idkhachhang,iddiachi,idnhanvien,phuongthuctt,ngayxuat,tongtien,trangthai,trangthai_thanhtoan,stock_deducted) VALUES(?,?,?,?,CURDATE(),?,0,?,1)', [$checkout['customer_id'],$checkout['address_id'],$staff,$label,$checkout['amount'],$paid?'PAID':'PENDING']);
        $invoice=(int)$this->db->lastInsertId();
        foreach ($items as $item) {
            $this->query('INSERT INTO chitiethoadon(idhoadon,idsach,soluong,gia) VALUES(?,?,?,?)', [$invoice,$item['book_id'],$item['quantity'],$item['price']]);
            // Preserve items added or quantities increased while the customer was at VNPAY.
            $this->query('UPDATE giohang SET soluong=GREATEST(0,soluong-?) WHERE idgiohang=? AND idkhachhang=? AND idsach=?', [$item['quantity'],$item['cart_id'],$checkout['customer_id'],$item['book_id']]);
            $this->query('DELETE FROM giohang WHERE idgiohang=? AND idkhachhang=? AND soluong=0', [$item['cart_id'],$checkout['customer_id']]);
        }
        return $invoice;
    }
    public function ipn(string $raw): array {
        $data = $this->gateway ? $this->gateway->verifiedQuery($raw) : null;
        if ($data === null) return ['RspCode'=>'97','Message'=>'Invalid signature'];
        return $this->confirmPayment($data);
    }
    public function reconcile(array $response, array $request): array {
        if (!$this->gateway || !$this->gateway->verifyQueryResponse($response,$request))
            return ['RspCode'=>'97','Message'=>'Invalid query signature'];
        // Query response code 00 means the QUERY worked, not that payment succeeded.
        // Accept a confirmed success or terminal payment error. Pending/reversal/refund states stay unresolved.
        if (($response['vnp_ResponseCode'] ?? '')!=='00' ||
            ($response['vnp_TransactionType'] ?? '')!=='01' ||
            !in_array($response['vnp_TransactionStatus'] ?? '',['00','02'],true))
            return ['RspCode'=>'99','Message'=>'Payment not confirmed by query'];
        return $this->confirmPayment($response);
    }
    private function confirmPayment(array $data): array {
        if (($data['vnp_TmnCode'] ?? '') !== $this->gateway->tmnCode) return ['RspCode'=>'01','Message'=>'Merchant mismatch'];
        foreach (['vnp_ResponseCode','vnp_TransactionStatus'] as $field) {
            if (!preg_match('/^[0-9]{2}$/D', $data[$field] ?? '')) return ['RspCode'=>'99','Message'=>'Invalid data'];
        }
        if (!preg_match('/^[A-Za-z0-9]{1,100}$/D', $data['vnp_TxnRef'] ?? '')) return ['RspCode'=>'01','Message'=>'Order not found'];
        $this->db->beginTransaction();
        try {
            // Customer lock is shared with place(): consistent lock order for concurrent IPNs/retries.
            $owner=$this->query('SELECT c.customer_id FROM checkout_payment_attempts p JOIN checkout_sessions c ON c.id=p.checkout_id WHERE p.txn_ref=?', [$data['vnp_TxnRef']])->fetchColumn();
            if ($owner) $this->query('SELECT idkhachhang FROM khachhang WHERE idkhachhang=? FOR UPDATE', [$owner]);
            $attempt=$this->query('SELECT * FROM checkout_payment_attempts WHERE txn_ref=? FOR UPDATE', [$data['vnp_TxnRef']])->fetch(PDO::FETCH_ASSOC);
            if (!$attempt) { $this->db->rollBack(); return ['RspCode'=>'01','Message'=>'Order not found']; }
            $checkout=$this->query('SELECT * FROM checkout_sessions WHERE id=? FOR UPDATE', [$attempt['checkout_id']])->fetch(PDO::FETCH_ASSOC);
            if (!preg_match('/^[0-9]{1,12}$/D', $data['vnp_Amount'] ?? '') || (int)$data['vnp_Amount'] !== (int)$attempt['amount']*100 || (int)$attempt['amount'] !== (int)$checkout['amount']) {
                $this->db->rollBack(); return ['RspCode'=>'04','Message'=>'Invalid amount'];
            }
            if ($attempt['confirmed_at'] !== null) { $this->db->commit(); return ['RspCode'=>'02','Message'=>'Order already confirmed']; }
            $paid=$data['vnp_ResponseCode']==='00' && $data['vnp_TransactionStatus']==='00';
            $review=$data['vnp_ResponseCode']==='07' || $data['vnp_TransactionStatus']==='07';
            $txn=$data['vnp_TransactionNo'] ?? '';
            if ($paid && (!preg_match('/^[0-9]{1,32}$/D', $txn) || (int)$txn === 0)) {
                $this->db->rollBack(); return ['RspCode'=>'99','Message'=>'Missing transaction number'];
            }
            // Failed payments commonly return TransactionNo=0: never put 0 in a unique key.
            $txn=preg_match('/^[0-9]{1,32}$/D', $txn) && trim($txn,'0') !== '' ? $txn : null;
            $status=$review?'REVIEW':($paid?'PAID':'FAILED');
            $invoice=null;
            if ($paid && !$checkout['invoice_id']) {
                $this->db->exec('SAVEPOINT invoice_creation');
                try { $invoice=$this->invoice($checkout,true); }
                catch (DomainException $e) {
                    $this->db->exec('ROLLBACK TO SAVEPOINT invoice_creation');
                    $status='REVIEW';
                    error_log('UniBook VNPAY PAID_REQUIRES_REVIEW checkout='.(int)$checkout['id']);
                }
            }
            $this->query('UPDATE checkout_payment_attempts SET status=?,transaction_no=?,response_code=?,transaction_status=?,raw_response=?,confirmed_at=NOW() WHERE id=?', [$status,$txn,$data['vnp_ResponseCode'],$data['vnp_TransactionStatus'],json_encode($data,JSON_THROW_ON_ERROR),$attempt['id']]);
            if (!$checkout['invoice_id']) $this->query('UPDATE checkout_sessions SET status=?,invoice_id=? WHERE id=?', [$status,$invoice,$checkout['id']]);
            $this->db->commit();
            error_log('UniBook VNPAY IPN checkout='.(int)$checkout['id'].' code='.$data['vnp_ResponseCode'].' status='.$status);
            return ['RspCode'=>'00','Message'=>'Confirm Success'];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('UniBook VNPAY IPN database error: '.get_class($e));
            return ['RspCode'=>'99','Message'=>'Unknown error'];
        }
    }
}
