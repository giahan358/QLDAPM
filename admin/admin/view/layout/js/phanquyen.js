// function loadPower(page) {
//     fetch(`../controller/pagPhanQuyen.php?page=${page}`)
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Network response was not ok');
//             }
//             return response.json();
//         })
//         .then(data => {
//             const tbody = document.getElementById("power-table-body");
//             tbody.innerHTML = ''; // Xóa dữ liệu cũ

//             // Thêm dữ liệu mới vào bảng
//             data.data.forEach(power => {
//                 const row = `
//                     <tr>
//                         <td>${power.idquyen}</td>
//                         <td>${power.tenquyen}</td>
//                         <td>${power.trangthai == 1 ? "Hoạt động" : "Tạm ngừng"}</td>
//                         <td>
//                             <a>
//                                 <img src="../view/layout/logo-icon/detail-icon.png" alt="Xem chi tiết" style="width:20px; height:20px;">
//                             </a>
//                         </td>
//                         <td>
//                             <a href="#">
//                                 <img src="../view/layout/logo-icon/update-icon.png" alt="Sửa" style="width:20px; height:20px;">
//                             </a>
//                         </td>
//                         <td>
//                             <a href="../controller/index.php?pg=xoaquyen&idquyen=${power.idquyen}" onclick="return confirm('Bạn có chắc muốn nhóm quyền ${power.tenquyen} không?')">
//                                 <img src="../view/layout/logo-icon/delete-icon.png" alt="Xóa" style="width:20px; height:20px;">
//                             </a>
//                         </td>
//                     </tr>
//                 `;
//                 tbody.innerHTML += row;
//             });

//             // Cập nhật phân trang
//             const pagination = document.getElementById("pagination_power");
//             pagination.innerHTML = "";
//             for (let i = 1; i <= data.totalPages; i++) {
//                 pagination.innerHTML += `
//                     <button data-page="${i}" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>
//                 `;
//             }

//             // Gán sự kiện click cho các nút phân trang
//             document.querySelectorAll("#pagination_power button").forEach(button => {
//                 button.addEventListener("click", function () {
//                     const page = parseInt(this.getAttribute("data-page"));
//                     loadPower(page);
//                 });
//             });
//         })
//         .catch(error => console.error('Error fetching data:', error));
// }


// //tải trang đầu tiên khi trang được tải
// document.addEventListener("DOMContentLoaded", function(){
//     loadPower(1);
// });

function searchQuyen() {
    const searchTerm = document.getElementById('searchQuyen').value.toLowerCase();  
    const rows = document.querySelectorAll('.table-body tr');  

    rows.forEach(row => {
        const quyenName = row.querySelector('td:nth-child(2)').textContent.toLowerCase(); 
        if (quyenName.includes(searchTerm)) {
            row.style.display = '';  
        } else {
            row.style.display = 'none'; 
        }
    });
}

function loadData() {
    const searchTerm = document.getElementById('searchQuyen').value;  
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `pagPhanQuyen.php?search=${searchTerm}`, true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            const quyenData = JSON.parse(xhr.responseText);  // Parse the JSON response
            const tableBody = document.getElementById('quyenTableBody');  // Get the table body element

            tableBody.innerHTML = '';

            quyenData.forEach((quyen, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${quyen.Quyen}</td>
                    <td>${quyen.ChucNang}</td>
                `;
                tableBody.appendChild(row);  
            });
        }
    };

    xhr.send();  
}

loadData();

document.getElementById('searchQuyen').addEventListener('input', loadData);


