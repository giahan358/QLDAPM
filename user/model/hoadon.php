<?php
function getDataHoaDon($page, $limit){
    $conn = connectdb();
    if($conn){
        $offset = ($page -1)*$limit;
        $sqlCount ="SELECT count(*) AS total FROM hoadon ";
        $sttm = $conn->prepare($sqlCount);
        $sttm->execute();
        $totalRecords = $sttm->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords/$limit);
        $sql = "SELECT * FROM hoadon LIMIT :limit OFFSET :offset";
        $sttm = $conn->prepare($sql);
        $sttm->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sttm->bindValue(':offset', $offset, PDO::PARAM_INT);
        $sttm->execute();
        $data = $sttm->fetchAll(PDO::FETCH_ASSOC);
        return [
            'data'=>$data,
            'currentPage'=>$page,
            'totalPages'=>$totalPages
        ];
    }
    return [
        'data'=>[],
        'currentPage'=>1,
        'totalPages'=>[]
    ];
}

function getDataHoaDonTheoKhoangThoiGian($page, $limit, $startDate, $endDate) {
    $conn = connectdb();
    if ($conn) {
        $offset = ($page - 1) * $limit;
        $sqlCount = "SELECT count(*) AS total FROM hoadon WHERE ngayxuat BETWEEN :startDate AND :endDate";
        $sttm = $conn->prepare($sqlCount);
        $sttm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $sttm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $sttm->execute();
        $totalRecords = $sttm->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords / $limit);
        $sql = "SELECT * FROM hoadon WHERE ngayxuat BETWEEN :startDate AND :endDate LIMIT :limit OFFSET :offset";
        $sttm = $conn->prepare($sql);
        $sttm->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $sttm->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $sttm->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sttm->bindValue(':offset', $offset, PDO::PARAM_INT);
        $sttm->execute();
        $data = $sttm->fetchAll(PDO::FETCH_ASSOC);
        return [
            'data' => $data,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }
    return [
        'data' => [],
        'currentPage' => 1,
        'totalPages' => 0
    ];
}

function getDataHoaDonByID($idhoadon){
    $conn = connectdb();
    if($conn){
        try{
            $sql = "SELECT * from hoadon WHERE idhoadon = :idhoadon ";
            $sttm =$conn->prepare($sql);
            $sttm->bindValue(':idhoadon', $idhoadon, PDO::PARAM_INT);
            $sttm->execute();
            $success = $sttm->rowCount()>0;
            if($success){
                return $sttm->fetch(PDO::FETCH_ASSOC);
            }else{
                return null;
            }
        }catch(PDOException $e){
            error_log("Không tìm thấy thông tin cụ thể của hóa đơn!", $e->getMessage());
        }
    }
    return null;
}

function addHoaDon($idkhachhang, $iddiachi, $idnhanvien, $phuongthuctt, $ngayxuat, $tongtien, $trangthai = 1) {
    $conn = connectdb();
    if ($conn) {
        try {
            $sql = "INSERT INTO hoadon (idkhachhang, iddiachi, idnhanvien, phuongthuctt, ngayxuat, tongtien, trangthai) 
                    VALUES (:idkhachhang, :iddiachi, :idnhanvien, :phuongthuctt, :ngayxuat, :tongtien, :trangthai)";
            $sttm = $conn->prepare($sql);
            $sttm->bindValue(':idkhachhang', $idkhachhang, PDO::PARAM_INT);
            $sttm->bindValue(':iddiachi', $iddiachi, PDO::PARAM_INT);
            $sttm->bindValue(':idnhanvien', $idnhanvien, $idnhanvien === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $sttm->bindValue(':phuongthuctt', $phuongthuctt, PDO::PARAM_STR);
            $sttm->bindValue(':ngayxuat', $ngayxuat, PDO::PARAM_STR);
            $sttm->bindValue(':tongtien', $tongtien, PDO::PARAM_STR);
            $sttm->bindValue(':trangthai', $trangthai, PDO::PARAM_INT);

            $result = $sttm->execute();
            return $result ? $conn->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Lỗi khi thêm hóa đơn: " . $e->getMessage());
            return false;
        }
    }
    return false;
}

function getOrdersByCustomerId($idKhachHang) {
    $conn = connectdb();
    if ($conn) {
        try {
            $conn->exec("SET time_zone='+07:00'");
            $sql = "SELECT h.idhoadon,h.ngayxuat,h.tongtien,h.trangthai,h.trangthai_thanhtoan,h.cancel_status,
                    h.received_at,DATE_ADD(h.received_at,INTERVAL 10 DAY) AS review_deadline,
                    (h.trangthai=2 AND h.received_at IS NOT NULL AND NOW()>=h.received_at AND NOW()<DATE_ADD(h.received_at,INTERVAL 10 DAY)
                      AND EXISTS (SELECT 1 FROM chitiethoadon d WHERE d.idhoadon=h.idhoadon AND NOT EXISTS (SELECT 1 FROM product_reviews pr WHERE pr.order_id=h.idhoadon AND pr.book_id=d.idsach))) AS can_review,
                    (EXISTS (SELECT 1 FROM chitiethoadon d WHERE d.idhoadon=h.idhoadon) AND NOT EXISTS (SELECT 1 FROM chitiethoadon d WHERE d.idhoadon=h.idhoadon AND NOT EXISTS (SELECT 1 FROM product_reviews pr WHERE pr.order_id=h.idhoadon AND pr.book_id=d.idsach))) AS reviews_complete,
                    r.status AS refund_status,c.decision_note
                    FROM hoadon h LEFT JOIN order_refunds r ON r.order_id=h.idhoadon
                    LEFT JOIN order_cancel_requests c ON c.order_id=h.idhoadon
                    WHERE h.idkhachhang = :idkhachhang ORDER BY h.idhoadon DESC";
            $sttm = $conn->prepare($sql);
            $sttm->bindValue(':idkhachhang', $idKhachHang, PDO::PARAM_INT);
            $sttm->execute();
            $result = $sttm->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi khi lấy danh sách hóa đơn: " . $e->getMessage());
            return [];
        }
    }
    return [];
}

function countOrdersByCustomerId($idKhachHang) {
    $conn = connectdb();
    if ($conn) {
        try {
            $sql = "SELECT COUNT(*) AS total FROM hoadon WHERE idkhachhang = :idkhachhang";
            $sttm = $conn->prepare($sql);
            $sttm->bindValue(':idkhachhang', $idKhachHang, PDO::PARAM_INT);
            $sttm->execute();
            $result = $sttm->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Lỗi khi đếm số lượng hóa đơn: " . $e->getMessage());
            return 0;
        }
    }
    return 0;
}
?>
