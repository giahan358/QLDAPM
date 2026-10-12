<?php
include_once '../../model/connectDB.php';
include_once '../../model/phieunhap.php';

$idphieunhap = isset($_GET['idphieunhap']) ? (int)$_GET['idphieunhap'] : null;
$receipt = null;
$itemreceipts = [];

if($idphieunhap){
    $receipt = getDataPhieuNhapTheoID($idphieunhap);
    $itemreceipts = getDataChiTietPhieuNhapTheoID($idphieunhap);
}
?>
<link rel="stylesheet" href="../view/layout/css/ctphieunhap.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eaeaea;
    position: relative;
}

.header-buttons {
    display: flex !important;
    align-items: center !important;
    gap: 15px !important;
}

/* Đảm bảo nút đóng X không bao giờ bị absolute đè lên nút Xuất Excel */
.tab-content .close-button,
.header-buttons .close-button,
button.close-button {
    position: static !important;
    top: auto !important;
    right: auto !important;
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    background-color: #e74c3c !important;
    color: #fff !important;
    border: none !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 18px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    box-shadow: 0 2px 5px rgba(231,76,60,0.3) !important;
    transition: all 0.2s ease !important;
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1 !important;
}

.tab-content .close-button:hover,
.header-buttons .close-button:hover,
button.close-button:hover {
    background-color: #c0392b !important;
    transform: scale(1.08) !important;
}

.btn-export-excel {
    background-color: #27ae60 !important;
    color: white !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 9px 18px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    box-shadow: 0 2px 5px rgba(39,174,96,0.3) !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}

.btn-export-excel:hover {
    background-color: #219653 !important;
    box-shadow: 0 4px 8px rgba(39,174,96,0.4) !important;
    transform: translateY(-1px) !important;
}

.btn-export-excel:active {
    transform: translateY(0) !important;
}
</style>

        <!-- Main Content -->
        <div class="right-content">
            <div id="tab6" class="tab-content active">
                <div class="header-actions">
                    <h2 style="margin: 0;">Chi tiết Phiếu nhập #<?= $idphieunhap ?></h2>
                    <div class="header-buttons">
                        <button type="button" id="btnExportCTPN" class="btn-export-excel" onclick="exportChiTietPhieuNhapCurrent()">
                            <i class="fas fa-file-excel"></i> Xuất Excel
                        </button>
                        <button type="button" class="close-button" onclick="location.href='../controller/index.php?pg=phieunhap'" title="Đóng">✕</button>
                    </div>
                </div>

                <p><strong>ID phiếu nhập:</strong>
                    <input type="text" id="idphieunhap" readonly value="<?= $receipt['idphieunhap'] ?? '' ?>">
                </p>
                <p><strong>Ngày nhập:</strong> 
                    <input type="text" id="ngaynhap" readonly value="<?= $receipt['ngaynhap'] ?? '' ?>">
                </p>
                <p><strong>Nhân viên:</strong> 
                    <input type="text" id="idnhanvien" readonly value="<?= !empty($receipt['tennhanvien']) ? $receipt['tennhanvien'] . ' (ID: ' . $receipt['idnhanvien'] . ')' : ($receipt['idnhanvien'] ?? '') ?>">
                </p>
                <p><strong>Nhà cung cấp:</strong>
                    <input type="text" id="idnhacungcap" readonly value="<?= !empty($receipt['tenncc']) ? $receipt['tenncc'] . ' (ID: ' . $receipt['idnhacungcap'] . ')' : ($receipt['idnhacungcap'] ?? '') ?>">
                </p>  
                <p><strong>Tổng tiền:</strong>
                    <input type="text" id="tongtien" readonly value="<?= !empty($receipt['tongtien']) ? number_format((float)$receipt['tongtien'], 0, ',', '.') . 'đ' : '0đ' ?>">
                </p>

                <div class="ctpn-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>ID Sách</th>
                                <th>Tên Sách</th>
                                <th>Số lượng</th>
                                <th>Giá nhập</th>
                                <th>Lợi nhuận (%)</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="ctpnhap-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="../view/layout/js/xlsx.full.min.js"></script>
    <script>
    if (typeof XLSX === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"><\/script>');
    }
    </script>
