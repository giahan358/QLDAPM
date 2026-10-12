function loadPage(page){
    fetch(`../controller/pagNhaCungCap.php?page=${page}`)
    .then(response =>{
        if(!response.ok){
            throw new Error("Network response was Not ok");
        }
        return response.json();
    })
    .then(data=>{
        const tbody = document.getElementById('provider-table-body');
        tbody.replaceChildren();
        //thêm dữ liệu mới vào bảng 

        data.data.forEach(provider=>{
            const row = 
                `
                    <tr>
                        <td>${provider.idnhacungcap}</td>
                        <td>${provider.tenncc}</td>
                        <td>${provider.email}</td>
                        <td>${provider.sodienthoai}</td>
                        <td>${provider.diachi}</td>
                        <td>${provider.trangthai == 1 ? "Đang hoạt động" : "Tạm ngưng"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suanhacungcap&idnhacungcap=${provider.idnhacungcap}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoanhacungcap&idnhacungcap=${provider.idnhacungcap}" onclick="return confirm('Bạn có chắc muốn xóa nhà cung cấp ${provider.tenncc} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
            tbody.innerHTML += row;
        });
        //cập nhật phân trang
        const pagination = document.getElementById('pagination');
        pagination.replaceChildren();
        for(let i =1; i<= data.totalPages; i++){
            pagination.innerHTML += `
                <button data-page="${i}" ${i == data.currentPage? 'class = "active"' : ''}>${i}</button>
            `;
        }
        //xử lý nút bấm phân trang
        document.querySelectorAll("#pagination button").forEach(button =>{
            button.addEventListener("click", function(){
                const page = parseInt(this.getAttribute('data-page'));
                loadPage(page);
            });
        });
        
    })
    .catch(error => console.error("Error fetching data ", error));
}

function checkFormNCC(){
    let emailNCC = document.querySelector('input[name="email"]').value.trim();
    let phoneNCC = document.querySelector('input[name="phoneNumber"]').value.trim();

    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if(!emailPattern.test(emailNCC)){
        alert("Email không hợp lệ! Vui lòng nhập theo cú pháp 'abc@gmail.com'.");
        return false;
    }

    let phonePattern = /^[0-9]{10,11}$/;
    if(!phonePattern.test(phoneNCC)){
        alert("Số điện thoại không hợp lệ! Vui lòng nhập 10 hoặc 11 số.");
        return false;
    }

    return true;
}

function searchNCC(keyword, page = 1) {
    fetch(`../controller/pagNhaCungCap.php?search=${encodeURIComponent(keyword)}&page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('provider-table-body');
            tbody.replaceChildren();

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align:center;">Không tìm thấy nhà cung cấp phù hợp!</td>
                    </tr>
                `;
                document.getElementById("pagination").innerHTML = '';
                return;
            }

            data.data.forEach(provider => {
                const row = `
                    <tr>
                        <td>${provider.idnhacungcap}</td>
                        <td>${provider.tenncc}</td>
                        <td>${provider.email}</td>
                        <td>${provider.sodienthoai}</td>
                        <td>${provider.diachi}</td>
                        <td>${provider.trangthai == 1 ? "Đang hoạt động" : "Tạm ngưng"}</td>
                        <td>
                            <a href="../controller/index.php?pg=suanhacungcap&idnhacungcap=${provider.idnhacungcap}">
                                <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
                            </a>
                        </td>
                        <td>
                            <a href="../controller/index.php?pg=xoanhacungcap&idnhacungcap=${provider.idnhacungcap}" onclick="return confirm('Bạn có chắc muốn xóa nhà cung cấp ${provider.tenncc} không?')">
                                <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
                            </a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            const pagination = document.getElementById('pagination');
            pagination.replaceChildren();

            for (let i = 1; i <= data.totalPages; i++) {
                pagination.innerHTML += `
                    <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
                `;
            }

            document.querySelectorAll("#pagination button").forEach(button => {
                button.addEventListener("click", function () {
                    const newPage = parseInt(this.getAttribute("data-page"));
                    searchNCC(keyword, newPage);
                });
            });
        })
        .catch(error => {
            console.error("Lỗi khi tìm kiếm nhà cung cấp:", error);
        });
}

//tải trang đầu tiên khi trang được tải
document.addEventListener("DOMContentLoaded", function(){
    loadPage(1);
    const form = document.querySelector("form");
    form.addEventListener("submit", function(event) {
        if(!checkFormNCC()){
            event.preventDefault();
        }
    });
});

// Tìm kiếm nhà cung câp
document.getElementById("searchNCC").addEventListener("input", function () {
    const keyword = this.value.trim();
    if (keyword !== "") {
        searchNCC(keyword);
    } else {
        loadPage(1);
    }
});

