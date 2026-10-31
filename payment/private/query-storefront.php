<?php
declare(strict_types=1);
// CLI read-only reconciliation probe. Never infer payment success from a browser Return.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/bootstrap.php';
$config=paymentConfig(); $gateway=paymentGateway($config);
$db=new PDO($config['PAYMENT_DB_DSN'],$config['PAYMENT_DB_USER'],$config['PAYMENT_DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$id=filter_var($argv[1] ?? null,FILTER_VALIDATE_INT);
if (!$id) throw new RuntimeException('Usage: php query-storefront.php attempt_id');
$q=$db->prepare('SELECT * FROM checkout_payment_attempts WHERE id=?');$q->execute([$id]);$p=$q->fetch(PDO::FETCH_ASSOC);
if (!$p) throw new RuntimeException('Attempt not found');
$request=['vnp_RequestId'=>bin2hex(random_bytes(16)),'vnp_Version'=>'2.1.0','vnp_Command'=>'querydr',
 'vnp_TmnCode'=>$gateway->tmnCode,'vnp_TxnRef'=>$p['txn_ref'],'vnp_TransactionDate'=>$p['create_date'],
 'vnp_CreateDate'=>UniBook\Payment\Gateway::now()->format('YmdHis'),'vnp_IpAddr'=>$gateway->serverIp,
 'vnp_OrderInfo'=>'Query UniBook payment '.$p['txn_ref']];
$request['vnp_SecureHash']=$gateway->pipeHash($request,['vnp_RequestId','vnp_Version','vnp_Command','vnp_TmnCode','vnp_TxnRef','vnp_TransactionDate','vnp_CreateDate','vnp_IpAddr','vnp_OrderInfo']);
$curl=curl_init(UniBook\Payment\Gateway::API_URL);
curl_setopt($curl,CURLOPT_CAINFO,__DIR__.'/cacert.pem');
curl_setopt($curl,CURLOPT_USERAGENT,'UniBook/1.0');
curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_POSTFIELDS=>json_encode($request,JSON_THROW_ON_ERROR),
 CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,
 CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false]);
$body=curl_exec($curl);$http=curl_getinfo($curl,CURLINFO_HTTP_CODE);$error=curl_error($curl);curl_close($curl);
if ($body===false || $http!==200) {
    fwrite(STDERR,'Query transport failed: HTTP '.$http.' '.$error.PHP_EOL);
    if (is_string($body)) fwrite(STDERR,substr(strip_tags($body),0,500).PHP_EOL);
    exit(1);
}
$response=json_decode($body,true,32,JSON_THROW_ON_ERROR);
$hash=$response['vnp_SecureHash'] ?? '';
$fields=array_merge(UniBook\Payment\Gateway::RESPONSE_FIELDS,['vnp_PromotionCode','vnp_PromotionAmount']);
$verified=is_string($hash) && preg_match('/^[a-f0-9]{128}$/iD',$hash) && hash_equals($gateway->pipeHash($response,$fields),strtolower($hash));
$bound=$verified && ($response['vnp_Command'] ?? '')==='querydr' && ($response['vnp_TmnCode'] ?? '')===$gateway->tmnCode && ($response['vnp_TxnRef'] ?? '')===$p['txn_ref'] && (string)($response['vnp_Amount'] ?? '')===(string)((int)$p['amount']*100);
echo json_encode(['attempt_id'=>$id,'signature_valid'=>(bool)$verified,'matches_payment'=>(bool)$bound,
 'response_code'=>$response['vnp_ResponseCode'] ?? null,'transaction_status'=>$response['vnp_TransactionStatus'] ?? null,
 'transaction_type'=>$response['vnp_TransactionType'] ?? null,'message'=>$response['vnp_Message'] ?? null,
 'database_changed'=>false],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
