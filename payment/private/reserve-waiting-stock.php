<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__,2).'/user/model/connectDB.php';
$db=connectdb();
$db->beginTransaction();
try {
    $orders=$db->query('SELECT idhoadon FROM hoadon WHERE trangthai=0 AND stock_deducted=0 ORDER BY idhoadon FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN);
    $backup=['created_at'=>date(DATE_ATOM),'orders'=>$orders,'stock'=>[]];
    $items=[];
    foreach($orders as $id){
        $q=$db->prepare('SELECT idsach,SUM(soluong) qty FROM chitiethoadon WHERE idhoadon=? GROUP BY idsach');$q->execute([$id]);
        $rows=$q->fetchAll(PDO::FETCH_ASSOC);
        if(!$rows)throw new RuntimeException('Missing details for order '.$id);
        foreach($rows as $row){if((int)$row['qty']<1)throw new RuntimeException('Invalid quantity');$items[$row['idsach']]=($items[$row['idsach']]??0)+(int)$row['qty'];}
    }
    ksort($items,SORT_NUMERIC);
    foreach($items as $book=>$qty){
        $q=$db->prepare('SELECT sltonkho FROM sach WHERE idsach=? FOR UPDATE');$q->execute([$book]);$stock=$q->fetchColumn();
        if($stock===false || (int)$stock<$qty)throw new RuntimeException('Insufficient stock for book '.$book.' required='.$qty.' available='.(int)$stock);
        $backup['stock'][]=['idsach'=>$book,'before'=>(int)$stock,'reserve'=>$qty];
    }
    if($orders){
        $file=__DIR__.'/backups/before-waiting-stock-'.date('Ymd-His').'.json';
        if(file_put_contents($file,json_encode($backup,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR))===false)throw new RuntimeException('Backup failed');
        foreach($items as $book=>$qty){$q=$db->prepare('UPDATE sach SET sltonkho=sltonkho-? WHERE idsach=? AND sltonkho>=?');$q->execute([$qty,$book,$qty]);if($q->rowCount()!==1)throw new RuntimeException('Stock changed');}
        foreach($orders as $id){$q=$db->prepare('UPDATE hoadon SET stock_deducted=1 WHERE idhoadon=?');$q->execute([$id]);}
    }
    $db->commit();echo json_encode(['updated_orders'=>count($orders),'reserved_products'=>count($items)]).PHP_EOL;
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}
