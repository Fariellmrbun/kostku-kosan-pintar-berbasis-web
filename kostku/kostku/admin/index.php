<?php
require_once __DIR__ . '/layout/header.php';

$totalProperti = $koneksi->query("SELECT COUNT(*) FROM properti")->fetchColumn();
$propertiTerisi = $koneksi->query("SELECT COUNT(*) FROM properti WHERE status_ketersediaan = 'terisi'")->fetchColumn();
$totalPenyewa = $koneksi->query("SELECT COUNT(*) FROM pengguna WHERE peran = 'penyewa'")->fetchColumn();

$queryPendapatan = $koneksi->query("
    SELECT SUM(p.jumlah_bayar) 
    FROM pembayaran p 
    JOIN sewa s ON p.id_sewa = s.id_sewa 
    WHERE s.status_sewa IN ('aktif', 'selesai')
");
$totalPendapatan = $queryPendapatan->fetchColumn();
$totalPendapatan = $totalPendapatan ? $totalPendapatan : 0;

$queryTerbaru = $koneksi->query("
    SELECT s.*, p.nama_properti, u.nama_lengkap 
    FROM sewa s
    JOIN properti p ON s.id_properti = p.id_properti
    JOIN pengguna u ON s.id_pengguna = u.id_pengguna
    ORDER BY s.id_sewa DESC
    LIMIT 5
");
$transaksiTerbaru = $queryTerbaru->fetchAll();
$queryVerifikasi = $koneksi->query("
    SELECT s.*, p.nama_properti, u.nama_lengkap, pb.bukti_transfer, pb.metode_pembayaran
    FROM sewa s
    JOIN properti p ON s.id_properti = p.id_properti
    JOIN pengguna u ON s.id_pengguna = u.id_pengguna
    JOIN pembayaran pb ON s.id_sewa = pb.id_sewa
    WHERE s.status_sewa = 'proses_verifikasi'
    ORDER BY pb.id_pembayaran ASC
");
$menungguVerifikasi = $queryVerifikasi->fetchAll();
?>

<div class="main-card mb-4">
    <h3 class="fw-bold text-dark mb-2">Dashboard Pengelola</h3>
    <p class="text-muted mb-0">Halaman ikhtisar sistem informasi kost dan kontrakan pintar. Pantau statistik dan verifikasi pembayaran masuk secara langsung.</p>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card card-stat p-3 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                    <i class="bi bi-building fs-3"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block mb-1">Total Unit Properti</span>
                    <h4 class="fw-bold text-dark mb-0"><?= $totalProperti; ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card card-stat p-3 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                    <i class="bi bi-house-lock fs-3"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block mb-1">Unit Sedang Terisi</span>
                    <h4 class="fw-bold text-dark mb-0"><?= $propertiTerisi; ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card card-stat p-3 border-0 bg-white shadow-sm">
            <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block mb-1">Total Akun Penyewa</span>
                    <h4 class="fw-bold text-dark mb-0"><?= $totalPenyewa; ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card card-stat p-3 border-0 bg-white shadow-sm">
      <div class="d-flex align-items-center">
           <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                <i class="bi bi-cash-stack fs-3"></i>
            </div>
            <div>
                    <span class="text-secondary small d-block mb-1">Total Pendapatan</span>
                    <h4 class="fw-bold text-dark mb-0 fs-5"><?= formatRupiah($totalPendapatan); ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card main-card h-100">
            <h5 class="fw-bold text-dark mb-4"><i class="bi bi-bell-fill text-warning me-2"></i>Persetujuan Pembayaran Masuk</h5>
            
            <?php if (count($menungguVerifikasi) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Penyewa</th>
                                <th>Properti</th>
                                <th>Jumlah Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menungguVerifikasi as $mv): ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark small d-block"><?= htmlspecialchars($mv['nama_lengkap']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="small text-secondary"><?= htmlspecialchars($mv['nama_properti']); ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-success small"><?= formatRupiah($mv['total_tagihan']); ?></strong>
                                    </td>
                                    <td>
                                        <a href="sewa_konfirmasi.php" class="btn btn-warning btn-sm rounded-pill"><i class="bi bi-check2-square"></i> Periksa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                    <h6 class="mt-3 text-secondary">Semua pembayaran sudah bersih terverifikasi!</h6>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card main-card h-100">
            <h5 class="fw-bold text-dark mb-4"><i class="bi bi-clock-history text-primary me-2"></i>Pemesanan Terbaru</h5>

            <?php if (count($transaksiTerbaru) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($transaksiTerbaru as $tr): ?>
                        <div class="list-group-item px-0 py-3 border-bottom border-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark small"><?= htmlspecialchars($tr['nama_lengkap']); ?></strong>
                                <span class="small text-muted"><?= date('d/m/Y H:i', strtotime($tr['tanggal_booking'])); ?></span>
                            </div>
                            <p class="small text-secondary mb-1">Memesan: <strong><?= htmlspecialchars($tr['nama_properti']); ?></strong></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary small"><?= formatRupiah($tr['total_tagihan']); ?></span>
                                <?php if ($tr['status_sewa'] === 'menunggu_pembayaran'): ?>
                                    <span class="badge bg-warning text-dark small" style="font-size: 0.75rem;">Menunggu Bayar</span>
                                <?php elseif ($tr['status_sewa'] === 'proses_verifikasi'): ?>
                                    <span class="badge bg-info text-white small" style="font-size: 0.75rem;">Proses Verifikasi</span>
                                <?php elseif ($tr['status_sewa'] === 'aktif'): ?>
                                    <span class="badge bg-success text-white small" style="font-size: 0.75rem;">Aktif</span>
                                <?php elseif ($tr['status_sewa'] === 'selesai'): ?>
                                    <span class="badge bg-secondary text-white small" style="font-size: 0.75rem;">Selesai</span>
                                <?php elseif ($tr['status_sewa'] === 'dibatalkan'): ?>
                                    <span class="badge bg-danger text-white small" style="font-size: 0.75rem;">Batal</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-card-list text-muted" style="font-size: 3.5rem;"></i>
                    <h6 class="mt-3 text-secondary">Belum ada pemesanan terdaftar.</h6>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
