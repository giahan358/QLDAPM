<?php
declare(strict_types=1);

/** Checkout reserves stock; shipping supports legacy unreserved orders; cancellation restores once. */
final class OrderReceipt {
    public static function transition(PDO $db, int $id, int $next, ?int $staff, ?int $customer = null): void {
        if ($id < 1 || !in_array($next,[0,1,2,3],true)) throw new DomainException('Trạng thái không hợp lệ.');
        $db->beginTransaction();
        try {
            $q=$db->prepare('SELECT * FROM hoadon WHERE idhoadon=? FOR UPDATE');$q->execute([$id]);$order=$q->fetch(PDO::FETCH_ASSOC);
            if (!$order || ($customer !== null && (int)$order['idkhachhang'] !== $customer)) throw new DomainException('Không tìm thấy đơn hàng của bạn.');
            $current=(int)$order['trangthai'];
            if(($order['cancel_status'] ?? 'NONE')==='REQUESTED' && $next!==3)throw new DomainException('Đơn đang chờ xét duyệt hủy. Vui lòng xử lý yêu cầu hủy trước.');
            if($next===3 && strtoupper($order['phuongthuctt'])==='VNPAY' && $order['trangthai_thanhtoan']==='PAID')throw new DomainException('Hãy sử dụng luồng duyệt hủy và hoàn tiền cho đơn VNPAY.');
            if ($customer !== null) {
                if ($next!==2 || !in_array($current,[1,2],true)) throw new DomainException('Đơn chưa được cửa hàng xác nhận để nhận hàng.');
            } else {
                $q=$db->prepare('SELECT idnhanvien FROM nhanvien WHERE idnhanvien=? AND trangthai=1');$q->execute([$staff]);
                if (!$q->fetchColumn()) throw new DomainException('Không có quyền cập nhật đơn hàng.');
            }
            // Receipt confirms collection for COD, including an idempotent repeat on a legacy order.
            if ($next===2 && in_array($current,[1,2],true) && in_array($order['phuongthuctt'],['cod','Thanh toán khi nhận hàng'],true) && $order['trangthai_thanhtoan']==='PENDING') {
                $q=$db->prepare("UPDATE hoadon SET trangthai_thanhtoan='PAID' WHERE idhoadon=?");$q->execute([$id]);
            }
            if ($current===$next) { $db->commit(); return; }
            if (!in_array($next, [0=>[1,3],1=>[2,3],2=>[],3=>[]][$current] ?? [],true)) throw new DomainException('Không thể chuyển trạng thái đơn hàng.');
            if (strtoupper($order['phuongthuctt'])==='VNPAY' && in_array($next,[1,2],true) && $order['trangthai_thanhtoan']!=='PAID') throw new DomainException('VNPAY chưa xác nhận thanh toán.');
            $deducted=(int)$order['stock_deducted'];
            if (($next===1 && !$deducted) || ($next===3 && $deducted)) {
                $q=$db->prepare('SELECT idsach,SUM(soluong) AS quantity FROM chitiethoadon WHERE idhoadon=? GROUP BY idsach ORDER BY idsach');$q->execute([$id]);$items=$q->fetchAll(PDO::FETCH_ASSOC);
                if (!$items) throw new DomainException('Đơn hàng không có chi tiết sản phẩm.');
                foreach ($items as $item) {
                    $qty=(int)$item['quantity'];
                    if ($qty<1) throw new DomainException('Số lượng sản phẩm không hợp lệ.');
                    if ($next===1) {
                        $q=$db->prepare('UPDATE sach SET sltonkho=sltonkho-? WHERE idsach=? AND sltonkho>=?');$q->execute([$qty,$item['idsach'],$qty]);
                    } else {
                        // Release stock reserved by checkout or legacy shipping.
                        $q=$db->prepare('UPDATE sach SET sltonkho=sltonkho+? WHERE idsach=?');$q->execute([$qty,$item['idsach']]);
                    }
                    if ($q->rowCount()!==1) throw new DomainException('Tồn kho không đủ hoặc sản phẩm không còn tồn tại. Chưa thể chuyển sang Đang giao.');
                }
                $deducted=$next===1?1:0;
            }
            $q=$db->prepare('UPDATE hoadon SET trangthai=?,stock_deducted=?,received_at=CASE WHEN ?=2 THEN NOW() ELSE received_at END,idnhanvien=COALESCE(?,idnhanvien) WHERE idhoadon=?');
            $q->execute([$next,$deducted,$next,$staff,$id]);
            $db->commit();
        } catch (Throwable $e) { if($db->inTransaction())$db->rollBack();throw $e; }
    }
}