</body>
<script>
    const receiptInfo = <?= json_encode($receipt, JSON_UNESCAPED_UNICODE) ?>;
    const itemreceipts = <?= json_encode($itemreceipts, JSON_UNESCAPED_UNICODE) ?>;

    const tbody = document.getElementById('ctpnhap-body');
    tbody.innerHTML = '';

    if (itemreceipts && itemreceipts.length > 0) {
        itemreceipts.forEach((item, index) => {
            const qty = Number(item.soluong) || 0;
            const price = Number(item.gia) || 0;
            const itemTotal = qty * price;
            const bookName = item.tensach || ('Sách #' + item.idsach);

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>S${String(item.idsach).padStart(4, '0')}</strong></td>
                    <td style="text-align: left;">${bookName}</td>
                    <td>${qty}</td>
                    <td>${price.toLocaleString('vi-VN')}đ</td>
                    <td>${item.loinhuan}%</td>
                    <td><strong>${itemTotal.toLocaleString('vi-VN')}đ</strong></td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; color: #888; padding: 20px;">
                    Chưa có chi tiết sản phẩm cho phiếu nhập này.
                </td>
            </tr>
        `;
    }

    function formatDateVN(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
    }

    function exportChiTietPhieuNhapCurrent() {
        if (typeof XLSX === 'undefined') {
            alert('Thư viện xuất Excel chưa sẵn sàng. Vui lòng tải lại trang!');
            return;
        }

        if (!receiptInfo) {
            alert('Không tìm thấy thông tin phiếu nhập để xuất Excel!');
            return;
        }

        const nccName = receiptInfo.tenncc ? `${receiptInfo.tenncc} (ID: ${receiptInfo.idnhacungcap})` : `NCC #${receiptInfo.idnhacungcap}`;
        const nvName = receiptInfo.tennhanvien ? `${receiptInfo.tennhanvien} (ID: ${receiptInfo.idnhanvien})` : `NV #${receiptInfo.idnhanvien}`;
        const isAct = Number(receiptInfo.trangthai) === 1;

        const now = new Date();
        const exportDateStr = ('0' + now.getDate()).slice(-2) + '/' +
            ('0' + (now.getMonth() + 1)).slice(-2) + '/' +
            now.getFullYear() + ' ' +
            ('0' + now.getHours()).slice(-2) + ':' +
            ('0' + now.getMinutes()).slice(-2) + ':' +
            ('0' + now.getSeconds()).slice(-2);

        const wsData = [
            ['PHIẾU NHẬP HÀNG CHI TIẾT'],
            [`Mã Phiếu Nhập: PN${String(receiptInfo.idphieunhap).padStart(4, '0')} | Ngày nhập: ${formatDateVN(receiptInfo.ngaynhap)}`],
            [`Nhà cung cấp: ${nccName}`],
            [`Nhân viên lập: ${nvName}`],
            [`Trạng thái phiếu: ${isAct ? 'Hoạt động' : 'Đã hủy'}`],
            [`Thời gian xuất file: ${exportDateStr}`],
            [], // Hàng trống
            ['STT', 'Mã Sách', 'Tên Sách', 'Số Lượng', 'Đơn Giá Nhập (VNĐ)', 'Lợi Nhuận (%)', 'Thành Tiền (VNĐ)']
        ];

        let totalQty = 0;
        let totalMoney = 0;

        (itemreceipts || []).forEach((item, index) => {
            const qty = Number(item.soluong) || 0;
            const price = Number(item.gia) || 0;
            const profit = Number(item.loinhuan) || 0;
            const itemTotal = qty * price;

            totalQty += qty;
            totalMoney += itemTotal;

            wsData.push([
                index + 1,
                'S' + String(item.idsach).padStart(4, '0'),
                item.tensach || `Sách #${item.idsach}`,
                qty,
                price,
                profit + '%',
                itemTotal
            ]);
        });

        const summaryRowIndex = wsData.length;
        wsData.push([
            'TỔNG CỘNG',
            '',
            '',
            totalQty,
            '',
            '',
            totalMoney
        ]);

        const ws = XLSX.utils.aoa_to_sheet(wsData);

        ws['!cols'] = [
            { wch: 8 },  // STT
            { wch: 14 }, // Mã Sách
            { wch: 34 }, // Tên Sách
            { wch: 14 }, // Số Lượng
            { wch: 22 }, // Giá Nhập
            { wch: 16 }, // Lợi Nhuận
            { wch: 24 }  // Thành Tiền
        ];

        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }, // Tiêu đề
            { s: { r: 1, c: 0 }, e: { r: 1, c: 6 } }, // Mã & Ngày
            { s: { r: 2, c: 0 }, e: { r: 2, c: 6 } }, // NCC
            { s: { r: 3, c: 0 }, e: { r: 3, c: 6 } }, // NV
            { s: { r: 4, c: 0 }, e: { r: 4, c: 6 } }, // Trạng thái
            { s: { r: 5, c: 0 }, e: { r: 5, c: 6 } }, // Ngày xuất
            { s: { r: summaryRowIndex, c: 0 }, e: { r: summaryRowIndex, c: 2 } } // TỔNG CỘNG
        ];

        for (let r = 7; r <= summaryRowIndex; r++) {
            const qtyRef = XLSX.utils.encode_cell({ r: r, c: 3 });
            if (ws[qtyRef] && typeof ws[qtyRef].v === 'number') {
                ws[qtyRef].z = '#,##0';
            }
            const priceRef = XLSX.utils.encode_cell({ r: r, c: 4 });
            if (ws[priceRef] && typeof ws[priceRef].v === 'number') {
                ws[priceRef].z = '#,##0';
            }
            const totalRef = XLSX.utils.encode_cell({ r: r, c: 6 });
            if (ws[totalRef] && typeof ws[totalRef].v === 'number') {
                ws[totalRef].z = '#,##0';
            }
        }

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, `PhieuNhap_${receiptInfo.idphieunhap}`);

        const fileName = `Phieu_Nhap_Hang_PN${String(receiptInfo.idphieunhap).padStart(4, '0')}.xlsx`;
        XLSX.writeFile(wb, fileName);
    }
</script>
</html>
