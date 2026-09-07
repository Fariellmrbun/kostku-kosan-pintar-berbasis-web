<?php
require_once __DIR__ . '/layout/header.php';

     $queryTotal = $koneksi->prepare("SELECT COUNT(*) FROM sewa WHERE id_pengguna = :id");
       $queryTotal->execute([':id' => $id_pengguna_aktif]);
         $totalSewa = $queryTotal->fetchColumn();
              $queryAktif = $koneksi->prepare("SELECT COUNT(*) FROM sewa WHERE id_pengguna = :id AND status_sewa = 'aktif'");
         $queryAktif->execute([':id' => $id_pengguna_aktif]);
          $totalAktif = $queryAktif->fetchColumn();

        $queryPending = $koneksi->prepare("SELECT COUNT(*) FROM sewa WHERE id_pengguna = :id AND status_sewa = 'menunggu_pembayaran'");
        $queryPending->execute([':id' => $id_pengguna_aktif]);
       $totalPending = $queryPending->fetchColumn();

$querySewaAktif = $koneksi->prepare("
    SELECT s.*, p.nama_properti, p.tipe 
    FROM sewa s 
    JOIN properti p ON s.id_properti = p.id_properti 
    WHERE s.id_pengguna = :id AND s.status_sewa = 'aktif'
    ORDER BY s.id_sewa DESC
");
$querySewaAktif->execute([':id' => $id_pengguna_aktif]);
$sewaAktif = $querySewaAktif->fetchAll();
?>
<div class="content-card mb-4">
    <h3 class="fw-bold text-dark mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h3>
    <p class="text-muted">Melalui halaman ini, Anda dapat memantau status penyewaan kost & kontrakan pintar Anda secara mudah dan real-time.</p>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm bg-primary text-white" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-medium text-white-50 mb-1">Total Penyewaan</h6>
                    <h3 class="fw-bold mb-0"><?= $totalSewa; ?></h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-3 p-3">
                    <i class="bi bi-journal-text fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm bg-success text-white" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-medium text-white-50 mb-1">Sewa Aktif (Terisi)</h6>
                    <h3 class="fw-bold mb-0"><?= $totalAktif; ?></h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-3 p-3">
                    <i class="bi bi-house-check fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm bg-warning text-white" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-medium text-white-50 mb-1">Menunggu Pembayaran</h6>
                    <h3 class="fw-bold mb-0"><?= $totalPending; ?></h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-3 p-3">
                    <i class="bi bi-clock-history fs-2"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card content-card">
    <h5 class="fw-bold text-dark mb-4"><i class="bi bi-info-circle-fill text-primary me-2"></i>Unit Hunian yang Anda Tempatkan</h5>
    <?php if (count($sewaAktif) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Properti</th>
                        <th>Tipe</th>
                        <th>Masa Sewa</th>
                        <th>Total Tagihan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sewaAktif as $sewa): ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($sewa['nama_properti']); ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-capitalize">
                                    <?= htmlspecialchars($sewa['tipe']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-success"><i class="bi bi-calendar-check me-1"></i>Mulai: <?= tanggalIndo($sewa['tanggal_mulai']); ?></span><br>
                                    <span class="text-danger"><i class="bi bi-calendar-x me-1"></i>Selesai: <?= tanggalIndo($sewa['tanggal_selesai']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-primary"><?= formatRupiah($sewa['total_tagihan']); ?></span>
                                <span class="text-muted d-block small">Durasi: <?= $sewa['jumlah_durasi'] . ' ' . $sewa['jenis_durasi']; ?></span>
                            </td>
                            <td>
                                <a href="../detail.php?id=<?= $sewa['id_properti']; ?>" class="btn btn-outline-primary btn-sm rounded-pill"><i class="bi bi-eye"></i> Detail Unit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-house-door text-muted" style="font-size: 3.5rem;"></i>
            <h6 class="mt-3 text-secondary">Saat ini Anda tidak memiliki unit kost atau kontrakan yang aktif disewa.</h6>
            <a href="../index.php" class="btn btn-primary rounded-pill btn-sm mt-3 px-4"><i class="bi bi-search me-1"></i>Cari Properti Sekarang</a>
        </div>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
