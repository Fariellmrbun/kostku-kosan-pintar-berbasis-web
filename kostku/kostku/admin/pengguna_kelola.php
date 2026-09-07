<?php
require_once __DIR__ . '/layout/header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$pesan_sukses = "";
$pesan_error = "";

$id_pengguna_login = $_SESSION['id_pengguna'];
if ($action === 'hapus' && isset($_GET['id'])) {
    $id_hapus = (int)$_GET['id'];
    
    if ($id_hapus === $id_pengguna_login) {
        $pesan_error = "Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif!";
    } else {
        try {
            $queryDelete = $koneksi->prepare("DELETE FROM pengguna WHERE id_pengguna = :id");
            $queryDelete->execute([':id' => $id_hapus]);
            $pesan_sukses = "Pengguna berhasil dihapus!";
        } catch (Exception $e) {
            $pesan_error = "Gagal menghapus pengguna: " . $e->getMessage();
        }
    }
    $action = '';
}
if (isset($_POST['tambah_pengguna'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $password = $_POST['password'];
    $peran = $_POST['peran'];

    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($nomor_hp) || empty($password) || empty($peran)) {
        $pesan_error = "Semua kolom formulir wajib diisi!";
    } else {
        try {
            $queryCek = $koneksi->prepare("SELECT COUNT(*) FROM pengguna WHERE username = ?");
            $queryCek->execute([$username]);
            if ($queryCek->fetchColumn() > 0) {
                $pesan_error = "Username sudah digunakan oleh akun lain!";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $queryInsert = $koneksi->prepare("
                    INSERT INTO pengguna (nama_lengkap, username, password, email, nomor_hp, peran) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $queryInsert->execute([$nama_lengkap, $username, $password_hash, $email, $nomor_hp, $peran]);
                
                $pesan_sukses = "Pengguna baru berhasil ditambahkan!";
                $action = '';
            }
        } catch (Exception $e) {
            $pesan_error = "Gagal menambahkan pengguna: " . $e->getMessage();
        }
    }
}
if (isset($_POST['edit_pengguna'])) {
    $id_edit = (int)$_POST['id_pengguna'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $password_baru = $_POST['password_baru'];
    $peran = $_POST['peran'];

    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($nomor_hp) || empty($peran)) {
        $pesan_error = "Semua kolom wajib diisi!";
    } else {
        try {
            $queryCek = $koneksi->prepare("SELECT COUNT(*) FROM pengguna WHERE username = ? AND id_pengguna != ?");
            $queryCek->execute([$username, $id_edit]);
            if ($queryCek->fetchColumn() > 0) {
                $pesan_error = "Username sudah digunakan oleh akun lain!";
            } else {
                if (!empty($password_baru)) {
                    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                    $queryUpdate = $koneksi->prepare("
                        UPDATE pengguna 
                        SET nama_lengkap = ?, username = ?, password = ?, email = ?, nomor_hp = ?, peran = ? 
                        WHERE id_pengguna = ?
                    ");
                    $queryUpdate->execute([$nama_lengkap, $username, $password_hash, $email, $nomor_hp, $peran, $id_edit]);
                } else {
                    $queryUpdate = $koneksi->prepare("
                        UPDATE pengguna 
                        SET nama_lengkap = ?, username = ?, email = ?, nomor_hp = ?, peran = ? 
                        WHERE id_pengguna = ?
                    ");
                    $queryUpdate->execute([$nama_lengkap, $username, $email, $nomor_hp, $peran, $id_edit]);
                }
                
                $pesan_sukses = "Data pengguna berhasil diperbarui!";
        
                if ($id_edit === $id_pengguna_login) {
                    $_SESSION['nama_lengkap'] = $nama_lengkap;
                    $_SESSION['username'] = $username;
                    $_SESSION['peran'] = $peran;
                }

                $action = '';
            }
        } catch (Exception $e) {
            $pesan_error = "Gagal memperbarui pengguna: " . $e->getMessage();
        }
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0"><i class="bi bi-people text-primary me-2"></i>Kelola Data Pengguna</h3>
    <?php if (empty($action)): ?>
        <a href="pengguna_kelola.php?action=tambah" class="btn btn-primary rounded-pill"><i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna</a>
    <?php else: ?>
        <a href="pengguna_kelola.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
    <?php endif; ?>
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
<?php if ($action === 'tambah'): ?>
    <div class="card main-card">
        <h5 class="fw-bold text-dark mb-4">Tambah Akun Pengguna Baru</h5>
        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Masukkan username" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Alamat Email</label>
                    <input type="email" class="form-control" name="email" placeholder="contoh: email@admin.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Nomor Handphone</label>
                    <input type="text" class="form-control" name="nomor_hp" placeholder="Contoh: 0812345678" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Password Akun</label>
                    <input type="password" class="form-control" name="password" placeholder="Minimal 4 karakter" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Peran Hak Akses</label>
                    <select class="form-select" name="peran" required>
                        <option value="penyewa">Penyewa (User Biasa)</option>
                        <option value="admin">Admin (Pengelola)</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 text-end">
                <button type="submit" name="tambah_pengguna" class="btn btn-primary px-4 rounded-pill">Simpan Pengguna</button>
            </div>
        </form>
    </div>
<?php elseif ($action === 'edit' && isset($_GET['id'])): 
    $id_edit = (int)$_GET['id'];
    $querySel = $koneksi->prepare("SELECT * FROM pengguna WHERE id_pengguna = :id");
    $querySel->execute([':id' => $id_edit]);
    $uData = $querySel->fetch();
    
    if (!$uData) {
        header("Location: pengguna_kelola.php");
        exit;
    }
?>
    <div class="card main-card">
        <h5 class="fw-bold text-dark mb-4">Edit Informasi Pengguna</h5>
        <form action="" method="POST">
            <input type="hidden" name="id_pengguna" value="<?= $uData['id_pengguna']; ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($uData['nama_lengkap']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Username</label>
                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($uData['username']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Alamat Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($uData['email']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Nomor Handphone</label>
                    <input type="text" class="form-control" name="nomor_hp" value="<?= htmlspecialchars($uData['nomor_hp']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Perbarui Password (Biarkan kosong jika tidak diubah)</label>
                    <input type="password" class="form-control" name="password_baru" placeholder="Masukkan password baru jika ingin diganti">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small fw-bold">Peran Hak Akses</label>
                    <select class="form-select" name="peran" required>
                        <option value="penyewa" <?= $uData['peran'] === 'penyewa' ? 'selected' : ''; ?>>Penyewa (User Biasa)</option>
                        <option value="admin" <?= $uData['peran'] === 'admin' ? 'selected' : ''; ?>>Admin (Pengelola)</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" name="edit_pengguna" class="btn btn-primary px-4 rounded-pill">Simpan Perubahan</button>
            </div>
        </form>
    </div>
<?php else: 
    $daftarPengguna = $koneksi->query("SELECT * FROM pengguna ORDER BY id_pengguna DESC")->fetchAll();
?>
    <div class="card main-card">
        <?php if (count($daftarPengguna) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Kontak HP</th>
                            <th>Peran / Hak Akses</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarPengguna as $usr): ?>
                            <tr>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($usr['nama_lengkap']); ?></strong>
                                    <?php if ($usr['id_pengguna'] === $id_pengguna_login): ?>
                                        <span class="badge bg-success small" style="font-size: 0.7rem;">Akun Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars($usr['username']); ?></code></td>
                                <td><?= htmlspecialchars($usr['email']); ?></td>
                                <td><?= htmlspecialchars($usr['nomor_hp']); ?></td>
                                <td>
                                    <?php if ($usr['peran'] === 'admin'): ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-1.5 small">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-white rounded-pill px-3 py-1.5 small">Penyewa</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="small text-muted"><?= tanggalIndo($usr['tanggal_daftar']); ?></span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="pengguna_kelola.php?action=edit&id=<?= $usr['id_pengguna']; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil-square"></i></a>
                                        <?php if ($usr['id_pengguna'] !== $id_pengguna_login): ?>
                                            <a href="pengguna_kelola.php?action=hapus&id=<?= $usr['id_pengguna']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Semua data sewa pengguna ini juga akan ikut terhapus!');"><i class="bi bi-trash"></i></a>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-trash"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 3.5rem;"></i>
                <h6 class="mt-3 text-secondary">Belum ada pengguna terdaftar.</h6>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php
require_once __DIR__ . '/layout/footer.php';
?>
