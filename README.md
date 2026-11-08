# LahanKritisTrack

Sistem informasi pemantauan lahan kritis & rehabilitasi Kota Bengkulu (Laravel 13, PHP ≥ 8.4).
Seluruh data kini bersumber dari **database** sesuai ERD: `users`, `data_lahan`, `kegiatan_rehabilitasi`, `monitoring`.

## Menjalankan

```bash
cp .env.example .env               # (lewati bila .env sudah ada)
php artisan key:generate           # (lewati bila APP_KEY sudah terisi)
php artisan migrate:fresh --seed   # MENGHAPUS semua tabel lalu membuat ulang + data awal
php artisan storage:link           # agar foto dokumentasi dapat ditampilkan
php artisan serve
```

Data awal (seeder): 3 akun dan **101 petak lahan kritis** (80 `objectid` sumber, terpotong per kecamatan) di 9 kecamatan Kota Bengkulu, total 15.028,54 ha. Sumbernya Peta Lahan Kritis 1:50.000 (KLHK/PDASRH) dari Satu Peta BIG, diolah di QGIS (Fix geometries, Intersection dengan batas kecamatan, pembuangan sliver) dan disimpan di `database/seeders/data/lahan_kritis.geojson`. Impor ulang berkas yang sama tidak menggandakan petak: kuncinya pasangan `objectid` + kecamatan.
Kata sandi awal semua akun: `lahankritis123` (ubah lewat `SEED_USER_PASSWORD` di `.env` sebelum seeding, atau lewat menu Manajemen Akun).

| Username | Role |
|---|---|
| `anjelia.admin` | Admin |
| `fikhri.petugas` | Petugas |
| `habiby.petugas` | Petugas |

Opsional, contoh kegiatan + monitoring agar semua halaman terisi: `php artisan db:seed --class=DemoDataSeeder`

### MySQL / MariaDB (XAMPP, Laragon)
Buat database kosong `lahankritistrack`, lalu di `.env` ubah `DB_CONNECTION=mysql` dan aktifkan baris `DB_HOST` … `DB_PASSWORD`, kemudian jalankan `php artisan migrate:fresh --seed`.

## Hak akses
| Fitur | Admin | Petugas |
|---|---|---|
| Dashboard, Akun, Data Lahan (ubah/hapus/impor), Laporan | ✔ | – |
| Peta, lihat kegiatan & monitoring | ✔ | ✔ |
| Tambah/ubah kegiatan, update status, catat monitoring | ✔ | ✔ |
| Ubah monitoring | semua | hanya miliknya |
| Hapus kegiatan / monitoring | ✔ | – |

Halaman publik hanya menampilkan kegiatan berstatus *dipublikasikan*.

## Tes
```bash
php vendor/bin/pest
```
