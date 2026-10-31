<?php
declare(strict_types=1);
require_once __DIR__.'/Gateway.php';

/** Cancellation and money transfer have separate states: cancelled never implies refunded. */
final class AfterSales {
    private PDO $db;
    private ?UniBook\Payment\Gateway $gateway;
    public function __construct(PDO $db, ?UniBook\Payment\Gateway $gateway=null) {
        $this->db=$db;$this->gateway=$gateway;
        $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $db->exec("SET time_zone='+07:00'");
    }
    private function q(string $sql,array $args=[]): PDOStatement {
        $q=$this->db->prepare($sql);$q->execute($args);return $q;
    }
    private function order(int $id): array {
        $order=$this->q('SELECT * FROM hoadon WHERE idhoadon=? FOR UPDATE',[$id])->fetch(PDO::FETCH_ASSOC);
        if(!$order)throw new DomainException('Không tìm thấy đơn hàng.');return $order;
    }
    public function requestCancel(int $id,int $customer,string $reason): array {
        $reason=trim($reason);
        if($reason==='' || mb_strlen($reason)>500)throw new DomainException('Nhập lý do hủy từ 1 đến 500 ký tự.');
        $this->db->beginTransaction();
        try {
            $order=$this->order($id);
            if((int)$order['idkhachhang']!==$customer)throw new DomainException('Không tìm thấy đơn hàng của bạn.');
            $existing=$this->q('SELECT status FROM order_cancel_requests WHERE order_id=?',[$id])->fetchColumn();
            if($existing){$this->db->commit();return ['cancel_status'=>$existing,'order_id'=>$id];}
            if(!in_array((int)$order['trangthai'],[0,1],true))throw new DomainException('Đơn đã nhận hoặc đã hủy không thể yêu cầu hủy.');
            $status=(int)$order['trangthai']===0?'APPROVED':'PENDING';
            $this->q('INSERT INTO order_cancel_requests(order_id,customer_id,status,reason,created_at,decided_at) VALUES(?,?,?,?,NOW(),CASE WHEN ?=\'APPROVED\' THEN NOW() ELSE NULL END)',[$id,$customer,$status,$reason,$status]);
            if($status==='APPROVED')$this->cancelLocked($order);
            else $this->q("UPDATE hoadon SET cancel_status='REQUESTED' WHERE idhoadon=?",[$id]);
            $this->db->commit();return ['cancel_status'=>$status,'order_id'=>$id];
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    public function decide(int $id,int $staff,bool $approve,string $note='',bool $direct=false): array {
        if(mb_strlen($note)>500)throw new DomainException('Ghi chú tối đa 500 ký tự.');
        $this->db->beginTransaction();
        try {
            $order=$this->order($id);
            if(!$this->q('SELECT idnhanvien FROM nhanvien WHERE idnhanvien=? AND trangthai=1',[$staff])->fetchColumn())throw new DomainException('Nhân viên không hợp lệ.');
            $request=$this->q('SELECT * FROM order_cancel_requests WHERE order_id=? FOR UPDATE',[$id])->fetch(PDO::FETCH_ASSOC);
            if($request && $request['status']!=='PENDING') {
                $this->db->commit();return ['cancel_status'=>$request['status'],'order_id'=>$id];
            }
            if(!$request && !$direct)throw new DomainException('Đơn chưa có yêu cầu hủy.');
            if(!in_array((int)$order['trangthai'],[0,1],true))throw new DomainException('Đơn không còn đủ điều kiện hủy.');
            $status=$approve?'APPROVED':'REJECTED';
            if(!$request)$this->q('INSERT INTO order_cancel_requests(order_id,customer_id,status,reason,created_at) VALUES(?,?,?,?,NOW())',[$id,$order['idkhachhang'],'PENDING','Quản trị hủy đơn']);
            if($approve)$this->cancelLocked($order);
            else $this->q("UPDATE hoadon SET cancel_status='REJECTED' WHERE idhoadon=?",[$id]);
            $this->q('UPDATE order_cancel_requests SET status=?,decision_note=?,staff_id=?,decided_at=NOW() WHERE order_id=?',[$status,$note,$staff,$id]);
            $this->db->commit();return ['cancel_status'=>$status,'order_id'=>$id];
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    private function cancelLocked(array $order): void {
        $id=(int)$order['idhoadon'];
        if((int)$order['stock_deducted']===1){
            $items=$this->q('SELECT idsach,SUM(soluong) AS qty FROM chitiethoadon WHERE idhoadon=? GROUP BY idsach ORDER BY idsach',[$id])->fetchAll(PDO::FETCH_ASSOC);
            foreach($items as $item){
                if((int)$item['qty']<1)throw new DomainException('Chi tiết đơn hàng không hợp lệ.');
                $q=$this->q('UPDATE sach SET sltonkho=sltonkho+? WHERE idsach=?',[$item['qty'],$item['idsach']]);
                if($q->rowCount()!==1)throw new DomainException('Không thể hoàn lại tồn kho.');
            }
        }
        if(strtoupper($order['phuongthuctt'])==='VNPAY' && $order['trangthai_thanhtoan']==='PAID') {
            $p=$this->q("SELECT p.* FROM checkout_payment_attempts p JOIN checkout_sessions c ON c.id=p.checkout_id WHERE c.invoice_id=? AND p.status='PAID' AND p.transaction_status='00' FOR UPDATE",[$id])->fetch(PDO::FETCH_ASSOC);
            if(!$p || (int)$p['amount']!==(int)$order['tongtien'])throw new DomainException('Thiếu giao dịch VNPAY đã xác nhận để hoàn tiền. Vui lòng đối soát trước khi hủy.');
            $this->q('INSERT INTO order_refunds(order_id,payment_id,amount,request_id,created_at,updated_at) VALUES(?,?,?,?,NOW(),NOW())',[$id,$p['id'],$p['amount'],bin2hex(random_bytes(16))]);
        }
        $this->q("UPDATE hoadon SET trangthai=3,cancel_status='APPROVED',stock_deducted=0 WHERE idhoadon=?",[$id]);
    }
    /** Reserve the send before HTTP. A crash/timeout must NEVER trigger an automatic second refund. */
    public function dispatchRefund(int $orderId,string $actor,?callable $transport=null): ?array {
        $this->db->beginTransaction();
        try {
            $order=$this->order($orderId);
            $refund=$this->q('SELECT * FROM order_refunds WHERE order_id=? FOR UPDATE',[$orderId])->fetch(PDO::FETCH_ASSOC);
            if(!$refund){$this->db->commit();return null;}
            if($refund['status']!=='QUEUED'){$this->db->commit();return $this->publicRefund($refund);}
            if((int)$order['trangthai']!==3 || $order['cancel_status']!=='APPROVED')throw new DomainException('Đơn chưa được duyệt hủy.');
            if(!$this->gateway)throw new RuntimeException('Gateway unavailable');
            $p=$this->q('SELECT * FROM checkout_payment_attempts WHERE id=? FOR UPDATE',[$refund['payment_id']])->fetch(PDO::FETCH_ASSOC);
            $payload=$this->gateway->refundPayload(['vnp_txn_ref'=>$p['txn_ref'],'vnp_transaction_no'=>$p['transaction_no'],'create_date'=>$p['create_date']],
                ['request_id'=>$refund['request_id'],'amount'=>(int)$refund['amount'],'transaction_type'=>'02'],$actor);
            $this->q("UPDATE order_refunds SET status='SENDING',raw_request=?,updated_at=NOW() WHERE id=?",[json_encode($payload,JSON_THROW_ON_ERROR),$refund['id']]);
            $this->db->commit();
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
        try {
            $response=$transport?$transport($payload):$this->gateway->sendRefund($payload);
            return $this->recordRefund((int)$refund['id'],$response,false);
        }catch(Throwable $e){
            $this->q("UPDATE order_refunds SET status='UNKNOWN',updated_at=NOW() WHERE id=? AND status='SENDING'",[$refund['id']]);
            error_log('UniBook refund outcome unknown order='.$orderId.' '.get_class($e));
            return ['status'=>'UNKNOWN','id'=>(int)$refund['id']];
        }
    }
    public function checkRefund(int $orderId,?callable $transport=null): ?array {
        $r=$this->q('SELECT r.*,p.txn_ref,p.create_date FROM order_refunds r JOIN checkout_payment_attempts p ON p.id=r.payment_id WHERE r.order_id=?',[$orderId])->fetch(PDO::FETCH_ASSOC);
        if(!$r)return null;
        if($r['status']==='QUEUED')return $this->dispatchRefund($orderId,'UniBookAdmin',$transport);
        if(in_array($r['status'],['SUCCEEDED','REJECTED'],true))return $this->publicRefund($r);
        if(!$this->gateway)throw new RuntimeException('Gateway unavailable');
        $q=$this->q('UPDATE order_refunds SET last_query_at=NOW() WHERE id=? AND (last_query_at IS NULL OR last_query_at<=DATE_SUB(NOW(),INTERVAL 5 MINUTE))',[$r['id']]);
        if($q->rowCount()!==1)return $this->publicRefund($r);
        $payload=$this->gateway->queryPayload($r);
        $response=$transport?$transport($payload):$this->gateway->sendQuery($payload);
        return $this->recordRefund((int)$r['id'],$response,true);
    }
    public function recordRefund(int $id,array $response,bool $query): array {
        $this->db->beginTransaction();
        try {
            $candidate=$this->q('SELECT order_id FROM order_refunds WHERE id=?',[$id])->fetchColumn();
            if(!$candidate)throw new DomainException('Không tìm thấy yêu cầu hoàn tiền.');
            $order=$this->order((int)$candidate);
            $r=$this->q('SELECT * FROM order_refunds WHERE id=? FOR UPDATE',[$id])->fetch(PDO::FETCH_ASSOC);
            if($r['status']==='SUCCEEDED'){$this->db->commit();return $this->publicRefund($r);}
            $request=json_decode($r['raw_request'] ?? '{}',true,16,JSON_THROW_ON_ERROR);
            $valid=$this->gateway && ($query?$this->gateway->verifyQueryResponse($response,$request):$this->gateway->verifyRefund($response,$request));
            if(!$valid || (string)($response['vnp_Amount'] ?? '')!==(string)((int)$r['amount']*100) || ($response['vnp_TransactionType'] ?? '')!=='02')throw new DomainException('Chưa xác minh được kết quả hoàn tiền.');
            $code=$response['vnp_ResponseCode'] ?? ''; $txn=$response['vnp_TransactionStatus'] ?? '';
            $status='UNKNOWN';
            if($code==='00' && $txn==='00')$status='SUCCEEDED';
            elseif($code==='94' || ($code==='00' && in_array($txn,['05','06'],true)))$status='PROCESSING';
            elseif($txn==='07')$status='REVIEW';
            elseif(($code==='00' && $txn==='09') || in_array($code,['02','03','04','13','91','93','95','97'],true))$status='REJECTED';
            $this->q('UPDATE order_refunds SET status=?,response_code=?,transaction_status=?,raw_response=?,updated_at=NOW() WHERE id=?',[$status,$code,substr($txn,0,2),json_encode($response,JSON_THROW_ON_ERROR),$id]);
            if($status==='SUCCEEDED')$this->q("UPDATE hoadon SET trangthai_thanhtoan='REFUNDED' WHERE idhoadon=? AND trangthai=3",[$order['idhoadon']]);
            $this->db->commit();return ['status'=>$status,'id'=>$id];
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    private function publicRefund(array $r): array {return ['id'=>(int)$r['id'],'status'=>$r['status']];}
}
