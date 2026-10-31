<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
require __DIR__.'/StoreReconciler.php';
$id=filter_var($argv[1] ?? null,FILTER_VALIDATE_INT);
if(!$id)throw new RuntimeException('Usage: php reconcile-storefront.php attempt_id');
$config=paymentConfig();$gateway=paymentGateway($config);
$db=new PDO($config['PAYMENT_DB_DSN'],$config['PAYMENT_DB_USER'],$config['PAYMENT_DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
echo json_encode((new StoreReconciler($db,$gateway))->run($id),JSON_UNESCAPED_UNICODE).PHP_EOL;
