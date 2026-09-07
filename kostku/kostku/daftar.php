<?php
session_start();
require_once 'konfigurasi/koneksi.php';
require_once 'konfigurasi/fungsi.php';

cekStatusKadaluarsa($koneksi);
if (isset($_SESSION['login'])) {
    if ($_SESSION['peran'] === 'admin') {
        header("Location: admin/index.php");
        exit;
    } else {
        header("Location: penyewa/index.php");
        exit;
    }
}
$pesan_error = "";
$pesan_sukses = "";
if (isset($_POST['daftar'])) {
       $nama_lengkap = trim($_POST['nama_lengkap']);
       $username = trim($_POST['username']);
       $email = trim($_POST['email']);
       $nomor_hp = trim($_POST['nomor_hp']);
       $password = $_POST['password'];
       $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($nomor_hp) || empty($password)) {
        $pesan_error = "Semua bidang wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format email tidak valid!";
    } elseif ($password !== $konfirmasi_password) {
        $pesan_error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 4) {
        $pesan_error = "Password minimal harus 4 karakter!";
    } else {
        try {
            $queryCek = $koneksi->prepare("SELECT COUNT(*) FROM pengguna WHERE username = :username");
            $queryCek->execute([':username' => $username]);
            if ($queryCek->fetchColumn() > 0) {
                $pesan_error = "Username sudah terdaftar! Gunakan username lain.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $peran = 'penyewa';
                $queryInsert = $koneksi->prepare("
                    INSERT INTO pengguna (nama_lengkap, username, password, email, nomor_hp, peran) 
                    VALUES (:nama_lengkap, :username, :password, :email, :nomor_hp, :peran)
                ");
                $queryInsert->execute([
                    ':nama_lengkap' => $nama_lengkap,
                    ':username' => $username,
                    ':password' => $password_hash,
                    ':email' => $email,
                    ':nomor_hp' => $nomor_hp,
                    ':peran' => $peran
                ]);

                $pesan_sukses = "Pendaftaran berhasil! Silakan masuk ke akun Anda.";
            }
        } catch (PDOException $e) {
            $pesan_error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Kost & Kontrakan Pintar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            padding: 40px 0;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            padding: 40px 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .register-card:hover {
            transform: translateY(-5px);
        }
        .brand-logo {
            font-size: 3rem;
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #2a5298, #1e3c72);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.4);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(30, 60, 114, 0.25);
            border-color: #1e3c72;
        }
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .back-home:hover {
            color: #f8f9fa;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home"><i class="bi bi-arrow-left"></i> Beranda</a>
    <div class="container d-flex justify-content-center">
        <div class="register-card">
            <div class="text-center mb-4">
                <div class="brand-logo"><i class="bi bi-person-plus-fill"></i></div>
                <h4 class="fw-bold text-dark">Daftar Akun Baru</h4>
                <p class="text-muted small">Lengkapi data diri Anda untuk menyewa kost atau kontrakan</p>
            </div>
            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($pesan_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($pesan_sukses); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label text-secondary small fw-bold">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap Anda" value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label text-secondary small fw-bold">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Buat username unik" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label text-secondary small fw-bold">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="contoh: email@anda.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nomor_hp" class="form-label text-secondary small fw-bold">Nomor Handphone</label>
                    <input type="text" class="form-control" id="nomor_hp" name="nomor_hp" placeholder="Contoh: 0812XXXXXXXX" value="<?= isset($_POST['nomor_hp']) ? htmlspecialchars($_POST['nomor_hp']) : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label text-secondary small fw-bold">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 4 karakter" required>
                </div>
                <div class="mb-4">
                    <label for="konfirmasi_password" class="form-label text-secondary small fw-bold">Ulangi Password</label>
                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password" required>
                </div>
                <button type="submit" name="daftar" class="btn btn-primary w-100 mb-3">Daftar Sekarang</button>
            </form>
            <div class="text-center mt-2">
                <p class="text-muted small">Sudah punya akun? <a href="masuk.php" class="text-primary fw-bold text-decoration-none">Masuk di sini</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
