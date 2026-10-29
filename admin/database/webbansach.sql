-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 04, 2025 lúc 03:26 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `webbansach`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banner`
--

CREATE TABLE `banner` (
  `idbanner` int(11) NOT NULL,
  `hinhanh` varchar(255) NOT NULL,
  `mota` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `banner`
--

INSERT INTO `banner` (`idbanner`, `hinhanh`, `mota`) VALUES
(1, 'img/banner/banner1.webp', 'Banner quảng cáo ưu đãi lên đến 50%.'),
(2, 'img/banner/banner2.webp', 'Banner Rộng mở giãn đơn.'),
(3, 'img/banner/banner3.webp', 'Banner Câu đố trí não'),
(4, 'img/banner/banner4.webp', 'Banner hồi sinh đứa trẻ bên trong bạn.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdanhmuc`
--

CREATE TABLE `chitietdanhmuc` (
  `idchitietdanhmuc` int(11) NOT NULL,
  `iddanhmuc` int(11) NOT NULL,
  `tenchitietdanhmuc` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdanhmuc`
--

INSERT INTO `chitietdanhmuc` (`idchitietdanhmuc`, `iddanhmuc`, `tenchitietdanhmuc`) VALUES
(1, 1, 'Tiểu thuyết lãng mạn'),
(2, 1, 'Tiểu thuyết lịch sử'),
(3, 1, 'Tiểu thuyết hiện đại'),
(4, 2, 'Khoa học viễn tưởng không gian'),
(5, 2, 'Khoa học viễn tưởng thời gian'),
(6, 2, 'Khoa học viễn tưởng công nghệ'),
(7, 3, 'Trinh thám kinh điển'),
(8, 3, 'Trinh thám hiện đại'),
(9, 3, 'Trinh thám tâm lý'),
(10, 4, 'Lãng mạn tuổi teen'),
(11, 4, 'Lãng mạn gia đình'),
(12, 4, 'Lãng mạn cổ điển'),
(13, 5, 'Kinh dị siêu nhiên'),
(14, 5, 'Kinh dị tâm lý'),
(15, 5, 'Kinh dị sinh tồn'),
(16, 6, 'Tự truyện cá nhân'),
(17, 6, 'Tự truyện nổi tiếng'),
(18, 6, 'Tự truyện truyền cảm hứng'),
(19, 7, 'Khoa học vật lý'),
(20, 7, 'Khoa học thiên văn'),
(21, 7, 'Khoa học sinh học'),
(22, 8, 'Lịch sử Việt Nam'),
(23, 8, 'Lịch sử thế giới'),
(24, 8, 'Lịch sử chiến tranh'),
(25, 9, 'Truyện tranh thiếu nhi'),
(26, 9, 'Sách giáo dục thiếu nhi'),
(27, 9, 'Truyện cổ tích thiếu nhi'),
(28, 10, 'Kỹ năng giao tiếp'),
(29, 10, 'Kỹ năng quản lý thời gian'),
(30, 10, 'Kỹ năng phát triển bản thân'),
(31, 1, 'ww');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiethoadon`
--

CREATE TABLE `chitiethoadon` (
  `idchitiethoadon` int(11) NOT NULL,
  `idhoadon` int(11) NOT NULL,
  `idsach` int(11) NOT NULL,
  `soluong` int(11) NOT NULL,
  `gia` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiethoadon`
--

INSERT INTO `chitiethoadon` (`idchitiethoadon`, `idhoadon`, `idsach`, `soluong`, `gia`) VALUES
(6, 19, 8, 1, 65000.00),
(7, 19, 10, 1, 45000.00),
(8, 19, 24, 1, 110000.00),
(9, 19, 9, 1, 110000.00),
(10, 20, 10, 1, 45000.00),
(11, 21, 24, 1, 110000.00),
(12, 22, 9, 1, 110000.00),
(13, 23, 8, 1, 65000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietphieunhap`
--

CREATE TABLE `chitietphieunhap` (
  `idchitietphieunhap` int(11) NOT NULL,
  `idphieunhap` int(11) NOT NULL,
  `idsach` int(11) NOT NULL,
  `soluong` int(11) NOT NULL,
  `gia` decimal(10,2) NOT NULL,
  `loinhuan` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietphieunhap`
--

INSERT INTO `chitietphieunhap` (`idchitietphieunhap`, `idphieunhap`, `idsach`, `soluong`, `gia`, `loinhuan`) VALUES
(1, 7, 1, 4, 12599975.00, 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `iddanhmuc` int(11) NOT NULL,
  `tendanhmuc` varchar(255) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc`
--

INSERT INTO `danhmuc` (`iddanhmuc`, `tendanhmuc`, `trangthai`) VALUES
(1, 'Sách Văn học', 1),
(2, 'Sách Khoa học', 1),
(3, 'Sách Thiếu nhi', 1),
(4, 'Sách Kỹ năng', 1),
(5, 'Sách Lịch sử', 1),
(6, 'Sách Ngoại văn', 1),
(7, 'Sách Kinh tế', 1),
(8, 'S(sinách Giáo dục', 1),
(9, 'Sách Tâm lý', 1),
(10, 'Sách Truyện tranh', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `idgiohang` int(11) NOT NULL,
  `idkhachhang` int(11) NOT NULL,
  `idsach` int(11) NOT NULL,
  `soluong` int(11) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinhanhsach`
--

CREATE TABLE `hinhanhsach` (
  `idhinhanh` int(11) NOT NULL,
  `idsach` int(11) NOT NULL,
  `duongdananh` varchar(255) NOT NULL,
  `mota` text NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hinhanhsach`
--

INSERT INTO `hinhanhsach` (`idhinhanh`, `idsach`, `duongdananh`, `mota`, `trangthai`) VALUES
(1, 1, 'img/cho_toi_xin_mot_ve/1.jpg', 'Mô tả hình ảnh 1', 1),
(2, 1, 'img/cho_toi_xin_mot_ve/2.jpg', 'Mô tả hình ảnh 2', 1),
(3, 1, 'img/cho_toi_xin_mot_ve/3.jpg', 'Mô tả hình ảnh 3', 1),
(4, 1, 'img/cho_toi_xin_mot_ve/4.jpg', 'Mô tả hình ảnh 4', 1),
(5, 1, 'img/cho_toi_xin_mot_ve/5.jpg', 'Mô tả hình ảnh 5', 1),
(6, 2, 'img/rung_na_uy/1.jpg', 'Mô tả hình ảnh 1', 1),
(7, 2, 'img/rung_na_uy/2.jpg', 'Mô tả hình ảnh 2', 1),
(8, 2, 'img/rung_na_uy/3.jpg', 'Mô tả hình ảnh 3', 1),
(9, 2, 'img/rung_na_uy/4.jpg', 'Mô tả hình ảnh 4', 1),
(10, 2, 'img/rung_na_uy/5.jpg', 'Mô tả hình ảnh 5', 1),
(11, 2, 'img/rung_na_uy/6.jpg', 'Mô tả hình ảnh 6', 1),
(12, 2, 'img/rung_na_uy/7.jpg', 'Mô tả hình ảnh 7', 1),
(13, 3, 'img/an_mang_tren_tau/1.jpg', 'Mô tả hình ảnh 1', 1),
(14, 3, 'img/an_mang_tren_tau/2.jpg', 'Mô tả hình ảnh 2', 1),
(15, 3, 'img/an_mang_tren_tau/3.jpg', 'Mô tả hình ảnh 3', 1),
(16, 3, 'img/an_mang_tren_tau/4.jpg', 'Mô tả hình ảnh 4', 1),
(17, 3, 'img/an_mang_tren_tau/5.jpg', 'Mô tả hình ảnh 5', 1),
(18, 3, 'img/an_mang_tren_tau/6.jpg', 'Mô tả hình ảnh 6', 1),
(19, 3, 'img/an_mang_tren_tau/7.jpg', 'Mô tả hình ảnh 7', 1),
(20, 3, 'img/an_mang_tren_tau/8.jpg', 'Mô tả hình ảnh 8', 1),
(21, 4, 'img/chi_pheo/1.jpg', 'Mô tả hình ảnh 1', 1),
(22, 4, 'img/chi_pheo/2.jpg', 'Mô tả hình ảnh 2', 1),
(23, 4, 'img/chi_pheo/3.jpg', 'Mô tả hình ảnh 3', 1),
(24, 4, 'img/chi_pheo/4.jpg', 'Mô tả hình ảnh 4', 1),
(25, 4, 'img/chi_pheo/5.jpg', 'Mô tả hình ảnh 5', 1),
(26, 4, 'img/chi_pheo/6.jpg', 'Mô tả hình ảnh 6', 1),
(27, 5, 'img/the_shinning/1.jpg', 'Mô tả hình ảnh 1', 1),
(28, 5, 'img/the_shinning/2.jpg', 'Mô tả hình ảnh 2', 1),
(29, 5, 'img/the_shinning/3.jpg', 'Mô tả hình ảnh 3', 1),
(30, 5, 'img/the_shinning/4.jpg', 'Mô tả hình ảnh 4', 1),
(31, 5, 'img/the_shinning/5.jpg', 'Mô tả hình ảnh 5', 1),
(32, 5, 'img/the_shinning/6.jpg', 'Mô tả hình ảnh 6', 1),
(33, 5, 'img/the_shinning/7.jpg', 'Mô tả hình ảnh 7', 1),
(34, 6, 'img/so_do/1.jpg', 'Mô tả hình ảnh 1', 1),
(35, 6, 'img/so_do/2.jpg', 'Mô tả hình ảnh 2', 1),
(36, 6, 'img/so_do/3.jpg', 'Mô tả hình ảnh 3', 1),
(37, 6, 'img/so_do/4.jpg', 'Mô tả hình ảnh 4', 1),
(38, 6, 'img/so_do/5.jpg', 'Mô tả hình ảnh 5', 1),
(39, 6, 'img/so_do/6.jpg', 'Mô tả hình ảnh 6', 1),
(40, 7, 'img/comos/1.jpg', 'Mô tả hình ảnh 1', 1),
(41, 7, 'img/comos/2.jpg', 'Mô tả hình ảnh 2', 1),
(42, 7, 'img/comos/3.jpg', 'Mô tả hình ảnh 3', 1),
(43, 7, 'img/comos/4.jpg', 'Mô tả hình ảnh 4', 1),
(44, 7, 'img/comos/5.jpg', 'Mô tả hình ảnh 5', 1),
(45, 7, 'img/comos/6.jpg', 'Mô tả hình ảnh 6', 1),
(46, 8, 'img/de_men_phieu_luu_ky/bia.jpg', 'Mô tả hình ảnh bìa', 1),
(47, 9, 'img/dac_nhan_tam/1.jpg', 'Mô tả hình ảnh 1', 1),
(48, 9, 'img/dac_nhan_tam/2.jpg', 'Mô tả hình ảnh 2', 1),
(49, 9, 'img/dac_nhan_tam/3.jpg', 'Mô tả hình ảnh 3', 1),
(50, 9, 'img/dac_nhan_tam/4.jpg', 'Mô tả hình ảnh 4', 1),
(51, 9, 'img/dac_nhan_tam/5.jpg', 'Mô tả hình ảnh 5', 1),
(52, 9, 'img/dac_nhan_tam/6.jpg', 'Mô tả hình ảnh 6', 1),
(53, 9, 'img/dac_nhan_tam/7.jpg', 'Mô tả hình ảnh 7', 1),
(54, 9, 'img/dac_nhan_tam/8.jpg', 'Mô tả hình ảnh 8', 1),
(55, 9, 'img/dac_nhan_tam/9.jpg', 'Mô tả hình ảnh 9', 1),
(56, 10, 'img/goc_san_va_khoang_troi/1.jpg', 'Mô tả hình ảnh 1', 1),
(57, 10, 'img/goc_san_va_khoang_troi/2.jpg', 'Mô tả hình ảnh 2', 1),
(58, 10, 'img/goc_san_va_khoang_troi/3.jpg', 'Mô tả hình ảnh 3', 1),
(59, 10, 'img/goc_san_va_khoang_troi/4.jpg', 'Mô tả hình ảnh 4', 1),
(60, 10, 'img/goc_san_va_khoang_troi/5.jpg', 'Mô tả hình ảnh 5', 1),
(61, 10, 'img/goc_san_va_khoang_troi/6.jpg', 'Mô tả hình ảnh 6', 1),
(62, 10, 'img/goc_san_va_khoang_troi/7.jpg', 'Mô tả hình ảnh 7', 1),
(63, 11, 'img/luoc_su_thoi_gian/1.jpg', 'Mô tả hình ảnh 1', 1),
(64, 11, 'img/luoc_su_thoi_gian/2.jpg', 'Mô tả hình ảnh 2', 1),
(65, 11, 'img/luoc_su_thoi_gian/3.jpg', 'Mô tả hình ảnh 3', 1),
(66, 11, 'img/luoc_su_thoi_gian/4.jpg', 'Mô tả hình ảnh 4', 1),
(67, 11, 'img/luoc_su_thoi_gian/5.jpg', 'Mô tả hình ảnh 5', 1),
(68, 11, 'img/luoc_su_thoi_gian/6.jpg', 'Mô tả hình ảnh 6', 1),
(69, 12, 'img/nha_gia_kim/1.jpg', 'Mô tả hình ảnh 1', 1),
(70, 12, 'img/nha_gia_kim/2.jpg', 'Mô tả hình ảnh 2', 1),
(71, 12, 'img/nha_gia_kim/3.jpg', 'Mô tả hình ảnh 3', 1),
(72, 12, 'img/nha_gia_kim/4.jpg', 'Mô tả hình ảnh 4', 1),
(73, 12, 'img/nha_gia_kim/5.jpg', 'Mô tả hình ảnh 5', 1),
(74, 12, 'img/nha_gia_kim/6.jpg', 'Mô tả hình ảnh 6', 1),
(75, 12, 'img/nha_gia_kim/7.jpg', 'Mô tả hình ảnh 7', 1),
(76, 12, 'img/nha_gia_kim/8.jpg', 'Mô tả hình ảnh 8', 1),
(77, 13, 'img/di_tim_le_song/1.jpg', 'Mô tả hình ảnh 1', 1),
(78, 13, 'img/di_tim_le_song/2.jpg', 'Mô tả hình ảnh 2', 1),
(79, 13, 'img/di_tim_le_song/3.jpg', 'Mô tả hình ảnh 3', 1),
(80, 13, 'img/di_tim_le_song/4.jpg', 'Mô tả hình ảnh 4', 1),
(81, 13, 'img/di_tim_le_song/5.jpg', 'Mô tả hình ảnh 5', 1),
(82, 13, 'img/di_tim_le_song/6.jpg', 'Mô tả hình ảnh 6', 1),
(83, 13, 'img/di_tim_le_song/7.jpg', 'Mô tả hình ảnh 7', 1),
(84, 13, 'img/di_tim_le_song/8.jpg', 'Mô tả hình ảnh 8', 1),
(85, 13, 'img/di_tim_le_song/9.jpg', 'Mô tả hình ảnh 9', 1),
(86, 13, 'img/di_tim_le_song/10.jpg', 'Mô tả hình ảnh 10', 1),
(87, 13, 'img/di_tim_le_song/11.jpg', 'Mô tả hình ảnh 11', 1),
(88, 14, 'img/bo_gia/1.jpg', 'Mô tả hình ảnh 1', 1),
(89, 14, 'img/bo_gia/2.jpg', 'Mô tả hình ảnh 2', 1),
(90, 14, 'img/bo_gia/3.jpg', 'Mô tả hình ảnh 3', 1),
(91, 14, 'img/bo_gia/4.jpg', 'Mô tả hình ảnh 4', 1),
(92, 14, 'img/bo_gia/5.jpg', 'Mô tả hình ảnh 5', 1),
(93, 14, 'img/bo_gia/6.jpg', 'Mô tả hình ảnh 6', 1),
(94, 14, 'img/bo_gia/7.jpg', 'Mô tả hình ảnh 7', 1),
(95, 14, 'img/bo_gia/8.jpg', 'Mô tả hình ảnh 8', 1),
(96, 14, 'img/bo_gia/9.jpg', 'Mô tả hình ảnh 9', 1),
(97, 14, 'img/bo_gia/10.jpg', 'Mô tả hình ảnh 10', 1),
(98, 15, 'img/co_gai_den_tu_hom_qua/1.jpg', 'Mô tả hình ảnh 1', 1),
(99, 15, 'img/co_gai_den_tu_hom_qua/2.jpg', 'Mô tả hình ảnh 2', 1),
(100, 15, 'img/co_gai_den_tu_hom_qua/3.jpg', 'Mô tả hình ảnh 3', 1),
(101, 15, 'img/co_gai_den_tu_hom_qua/4.jpg', 'Mô tả hình ảnh 4', 1),
(102, 16, 'img/mat_biet/1.jpg', 'Mô tả hình ảnh 1', 1),
(103, 16, 'img/mat_biet/2.jpg', 'Mô tả hình ảnh 2', 1),
(104, 16, 'img/mat_biet/3.jpg', 'Mô tả hình ảnh 3', 1),
(105, 16, 'img/mat_biet/4.jpg', 'Mô tả hình ảnh 4', 1),
(106, 16, 'img/mat_biet/5.jpg', 'Mô tả hình ảnh 5', 1),
(107, 17, 'img/kafka_ben_bo_bien/1.jpg', 'Mô tả hình ảnh 1', 1),
(108, 17, 'img/kafka_ben_bo_bien/2.jpg', 'Mô tả hình ảnh 2', 1),
(109, 17, 'img/kafka_ben_bo_bien/3.jpg', 'Mô tả hình ảnh 3', 1),
(110, 17, 'img/kafka_ben_bo_bien/4.jpg', 'Mô tả hình ảnh 4', 1),
(111, 17, 'img/kafka_ben_bo_bien/5.jpg', 'Mô tả hình ảnh 5', 1),
(112, 18, 'img/1q84/1.jpg', 'Mô tả hình ảnh 1', 1),
(113, 18, 'img/1q84/2.jpg', 'Mô tả hình ảnh 2', 1),
(114, 18, 'img/1q84/3.jpg', 'Mô tả hình ảnh 3', 1),
(115, 18, 'img/1q84/4.jpg', 'Mô tả hình ảnh 4', 1),
(116, 18, 'img/1q84/5.jpg', 'Mô tả hình ảnh 5', 1),
(117, 18, 'img/1q84/6.jpg', 'Mô tả hình ảnh 6', 1),
(118, 18, 'img/1q84/7.jpg', 'Mô tả hình ảnh 7', 1),
(119, 18, 'img/1q84/8.jpg', 'Mô tả hình ảnh 8', 1),
(120, 18, 'img/1q84/9.jpg', 'Mô tả hình ảnh 9', 1),
(121, 18, 'img/1q84/10.jpg', 'Mô tả hình ảnh 10', 1),
(122, 19, 'img/nhung_nguoi_khon_kho/1.jpg', 'Mô tả hình ảnh 1', 1),
(123, 19, 'img/nhung_nguoi_khon_kho/2.jpg', 'Mô tả hình ảnh 2', 1),
(124, 19, 'img/nhung_nguoi_khon_kho/3.jpg', 'Mô tả hình ảnh 3', 1),
(125, 19, 'img/nhung_nguoi_khon_kho/4.jpg', 'Mô tả hình ảnh 4', 1),
(126, 19, 'img/nhung_nguoi_khon_kho/5.jpg', 'Mô tả hình ảnh 5', 1),
(127, 19, 'img/nhung_nguoi_khon_kho/6.jpg', 'Mô tả hình ảnh 6', 1),
(128, 19, 'img/nhung_nguoi_khon_kho/7.jpg', 'Mô tả hình ảnh 7', 1),
(129, 19, 'img/nhung_nguoi_khon_kho/8.jpg', 'Mô tả hình ảnh 8', 1),
(130, 19, 'img/nhung_nguoi_khon_kho/9.jpg', 'Mô tả hình ảnh 9', 1),
(131, 20, 'img/chien_tranh_va_hoa_binh/1.jpg', 'Mô tả hình ảnh 1', 1),
(132, 20, 'img/chien_tranh_va_hoa_binh/2.jpg', 'Mô tả hình ảnh 2', 1),
(133, 20, 'img/chien_tranh_va_hoa_binh/3.jpg', 'Mô tả hình ảnh 3', 1),
(134, 20, 'img/chien_tranh_va_hoa_binh/4.jpg', 'Mô tả hình ảnh 4', 1),
(135, 21, 'img/tieng_chim_hot_trong_bui_man_gai/1.jpg', 'Mô tả hình ảnh 1', 1),
(136, 21, 'img/tieng_chim_hot_trong_bui_man_gai/2.jpg', 'Mô tả hình ảnh 2', 1),
(137, 21, 'img/tieng_chim_hot_trong_bui_man_gai/3.jpg', 'Mô tả hình ảnh 3', 1),
(138, 21, 'img/tieng_chim_hot_trong_bui_man_gai/4.jpg', 'Mô tả hình ảnh 4', 1),
(139, 21, 'img/tieng_chim_hot_trong_bui_man_gai/5.jpg', 'Mô tả hình ảnh 5', 1),
(140, 21, 'img/tieng_chim_hot_trong_bui_man_gai/6.jpg', 'Mô tả hình ảnh 6', 1),
(141, 21, 'img/tieng_chim_hot_trong_bui_man_gai/7.jpg', 'Mô tả hình ảnh 7', 1),
(142, 22, 'img/ong_gia_va_bien_ca/1.jpg', 'Mô tả hình ảnh 1', 1),
(143, 22, 'img/ong_gia_va_bien_ca/2.jpg', 'Mô tả hình ảnh 2', 1),
(144, 22, 'img/ong_gia_va_bien_ca/3.jpg', 'Mô tả hình ảnh 3', 1),
(145, 22, 'img/ong_gia_va_bien_ca/4.jpg', 'Mô tả hình ảnh 4', 1),
(146, 22, 'img/ong_gia_va_bien_ca/5.jpg', 'Mô tả hình ảnh 5', 1),
(147, 23, 'img/cuon_theo_chieu_gio/1.jpg', 'Mô tả hình ảnh 1', 1),
(148, 23, 'img/cuon_theo_chieu_gio/2.jpg', 'Mô tả hình ảnh 2', 1),
(149, 23, 'img/cuon_theo_chieu_gio/3.jpg', 'Mô tả hình ảnh 3', 1),
(150, 23, 'img/cuon_theo_chieu_gio/4.jpg', 'Mô tả hình ảnh 4', 1),
(151, 24, 'img/harrypotter_va_hon_da_phu_thuy/1.jpg', 'Mô tả hình ảnh 1', 1),
(152, 24, 'img/harrypotter_va_hon_da_phu_thuy/2.jpg', 'Mô tả hình ảnh 2', 1),
(153, 24, 'img/harrypotter_va_hon_da_phu_thuy/3.jpg', 'Mô tả hình ảnh 3', 1),
(154, 24, 'img/harrypotter_va_hon_da_phu_thuy/4.jpg', 'Mô tả hình ảnh 4', 1),
(155, 24, 'img/harrypotter_va_hon_da_phu_thuy/5.jpg', 'Mô tả hình ảnh 5', 1),
(156, 24, 'img/harrypotter_va_hon_da_phu_thuy/6.jpg', 'Mô tả hình ảnh 6', 1),
(157, 24, 'img/harrypotter_va_hon_da_phu_thuy/7.jpg', 'Mô tả hình ảnh 7', 1),
(158, 24, 'img/harrypotter_va_hon_da_phu_thuy/8.jpg', 'Mô tả hình ảnh 8', 1),
(159, 24, 'img/harrypotter_va_hon_da_phu_thuy/9.jpg', 'Mô tả hình ảnh 9', 1),
(160, 25, 'img/harrypoter_va_phong_chua_bi_mat/1.jpg', 'Mô tả hình ảnh 1', 1),
(161, 25, 'img/harrypoter_va_phong_chua_bi_mat/2.jpg', 'Mô tả hình ảnh 2', 1),
(162, 25, 'img/harrypoter_va_phong_chua_bi_mat/3.jpg', 'Mô tả hình ảnh 3', 1),
(163, 26, 'img/harrypotter_va_dia_nguc_buoi/1.jpg', 'Mô tả hình ảnh 1', 1),
(164, 26, 'img/harrypotter_va_dia_nguc_buoi/2.jpg', 'Mô tả hình ảnh 2', 1),
(165, 26, 'img/harrypotter_va_dia_nguc_buoi/3.jpg', 'Mô tả hình ảnh 3', 1),
(166, 26, 'img/harrypotter_va_dia_nguc_buoi/4.jpg', 'Mô tả hình ảnh 4', 1),
(167, 27, 'img/harrypotter_va_cbiec_coc_lua/1.jpg', 'Mô tả hình ảnh 1', 1),
(168, 27, 'img/harrypotter_va_cbiec_coc_lua/2.jpg', 'Mô tả hình ảnh 2', 1),
(169, 27, 'img/harrypotter_va_cbiec_coc_lua/3.jpg', 'Mô tả hình ảnh 3', 1),
(170, 28, 'img/hrpt_va_hoi_phuong_hoang/1.jpg', 'Mô tả hình ảnh 1', 1),
(171, 29, 'img/hrpt_va_hoang_tu_lai/1.jpg', 'Mô tả hình ảnh 1', 1),
(172, 29, 'img/hrpt_va_hoang_tu_lai/2.jpg', 'Mô tả hình ảnh 2', 1),
(173, 29, 'img/hrpt_va_hoang_tu_lai/3.jpg', 'Mô tả hình ảnh 3', 1),
(174, 29, 'img/hrpt_va_hoang_tu_lai/4.jpg', 'Mô tả hình ảnh 4', 1),
(175, 30, 'img/hrpt_va_bao_boi_tu_than/1.jpg', 'Mô tả hình ảnh 1', 1),
(176, 31, 'img/chang_vang/bia.jpg', 'Mô tả hình ảnh bìa', 1),
(177, 32, 'img/trang_non/1.jpg', 'Mô tả hình ảnh 1', 1),
(178, 32, 'img/trang_non/2.jpg', 'Mô tả hình ảnh 2', 1),
(179, 32, 'img/trang_non/3.jpg', 'Mô tả hình ảnh 3', 1),
(180, 32, 'img/trang_non/4.jpg', 'Mô tả hình ảnh 4', 1),
(181, 32, 'img/trang_non/5.jpg', 'Mô tả hình ảnh 5', 1),
(182, 33, 'img/nhat_thuc/1.jpg', 'Mô tả hình ảnh 1', 1),
(183, 33, 'img/nhat_thuc/2.jpg', 'Mô tả hình ảnh 2', 1),
(184, 33, 'img/nhat_thuc/3.jpg', 'Mô tả hình ảnh 3', 1),
(185, 33, 'img/nhat_thuc/4.jpg', 'Mô tả hình ảnh 4', 1),
(186, 33, 'img/nhat_thuc/5.jpg', 'Mô tả hình ảnh 5', 1),
(187, 34, 'img/hung_dong/1.jpg', 'Mô tả hình ảnh 1', 1),
(188, 34, 'img/hung_dong/2.jpg', 'Mô tả hình ảnh 2', 1),
(189, 35, 'img/doi_gio_hu/1.jpg', 'Mô tả hình ảnh 1', 1),
(190, 35, 'img/doi_gio_hu/2.jpg', 'Mô tả hình ảnh 2', 1),
(191, 35, 'img/doi_gio_hu/3.jpg', 'Mô tả hình ảnh 3', 1),
(192, 35, 'img/doi_gio_hu/4.jpg', 'Mô tả hình ảnh 4', 1),
(193, 35, 'img/doi_gio_hu/5.jpg', 'Mô tả hình ảnh 5', 1),
(194, 36, 'img/jane_eyre/1.jpg', 'Mô tả hình ảnh 1', 1),
(195, 36, 'img/jane_eyre/2.jpg', 'Mô tả hình ảnh 2', 1),
(196, 36, 'img/jane_eyre/3.jpg', 'Mô tả hình ảnh 3', 1),
(197, 37, 'img/bat_tre_dong_xanh/1.jpg', 'Mô tả hình ảnh 1', 1),
(198, 37, 'img/bat_tre_dong_xanh/2.jpg', 'Mô tả hình ảnh 2', 1),
(199, 37, 'img/bat_tre_dong_xanh/3.jpg', 'Mô tả hình ảnh 3', 1),
(200, 37, 'img/bat_tre_dong_xanh/4.jpg', 'Mô tả hình ảnh 4', 1),
(201, 37, 'img/bat_tre_dong_xanh/5.jpg', 'Mô tả hình ảnh 5', 1),
(202, 37, 'img/bat_tre_dong_xanh/6.jpg', 'Mô tả hình ảnh 6', 1),
(203, 37, 'img/bat_tre_dong_xanh/7.jpg', 'Mô tả hình ảnh 7', 1),
(204, 37, 'img/bat_tre_dong_xanh/8.jpg', 'Mô tả hình ảnh 8', 1),
(205, 37, 'img/bat_tre_dong_xanh/9.jpg', 'Mô tả hình ảnh 9', 1),
(206, 38, 'img/thep_da_toi_the_day/bia.jpg', 'Mô tả hình ảnh bìa', 1),
(207, 39, 'img/nhung_ke_si_tinh/1.jpg', 'Mô tả hình ảnh 1', 1),
(208, 39, 'img/nhung_ke_si_tinh/2.jpg', 'Mô tả hình ảnh 2', 1),
(209, 39, 'img/nhung_ke_si_tinh/3.jpg', 'Mô tả hình ảnh 3', 1),
(210, 39, 'img/nhung_ke_si_tinh/4.jpg', 'Mô tả hình ảnh 4', 1),
(211, 39, 'img/nhung_ke_si_tinh/5.jpg', 'Mô tả hình ảnh 5', 1),
(212, 39, 'img/nhung_ke_si_tinh/6.jpg', 'Mô tả hình ảnh 6', 1),
(213, 39, 'img/nhung_ke_si_tinh/7.jpg', 'Mô tả hình ảnh 7', 1),
(214, 40, 'img/thang_gu_nha_tho_duc_ba/1.jpg', 'Mô tả hình ảnh 1', 1),
(215, 40, 'img/thang_gu_nha_tho_duc_ba/2.jpg', 'Mô tả hình ảnh 2', 1),
(216, 40, 'img/thang_gu_nha_tho_duc_ba/3.jpg', 'Mô tả hình ảnh 3', 1),
(217, 40, 'img/thang_gu_nha_tho_duc_ba/4.jpg', 'Mô tả hình ảnh 4', 1),
(218, 40, 'img/thang_gu_nha_tho_duc_ba/5.jpg', 'Mô tả hình ảnh 5', 1),
(219, 40, 'img/thang_gu_nha_tho_duc_ba/6.jpg', 'Mô tả hình ảnh 6', 1),
(220, 41, 'img/anna/1.jpg', 'Mô tả hình ảnh 1', 1),
(221, 41, 'img/anna/2.jpg', 'Mô tả hình ảnh 2', 1),
(222, 41, 'img/anna/3.jpg', 'Mô tả hình ảnh 3', 1),
(223, 41, 'img/anna/4.jpg', 'Mô tả hình ảnh 4', 1),
(224, 41, 'img/anna/5.jpg', 'Mô tả hình ảnh 5', 1),
(225, 41, 'img/anna/6.jpg', 'Mô tả hình ảnh 6', 1),
(226, 41, 'img/anna/7.jpg', 'Mô tả hình ảnh 7', 1),
(227, 41, 'img/anna/8.jpg', 'Mô tả hình ảnh 8', 1),
(228, 41, 'img/anna/9.jpg', 'Mô tả hình ảnh 9', 1),
(229, 42, 'img/lolita/1.jpg', 'Mô tả hình ảnh 1', 1),
(230, 42, 'img/lolita/2.jpg', 'Mô tả hình ảnh 2', 1),
(231, 42, 'img/lolita/3.jpg', 'Mô tả hình ảnh 3', 1),
(232, 42, 'img/lolita/4.jpg', 'Mô tả hình ảnh 4', 1),
(233, 42, 'img/lolita/5.jpg', 'Mô tả hình ảnh 5', 1),
(234, 43, 'img/1984/bia.jpg', 'Mô tả hình ảnh bìa', 1),
(235, 44, 'img/trai_suc_vat/bia.jpg', 'Mô tả hình ảnh bìa', 1),
(236, 45, 'img/451/1.jpg', 'Mô tả hình ảnh 1', 1),
(237, 45, 'img/451/2.jpg', 'Mô tả hình ảnh 2', 1),
(238, 45, 'img/451/3.jpg', 'Mô tả hình ảnh 3', 1),
(239, 45, 'img/451/4.jpg', 'Mô tả hình ảnh 4', 1),
(240, 45, 'img/451/5.jpg', 'Mô tả hình ảnh 5', 1),
(241, 45, 'img/451/6.jpg', 'Mô tả hình ảnh 6', 1),
(242, 45, 'img/451/7.jpg', 'Mô tả hình ảnh 7', 1),
(243, 46, 'img/chua_te_cua_nhung_chiec_nha/1.jpg', 'Mô tả hình ảnh 1', 1),
(244, 46, 'img/chua_te_cua_nhung_chiec_nha/2.jpg', 'Mô tả hình ảnh 2', 1),
(245, 46, 'img/chua_te_cua_nhung_chiec_nha/3.jpg', 'Mô tả hình ảnh 3', 1),
(246, 46, 'img/chua_te_cua_nhung_chiec_nha/4.jpg', 'Mô tả hình ảnh 4', 1),
(247, 46, 'img/chua_te_cua_nhung_chiec_nha/5.jpg', 'Mô tả hình ảnh 5', 1),
(248, 46, 'img/chua_te_cua_nhung_chiec_nha/6.jpg', 'Mô tả hình ảnh 6', 1),
(249, 46, 'img/chua_te_cua_nhung_chiec_nha/7.jpg', 'Mô tả hình ảnh 7', 1),
(250, 46, 'img/chua_te_cua_nhung_chiec_nha/8.jpg', 'Mô tả hình ảnh 8', 1),
(251, 47, 'img/hobbit/1.jpg', 'Mô tả hình ảnh 1', 1),
(252, 47, 'img/hobbit/2.jpg', 'Mô tả hình ảnh 2', 1),
(253, 47, 'img/hobbit/3.jpg', 'Mô tả hình ảnh 3', 1),
(254, 47, 'img/hobbit/4.jpg', 'Mô tả hình ảnh 4', 1),
(255, 48, 'img/nguoi_dua_dieu/1.jpg', 'Mô tả hình ảnh 1', 1),
(256, 48, 'img/nguoi_dua_dieu/2.jpg', 'Mô tả hình ảnh 2', 1),
(257, 49, 'img/ngan_mat_troi_ruc_ro/1.jpg', 'Mô tả hình ảnh 1', 1),
(258, 49, 'img/ngan_mat_troi_ruc_ro/2.jpg', 'Mô tả hình ảnh 2', 1),
(259, 50, 'img/va_roi_nui_vong/1.jpg', 'Mô tả hình ảnh 1', 1),
(260, 50, 'img/va_roi_nui_vong/2.jpg', 'Mô tả hình ảnh 2', 1),
(261, 50, 'img/va_roi_nui_vong/3.jpg', 'Mô tả hình ảnh 3', 1),
(262, 51, 'img/dieu_ky_dieu/1.jpg', 'Mô tả hình ảnh 1', 1),
(263, 51, 'img/dieu_ky_dieu/2.jpg', 'Mô tả hình ảnh 2', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadon`
--

CREATE TABLE `hoadon` (
  `idhoadon` int(11) NOT NULL,
  `idkhachhang` int(11) NOT NULL,
  `iddiachi` int(11) NOT NULL,
  `idnhanvien` int(11) NOT NULL,
  `phuongthuctt` varchar(255) NOT NULL,
  `ngayxuat` date NOT NULL,
  `tongtien` decimal(10,2) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoadon`
--

INSERT INTO `hoadon` (`idhoadon`, `idkhachhang`, `iddiachi`, `idnhanvien`, `phuongthuctt`, `ngayxuat`, `tongtien`, `trangthai`) VALUES
(19, 2, 3, 1, 'Thanh toán khi nhận hàng', '2025-11-17', 330000.00, 2),
(20, 2, 3, 1, 'Thanh toán khi nhận hàng', '2025-12-01', 45000.00, 2),
(21, 2, 3, 1, 'Thanh toán khi nhận hàng', '2025-12-01', 110000.00, 3),
(22, 2, 3, 1, 'Thanh toán khi nhận hàng', '2025-12-01', 110000.00, 0),
(23, 2, 3, 1, 'Thanh toán khi nhận hàng', '2025-12-01', 65000.00, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `idkhachhang` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sodienthoai` varchar(20) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`idkhachhang`, `ten`, `email`, `sodienthoai`, `trangthai`) VALUES
(1, 'Nguyễn Văn B', 'nguyenvana@gmail.com', '0912345678', 0),
(2, 'Trần Thị B', 'tranthib@gmail.com', '0987654321', 1),
(3, 'Lê Văn C', 'levanc@yahoo.com', '0909123456', 1),
(4, 'Phạm Thị D', 'phamthid@gmail.com', '0978123456', 1),
(5, 'Hoàng Văn E', 'hoangve@outlook.com', '0967890123', 1),
(6, 'Vũ Thị F', 'vuthif@gmail.com', '0912456789', 0),
(7, 'Đặng Văn G', 'dangvang@yahoo.com', '0988123456', 0),
(8, 'Bùi Thị H', 'buithih@gmail.com', '0909876543', 1),
(9, 'Mai Văn I', 'maivani@gmail.com', '0977123456', 1),
(10, 'Lý Thị K', 'lythik@outlook.com', '0966789012', 1),
(11, 'ss', 'ss@gmail.com', '0123456789', 1),
(12, 'Phạm Thị D', 'phamthid@example.com', '0898788788', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `idnhacungcap` int(11) NOT NULL,
  `tenncc` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sodienthoai` varchar(20) NOT NULL,
  `diachi` varchar(255) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhacungcap`
--

INSERT INTO `nhacungcap` (`idnhacungcap`, `tenncc`, `email`, `sodienthoai`, `diachi`, `trangthai`) VALUES
(1, 'Công ty TNHH Fahasa', 'fahasa@gmail.com', '0909123456', '60 Lê Lợi, Q1, TP.HCM', 1),
(2, 'Công ty Sách Phương Nam', 'phuongnam@gmail.com', '0918234567', '940 Đường 3/2, Q10, TP.HCM', 1),
(3, 'Công ty Tiki', 'tiki@gmail.com', '0987654321', '52 Lê Đại Hành, Hà Nội', 1),
(4, 'Công ty Vinabook', 'vinabook@gmail.com', '0932145678', '300A Nguyễn Tất Thành, Q4, TP.HCM', 1),
(5, 'Công ty Alpha Books', 'alphabooks@gmail.com', '0945123789', '176 Thái Hà, Hà Nội', 1),
(6, 'Công ty Nhã Nam', 'nhanam@gmail.com', '0912345678', '59 Đỗ Quang, Hà Nội', 1),
(7, 'Công ty Sách Đông A', 'donga@gmail.com', '0903456789', '33 Đặng Dung, Hà Nội', 1),
(8, 'Công ty First News', 'firstnews@gmail.com', '0934567890', '11H Nguyễn Thị Minh Khai, TP.HCM', 1),
(9, 'Công ty Sách Thái Hà', 'thaiha@gmail.com', '0915678901', '119C Lý Chính Thắng, TP.HCM', 1),
(10, 'Công ty Sách Minh Lâm', 'minhlam@gmail.com', '0906789012', '45 Nguyễn Huệ, Huế', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

CREATE TABLE `nhanvien` (
  `idnhanvien` int(11) NOT NULL,
  `ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `chucvu` varchar(255) NOT NULL,
  `sodienthoai` varchar(20) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhanvien`
--

INSERT INTO `nhanvien` (`idnhanvien`, `ten`, `email`, `chucvu`, `sodienthoai`, `trangthai`) VALUES
(1, 'Nguyễn Văn A', 'nguyenvana@example.com', 'Nhân viên bán hàng', '0999888777', 1),
(2, 'Trần Thị B', 'tranthib@example.com', 'Nhân viên nhập hàng', '0123456789', 0),
(3, 'Lê Văn C', 'levancc@example.com', 'Quản lý', '0898787765', 0),
(4, 'Phạm Thị D', 'phamthid@example.com', 'Quản trị viên', '0898788788', 1),
(5, 'd', 'ss@gmail.com', 'Default', '0387299617', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhaxuatban`
--

CREATE TABLE `nhaxuatban` (
  `idnhaxuatban` int(11) NOT NULL,
  `tennxb` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sodienthoai` varchar(20) NOT NULL,
  `diachi` varchar(255) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhaxuatban`
--

INSERT INTO `nhaxuatban` (`idnhaxuatban`, `tennxb`, `email`, `sodienthoai`, `diachi`, `trangthai`) VALUES
(1, 'NXB Trẻ', 'nxbtre@gmail.com', '0909123456', '161 Lý Chính Thắng, Q3, TP.HCM', 0),
(2, 'NXB Kim Đồng', 'kimdong@nxb.com', '0918234567', '55 Quang Trung, Hà Nội', 1),
(3, 'NXB Văn Học', 'vanhoc@nxb.com', '0987654321', '18 Nguyễn Du, Hà Nội', 1),
(4, 'NXB Tổng Hợp', 'tonghop@nxb.com', '0932145678', '62 Nguyễn Thị Minh Khai,\r\n\r\n TP.HCM', 1),
(5, 'NXB Giáo Dục', 'giaoduc@nxb.com', '0945123789', '81 Trần Quốc Toàn, Hà Nội', 1),
(6, 'NXB Khoa Học', 'khoahoc@nxb.com', '0912345678', '24 Tràng Thi, Hà Nội', 1),
(7, 'NXB Phụ Nữ', 'phunu@nxb.com', '0903456789', '47 Hàng Chuối, Hà Nội', 1),
(8, 'NXB Hội Nhà Văn', 'hoinhavan@nxb.com', '0934567890', '9 Nguyễn Đình Chiểu, TP.HCM', 1),
(9, 'NXB Thế Giới', 'thegioi@nxb.com', '0915678901', '46 Trần Hưng Đạo, Hà Nội', 1),
(10, 'NXB Thanh Niên', 'thanhnien@nxb.com', '0906789012', '64 Bà Triệu, TP.HCM', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phanquyen`
--

CREATE TABLE `phanquyen` (
  `Quyen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default' COMMENT 'Tên quyền',
  `QLSanPham` int(1) NOT NULL DEFAULT 0,
  `QLDanhMuc` int(1) NOT NULL DEFAULT 0,
  `QLNhanVien` int(1) NOT NULL DEFAULT 0,
  `QLKhachHang` int(1) NOT NULL DEFAULT 0,
  `QLNhaCungCap` int(1) NOT NULL DEFAULT 0,
  `QLDonHang` int(1) NOT NULL DEFAULT 0,
  `QLPhieuNhap` int(1) NOT NULL DEFAULT 0,
  `QLThongke` int(1) NOT NULL DEFAULT 0,
  `QLTaiKhoan` int(1) NOT NULL DEFAULT 0,
  `QLPhanQuyen` int(1) NOT NULL DEFAULT 0,
  `QLCuaHang` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phanquyen`
--

INSERT INTO `phanquyen` (`Quyen`, `QLSanPham`, `QLDanhMuc`, `QLNhanVien`, `QLKhachHang`, `QLNhaCungCap`, `QLDonHang`, `QLPhieuNhap`, `QLThongke`, `QLTaiKhoan`, `QLPhanQuyen`, `QLCuaHang`) VALUES
('Default', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
('Nhân viên bán hàng', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
('Nhân viên nhập hàng', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
('Quản lý', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
('Quản trị viên', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieunhap`
--

CREATE TABLE `phieunhap` (
  `idphieunhap` int(11) NOT NULL,
  `idnhacungcap` int(11) NOT NULL,
  `idnhanvien` int(11) NOT NULL,
  `ngaynhap` date NOT NULL,
  `tongtien` decimal(10,2) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phieunhap`
--

INSERT INTO `phieunhap` (`idphieunhap`, `idnhacungcap`, `idnhanvien`, `ngaynhap`, `tongtien`, `trangthai`) VALUES
(1, 1, 1, '2025-12-01', 99999999.99, 0),
(2, 1, 1, '2025-12-01', 99999999.99, 0),
(3, 1, 1, '2025-12-01', 99999999.99, 0),
(4, 1, 1, '2025-12-01', 185175.00, 1),
(5, 3, 1, '2025-12-01', 2052780.00, 1),
(6, 1, 1, '2025-12-01', 225000.00, 1),
(7, 2, 1, '2025-12-01', 503999.00, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach`
--

CREATE TABLE `sach` (
  `idsach` int(11) NOT NULL,
  `tensach` varchar(255) NOT NULL,
  `idtacgia` int(11) NOT NULL,
  `idnhaxuatban` int(11) NOT NULL,
  `idtheloai` int(11) DEFAULT NULL,
  `idctdanhmuc` int(11) NOT NULL,
  `gia` decimal(10,2) NOT NULL,
  `sltonkho` int(11) NOT NULL,
  `mota` text NOT NULL,
  `anhbia` varchar(255) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sach`
--

INSERT INTO `sach` (`idsach`, `tensach`, `idtacgia`, `idnhaxuatban`, `idtheloai`, `idctdanhmuc`, `gia`, `sltonkho`, `mota`, `anhbia`, `trangthai`) VALUES
(1, 'Cho tôi xin một vé đi tuổi thơ', 1, 1, 1, 1, 13103974.00, 104, 'Tác phẩm kinh điển của Nguyễn Nhật Ánh, tái hiện tuổi thơ sống động. \nNhững trò chơi, ký ức và tình bạn trong trẻo được khắc họa tinh tế. \nCâu chuyện mang đậm hơi thở miền quê Nam Bộ, đầy hoài niệm. \nSách đã chinh phục hàng triệu độc giả qua nhiều thế hệ. \nMột hành trình trở về tuổi thơ mà bất kỳ ai cũng nên đọc.', 'img/cho_toi_xin_mot_ve/bia.jpg', 1),
(2, 'Rừng Na Uy', 2, 2, 1, 1, 120000.00, 50, 'Tiểu thuyết nổi tiếng của Haruki Murakami, kể về tình yêu và cô đơn. \nNhân vật chính Toru Watanabe đối mặt với mất mát và ký ức tuổi trẻ. \nBối cảnh Nhật Bản thập niên 60 được tái hiện đầy cảm xúc. \nLối viết mơ màng, sâu lắng, đậm chất Murakami. \nTác phẩm đã được dịch ra nhiều thứ tiếng và yêu thích toàn cầu.', 'img/rung_na_uy/bia.jpg', 1),
(3, 'Án mạng trên chuyến tàu tốc hành', 3, 3, 3, 3, 95000.00, 75, 'Tác phẩm trinh thám kinh điển của Agatha Christie, không thể bỏ qua. \nThám tử Hercule Poirot giải mã vụ án trên chuyến tàu sang trọng. \nMỗi hành khách đều là nghi phạm, tạo nên những tình tiết gay cấn. \nCốt truyện được xây dựng chặt chẽ với cái kết bất ngờ. \nSách đã được chuyển thể thành phim và kịch nổi tiếng.', 'img/an_mang_tren_tau/bia.jpg', 1),
(4, 'Chí Phèo', 4, 4, 1, 1, 55000.00, 120, 'Kiệt tác văn học hiện thực của Nam Cao, khắc họa số phận bi thảm. \nChí Phèo, một người nông dân bị tha hóa bởi xã hội phong kiến. \nTình yêu với Thị Nở là tia sáng hiếm hoi nhưng cũng đầy bi kịch. \nTác phẩm phê phán sâu sắc sự bất công và tàn nhẫn của xã hội cũ. \nĐây là một trong những tác phẩm quan trọng của văn học Việt Nam.', 'img/chi_pheo/bia.jpg', 1),
(5, 'The Shining', 5, 5, 5, 5, 135000.00, 60, 'Tiểu thuyết kinh dị nổi tiếng của Stephen King, đầy ám ảnh. \nGia đình Torrance bị mắc kẹt trong khách sạn Overlook ma quái. \nNhững hiện tượng siêu nhiên và tâm lý bất ổn đẩy họ vào nguy hiểm. \nLối kể chuyện lôi cuốn, tạo cảm giác rùng rợn đến từng trang. \nSách đã được chuyển thể thành phim kinh điển của Stanley Kubrick.', 'img/the_shinning/bia.jpg', 1),
(6, 'Số đỏ', 6, 6, 1, 1, 85000.00, 90, 'Tác phẩm châm biếm đỉnh cao của Vũ Trọng Phụng, đầy hài hước. \nNhân vật Xuân Tóc Đỏ từ kẻ vô danh trở thành \"người hùng\" xã hội. \nTác phẩm phơi bày sự giả dối và lố lăng của tầng lớp thượng lưu. \nLối viết sắc sảo, giọng văn trào phúng khiến người đọc không thể rời mắt. \nĐây là một trong những tác phẩm tiêu biểu của văn học Việt Nam.', 'img/so_do/bia.jpg', 1),
(7, 'Cosmos', 7, 7, 7, 7, 150000.00, 40, 'Cuốn sách khoa học kinh điển của Carl Sagan, mở ra cánh cửa vũ trụ. \nTác phẩm giải thích các khái niệm phức tạp một cách dễ hiểu. \nTừ lịch sử thiên văn đến tương lai của loài người, sách đều đề cập. \nLối viết truyền cảm hứng, khiến người đọc say mê khám phá khoa học. \nSách đi kèm với series truyền hình nổi tiếng cùng tên.', 'img/comos/bia.jpg', 1),
(8, 'Dế mèn phiêu lưu ký', 8, 8, 9, 9, 65000.00, 198, 'Tác phẩm thiếu nhi nổi tiếng của Tô Hoài, gắn bó với nhiều thế hệ. \nChú dế mèn trải qua hành trình phiêu lưu đầy thú vị và ý nghĩa. \nNhững bài học về tình bạn, lòng dũng cảm được lồng ghép khéo léo. \nLối kể chuyện sinh động, đậm chất dân gian, thu hút trẻ em. \nSách là một phần ký ức tuổi thơ của hàng triệu độc giả Việt Nam.', 'img/de_men_phieu_luu_ky/bia.jpg', 1),
(9, 'Đắc nhân tâm', 9, 9, 10, 10, 110000.00, 147, 'Cuốn sách kỹ năng sống kinh điển của Dale Carnegie, thay đổi cuộc đời. \nTác phẩm hướng dẫn cách xây dựng mối quan hệ và thu phục lòng người. \nNhững lời khuyên thực tế giúp người đọc cải thiện kỹ năng giao tiếp. \nSách đã được dịch ra hàng chục ngôn ngữ, bán hàng triệu bản. \nĐây là cuốn sách gối đầu giường cho bất kỳ ai muốn thành công.', 'img/dac_nhan_tam/bia.jpg', 1),
(10, 'Góc sân và khoảng trời', 10, 10, 9, 9, 45000.00, 176, 'Tập thơ nổi tiếng của Trần Đăng Khoa, viết khi ông mới 8 tuổi. \nNhững vần thơ trong sáng, mộc mạc, tái hiện tuổi thơ làng quê. \nTác phẩm phản ánh thế giới hồn nhiên qua đôi mắt trẻ thơ. \nSự tài năng của \"thần đồng thơ ca\" khiến độc giả kinh ngạc. \nĐây là một trong những tập thơ thiếu nhi xuất sắc của Việt Nam.', 'img/goc_san_va_khoang_troi/bia.jpg', 1),
(11, 'Lược sử thời gian', 11, 1, 7, 7, 160000.00, 30, 'Cuốn sách khoa học nổi tiếng của Stephen Hawking, giải thích về vũ trụ. \nTác phẩm đưa người đọc khám phá thời gian, không gian và lỗ đen. \nLối viết dễ hiểu, dù đề cập đến những khái niệm vật lý phức tạp. \nSách đã truyền cảm hứng cho hàng triệu người yêu khoa học. \nĐây là một trong những tác phẩm bán chạy nhất mọi thời đại.', 'img/luoc_su_thoi_gian/bia.jpg', 1),
(12, 'Nhà giả kim', 2, 2, 1, 1, 90000.00, 80, 'Tiểu thuyết nổi tiếng của Paulo Coelho, kể về hành trình tìm ước mơ. \nNhân vật Santiago đi tìm \"kho báu\" và khám phá ý nghĩa cuộc sống. \nTác phẩm truyền tải thông điệp sâu sắc về việc lắng nghe trái tim. \nLối viết giàu hình ảnh, mang tính triết lý và cảm hứng. \nSách đã bán hàng triệu bản và được yêu thích trên toàn thế giới.', 'img/nha_gia_kim/bia.jpg', 1),
(13, 'Đi tìm lẽ sống', 9, 9, 6, 6, 80000.00, 70, 'Cuốn sách truyền cảm hứng của Viktor Frankl, nói về ý nghĩa cuộc đời. \nTác giả kể lại trải nghiệm sống sót trong trại tập trung Đức Quốc xã. \nSách nhấn mạnh tầm quan trọng của việc tìm kiếm mục đích sống. \nLối viết chân thực, sâu sắc, chạm đến trái tim người đọc. \nĐây là một tác phẩm giúp người đọc vượt qua khó khăn trong cuộc sống.', 'img/di_tim_le_song/bia.jpg', 1),
(14, 'Bố già', 3, 3, 1, 1, 110000.00, 90, 'Tiểu thuyết kinh điển của Mario Puzo, kể về thế giới mafia Ý. \nNhân vật Don Vito Corleone là biểu tượng của quyền lực và lòng trung thành. \nTác phẩm khắc họa sâu sắc tình thân và sự tàn nhẫn của thế giới ngầm. \nLối kể chuyện hấp dẫn, đầy kịch tính và bất ngờ. \nSách đã được chuyển thể thành bộ phim nổi tiếng đoạt giải Oscar.', 'img/bo_gia/bia.jpg', 1),
(15, 'Cô gái đến từ hôm qua', 1, 1, 1, 1, 70000.00, 100, 'Tác phẩm nổi tiếng của Nguyễn Nhật Ánh, đong đầy cảm xúc tuổi trẻ. \nCâu chuyện kể về mối tình đầu trong sáng giữa hai thế hệ. \nBối cảnh Sài Gòn xưa được tái hiện sống động qua từng trang sách. \nLối viết nhẹ nhàng, sâu lắng, khiến người đọc không thể rời mắt. \nSách đã được chuyển thể thành phim, được khán giả yêu thích.', 'img/co_gai_den_tu_hom_qua/bia.jpg', 1),
(16, 'Mắt biếc', 1, 1, 1, 1, 85000.00, 110, 'Tác phẩm đầy cảm xúc của Nguyễn Nhật Ánh, kể về tình yêu đơn phương. \nNhân vật Ngạn yêu Hà Lan qua đôi mắt biếc đầy mê hoặc. \nCâu chuyện mang đến những rung động đầu đời và nỗi buồn sâu lắng. \nLối viết trong trẻo, giàu hình ảnh, chạm đến trái tim người đọc. \nSách đã được chuyển thể thành phim, gây sốt tại Việt Nam.', 'img/mat_biec/bia.jpg', 1),
(17, 'Kafka bên bờ biển', 2, 2, 1, 1, 130000.00, 60, 'Tiểu thuyết kỳ ảo của Haruki Murakami, đầy bí ẩn và sâu sắc. \nHai câu chuyện song song về một thiếu niên bỏ nhà đi và một ông lão lạ. \nTác phẩm đan xen giữa hiện thực và huyền bí, khiến người đọc tò mò. \nLối viết đặc trưng của Murakami, mơ màng và đầy triết lý. \nSách là một trong những kiệt tác của văn học Nhật Bản hiện đại.', 'img/kafka_ben_bo_bien/bia.jpg', 1),
(18, '1Q84', 2, 2, 1, 1, 140000.00, 50, 'Tiểu thuyết đồ sộ của Haruki Murakami, gồm ba phần hấp dẫn. \nCâu chuyện diễn ra trong một thế giới song song đầy bí ẩn. \nNhân vật chính Aomame và Tengo đối mặt với những sự kiện kỳ lạ. \nLối viết phức tạp, đan xen giữa hiện thực và giả tưởng. \nSách là một trong những tác phẩm quan trọng của Murakami.', 'img/1q84/bia.jpg', 1),
(19, 'Những người khốn khổ', 3, 3, 1, 1, 120000.00, 70, 'Kiệt tác văn học của Victor Hugo, kể về số phận những con người bất hạnh. \nNhân vật Jean Valjean từ một tù nhân trở thành người hùng cứu giúp người khác. \nTác phẩm khắc họa sâu sắc xã hội Pháp thế kỷ 19 đầy bất công. \nLối viết giàu cảm xúc, mang tính sử thi và nhân văn. \nSách đã được chuyển thể thành phim và nhạc kịch nổi tiếng.', 'img/nhung_nguoi_khon_kho/bia.jpg', 1),
(20, 'Chiến tranh và hòa bình', 3, 3, 1, 1, 150000.00, 40, 'Tiểu thuyết sử thi của Leo Tolstoy, tái hiện nước Nga thời Napoleon. \nTác phẩm khắc họa số phận của nhiều nhân vật qua chiến tranh và hòa bình. \nCâu chuyện đan xen giữa lịch sử, tình yêu và những câu hỏi triết học. \nLối viết đồ sộ, chi tiết, nhưng đầy cảm xúc và cuốn hút. \nĐây là một trong những kiệt tác vĩ đại nhất của văn học thế giới.', 'img/chien_tranh_va_hoa_binh/bia.jpg', 1),
(21, 'Tiếng chim hót trong bụi mận gai', 3, 3, 1, 1, 95000.00, 80, 'Tiểu thuyết tình cảm nổi tiếng của Colleen McCullough, đầy cảm xúc. \nCâu chuyện kể về tình yêu cấm kỵ giữa Meggie và cha Ralph de Bricassart. \nBối cảnh nước Úc rộng lớn được tái hiện sống động và thơ mộng. \nTác phẩm mang đến những rung động sâu sắc và nỗi buồn day dứt. \nSách đã được chuyển thể thành phim truyền hình nổi tiếng.', 'img/tieng_chim_hot_trong_bui_man_gai/bia.jpg', 1),
(22, 'Ông già và biển cả', 3, 3, 1, 1, 60000.00, 100, 'Tác phẩm kinh điển của Ernest Hemingway, kể về ý chí con người. \nÔng lão Santiago chiến đấu với con cá lớn trong cuộc chiến sinh tồn. \nTác phẩm đề cao tinh thần kiên cường và lòng dũng cảm. \nLối viết ngắn gọn, mạnh mẽ, đậm chất Hemingway. \nSách đã giúp Hemingway nhận giải Nobel Văn học.', 'img/ong_gia_va_bien_ca/bia.jpg', 1),
(23, 'Cuốn theo chiều gió', 3, 3, 1, 1, 125000.00, 60, 'Tiểu thuyết tình cảm nổi tiếng của Margaret Mitchell, đầy kịch tính. \nNhân vật Scarlett O’Hara đối mặt với tình yêu và chiến tranh. \nBối cảnh nước Mỹ thời nội chiến được tái hiện chân thực. \nTác phẩm khắc họa mạnh mẽ cá tính và sự kiên cường của phụ nữ. \nSách đã được chuyển thể thành phim kinh điển, đoạt nhiều giải Oscar.', 'img/cuon_theo_chieu_gio/bia.jpg', 1),
(24, 'Harry Potter và Hòn đá phù thủy', 3, 3, 2, 2, 110000.00, 148, 'Tác phẩm đầu tiên trong series Harry Potter của J.K. Rowling. \nCậu bé Harry Potter khám phá thế giới phép thuật tại trường Hogwarts. \nCuốn sách mở ra hành trình phiêu lưu đầy ma thuật và tình bạn. \nLối viết hấp dẫn, phù hợp với cả trẻ em và người lớn. \nSách đã trở thành hiện tượng văn học toàn cầu.', 'img/harrypotter_va_hon_da_phu_thuy/bia.jpg', 1),
(25, 'Harry Potter và Phòng chứa bí mật', 3, 3, 2, 2, 110000.00, 140, 'Tác phẩm thứ hai trong series Harry Potter, đầy bí ẩn. \nHarry đối mặt với những bí mật đen tối tại trường Hogwarts. \nCâu chuyện về con rắn Basilisk và Phòng chứa bí mật gây hồi hộp. \nTình bạn giữa Harry, Ron và Hermione tiếp tục được khắc họa sâu sắc. \nSách tiếp tục chinh phục độc giả trên toàn thế giới.', 'img/harrypoter_va_phong_chua_bi_mat/bia.jpg', 1),
(26, 'Harry Potter và tên tù nhân ngục Azkaban', 3, 3, 2, 2, 110000.00, 130, 'Tác phẩm thứ ba trong series Harry Potter, đầy cảm xúc. \nHarry đối mặt với Sirius Black, một kẻ đào tẩu từ ngục Azkaban. \nNhững bí mật về gia đình Harry dần được hé lộ qua câu chuyện. \nTác phẩm mang đến nhiều tình tiết bất ngờ và cảm động. \nSách đã được chuyển thể thành phim, được khán giả yêu thích.', 'img/harrypotter_va_dia_nguc_buoi/bia.jpg', 1),
(27, 'Harry Potter và chiếc cốc lửa', 3, 3, 2, 2, 110000.00, 120, 'Tác phẩm thứ tư trong series Harry Potter, đầy kịch tính. \nHarry tham gia giải đấu Tam Pháp Thuật nguy hiểm tại Hogwarts. \nSự trở lại của Chúa tể Voldemort đánh dấu bước ngoặt đen tối. \nCâu chuyện gay cấn với những thử thách phép thuật đầy hấp dẫn. \nSách đã giành được nhiều giải thưởng văn học danh giá.', 'img/harrypotter_va_cbiec_coc_lua/bia.jpg', 1),
(28, 'Harry Potter và Hội Phượng Hoàng', 3, 3, 2, 2, 110000.00, 110, 'Tác phẩm thứ năm trong series Harry Potter, đầy cảm xúc. \nHarry thành lập Hội Phượng Hoàng để chống lại Voldemort. \nNhững mất mát lớn khiến câu chuyện trở nên sâu sắc hơn. \nTác phẩm khắc họa sự trưởng thành của Harry và bạn bè. \nSách tiếp tục là một phần không thể thiếu của series huyền thoại.', 'img/hrpt_va_hoi_phuong_hoang/bia.jpg', 1),
(29, 'Harry Potter và Hoàng tử lai', 3, 3, 2, 2, 110000.00, 100, 'Tác phẩm thứ sáu trong series Harry Potter, đầy bất ngờ. \nHarry khám phá bí mật về quá khứ của Voldemort qua ký ức. \nNhân vật \"Hoàng tử lai\" đóng vai trò quan trọng trong cốt truyện. \nTác phẩm mang đến nhiều tình tiết căng thẳng và cảm động. \nSách chuẩn bị cho cái kết hoành tráng của series.', 'img/hrpt_va_hoang_tu_lai/bia.jpg', 1),
(30, 'Harry Potter và Bảo bối Tử thần', 3, 3, 2, 2, 110000.00, 90, 'Tác phẩm cuối cùng trong series Harry Potter, đầy kịch tính. \nHarry, Ron và Hermione săn lùng Trường Sinh Linh Giá của Voldemort. \nCuộc chiến cuối cùng tại Hogwarts quyết định số phận thế giới phép thuật. \nTác phẩm mang đến cái kết hoàn hảo cho một hành trình dài. \nSách đã trở thành biểu tượng văn học của thế kỷ 21.', 'img/hrpt_va_bao_boi_tu_than/bia.jpg', 1),
(31, 'Chạng vạng', 3, 3, 4, 4, 90000.00, 80, 'Tác phẩm đầu tiên trong series Chạng vạng của Stephenie Meyer. \nBella Swan yêu Edward Cullen, một chàng trai ma cà rồng bí ẩn. \nTình yêu giữa con người và ma cà rồng đầy nguy hiểm và cám dỗ. \nLối viết lãng mạn, cuốn hút, đặc biệt dành cho tuổi teen. \nSách đã được chuyển thể thành phim, gây sốt trên toàn thế giới.', 'img/chang_vang/bia.jpg', 1),
(32, 'Trăng non', 3, 3, 4, 4, 90000.00, 70, 'Tác phẩm thứ hai trong series Chạng vạng, đầy cảm xúc. \nBella rơi vào tuyệt vọng khi Edward rời bỏ cô để bảo vệ cô. \nMối quan hệ với Jacob Black, một người sói, làm phức tạp thêm câu chuyện. \nTác phẩm tiếp tục khám phá tình yêu và sự hy sinh. \nSách là một phần không thể thiếu của series nổi tiếng.', 'img/trang_non/bia.jpg', 1),
(33, 'Nhật thực', 3, 3, 4, 4, 90000.00, 60, 'Tác phẩm thứ ba trong series Chạng vạng, đầy kịch tính. \nBella phải lựa chọn giữa Edward và Jacob trong bối cảnh nguy hiểm. \nMột đội quân ma cà rồng mới sinh gây ra mối đe dọa lớn. \nTác phẩm mang đến những tình tiết gay cấn và cảm xúc mạnh mẽ. \nSách tiếp tục chinh phục người hâm mộ của series.', 'img/nhat_thuc/bia.jpg', 1),
(34, 'Hừng đông', 3, 3, 4, 4, 90000.00, 50, 'Tác phẩm cuối cùng trong series Chạng vạng, đầy bất ngờ. \nBella và Edward đối mặt với những thử thách sau khi kết hôn. \nSự ra đời của Renesmee kéo theo cuộc chiến với gia tộc Volturi. \nTác phẩm mang đến cái kết trọn vẹn cho câu chuyện tình yêu. \nSách đã được chuyển thể thành phim, khép lại series đình đám.', 'img/hung_dong/bia.jpg', 1),
(35, 'Đồi gió hú', 3, 3, 1, 1, 85000.00, 90, 'Tiểu thuyết kinh điển của Emily Brontë, đầy ám ảnh. \nTình yêu mãnh liệt giữa Heathcliff và Catherine Earnshaw gây ra bi kịch. \nBối cảnh vùng đồng hoang Yorkshire được tái hiện sống động. \nTác phẩm khám phá sự đam mê, thù hận và trả thù. \nSách là một trong những kiệt tác của văn học Anh.', 'img/doi_gio_hu/bia.jpg', 1),
(36, 'Jane Eyre', 3, 3, 1, 1, 80000.00, 80, 'Tiểu thuyết nổi tiếng của Charlotte Brontë, kể về hành trình trưởng thành. \nJane Eyre, một cô gái mồ côi, đối mặt với nhiều khó khăn trong cuộc sống. \nTình yêu với ông Rochester đầy thử thách và cảm xúc mãnh liệt. \nTác phẩm đề cao sự tự lập và lòng kiên cường của phụ nữ. \nSách là một trong những tác phẩm kinh điển của văn học Anh.', 'img/jane_eyre/bia.jpg', 1),
(37, 'Bắt trẻ đồng xanh', 3, 3, 1, 1, 70000.00, 100, 'Tác phẩm nổi tiếng của J.D. Salinger, nói về tuổi trẻ nổi loạn. \nNhân vật Holden Caulfield lang thang ở New York sau khi bị đuổi học. \nTác phẩm khắc họa tâm lý phức tạp của một thiếu niên bất mãn. \nLối viết chân thực, giọng văn độc đáo, chạm đến trái tim người đọc. \nSách đã trở thành biểu tượng của văn học Mỹ thế kỷ 20.', 'img/bat_tre_dong_xanh/bia.jpg', 1),
(38, 'Thép đã tôi thế đấy', 3, 3, 1, 1, 75000.00, 90, 'Tiểu thuyết kinh điển của Nikolai Ostrovsky, truyền cảm hứng mạnh mẽ. \nNhân vật Pavel Korchagin vượt qua khó khăn để cống hiến cho lý tưởng. \nTác phẩm ca ngợi tinh thần thép của con người trong thời chiến. \nLối viết giàu cảm xúc, mang tính giáo dục sâu sắc. \nSách đã trở thành biểu tượng của văn học Xô Viết.', 'img/thep_da_toi_the_day/bia.jpg', 1),
(39, 'Những kẻ si tinh', 3, 3, 1, 1, 95000.00, 70, 'Tiểu thuyết nổi tiếng của Victor Hugo, khắc họa xã hội Pháp thế kỷ 19. \nNhân vật Jean Valjean từ tội phạm trở thành người hùng cứu giúp người khác. \nTác phẩm đề cao lòng nhân ái, sự hy sinh và công lý. \nLối viết sử thi, giàu cảm xúc, chạm đến trái tim người đọc. \nSách đã được chuyển thể thành phim và nhạc kịch nổi tiếng.', 'img/nhung_ke_si_tinh/bia.jpg', 1),
(40, 'Thằng gù nhà thờ Đức Bà', 3, 3, 1, 1, 85000.00, 80, 'Tiểu thuyết kinh điển của Victor Hugo, kể về tình yêu và bi kịch. \nQuasimodo, một người gù xấu xí, yêu say đắm nàng Esmeralda. \nBối cảnh Paris thế kỷ 15 được tái hiện sống động và đầy cảm xúc. \nTác phẩm đề cao vẻ đẹp tâm hồn vượt qua định kiến xã hội. \nSách đã được chuyển thể thành phim và nhạc kịch nổi tiếng.', 'img/thang_gu_nha_tho_duc_ba/bia.jpg', 1),
(41, 'Anna Karenina', 3, 3, 1, 1, 130000.00, 50, 'Tiểu thuyết nổi tiếng của Leo Tolstoy, kể về tình yêu và bi kịch. \nAnna Karenina rơi vào mối tình cấm kỵ, dẫn đến những hậu quả đau lòng. \nTác phẩm khắc họa sâu sắc xã hội Nga thế kỷ 19 và tâm lý con người. \nLối viết tinh tế, giàu cảm xúc, khiến người đọc không thể rời mắt. \nSách là một trong những kiệt tác của văn học thế giới.', 'img/anna/bia.jpg', 1),
(42, 'Lolita', 3, 3, 1, 1, 90000.00, 60, 'Tiểu thuyết gây tranh cãi của Vladimir Nabokov, kể về một tình yêu ám ảnh. \nNhân vật Humbert Humbert bị cuốn vào mối quan hệ với cô bé Lolita. \nTác phẩm khám phá tâm lý phức tạp và những ranh giới đạo đức. \nLối viết tinh tế, giàu hình ảnh, nhưng đầy tranh cãi. \nSách đã được chuyển thể thành phim và gây nhiều tranh luận.', 'img/lolita/bia.jpg', 1),
(43, '1984', 3, 3, 2, 2, 80000.00, 100, 'Tiểu thuyết dystopian nổi tiếng của George Orwell, đầy ám ảnh. \nTác phẩm mô tả một xã hội toàn trị, nơi tự do bị bóp nghẹt hoàn toàn. \nNhân vật Winston Smith đấu tranh để giữ gìn nhân tính của mình. \nLối viết sắc sảo, mang tính cảnh báo về quyền lực và kiểm soát. \nSách là một trong những tác phẩm quan trọng của văn học thế kỷ 20.', 'img/1984/bia.jpg', 1),
(44, 'Trại súc vật', 3, 3, 2, 2, 70000.00, 110, 'Tác phẩm châm biếm nổi tiếng của George Orwell, đầy ý nghĩa. \nNhững con vật trong trang trại nổi dậy nhưng rơi vào chế độ độc tài mới. \nTác phẩm là một ẩn dụ sâu sắc về cách mạng và sự tha hóa quyền lực. \nLối viết đơn giản nhưng sắc sảo, khiến người đọc suy ngẫm. \nSách đã trở thành một biểu tượng của văn học chính trị.', 'img/trai_suc_vat/bia.jpg', 1),
(45, 'Fahrenheit 451', 3, 3, 2, 2, 85000.00, 90, 'Tiểu thuyết dystopian của Ray Bradbury, nói về một thế giới không sách. \nNhân vật Guy Montag là lính cứu hỏa chuyên đốt sách nhưng dần thức tỉnh. \nTác phẩm cảnh báo về sự kiểm soát tri thức và mất đi tự do tư duy. \nLối viết giàu hình ảnh, đầy cảm xúc, khiến người đọc suy ngẫm. \nSách là một trong những tác phẩm kinh điển của văn học khoa học viễn tưởng.', 'img/451/bia.jpg', 1),
(46, 'Chúa tể những chiếc nhẫn', 3, 3, 2, 2, 140000.00, 60, 'Tác phẩm kinh điển của J.R.R. Tolkien, mở ra thế giới giả tưởng hùng vĩ. \nFrodo và các bạn đồng hành đối mặt với hiểm nguy để tiêu diệt chiếc nhẫn. \nTác phẩm khắc họa sâu sắc tình bạn, lòng dũng cảm và sự hy sinh. \nLối viết chi tiết, giàu hình ảnh, tạo nên một thế giới sống động. \nSách đã được chuyển thể thành phim bom tấn, đoạt nhiều giải Oscar.', 'img/chua_te_cua_nhung_chiec_nha/bia.jpg', 1),
(47, 'Hobbit', 3, 3, 2, 2, 110000.00, 80, 'Tiền truyện của Chúa tể những chiếc nhẫn, do J.R.R. Tolkien sáng tác. \nBilbo Baggins tham gia hành trình phiêu lưu đầy bất ngờ với nhóm người lùn. \nTác phẩm mang đến những câu chuyện vui nhộn nhưng cũng đầy ý nghĩa. \nLối viết nhẹ nhàng, phù hợp với cả trẻ em và người lớn. \nSách đã được chuyển thể thành phim, được khán giả yêu thích.', 'img/hobbit/bia.jpg', 1),
(48, 'Người đua diều', 3, 3, 1, 1, 95000.00, 70, 'Tiểu thuyết nổi tiếng của Khaled Hosseini, đầy cảm xúc. \nCâu chuyện kể về tình bạn và sự chuộc lỗi của Amir và Hassan. \nBối cảnh Afghanistan đầy biến động được tái hiện chân thực. \nTác phẩm khám phá tình thân, lòng trung thành và sự tha thứ. \nSách đã chinh phục hàng triệu độc giả trên toàn thế giới.', 'img/nguoi_dua_dieu/bia.jpg', 1),
(49, 'Ngàn mặt trời rực rỡ', 3, 3, 1, 1, 100000.00, 60, 'Tác phẩm thứ hai của Khaled Hosseini, kể về số phận phụ nữ Afghanistan. \nMariam và Laila đối mặt với những bất công và đau khổ trong chiến tranh. \nTác phẩm đề cao sức mạnh của tình yêu và sự hy sinh của phụ nữ. \nLối viết cảm động, sâu sắc, khiến người đọc rơi nước mắt. \nSách là một trong những tác phẩm nổi bật của Hosseini.', 'img/ngan_mat_troi_ruc_ro/bia.jpg', 1),
(50, 'Và rồi núi vọng', 3, 3, 1, 1, 105000.00, 50, 'Tác phẩm thứ ba của Khaled Hosseini, kể về tình thân và chia ly. \nHai anh em Abdullah và Pari bị chia cắt, dẫn đến những câu chuyện cảm động. \nBối cảnh Afghanistan qua nhiều thập kỷ được tái hiện đầy chân thực. \nTác phẩm khám phá sự mất mát và tình yêu gia đình. \nSách tiếp tục khẳng định tài năng của Hosseini trong văn học.', 'img/va_roi_nui_vong/bia.jpg', 1),
(51, 'Điều kỳ diệu', 3, 3, 1, 1, 90000.00, 80, 'Tiểu thuyết nổi tiếng của R.J. Palacio, truyền cảm hứng mạnh mẽ. \nAuggie Pullman, một cậu bé bị dị tật bẩm sinh, đối mặt với định kiến. \nTác phẩm đề cao lòng tử tế và sự chấp nhận trong xã hội. \nLối viết nhẹ nhàng, cảm động, phù hợp với mọi lứa tuổi. \nSách đã được chuyển thể thành phim, được khán giả yêu thích.', 'img/dieu_ky_dieu/bia.jpg', 1),
(59, 'đ', 1, 3, NULL, 2, 0.00, 0, 'sdsd', 'img/ANH_SACH_MOI/bia.jpg', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tacgia`
--

CREATE TABLE `tacgia` (
  `idtacgia` int(11) NOT NULL,
  `tentacgia` varchar(255) NOT NULL,
  `tieusu` text NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tacgia`
--

INSERT INTO `tacgia` (`idtacgia`, `tentacgia`, `tieusu`, `trangthai`) VALUES
(1, 'Nguyễn Nhật Ánh', 'Nguyễn Nhật Ánh là một trong những nhà văn nổi tiếng nhất Việt Nam, được yêu mến qua các tác phẩm dành cho tuổi trẻ. Ông sinh ngày 7 tháng 5 năm 1955 tại Quảng Nam, từng là giáo viên trước khi chuyển sang viết văn chuyên nghiệp. Các tác phẩm của ông thường mang hơi thở tuổi thơ, đầy hoài niệm và cảm xúc trong sáng. Ông bắt đầu sự nghiệp viết lách từ rất sớm, với nhiều truyện ngắn được đăng trên các báo. Nguyễn Nhật Ánh nổi bật với lối kể chuyện nhẹ nhàng, gần gũi, đậm chất Nam Bộ. Ông đã xuất bản hơn 100 tác phẩm, nhiều trong số đó được chuyển thể thành phim. Tác phẩm tiêu biểu như \"Cho tôi xin một vé đi tuổi thơ\" đã chinh phục hàng triệu độc giả. Ông từng nhận nhiều giải thưởng văn học uy tín trong nước. Ngoài viết văn, ông còn sáng tác thơ và tham gia báo chí. Hiện nay, ông vẫn tiếp tục sáng tác và là thần tượng của nhiều thế hệ bạn đọc.', 1),
(2, 'Haruki Murakami', 'Haruki Murakami là một nhà văn người Nhật Bản nổi tiếng thế giới với phong cách viết độc đáo, pha trộn giữa hiện thực và kỳ ảo. Ông sinh ngày 12 tháng 1 năm 1949 tại Kyoto, từng học ngành kịch nghệ tại Đại học Waseda. Trước khi trở thành nhà văn, ông từng quản lý một quán bar jazz ở Tokyo. Tác phẩm đầu tay \"Lắng nghe gió hát\" ra mắt năm 1979 đã đánh dấu bước ngoặt trong sự nghiệp của ông. Ông thường viết về sự cô đơn, mất mát và những hành trình nội tâm sâu sắc. Các tiểu thuyết như \"Rừng Na Uy\" hay \"Kafka bên bờ biển\" được dịch ra nhiều thứ tiếng và yêu thích toàn cầu. Murakami cũng là một người yêu chạy bộ, từng viết sách về trải nghiệm marathon của mình. Ông được xem là ứng cử viên tiềm năng cho giải Nobel Văn học nhiều năm. Phong cách của ông ảnh hưởng sâu sắc đến văn học hiện đại Nhật Bản. Hiện ông sống kín tiếng và tiếp tục sáng tác đều đặn.', 1),
(3, 'Agatha Christie', 'Agatha Christie, được mệnh danh là \"Nữ hoàng trinh thám\", là nhà văn Anh nổi tiếng nhất trong thể loại tiểu thuyết bí ẩn. Bà sinh ngày 15 tháng 9 năm 1890 tại Torquay, Anh, trong một gia đình trung lưu. Bà bắt đầu viết truyện từ thời Thế chiến I, khi làm việc trong bệnh viện và học về chất độc. Tác phẩm đầu tiên \"Vụ án tại Styles\" giới thiệu nhân vật Hercule Poirot nổi tiếng. Bà đã sáng tạo hơn 80 tiểu thuyết và kịch bản, bán được hàng tỷ bản trên toàn thế giới. Các tác phẩm như \"Án mạng trên chuyến tàu tốc hành\" là kinh điển của văn học trinh thám. Ngoài Poirot, bà còn tạo ra nhân vật Miss Marple, một bà già thông minh phá án. Cuộc đời bà từng gây chú ý với vụ mất tích bí ẩn năm 1926. Bà qua đời năm 1976, để lại di sản văn học đồ sộ. Tác phẩm của bà vẫn được dựng phim và tái bản liên tục đến nay.', 1),
(4, 'Nam Cao', 'Nam Cao là một trong những nhà văn hiện thực xuất sắc nhất của văn học Việt Nam thế kỷ 20. Ông sinh ngày 29 tháng 10 năm 1915 tại Hà Nam, trong một gia đình nông dân nghèo. Trước khi viết văn, ông từng làm nhiều nghề như dạy học, viết báo để mưu sinh. Các tác phẩm của ông phản ánh sâu sắc hiện thực xã hội Việt Nam trước Cách mạng tháng Tám. \"Chí Phèo\" là kiệt tác, khắc họa số phận bi thảm của người nông dân bị tha hóa. Ông có lối viết chân thực, sắc sảo, đậm chất nhân văn và phê phán xã hội. Ngoài truyện ngắn, ông còn viết tiểu thuyết như \"Đời mưa gió\" đầy cảm xúc. Nam Cao hy sinh năm 1951 khi làm nhiệm vụ trong kháng chiến chống Pháp. Di sản của ông vẫn được giảng dạy và nghiên cứu rộng rãi. Ông được truy tặng Giải thưởng Hồ Chí Minh về văn học nghệ thuật.', 1),
(5, 'Stephen King', 'Stephen King là nhà văn kinh dị nổi tiếng nhất thế giới, được mệnh danh là \"Ông hoàng kinh dị\". Ông sinh ngày 21 tháng 9 năm 1947 tại Portland, Maine, Hoa Kỳ. Từ nhỏ, ông đã mê đọc truyện kinh dị và bắt đầu sáng tác khi còn đi học. Tác phẩm đầu tiên \"Carrie\" ra mắt năm 1974 đã đưa tên tuổi ông lên tầm cao mới. Ông nổi tiếng với khả năng xây dựng câu chuyện rùng rợn, ám ảnh nhưng đầy chiều sâu tâm lý. Các tiểu thuyết như \"The Shining\" hay \"It\" đã được chuyển thể thành phim bom tấn. King đã viết hơn 60 tiểu thuyết và hàng trăm truyện ngắn trong sự nghiệp. Ông từng vượt qua nghiện rượu và ma túy để tiếp tục sáng tác. Ngoài kinh dị, ông còn thử sức ở các thể loại khác như khoa học viễn tưởng. Hiện nay, ông vẫn là biểu tượng của văn học đại chúng Mỹ.', 1),
(6, 'Vũ Trọng Phụng', 'Vũ Trọng Phụng là nhà văn Việt Nam xuất sắc với phong cách châm biếm sâu cay và hiện thực. Ông sinh ngày 20 tháng 10 năm 1912 tại Hà Nội, trong một gia đình nghèo khó. Từ nhỏ, ông đã phải nghỉ học để đi làm kiếm sống, từng làm thư ký và viết báo. Tác phẩm \"Số đỏ\" là kiệt tác, phơi bày sự giả dối của xã hội Việt Nam thời thuộc địa. Ông có lối viết sắc bén, hài hước nhưng đầy tính nhân văn và phê phán. Các tiểu thuyết như \"Giông tố\" hay \"Làm đĩ\" cũng gây tiếng vang lớn. Ông mắc bệnh lao phổi và qua đời sớm vào năm 1939, khi mới 27 tuổi. Dù sống ngắn ngủi, ông để lại dấu ấn sâu đậm trong văn học Việt Nam. Tác phẩm của ông được tái bản và nghiên cứu rộng rãi. Ông được xem là \"ông vua phóng sự\" của văn đàn Việt.', 1),
(7, 'Carl Sagan', 'Carl Sagan là nhà khoa học, nhà văn khoa học viễn tưởng nổi tiếng người Mỹ. Ông sinh ngày 9 tháng 11 năm 1934 tại New York, đam mê thiên văn từ nhỏ. Ông từng là giáo sư tại Đại học Cornell và góp phần lớn vào nghiên cứu không gian. Tác phẩm \"Cosmos\" của ông là cuốn sách khoa học bán chạy nhất mọi thời đại. Ông có tài truyền tải kiến thức phức tạp một cách dễ hiểu và cuốn hút. Ngoài viết sách, ông còn dẫn chương trình truyền hình \"Cosmos\", truyền cảm hứng cho hàng triệu người. Sagan tham gia các dự án của NASA như chương trình Voyager. Ông qua đời năm 1996 vì bệnh viêm phổi, để lại di sản khoa học đồ sộ. Ông từng nhận giải Pulitzer cho tác phẩm phi hư cấu. Sagan là biểu tượng của sự kết hợp giữa khoa học và văn học.', 1),
(8, 'Tô Hoài', 'Tô Hoài là nhà văn Việt Nam nổi tiếng với các tác phẩm thiếu nhi và văn học hiện thực. Ông sinh ngày 27 tháng 9 năm 1920 tại Hà Nội, trong một gia đình tiểu tư sản. Từ nhỏ, ông đã thích viết lách và quan sát cuộc sống xung quanh. \"Dế mèn phiêu lưu ký\" là tác phẩm kinh điển, gắn liền với tuổi thơ nhiều thế hệ. Ông có lối viết sinh động, giàu hình ảnh và đậm chất dân gian. Ngoài thiếu nhi, ông còn viết về đời sống Hà Nội qua \"Chuyện cũ Hà Nội\". Tô Hoài tham gia cách mạng và từng làm báo trong kháng chiến chống Pháp. Ông được trao Giải thưởng Hồ Chí Minh về văn học nghệ thuật. Ông qua đời năm 2014, để lại hơn 200 tác phẩm giá trị. Tên tuổi ông mãi là biểu tượng của văn học Việt Nam.', 1),
(9, 'Dale Carnegie', 'Dale Carnegie là tác giả người Mỹ nổi tiếng với các sách kỹ năng sống và giao tiếp. Ông sinh ngày 24 tháng 11 năm 1888 tại Missouri, trong một gia đình nông dân nghèo. Ông từng làm nhiều nghề trước khi trở thành diễn giả và nhà văn. Tác phẩm \"Đắc nhân tâm\" ra đời năm 1936, trở thành sách gối đầu giường của hàng triệu người. Ông có tài phân tích tâm lý và đưa ra lời khuyên thực tế, dễ áp dụng. Carnegie sáng lập các khóa học về kỹ năng giao tiếp, nổi tiếng toàn cầu. Ông tin rằng thành công đến từ cách đối nhân xử thế. Ngoài viết sách, ông còn là nhà đào tạo có tầm ảnh hưởng lớn. Ông qua đời năm 1955, nhưng triết lý của ông vẫn sống mãi. Tác phẩm của ông được dịch ra hàng chục ngôn ngữ trên thế giới.', 1),
(10, 'Trần Đăng Khoa', 'Trần Đăng Khoa là nhà thơ, nhà văn Việt Nam nổi tiếng từ khi còn rất nhỏ. Ông sinh ngày 24 tháng 4 năm 1958 tại Hải Dương, được xem là thần đồng thơ ca. Tập thơ \"Góc sân và khoảng trời\" ra đời khi ông mới 8 tuổi, gây kinh ngạc dư luận. Ông có tài làm thơ mộc mạc, trong sáng, đậm chất làng quê Việt. Ngoài thơ, ông còn viết văn xuôi và tiểu luận sắc sảo, giàu cảm xúc. Trần Đăng Khoa từng là lính trong thời chiến và sau đó làm báo chí. Ông có lối viết gần gũi, phản ánh chân thực cuộc sống và con người Việt Nam. Ông được trao nhiều giải thưởng văn học danh giá trong nước. Hiện nay, ông vẫn sáng tác và tham gia các hoạt động văn hóa. Tên tuổi ông là niềm tự hào của văn học Việt Nam hiện đại.', 1),
(11, 'Stephen Hawking', 'Stephen Hawking là một trong những nhà vật lý lý thuyết và vũ trụ học vĩ đại nhất của thế kỷ 20 và 21. Ông sinh ngày 8 tháng 1 năm 1942 tại Oxford, Anh, trong thời kỳ Thế chiến II. Hawking nổi tiếng với công trình nghiên cứu về lỗ đen và thuyết tương đối, đặc biệt là lý thuyết bức xạ Hawking. Ông tốt nghiệp Đại học Oxford và lấy bằng tiến sĩ tại Cambridge, nơi ông bắt đầu sự nghiệp khoa học xuất sắc. Dù bị chẩn đoán mắc bệnh xơ cứng teo cơ (ALS) năm 21 tuổi và chỉ được tiên lượng sống thêm vài năm, ông đã vượt qua nghịch cảnh để sống và sáng tạo đến năm 2018. Tác phẩm \"Lược sử thời gian\" của ông trở thành sách khoa học bán chạy nhất, đưa vật lý học đến gần công chúng. Hawking sử dụng ghế lăn và giao tiếp qua máy tính do bệnh tật, nhưng trí tuệ của ông không ngừng tỏa sáng. Ông nhận vô số giải thưởng, bao gồm Huân chương Tự do Tổng thống Mỹ. Ông qua đời ngày 14 tháng 3 năm 2018, để lại di sản khoa học và văn học khổng lồ. Hawking là biểu tượng của sự kiên cường và niềm đam mê khám phá vũ trụ.', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan_khachhang`
--

CREATE TABLE `taikhoan_khachhang` (
  `idtaikhoan` int(11) NOT NULL,
  `idkhachhang` int(11) NOT NULL,
  `tendangnhap` varchar(255) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `taikhoan_khachhang`
--

INSERT INTO `taikhoan_khachhang` (`idtaikhoan`, `idkhachhang`, `tendangnhap`, `matkhau`, `trangthai`) VALUES
(1, 1, 'nguyenvana', '123456', 1),
(2, 2, 'admin', 'admin', 1),
(4, 11, 'taone', 'taone', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan_nhanvien`
--

CREATE TABLE `taikhoan_nhanvien` (
  `idnhanvien` int(11) NOT NULL,
  `TaiKhoan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MatKhau` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Quyen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `taikhoan_nhanvien`
--

INSERT INTO `taikhoan_nhanvien` (`idnhanvien`, `TaiKhoan`, `MatKhau`, `Quyen`, `TrangThai`) VALUES
(1, 'admin', 'admin', 'Quản trị viên', 1),
(2, 'nv01', 'nv01', 'Nhân viên nhập hàng', 1),
(3, 'quanly', 'quanly', 'Quản lý', 0),
(4, 'nv02', 'nv02', 'Nhân viên bán hàng', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `theloai`
--

CREATE TABLE `theloai` (
  `idtheloai` int(11) NOT NULL,
  `tentheloai` varchar(255) NOT NULL,
  `trangthai` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `theloai`
--

INSERT INTO `theloai` (`idtheloai`, `tentheloai`, `trangthai`) VALUES
(1, 'Tiểu thuyết', 1),
(2, 'Khoa học viễn tưởng', 1),
(3, 'Trinh thám', 1),
(4, 'Lãng mạn', 1),
(5, 'Kinh dị', 1),
(6, 'Tự truyện', 1),
(7, 'Khoa học', 1),
(8, 'Lịch sử', 1),
(9, 'Thiếu nhi', 1),
(10, 'Kỹ năng sống', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongtincuahang`
--

CREATE TABLE `thongtincuahang` (
  `idthongtin` int(11) NOT NULL,
  `diachi` varchar(255) NOT NULL,
  `sodienthoai` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `tenNH` varchar(255) NOT NULL,
  `stk` varchar(255) NOT NULL,
  `tenChuTK` varchar(255) NOT NULL,
  `anhQrCk` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thongtincuahang`
--

INSERT INTO `thongtincuahang` (`idthongtin`, `diachi`, `sodienthoai`, `email`, `facebook`, `tiktok`, `tenNH`, `stk`, `tenChuTK`, `anhQrCk`) VALUES
(1, '273, An Dương Vương ,phường 2 , Quận 5, TP Hồ Chí Minh', '0909876543', 'unibook@gmail.com', 'https://www.facebook.com/', 'https://tiktok.com/', 'Vietcombank', '0123456789', 'UniBook', 'img/qrcode/bia.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongtinnhanhang`
--

CREATE TABLE `thongtinnhanhang` (
  `iddiachi` int(11) NOT NULL,
  `idkhachhang` int(11) NOT NULL,
  `thanhpho` varchar(255) NOT NULL,
  `huyen` varchar(255) NOT NULL,
  `xa` varchar(255) NOT NULL,
  `diachi_chitiet` varchar(255) NOT NULL,
  `hotenNgNhan` varchar(255) NOT NULL,
  `sdtNgNhan` varchar(20) NOT NULL,
  `emailNgNhan` varchar(50) DEFAULT NULL,
  `trangthai` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thongtinnhanhang`
--

INSERT INTO `thongtinnhanhang` (`iddiachi`, `idkhachhang`, `thanhpho`, `huyen`, `xa`, `diachi_chitiet`, `hotenNgNhan`, `sdtNgNhan`, `emailNgNhan`, `trangthai`) VALUES
(1, 1, 'Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', 'ádfsadf', 'ádf', '0123457891', '', 0),
(3, 2, 'Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', 'sdsdsd', 'Vũ', '0342362164', '', 1);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`idbanner`);

--
-- Chỉ mục cho bảng `chitietdanhmuc`
--
ALTER TABLE `chitietdanhmuc`
  ADD PRIMARY KEY (`idchitietdanhmuc`),
  ADD KEY `fk_iddanhmuc` (`iddanhmuc`);

--
-- Chỉ mục cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  ADD PRIMARY KEY (`idchitiethoadon`),
  ADD KEY `fk_idhoadon` (`idhoadon`),
  ADD KEY `fk_idsach` (`idsach`);

--
-- Chỉ mục cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD PRIMARY KEY (`idchitietphieunhap`),
  ADD KEY `fk_idphieunhap` (`idphieunhap`),
  ADD KEY `fk_idsach1` (`idsach`);

--
-- Chỉ mục cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`iddanhmuc`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`idgiohang`),
  ADD KEY `fk_idkhachhang3` (`idkhachhang`),
  ADD KEY `fk_idsach5` (`idsach`);

--
-- Chỉ mục cho bảng `hinhanhsach`
--
ALTER TABLE `hinhanhsach`
  ADD PRIMARY KEY (`idhinhanh`),
  ADD KEY `fk_idsach2` (`idsach`);

--
-- Chỉ mục cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD PRIMARY KEY (`idhoadon`),
  ADD KEY `fk_idkhachhang` (`idkhachhang`),
  ADD KEY `fk_idnhanvien` (`idnhanvien`),
  ADD KEY `fk_iddiachi` (`iddiachi`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`idkhachhang`);

--
-- Chỉ mục cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD PRIMARY KEY (`idnhacungcap`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`idnhanvien`),
  ADD KEY `fk_chucvu_nhanvien` (`chucvu`);

--
-- Chỉ mục cho bảng `nhaxuatban`
--
ALTER TABLE `nhaxuatban`
  ADD PRIMARY KEY (`idnhaxuatban`);

--
-- Chỉ mục cho bảng `phanquyen`
--
ALTER TABLE `phanquyen`
  ADD PRIMARY KEY (`Quyen`);

--
-- Chỉ mục cho bảng `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD PRIMARY KEY (`idphieunhap`),
  ADD KEY `fk_idnhacungcap` (`idnhacungcap`),
  ADD KEY `fk_idnhanvien1` (`idnhanvien`);

--
-- Chỉ mục cho bảng `sach`
--
ALTER TABLE `sach`
  ADD PRIMARY KEY (`idsach`),
  ADD KEY `fk_idnhaxuatban` (`idnhaxuatban`),
  ADD KEY `fk_idtacgia` (`idtacgia`),
  ADD KEY `fk_idtheloai` (`idtheloai`),
  ADD KEY `fk_iddanhmuc1` (`idctdanhmuc`);

--
-- Chỉ mục cho bảng `tacgia`
--
ALTER TABLE `tacgia`
  ADD PRIMARY KEY (`idtacgia`);

--
-- Chỉ mục cho bảng `taikhoan_khachhang`
--
ALTER TABLE `taikhoan_khachhang`
  ADD PRIMARY KEY (`idtaikhoan`),
  ADD KEY `fk_tk_khachhang` (`idkhachhang`);

--
-- Chỉ mục cho bảng `taikhoan_nhanvien`
--
ALTER TABLE `taikhoan_nhanvien`
  ADD PRIMARY KEY (`idnhanvien`),
  ADD KEY `fk_quyen` (`Quyen`);

--
-- Chỉ mục cho bảng `theloai`
--
ALTER TABLE `theloai`
  ADD PRIMARY KEY (`idtheloai`);

--
-- Chỉ mục cho bảng `thongtincuahang`
--
ALTER TABLE `thongtincuahang`
  ADD PRIMARY KEY (`idthongtin`);

--
-- Chỉ mục cho bảng `thongtinnhanhang`
--
ALTER TABLE `thongtinnhanhang`
  ADD PRIMARY KEY (`iddiachi`),
  ADD KEY `fk_khachhang_nhanhang` (`idkhachhang`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banner`
--
ALTER TABLE `banner`
  MODIFY `idbanner` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `chitietdanhmuc`
--
ALTER TABLE `chitietdanhmuc`
  MODIFY `idchitietdanhmuc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  MODIFY `idchitiethoadon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  MODIFY `idchitietphieunhap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `iddanhmuc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `idgiohang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `hinhanhsach`
--
ALTER TABLE `hinhanhsach`
  MODIFY `idhinhanh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=264;

--
-- AUTO_INCREMENT cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  MODIFY `idhoadon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `idkhachhang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  MODIFY `idnhacungcap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  MODIFY `idnhanvien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `nhaxuatban`
--
ALTER TABLE `nhaxuatban`
  MODIFY `idnhaxuatban` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `phieunhap`
--
ALTER TABLE `phieunhap`
  MODIFY `idphieunhap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `sach`
--
ALTER TABLE `sach`
  MODIFY `idsach` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT cho bảng `tacgia`
--
ALTER TABLE `tacgia`
  MODIFY `idtacgia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `taikhoan_khachhang`
--
ALTER TABLE `taikhoan_khachhang`
  MODIFY `idtaikhoan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `taikhoan_nhanvien`
--
ALTER TABLE `taikhoan_nhanvien`
  MODIFY `idnhanvien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `theloai`
--
ALTER TABLE `theloai`
  MODIFY `idtheloai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `thongtincuahang`
--
ALTER TABLE `thongtincuahang`
  MODIFY `idthongtin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `thongtinnhanhang`
--
ALTER TABLE `thongtinnhanhang`
  MODIFY `iddiachi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietdanhmuc`
--
ALTER TABLE `chitietdanhmuc`
  ADD CONSTRAINT `fk_iddanhmuc` FOREIGN KEY (`iddanhmuc`) REFERENCES `danhmuc` (`iddanhmuc`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  ADD CONSTRAINT `fk_idhoadon` FOREIGN KEY (`idhoadon`) REFERENCES `hoadon` (`idhoadon`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idsach` FOREIGN KEY (`idsach`) REFERENCES `sach` (`idsach`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD CONSTRAINT `fk_idphieunhap` FOREIGN KEY (`idphieunhap`) REFERENCES `phieunhap` (`idphieunhap`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idsach1` FOREIGN KEY (`idsach`) REFERENCES `sach` (`idsach`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `fk_idkhachhang3` FOREIGN KEY (`idkhachhang`) REFERENCES `khachhang` (`idkhachhang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idsach5` FOREIGN KEY (`idsach`) REFERENCES `sach` (`idsach`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `hinhanhsach`
--
ALTER TABLE `hinhanhsach`
  ADD CONSTRAINT `fk_idsach2` FOREIGN KEY (`idsach`) REFERENCES `sach` (`idsach`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD CONSTRAINT `fk_iddiachi` FOREIGN KEY (`iddiachi`) REFERENCES `thongtinnhanhang` (`iddiachi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idkhachhang` FOREIGN KEY (`idkhachhang`) REFERENCES `khachhang` (`idkhachhang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idnhanvien` FOREIGN KEY (`idnhanvien`) REFERENCES `nhanvien` (`idnhanvien`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD CONSTRAINT `fk_chucvu_nhanvien` FOREIGN KEY (`chucvu`) REFERENCES `phanquyen` (`Quyen`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD CONSTRAINT `fk_idnhacungcap` FOREIGN KEY (`idnhacungcap`) REFERENCES `nhacungcap` (`idnhacungcap`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idnhanvien1` FOREIGN KEY (`idnhanvien`) REFERENCES `nhanvien` (`idnhanvien`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `sach`
--
ALTER TABLE `sach`
  ADD CONSTRAINT `fk_iddanhmuc1` FOREIGN KEY (`idctdanhmuc`) REFERENCES `chitietdanhmuc` (`idchitietdanhmuc`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idnhaxuatban` FOREIGN KEY (`idnhaxuatban`) REFERENCES `nhaxuatban` (`idnhaxuatban`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idtacgia` FOREIGN KEY (`idtacgia`) REFERENCES `tacgia` (`idtacgia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idtheloai` FOREIGN KEY (`idtheloai`) REFERENCES `theloai` (`idtheloai`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `taikhoan_khachhang`
--
ALTER TABLE `taikhoan_khachhang`
  ADD CONSTRAINT `fk_tk_khachhang` FOREIGN KEY (`idkhachhang`) REFERENCES `khachhang` (`idkhachhang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `taikhoan_nhanvien`
--
ALTER TABLE `taikhoan_nhanvien`
  ADD CONSTRAINT `fk_nhanvien` FOREIGN KEY (`idnhanvien`) REFERENCES `nhanvien` (`idnhanvien`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quyen` FOREIGN KEY (`Quyen`) REFERENCES `phanquyen` (`Quyen`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `thongtinnhanhang`
--
ALTER TABLE `thongtinnhanhang`
  ADD CONSTRAINT `fk_khachhang_nhanhang` FOREIGN KEY (`idkhachhang`) REFERENCES `khachhang` (`idkhachhang`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
