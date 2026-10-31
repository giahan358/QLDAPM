<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__).'/private/Gateway.php';
require_once dirname(__DIR__).'/private/StoreCheckout.php';
require_once dirname(__DIR__).'/private/OrderReceipt.php';
require_once dirname(__DIR__,2).'/admin/model/order-list.php';
require_once dirname(__DIR__,2).'/user/model/hoadon.php';
require_once dirname(__DIR__).'/private/Reviews.php';
require_once dirname(__DIR__).'/private/StoreReconciler.php';
function connectdb(){global $db;return $db;}
require_once dirname(__DIR__,2).'/user/model/address_catalog.php';
use UniBook\Payment\Gateway;
$admin=new PDO('mysql:host=127.0.0.1;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$name='unibook_checkout_test_'.bin2hex(random_bytes(5));
$checks=0;
function check(bool $condition,string $message): void { global $checks; if (!$condition) throw new RuntimeException($message); ++$checks; }
function reject(callable $fn,string $message): void { try { $fn(); } catch (DomainException $e) { check(true,$message); return; } throw new RuntimeException($message); }
$admin->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4");
try {
    $db=new PDO("mysql:host=127.0.0.1;dbname=$name;charset=utf8mb4",'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    // Clone the REAL localhost schemas, without copying customer data. CREATE LIKE omits FKs.
    foreach (['khachhang','thongtinnhanhang','nhanvien','sach','giohang','hoadon','chitiethoadon'] as $table) $db->exec("CREATE TABLE `$table` LIKE webbansach.`$table`");
    $db->exec(file_get_contents(dirname(__DIR__).'/private/storefront-schema.sql'));
    $db->exec(file_get_contents(dirname(__DIR__).'/private/receipt-stock.sql'));
    $db->exec(file_get_contents(dirname(__DIR__).'/private/reconciliation.sql'));
    // Populate all mandatory fields with deterministic test-only values.
    foreach (['khachhang','nhanvien','sach','thongtinnhanhang'] as $table) {
        $columns=$db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $names=[];$values=[];
        foreach ($columns as $column) {
            if ($column['Extra']==='auto_increment') continue;
            $names[]='`'.$column['Field'].'`';
            $values[]=preg_match('/int|decimal|float|double/', $column['Type']) ? 1 : (strpos($column['Type'],'date')!==false?'2026-09-13':'Test');
        }
        $db->prepare("INSERT INTO `$table` (".implode(',',$names).') VALUES('.implode(',',array_fill(0,count($names),'?')).')')->execute($values);
    }
    $db->exec("UPDATE nhanvien SET chucvu='Nhân viên bán hàng',trangthai=1; UPDATE sach SET gia=10000,sltonkho=20,trangthai=1");
    $gateway=new Gateway('TEST0001','test-only-secret','https://example.com/payment/vnpay-return','127.0.0.1');
    $service=new StoreCheckout($db,$gateway);
    $count=fn(string $table)=>(int)$db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $stock=fn()=>(int)$db->query('SELECT sltonkho FROM sach WHERE idsach=1')->fetchColumn();
    $cart=function() use($db) { $db->exec('DELETE FROM giohang'); $db->exec('INSERT INTO giohang(idkhachhang,idsach,soluong,trangthai) VALUES(1,1,2,1)'); };
    $clear=function() use($db) { $db->exec('DELETE FROM checkout_payment_attempts; DELETE FROM checkout_sessions; DELETE FROM chitiethoadon; DELETE FROM hoadon; DELETE FROM giohang; UPDATE sach SET sltonkho=20; UPDATE nhanvien SET trangthai=1'); };
    $callback=function(string $code='00',string $txn='12345',?string $status=null) use($db,$gateway): string {
        $p=$db->query('SELECT * FROM checkout_payment_attempts ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        $data=['vnp_TmnCode'=>'TEST0001','vnp_TxnRef'=>$p['txn_ref'],'vnp_Amount'=>(string)((int)$p['amount']*100),'vnp_ResponseCode'=>$code,'vnp_TransactionStatus'=>$status ?? $code,'vnp_TransactionNo'=>$txn,'vnp_BankCode'=>'NCB','vnp_PayDate'=>Gateway::now()->format('YmdHis')];
        return 'action=ipn&'.Gateway::canonical($data).'&vnp_SecureHash='.$gateway->sign($data);
    };
    foreach (['cod','bank','vnpay'] as $method) {
        $clear();$cart();$db->exec('UPDATE sach SET sltonkho=1');
        reject(fn()=>$service->place(1,1,$method,bin2hex(random_bytes(16))), 'Insufficient stock rejected '.$method);
        check($count('hoadon')===0 && $count('chitiethoadon')===0 && $count('checkout_sessions')===0,'Failed checkout leaves no invoice/draft');
        check($count('giohang')===1 && $stock()===1,'Cart and stock unchanged on failure');
    }
    $clear();$cart();
    reject(fn()=>(new StoreCheckout($db))->place(1,1,'vnpay',bin2hex(random_bytes(16))), 'Missing config rejected before invoice');
    check($count('hoadon')===0 && $count('checkout_sessions')===0,'Missing configuration writes nothing');
    reject(fn()=>$service->place(1,999,'cod',bin2hex(random_bytes(16))), 'Foreign address rejected');
    $key=bin2hex(random_bytes(16));$result=$service->place(1,1,'vnpay',$key);
    check(isset($result['redirectUrl']) && $count('hoadon')===0 && $count('chitiethoadon')===0,'Pending online creates no invoices');
    check($stock()===20 && $count('giohang')===1,'Pending online does not consume stock or cart');
    parse_str(parse_url($result['redirectUrl'],PHP_URL_QUERY),$url);
    check($url['vnp_Amount']==='2000000','Amount calculated from DB times 100');
    check($service->place(1,1,'vnpay',$key)['redirectUrl']===$result['redirectUrl'],'Duplicate create returns same URL');
    check($count('checkout_payment_attempts')===1,'Duplicate create does not add attempts');
    check($service->ipn('action=ipn&vnp_SecureHash=bad')['RspCode']==='97','Wrong checksum rejected');
    $raw=$callback(); parse_str($raw,$bad);$bad['vnp_Amount']='1';unset($bad['action']);$badRaw=Gateway::canonical($bad).'&vnp_SecureHash='.$gateway->sign($bad);
    check($service->ipn($badRaw)['RspCode']==='04','Signed wrong amount rejected');
    check($count('hoadon')===0,'Invalid IPN creates no invoice');
    $raw=$callback('24','0'); check($service->ipn($raw)['RspCode']==='00','Cancelled payment acknowledged');
    check($count('hoadon')===0 && $stock()===20 && $count('giohang')===1,'Cancellation preserves invoice/cart/stock');
    check($service->ipn($raw)['RspCode']==='02','Duplicate failure idempotent');
    $oldRef=$url['vnp_TxnRef'];
    $checkoutId=(int)$result['checkout_id'];
    reject(fn()=>$service->retry(999,$checkoutId),'Foreign customer cannot retry');
    reject(fn()=>$service->place(1,1,'bank',bin2hex(random_bytes(16))),'Removed bank method rejected');
    $db->exec('DELETE FROM giohang');
    $retry=$service->retry(1,$checkoutId);
    check((int)$retry['checkout_id']===$checkoutId && $count('checkout_sessions')===1,'Retry keeps original checkout without cart');
    check($service->retry(1,$checkoutId)['redirectUrl']===$retry['redirectUrl'],'Duplicate retry returns same pending attempt');
    parse_str(parse_url($retry['redirectUrl'],PHP_URL_QUERY),$retryUrl);
    check($retryUrl['vnp_Amount']==='2000000' && $retryUrl['vnp_TxnRef']!==$oldRef,'Retry preserves amount and changes reference');
    check($service->ipn($callback('24','0'))['RspCode']==='00','Retry failure acknowledged');
    // Restore the original cart IDs so the existing place() retry tests use the same snapshot.
    $snapshot=json_decode($db->query('SELECT items FROM checkout_sessions')->fetchColumn(),true);
    $db->prepare('INSERT INTO giohang(idgiohang,idkhachhang,idsach,soluong,trangthai) VALUES(?,1,1,2,1)')->execute([$snapshot[0]['cart_id']]);
    $result=$service->place(1,1,'vnpay',bin2hex(random_bytes(16)));parse_str(parse_url($result['redirectUrl'],PHP_URL_QUERY),$url);
    check($url['vnp_TxnRef']!==$oldRef,'Retry gets new reference');
    $raw=$callback('51','0');check($service->ipn($raw)['RspCode']==='00','Repeated failed TransactionNo=0 allowed');
    check($count('hoadon')===0,'Insufficient funds creates no invoice');
    $service->place(1,1,'vnpay',bin2hex(random_bytes(16)));$raw=$callback();
    check($service->ipn($raw)['RspCode']==='00','Successful IPN accepted');
    check($count('hoadon')===1 && $count('chitiethoadon')===1,'Paid IPN creates exactly one invoice/details');
    check($stock()===18 && $count('giohang')===0,'Paid IPN reserves stock and clears cart');
    check($service->ipn($raw)['RspCode']==='02' && $count('hoadon')===1 && $stock()===18,'Duplicate success cannot double create/decrement');
    $clear();$cart();$service->place(1,1,'vnpay',bin2hex(random_bytes(16)));$db->exec('UPDATE sach SET sltonkho=0');
    check($service->ipn($callback())['RspCode']==='00','Paid IPN recorded when stock requires review');
    check($count('hoadon')===0 && $count('chitiethoadon')===0 && $count('giohang')===1 && $stock()===0,'Insufficient stock cannot create oversold invoice or clear cart');
    check($db->query('SELECT status FROM checkout_sessions')->fetchColumn()==='REVIEW','Paid transaction with insufficient stock requires review');
    $clear();$cart();
    for ($i=0;$i<6;$i++) { $service->place(1,1,'vnpay',bin2hex(random_bytes(16))); check($service->ipn($callback('24','0'))['RspCode']==='00','Retry failure '.$i); }
    reject(fn()=>$service->place(1,1,'vnpay',bin2hex(random_bytes(16))), 'Retry limit enforced');
    $limitedId=(int)$db->query('SELECT id FROM checkout_sessions')->fetchColumn();
    reject(fn()=>$service->retry(1,$limitedId),'Explicit retry endpoint enforces five-retry limit');
    check($count('checkout_payment_attempts')===6 && $count('hoadon')===0,'Retry limit writes no invoice');
    foreach (['cod'] as $method) {
        $clear();$cart();$key=bin2hex(random_bytes(16));$result=$service->place(1,1,$method,$key);
        check($count('hoadon')===1 && $count('chitiethoadon')===1 && $stock()===18,'Offline order atomic '.$method);
        check($db->query('SELECT trangthai_thanhtoan FROM hoadon')->fetchColumn()==='PENDING','Offline order not falsely marked paid');
        check($service->place(1,1,$method,$key)['idhoadon']===$result['idhoadon'] && $count('hoadon')===1,'Offline duplicate idempotent');
    }
    // Force a detail-insert failure AFTER stock/invoice writes, then prove transaction rollback.
    $clear();$cart();$db->exec("CREATE TRIGGER fail_test_detail BEFORE INSERT ON chitiethoadon FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='intentional integration test failure'");
    try { $service->place(1,1,'cod',bin2hex(random_bytes(16)));throw new RuntimeException('Expected database failure'); } catch (PDOException $e) { check(true,'Forced detail failure'); }
    check($count('hoadon')===0 && $count('chitiethoadon')===0 && $count('checkout_sessions')===0 && $stock()===20 && $count('giohang')===1,'Real transaction rolls back invoice/details/stock/cart');
    $db->exec('DROP TRIGGER fail_test_detail');
    $clear();$cart();
    $order=$service->place(1,1,'cod',bin2hex(random_bytes(16)))['idhoadon'];
    check($stock()===18,'COD waiting confirmation reserves stock');
    reject(fn()=>OrderReceipt::transition($db,$order,2,null,999),'Another customer cannot receive order');
    reject(fn()=>OrderReceipt::transition($db,$order,2,null,1),'Cannot receive before confirmation');
    OrderReceipt::transition($db,$order,1,1);
    check($stock()===18,'Shipping does not deduct reserved stock again');
    OrderReceipt::transition($db,$order,2,null,1);
    check($stock()===18,'Receipt does not deduct again');
    check($db->query('SELECT trangthai_thanhtoan FROM hoadon')->fetchColumn()==='PAID','COD receipt marks payment paid');
    OrderReceipt::transition($db,$order,2,null,1);
    OrderReceipt::transition($db,$order,2,1);
    check($stock()===18,'Repeated customer/admin receipt never deducts twice');
    reject(fn()=>OrderReceipt::transition($db,$order,3,1),'Received order cannot be cancelled');
    $clear();$cart();$order=$service->place(1,1,'cod',bin2hex(random_bytes(16)))['idhoadon'];
    OrderReceipt::transition($db,$order,3,1);OrderReceipt::transition($db,$order,3,1);
    check($stock()===20,'Cancelling waiting order restores reserved stock once');
    $clear();$cart();$order=$service->place(1,1,'cod',bin2hex(random_bytes(16)))['idhoadon'];
    $db->exec('UPDATE sach SET sltonkho=18; UPDATE hoadon SET stock_deducted=1');
    OrderReceipt::transition($db,$order,1,1);OrderReceipt::transition($db,$order,2,1);
    check($stock()===18,'Old previously deducted orders do not deduct twice');
    $clear();$cart();$order=$service->place(1,1,'cod',bin2hex(random_bytes(16)))['idhoadon'];
    $db->exec('UPDATE sach SET sltonkho=20; UPDATE hoadon SET stock_deducted=0'); // Simulate a legacy unreserved invoice.
    $book=$db->query('SELECT * FROM sach WHERE idsach=1')->fetch(PDO::FETCH_ASSOC);unset($book['idsach']);$book['sltonkho']=0;
    $db->prepare('INSERT INTO sach ('.implode(',',array_map(fn($k)=>'`'.$k.'`',array_keys($book))).') VALUES('.implode(',',array_fill(0,count($book),'?')).')')->execute(array_values($book));
    $second=(int)$db->lastInsertId();
    $db->prepare('INSERT INTO chitiethoadon(idhoadon,idsach,soluong,gia) VALUES(?,?,1,10000)')->execute([$order,$second]);
    reject(fn()=>OrderReceipt::transition($db,$order,1,1),'Insufficient second product rejects shipping');
    check($stock()===20 && (int)$db->query('SELECT trangthai FROM hoadon')->fetchColumn()===0,'Shipping rollback restores first product and order state');
    $clear();$cart();$service->place(1,1,'vnpay',bin2hex(random_bytes(16)));
    $db->exec('UPDATE giohang SET soluong=5');
    check($service->ipn($callback())['RspCode']==='00','Successful payment clears purchased portion');
    check((int)$db->query('SELECT soluong FROM giohang')->fetchColumn()===3 && $stock()===18,'Newly added cart quantity preserved, purchased stock reserved');
    $clear();$cart();$order=$service->place(1,1,'cod',bin2hex(random_bytes(16)))['idhoadon'];
    OrderReceipt::transition($db,$order,1,1);OrderReceipt::transition($db,$order,1,1);
    check($stock()===18,'Repeated shipping does not double deduct');
    OrderReceipt::transition($db,$order,3,1);OrderReceipt::transition($db,$order,3,1);
    check($stock()===20,'Cancel shipping restores stock once');
    $clear();$cart();$service->place(1,1,'vnpay',bin2hex(random_bytes(16)));
    $p=$db->query('SELECT * FROM checkout_payment_attempts ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $request=$gateway->queryPayload($p);
    $response=['vnp_ResponseId'=>'query-test','vnp_Command'=>'querydr','vnp_ResponseCode'=>'00','vnp_Message'=>'Success',
        'vnp_TmnCode'=>'TEST0001','vnp_TxnRef'=>$p['txn_ref'],'vnp_Amount'=>(string)((int)$p['amount']*100),
        'vnp_BankCode'=>'NCB','vnp_PayDate'=>Gateway::now()->format('YmdHis'),'vnp_TransactionNo'=>'998877',
        'vnp_TransactionType'=>'01','vnp_TransactionStatus'=>'00','vnp_OrderInfo'=>'Query test'];
    $signQuery=function(array $r) use($gateway):array{$r['vnp_SecureHash']=$gateway->pipeHash($r,array_merge(Gateway::RESPONSE_FIELDS,['vnp_PromotionCode','vnp_PromotionAmount']));return $r;};
    check($service->reconcile($response,$request)['RspCode']==='97','Unsigned query cannot settle');
    $wrong=$response;$wrong['vnp_Amount']='100';check($service->reconcile($signQuery($wrong),$request)['RspCode']==='04','Wrong signed query amount rejected');
    $pending=$response;$pending['vnp_TransactionStatus']='01';check($service->reconcile($signQuery($pending),$request)['RspCode']==='99','Query success does not imply payment success');
    check($count('hoadon')===0 && $count('giohang')===1,'Unconfirmed query does not alter invoice/cart');
    check($service->reconcile($signQuery($response),$request)['RspCode']==='00','Signed query settles missing IPN');
    check($count('hoadon')===1 && $count('giohang')===0 && $stock()===18,'Reconciliation creates invoice, clears cart and reserves stock');
    check($service->reconcile($signQuery($response),$request)['RspCode']==='02','Duplicate query idempotent');
    check($service->ipn($callback('00','998877'))['RspCode']==='02','Late IPN after query cannot create duplicate invoice');
    $clear();$cart();$draft=$service->place(1,1,'vnpay',bin2hex(random_bytes(16)));
    $p=$db->query('SELECT * FROM checkout_payment_attempts ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $request=$gateway->queryPayload($p);$response['vnp_TxnRef']=$p['txn_ref'];$response['vnp_TransactionStatus']='02';$response['vnp_TransactionNo']='0';
    check($service->reconcile($signQuery($response),$request)['RspCode']==='00','Signed terminal failed query is recorded');
    check($count('hoadon')===0 && $count('giohang')===1,'Failed query preserves cart and creates no invoice');
    check((int)$service->retry(1,(int)$draft['checkout_id'])['checkout_id']===(int)$draft['checkout_id'],'Failed query unlocks retry on same checkout');
    $service->ipn($callback('24','0'));
    $db->exec(file_get_contents(dirname(__DIR__).'/private/after-sales.sql'));
    $offline=$service->place(1,1,'cod',bin2hex(random_bytes(16)))['idhoadon'];
    $list=unifiedOrderList($db,[],1);
    check(count($list['data'])===2,'Unified list contains failed checkout and COD invoice once each');
    $draftList=unifiedOrderList($db,['status'=>'unpaid'],1);
    check(count($draftList['data'])===1 && $draftList['data'][0]['row_type']==='checkout','Unfinished filter excludes invoices');
    check(count($draftList['data'][0]['payment_attempts'])===2,'Unified row exposes all attempts');
    check(count(unifiedOrderList($db,['search'=>'TT-'.$draft['checkout_id']],1)['data'])===1,'Search supports checkout codes');
    check(count(unifiedOrderList($db,['status'=>'0'],1)['data'])===1,'Waiting filter excludes failed checkout');
    OrderReceipt::transition($db,(int)$offline,1,1);OrderReceipt::transition($db,(int)$offline,2,1);
    $received=$db->query('SELECT received_at FROM hoadon')->fetchColumn();
    $db->exec("UPDATE hoadon SET trangthai_thanhtoan='PENDING'");
    OrderReceipt::transition($db,(int)$offline,2,1);
    check($db->query('SELECT trangthai_thanhtoan FROM hoadon')->fetchColumn()==='PAID' && $received===$db->query('SELECT received_at FROM hoadon')->fetchColumn(),'Legacy COD repeat fixes payment without changing review deadline');
    check((int)getOrdersByCustomerId(1)[0]['can_review']===1,'Received unreviewed order has review action');
    $reviews=new Reviews($db);$reviews->create(1,(int)$offline,1,5,'Test review');
    $history=getOrdersByCustomerId(1)[0];
    check((int)$history['can_review']===0 && (int)$history['reviews_complete']===1,'Review button hidden when all purchased products are reviewed');
    $db->prepare('INSERT INTO chitiethoadon(idhoadon,idsach,soluong,gia) VALUES(?,?,1,10000)')->execute([$offline,$second]);
    check((int)getOrdersByCustomerId(1)[0]['can_review']===1,'Partially reviewed order keeps action for remaining product');
    $reviews->create(1,(int)$offline,$second,4,'Second test review');
    check((int)getOrdersByCustomerId(1)[0]['can_review']===0,'Action disappears after last product review');
    $cart();$service->place(1,1,'vnpay',bin2hex(random_bytes(16)));
    $attemptId=(int)$db->query('SELECT MAX(id) FROM checkout_payment_attempts')->fetchColumn();
    $calls=0;$finalPayment=false;
    $transport=function(array $request)use(&$calls,&$finalPayment,$signQuery,$response){
        ++$calls;$r=$response;$r['vnp_TxnRef']=$request['vnp_TxnRef'];$r['vnp_TransactionStatus']=$finalPayment?'00':'11';$r['vnp_TransactionNo']='87654321';return $signQuery($r);
    };
    $reconciler=new StoreReconciler($db,$gateway);
    check($reconciler->run($attemptId,$transport)['RspCode']==='99','Unknown provider status cannot settle payment');
    $waiting=$reconciler->run($attemptId,$transport);
    check($waiting['RspCode']==='94' && $waiting['retry_after']>0 && $calls===1,'Throttle exposes retry delay without another VNPAY call');
    $db->prepare('UPDATE checkout_payment_attempts SET last_query_at=DATE_SUB(NOW(),INTERVAL 301 SECOND) WHERE id=?')->execute([$attemptId]);
    $finalPayment=true;$invoices=$count('hoadon');
    check($reconciler->run($attemptId,$transport)['RspCode']==='00','Later successful query settles previously unresolved payment');
    check($count('hoadon')===$invoices+1 && $count('giohang')===0,'Delayed confirmation creates invoice and clears purchased cart');
    check($reconciler->run($attemptId,$transport)['RspCode']==='02' && $calls===2,'Already confirmed reconciliation cannot duplicate invoice');
    // Direct purchase must work with an empty cart and preserve unrelated cart quantities.
    $db->exec('DELETE FROM product_review_images; DELETE FROM product_reviews');
    $clear();$cart();
    $old=$service->place(1,1,'vnpay',bin2hex(random_bytes(16)));
    $oldCallback=$callback('00','987654');
    reject(fn()=>$service->place(1,1,'cod',bin2hex(random_bytes(16))),'Active VNPAY link still blocks another purchase');
    $db->exec("UPDATE checkout_payment_attempts SET expire_date=DATE_FORMAT(DATE_SUB(NOW(),INTERVAL 1 DAY),'%Y%m%d%H%i%s')");
    reject(fn()=>$service->retry(1,(int)$old['checkout_id']),'Expired unresolved payment cannot reuse its payment URL');
    $service->place(1,1,'cod',bin2hex(random_bytes(16)));
    check($count('hoadon')===1 && $stock()===18,'Expired unresolved checkout does not block a new order');
    check($db->query('SELECT status FROM checkout_payment_attempts')->fetchColumn()==='PENDING','Expiry never falsely marks financial result failed or refunded');
    check($service->ipn($oldCallback)['RspCode']==='00' && $count('hoadon')===2 && $stock()===16,'Late verified payment is still accounted for after expired link');
    check($service->ipn($oldCallback)['RspCode']==='02' && $stock()===16,'Late duplicate callback does not double settle');
    $clear();$cart();$db->exec('UPDATE giohang SET soluong=7');
    $key=bin2hex(random_bytes(16));
    $direct=$service->place(1,1,'cod',$key,['book_id'=>1,'quantity'=>3]);
    check($stock()===17 && (int)$db->query('SELECT soluong FROM giohang')->fetchColumn()===7,'Buy now reserves only selected quantity and preserves existing cart');
    check((int)$db->query('SELECT soluong FROM chitiethoadon')->fetchColumn()===3,'Buy now invoice contains selected quantity only');
    $service->place(1,1,'cod',$key,['book_id'=>1,'quantity'=>3]);
    check($stock()===17 && $count('hoadon')===1,'Repeated buy-now request does not double reserve');
    $clear();$db->exec('UPDATE sach SET sltonkho=3');
    $service->place(1,1,'cod',bin2hex(random_bytes(16)),['book_id'=>1,'quantity'=>2]);
    reject(fn()=>$service->place(1,1,'cod',bin2hex(random_bytes(16)),['book_id'=>1,'quantity'=>2]),'Next purchase cannot consume stock already reserved by waiting order');
    check($stock()===1 && $count('hoadon')===1,'Stock never goes negative and rejected purchase leaves no invoice');
    $clear();$cart();
    $service->place(1,1,'vnpay',bin2hex(random_bytes(16)),['book_id'=>1,'quantity'=>4]);
    check($stock()===20,'Opening buy-now VNPAY does not reserve before invoice confirmation');
    $service->ipn($callback());
    check($stock()===16 && (int)$db->query('SELECT soluong FROM giohang')->fetchColumn()===2,'VNPAY buy now reserves selected items without consuming cart');
    $data=json_decode(ltrim(file_get_contents(dirname(__DIR__,2).'/user/database/vietnam-addresses.json'),"\xEF\xBB\xBF"),true);
    check(count($data)===34,'34 provinces');$wards=0;$codes=[];
    foreach($data as $province)foreach($province['wards'] as $ward){$wards++;check(!isset($codes[$ward['code']]),'Unique ward code');$codes[$ward['code']]=true;}
    check($data[0]['name']==='Thành phố Hà Nội','Province names decode as UTF-8');
    check($data[0]['wards'][0]['name']==='Phường Ba Đình','Ward names decode as UTF-8');
    check($wards===3321,'3321 wards');
    $valid=['thanhpho'=>$data[0]['name'],'huyen'=>'','xa'=>$data[0]['wards'][0]['name']];
    check(validShippingRegion($valid),'Valid address accepted');
    $valid['xa']='Invalid Ward';check(!validShippingRegion($valid),'Invented ward rejected');
    $legacy=['thanhpho'=>'Hồ Chí Minh','huyen'=>'Quận 1','xa'=>'Phường Bến Nghé'];
    check(validShippingRegion($legacy,$legacy),'Stored historical address preserved');
    echo "PASS: $checks checks; isolated MariaDB database, no live customer data modified.\n";
} finally {
    // Name generated above, never supplied by CLI/user. Only this isolated test database is removed.
    if (preg_match('/^unibook_checkout_test_[a-f0-9]{10}$/D',$name)) $admin->exec("DROP DATABASE `$name`");
}
