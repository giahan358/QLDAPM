document.getElementById('productImage').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const previewImage = document.getElementById('previewImage');

    // Hiển thị tên file
    showFileName(event);

    // Hiển thị ảnh
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewImage.style.display = 'none';
    }
});

function showFileName(event) {
    const input = event.target;
    const fileNameSpan = document.getElementById('fileName');

    if (input.files.length > 0) {
        fileNameSpan.textContent = input.files[0].name;
    } else {
        fileNameSpan.textContent = 'default-book.jpg';
    }
}
