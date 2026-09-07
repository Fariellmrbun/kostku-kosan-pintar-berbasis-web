<?php
session_start();
require_once __DIR__ . '/../konfigurasi/koneksi.php';
require_once __DIR__ . '/../konfigurasi/fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'penyewa') {
    header("Location: ../login.php");
    exit;
}
$id_pengguna_aktif = $_SESSION['id_pengguna'];
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: riwayat_sewa.php");
    exit;
}
$id_sewa = (int)$_GET['id'];
$querySewa = $koneksi->prepare("
    SELECT s.*, p.nama_properti, p.tipe 
    FROM sewa s 
    JOIN properti p ON s.id_properti = p.id_properti 
    WHERE s.id_sewa = :id_sewa AND s.id_pengguna = :id_pengguna AND s.status_sewa = 'menunggu_pembayaran'
");
$querySewa->execute([
    ':id_sewa' => $id_sewa,
    ':id_pengguna' => $id_pengguna_aktif
]);
$sewa = $querySewa->fetch();

if (!$sewa) {
    header("Location: riwayat_sewa.php");
    exit;
}
$pesan_error = "";
if (isset($_POST['unggah'])) {
    $metode_pembayaran = trim($_POST['metode_pembayaran']);
      $jumlah_bayar = $sewa['total_tagihan'];
       $catatan = trim($_POST['catatan']);
        $nama_file = $_FILES['bukti_transfer']['name'];
         $ukuran_file = $_FILES['bukti_transfer']['size'];
             $error_file = $_FILES['bukti_transfer']['error'];
                   $tmp_file = $_FILES['bukti_transfer']['tmp_name'];

          $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png'];
           $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
       if (empty($metode_pembayaran)) {
        $pesan_error = "Harap pilih metode pembayaran.";
    } elseif ($error_file === 4) {
        $pesan_error = "Harap unggah berkas bukti transfer.";
    } elseif (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
        $pesan_error = "Format file bukti transfer harus berupa JPG, JPEG, atau PNG!";
    } elseif ($ukuran_file > 2097152) { // 2MB
        $pesan_error = "Ukuran file bukti transfer maksimal adalah 2 Megabytes (2 MB)!";
    } else {
        $nama_file_baru = "bukti_" . $id_sewa . "_" . time() . "." . $ekstensi;
        $tujuan_upload = __DIR__ . "/../aset/bukti_transfer/" . $nama_file_baru;

        if (move_uploaded_file($tmp_file, $tujuan_upload)) {
            try {
                $koneksi->beginTransaction();
                $queryBayar = $koneksi->prepare("
                    INSERT INTO pembayaran (id_sewa, jumlah_bayar, tanggal_bayar, metode_pembayaran, bukti_transfer, catatan) 
                    VALUES (:id_sewa, :jumlah_bayar, NOW(), :metode, :bukti, :catatan)
                ");
                $queryBayar->execute([
                    ':id_sewa' => $id_sewa,
                    ':jumlah_bayar' => $jumlah_bayar,
                    ':metode' => $metode_pembayaran,
                    ':bukti' => $nama_file_baru,
                    ':catatan' => $catatan
                ]);
                $queryUpdateSewa = $koneksi->prepare("UPDATE sewa SET status_sewa = 'proses_verifikasi' WHERE id_sewa = :id_sewa");
                $queryUpdateSewa->execute([':id_sewa' => $id_sewa]);

                $koneksi->commit();
                header("Location: riwayat_sewa.php?status=bayar_sukses");
                exit;
            } catch (Exception $e) {
                $koneksi->rollBack();
                if (file_exists($tujuan_upload)) {
                    unlink($tujuan_upload);
                }
                $pesan_error = "Gagal memproses data pembayaran: " . $e->getMessage();
            }
        } else {
            $pesan_error = "Gagal mengunggah file bukti transfer ke server.";
        }
    }
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="card content-card">
    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 text-primary me-2"></i>Formulir Pembayaran Sewa</h4>
    <p class="text-muted">Lakukan transfer pembayaran sesuai detail tagihan di bawah ini lalu unggah buktinya.</p>
    <hr class="my-4">
    <div class="row g-4">
        <!-- Rincian -->
        <div class="col-md-5">
            <div class="bg-light p-4 rounded-3 mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-receipt me-1"></i> Rincian Tagihan</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-secondary small">Nama Properti</td>
                        <td class="fw-bold text-dark">: <?= htmlspecialchars($sewa['nama_properti']); ?></td>
                    </tr>
                    <tr>
                        <td class="text-secondary small">Tipe Properti</td>
                        <td class="text-capitalize">: <?= htmlspecialchars($sewa['tipe']); ?></td>
                    </tr>
                    <tr>
                        <td class="text-secondary small">Durasi Sewa</td>
                        <td>: <?= $sewa['jumlah_durasi'] . ' ' . $sewa['jenis_durasi']; ?></td>
                    </tr>
                    <tr>
                        <td class="text-secondary small">Periode Sewa</td>
                        <td>: <span class="small"><?= tanggalIndo($sewa['tanggal_mulai']) . ' s/d ' . tanggalIndo($sewa['tanggal_selesai']); ?></span></td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-secondary small pt-2">Total Harus Bayar</td>
                        <td class="fw-bold text-primary fs-5 pt-2">: <?= formatRupiah($sewa['total_tagihan']); ?></td>
                    </tr>
                </table>
            </div>
            <div class="card border-primary-subtle p-3" style="background-color: #f0f7ff;">
                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-bank"></i> Rekening Pembayaran (Pihak Pengelola)</h6>
                <p class="small text-dark mb-0">
                    Harap transfer tepat sebesar <strong class="text-primary"><?= formatRupiah($sewa['total_tagihan']); ?></strong> ke rekening di bawah ini:
                </p>
                <div class="mt-3 bg-white p-2 rounded border">
                    <span class="small text-secondary d-block">Bank BSI</span>
                    <strong class="text-dark">7314586861</strong>
                    <span class="small text-muted d-block">a/n Pengelola Kost & Kontrakan Pintar</span>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($pesan_error); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="jumlah_bayar" class="form-label text-secondary small fw-bold">Nominal Pembayaran (Pas)</label>
                    <input type="text" class="form-control bg-light" id="jumlah_bayar" value="<?= formatRupiah($sewa['total_tagihan']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="metode_pembayaran" class="form-label text-secondary small fw-bold">Metode Pengiriman Transfer</label>
                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="" disabled selected>Pilih Bank Pengirim / E-Wallet</option>
                        <option value="Transfer Bank Mandiri">Transfer Bank Mandiri</option>
                        <option value="Transfer Bank BCA">Transfer Bank BCA</option>
                        <option value="Transfer Bank BRI">Transfer Bank BRI</option>
                        <option value="Transfer Bank BNI">Transfer Bank BNI</option>
                        <option value="E-Wallet (OVO/Dana/Gopay)">E-Wallet (OVO / Dana / Gopay)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bukti_transfer" class="form-label text-secondary small fw-bold">Unggah Bukti Transfer (Foto/Gambar)</label>
                    <input class="form-control" type="file" id="bukti_transfer" name="bukti_transfer" accept="image/png, image/jpeg, image/jpg" required>
                    <div class="form-text small text-muted">Format file yang didukung: PNG, JPG, JPEG. Maksimal ukuran file 2 MB.</div>
                </div>
                <div class="mb-4">
                    <label for="catatan" class="form-label text-secondary small fw-bold">Catatan Pembayaran (Opsional)</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: Pembayaran Kost Kamar A1 atas nama Budi."></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="unggah" class="btn btn-primary px-4 rounded-pill"><i class="bi bi-send-fill me-1"></i> Unggah Bukti Pembayaran</button>
                    <a href="riwayat_sewa.php" class="btn btn-outline-secondary px-4 rounded-pill">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';
?>