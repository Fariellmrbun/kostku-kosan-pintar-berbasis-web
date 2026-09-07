<?php
session_start();
require_once 'konfigurasi/koneksi.php';
require_once 'konfigurasi/fungsi.php';

cekStatusKadaluarsa($koneksi);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_properti = (int)$_GET['id'];
$query = $koneksi->prepare("SELECT * FROM properti WHERE id_properti = :id");
$query->execute([':id' => $id_properti]);
$properti = $query->fetch();

if (!$properti) {
    header("Location: index.php");
    exit;
}

$info_selesai_sewa = null;
if ($properti['status_ketersediaan'] === 'terisi') {
    $querySewaAktif = $koneksi->prepare("
        SELECT tanggal_selesai FROM sewa 
        WHERE id_properti = :id_properti AND status_sewa IN ('aktif', 'menunggu_pembayaran', 'proses_verifikasi')
        ORDER BY tanggal_selesai DESC LIMIT 1
    ");
    $querySewaAktif->execute([':id_properti' => $id_properti]);
    $info_selesai_sewa = $querySewaAktif->fetchColumn();
}

$pesan_error = "";

if (isset($_POST['booking'])) {
    if (!isset($_SESSION['login'])) {
        header("Location: masuk.php");
        exit;
    }
    if ($_SESSION['peran'] !== 'penyewa') {
        $pesan_error = "Hanya penyewa yang dapat melakukan pemesanan.";
    } elseif ($properti['status_ketersediaan'] !== 'tersedia') {
        $pesan_error = "Unit properti ini sudah tidak tersedia (terisi).";
    } else {
        $id_pengguna = $_SESSION['id_pengguna'];
        $jenis_durasi = $_POST['jenis_durasi'];
        $jumlah_durasi = (int)$_POST['jumlah_durasi'];
        $tanggal_mulai = $_POST['tanggal_mulai'];

        if (empty($jenis_durasi) || $jumlah_durasi <= 0 || empty($tanggal_mulai)) {
            $pesan_error = "Harap isi semua kolom formulir penyewaan!";
        } else {
            $harga_satuan = 0;
            if ($jenis_durasi === 'harian' && $properti['harga_harian']) {
                $harga_satuan = $properti['harga_harian'];
            } elseif ($jenis_durasi === 'bulanan' && $properti['harga_bulanan']) {
                $harga_satuan = $properti['harga_bulanan'];
            } elseif ($jenis_durasi === 'tahunan' && $properti['harga_tahunan']) {
                $harga_satuan = $properti['harga_tahunan'];
            }

            if ($harga_satuan == 0) {
                $pesan_error = "Durasi sewa yang dipilih tidak didukung unit properti ini.";
            } else {
                $total_tagihan = $harga_satuan * $jumlah_durasi;
                $tanggal_selesai = hitungTanggalSelesai($tanggal_mulai, $jenis_durasi, $jumlah_durasi);
                $batas_pembayaran = date('Y-m-d H:i:s', strtotime('+24 hours'));

                try {
                    $koneksi->beginTransaction();
                    $querySewa = $koneksi->prepare("
                        INSERT INTO sewa (id_pengguna, id_properti, jenis_durasi, jumlah_durasi, tanggal_mulai, tanggal_selesai, total_tagihan, batas_pembayaran, status_sewa) 
                        VALUES (:id_pengguna, :id_properti, :jenis_durasi, :jumlah_durasi, :tanggal_mulai, :tanggal_selesai, :total_tagihan, :batas_pembayaran, 'menunggu_pembayaran')
                    ");
                    $querySewa->execute([
                        ':id_pengguna' => $id_pengguna,
                        ':id_properti' => $id_properti,
                        ':jenis_durasi' => $jenis_durasi,
                        ':jumlah_durasi' => $jumlah_durasi,
                        ':tanggal_mulai' => $tanggal_mulai,
                        ':tanggal_selesai' => $tanggal_selesai,
                        ':total_tagihan' => $total_tagihan,
                        ':batas_pembayaran' => $batas_pembayaran
                    ]);

                    $queryProperti = $koneksi->prepare("UPDATE properti SET status_ketersediaan = 'terisi' WHERE id_properti = :id");
                    $queryProperti->execute([':id' => $id_properti]);

                    $koneksi->commit();
                    header("Location: penyewa/riwayat_sewa.php?status=sukses");
                    exit;
                } catch (Exception $e) {
                    $koneksi->rollBack();
                    $pesan_error = "Gagal memproses pemesanan: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($properti['nama_properti']); ?>  Detail Properti</title>
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
        .detail-img-placeholder {
            height: 380px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .detail-img-placeholder i {
            font-size: 8rem;
        }
        .card-booking {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            background-color: #fff;
        }
        .badge-ketersediaan {
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 600;
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
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['login'])): ?>
                        <span class="me-2 text-secondary small">Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></span>
                        <?php if ($_SESSION['peran'] === 'admin'): ?>
                            <a href="admin/index.php" class="btn btn-outline-primary rounded-pill px-3 btn-sm">Panel Admin</a>
                        <?php else: ?>
                            <a href="penyewa/index.php" class="btn btn-outline-primary rounded-pill px-3 btn-sm">Menu Penyewa</a>
                        <?php endif; ?>
                        <a href="keluar.php" class="btn btn-danger rounded-pill px-3 btn-sm">Keluar</a>
                    <?php else: ?>
                        <a href="masuk.php" class="btn btn-outline-primary rounded-pill px-4 btn-sm">Masuk</a>
                        <a href="daftar.php" class="btn btn-primary rounded-pill px-4 btn-sm">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="detail-img-placeholder mb-4" style="overflow: hidden;">
                    <?php if (!empty($properti['foto']) && file_exists('aset/foto_properti/' . $properti['foto']) && $properti['foto'] !== 'default.jpg'): ?>
                        <img src="aset/foto_properti/<?= htmlspecialchars($properti['foto']); ?>" alt="<?= htmlspecialchars($properti['nama_properti']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="bi <?= $properti['tipe'] === 'kost' ? 'bi-door-closed' : 'bi-house'; ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="bg-white p-4 rounded-4 shadow-sm mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h2 class="fw-bold text-dark mb-0"><?= htmlspecialchars($properti['nama_properti']); ?></h2>
                        <div>
                            <?php if ($properti['status_ketersediaan'] === 'tersedia'): ?>
                                <span class="badge-ketersediaan bg-success text-white">Tersedia</span>
                            <?php else: ?>
                                <span class="badge-ketersediaan bg-danger text-white">Terisi / Terkunci</span>
                            <?php endif; ?>
                            <span class="badge bg-primary px-3 py-2 rounded-pill text-capitalize fs-6"><?= htmlspecialchars($properti['tipe']); ?></span>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mt-4 mb-3">Deskripsi & Fasilitas</h5>
                    <p class="text-secondary" style="line-height: 1.8;">
                        <?= nl2br(htmlspecialchars($properti['deskripsi'])); ?>
                    </p>
                    <h5 class="fw-bold text-dark mt-4 mb-3">Daftar Tarif Sewa</h5>
                    <div class="row g-3">
                        <?php if ($properti['harga_harian']): ?>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center">
                                    <span class="text-secondary small d-block mb-1">Harian</span>
                                    <span class="fw-bold text-primary fs-5"><?= formatRupiah($properti['harga_harian']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($properti['harga_bulanan']): ?>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center">
                                    <span class="text-secondary small d-block mb-1">Bulanan</span>
                                    <span class="fw-bold text-primary fs-5"><?= formatRupiah($properti['harga_bulanan']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($properti['harga_tahunan']): ?>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center">
                                    <span class="text-secondary small d-block mb-1">Tahunan</span>
                                    <span class="fw-bold text-primary fs-5"><?= formatRupiah($properti['harga_tahunan']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-booking p-4">
                    <h4 class="fw-bold text-dark mb-4 text-center">Formulir Penyewaan</h4>
                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($pesan_error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($properti['status_ketersediaan'] === 'terisi'): ?>
                        <div class="alert alert-warning text-center" role="alert">
                            <i class="bi bi-lock-fill fs-3 mb-2 d-block"></i>
                            <strong>Unit Sudah Terisi</strong>
                            <p class="small text-muted mt-2 mb-0">Unit sedang aktif disewa.</p>
                            <?php if ($info_selesai_sewa): ?>
                                <hr>
                                <span class="small">Perkiraan tersedia kembali:<br><strong><?= tanggalIndo($info_selesai_sewa); ?></strong></span>
                            <?php endif; ?>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-left"></i> Cari Unit Lain</a>
                    <?php elseif (!isset($_SESSION['login'])): ?>
                        <div class="text-center p-3">
                            <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Silakan masuk ke akun Anda terlebih dahulu untuk menyewa properti ini.</p>
                            <a href="masuk.php" class="btn btn-primary w-100 rounded-3 mb-2">Masuk Akun</a>
                            <a href="daftar.php" class="btn btn-outline-primary w-100 rounded-3">Daftar Akun Baru</a>
                        </div>
                    
                    <?php elseif ($_SESSION['peran'] === 'admin'): ?>
                        <div class="alert alert-info text-center" role="alert">
                            Anda masuk sebagai <strong>Admin</strong>. Form booking hanya untuk penyewa.
                        </div>
                        <a href="admin/index.php" class="btn btn-primary w-100">Kembali ke Panel Admin</a>

                    <?php else: ?>
                        <form action="" method="POST" id="formBooking">
                            <div class="mb-3">
                                <label for="jenis_durasi" class="form-label text-secondary small fw-bold">Pilih Tipe Sewa</label>
                                <select class="form-select" id="jenis_durasi" name="jenis_durasi" required>
                                    <option value="" disabled selected>-- Pilih Durasi --</option>
                                    <?php if ($properti['harga_harian']): ?>
                                        <option value="harian" data-harga="<?= $properti['harga_harian']; ?>">Harian (<?= formatRupiah($properti['harga_harian']); ?>/hari)</option>
                                    <?php endif; ?>
                                    <?php if ($properti['harga_bulanan']): ?>
                                        <option value="bulanan" data-harga="<?= $properti['harga_bulanan']; ?>">Bulanan (<?= formatRupiah($properti['harga_bulanan']); ?>/bulan)</option>
                                    <?php endif; ?>
                                    <?php if ($properti['harga_tahunan']): ?>
                                        <option value="tahunan" data-harga="<?= $properti['harga_tahunan']; ?>">Tahunan (<?= formatRupiah($properti['harga_tahunan']); ?>/tahun)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_durasi" class="form-label text-secondary small fw-bold">Jumlah Durasi Sewa</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="jumlah_durasi" name="jumlah_durasi" min="1" value="1" required>
                                    <span class="input-group-text" id="labelSatuan">Satuan</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_mulai" class="form-label text-secondary small fw-bold">Tanggal Mulai Sewa</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" min="<?= date('Y-m-d'); ?>" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <hr class="my-4">
                            <div class="bg-light p-3 rounded-3 mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Tanggal Selesai:</span>
                                    <span id="outputTanggalSelesai" class="fw-bold text-dark small">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary small">Total Tagihan:</span>
                                    <span id="outputTotalTagihan" class="fw-bold text-primary">-</span>
                                </div>
                            </div>
                            <button type="submit" name="booking" class="btn btn-primary w-100 py-3 rounded-3 fw-bold">
                                Konfirmasi Pemesanan
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer py-4 bg-dark text-white-50 text-center">
        <div class="container">
            <h5 class="fw-bold mb-2 text-white">Kost & Kontrakan Pintar</h5>
            <p class="small mb-0">&copy; 2026 Kost & Kontrakan Pintar. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectDurasi = document.getElementById("jenis_durasi");
            const inputJumlah = document.getElementById("jumlah_durasi");
            const inputMulai = document.getElementById("tanggal_mulai");
            const labelSatuan = document.getElementById("labelSatuan");
            const outputSelesai = document.getElementById("outputTanggalSelesai");
            const outputTotal = document.getElementById("outputTotalTagihan");

            if (!selectDurasi) return;

            function hitungTagihanDanSelesai() {
                const opsiTerpilih = selectDurasi.options[selectDurasi.selectedIndex];
                if (!opsiTerpilih.value) {
                    outputSelesai.innerText = "-";
                    outputTotal.innerText = "-";
                    return;
                }

                const tipe = opsiTerpilih.value;
                const harga = parseFloat(opsiTerpilih.getAttribute("data-harga"));
                const jumlah = parseInt(inputJumlah.value) || 1;
                const tglMulaiStr = inputMulai.value;

                if (tipe === 'harian') labelSatuan.innerText = "Hari";
                else if (tipe === 'bulanan') labelSatuan.innerText = "Bulan";
                else if (tipe === 'tahunan') labelSatuan.innerText = "Tahun";

                const total = harga * jumlah;
                outputTotal.innerText = "Rp " + total.toLocaleString("id-ID");

                if (tglMulaiStr) {
                    const tglMulai = new Date(tglMulaiStr);
                    if (!isNaN(tglMulai.getTime())) {
                        if (tipe === 'harian') {
                            tglMulai.setDate(tglMulai.getDate() + jumlah);
                        } else if (tipe === 'bulanan') {
                            tglMulai.setMonth(tglMulai.getMonth() + jumlah);
                        } else if (tipe === 'tahunan') {
                            tglMulai.setFullYear(tglMulai.getFullYear() + jumlah);
                        }
                        const opsiFormat = { year: 'numeric', month: 'long', day: 'numeric' };
                        outputSelesai.innerText = tglMulai.toLocaleDateString("id-ID", opsiFormat);
                    }
                }
            }
            selectDurasi.addEventListener("change", hitungTagihanDanSelesai);
            inputJumlah.addEventListener("input", hitungTagihanDanSelesai);
            inputMulai.addEventListener("change", hitungTagihanDanSelesai);
            hitungTagihanDanSelesai();
        });
    </script>
</body>
</html>