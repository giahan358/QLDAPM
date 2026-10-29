function loadCustomer(page){
    fetch(`../controller/pagKhachHang.php?page=${page}`)
    .then(response=>{
        if(!response.ok){
            throw new Error('Network response was not ok ');
        }
        return response.json();
    })
    .then(data =>{
        const tbody = document.getElementById('customer-table-body');
        tbody.replaceChildren();

        //thêm dữ liệu mới vào bảng
        data.data.forEach(customer =>{
            const row = `
                <tr>
                    <td>${customer.idkhachhang}</td>
                    <td>${customer.ten}</td>
                    <td>${customer.email}</td>
                    <td>${customer.sodienthoai}</td>
                    <td>${customer.trangthai == 1 ? "Đang hoạt động " : "Tạm ngưng"}</td>
                    <td>
                        <a href="../controller/index.php?pg=suakhachhang&idkhachhang=${customer.idkhachhang}">
                            <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                        </a>
                    </td>
                    <td>
                        <a href="../controller/index.php?pg=xoakhachhang&idkhachhang=${customer.idkhachhang}" onclick="return confirm('Bạn có chắc muốn khách hàng ${customer.ten} không?')">
                            <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                        </a>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
        // Cập nhật phân trang 
        const pagination = document.getElementById('pagination');
        pagination.replaceChildren();
        for(let i = 1; i <= data.totalPages; i++){
            pagination.innerHTML += `
                <button data-page= "${i}" ${i == data.currentPage ? 'class = "active"' : '' }>${i}</button>
            `
        }

        //gắn sự kiện cho các nút phân trang
        document.querySelectorAll("#pagination button").forEach(button=>{
            button.addEventListener("click", function(){
                const page = parseInt(this.getAttribute("data-page"));
                loadCustomer(page);
            });
        });
    })
    .catch(error => console.error("Error fetching data ", error));

}

function checkFormKH() {
    let emailKH = document.querySelector('input[name="emailKH"]').value.trim();
    let phoneKH = document.querySelector('input[name="phoneKH"]').value.trim();

    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if(!emailPattern.test(emailKH)){
        alert("Email không hợp lệ! Vui lòng nhập theo cú pháp 'abc@gmail.com'.");
        return false;
    }

    let phonePattern = /^[0-9]{10}$/;
    if(!phonePattern.test(phoneKH)){
        alert("Số điện thoại không hợp lệ! Vui lòng nhập 10 số.");
        return false;
    }

    return true;
}

function searchKhachHang(keyword, page = 1) {
    fetch(`../controller/pagKhachHang.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('customer-table-body');
            tbody.replaceChildren();

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align:center;">Không tìm thấy khách hàng phù hợp!</td>
                    </tr>
                `;
                document.getElementById("pagination").innerHTML = '';
                return;
            }

            data.data.forEach(customer => {
                const row = `
                    <tr>
                        <td>${customer.idkhachhang}</td>
                        <td>${customer.ten}</td>
                        <td>${customer.email}</td>
                        <td>${customer.sodienthoai}</td>
                        <td>${customer.trangthai == 1 ? "Đang hoạt động" : "Tạm ngưng"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suakhachhang&idkhachhang=${customer.idkhachhang}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoakhachhang&idkhachhang=${customer.idkhachhang}" onclick="return confirm('Bạn có chắc muốn xóa khách hàng ${customer.ten} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Phân trang
            const pagination = document.getElementById('pagination');
            pagination.replaceChildren();

            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            // Gắn lại sự kiện click cho nút phân trang sau khi render
            document.querySelectorAll("#pagination button").forEach(button => {
                button.addEventListener("click", function () {
                    const newPage = parseInt(this.getAttribute("data-page"));
                    searchKhachHang(keyword, newPage);
                });
            });
        })
        .catch(error => {
            console.error("Lỗi khi tìm kiếm khách hàng:", error);
        });
}

//tải lại trang đầu tiên khi trang được tải
document.addEventListener("DOMContentLoaded", function(){
    loadCustomer(1);
    // searchKhachHang();
    const form = document.querySelector("form");
    form.addEventListener("submit", function(event){
        if(!checkFormKH()){
            event.preventDefault();
        }
    });
});

 // Tìm kiếm khách hàng
 document.getElementById("searchKH").addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword !== "") {
        searchKhachHang(keyword);
    } else {
        loadCustomer(1);
    }
});
