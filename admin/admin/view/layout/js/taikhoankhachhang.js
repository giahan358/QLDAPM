function loadTaiKhoanKhachHang(page){
    fetch(`../controller/pagTaiKhoanKhachHang.php?page=${page}`)
    .then(response=>{
        if(!response.ok){
            throw new Error('Network response was not ok ');
        }
        return response.json();
    })
    .then(data =>{
        const tbody = document.getElementById('accountCustomer-table-body');
        tbody.replaceChildren();

        //thêm dữ liệu mới vào bảng
        data.data.forEach(accountCustomer =>{
            const row = `
                <tr>
                    <td>${accountCustomer.idkhachhang}</td>
                    <td>${accountCustomer.tendangnhap}</td>
                    <td>${accountCustomer.trangthai == 1 ? "Đang hoạt động " : "Khóa"}</td>
                    <td>
                        <a href="../controller/index.php?pg=khoakhachhang&idkhachhang=${accountCustomer.idkhachhang}" onclick="return confirm('Bạn có chắc muốn khóa khách hàng ${accountCustomer.idkhachhang} không?')">
                            <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                        </a>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
        // Cập nhật phân trang 
        const pagination = document.getElementById('pagination_accountCustomer');
        pagination.replaceChildren();
        for(let i = 1; i <= data.totalPages; i++){
            pagination.innerHTML += `
                <button data-page= "${i}" ${i == data.currentPage ? 'class = "active"' : '' }>${i}</button>
            `
        }

        //gắn sự kiện cho các nút phân trang
        document.querySelectorAll("#pagination_accountCustomer button").forEach(button=>{
            button.addEventListener("click", function(){
                const page = parseInt(this.getAttribute("data-page"));
                loadTaiKhoanKhachHang(page);
            });
        });
    })
    .catch(error => console.error("Error fetching data ", error));

}

function searchTaiKhoanKhachHang(keyword, page=1){
    fetch(`../controller/pagTaiKhoanKhachHang.php?search=${encodeURIComponent(keyword)}&page=${page}`)
    .then(response=>{
        if(!response.ok){
            throw new Error('Network response was not ok ');
        }
        return response.json();
    })
    .then(data =>{
        const tbody = document.getElementById('accountCustomer-table-body');
        tbody.replaceChildren();

        if(data.data.length === 0){
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align:center;">Không tìm thấy tài khoản khách hàng phù hợp!</td>
                </tr>
            `;

            // Xóa phân trang nếu không có kết quả
            document.getElementById("pagination_accountCustomer").innerHTML = "";
            return;
        }

        //thêm dữ liệu mới vào bảng
        data.data.forEach(accountCustomer =>{
            const row = `
                <tr>
                    <td>${accountCustomer.idkhachhang}</td>
                    <td>${accountCustomer.tendangnhap}</td>
                    <td>${accountCustomer.trangthai == 1 ? "Đang hoạt động " : "Khóa"}</td>
                    <td>
                        <a href="../controller/index.php?pg=khoakhachhang&idkhachhang=${accountCustomer.idkhachhang}" onclick="return confirm('Bạn có chắc muốn khóa khách hàng ${accountCustomer.idkhachhang} không?')">
                            <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                        </a>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
        // Cập nhật phân trang 
        const pagination = document.getElementById('pagination_accountCustomer');
        pagination.replaceChildren();
        for(let i = 1; i <= data.totalPages; i++){
            pagination.innerHTML += `
                <button data-page= "${i}" ${i == data.currentPage ? 'class = "active"' : '' }>${i}</button>
            `
        }

        //gắn sự kiện cho các nút phân trang
        document.querySelectorAll("#pagination_accountCustomer button").forEach(button=>{
            button.addEventListener("click", function(){
                const page = parseInt(this.getAttribute("data-page"));
                searchTaiKhoanKhachHang(keyword, page);
            });
        });
    })
    .catch(error => console.error("Error fetching data ", error));

}

document.getElementById("searchTKKH").addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword !== "") {
        searchTaiKhoanKhachHang(keyword);
    } else {
        loadTaiKhoanKhachHang(1);
    }
});
