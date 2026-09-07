<?php
$host = "localhost";
$username = "root";
$password = "";
$db_name = "db_kost_kontrakan";

try {
    $koneksi = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $koneksi->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $tabelAda = $koneksi->query("SHOW TABLES LIKE 'pengguna'")->rowCount() > 0;
    if ($tabelAda) {
        $queryCek = $koneksi->prepare("SELECT COUNT(*) FROM pengguna WHERE username = :username");
        $queryCek->execute([':username' => 'admin123']);
        
        if ($queryCek->fetchColumn() == 0) {
            $nama = "Pengelola Kost & Kontrakan";
            $userAdmin = "admin123";
            $passAdmin = password_hash("KPR2", PASSWORD_DEFAULT);
            $email = "admin@kostkontrakan.com";
            $hp = "08123456789";
            $peran = "admin";

            $queryInsert = $koneksi->prepare("INSERT INTO pengguna (nama_lengkap, username, password, email, nomor_hp, peran) VALUES (?, ?, ?, ?, ?, ?)");
            $queryInsert->execute([$nama, $userAdmin, $passAdmin, $email, $hp, $peran]);
        }
    }
} catch (PDOException $e) {
    die("Koneksia Anda ke database gagal: " . $e->getMessage() . "<br><br><strong>Catatan:</strong> Pastikan database db_kost_kontrakan sudah dibuat di phpmyadmin dan import file database.sql.");
}
?>
