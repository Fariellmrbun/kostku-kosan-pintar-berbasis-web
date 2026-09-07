-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2026 at 06:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kost_kontrakan`
--

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_sewa` int(11) NOT NULL,
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `tanggal_bayar` datetime NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `bukti_transfer` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_sewa`, `jumlah_bayar`, `tanggal_bayar`, `metode_pembayaran`, `bukti_transfer`, `catatan`) VALUES
(5, 5, 989000.00, '2026-07-02 10:58:20', 'Transfer Bank BCA', 'bukti_5_1782964700.jpeg', '');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nomor_hp` varchar(15) NOT NULL,
  `peran` enum('admin','penyewa') NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `username`, `password`, `email`, `nomor_hp`, `peran`, `tanggal_daftar`) VALUES
(1, 'Pengelola Kost & Kontrakan', 'admin123', '$2y$10$INhEROycNS/bKr3lp9unBO24TdbHHwJ9hCMzgkStw7TrycbxZsxBK', 'admin@kostkontrakan.com', '08123456789', 'admin', '2026-06-28 20:15:36'),
(4, 'm.fariel luddin marbun', 'fariel', '$2y$10$OZjfrtQ.sgROUEKYCxMb7uPs/YiaZ9d4UR7uUG6lzGgUIuI7kLK.u', 'marbunfariel@gmail.com', '0845678978', 'penyewa', '2026-07-02 03:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `properti`
--

CREATE TABLE `properti` (
  `id_properti` int(11) NOT NULL,
  `nama_properti` varchar(100) NOT NULL,
  `tipe` enum('kost','kontrakan') NOT NULL,
  `deskripsi` text NOT NULL,
  `harga_harian` decimal(10,2) DEFAULT NULL,
  `harga_bulanan` decimal(10,2) DEFAULT NULL,
  `harga_tahunan` decimal(10,2) DEFAULT NULL,
  `status_ketersediaan` enum('tersedia','terisi') NOT NULL DEFAULT 'tersedia',
  `foto` varchar(255) NOT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properti`
--

INSERT INTO `properti` (`id_properti`, `nama_properti`, `tipe`, `deskripsi`, `harga_harian`, `harga_bulanan`, `harga_tahunan`, `status_ketersediaan`, `foto`, `tanggal_input`) VALUES
(4, 'Kontrakan Satria', 'kontrakan', '3 kamar tidur, 1 ruang tamu, Peralatan dapur, 2 AC, 2 kamar mandi, Mesin cuci dan Kulkas, PDAM, Wifi', 250000.00, 1400000.00, 14500000.00, 'tersedia', 'prop_1782755295.jpg', '2026-06-29 16:52:43'),
(5, 'Kost Mawar A1', 'kost', '1 kasur, 1 Kamar Mandi , 1 lemari, 1 kipas angin, PDAM, Wifi', 350000.00, 1300000.00, 8000000.00, 'tersedia', 'prop_1782756427.jpg', '2026-06-29 18:07:07'),
(6, 'Kontrakan Anggrek', 'kontrakan', '4 kamar, 1 ruang tamu, 3 kamar mandi, 1 mesin cuci, 3 AC, WIFI, PDAM, 1 kulkas', 586000.00, 3400000.00, 18600000.00, 'tersedia', 'prop_1782756631.jpg', '2026-06-29 18:10:31'),
(7, 'Kontrakan Akasia A1', 'kontrakan', '5 Kamar, 2 lantai, 1 ruang tamu, 1 ruang keluarga 5 AC, PDAM, WIFI, Perlengkapan Dapur, 3 TV, 2 Kulkas, 2 mesin cuci', 900000.00, 467000.00, 22700000.00, 'tersedia', 'prop_1782757037.jpg', '2026-06-29 18:17:17'),
(8, 'Kontrakan Tahfizd', 'kontrakan', '2 Kamar, 1 mesin cuci, perlengkapan dapur, PDAM, WIFI, 2 Kasur, 2 lemari, 2 kipas angin', 230000.00, 989000.00, 5600000.00, 'terisi', 'prop_1782757207.jpg', '2026-06-29 18:20:07'),
(9, 'Kost Ambyar B4', 'kost', '1 kasur, 1 lemari, 1 AC, WIFI, PDAM, Perlengkapan kamar, 1 kultas', 600000.00, 2500000.00, 9800000.00, 'tersedia', 'prop_1782757423.jpg', '2026-06-29 18:23:43'),
(10, 'Kost Sayunara', 'kost', '2 kasur, 1 kamar mandi, PDAM, WIFI, 1 lemari, 1 kipas angin', 465000.00, 2450000.00, 8678000.00, 'tersedia', 'prop_1782757913.jpg', '2026-06-29 18:31:53');

-- --------------------------------------------------------

--
-- Table structure for table `sewa`
--

CREATE TABLE `sewa` (
  `id_sewa` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_properti` int(11) NOT NULL,
  `jenis_durasi` enum('harian','bulanan','tahunan') NOT NULL,
  `jumlah_durasi` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `total_tagihan` decimal(10,2) NOT NULL,
  `batas_pembayaran` datetime NOT NULL,
  `status_sewa` enum('menunggu_pembayaran','proses_verifikasi','aktif','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `tanggal_booking` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sewa`
--

INSERT INTO `sewa` (`id_sewa`, `id_pengguna`, `id_properti`, `jenis_durasi`, `jumlah_durasi`, `tanggal_mulai`, `tanggal_selesai`, `total_tagihan`, `batas_pembayaran`, `status_sewa`, `tanggal_booking`) VALUES
(5, 4, 8, 'bulanan', 1, '2026-07-02', '2026-08-02', 989000.00, '2026-07-03 10:57:48', 'aktif', '2026-07-02 03:57:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_sewa` (`id_sewa`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `properti`
--
ALTER TABLE `properti`
  ADD PRIMARY KEY (`id_properti`);

--
-- Indexes for table `sewa`
--
ALTER TABLE `sewa`
  ADD PRIMARY KEY (`id_sewa`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_properti` (`id_properti`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `properti`
--
ALTER TABLE `properti`
  MODIFY `id_properti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sewa`
--
ALTER TABLE `sewa`
  MODIFY `id_sewa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_sewa`) REFERENCES `sewa` (`id_sewa`) ON DELETE CASCADE;

--
-- Constraints for table `sewa`
--
ALTER TABLE `sewa`
  ADD CONSTRAINT `sewa_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `sewa_ibfk_2` FOREIGN KEY (`id_properti`) REFERENCES `properti` (`id_properti`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
