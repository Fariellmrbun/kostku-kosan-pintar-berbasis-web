<?php
session_start();
require_once __DIR__ . '/../../konfigurasi/koneksi.php';
require_once __DIR__ . '/../../konfigurasi/fungsi.php';

cekStatusKadaluarsa($koneksi);
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'penyewa') {
    header("Location: ../masuk.php");
    exit;
}
$id_pengguna_aktif = $_SESSION['id_pengguna'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Penyewa - Kost & Kontrakan Pintar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
        }
        .navbar-tenant {
            background-color: #1e3c72;
        }
        .navbar-tenant .navbar-brand, 
        .navbar-tenant .nav-link,
        .navbar-tenant .navbar-text {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .navbar-tenant .nav-link:hover,
        .navbar-tenant .nav-link.active {
            color: #ffffff !important;
            font-weight: 500;
        }
        .sidebar-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: none;
        }
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: none;
            padding: 30px;
        }
        .badge-status-sewa {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-tenant sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-houses-fill me-2 fs-4"></i>
                <span>Menu Penyewa <span class="fw-light">Pintar</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#tenantNavbar" aria-controls="tenantNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="tenantNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat_sewa.php"><i class="bi bi-clock-history me-1"></i> Riwayat Sewa</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-globe me-1"></i> Lihat Beranda</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="navbar-text d-none d-md-inline">Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></span>
                    <a href="../keluar.php" class="btn btn-danger btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mb-5">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card sidebar-card p-3">
                    <div class="text-center py-3 border-bottom mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-person-fill fs-1"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></h6>
                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill">Penyewa Kost/Kontrakan</span>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 mb-1"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                        <a href="riwayat_sewa.php" class="list-group-item list-group-item-action border-0 rounded-3 py-2.5 mb-1"><i class="bi bi-clock-history me-2"></i> Riwayat Sewa</a>
                        <a href="../index.php" class="list-group-item list-group-item-action border-0 rounded-3 py-2.5"><i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
