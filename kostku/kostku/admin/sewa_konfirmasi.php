<?php
require_once __DIR__ . '/layout/header.php';

$pesan_sukses = "";
$pesan_error = "";
if (isset($_POST['setujui']) && isset($_POST['id_sewa'])) {
    $id_sewa = (int)$_POST['id_sewa'];
    
    try {
        $koneksi->beginTransaction();
        
        $updateSewa = $koneksi->prepare("UPDATE sewa SET status_sewa = 'aktif' WHERE id_sewa = ?");
        $updateSewa->execute([$id_sewa]);
        
        $koneksi->commit();
        $pesan_sukses = "Pembayaran disetujui! Sewa properti kini berstatus AKTIF.";
    } catch (Exception $e) {
        $koneksi->rollBack();
        $pesan_error = "Gagal menyetujui sewa: " . $e->getMessage();
    }
}
if (isset($_POST['tolak']) && isset($_POST['id_sewa'])) {
    $id_sewa = (int)$_POST['id_sewa'];
    $id_properti = (int)$_POST['id_properti'];
    
    try {
        $koneksi->beginTransaction();
        
        $updateSewa = $koneksi->prepare("UPDATE sewa SET status_sewa = 'dibatalkan' WHERE id_sewa = ?");
        $updateSewa->execute([$id_sewa]);
        
        $updateProperti = $koneksi->prepare("UPDATE properti SET status_ketersediaan = 'tersedia' WHERE id_properti = ?");
        $updateProperti->execute([$id_properti]);
        
        $koneksi->commit();
        $pesan_sukses = "Pembayaran ditolak! Transaksi dibatalkan dan unit properti tersedia kembali.";
    } catch (Exception $e) {
        $koneksi->rollBack();
        $pesan_error = "Gagal menolak sewa: " . $e->getMessage();
    }
}

if (isset($_POST['hentikan']) && isset($_POST['id_sewa'])) {
    $id_sewa = (int)$_POST['id_sewa'];
    $id_properti = (int)$_POST['id_properti'];
    
    try {
        $koneksi->beginTransaction();
        
        $updateSewa = $koneksi->prepare("UPDATE sewa SET status_sewa = 'selesai' WHERE id_sewa = ?");
        $updateSewa->execute([$id_sewa]);
        
        $updateProperti = $koneksi->prepare("UPDATE properti SET status_ketersediaan = 'tersedia' WHERE id_properti = ?");
        $updateProperti->execute([$id_properti]);
        
        $koneksi->commit();
        $pesan_sukses = "Masa sewa berhasil dihentikan! Unit properti tersedia kembali.";
    } catch (Exception $e) {
        $koneksi->rollBack();
        $pesan_error = "Gagal menghentikan sewa: " . $e->getMessage();
    }
}

