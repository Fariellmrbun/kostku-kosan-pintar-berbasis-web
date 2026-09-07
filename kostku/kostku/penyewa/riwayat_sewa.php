<?php
require_once __DIR__ . '/layout/header.php';

$queryRiwayat = $koneksi->prepare("
    SELECT s.*, p.nama_properti, p.tipe 
    FROM sewa s 
    JOIN properti p ON s.id_properti = p.id_properti 
    WHERE s.id_pengguna = :id
    ORDER BY s.id_sewa DESC
");
$queryRiwayat->execute([':id' => $id_pengguna_aktif]);
$daftarRiwayat = $queryRiwayat->fetchAll();
?>

<div class="card content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Transaksi & Sewa</h4>
        <span class="badge bg-secondary px-3 py-2 rounded-pill"><?= count($daftarRiwayat); ?> Transaksi</span>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Pemesanan berhasil dibuat! Silakan lakukan pembayaran sebelum batas waktu yang ditentukan.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'bayar_sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Bukti pembayaran berhasil diunggah! Mohon tunggu konfirmasi verifikasi dari pihak pengelola/admin.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (count($daftarRiwayat) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Properti</th>
                        <th>Masa Sewa</th>
                        <th>Total Biaya</th>
                        <th>Status</th>
                        <th>Batas Waktu Bayar / Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarRiwayat as $item): ?>
                        <tr>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($item['nama_properti']); ?></strong>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill text-capitalize small mt-1">
                                    <?= htmlspecialchars($item['tipe']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-muted"><i class="bi bi-calendar-check me-1"></i><?= tanggalIndo($item['tanggal_mulai']); ?> s/d</span><br>
                                    <span class="text-muted"><i class="bi bi-calendar-x me-1"></i><?= tanggalIndo($item['tanggal_selesai']); ?></span>
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary"><?= formatRupiah($item['total_tagihan']); ?></strong>
                                <span class="text-muted d-block small"><?= $item['jumlah_durasi'] . ' ' . $item['jenis_durasi']; ?></span>
                            </td>
                            <td>
                                <?php if ($item['status_sewa'] === 'menunggu_pembayaran'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Menunggu Pembayaran</span>
                                <?php elseif ($item['status_sewa'] === 'proses_verifikasi'): ?>
                                    <span class="badge bg-info text-white"><i class="bi bi-shield-fill-exclamation me-1"></i>Verifikasi Admin</span>
                                <?php elseif ($item['status_sewa'] === 'aktif'): ?>
                                    <span class="badge bg-success text-white"><i class="bi bi-check-circle-fill me-1"></i>Aktif disewa</span>
                                <?php elseif ($item['status_sewa'] === 'selesai'): ?>
                                    <span class="badge bg-secondary text-white"><i class="bi bi-flag-fill me-1"></i>Selesai</span>
                                <?php elseif ($item['status_sewa'] === 'dibatalkan'): ?>
                                    <span class="badge bg-danger text-white"><i class="bi bi-x-circle-fill me-1"></i>Dibatalkan / Kadaluarsa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['status_sewa'] === 'menunggu_pembayaran'): ?>
                                    <div class="small text-danger fw-medium">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Batas Akhir:<br>
                                        <?= tanggalWaktuIndo($item['batas_pembayaran']); ?>
                                        
                                        <!-- Countdown Timer Visual sederhana -->
                                        <div class="mt-1 text-secondary small bg-light p-1 rounded countdown-timer" data-deadline="<?= $item['batas_pembayaran']; ?>">
                                            Memuat sisa waktu...
                                        </div>
                                    </div>
                                <?php elseif ($item['status_sewa'] === 'proses_verifikasi'): ?>
                                    <span class="small text-muted">Bukti transfer sudah diunggah. Menunggu approval admin.</span>
                                <?php elseif ($item['status_sewa'] === 'aktif'): ?>
                                    <span class="small text-success fw-bold"><i class="bi bi-check-all"></i> Sedang Anda tempatkan</span>
                                <?php else: ?>
                                    <span class="small text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['status_sewa'] === 'menunggu_pembayaran'): ?>
                                    <a href="bayar.php?id=<?= $item['id_sewa']; ?>" class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-upload"></i> Bayar Sekarang</a>
                                <?php else: ?>
                                    <a href="../detail.php?id=<?= $item['id_properti']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="bi bi-eye"></i> Detail Unit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-folder-x text-muted" style="font-size: 3.5rem;"></i>
            <h6 class="mt-3 text-secondary">Belum ada riwayat transaksi sewa.</h6>
            <a href="../index.php" class="btn btn-primary rounded-pill btn-sm mt-3 px-4">Sewa Sekarang</a>
        </div>
    <?php endif; ?>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const timers = document.querySelectorAll(".countdown-timer");
        
        function updateTimers() {
            const sekarang = new Date().getTime();
            
            timers.forEach(timer => {
                    const deadlineStr = timer.getAttribute("data-deadline").replace(/-/g, "/"); // compatibility fix
                       const deadline = new Date(deadlineStr).getTime();
                           const selisih = deadline - sekarang;
                
                if (selisih <= 0) {
                    timer.innerHTML = "<span class='text-danger fw-bold'>Waktu Habis! Harap refresh halaman</span>";
                    return;
                }
                    const jam = Math.floor((selisih % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                      const menit = Math.floor((selisih % (1000 * 60 * 60)) / (1000 * 60));
                     const detik = Math.floor((selisih % (1000 * 60)) / 1000);
                
                timer.innerHTML = `<i class="bi bi-stopwatch"></i> Sisa waktu: <strong>${jam}j ${menit}m ${detik}s</strong>`;
            });
        }
        updateTimers();
        setInterval(updateTimers, 1000);
    });
</script>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
