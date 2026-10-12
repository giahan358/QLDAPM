<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/nhanvien.php';
    include_once '../../model/nhacungcap.php';
    include_once '../../model/phieunhap.php';
    include_once '../../model/sanpham.php';


    $idnhanvien = isset($_SESSION['idnhanvien']) || $_SESSION['loggedin'] == true;
    echo "<script>console.log('" . $_SESSION['idnhanvien'] . "');</script>";
    $nhanvien = getDataNhanVienTheoId($idnhanvien);
    $cmbNhaCungCap = loadCmbNCC();

    //sau khi gửi dữ liệu đến server bằng ajax thì lúc này phải gọi lại chúng để xử lý lưu  
    if (isset($_GET['pg']) && $_GET['pg'] == 'themphieunhap') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            ob_clean(); // Xóa buffer
            header('Content-Type: application/json');
    
            $input = json_decode(file_get_contents('php://input'), true);
    
            $idNhaCungCap = $input['idNhaCungCap'] ?? null;
            $idNhanVien = $input['idNhanVien'] ?? null;
            $ngayNhap = $input['ngayNhap'] ?? null;
            $tongTien = $input['tongTien'] ?? null;
            $chiTietPhieuNhap = $input['chiTietPhieuNhap'] ?? [];
    
            // Kiểm tra dữ liệu đầu vào
            if (!$idNhaCungCap || !$idNhanVien || !$ngayNhap || !$tongTien) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu đầu vào không hợp lệ']);
                exit;
            }
            // Lưu phiếu nhập và lấy ID
            $idPhieuNhap = addPhieuNhap($idNhaCungCap, $idNhanVien, $ngayNhap, $tongTien);
    
            if ($idPhieuNhap) {
                // Lưu chi tiết phiếu nhập
                foreach ($chiTietPhieuNhap as $chiTiet) {
                    addCTPhieuNhap($idPhieuNhap, $chiTiet['idSach'], $chiTiet['soLuong'], $chiTiet['gia'],  $chiTiet['loinhuan']);
                    $sanpham = getDataSanPhamTheoId($chiTiet['idSach']);
                    $soluongcu = $sanpham['sltonkho'];
                    $soluongmoi = $soluongcu + $chiTiet['soLuong'];
                    updateSoLuongTheoId($chiTiet['idSach'], $soluongmoi);
                
                    $giacu = $sanpham['gia'];
                    $giamoi =  (($chiTiet['loinhuan'] /100) * ($chiTiet['gia'])) + $chiTiet['gia'];
                    if($giacu < $giamoi){
                        updateDonGiaTheoId($chiTiet['idSach'], $giamoi);
                    }
                }                
                echo json_encode(['success' => true, 'idPhieuNhap' => $idPhieuNhap]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu phiếu nhập']);
            }
            exit;
        }
    }
