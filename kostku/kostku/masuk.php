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
if (isset($_POST['masuk'])) {
    $username = trim($_POST['username']);
       $password = $_POST['password'];
         if (empty($username) || empty($password)) {
          $pesan_error = "Username dan password tidak boleh kosong!";
     } else {
           $query = $koneksi->prepare("SELECT * FROM pengguna WHERE username = :username");
            $query->execute([':username' => $username]);
               $pengguna = $query->fetch();

        if ($pengguna && password_verify($password, $pengguna['password'])) {
           
            $_SESSION['login'] = true;
            $_SESSION['id_pengguna'] = $pengguna['id_pengguna'];
            $_SESSION['nama_lengkap'] = $pengguna['nama_lengkap'];
            $_SESSION['username'] = $pengguna['username'];
            $_SESSION['peran'] = $pengguna['peran'];

            if ($pengguna['peran'] === 'admin') {
                header("Location: admin/index.php");
                exit;
            } else {
                header("Location: penyewa/index.php");
                exit;
            }
        } else {
            $pesan_error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Kost & Kontrakan Pintar</title>
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
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            padding: 40px 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .login-card:hover {
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
        <div class="login-card">
            <div class="text-center mb-4">
                <div class="brand-logo"><i class="bi bi-houses-fill"></i></div>
                <h4 class="fw-bold text-dark">Masuk Akun</h4>
                <p class="text-muted small">Silakan masuk untuk mengelola kost & kontrakan</p>
            </div>
            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($pesan_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label text-secondary small fw-bold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;"><i class="bi bi-person text-secondary"></i></span>
                        <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Masukkan username" required style="border-radius: 0 10px 10px 0;">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label text-secondary small fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;"><i class="bi bi-lock text-secondary"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Masukkan password" required style="border-radius: 0 10px 10px 0;">
                    </div>
                </div>
                <button type="submit" name="masuk" class="btn btn-primary w-100 mb-3">Masuk Sekarang</button>
            </form>
            <div class="text-center mt-3">
                <p class="text-muted small">Belum punya akun? <a href="daftar.php" class="text-primary fw-bold text-decoration-none">Daftar Sekarang</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
