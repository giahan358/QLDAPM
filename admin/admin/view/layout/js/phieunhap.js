function formatDateVN(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
}

function renderReceiptRows(receipts, tbody) {
    tbody.replaceChildren();

    if (!receipts || receipts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #888;">
                    Không tìm thấy phiếu nhập nào!
                </td>
            </tr>
        `;
        return;
    }

    receipts.forEach(receipt => {
        const nccName = receipt.tenncc ? `${receipt.tenncc} (ID: ${receipt.idnhacungcap})` : receipt.idnhacungcap;
        const nvName = receipt.tennhanvien ? `${receipt.tennhanvien} (ID: ${receipt.idnhanvien})` : receipt.idnhanvien;
        const isAct = Number(receipt.trangthai) === 1;

        const row = `
            <tr>
                <td>${receipt.idphieunhap}</td>
                <td>${nccName}</td>
                <td>${nvName}</td>
                <td>${formatDateVN(receipt.ngaynhap)}</td>
                <td><strong>${Number(receipt.tongtien || 0).toLocaleString('vi-VN')}đ</strong></td>
                <td>
                    <span style="color: ${isAct ? '#28a745' : '#dc3545'}; font-weight: 600;">
                        ${isAct ? 'Hoạt động' : 'Đã hủy'}
                    </span>
                </td>
                <td>
                    <a href="../controller/index.php?pg=chitietphieunhap&idphieunhap=${receipt.idphieunhap}" title="Xem chi tiết">
                        <img src="../view/layout/logo-icon/detail-icon.png" alt="Xem chi tiết" style="width:20px; height:20px;">
                    </a>
                </td>
                <td>
                    <a href="javascript:void(0)" onclick="exportSinglePhieuNhap(${receipt.idphieunhap})" title="Xuất Excel phiếu #${receipt.idphieunhap}">
                        <i class="fas fa-file-excel icon-export-single"></i>
                    </a>
                </td>
                <td>
                    ${isAct ? `
                    <a href="../controller/index.php?pg=xoaphieunhap&idphieunhap=${receipt.idphieunhap}" onclick="return confirm('Bạn có chắc muốn xóa phiếu nhập #${receipt.idphieunhap} ?')" title="Xóa phiếu nhập">
                        <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                    </a>
                    ` : '<span style="color:#aaa;">-</span>'}
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function updatePagination(totalPages, currentPage, onPageClick) {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;
    pagination.replaceChildren();

    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `
            <button data-page="${i}" ${i === Number(currentPage) ? 'class="active"' : ''}>${i}</button>
        `;
    }

    pagination.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function () {
            const page = parseInt(this.getAttribute('data-page'));
            onPageClick(page);
        });
    });
}

function loadPage(page = 1) {
    fetch(`../controller/pagPhieuNhap.php?page=${page}`)
        .then(response => {
            if (!response.ok) throw new Error('Không thể tải dữ liệu phiếu nhập');
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('receipt-table-body');
            renderReceiptRows(data.data, tbody);
            updatePagination(data.totalPages, data.currentPage, loadPage);
        })
        .catch(error => console.error('Error fetching data:', error));
}