?>

        <div class="container">
            <div class="left-pane">
                <h2>Danh sách sản phẩm</h2>
                <div class="table-container">
                    <table id="product-table">
                        <thead>
                            <tr>
                                <th>Hình ảnh</th>
                                <th>ID</th>
                                <th>Tên Sách</th>
                                <th>Tác Giả</th>
                                <th>Nhà Xuất Bản</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Trạng thái</th>
                                <th>Chọn</th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            
                        </tbody>
                    </table>
                </div>

                <h2>Nhập hàng</h2>
                <div class="table-container">
                    <table id="receipt-details-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên Sách</th>
                                <th>Tác Giả</th>
                                <th>Nhà Xuất Bản</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Lợi Nhuận(%)</th>
                                <th>Thành tiền</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="cart-table-body">
                            
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="right-pane">
                <form action="../controller/index.php?pg=themphieunhap" method="post" id="themvaogio">
                    <h2>Thông tin Sản phẩm</h2>
                    <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=phieunhap'">X</button>

                    <div class="form-group">
                        <label for="nhaCungCap"><b>Nhà cung cấp:</b></label>
                        <select name="cmbNCC" id ="cmbNCC" onchange="khoaCmbNCC()">
                            <option value="">----Chọn nhà cung cấp---</option>
                            <?php foreach($cmbNhaCungCap as $cmbNCC): ?>
                                <option value="<?=$cmbNCC['idnhacungcap']?>"><?=$cmbNCC['idnhacungcap'].' - '.$cmbNCC['tenncc']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="IDSach"><b>ID Sách:</b></label>
                        <input type="text" id="IDSach" name="IDSach" readonly>
                    </div>

                    <div class="form-group">
                        <label for="tenSach"><b>Tên Sách:</b></label>
                        <input type="text" id="tenSach" name="tenSach" readonly>
                    </div>

                    <div class="form-group">
                        <label for="tacGia"><b>Tác giả:</b></label>
                        <input type="text" id="tacGia" name="tacGia" readonly>
                    </div>

                    <div class="form-group">
                        <label for="nxb"><b>Nhà xuất bản:</b></label>
                        <input type="text" id="nxb" name="nxb" readonly>
                    </div>

                    <div class="form-group">
                        <label for="danhMuc"><b>Danh mục:</b></label>
                        <input type="text" id="danhMuc" name="danhMuc" readonly>
                    </div>

                    <div class="form-group">
                        <label for="price"><b>Giá:</b></label>
                        <input type="text" id="price" name="price" required>
                    </div>

                    <div class="form-group">
                        <label for="quantity"><b>Số lượng:</b></label>
                        <input type="text" id="quantity" name="quantity" required>
                    </div>

                    <div class="form-group">
                        <label for="loiNhuan"><b>Lợi nhuận(%):</b></label>
                        <input type="text" id="loiNhuan" name="loiNhuan" required>
                    </div>

                    <div class="form-group">
                        <label for="nhanVienNhapHang"><b>Nhân viên nhập hàng:</b></label>
                        <input type="text" id="nhanVienNhapHang" name="nhanVienNhapHang" value="<?=$nhanvien['idnhanvien'].' - '.$nhanvien['ten']?>"  readonly>
                    </div>

                    <div class="form-actions" style="display: flex; justify-content: space-between; gap: 20px;">
                        <button type="button" class="btn" onclick="themvaogiohang()">Thêm vào giỏ hàng</button>
                        <button type="button" class="btn" onclick="luuPhieuNhap()">Xác nhận</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <link rel="stylesheet" href="../view/layout/css/formPhieuNhap.css">
</body>

