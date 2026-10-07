<?php if (empty($_SESSION['payment_csrf'])) $_SESSION['payment_csrf']=bin2hex(random_bytes(32)); ?>
<article class="container block-content block-content-news">
    <div class="flex-item">
        <aside class="block-main-content">
            <div class="box-block-main-content">
                <h1 class="text-title">Lịch sử mua hàng</h1>
                <h3 class="title-gio-hang">Những đơn hàng của bạn</h3>
                <!-- Bảng lịch sử mua hàng -->
                <div class="table-scroll" role="region" aria-label="Lịch sử mua hàng" tabindex="0">
                <table class="purchase-history">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody id="purchase-history-body">
                        <!-- Dữ liệu sẽ được thêm động bằng JavaScript -->
                    </tbody>
                </table>
                </div>
                <!-- Kết thúc bảng -->
            </div>
        </aside>
    </div>

    <!-- Modal Chi tiết hóa đơn -->
    <div class="block-modal block-modal-complete-cart block-modal-history" id="modal-dat-hang-thanh-cong" style="display:none;">
        <h1>Thông báo
            <span class="closepopup"></span>
        </h1>
        <div class="box-descript-modal">
            <h2>Cảm ơn bạn đã mua hàng tại Unibook!</h2>
            <h2>Chúng tôi đã tiếp nhận đơn hàng của bạn với:</h2>
            <ul>
                <li><b>Mã hóa đơn là: </b><span id="modal-idhoadon"></span></li>
                <li><b>Tên người nhận:</b> <span id="modal-tennguoinhan"></span></li>
                <li><b>SĐT nhận hàng:</b> <span id="modal-sdt"></span></li>
                <li><b>Địa chỉ nhận hàng:</b> <span id="modal-diachi"></span></li>
                <li><b>Hình thức thanh toán: </b><span id="modal-phuongthuctt"></span></li>
            </ul>
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá tiền</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>
                <tbody id="modal-order-details"></tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4">Tổng số tiền</td>
                        <td id="modal-total-price"></td>
                    </tr>
                </tfoot>
            </table>
            <h4><a href="../controller/index.php"><i class="icon-arrow-left"></i>Về trang chủ</a></h4>
        </div>
    </div>

    <?php include __DIR__.'/after-sales-dialogs.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../view/resources/js/order-sync.js?v=1" defer></script>
    <script>
    $(document).ready(function() {
        var idKhachHang = <?php echo isset($_SESSION['idkhachhang']) ? $_SESSION['idkhachhang'] : 'null'; ?>;

        if (idKhachHang === null) {
            alert('Vui lòng đăng nhập để xem lịch sử mua hàng.');
            window.location.href = '../controller/index.php?pg=dangnhap';
            return;
        }

        let historyLoading=false,historyQueued=false,historySnapshot=null,historyStopped=false;
        loadPurchaseHistory();
        window.addEventListener('unibook:orders-changed',loadPurchaseHistory);
        $(document).on('click', '.confirm-received', function() {
            if (!confirm('Bạn xác nhận đã nhận đủ sản phẩm của đơn hàng này?')) return;
            const button=$(this).prop('disabled',true);
            $.ajax({url:'../controller/confirm-received.php',method:'POST',dataType:'json',
                data:{idhoadon:button.data('idhoadon'),csrf_token:<?=json_encode($_SESSION['payment_csrf'])?>},
                success:function(result){if(result.status==='success'){loadPurchaseHistory();window.dispatchEvent(new CustomEvent('unibook:received',{detail:{orderId:Number(button.data('idhoadon'))}}));}else alert(result.message);},
                error:function(xhr){alert(xhr.responseJSON?.message || 'Không thể xác nhận nhận hàng.');},
                complete:function(){button.prop('disabled',false);}
            });
        });

        $('#modal-dat-hang-thanh-cong .closepopup').on('click', function() {
            $('#modal-dat-hang-thanh-cong').hide().removeClass('block-modal-active');
        });

        $(document).on('click', '.dataModal', function(e) {
            e.preventDefault();
            var idhoadon = $(this).data('idhoadon');
            fetchOrderDetails(idhoadon);
        });

        // Hàm tải danh sách hóa đơn
        function loadPurchaseHistory() {
            if(historyStopped)return;
            if(historyLoading){historyQueued=true;return;}
            historyLoading=true;
            $.ajax({
                url: '../controller/hoadon.php?action=getOrderByCustomer&idKhachHang=' + idKhachHang,
                method: 'GET',
                dataType: 'json',
                cache:false,
                timeout:15000,
                success: function(response) {
                    if(response.status!=='success' || !Array.isArray(response.data))return;
                    const snapshot=JSON.stringify(response.data);
                    if(snapshot===historySnapshot)return;
                    historySnapshot=snapshot;
                    var tbody = $('#purchase-history-body');
                    tbody.empty();

                    if (response.status === 'success' && response.data.length > 0) {
                        response.data.forEach(function(order) {
                            let statusText = '';
                            switch (parseInt(order.trangthai)) {
                                case 0:
                                    statusText = 'Chờ xác nhận';
                                    break;
                                case 1:
                                    statusText = 'Đang giao';
                                    break;
                                case 2:
                                    statusText = 'Đã nhận hàng';
                                    break;
                                case 3:
                                    statusText = 'Đã hủy';
                                    break;
                                default:
                                    statusText = 'Không xác định';
                            }

                            const rowHtml = `
                                <tr>
                                    <td>#${order.idhoadon}</td>
                                    <td>${formatDate(order.ngayxuat)}</td>
                                    <td>${parseFloat(order.tongtien).toLocaleString('vi-VN')} VND</td>
                                    <td>${statusText}${parseInt(order.trangthai)===1 && order.cancel_status!=='REQUESTED' ? '<br><button type="button" class="confirm-received" data-idhoadon="'+Number(order.idhoadon)+'">Đã nhận hàng</button>' : ''}</td>
                                    <td><a href="#" class="dataModal" data-idhoadon="${order.idhoadon}" data-id="modal-dat-hang-thanh-cong">Xem</a></td>
                                </tr>
                            `;
                            tbody.append(rowHtml);
                            const cell=tbody.find('tr:last td').eq(3);
                            const notes={REQUESTED:'Đang chờ duyệt hủy',REJECTED:'Yêu cầu hủy bị từ chối',APPROVED:'Đơn đã hủy'};
                            const refunds={QUEUED:'Chờ hoàn tiền',SENDING:'Đang gửi yêu cầu hoàn tiền',PROCESSING:'VNPAY đang xử lý hoàn tiền',SUCCEEDED:'Đã hoàn tiền',REJECTED:'Hoàn tiền bị từ chối — liên hệ cửa hàng',UNKNOWN:'Hoàn tiền đang được đối soát',REVIEW:'Hoàn tiền cần kiểm tra'};
                            if(Number(order.trangthai)!==2 && notes[order.cancel_status]) cell.append($('<span class="ub-order-note">').text(notes[order.cancel_status]));
                            if(Number(order.trangthai)!==2 && order.decision_note)cell.append($('<span class="ub-order-note">').text(order.decision_note));
                            if(refunds[order.refund_status])cell.append($('<span class="ub-order-note">').text(refunds[order.refund_status]));
                            const actions=$('<div class="ub-order-actions">');
                            if([0,1].includes(Number(order.trangthai)) && order.cancel_status==='NONE'){
                                actions.append($('<button type="button" data-after-sales="cancel">').attr('data-order-id',Number(order.idhoadon)).attr('data-shipping',Number(order.trangthai)===1?'1':'0').text(Number(order.trangthai)===1?'Yêu cầu hủy':'Hủy đơn'));
                            }
                            if(Number(order.trangthai)===2){
                                if(Number(order.can_review))actions.append($('<button type="button" data-after-sales="review">').attr('data-order-id',Number(order.idhoadon)).text('Đánh giá sản phẩm'));
                                else actions.append($('<span class="ub-order-note">').text(Number(order.reviews_complete)?'Đã đánh giá':order.received_at?'Đã hết hạn đánh giá':'Chưa có thời điểm nhận hàng để mở đánh giá'));
                            }
                            cell.append(actions);
                        });
                    } else {
                        tbody.append('<tr><td colspan="5">Bạn chưa có hóa đơn nào.</td></tr>');
                    }
                },
                error: function(xhr) {
                    if(xhr.status===401){historyStopped=true;window.dispatchEvent(new Event('unibook:order-sync-stop'));historySnapshot=null;$('#purchase-history-body').html('<tr><td colspan="5">Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.</td></tr>');return;}
                    if(historySnapshot===null)$('#purchase-history-body').html('<tr><td colspan="5">Chưa tải được dữ liệu. Đang thử kết nối lại…</td></tr>');
                },
                complete:function(){
                    historyLoading=false;
                    if(historyQueued){historyQueued=false;loadPurchaseHistory();}
                }
            });
        }

        function fetchOrderDetails(idhoadon) {
            $.ajax({
                url: '../controller/hoadon.php?action=getOrderInfo&id=' + idhoadon,
                method: 'GET',
                dataType: 'json',
                success: function(orderInfo) {
                    $.ajax({
                        url: '../controller/chitiethoadon.php?action=getOrderDetails&id=' + idhoadon,
                        method: 'GET',
                        dataType: 'json',
                        success: function(orderDetails) {
                            displayOrderModal(orderInfo, orderDetails);
                        },
                        error: function(xhr, status, error) {
                            alert('Lỗi khi lấy chi tiết hóa đơn: ' + error);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    alert('Lỗi khi lấy thông tin hóa đơn: ' + error);
                }
            });
        }

        function displayOrderModal(orderInfo, orderDetails) {
            if (!orderInfo || typeof orderInfo !== 'object') {
                alert('Lỗi: Không lấy được thông tin hóa đơn');
                return;
            }

            $('#modal-idhoadon').text(orderInfo.idhoadon || 'Không xác định');
            $('#modal-tennguoinhan').text(orderInfo.tennguoinhan || 'Không xác định');
            $('#modal-sdt').text(orderInfo.sdt || 'Không xác định');
            $('#modal-diachi').text(orderInfo.diachi || 'Không xác định');
            $('#modal-phuongthuctt').text(orderInfo.phuongthuctt || 'Không xác định');

            $('#modal-order-details').empty();

            let stt = 1;
            let tongtien = 0;
            if (orderDetails && Array.isArray(orderDetails)) {
                orderDetails.forEach(function(item) {
                    let thanhtien = item.soluong * item.gia;
                    $('#modal-order-details').append(`
                        <tr>
                            <td>${stt}</td>
                            <td>${item.tensach || 'Không xác định'}</td>
                            <td>${item.soluong || 0}</td>
                            <td>${(item.gia || 0).toLocaleString('vi-VN')} VND</td>
                            <td>${thanhtien.toLocaleString('vi-VN')} VND</td>
                        </tr>
                    `);
                    tongtien += thanhtien;
                    stt++;
                });
            } else {
                $('#modal-order-details').append('<tr><td colspan="5">Không có chi tiết hóa đơn</td></tr>');
            }

            $('#modal-total-price').text(tongtien.toLocaleString('vi-VN') + ' VND');

            $('#modal-dat-hang-thanh-cong').show().addClass('block-modal-active');
        }

        function formatDate(dateString) {
            var date = new Date(dateString);
            var day = date.getDate().toString().padStart(2, '0');
            var month = (date.getMonth() + 1).toString().padStart(2, '0');
            var year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
    });
    </script>
</article>
