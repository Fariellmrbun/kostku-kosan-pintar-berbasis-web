<?php
// Cek apakah session sudah aktif sebelum dijalankan agar tidak error
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../konfigurasi/koneksi.php';
require_once __DIR__ . '/../../konfigurasi/fungsi.php';

cekStatusKadaluarsa($koneksi);

if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: ../masuk.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Kost & Kontrakan Pintar</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        #sidebar ul.components {
            padding: 20px 0;
        }
        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: block;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.3s;
        }
        #sidebar ul li a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            padding-left: 25px;
        }
        #sidebar ul li.active > a {
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
            border-left: 4px solid #fff;
        }
        #content {
            width: 100%;
            padding: 30px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        .admin-navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 15px 20px;
        }
        .card-stat {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            transition: transform 0.3s;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .main-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: none;
            padding: 25px;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <!-- Sidebar Navigation -->
        <nav id="sidebar">
            <div class="sidebar-header text-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-houses-fill me-2"></i>Panel Admin</h5>
                <small class="text-white-50">Kost & Kontrakan Pintar</small>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="properti_kelola.php"><i class="bi bi-door-closed me-2"></i> Kelola Properti</a>
                </li>
                <li>
                    <a href="pengguna_kelola.php"><i class="bi bi-people me-2"></i> Kelola Pengguna</a>
                </li>
                <li>
                    <a href="sewa_konfirmasi.php"><i class="bi bi-credit-card-2-front me-2"></i> Konfirmasi Sewa</a>
                </li>
                <li>
                    <a href="laporan.php"><i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan Keuangan</a>
                </li>
                <li class="border-top mt-3 pt-3">
                    <a href="../index.php" target="_blank"><i class="bi bi-globe me-2"></i> Lihat Website</a>
                </li>
                <li>
                    <a href="../keluar.php" class="text-danger"><i class="bi bi-box-arrow-right me-2 text-danger"></i> Keluar</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content Area -->
        <div id="content">
            <!-- Navbar Admin Atas -->
            <nav class="navbar navbar-expand-lg navbar-light admin-navbar mb-4">
                <div class="container-fluid">
                    <span class="navbar-text">
                        Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong> (Pengelola)
                    </span>
                    
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <span class="badge bg-primary px-3 py-2 rounded-pill">Status: Online</span>
                        <a href="../keluar.php" class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                    </div>
                </div>
            </nav>
            
            <!-- Tempat Konten Turunan -->