<?php
    include_once '../../model/connectDB.php';
    include_once '../../model/dangNhap.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username-NV'];
        $password = $_POST['password-NV'];

        $user_data = checkUsernameExist($username);

        if (!$user_data) {
            echo "<script>alert('Tên đăng nhập không tồn tại! Vui lòng nhập lại.');</script>";
        } else {
            $user_info = checkuser($username, $password);

            if (!$user_info) {
                echo "<script>alert('Mật khẩu không đúng! Vui lòng nhập lại.');</script>";
            } else {
                if ($user_info['TrangThai'] == 0) {
                    echo "<script>alert('Tài khoản này đã bị khóa. Vui lòng liên hệ quản trị viên.');</script>";
                } else {
                    session_start();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username-NV'] = $user_info['TaiKhoan'];
                    $_SESSION['idnhanvien'] = $user_info['idnhanvien'];
                    $_SESSION['quyen'] = $user_info['Quyen'];
                    // echo "<script>alert('Đăng nhập thành công!');</script>";
                    echo "<script>window.location.href = '../controller/index.php?pg=sanpham';</script>";
                    exit;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="../view/layout/css/dangNhap.css">
</head>
<body>
    <div class="login-form">
        <h2>Đăng Nhập</h2>
        <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
            <div class="input-container">
                <label for="username-NV">Tên đăng nhập:</label>
                <input type="text" name="username-NV" placeholder="Tên đăng nhập" 
                    value="<?php echo isset($_POST['username-NV']) ? htmlspecialchars($_POST['username-NV']) : ''; ?>" required>
            </div>
            <div class="input-container">
                <label for="password-NV">Mật khẩu:</label>
                <input type="password" name="password-NV" placeholder="Mật khẩu" 
                    value="<?php echo isset($_POST['password-NV']) ? htmlspecialchars($_POST['password-NV']) : ''; ?>" required>
            </div>
            <div class="button-container">
                <button type="submit">Đăng Nhập</button>
            </div>
        </form>
    </div>
</body>
</html>
