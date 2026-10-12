<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/hoadon.php';
    include_once '../../model/chitiethoadon.php';
    include_once '../../model/thongtinnhanhang.php';
    include_once '../../model/khachhang.php';
    include_once '../../model/sanpham.php';

    if (empty($_SESSION['order_admin_csrf'])) $_SESSION['order_admin_csrf']=bin2hex(random_bytes(32));
    $idHD = isset($_GET['idhoadon']) ? $_GET['idhoadon']: null;
    $idnhanvien = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['idnhanvien'])) ? $_SESSION['idnhanvien'] : null;    echo "<script>console.log('ID Nhân viên: " . $idnhanvien . "');</script>";
    if($idHD) {
        $hoadon = getDataHoaDonByID($idHD);
        $cthds = getCTHoaDonByIDHoaDon($idHD);
        $diachi = getDataThongTinNhanHangByID($hoadon['iddiachi']);
        $khachhang = getDataKhachHangTheoId($hoadon['idkhachhang']);
        // Email người nhận có thể để trống; dùng email tài khoản khách hàng làm dự phòng.
        $emailHienThi = trim((string)($diachi['emailNgNhan'] ?? '')) !== ''
            ? $diachi['emailNgNhan'] : ($khachhang['email'] ?? '');

        if($_SERVER['REQUEST_METHOD'] == "POST"){
            $trangthai = $_POST['trangthai'];
           
            $token=$_POST['csrf_token'] ?? '';
            $result=is_string($token) && hash_equals($_SESSION['order_admin_csrf'],$token) && $idnhanvien
                && updateHoaDonById($idHD, $idnhanvien, $trangthai);
            if($result){
                echo "<script>
                alert('Cập nhật thông tin hóa đơn thành công!');
                window.location.href = '../controller/index.php?pg=hoadon';
                </script>";
            }else{
                echo '<script>alert('.json_encode($GLOBALS['order_update_error'] ?? 'Phiên không hợp lệ. Vui lòng tải lại trang.', JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP).')</script>';
            }
        }
    }

    $quayVe = (isset($_GET['from']) && $_GET['from'] === 'thongke') 
    ? '../controller/index.php?pg=thongke' 
    : '../controller/index.php?pg=hoadon';