$queryVerifikasi = $koneksi->query("
    SELECT s.*, p.nama_properti, p.tipe, u.nama_lengkap, u.nomor_hp, pb.bukti_transfer, pb.metode_pembayaran, pb.tanggal_bayar, pb.catatan
    FROM sewa s
    JOIN properti p ON s.id_properti = p.id_properti
    JOIN pengguna u ON s.id_pengguna = u.id_pengguna
    JOIN pembayaran pb ON s.id_sewa = pb.id_sewa
    WHERE s.status_sewa = 'proses_verifikasi'
    ORDER BY pb.id_pembayaran ASC
");
$daftarVerifikasi = $queryVerifikasi->fetchAll();

$queryAktif = $koneksi->query("
    SELECT s.*, p.nama_properti, p.tipe, u.nama_lengkap, u.nomor_hp
    FROM sewa s
    JOIN properti p ON s.id_properti = p.id_properti
    JOIN pengguna u ON s.id_pengguna = u.id_pengguna
    WHERE s.status_sewa = 'aktif'
    ORDER BY s.tanggal_selesai ASC
");
$daftarAktif = $queryAktif->fetchAll();
?>
<div class="mb-4">
    <h3 class="fw-bold text-dark"><i class="bi bi-credit-card-2-front text-primary me-2"></i>Konfirmasi & Kelola Penyewaan</h3>
    <p class="text-muted">Proses verifikasi bukti transfer pembayaran dari penyewa dan kelola penyewaan yang sedang aktif.</p>
</div>
<?php if (!empty($pesan_sukses)): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($pesan_sukses); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (!empty($pesan_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($pesan_error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<div class="card main-card mb-5">
    <h5 class="fw-bold text-dark mb-4"><i class="bi bi-shield-fill-exclamation text-warning me-2"></i>Pembayaran Menunggu Verifikasi</h5>

    <?php if (count($daftarVerifikasi) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Penyewa</th>
                        <th>Properti</th>
                        <th>Tagihan & Metode</th>
                        <th>Bukti Transfer</th>
                        <th>Catatan Penyewa</th>
                        <th>Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarVerifikasi as $item): ?>
                        <tr>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($item['nama_lengkap']); ?></strong>
                                <span class="text-muted small"><i class="bi bi-telephone"></i> <?= htmlspecialchars($item['nomor_hp']); ?></span>
                            </td>
                            <td>
                                <strong class="text-dark small d-block"><?= htmlspecialchars($item['nama_properti']); ?></strong>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill text-capitalize small">
                                    <?= htmlspecialchars($item['tipe']); ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-success"><?= formatRupiah($item['total_tagihan']); ?></strong>
                                <span class="text-muted d-block small mt-1">Via: <?= htmlspecialchars($item['metode_pembayaran']); ?></span>
                            </td>
                            <td>
                                <a href="../aset/bukti_transfer/<?= htmlspecialchars($item['bukti_transfer']); ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-pill">
                                    <i class="bi bi-image"></i> Lihat Bukti
                                </a>
                            </td>
                            <td>
                                <span class="small text-muted"><?= !empty($item['catatan']) ? htmlspecialchars($item['catatan']) : '-'; ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pembayaran ini?');">
                                        <input type="hidden" name="id_sewa" value="<?= $item['id_sewa']; ?>">
                                        <button type="submit" name="setujui" class="btn btn-success btn-sm rounded-3"><i class="bi bi-check-lg"></i> Setujui</button>
                                    </form>
                                    <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak pembayaran ini? Transaksi sewa akan dibatalkan.');">
                                        <input type="hidden" name="id_sewa" value="<?= $item['id_sewa']; ?>">
                                        <input type="hidden" name="id_properti" value="<?= $item['id_properti']; ?>">
                                        <button type="submit" name="tolak" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-x-lg"></i> Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-shield-check text-success" style="font-size: 3.5rem;"></i>
            <h6 class="mt-3 text-secondary">Tidak ada pembayaran baru yang perlu diverifikasi saat ini.</h6>
        </div>
    <?php endif; ?>
</div>
<div class="card main-card">
    <h5 class="fw-bold text-dark mb-4"><i class="bi bi-house-check text-success me-2"></i>Daftar Penyewaan Aktif (Terisi)</h5>

    <?php if (count($daftarAktif) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Penyewa</th>
                        <th>Properti</th>
                        <th>Durasi Sewa</th>
                        <th>Periode Sewa</th>
                        <th>Aksi Pengelola</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarAktif as $sewa): ?>
                        <tr>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($sewa['nama_lengkap']); ?></strong>
                                <span class="text-muted small"><i class="bi bi-telephone"></i> <?= htmlspecialchars($sewa['nomor_hp']); ?></span>
                            </td>
                            <td>
                                <strong class="text-dark small d-block"><?= htmlspecialchars($sewa['nama_properti']); ?></strong>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-capitalize small mt-1">
                                    <?= htmlspecialchars($sewa['tipe']); ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-primary"><?= formatRupiah($sewa['total_tagihan']); ?></strong>
                                <span class="text-muted d-block small"><?= $sewa['jumlah_durasi'] . ' ' . $sewa['jenis_durasi']; ?></span>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-success"><i class="bi bi-calendar-check me-1"></i>Mulai: <?= tanggalIndo($sewa['tanggal_mulai']); ?></span><br>
                                    <span class="text-danger"><i class="bi bi-calendar-x me-1"></i>Selesai: <?= tanggalIndo($sewa['tanggal_selesai']); ?></span>
                                </div>
                            </td>
                            <td>
                                <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghentikan masa sewa ini secara paksa/lebih awal? Properti akan kembali berstatus tersedia.');">
                                    <input type="hidden" name="id_sewa" value="<?= $sewa['id_sewa']; ?>">
                                    <input type="hidden" name="id_properti" value="<?= $sewa['id_properti']; ?>">
                                    <button type="submit" name="hentikan" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                        <i class="bi bi-stop-circle"></i> Hentikan Sewa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-door-closed text-muted" style="font-size: 3.5rem;"></i>
            <h6 class="mt-3 text-secondary">Belum ada penyewaan yang berstatus aktif saat ini.</h6>
        </div>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
