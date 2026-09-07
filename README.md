 Database Kost & Kontrakan

Deskripsi

**Database Kost & Kontrakan** adalah sebuah sistem basis data yang dirancang untuk membantu pengelolaan informasi kost dan kontrakan secara terstruktur.

Database ini menyimpan dan mengelola berbagai data yang berkaitan dengan properti, unit/kamar, pemilik, penyewa, serta transaksi pembayaran. Project ini dibuat sebagai dasar untuk pengembangan aplikasi manajemen kost dan kontrakan yang lebih terintegrasi.

Fitur

* Manajemen Properti — Mengelola data kost dan kontrakan.
* Manajemen Kamar/Unit — Menyimpan informasi unit, status ketersediaan, dan detail kamar.
* Data Pemilik — Mengelola informasi pemilik properti.
* Data Penyewa — Menyimpan data penghuni atau penyewa.
* Manajemen Pembayaran— Mencatat transaksi pembayaran sewa.
* Relasi Antar Data — Menggunakan relasi tabel untuk menjaga keterhubungan dan konsistensi data.
* Database Terstruktur — Dirancang agar data mudah dikelola dan dikembangkan menjadi sebuah aplikasi.

 Teknologi

Database: MySQL / MariaDB
Query Language: SQL
File Database:`.sql`

 Cara Menjalankan

1. Clone Repository

```bash
git clone https://github.com/username/nama-repository.git
cd nama-repository
```

 2. Import Database

Buka phpMyAdmin, MySQL Workbench, atau MySQL melalui terminal.

Jika menggunakan terminal:

```bash
mysql -u root -p < db_kost_kontrakan.sql
```

Atau melalui phpMyAdmin:

1. Buka phpMyAdmin.
2. Buat database baru.
3. Pilih menu Import.
4. Upload file `db_kost_kontrakan.sql`.
5. Klik Import atau Go.
6. Pastikan seluruh tabel berhasil dibuat.

 3. Cek Database

Setelah proses import selesai, pastikan tabel dan relasi database telah berhasil dibuat dan dapat digunakan.

 Tujuan Project

Project ini dibuat untuk menerapkan konsep **database relational**, mulai dari perancangan tabel, primary key, foreign key, relasi antar tabel, hingga pengelolaan data menggunakan SQL.

Database ini juga dapat dikembangkan lebih lanjut menjadi aplikasi berbasis **web maupun desktop** untuk membantu pemilik kost atau kontrakan dalam mengelola properti dan transaksi penyewaan.

 📁 Struktur Project

```text
db-kost-kontrakan/
│
├── db_kost_kontrakan.sql
└── README.md
```

🔮 Pengembangan Selanjutnya

Beberapa pengembangan yang dapat ditambahkan:

* Sistem login dan autentikasi pengguna
* Dashboard pengelolaan kost
* Notifikasi jatuh tempo pembayaran
* Laporan transaksi
* Pencarian dan filter data
* Integrasi dengan aplikasi web
* Sistem pembayaran online

---

Project Status: `Database Design / Development`

Dibuat sebagai project pembelajaran dan pengembangan sistem informasi manajemen kost & kontrakan.

