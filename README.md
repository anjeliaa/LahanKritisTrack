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

Data awal (seeder): 3 akun dan **80 petak lahan kritis** dari GeoJSON BPDAS Ketahun.

| Username | Role |
|---|---|
| `anjelia.admin` | Admin |
| `fikhri.admin` | Admin |
| `habiby.admin` | Admin |
| `budi.petugas` | Petugas |


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
