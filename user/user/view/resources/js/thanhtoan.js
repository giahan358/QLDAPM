$(document).ready(function() {
    var idKhachHang = window.idKhachHang; // Lấy từ biến toàn cục được truyền từ PHP
    
    // Tải địa chỉ mặc định khi trang được tải
    loadDefaultAddress();

    // Tải danh sách địa chỉ khi nhấn "Chọn địa chỉ khác"
    $('#diff-addr').click(function() {
        $('#container-address').show();
        loadAddressList();
    });

    // Đóng danh sách địa chỉ
    $('#container-address .close').click(function() {
        $('#container-address').hide();
    });

    // Khi chọn địa chỉ
    $(document).on('change', 'input[name="shipping_address"]', function() {
        let idDiaChi = $(this).val();
        updateSelectedAddress(idDiaChi);
    });

    // Khi nhấn "Đặt làm mặc định"
    $('#set-default').change(function() {
        if ($(this).is(':checked')) {
            let idDiaChi = $('input[name="shipping_address"]:checked').val();
            if (idDiaChi) {
                setDefaultAddress(idDiaChi);
            }
        }
    });

    // Khi nhấn "Sửa" địa chỉ
    $(document).on('click', '.sua:not(.delete-address)', function() {
        let idDiaChi = $(this).closest('.item-diachi').find('input[name="shipping_address"]').val();
        loadEditAddressForm(idDiaChi);
        $('#edit-address').show();
    });

    // Khi nhấn "Xóa" địa chỉ
    $(document).on('click', '.delete-address', function() {
        let idDiaChi = $(this).data('id');
        if (confirm('Bạn có chắc muốn xóa địa chỉ này?')) {
            deleteAddress(idDiaChi);
        }
    });

    // Hủy sửa địa chỉ
    $('#btn-huy1').click(function() {
        $('#edit-address').hide();
    });

    // Lưu sửa địa chỉ
    $('#edit-address-form').submit(function(e) {
        e.preventDefault();
        let idDiaChi = $('#edit-iddiachi').val();
        let data = {
            iddiachi: idDiaChi,
            hotenNgNhan: $('#full-name1').val(),
            sdtNgNhan: $('#phone1').val(),
            thanhpho: $('#city1').val(),
            huyen: $('#district1').val(),
            xa: $('#ward1').val(),
            diachi_chitiet: $('#text1').val(),
            emailNgNhan: ''
        };
        updateAddress(data);
    });

    // Khi nhấn "Thêm địa chỉ mới"
    $('#address-new').click(function() {
        $('#add-address').show();
        loadCities('', '', ''); // Tải dropdown trống cho form thêm mới
        $('#full-name').val('');
        $('#phone').val('');
        $('#text').val('');
    });

    // Hủy thêm địa chỉ
    $('#btn-huy').click(function() {
        $('#add-address').hide();
    });

    // Lưu thêm địa chỉ
    $('#add-address-form').submit(function(e) {
        e.preventDefault();
        let data = {
            idkhachhang: idKhachHang,
            hotenNgNhan: $('#full-name').val(),
            sdtNgNhan: $('#phone').val(),
            thanhpho: $('#city').val(),
            huyen: $('#district').val(),
            xa: $('#ward').val(),
            diachi_chitiet: $('#text').val(),
            emailNgNhan: ''
        };
        addAddress(data);
    });

    // Hàm tải địa chỉ mặc định
    function loadDefaultAddress() {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=hienThi&idKhachHang=' + idKhachHang,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let defaultAddress = response.data.find(item => item.trangthai == 1);
                    if (defaultAddress) {
                        updateAddressInfo(defaultAddress);
                    } else if (response.data.length > 0) {
                        updateAddressInfo(response.data[0]);
                    } else {
                        $('#receiver-name').text('Chưa có địa chỉ');
                        $('#receiver-phone').text('');
                        $('#receiver-address').text('');
                        $('#default3').hide();
                    }
                } else {
                    $('#receiver-name').text('Chưa có địa chỉ');
                    $('#receiver-phone').text('');
                    $('#receiver-address').text('');
                    $('#default3').hide();
                }
            },
            error: function() {
                $('#receiver-name').text('Lỗi tải dữ liệu');
                $('#receiver-phone').text('');
                $('#receiver-address').text('');
                $('#default3').hide();
            }
        });
    }

    // Hàm tải danh sách địa chỉ
    function loadAddressList() {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=hienThi&idKhachHang=' + idKhachHang,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                let addressList = $('#address-list');
                addressList.empty();
                
                if (response.status === 'success' && response.data.length > 0) {
                    response.data.forEach(function(item) {
                        let isChecked = item.trangthai == 1 ? 'checked' : '';
                        let defaultLabel = item.trangthai == 1 ? '<div class="macdinh">Mặc định</div>' : '';
                        let addressHtml = `
                            <div class="item-diachi">
                                <input type="radio" id="address_${item.iddiachi}" name="shipping_address" value="${item.iddiachi}" ${isChecked}>
                                <p>${item.hotenngnhan}</p>
                                <span>| ${item.sdtngnhan}</span>
                                <div class="sua">Sửa</div>
                                <div class="sua delete-address" data-id="${item.iddiachi}">Xóa</div>
                                <label for="address_${item.iddiachi}">${item.diachi_chitiet}, ${item.xa}, ${item.huyen}, ${item.thanhpho}</label>
                                ${defaultLabel}
                            </div>
                        `;
                        addressList.append(addressHtml);
                    });
                } else {
                    addressList.append('<p>Chưa có địa chỉ nào.</p>');
                }
            },
            error: function() {
                $('#address-list').html('<p>Lỗi tải dữ liệu.</p>');
            }
        });
    }

    // Hàm cập nhật thông tin địa chỉ được chọn
    function updateSelectedAddress(idDiaChi) {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=hienThi&idKhachHang=' + idKhachHang,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let selectedAddress = response.data.find(item => item.iddiachi == idDiaChi);
                    if (selectedAddress) {
                        updateAddressInfo(selectedAddress);
                    }
                }
            },
            error: function() {
                alert('Lỗi khi cập nhật thông tin địa chỉ.');
            }
        });
    }

    // Hàm cập nhật thông tin vào khối container-info-nhanhang
    function updateAddressInfo(address) {
        $('#receiver-name').text(address.hotenngnhan);
        $('#receiver-phone').text(address.sdtngnhan);
        $('#receiver-address').text(`${address.diachi_chitiet}, ${address.xa}, ${address.huyen}, ${address.thanhpho}`);
        $('#default3').css('display', address.trangthai == 1 ? 'block' : 'none');
    }

    // Hàm đặt địa chỉ mặc định
    function setDefaultAddress(idDiaChi) {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=setDefault&idDiaChi=' + idDiaChi + '&idKhachHang=' + idKhachHang,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    loadDefaultAddress();
                    loadAddressList();
                    $('#set-default').prop('checked', false);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Lỗi khi đặt địa chỉ mặc định.');
            }
        });
    }

    // Hàm tải form sửa địa chỉ
    function loadEditAddressForm(idDiaChi) {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=hienThi&idKhachHang=' + idKhachHang,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let address = response.data.find(item => item.iddiachi == idDiaChi);
                    if (address) {
                        $('#edit-iddiachi').val(address.iddiachi);
                        $('#full-name1').val(address.hotenngnhan);
                        $('#phone1').val(address.sdtngnhan);
                        $('#city1').val(address.thanhpho);
                        $('#district1').val(address.huyen);
                        $('#ward1').val(address.xa);
                        $('#text1').val(address.diachi_chitiet);
                        loadCities(address.thanhpho, address.huyen, address.xa, '1');
                    }
                }
            },
            error: function() {
                alert('Lỗi khi tải thông tin địa chỉ.');
            }
        });
    }

    // Hàm cập nhật địa chỉ
    function updateAddress(data) {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=update',
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#edit-address').hide();
                    loadAddressList();
                    loadDefaultAddress();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Lỗi khi cập nhật địa chỉ.');
            }
        });
    }

    // Hàm xóa địa chỉ
    function deleteAddress(idDiaChi) {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=delete&idDiaChi=' + idDiaChi,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    loadAddressList();
                    loadDefaultAddress();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Lỗi khi xóa địa chỉ.');
            }
        });
    }

    // Hàm thêm địa chỉ mới
    function addAddress(data) {
        $.ajax({
            url: '../controller/thongtinnhanhang.php?action=add',
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#add-address').hide();
                    loadAddressList();
                    loadDefaultAddress();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Lỗi khi thêm địa chỉ.');
            }
        });
    }

    // Hàm tải danh sách thành phố, quận/huyện, phường/xã
    function loadCities(selectedCity, selectedDistrict, selectedWard, suffix = '') {
        return window.UniBookAddress.load(suffix, selectedCity, selectedDistrict, selectedWard);
    }

    // Hàm tải danh sách sản phẩm
    function loadOrderItems() {
        $.ajax({
            url: '../controller/giohang.php?action=hienThi&idKhachHang=' + idKhachHang,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let orderItemsContainer = $('#order-items-container');
                    let totalPrice = 0;
                    let totalProducts = response.data.length;

                    orderItemsContainer.empty();

                    response.data.forEach(function(item) {
                        let thanhTien = parseFloat(item.gia) * parseInt(item.soluong);
                        totalPrice += thanhTien;

                        let orderItemHtml = `
                            <div class="box-item-buy">
                                <div class="box-img-buy">
                                    <img src="../../${item.anhbia}" alt="${item.tensach}">
                                    <span class="total-item">${item.soluong}</span>
                                </div>
                                <div class="box-description-buy">
                                    <h1>${item.tensach}</h1>
                                    <ul class="sub-cate-buy-cart">
                                        <li><a href="#">${item.loai || 'Không xác định'}</a></li>
                                    </ul>
                                    <p class="price-cart">${item.gia}</p>
                                </div>
                            </div>
                        `;

                        orderItemsContainer.append(orderItemHtml);
                    });

                    $('#total-products').text('(' + totalProducts + ' sản phẩm)');
                    $('#total-order-price').text(totalPrice + ' VND');
                } else {
                    $('#order-items-container').html('<p>Không có sản phẩm trong đơn hàng.</p>');
                    $('#total-products').text('(0 sản phẩm)');
                    $('#total-order-price').text('0 VND');
                }
            },
            error: function() {
                $('#order-items-container').html('<p>Có lỗi khi tải dữ liệu.</p>');
                $('#total-products').text('(0 sản phẩm)');
                $('#total-order-price').text('0 VND');
            }
        });
    }

    // Gọi hàm loadOrderItems khi trang tải
    loadOrderItems();
});
