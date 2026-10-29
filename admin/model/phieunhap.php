<?php
function getDataPhieuNhap($page, $limit){
    $conn = connectdb();
    if($conn){
        $offset = ($page - 1) * $limit;
        $sqlCount = "SELECT count(*) AS total FROM phieunhap";
        $sttm = $conn->prepare($sqlCount);
        $sttm->execute();
        $totalRecords = $sttm->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords / $limit);

        $sql = "SELECT pn.*, ncc.tenncc, nv.ten AS tennhanvien 
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.idnhacungcap = ncc.idnhacungcap
                LEFT JOIN nhanvien nv ON pn.idnhanvien = nv.idnhanvien
                ORDER BY pn.idphieunhap DESC 
                LIMIT :limit OFFSET :offset";
        $sttm = $conn->prepare($sql);
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

function getDataPhieuNhapTheoKhoangThoiGian($page, $limit, $startDate = null, $endDate = null) {
    $conn = connectdb();
    if ($conn) {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        $params = [];

        if (!empty($startDate)) {
            $conditions[] = "pn.ngaynhap >= :startDate";
            $params[':startDate'] = $startDate;
        }
        if (!empty($endDate)) {
            $conditions[] = "pn.ngaynhap <= :endDate";
            $params[':endDate'] = $endDate;
        }
        $whereSql = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

        $countSql = "SELECT COUNT(*) AS total FROM phieunhap pn $whereSql";
        $stmtCount = $conn->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $totalRecords = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = $limit > 0 ? ceil($totalRecords / $limit) : 1;

        $sql = "SELECT pn.*, ncc.tenncc, nv.ten AS tennhanvien 
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.idnhacungcap = ncc.idnhacungcap
                LEFT JOIN nhanvien nv ON pn.idnhanvien = nv.idnhanvien
                $whereSql 
                ORDER BY pn.idphieunhap DESC";
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        if ($limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

function getAllPhieuNhapFiltered($keyword = null, $startDate = null, $endDate = null) {
    $conn = connectdb();
    if ($conn) {
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(pn.idphieunhap LIKE :kw OR ncc.tenncc LIKE :kw OR nv.ten LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        if (!empty($startDate)) {
            $conditions[] = "pn.ngaynhap >= :startDate";
            $params[':startDate'] = $startDate;
        }
        if (!empty($endDate)) {
            $conditions[] = "pn.ngaynhap <= :endDate";
            $params[':endDate'] = $endDate;
        }

        $whereSql = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT pn.*, ncc.tenncc, nv.ten AS tennhanvien 
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.idnhacungcap = ncc.idnhacungcap
                LEFT JOIN nhanvien nv ON pn.idnhanvien = nv.idnhanvien
                $whereSql 
                ORDER BY pn.idphieunhap DESC";
        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

function delPhieuNhapByID($idphieunhap){
    $conn = connectdb();
    if($conn){
        $sql = 'UPDATE phieunhap SET trangthai = 0 WHERE idphieunhap = :idphieunhap';
        $sttm = $conn->prepare($sql);
        $sttm->bindValue(':idphieunhap', $idphieunhap, PDO::PARAM_INT);
        $sttm->execute();
        $result = $sttm->rowCount();
        if($result > 0){
            return true;
        }
    }
    return false;
}

function searchPhieuNhap($keyword, $page, $limit) {
    $offset = ($page - 1) * $limit;
    $conn = connectdb();
    $keyword = "%" . $keyword . "%";

    $countSql = "SELECT COUNT(*) FROM phieunhap pn 
                 LEFT JOIN nhacungcap ncc ON pn.idnhacungcap = ncc.idnhacungcap
                 LEFT JOIN nhanvien nv ON pn.idnhanvien = nv.idnhanvien
                 WHERE pn.idphieunhap LIKE :kw1 OR ncc.tenncc LIKE :kw2 OR nv.ten LIKE :kw3";
    $stmt = $conn->prepare($countSql);
    $stmt->bindValue(':kw1', $keyword);
    $stmt->bindValue(':kw2', $keyword);
    $stmt->bindValue(':kw3', $keyword);
    $stmt->execute();
    $total = $stmt->fetchColumn();

    $totalPages = ceil($total / $limit);

    $sql = "SELECT pn.*, ncc.tenncc, nv.ten AS tennhanvien 
            FROM phieunhap pn 
            LEFT JOIN nhacungcap ncc ON pn.idnhacungcap = ncc.idnhacungcap
            LEFT JOIN nhanvien nv ON pn.idnhanvien = nv.idnhanvien
            WHERE pn.idphieunhap LIKE :kw1 OR ncc.tenncc LIKE :kw2 OR nv.ten LIKE :kw3
            ORDER BY pn.idphieunhap DESC 
            LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':kw1', $keyword);
    $stmt->bindValue(':kw2', $keyword);
    $stmt->bindValue(':kw3', $keyword);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'data' => $data,
        'currentPage' => $page,
        'totalPages' => $totalPages
    ];
}

function addPhieuNhap($idnhacungcap, $idnhanvien, $ngaynhap, $tongtien) {
    $conn = connectdb();
    if ($conn) {
        $sql = 'INSERT INTO phieunhap (idnhacungcap, idnhanvien, ngaynhap, tongtien) VALUES (?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idnhacungcap, $idnhanvien, $ngaynhap, $tongtien]);
        return $conn->lastInsertId();
    }
    return null;
}

function addCTPhieuNhap($idphieunhap, $idsach, $soluong,  $gia, $loinhuan){
    $conn = connectdb();
    if($conn){
        $sql = 'INSERT INTO chitietphieunhap(idphieunhap, idsach, soluong, gia, loinhuan) VALUES (?,?,?,?,?)';
        $sttm = $conn->prepare($sql);
        return $sttm->execute([$idphieunhap, $idsach, $soluong, $gia, $loinhuan]);
    }
    return null;
}

function getDataPhieuNhapTheoID($idphieunhap){
    $conn = connectdb();
    if($conn){
        $sql = "SELECT pn.*, ncc.tenncc, nv.ten AS tennhanvien 
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.idnhacungcap = ncc.idnhacungcap
                LEFT JOIN nhanvien nv ON pn.idnhanvien = nv.idnhanvien
                WHERE pn.idphieunhap = :idphieunhap";
        $sttm = $conn->prepare($sql);
        $sttm->bindValue(':idphieunhap', $idphieunhap, PDO::PARAM_INT);
        $sttm->execute();
        if($sttm->rowCount() > 0){
            return $sttm->fetch(PDO::FETCH_ASSOC);
        }
    }
    return null;
}

function getDataChiTietPhieuNhapTheoID($idphieunhap){
    $conn = connectdb();
    if($conn){
        $sql = "SELECT ctpn.*, s.tensach 
                FROM chitietphieunhap ctpn 
                LEFT JOIN sach s ON ctpn.idsach = s.idsach 
                WHERE ctpn.idphieunhap = :idphieunhap";
        $sttm = $conn->prepare($sql);
        $sttm->bindValue(':idphieunhap', $idphieunhap, PDO::PARAM_INT);
        $sttm->execute();
        return $sttm->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}
?>
