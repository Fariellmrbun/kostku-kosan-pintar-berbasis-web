<?php
session_start();
require_once __DIR__ . '/../konfigurasi/koneksi.php';
require_once __DIR__ . '/../konfigurasi/fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: ../masuk.php");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$pesan_sukses = "";
$pesan_error = "";
if ($action === 'hapus' && isset($_GET['id'])) {
    $id_properti = (int)$_GET['id'];
    $queryFoto = $koneksi->prepare("SELECT foto FROM properti WHERE id_properti = :id");
    $queryFoto->execute([':id' => $id_properti]);
    $foto = $queryFoto->fetchColumn();
    try {
        $queryDelete = $koneksi->prepare("DELETE FROM properti WHERE id_properti = :id");
        $queryDelete->execute([':id' => $id_properti]);
        
        if ($foto && !in_array($foto, ['kost_a1.jpg', 'kost_a2.jpg', 'kontrakan_budi_1.jpg', 'kontrakan_asri.jpg', 'default.jpg'])) {
            $pathFoto = __DIR__ . '/../aset/foto_properti/' . $foto;
            if (file_exists($pathFoto)) {
                unlink($pathFoto);
            }
        }
        
        $pesan_sukses = "Properti berhasil dihapus!";
        $action = '';
    } catch (Exception $e) {
        $pesan_error = "Gagal menghapus properti: " . $e->getMessage();
    }
}
if (isset($_POST['tambah_properti'])) {
    $nama_properti = trim($_POST['nama_properti']);
    $tipe = $_POST['tipe'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga_harian = !empty($_POST['harga_harian']) ? $_POST['harga_harian'] : null;
    $harga_bulanan = !empty($_POST['harga_bulanan']) ? $_POST['harga_bulanan'] : null;
    $harga_tahunan = !empty($_POST['harga_tahunan']) ? $_POST['harga_tahunan'] : null;
    $status_ketersediaan = $_POST['status_ketersediaan'];
    
    if (empty($nama_properti) || empty($tipe) || empty($deskripsi)) {
        $pesan_error = "Nama, tipe, dan deskripsi wajib diisi!";
    } elseif (empty($harga_harian) && empty($harga_bulanan) && empty($harga_tahunan)) {
        $pesan_error = "Harus mengisi minimal salah satu tarif (Harian, Bulanan, atau Tahunan)!";
    } else {
        $foto_db = 'default.jpg';
        
        if ($_FILES['foto']['error'] !== 4) {
            $nama_file = $_FILES['foto']['name'];
            $tmp_file = $_FILES['foto']['tmp_name'];
            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            
            if (in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
                $foto_db = "prop_" . time() . "." . $ekstensi;
                if (!file_exists(__DIR__ . "/../aset/foto_properti")) {
                    mkdir(__DIR__ . "/../aset/foto_properti", 0777, true);
                }
                move_uploaded_file($tmp_file, __DIR__ . "/../aset/foto_properti/" . $foto_db);
            }
        }

        try {
            $queryInsert = $koneksi->prepare("
                INSERT INTO properti (nama_properti, tipe, deskripsi, harga_harian, harga_bulanan, harga_tahunan, status_ketersediaan, foto) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $queryInsert->execute([$nama_properti, $tipe, $deskripsi, $harga_harian, $harga_bulanan, $harga_tahunan, $status_ketersediaan, $foto_db]);
            
            $pesan_sukses = "Properti baru berhasil ditambahkan!";
            $action = '';
        } catch (Exception $e) {
            $pesan_error = "Gagal menambah properti: " . $e->getMessage();
        }
    }
}
if (isset($_POST['edit_properti'])) {
    $id_properti = (int)$_POST['id_properti'];
    $nama_properti = trim($_POST['nama_properti']);
    $tipe = $_POST['tipe'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga_harian = !empty($_POST['harga_harian']) ? $_POST['harga_harian'] : null;
    $harga_bulanan = !empty($_POST['harga_bulanan']) ? $_POST['harga_bulanan'] : null;
    $harga_tahunan = !empty($_POST['harga_tahunan']) ? $_POST['harga_tahunan'] : null;
    $status_ketersediaan = $_POST['status_ketersediaan'];
    $foto_lama = $_POST['foto_lama'];
    
    if (empty($nama_properti) || empty($tipe) || empty($deskripsi)) {
        $pesan_error = "Nama, tipe, dan deskripsi wajib diisi!";
    } elseif (empty($harga_harian) && empty($harga_bulanan) && empty($harga_tahunan)) {
        $pesan_error = "Harus mengisi minimal salah satu tarif (Harian, Bulanan, atau Tahunan)!";
    } else {
        $foto_db = $foto_lama;

        if ($_FILES['foto']['error'] !== 4) {
            $nama_file = $_FILES['foto']['name'];
            $tmp_file = $_FILES['foto']['tmp_name'];
            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            
            if (in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
                $foto_db = "prop_" . time() . "." . $ekstensi;
                if (!file_exists(__DIR__ . "/../aset/foto_properti")) {
                    mkdir(__DIR__ . "/../aset/foto_properti", 0777, true);
                }
                move_uploaded_file($tmp_file, __DIR__ . "/../aset/foto_properti/" . $foto_db);
                
                if ($foto_lama && !in_array($foto_lama, ['kost_a1.jpg', 'kost_a2.jpg', 'kontrakan_budi_1.jpg', 'kontrakan_asri.jpg', 'default.jpg'])) {
                    $pathFotoLama = __DIR__ . '/../aset/foto_properti/' . $foto_lama;
                    if (file_exists($pathFotoLama)) {
                        unlink($pathFotoLama);
                    }
                }
            }
        }

        try {
            $queryUpdate = $koneksi->prepare("
                UPDATE properti 
                SET nama_properti = ?, tipe = ?, deskripsi = ?, harga_harian = ?, harga_bulanan = ?, harga_tahunan = ?, status_ketersediaan = ?, foto = ? 
                WHERE id_properti = ?
            ");
            $queryUpdate->execute([$nama_properti, $tipe, $deskripsi, $harga_harian, $harga_bulanan, $harga_tahunan, $status_ketersediaan, $foto_db, $id_properti]);
            
            $pesan_sukses = "Properti berhasil diperbarui!";
            $action = '';
        } catch (Exception $e) {
            $pesan_error = "Gagal memperbarui properti: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-door-closed text-primary me-2"></i>Kelola Properti Kost & Kontrakan</h3>
    <?php if (empty($action)): ?>
        <a href="properti_kelola.php?action=tambah" class="btn btn-primary rounded-pill"><i class="bi bi-plus-circle me-1"></i> Tambah Properti</a>
    <?php else: ?>
        <a href="properti_kelola.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
    <?php endif; ?>
</div>

<?php if (!empty($pesan_sukses)): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <?= htmlspecialchars($pesan_sukses); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($pesan_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <?= htmlspecialchars($pesan_error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($action === 'tambah'): ?>
    <div class="card main-card">
        <h5 class="fw-bold text-dark mb-4">Tambah Unit Properti Baru</h5>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Nama Properti</label>
                    <input type="text" class="form-control" name="nama_properti" placeholder="Contoh: Kost Melati Kamar A3" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Tipe Hunian</label>
                    <select class="form-select" name="tipe" required>
                        <option value="kost">Kost</option>
                        <option value="kontrakan">Kontrakan</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <label class="form-label text-secondary small fw-bold">Deskripsi / Detail Fasilitas</label>
                    <textarea class="form-control" name="deskripsi" rows="4" placeholder="Tulis deskripsi fasilitas unit di sini..." required></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-bold">Tarif Harian</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="harga_harian" placeholder="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-bold">Tarif Bulanan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="harga_bulanan" placeholder="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-bold">Tarif Tahunan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="harga_tahunan" placeholder="0">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Status Ketersediaan Awal</label>
                    <select class="form-select" name="status_ketersediaan" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="terisi">Terisi</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Foto Properti</label>
                    <input class="form-control" type="file" name="foto" accept="image/*">
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" name="tambah_properti" class="btn btn-primary px-4 rounded-pill">Simpan Properti</button>
            </div>
        </form>
    </div>
<?php elseif ($action === 'edit' && isset($_GET['id'])): 
    $id_properti = (int)$_GET['id'];
    $querySel = $koneksi->prepare("SELECT * FROM properti WHERE id_properti = :id");
    $querySel->execute([':id' => $id_properti]);
    $propData = $querySel->fetch();
    
    if (!$propData) {
        header("Location: properti_kelola.php");
        exit;
    }
?>
    <div class="card main-card">
        <h5 class="fw-bold text-dark mb-4">Edit Unit Properti</h5>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_properti" value="<?= $propData['id_properti']; ?>">
            <input type="hidden" name="foto_lama" value="<?= $propData['foto']; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Nama Properti</label>
                    <input type="text" class="form-control" name="nama_properti" value="<?= htmlspecialchars($propData['nama_properti']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Tipe Hunian</label>
                    <select class="form-select" name="tipe" required>
                        <option value="kost" <?= $propData['tipe'] === 'kost' ? 'selected' : ''; ?>>Kost</option>
                        <option value="kontrakan" <?= $propData['tipe'] === 'kontrakan' ? 'selected' : ''; ?>>Kontrakan</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <label class="form-label text-secondary small fw-bold">Deskripsi / Detail Fasilitas</label>
                    <textarea class="form-control" name="deskripsi" rows="4" required><?= htmlspecialchars($propData['deskripsi']); ?></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-bold">Tarif Harian</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="harga_harian" value="<?= $propData['harga_harian']; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-bold">Tarif Bulanan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="harga_bulanan" value="<?= $propData['harga_bulanan']; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-secondary small fw-bold">Tarif Tahunan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="harga_tahunan" value="<?= $propData['harga_tahunan']; ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Status Ketersediaan</label>
                    <select class="form-select" name="status_ketersediaan" required>
                        <option value="tersedia" <?= $propData['status_ketersediaan'] === 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="terisi" <?= $propData['status_ketersediaan'] === 'terisi' ? 'selected' : ''; ?>>Terisi (Terkunci)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Ganti Foto Properti (Biarkan kosong jika tetap)</label>
                    <input class="form-control" type="file" name="foto" accept="image/*">
                    <span class="small text-muted d-block mt-1">Foto saat ini: <code><?= htmlspecialchars($propData['foto']); ?></code></span>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" name="edit_properti" class="btn btn-primary px-4 rounded-pill">Simpan Perubahan</button>
            </div>
        </form>
    </div>
<?php else: 
    $daftarProperti = $koneksi->query("SELECT * FROM properti ORDER BY id_properti DESC")->fetchAll();
?>
    <div class="card main-card">
        <?php if (count($daftarProperti) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Foto</th>
                            <th>Nama Unit</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Tarif Sewa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarProperti as $p): ?>
                            <tr>
                                <td style="width: 80px;">
                                    <div class="bg-secondary bg-opacity-25 rounded-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; overflow: hidden;">
                                        <?php if (!empty($p['foto']) && file_exists('../aset/foto_properti/' . $p['foto']) && $p['foto'] !== 'default.jpg'): ?>
                                            <img src="../aset/foto_properti/<?= htmlspecialchars($p['foto']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="bi <?= $p['tipe'] === 'kost' ? 'bi-door-closed' : 'bi-house'; ?> fs-3 text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($p['nama_properti']); ?></strong>
                                    <span class="text-muted small"><?= htmlspecialchars(substr($p['deskripsi'], 0, 60)) . (strlen($p['deskripsi']) > 60 ? '...' : ''); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-capitalize small">
                                        <?= htmlspecialchars($p['tipe']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($p['status_ketersediaan'] === 'tersedia'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">Terisi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small">
                                        <?php if ($p['harga_harian']): ?>
                                            <div>Harian: <strong class="text-primary"><?= formatRupiah($p['harga_harian']); ?></strong></div>
                                        <?php endif; ?>
                                        <?php if ($p['harga_bulanan']): ?>
                                            <div>Bulanan: <strong class="text-primary"><?= formatRupiah($p['harga_bulanan']); ?></strong></div>
                                        <?php endif; ?>
                                        <?php if ($p['harga_tahunan']): ?>
                                            <div>Tahunan: <strong class="text-primary"><?= formatRupiah($p['harga_tahunan']); ?></strong></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="properti_kelola.php?action=edit&id=<?= $p['id_properti']; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                                        <a href="properti_kelola.php?action=hapus&id=<?= $p['id_properti']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus properti ini?');"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-door-closed text-muted" style="font-size: 3.5rem;"></i>
                <h6 class="mt-3 text-secondary">Belum ada data properti yang didaftarkan.</h6>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php
require_once __DIR__ . '/layout/footer.php';
?>