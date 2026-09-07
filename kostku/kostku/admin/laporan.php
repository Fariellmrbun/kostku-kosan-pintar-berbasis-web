<?php
require_once __DIR__ . '/layout/header.php';

$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
$tipe_filter = isset($_GET['tipe']) ? $_GET['tipe'] : '';

$sql = "
    SELECT s.*, p.nama_properti, p.tipe, u.nama_lengkap 
    FROM sewa s
    JOIN properti p ON s.id_properti = p.id_properti
    JOIN pengguna u ON s.id_pengguna = u.id_pengguna
    WHERE s.status_sewa IN ('aktif', 'selesai')
";
$params = [];
if (!empty($tanggal_awal)) {
    $sql .= " AND s.tanggal_mulai >= :tgl_awal";
    $params[':tgl_awal'] = $tanggal_awal;
}
if (!empty($tanggal_akhir)) {
    $sql .= " AND s.tanggal_mulai <= :tgl_akhir";
    $params[':tgl_akhir'] = $tanggal_akhir;
}
if (!empty($tipe_filter)) {
    $sql .= " AND p.tipe = :tipe";
    $params[':tipe'] = $tipe_filter;
}
$sql .= " ORDER BY s.id_sewa ASC";
$query = $koneksi->prepare($sql);
$query->execute($params);
$laporanSewa = $query->fetchAll();

$totalAkumulasi = 0;
foreach ($laporanSewa as $l) {
    $totalAkumulasi += $l['total_tagihan'];
}
?>
<style>
    @media print {
        #sidebar, 
        .navbar, 
        .filter-section, 
        .btn-print-action, 
        footer {
            display: none !important;
        }
        #content {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .main-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
        body {
            background-color: #fff !important;
            color: #000 !important;
        }
        table {
            border: 1px solid #000 !important;
        }
        th, td {
            border: 1px solid #000 !important;
        }
    }
    .print-header {
        display: none;
    }
</style>
<div class="print-header">
    <h3 class="fw-bold mb-1">LAPORAN KEUANGAN & PENYEWAAN</h3>
    <h5 class="fw-normal mb-1">Sistem Informasi Manajemen Kost & Kontrakan Pintar</h5>
    <p class="small text-muted mb-0">Laporan Transaksi Masuk Periode: <?= !empty($tanggal_awal) ? tanggalIndo($tanggal_awal) : 'Awal'; ?> s/d <?= !empty($tanggal_akhir) ? tanggalIndo($tanggal_akhir) : 'Hari Ini'; ?></p>
</div>
<div class="d-flex justify-content-between align-items-center mb-4 btn-print-action">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Laporan Keuangan & Sewa</h3>
    <button onclick="window.print()" class="btn btn-success rounded-pill px-4"><i class="bi bi-printer-fill me-1"></i> Cetak Laporan (PDF)</button>
</div>
<div class="card main-card mb-4 filter-section">
    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-funnel"></i> Filter Laporan</h6>
    <form action="" method="GET">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-secondary small fw-bold">Dari Tanggal Mulai</label>
                <input type="date" class="form-control" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label text-secondary small fw-bold">Sampai Tanggal Mulai</label>
                <input type="date" class="form-control" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-bold">Tipe Hunian</label>
                <select class="form-select" name="tipe">
                    <option value="">Semua Tipe</option>
                    <option value="kost" <?= $tipe_filter === 'kost' ? 'selected' : ''; ?>>Kost</option>
                    <option value="kontrakan" <?= $tipe_filter === 'kontrakan' ? 'selected' : ''; ?>>Kontrakan</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>
</div>
<div class="card main-card">
    <div class="mb-4">
        <h5 class="fw-bold text-dark mb-1">Rincian Transaksi Masuk (Aktif & Selesai)</h5>
        <small class="text-secondary">Menampilkan transaksi sewa yang telah dibayar lunas dan disetujui.</small>
    </div>
    <?php if (count($laporanSewa) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Penyewa</th>
                        <th>Nama Properti</th>
                        <th>Tipe</th>
                        <th>Durasi Sewa</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th class="text-end">Jumlah Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($laporanSewa as $item): 
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($item['nama_lengkap']); ?></strong></td>
                            <td><?= htmlspecialchars($item['nama_properti']); ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($item['tipe']); ?></td>
                            <td><?= $item['jumlah_durasi'] . ' ' . $item['jenis_durasi']; ?></td>
                            <td><?= tanggalIndo($item['tanggal_mulai']); ?></td>
                            <td><?= tanggalIndo($item['tanggal_selesai']); ?></td>
                            <td class="text-end fw-bold text-primary"><?= formatRupiah($item['total_tagihan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-primary fw-bold">
                        <td colspan="7" class="text-end">TOTAL SELURUH PENDAPATAN :</td>
                        <td class="text-end text-success fs-6"><?= formatRupiah($totalAkumulasi); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-excel text-muted" style="font-size: 3.5rem;"></i>
            <h6 class="mt-3 text-secondary">Tidak ada data transaksi sewa untuk periode/filter yang dipilih.</h6>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
