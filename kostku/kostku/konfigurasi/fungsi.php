<?php
date_default_timezone_set('Asia/Jakarta');
function formatRupiah($angka) {
    if ($angka === null) return "Tidak Ada";
    return "Rp " . number_format($angka, 0, ',', '.');
}
function tanggalIndo($tanggal) {
    if (!$tanggal) return "-";

    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulanIndo[(int)$split[1]] . ' ' . $split[0];
}

function tanggalWaktuIndo($datetime) {
    if (!$datetime) return "-";
    $tanggal = date('Y-m-d', strtotime($datetime));
    $waktu = date('H:i', strtotime($datetime));
    return tanggalIndo($tanggal) . ' pukul ' . $waktu . ' WIB';
}

function hitungTanggalSelesai($tanggal_mulai, $jenis_durasi, $jumlah_durasi) {
    $mulai = strtotime($tanggal_mulai);
    $jumlah = (int)$jumlah_durasi;
    
    if ($jenis_durasi === 'harian') {
        return date('Y-m-d', strtotime("+$jumlah days", $mulai));
    } elseif ($jenis_durasi === 'bulanan') {
        return date('Y-m-d', strtotime("+$jumlah months", $mulai));
    } elseif ($jenis_durasi === 'tahunan') {
        return date('Y-m-d', strtotime("+$jumlah years", $mulai));
    }
    return $tanggal_mulai;
}

function cekStatusKadaluarsa($koneksi) {
    $sekarang = date('Y-m-d H:i:s');
    $hariIni = date('Y-m-d');

    try {
        $queryKadaluarsaBayar = $koneksi->prepare("
            SELECT id_sewa, id_properti 
            FROM sewa 
            WHERE status_sewa = 'menunggu_pembayaran' AND batas_pembayaran < :sekarang
        ");
        $queryKadaluarsaBayar->execute([':sekarang' => $sekarang]);
        $sewaBatal = $queryKadaluarsaBayar->fetchAll();
        if (count($sewaBatal) > 0) {
            $koneksi->beginTransaction();
            foreach ($sewaBatal as $sw) {

                $updateSewa = $koneksi->prepare("UPDATE sewa SET status_sewa = 'dibatalkan' WHERE id_sewa = :id_sewa");
          $updateSewa->execute([':id_sewa' => $sw['id_sewa']]);
         $updateProperti = $koneksi->prepare("UPDATE properti SET status_ketersediaan = 'tersedia' WHERE id_properti = :id_properti");
                $updateProperti->execute([':id_properti' => $sw['id_properti']]);
            }
            $koneksi->commit();
        }
        $querySelesaiSewa = $koneksi->prepare("
            SELECT id_sewa, id_properti 
            FROM sewa 
            WHERE status_sewa = 'aktif' AND tanggal_selesai < :hariIni
        ");
        $querySelesaiSewa->execute([':hariIni' => $hariIni]);
        $sewaSelesai = $querySelesaiSewa->fetchAll();
        if (count($sewaSelesai) > 0) {
            $koneksi->beginTransaction();
            foreach ($sewaSelesai as $sw) {
                $updateSewa = $koneksi->prepare("UPDATE sewa SET status_sewa = 'selesai' WHERE id_sewa = :id_sewa");
         $updateSewa->execute([':id_sewa' => $sw['id_sewa']]);
         $updateProperti = $koneksi->prepare("UPDATE properti SET status_ketersediaan = 'tersedia' WHERE id_properti = :id_properti");
                $updateProperti->execute([':id_properti' => $sw['id_properti']]);
            }
            $koneksi->commit();
        }
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) {
            $koneksi->rollBack();
        }
    }
}
?>
