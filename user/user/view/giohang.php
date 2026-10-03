<article class="container block-content block-content-news">
    <div class="flex-item">
        <aside class="block-main-content">
            <div class="box-block-main-content">
                <h1 class="text-title">Giỏ hàng</h1>
                <h3 class="title-gio-hang">Giỏ hàng của bạn</h3>
                <div class="cart-detail__content">
                    <div class="title-item-cart-detail item-cart-detail">
                        <div class="box-img-cart-detail">
                            <b id="soSanPham">0 Sản phẩm</b>
                        </div>
                        <div class="box-name-cart-detail"></div>
                        <div class="box-number-cart-detail">
                            <b>Số lượng</b>
                        </div>
                        <div class="box-price-cart-detail">
                            <b>Đơn giá</b>
                        </div>
                        <div class="box-total-price-cart-detail">
                            <b>Thành tiền</b>
                        </div>
                    </div>
                    <div id="cart-items-container">
                        <!-- Dữ liệu sẽ được thêm động bằng JavaScript -->
                    </div>

                    <div class="block-total-price">
                        <p><b>Tổng tiền:</b> <span id="total-price">0</span></p>
                    </div>
                    <div class="block-action-cart-detail">
                        <span class="continue-buy-cart"><a href="../controller/index.php"><i class="icon-arrow-left"></i> Tiếp tục mua hàng</a></span>
                        <div class="continue-order-page">
                            <button><a href="../controller/index.php?pg=thanhtoan">Đặt hàng <i class="icon-arrow-right"></i></a></button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</article>

<!-- Thêm script để tải và hiển thị dữ liệu -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var idKhachHang = <?php echo isset($_SESSION['idkhachhang']) ? $_SESSION['idkhachhang'] : 'null'; ?>;
    
    // Hàm tải số lượng sản phẩm và giỏ hàng
    function loadGioHangAndCount() {
        if (idKhachHang === null) {
            $('#soSanPham').text('0 Sản phẩm');
            $('#cart-items-container').html('<p>Vui lòng đăng nhập để xem giỏ hàng.</p>');
            return;
        }

        // Gọi AJAX để lấy số lượng sản phẩm
        $.ajax({
            url: '../controller/giohang.php?action=hienThi&idKhachHang=' + idKhachHang,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Cập nhật số lượng sản phẩm khác nhau
                    let soSanPham = response.data.length; // Số lượng bản ghi là số sản phẩm khác nhau
                    $('#soSanPham').text(soSanPham + ' Sản phẩm');

                    // Hiển thị các mục trong giỏ hàng
                    let cartItemsContainer = $('#cart-items-container');
                    let totalPrice = 0;

                    cartItemsContainer.empty();
                    response.data.forEach(function(item) {
                        let thanhTien = parseFloat(item.gia) * parseInt(item.soluong);
                        totalPrice += thanhTien;

                        let cartItemHtml = `
                            <div class="item-cart-detail" data-idgiohang="${item.idgiohang}" data-idsach="${item.idsach}" data-price="${item.gia}" data-sltonkho="${item.sltonkho}">
                                <div class="box-img-cart-detail">
                                    <a href="../controller/index.php?pg=chitietsp&id=${item.idsach}">
                                        <img src="../../${item.anhbia}" alt="${item.tensach}">
                                    </a>
                                </div>
                                <div class="box-name-cart-detail">
                                    <h1><a href="../controller/index.php?pg=chitietsp&id=${item.idsach}">${item.tensach}</a></h1>
                                    <button class="delete-cart-item">Xoá</button>
                                </div>
                                <div class="box-number-cart-detail cart-detail__quanity-col" data-cur-quantity="${item.soluong}">
                                    <div class="cart-detail__quantity-input-controls">
                                        <button class="decrease-btn icon-tru">-</button>
                                        <input class="-quantity-input" type="text" inputmode="numeric" value="${item.soluong}" data-limit-quantity="${item.sltonkho}" aria-label="Số lượng sản phẩm">
                                        <button class="increase-btn icon-plus"></button>
                                    </div>
                                    <div class="cart-detail__quantity-select-controls">
                                        <select class="-quantity-select" name="">
                                            <!-- Các option như trước -->
                                        </select>
                                    </div>
                                </div>
                                <div class="box-price-cart-detail">
                                    <p>${item.gia}</p>
                                </div>
                                <div class="box-total-price-cart-detail">
                                    <p>${thanhTien}</p>
                                </div>
                            </div>
                        `;

                        cartItemsContainer.append(cartItemHtml);
                    });

                    $('#total-price').text(totalPrice);
                } else {
                    $('#soSanPham').text('0 Sản phẩm');
                    $('#cart-items-container').html('<p>Không có sản phẩm trong giỏ hàng.</p>');
                }
            },
            error: function() {
                $('#soSanPham').text('0 Sản phẩm');
                $('#cart-items-container').html('<p>Có lỗi khi tải dữ liệu.</p>');
            }
        });
    }

    // Gọi hàm khi trang được tải
    loadGioHangAndCount();

    // Xử lý sự kiện xóa
    $(document).on('click', '.delete-cart-item', function() {
        let idGioHang = $(this).closest('.item-cart-detail').data('idgiohang');
        if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
            $.ajax({
                url: '../controller/giohang.php?action=xoa&idGioHang=' + idGioHang,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        loadCartItems();
                        loadGioHangAndCount(); // Tải lại cả số lượng và giỏ hàng
                    } else {
                        alert(response.message);
                    }
                }
            });
        }
    });

    // Xử lý tăng/giảm số lượng
    $(document).on('click', '.increase-btn, .decrease-btn', function() {
        let $item = $(this).closest('.item-cart-detail');
        let idGioHang = $item.data('idgiohang');
        let idSach = $item.data('idsach'); 
        let maxQuantity = parseInt($item.data('sltonkho')); // lấy số lượng tồn kho
        let currentQuantity = parseInt($item.find('.cart-detail__quanity-col').data('cur-quantity'));
        let newQuantity = $(this).hasClass('increase-btn') ? currentQuantity + 1 : currentQuantity - 1;

        if (newQuantity < 1) newQuantity = 1;
        if (newQuantity > maxQuantity) newQuantity = maxQuantity;

        $.ajax({
            url: '../controller/giohang.php',
            method: 'GET',
            data: {
                action: 'capNhat',
                idGioHang: idGioHang,
                soLuong: newQuantity,
                idSach: idSach
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $item.find('.cart-detail__quanity-col').data('cur-quantity', newQuantity);
                    $item.find('.cart-detail__quanity-col input').val(newQuantity);
                    loadGioHangAndCount();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi khi cập nhật số lượng.');
            }
        });
    });
});
</script>
<script src="../view/resources/js/cart-quantity.js?v=20260916"></script>