?>


        <div class="container">
            <form action="../controller/index.php?pg=xetduyetHD&idhoadon=<?=$hoadon['idhoadon']?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['order_admin_csrf'],ENT_QUOTES,'UTF-8')?>">
                    <h2>Chi Tiết Đơn Hàng</h2>
                    <p><b>Thanh toán:</b> <?=htmlspecialchars((int)$hoadon['trangthai']===3 && in_array($hoadon['phuongthuctt'],['cod','Thanh toán khi nhận hàng'],true) && $hoadon['trangthai_thanhtoan']==='PENDING' ? 'Đã hủy — không thu tiền' : (['PENDING'=>'Chưa thanh toán','PAID'=>'Đã thanh toán','FAILED'=>'Thất bại','REFUNDED'=>'Đã hoàn tiền'][$hoadon['trangthai_thanhtoan']] ?? $hoadon['trangthai_thanhtoan']),ENT_QUOTES,'UTF-8')?></p>
                    <?php if((int)$hoadon['trangthai']!==2): ?><p><b>Yêu cầu hủy:</b> <?=htmlspecialchars(['NONE'=>'Không có','REQUESTED'=>'Chờ xét duyệt','APPROVED'=>'Đã duyệt hủy','REJECTED'=>'Đã từ chối'][$hoadon['cancel_status'] ?? 'NONE'] ?? '',ENT_QUOTES,'UTF-8')?> · <a href="../controller/index.php?pg=hoadon#admin-after-sales">Xử lý hủy / hoàn tiền</a></p><?php endif; ?>
                    <p><b>Kho:</b> <?=(int)$hoadon['stock_deducted']===1?'Đã trừ kho cho đơn hàng':'Chưa trừ kho hoặc đã hoàn kho khi hủy'?></p>
                    <?php if($hoadon['received_at']): ?><p><b>Nhận hàng:</b> <?=htmlspecialchars($hoadon['received_at'],ENT_QUOTES,'UTF-8')?> · Hạn đánh giá: <?=htmlspecialchars(date('Y-m-d H:i:s',strtotime($hoadon['received_at'].' +10 days')),ENT_QUOTES,'UTF-8')?></p><?php endif; ?>
                    <button type="button" class="close-button" onclick="location.href='<?=$quayVe?>'">X</button>
                    <div class="form-grid">
                        <div class="form-column">
                            <div class="form-group">
                                <label>Mã hóa đơn</label>
                                <input type="text" value="<?=$hoadon['idhoadon']?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Phương thức thanh toán</label>
                                <input type="text" value="<?=$hoadon['phuongthuctt']?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" value="<?=$diachi['sdtNgNhan']?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?=htmlspecialchars((string)$emailHienThi, ENT_QUOTES, 'UTF-8')?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="status"><b>Trạng thái</b></label>
                                <select name="trangthai" <?=($hoadon['cancel_status'] ?? '')==='REQUESTED'?'disabled':''?>>
                                    <?php if ($hoadon['trangthai'] == 0) {  ?>
                                    <option value="0" <?=$hoadon['trangthai'] ==0 ? 'selected' : ''?>>Chờ xác nhận</option>
                                    <option value="1" <?=$hoadon['trangthai'] ==1 ? 'selected' : ''?>>Đang giao</option>
                                                                        <option value="3" <?=$hoadon['trangthai'] ==3 ? 'selected' : ''?>>Đã hủy</option>
                                    <?php }else if($hoadon['trangthai'] == 1){?>
                                    <option value="1" <?=$hoadon['trangthai'] ==1 ? 'selected' : ''?>>Đang giao</option>
                                    <option value="2" <?=$hoadon['trangthai'] ==2 ? 'selected' : ''?>>Đã nhận hàng</option>
                                    <option value="3" <?=$hoadon['trangthai'] ==3 ? 'selected' : ''?>>Đã hủy</option>
                                    <?php }else if($hoadon['trangthai'] == 2) { ?>
                                        <option value="2" <?=$hoadon['trangthai'] ==2 ? 'selected' : ''?>>Đã nhận hàng</option>
                                    <?php }else if($hoadon['trangthai'] == 3) { ?>
                                        <option value="3" <?=$hoadon['trangthai'] ==3 ? 'selected' : ''?>>Đã hủy</option>
                                    <?php } ?>


                                </select>
                            </div>
                        </div>

                        <div class="form-column">
                            <div class="form-group">
                                <label>Mã khách hàng</label>
                                <input type="text" value="<?=$hoadon['idkhachhang']?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Người nhận</label>
                                <input type="text" value="<?=$diachi['hotenNgNhan']?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Ngày xuất</label>
                                <input type="text" value="<?=$hoadon['ngayxuat']?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Tổng tiền</label>
                                <input type="text" value="<?= number_format($hoadon['tongtien'], 0, '.', '.') . ' đ' ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Địa chỉ nhận hàng</label>
                                <input type="text" value="<?=$diachi['diachi_chitiet']?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="scroll-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên sách</th>
                                    <th>Tác giả</th>
                                    <th>Nhà xuất bản</th>
                                    <th>Danh mục</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cthds as $cthd): ?>
                                    <tr>
                                        <td><?=$cthd['idsach']?></td>
                                        <td><?=$cthd['tensach']?></td>
                                        <td><?=$cthd['idtacgia']?></td>
                                        <td><?=$cthd['idnhaxuatban']?></td>
                                        <td><?=$cthd['idctdanhmuc']?></td>
                                        <td><?=$cthd['soluong']?></td>
                                        <td><?= number_format($cthd['gia'], 0, '.', '.') ?> đ</td>
                                        <td><?= number_format($cthd['thanhtien'], 0, '.', '.') ?> đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn" <?=($hoadon['cancel_status'] ?? '')==='REQUESTED' || (int)$hoadon['trangthai']>=2?'disabled':''?>>Cập nhật trạng thái</button>
                    </div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/form.css">
    <link rel="stylesheet" href="../view/layout/css/xetduyethoadon.css">
</body>
</html>
