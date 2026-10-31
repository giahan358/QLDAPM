<?php
declare(strict_types=1);
require_once __DIR__.'/StoreCheckout.php';

final class StoreReconciler {
    private PDO $db;
    private UniBook\Payment\Gateway $gateway;
    public function __construct(PDO $db, UniBook\Payment\Gateway $gateway) {
        $this->db=$db;$this->gateway=$gateway;
        $db->exec("SET time_zone='+07:00'");
    }
    public function run(int $id, ?callable $transport=null): array {
        $q=$this->db->prepare('SELECT * FROM checkout_payment_attempts WHERE id=?');$q->execute([$id]);$payment=$q->fetch(PDO::FETCH_ASSOC);
        if (!$payment) throw new DomainException('Không tìm thấy giao dịch.');
        if ($payment['status']!=='PENDING') return ['RspCode'=>'02','Message'=>'Already processed'];
        // One query per five minutes across all browser tabs/processes. No DB lock during HTTP.
        $q=$this->db->prepare("UPDATE checkout_payment_attempts SET last_query_at=NOW() WHERE id=? AND status='PENDING' AND (last_query_at IS NULL OR last_query_at<=DATE_SUB(NOW(),INTERVAL 5 MINUTE))");
        $q->execute([$id]);
        if ($q->rowCount()!==1) {
            $q=$this->db->prepare('SELECT GREATEST(1,300-TIMESTAMPDIFF(SECOND,last_query_at,NOW())) FROM checkout_payment_attempts WHERE id=?');$q->execute([$id]);
            return ['RspCode'=>'94','Message'=>'Waiting for query interval','retry_after'=>(int)$q->fetchColumn()];
        }
        $request=$this->gateway->queryPayload($payment);
        $response=$transport ? $transport($request) : $this->gateway->sendQuery($request);
        $q=$this->db->prepare('UPDATE checkout_payment_attempts SET last_query_response=? WHERE id=?');
        $q->execute([json_encode($response,JSON_THROW_ON_ERROR),$id]);
        // An API throttle error never authorizes settlement; simply leave payment pending.
        if (($response['vnp_ResponseCode'] ?? '')==='94') return ['RspCode'=>'94','Message'=>'VNPAY query interval','retry_after'=>300];
        $result=(new StoreCheckout($this->db,$this->gateway))->reconcile($response,$request);
        if(!in_array($result['RspCode'],['00','02'],true))$result['retry_after']=300;
        return $result;
    }
}
