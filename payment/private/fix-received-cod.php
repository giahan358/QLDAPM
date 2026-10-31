<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__,2).'/user/model/connectDB.php';
$db=connectdb();$db->beginTransaction();
try{
 $rows=$db->query("SELECT idhoadon,trangthai_thanhtoan FROM hoadon WHERE trangthai=2 AND phuongthuctt IN ('cod','Thanh toán khi nhận hàng') AND trangthai_thanhtoan='PENDING' FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
 if(!$rows){$db->commit();echo "No COD correction needed.\n";exit;}
 $path=__DIR__.'/backups/received-cod-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.json';
 if(file_put_contents($path,json_encode($rows,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR))===false)throw new RuntimeException('Cannot back up COD statuses.');
 $q=$db->prepare("UPDATE hoadon SET trangthai_thanhtoan='PAID' WHERE idhoadon=? AND trangthai=2 AND trangthai_thanhtoan='PENDING'");
 foreach($rows as $row)$q->execute([$row['idhoadon']]);
 $db->commit();echo 'Updated '.count($rows).' received COD orders; backup: '.$path.PHP_EOL;
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
