# LahanKritisTrack

Sistem informasi pemantauan lahan kritis dan rehabilitasi Kota Bengkulu berbasis Laravel 13 dan PHP ≥ 8.4.

Seluruh data kini bersumber dari **database** sesuai ERD, yaitu:

- `users`
- `data_lahan`
- `kegiatan_rehabilitasi`
- `monitoring`

## Menjalankan Project

### 1. Persiapan Environment

Salin file `.env.example` menjadi `.env` jika file `.env` belum tersedia.

```bash
cp .env.example .env
```

### 2. Generate Application Key

Jalankan perintah berikut jika `APP_KEY` belum terisi:

```bash
php artisan key:generate
```

### 3. Migrasi Database dan Seeder

```bash
php artisan migrate:fresh --seed
```

**Perhatian:** Perintah `migrate:fresh` akan menghapus seluruh tabel yang ada, kemudian membuat ulang tabel dan mengisi data awal.

### 4. Membuat Storage Link

Agar foto dokumentasi dapat ditampilkan:

```bash
php artisan storage:link
```

### 5. Menjalankan Server Laravel

```bash
php artisan serve
```

Project dapat diakses melalui:

`http://127.0.0.1:8000`

## Data Awal (Seeder)

Data awal yang disediakan meliputi:

- 3 akun pengguna.
- 101 petak lahan kritis.
- 80 `objectid` sumber yang dipotong berdasarkan kecamatan.
- 9 kecamatan di Kota Bengkulu.
- Total luas lahan sebesar 15.028,54 ha.

Sumber data berasal dari Peta Lahan Kritis skala 1:50.000 (KLHK/PDASRH) dari Satu Peta BIG.

Data diolah menggunakan QGIS melalui proses:

- Fix geometries.
- Intersection dengan batas kecamatan.
- Pembuangan sliver.

Data disimpan dalam berkas:

`database/seeders/data/lahan_kritis.geojson`

Impor ulang berkas yang sama tidak akan menggandakan petak lahan. Kunci identifikasi menggunakan pasangan `objectid` dan kecamatan.

### Akun Default

Kata sandi awal seluruh akun adalah:

`lahankritis123`

Kata sandi dapat diubah melalui konfigurasi `SEED_USER_PASSWORD` di file `.env` sebelum proses seeding, atau melalui menu Manajemen Akun.

| Username | Role |
|---|---|
| `anjelia.admin` | Admin |
| `fikhri.petugas` | Petugas |
| `habiby.petugas` | Petugas |

### Data Demo (Opsional)

Untuk menambahkan contoh kegiatan rehabilitasi dan monitoring agar seluruh halaman terisi:

```bash
php artisan db:seed --class=DemoDataSeeder
```

## Konfigurasi Database MySQL / MariaDB

Project dapat menggunakan MySQL atau MariaDB melalui XAMPP maupun Laragon.

### Langkah Konfigurasi

1. Buat database kosong bernama `lahankritistrack`.
2. Buka file `.env`.
3. Ubah konfigurasi database menjadi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lahankritistrack
DB_USERNAME=root
DB_PASSWORD=
```

4. Jalankan migrasi dan seeder:

```bash
php artisan migrate:fresh --seed
```

**Perhatian:** Perintah tersebut akan menghapus seluruh tabel yang ada pada database sebelum membuat ulang tabel dan data awal.

## Hak Akses Pengguna

Berikut pembagian hak akses Admin dan Petugas:

| Fitur | Admin | Petugas |
|---|---|---|
| Dashboard, Akun, Data Lahan (ubah, hapus, impor), Laporan | ✔ | – |
| Peta, melihat kegiatan dan monitoring | ✔ | ✔ |
| Tambah/ubah kegiatan, update status, catat monitoring | ✔ | ✔ |
| Ubah monitoring | Semua | Hanya miliknya |
| Hapus kegiatan / monitoring | ✔ | – |

Halaman publik hanya menampilkan kegiatan dengan status **dipublikasikan**.

## Pengujian

Untuk menjalankan pengujian menggunakan Pest:

```bash
php vendor/bin/pest
```

## Teknologi

- Laravel 13
- PHP ≥ 8.4
- MySQL / MariaDB
- QGIS
- Pest