function searchPhieuNhap(keyword, page = 1) {
    fetch(`../controller/pagPhieuNhap.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) throw new Error('Không thể tìm kiếm phiếu nhập');
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('receipt-table-body');
            renderReceiptRows(data.data, tbody);
            updatePagination(data.totalPages, data.currentPage, (newPage) => searchPhieuNhap(keyword, newPage));
        })
        .catch(error => console.error('Error fetching search data:', error));
}

function applyFiltersPN() {
    const startDate = document.getElementById('start').value;
    const endDate = document.getElementById('end').value;

    if (!startDate && !endDate) {
        loadPage(1);
        return;
    }

    if (startDate && endDate) {
        if (new Date(startDate) > new Date(endDate)) {
            alert('Ngày bắt đầu không được lớn hơn ngày kết thúc!');
            return;
        }
    }

    loadReceiptData(startDate, endDate, 1);
}

function loadReceiptData(startDate, endDate, page = 1) {
    fetch(`../controller/pagPhieuNhap.php?page=${page}&start=${startDate}&end=${endDate}`)
        .then(response => {
            if (!response.ok) throw new Error('Không thể lọc phiếu nhập');
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('receipt-table-body');
            renderReceiptRows(data.data, tbody);
            updatePagination(data.totalPages, data.currentPage, (newPage) => loadReceiptData(startDate, endDate, newPage));
        })
        .catch(error => console.error('Error fetching filter data:', error));
}

// =================== XUẤT EXCEL TOÀN BỘ DANH SÁCH ===================
async function exportDanhSachPhieuNhap() {
    if (typeof XLSX === 'undefined') {
        alert('Thư viện xuất Excel chưa sẵn sàng. Vui lòng tải lại trang!');
        return;
    }

    const startDate = document.getElementById('start').value;
    const endDate = document.getElementById('end').value;
    const keyword = document.getElementById('searchPN').value.trim();

    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        alert('Ngày bắt đầu không được lớn hơn ngày kết thúc!');
        return;
    }

    const btn = document.getElementById('btnExportAllPN');
    const oldHtml = btn ? btn.innerHTML : 'Xuất Excel';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xuất...';
    }

    try {
        let url = `../controller/pagPhieuNhap.php?all=1`;
        if (startDate) url += `&start=${startDate}`;
        if (endDate) url += `&end=${endDate}`;
        if (keyword) url += `&search=${encodeURIComponent(keyword)}`;

        const response = await fetch(url);
        const res = await response.json();
        const list = res.data || [];

        if (!list.length) {
            alert('Không có phiếu nhập nào phù hợp để xuất Excel!');
            return;
        }

        const now = new Date();
        const exportDateStr = ('0' + now.getDate()).slice(-2) + '/' +
            ('0' + (now.getMonth() + 1)).slice(-2) + '/' +
            now.getFullYear() + ' ' +
            ('0' + now.getHours()).slice(-2) + ':' +
            ('0' + now.getMinutes()).slice(-2) + ':' +
            ('0' + now.getSeconds()).slice(-2);

        let filterText = 'Tất cả';
        if (startDate || endDate) {
            filterText = `Từ ngày ${formatDateVN(startDate) || '...'} đến ngày ${formatDateVN(endDate) || '...'}`;
        }
        if (keyword) {
            filterText += ` | Từ khóa tìm kiếm: "${keyword}"`;
        }

        const wsData = [
            ['BÁO CÁO DANH SÁCH PHIẾU NHẬP HÀNG'],
            [`Điều kiện lọc: ${filterText}`],
            [`Ngày xuất báo cáo: ${exportDateStr}`],
            [], // Hàng trống
            ['STT', 'Mã Phiếu Nhập', 'Nhà Cung Cấp', 'Nhân Viên Lập', 'Ngày Nhập', 'Trạng Thái', 'Tổng Tiền (VNĐ)']
        ];

        let totalAmount = 0;
        list.forEach((item, index) => {
            const ncc = item.tenncc ? `${item.tenncc} (ID: ${item.idnhacungcap})` : `NCC #${item.idnhacungcap}`;
            const nv = item.tennhanvien ? `${item.tennhanvien} (ID: ${item.idnhanvien})` : `NV #${item.idnhanvien}`;
            const statusStr = Number(item.trangthai) === 1 ? 'Hoạt động' : 'Đã hủy';
            const amount = Number(item.tongtien) || 0;
            totalAmount += amount;

            wsData.push([
                index + 1,
                'PN' + String(item.idphieunhap).padStart(4, '0'),
                ncc,
                nv,
                formatDateVN(item.ngaynhap),
                statusStr,
                amount
            ]);
        });

        const summaryRowIndex = wsData.length;
        wsData.push([
            'TỔNG CỘNG',
            '',
            '',
            '',
            '',
            `${list.length} phiếu`,
            totalAmount
        ]);

        const ws = XLSX.utils.aoa_to_sheet(wsData);

        ws['!cols'] = [
            { wch: 8 },  // STT
            { wch: 18 }, // Mã PN
            { wch: 30 }, // Nhà Cung Cấp
            { wch: 25 }, // Nhân Viên
            { wch: 15 }, // Ngày Nhập
            { wch: 15 }, // Trạng Thái
            { wch: 22 }  // Tổng Tiền
        ];

        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } },
            { s: { r: 1, c: 0 }, e: { r: 1, c: 6 } },
            { s: { r: 2, c: 0 }, e: { r: 2, c: 6 } },
            { s: { r: summaryRowIndex, c: 0 }, e: { r: summaryRowIndex, c: 4 } }
        ];

        // Format định dạng số tiền cho cột G (index 6)
        for (let r = 4; r <= summaryRowIndex; r++) {
            const revRef = XLSX.utils.encode_cell({ r: r, c: 6 });
            if (ws[revRef] && typeof ws[revRef].v === 'number') {
                ws[revRef].z = '#,##0';
            }
        }

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'DanhSachPhieuNhap');

        const cleanStart = startDate ? startDate.replace(/-/g, '') : 'all';
        const cleanEnd = endDate ? endDate.replace(/-/g, '') : 'all';
        const fileName = `Danh_Sach_Phieu_Nhap_${cleanStart}_den_${cleanEnd}.xlsx`;

        XLSX.writeFile(wb, fileName);
    } catch (err) {
        alert(err.message || 'Lỗi khi xuất dữ liệu ra Excel!');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }
}

