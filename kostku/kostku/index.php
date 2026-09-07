<?php
session_start();
require_once 'konfigurasi/koneksi.php';
require_once 'konfigurasi/fungsi.php';

cekStatusKadaluarsa($koneksi);

$tipe_filter = isset($_GET['tipe']) ? $_GET['tipe'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$sql = "SELECT * FROM properti WHERE 1=1";
$params = [];

if (!empty($tipe_filter)) {
    $sql .= " AND tipe = :tipe";
    $params[':tipe'] = $tipe_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND status_ketersediaan = :status";
    $params[':status'] = $status_filter;
}

if (!empty($cari)) {
    $sql .= " AND nama_properti LIKE :cari";
    $params[':cari'] = '%' . $cari . '%';
}

$sql .= " ORDER BY id_properti DESC";
$query = $koneksi->prepare($sql);
$query->execute($params);
$daftar_properti = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kost & Kontrakan Pintar - Hunian Mudah & Praktis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: 700;
            color: #1e3c72;
        }
        .hero-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 80px 0;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
        }
        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .search-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-top: -40px;
            padding: 25px;
            border: none;
        }
        .property-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            background-color: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        .card-img-container {
            position: relative;
            height: 220px;
            background: linear-gradient(135deg, #e0e0e0, #f5f5f5);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Tambahan pengaman */
        }
        .card-img-container i {
            font-size: 4rem;
            color: #b0bec5;
        }
        .badge-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .badge-tipe {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 8px 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.8rem;
            background-color: rgba(30, 60, 114, 0.9);
            color: white;
        }
        .price-text {
            color: #1e3c72;
            font-weight: 700;
        }
        .btn-detail {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .footer {
            background-color: #1a252f;
            color: #b2bec3;
            padding: 40px 0;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-houses-fill me-2 fs-3 text-primary"></i>
                <span>Kost & Kontrakan <span class="text-primary">Pintar</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?tipe=kost">Cari Kost</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?tipe=kontrakan">Cari Kontrakan</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['login'])): ?>
                        <span class="me-2 text-secondary small d-none d-md-inline">Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></span>
                        <?php if ($_SESSION['peran'] === 'admin'): ?>
                            <a href="admin/index.php" class="btn btn-outline-primary rounded-pill px-3 btn-sm"><i class="bi bi-speedometer2 me-1"></i> Panel Admin</a>
                        <?php else: ?>
                            <a href="penyewa/index.php" class="btn btn-outline-primary rounded-pill px-3 btn-sm"><i class="bi bi-person-fill me-1"></i> Menu Penyewa</a>
                        <?php endif; ?>
                        <a href="keluar.php" class="btn btn-danger rounded-pill px-3 btn-sm"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                    <?php else: ?>
                        <a href="masuk.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm">Masuk</a>
                        <a href="daftar.php" class="btn btn-primary rounded-pill px-4 btn-sm">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <header class="hero-section text-center">
        <div class="container">
            <h1 class="hero-title">Temukan Hunian Pintar Impian Anda</h1>
            <p class="lead mb-4 col-lg-8 mx-auto">Sistem manajemen sewa Kost dan Kontrakan terpadu dengan batasan pembayaran realistis, ketersediaan kamar real-time, dan pemesanan yang mudah.</p>
            <div class="d-flex justify-content-center">
                <a href="#properti-list" class="btn btn-light btn-lg px-4 rounded-pill fw-medium text-primary">Lihat Unit</a>
            </div>
        </div>
    </header>
    <section class="container mb-5">
        <div class="card search-card">
            <form action="" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-secondary small fw-bold">Cari Nama Properti</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0" name="cari" placeholder="Ketik nama kost/kontrakan..." value="<?= htmlspecialchars($cari); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary small fw-bold">Tipe Hunian</label>
                        <select class="form-select bg-light" name="tipe">
                            <option value="">Semua Tipe</option>
                            <option value="kost" <?= $tipe_filter === 'kost' ? 'selected' : ''; ?>>Kost</option>
                            <option value="kontrakan" <?= $tipe_filter === 'kontrakan' ? 'selected' : ''; ?>>Kontrakan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary small fw-bold">Status Ketersediaan</label>
                        <select class="form-select bg-light" name="status">
                            <option value="">Semua Status</option>
                            <option value="tersedia" <?= $status_filter === 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="terisi" <?= $status_filter === 'terisi' ? 'selected' : ''; ?>>Terisi</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <main class="container" id="properti-list">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark mb-0">Daftar Hunian Terbaru</h3>
            <span class="badge bg-secondary px-3 py-2 rounded-pill"><?= count($daftar_properti); ?> Unit Ditemukan</span>
        </div>
        <div class="row g-4">
            <?php if (count($daftar_properti) > 0): ?>
                <?php foreach ($daftar_properti as $properti): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="property-card">
                            <div class="card-img-container">
                                <?php if (!empty($properti['foto']) && file_exists('aset/foto_properti/' . $properti['foto']) && $properti['foto'] !== 'default.jpg'): ?>
                                    <img src="aset/foto_properti/<?= htmlspecialchars($properti['foto']); ?>" alt="<?= htmlspecialchars($properti['nama_properti']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="bi <?= $properti['tipe'] === 'kost' ? 'bi-door-closed' : 'bi-house'; ?>"></i>
                                <?php endif; ?>
                                <span class="badge-tipe text-capitalize"><?= htmlspecialchars($properti['tipe']); ?></span>
                                
                                <?php if ($properti['status_ketersediaan'] === 'tersedia'): ?>
                                    <span class="badge-status bg-success text-white">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge-status bg-danger text-white">Terisi / Terkunci</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($properti['nama_properti']); ?></h5>
                                <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars(substr($properti['deskripsi'], 0, 100)) . (strlen($properti['deskripsi']) > 100 ? '...' : ''); ?></p>
                                <hr class="my-3 text-muted">
                                <div class="mb-3 small">
                                    <?php if (!empty($properti['harga_harian'])): ?>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-secondary">Harian</span>
                                            <span class="price-text"><?= formatRupiah($properti['harga_harian']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($properti['harga_bulanan'])): ?>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-secondary">Bulanan</span>
                                            <span class="price-text"><?= formatRupiah($properti['harga_bulanan']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($properti['harga_tahunan'])): ?>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-secondary">Tahunan</span>
                                            <span class="price-text"><?= formatRupiah($properti['harga_tahunan']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="detail.php?id=<?= $properti['id_properti']; ?>" class="btn <?= $properti['status_ketersediaan'] === 'tersedia' ? 'btn-primary' : 'btn-outline-secondary'; ?> w-100 btn-detail py-2">
                                    <i class="bi bi-eye-fill me-1"></i> Detail & Sewa
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center my-5">
                    <i class="bi bi-building-fill-slash text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-secondary">Tidak ada unit properti yang sesuai dengan kriteria pencarian Anda.</h5>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer class="footer py-4 mt-5 bg-dark text-white-50 text-center">
        <div class="container">
            <h5 class="fw-bold mb-2 text-white"><i class="bi bi-houses-fill me-2 text-primary"></i>Kost & Kontrakan Pintar</h5>
            <p class="small mb-0">&copy; 2026 Kost & Kontrakan Pintar. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>