<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
require_once dirname(__DIR__,2).'/user/model/connectDB.php';
require_once dirname(__DIR__,2).'/admin/model/order-list.php';
$db=connectdb();$rows=[];$page=1;
do{$result=unifiedOrderList($db,[],$page++);$rows=array_merge($rows,$result['data']);}while($page<=$result['totalPages']);
$expected=(int)$db->query("SELECT (SELECT COUNT(*) FROM hoadon)+(SELECT COUNT(*) FROM checkout_sessions WHERE method='vnpay' AND invoice_id IS NULL)")->fetchColumn();
if(count($rows)!==$expected)throw new RuntimeException('Missing or duplicated orders in unified list');
$keys=[];
foreach($rows as $row){$key=$row['row_type'].':'.($row['idhoadon'] ?? $row['checkout_id']);if(isset($keys[$key]))throw new RuntimeException('Duplicate row');$keys[$key]=true;}
$unpaid=(int)$db->query("SELECT COUNT(*) FROM hoadon WHERE trangthai=2 AND phuongthuctt IN ('cod','Thanh toán khi nhận hàng') AND trangthai_thanhtoan='PENDING'")->fetchColumn();
if($unpaid!==0)throw new RuntimeException('Received COD still unpaid');
echo 'PASS read-only localhost: '.$expected.' orders/checkouts in one list; no duplicates; received COD statuses correct.'.PHP_EOL;
