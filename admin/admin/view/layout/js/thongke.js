document.addEventListener('DOMContentLoaded', function () {
    let chart = null;
    let currentData = null;

    function formatDateVN(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
    }

    function render(data) {
        if (!Array.isArray(data)) return;
        const top = Number(document.getElementById('top-kh').value) || 5;
        const direction = document.getElementById('sort-order').value === 'ASC' ? 1 : -1;
        const rows = [...data].sort((a, b) => direction * (Number(a.tong_mua) - Number(b.tong_mua))).slice(0, top);

        const tbody = document.getElementById('result-table');
        tbody.replaceChildren();

        for (const item of rows) {
            const row = document.createElement('tr');
            const name = document.createElement('td');
            const orders = document.createElement('td');
            const count = document.createElement('td');
            const amount = document.createElement('td');

            name.textContent = item.ten || 'Chưa cập nhật';

            const dsDonHang = Array.isArray(item.ds_don_hang) ? item.ds_don_hang : [];
            dsDonHang.forEach((id, index) => {
                if (index) orders.append(document.createTextNode(', '));
                const link = document.createElement('a');
                link.className = 'link-donhang';
                link.href = '../controller/index.php?pg=xetduyetHD&idhoadon=' + Number(id) + '&from=thongke';
                link.textContent = (index + 1) + ' - ID: ' + id;
                orders.append(link);
            });

            count.textContent = dsDonHang.length;
            amount.textContent = Number(item.tong_mua || 0).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });

            row.append(name, orders, count, amount);
            tbody.append(row);
        }

        if (!rows.length) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 4;
            cell.className = 'text-center text-muted py-3';
            cell.textContent = 'Không có dữ liệu trong khoảng thời gian đã chọn';
            row.append(cell);
            tbody.append(row);
        }

        if (chart) chart.destroy();
        if (typeof Chart === 'undefined') return;

        const ctx = document.getElementById('myChart');
        if (!ctx) return;

        const colors = [
            'rgba(54, 162, 235, 0.65)',
            'rgba(255, 99, 132, 0.65)',
            'rgba(255, 206, 86, 0.65)',
            'rgba(75, 192, 192, 0.65)',
            'rgba(153, 102, 255, 0.65)',
            'rgba(255, 159, 64, 0.65)'
        ];

        chart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: rows.map(r => r.ten || 'N/A'),
                datasets: [{
                    label: 'Tổng Mua (VNĐ)',
                    data: rows.map(r => Number(r.tong_mua || 0)),
                    backgroundColor: rows.map((_, i) => colors[i % colors.length]),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (val) {
                                return Number(val).toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                }
            }
        });
    }

    async function loadStatistics(start, end) {
        const top = document.getElementById('top-kh').value || '';
        const sortOrder = document.getElementById('sort-order').value || 'DESC';
        const params = new URLSearchParams({
            'start-date': start,
            'end-date': end,
            'top-kh': top,
            'sort-order': sortOrder
        });

        const response = await fetch('../controller/pagThongKe.php', {
            method: 'POST',
            body: params
        });

        const result = await response.json();
        if (result.status !== 'success') {
            throw new Error(result.message || 'Lỗi khi tải dữ liệu thống kê');
        }

        currentData = result.data || [];
        render(currentData);

        try {
            sessionStorage.setItem('dulieuThongKe', JSON.stringify(currentData));
            sessionStorage.setItem('startDate', start);
            sessionStorage.setItem('endDate', end);
        } catch (_) {}

        return currentData;
    }

    // Submit form thống kê
    const thongKeForm = document.getElementById('thongke-form');
    if (thongKeForm) {
        thongKeForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const start = document.getElementById('start-date').value;
            const end = document.getElementById('end-date').value;

            if (!start || !end) {
                alert('Vui lòng chọn khoảng thời gian!');
                return;
            }
            if (start > end) {
                alert('Ngày bắt đầu không được lớn hơn ngày kết thúc!');
                return;
            }

            const btnSubmit = document.getElementById('btn-thongke');
            const originalContent = btnSubmit ? btnSubmit.innerHTML : 'Thống Kê';
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang tải...';
            }

            try {
                await loadStatistics(start, end);
            } catch (error) {
                alert(error.message || 'Lỗi khi gửi yêu cầu!');
            } finally {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalContent;
                }
            }
        });
    }

    // Tự động re-render khi thay đổi top-kh hoặc sort-order nếu đã có dữ liệu
    const topKhInput = document.getElementById('top-kh');
    if (topKhInput) {
        topKhInput.addEventListener('change', function () {
            if (currentData) render(currentData);
        });
    }

    const sortOrderSelect = document.getElementById('sort-order');
    if (sortOrderSelect) {
        sortOrderSelect.addEventListener('change', function () {
            if (currentData) render(currentData);
        });
    }

    // Chức năng Xuất Excel
    function exportToExcel(data) {
        if (typeof XLSX === 'undefined') {
            alert('Thư viện xuất Excel chưa được nạp. Vui lòng tải lại trang!');
            return;
        }

        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;
        const top = Number(document.getElementById('top-kh').value) || data.length;
        const sortOrder = document.getElementById('sort-order').value;
        const direction = sortOrder === 'ASC' ? 1 : -1;
        const rows = [...data].sort((a, b) => direction * (Number(a.tong_mua) - Number(b.tong_mua))).slice(0, top);

        if (!rows.length) {
            alert('Không có dữ liệu trong khoảng thời gian này để xuất Excel!');
            return;
        }

        const sortText = sortOrder === 'ASC' ? 'Tăng dần theo tổng mua' : 'Giảm dần theo tổng mua';
        const now = new Date();
        const exportDateStr = ('0' + now.getDate()).slice(-2) + '/' +
            ('0' + (now.getMonth() + 1)).slice(-2) + '/' +
            now.getFullYear() + ' ' +
            ('0' + now.getHours()).slice(-2) + ':' +
            ('0' + now.getMinutes()).slice(-2) + ':' +
            ('0' + now.getSeconds()).slice(-2);

        const wsData = [
            ['BÁO CÁO THỐNG KÊ DOANH THU THEO KHÁCH HÀNG'],
            [`Khoảng thời gian: Từ ngày ${formatDateVN(start)} đến ngày ${formatDateVN(end)}`],
            [`Ngày xuất báo cáo: ${exportDateStr}`],
            [`Bộ lọc: Top ${top} khách hàng | Sắp xếp: ${sortText}`],
            [], // Hàng trống
            ['STT', 'Mã Khách Hàng', 'Tên Khách Hàng', 'Danh Sách Mã Đơn Hàng', 'Tổng Số Đơn Hàng', 'Tổng Tiền Mua (VNĐ)']
        ];

        let totalOrders = 0;
        let totalRevenue = 0;

        rows.forEach((item, index) => {
            const orderList = Array.isArray(item.ds_don_hang) ? item.ds_don_hang : [];
            const orderCount = orderList.length;
            const orderStr = orderList.map(id => '#' + id).join(', ');
            const revenue = Number(item.tong_mua) || 0;

            totalOrders += orderCount;
            totalRevenue += revenue;

            wsData.push([
                index + 1,
                item.idkhachhang ? 'KH' + String(item.idkhachhang).padStart(3, '0') : '',
                item.ten || '',
                orderStr,
                orderCount,
                revenue
            ]);
        });

        // Dòng tổng cộng
        const summaryRowIndex = wsData.length;
        wsData.push([
            'TỔNG CỘNG',
            '',
            '',
            '',
            totalOrders,
            totalRevenue
        ]);

        const ws = XLSX.utils.aoa_to_sheet(wsData);

        // Thiết lập độ rộng cột (Column Widths)
        ws['!cols'] = [
            { wch: 8 },  // STT
            { wch: 16 }, // Mã KH
            { wch: 28 }, // Tên Khách Hàng
            { wch: 35 }, // Danh Sách Đơn Hàng
            { wch: 20 }, // Tổng Số Đơn Hàng
            { wch: 25 }  // Tổng Tiền Mua
        ];

        // Thiết lập merge cells cho tiêu đề và dòng tổng cộng
        ws['!merges'] = [
            { s: { r: 0, c: 0 }, e: { r: 0, c: 5 } }, // Tiêu đề báo cáo
            { s: { r: 1, c: 0 }, e: { r: 1, c: 5 } }, // Khoảng thời gian
            { s: { r: 2, c: 0 }, e: { r: 2, c: 5 } }, // Ngày xuất
            { s: { r: 3, c: 0 }, e: { r: 3, c: 5 } }, // Bộ lọc
            { s: { r: summaryRowIndex, c: 0 }, e: { r: summaryRowIndex, c: 3 } } // Merge TỔNG CỘNG ô A -> D
        ];

        // Format định dạng số cho cột Tổng Số Đơn Hàng và Tổng Tiền Mua
        for (let r = 5; r <= summaryRowIndex; r++) {
            const countCellRef = XLSX.utils.encode_cell({ r: r, c: 4 });
            if (ws[countCellRef] && typeof ws[countCellRef].v === 'number') {
                ws[countCellRef].z = '#,##0';
            }

            const revCellRef = XLSX.utils.encode_cell({ r: r, c: 5 });
            if (ws[revCellRef] && typeof ws[revCellRef].v === 'number') {
                ws[revCellRef].z = '#,##0';
            }
        }

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'ThongKeDoanhThu');

        const cleanStart = start ? start.replace(/-/g, '') : 'batdau';
        const cleanEnd = end ? end.replace(/-/g, '') : 'ketthuc';
        const fileName = `Thong_Ke_Doanh_Thu_${cleanStart}_den_${cleanEnd}.xlsx`;

        XLSX.writeFile(wb, fileName);
    }

    const btnExport = document.getElementById('btn-export-excel');
    if (btnExport) {
        btnExport.addEventListener('click', async function () {
            const start = document.getElementById('start-date').value;
            const end = document.getElementById('end-date').value;

            if (!start || !end) {
                alert('Vui lòng chọn khoảng thời gian thống kê trước khi xuất Excel!');
                document.getElementById('start-date').focus();
                return;
            }
            if (start > end) {
                alert('Ngày bắt đầu không được lớn hơn ngày kết thúc!');
                return;
            }

            // Nếu đã có dữ liệu trong biến currentData thì xuất luôn
            if (currentData && currentData.length > 0) {
                exportToExcel(currentData);
                return;
            }

            // Nếu chưa có dữ liệu, thực hiện tải dữ liệu trước rồi xuất
            const originalContent = btnExport.innerHTML;
            btnExport.disabled = true;
            btnExport.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang chuẩn bị file...';

            try {
                const data = await loadStatistics(start, end);
                if (data && data.length > 0) {
                    exportToExcel(data);
                } else {
                    alert('Không có dữ liệu trong khoảng thời gian này để xuất Excel!');
                }
            } catch (error) {
                alert(error.message || 'Lỗi khi tải dữ liệu để xuất Excel!');
            } finally {
                btnExport.disabled = false;
                btnExport.innerHTML = originalContent;
            }
        });
    }

    // Khôi phục dữ liệu từ sessionStorage nếu có
    try {
        const start = sessionStorage.getItem('startDate');
        const end = sessionStorage.getItem('endDate');
        const data = sessionStorage.getItem('dulieuThongKe');
        if (start) document.getElementById('start-date').value = start;
        if (end) document.getElementById('end-date').value = end;
        if (data) {
            currentData = JSON.parse(data);
            render(currentData);
        }
    } catch (_) {}
});