// =================== XUẤT EXCEL CHI TIẾT 1 PHIẾU NHẬP ===================
async function exportSinglePhieuNhap(idphieunhap) {
    if (typeof XLSX === 'undefined') {
        alert('Thư viện xuất Excel chưa sẵn sàng. Vui lòng tải lại trang!');
        return;
    }

    try {
        const response = await fetch(`../controller/pagPhieuNhap.php?idphieunhap=${idphieunhap}`);
        const res = await response.json();
        if (res.status !== 'success' || !res.receipt) {
            alert('Không tìm thấy thông tin phiếu nhập #' + idphieunhap);
            return;
        }

        const receipt = res.receipt;
        const items = res.items || [];
        const nccName = receipt.tenncc ? `${receipt.tenncc} (Mã: ${receipt.idnhacungcap})` : `NCC #${receipt.idnhacungcap}`;
        const nvName = receipt.tennhanvien ? `${receipt.tennhanvien} (Mã: ${receipt.idnhanvien})` : `NV #${receipt.idnhanvien}`;
        const now = new Date();
        const exportDateStr = ('0' + now.getDate()).slice(-2) + '/' +
            ('0' + (now.getMonth() + 1)).slice(-2) + '/' +
            now.getFullYear() + ' ' +
            ('0' + now.getHours()).slice(-2) + ':' +
            ('0' + now.getMinutes()).slice(-2) + ':' +
            ('0' + now.getSeconds()).slice(-2);

        const wsData = [
            ['PHIẾU NHẬP HÀNG CHI TIẾT'],
            [`Mã Phiếu Nhập: PN${String(receipt.idphieunhap).padStart(4, '0')} | Ngày nhập: ${formatDateVN(receipt.ngaynhap)}`],
            [`Nhà cung cấp: ${nccName}`],
            [`Nhân viên lập phiếu: ${nvName}`],
            [`Trạng thái phiếu: ${Number(receipt.trangthai) === 1 ? 'Hoạt động' : 'Đã hủy'}`],
            [`Ngày xuất file: ${exportDateStr}`],
            [],
            ['STT', 'Mã Sách', 'Tên Sách', 'Số Lượng', 'Đơn Giá Nhập (VNĐ)', 'Lợi Nhuận (%)', 'Thành Tiền (VNĐ)']
        ];

        let totalQty = 0;
        let totalMoney = 0;

        items.forEach((item, index) => {
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
            { wch: 32 }, // Tên Sách
            { wch: 14 }, // Số Lượng
            { wch: 22 }, // Đơn Giá Nhập
            { wch: 16 }, // Lợi Nhuận
            { wch: 24 }  // Thành Tiền
        ];

        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } },
            { s: { r: 1, c: 0 }, e: { r: 1, c: 6 } },
            { s: { r: 2, c: 0 }, e: { r: 2, c: 6 } },
            { s: { r: 3, c: 0 }, e: { r: 3, c: 6 } },
            { s: { r: 4, c: 0 }, e: { r: 4, c: 6 } },
            { s: { r: 5, c: 0 }, e: { r: 5, c: 6 } },
            { s: { r: summaryRowIndex, c: 0 }, e: { r: summaryRowIndex, c: 2 } }
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
        XLSX.utils.book_append_sheet(wb, ws, `PhieuNhap_${receipt.idphieunhap}`);
        XLSX.writeFile(wb, `Phieu_Nhap_Hang_PN${String(receipt.idphieunhap).padStart(4, '0')}.xlsx`);
    } catch (err) {
        alert('Lỗi khi xuất phiếu nhập: ' + err.message);
    }
}

// Khởi chạy khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', function () {
    loadPage(1);

    const searchInput = document.getElementById('searchPN');
    if (searchInput) {
        let debounceTimer = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const keyword = this.value.trim();
            debounceTimer = setTimeout(() => {
                if (keyword !== '') {
                    searchPhieuNhap(keyword);
                } else {
                    loadPage(1);
                }
            }, 300);
        });
    }
});