<script src="../view/layout/js/sanpham_phieunhap.js"></script>
<script>
    // tab mặc định
    loadProducts(1);

    function khoaCmbNCC() {
        document.getElementById('cmbNCC').setAttribute("disabled", true);
    }

    function themvaogiohang() {
        // Lấy giá trị từ các trường input
        const idSach = document.getElementById('IDSach').value;
        const tenSach = document.getElementById('tenSach').value;
        const tacGia = document.getElementById('tacGia').value;
        const nxb = document.getElementById('nxb').value;
        const danhMuc = document.getElementById('danhMuc').value;
        const gia = document.getElementById('price').value;
        const soLuong = document.getElementById('quantity').value;
        const loiNhuan = document.getElementById('loiNhuan').value;

        // Kiểm tra sản phẩm đã chọn chưa
        if (!idSach || !tenSach) {
            alert("Vui lòng chọn sản phẩm trước khi thêm vào giỏ hàng!");
            return;
        }

        const cmbNCC = document.getElementById('cmbNCC');
        const selectedNCC = cmbNCC.value;
        
        if (!selectedNCC || selectedNCC === 'default') {
            alert("Vui lòng chọn Nhà Cung Cấp trước khi thêm vào giỏ!");
            return;
        }

        // Kiểm tra giá
        if (!gia || isNaN(gia) || parseFloat(gia) <= 0) {
            alert("Vui lòng nhập đơn giá hợp lệ!");
            return;
        }

        // Kiểm tra số lượng
        if (!soLuong || isNaN(soLuong) || parseInt(soLuong) <= 0) {
            alert("Vui lòng nhập số lượng hợp lệ!");
            return;
        }

        // Kiểm tra lợi nhuận
        if (!loiNhuan || isNaN(loiNhuan) || parseFloat(loiNhuan) < 0) {
            alert("Vui lòng nhập lợi nhuận hợp lệ!");
            return;
        }

        // Tính thành tiền (giá × số lượng)
        const thanhTien = (parseFloat(gia) * parseInt(soLuong)).toLocaleString('vi-VN');

        // Lấy bảng nhập hàng
        const cartTableBody = document.getElementById('cart-table-body');

        // Tạo hàng mới cho bảng nhập hàng
        const row = `
            <tr>
                <td>${idSach}</td>
                <td>${tenSach}</td>
                <td>${tacGia}</td>
                <td>${nxb}</td>
                <td>${danhMuc}</td>
                <td>${Number(gia).toLocaleString('vi-VN')}đ</td>
                <td>${soLuong}</td>
                <td>${loiNhuan}%</td>
                <td>${thanhTien}đ</td>
                <td>
                    <a href="#" onclick="this.parentElement.parentElement.remove()">
                        <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                    </a>
                </td>
            </tr>
        `;

        // Thêm hàng mới vào bảng
        cartTableBody.innerHTML += row;

        // Xóa dữ liệu trong form
        document.getElementById('IDSach').value = '';
        document.getElementById('tenSach').value = '';
        document.getElementById('tacGia').value = '';
        document.getElementById('nxb').value = '';
        document.getElementById('danhMuc').value = '';
        document.getElementById('price').value = '';
        document.getElementById('quantity').value = '';
        document.getElementById('loiNhuan').value = '';

        // Vô hiệu hóa nút chọn nhà cung cấp nếu đã chọn
        khoaCmbNCC();
    }
    function luuPhieuNhap() {
    const idnhacungcap = document.getElementById('cmbNCC').value;
    if (!idnhacungcap) {
        alert("Vui lòng chọn nhà cung cấp để nhập hàng");
        document.getElementById('cmbNCC').removeAttribute('disabled');
        return;
    }

    const nhanvien = document.getElementById('nhanVienNhapHang').value;
    const idnhanvien = nhanvien.split('-')[0].trim();
    const data = document.getElementById('cart-table-body');
    const rows = data.getElementsByTagName('tr');

    if (rows.length === 0) {
        alert("Vui lòng thêm sản phẩm vào giỏ hàng rồi mới nhấn xác nhận!");
        return;
    }

    let chitietphieunhap = [];
    let tongtien = 0;

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        const idSach = cells[0].innerText;
        const gia = parseFloat(cells[5].innerText.replace(/[^\d]/g, ''));
        const soLuong = parseInt(cells[6].innerText);
        const loiNhuan = parseFloat(cells[7].innerText.replace('%', ''));
        const thanhTien = parseFloat(cells[8].innerText.replaceAll('.', '').replace(/[^0-9]/g, ''));

        chitietphieunhap.push({
            idSach: idSach,
            soLuong: soLuong,
            gia: gia,
            loinhuan: loiNhuan
        });

        tongtien += thanhTien;
    }

    const dataPhieuNhap = {
        idNhaCungCap: idnhacungcap,
        idNhanVien: idnhanvien,
        ngayNhap: new Date().toISOString().split('T')[0],
        tongTien: tongtien,
        chiTietPhieuNhap: chitietphieunhap
    };

    console.log('Dữ liệu gửi đi:', JSON.stringify(dataPhieuNhap));

    fetch('../controller/index.php?pg=themphieunhap', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(dataPhieuNhap)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Lỗi HTTP: ' + response.status);
        }
        return response.text();
    })
    .then(responseText => {
        console.log('Response text:', responseText);
        if (!responseText) {
            throw new Error('Phản hồi từ server rỗng');
        }
        let res;
        try {
            res = JSON.parse(responseText);
        } catch (e) {
            console.error('Parse error:', e);
            throw new Error('Phản hồi không phải JSON hợp lệ: ' + responseText);
        }

        if (res.success) {
            alert('Lưu phiếu nhập thành công!');
            document.getElementById('cart-table-body').innerHTML = '';
            document.getElementById('cmbNCC').removeAttribute('disabled');
            document.getElementById('cmbNCC').value = '';
        } else {
            alert('Lỗi: ' + (res.message || 'Không xác định'));
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra: ' + error.message);
    });
    loadProducts(1);
}

</script>

</html>
