<?php

/*
|--------------------------------------------------------------------------
| Data referensi LahanKritisTrack
|--------------------------------------------------------------------------
| Daftar ini hanya referensi (bukan data transaksi). Data lahan, kegiatan,
| dan monitoring tersimpan di database sesuai ERD.
*/

return [

    // 9 kecamatan Kota Bengkulu. `id` dipakai sebagai DATA_LAHAN.kecamatan_id.
    'kecamatan' => [
        1 => ['slug' => 'selebar',   'nama' => 'Selebar'],
        2 => ['slug' => 'kmelayu',   'nama' => 'Kampung Melayu'],
        3 => ['slug' => 'gcempaka',  'nama' => 'Gading Cempaka'],
        4 => ['slug' => 'ratuagung', 'nama' => 'Ratu Agung'],
        5 => ['slug' => 'rsamban',   'nama' => 'Ratu Samban'],
        6 => ['slug' => 'spati',     'nama' => 'Singgaran Pati'],
        7 => ['slug' => 'sserut',    'nama' => 'Sungai Serut'],
        8 => ['slug' => 'mbhulu',    'nama' => 'Muara Bangka Hulu'],
        9 => ['slug' => 'tsegara',   'nama' => 'Teluk Segara'],
    ],

    // kelas => label tingkat kekritisan (urut dari paling ringan)
    'kritis' => [
        'tidak'     => 'Tidak Kritis',
        'potensial' => 'Potensial Kritis',
        'agak'      => 'Agak Kritis',
        'kritis'    => 'Kritis',
        'sangat'    => 'Sangat Kritis',
    ],

    // Warna per kelas: SATU sumber untuk peta, legenda, donut dashboard (dikirim ke JS lewat window.APP.warna)
    'warna' => [
        'tidak'     => '#22c55e',
        'potensial' => '#eab308',
        'agak'      => '#f97316',
        'kritis'    => '#ef4444',
        'sangat'    => '#b91c1c',
        'pulih'     => '#06b6d4',   // petak yang kegiatan rehabilitasinya sudah Selesai (status tampilan, bukan kelas BPDAS)
    ],

    // Status TAMPILAN untuk petak yang sudah selesai direhabilitasi. Kelas kekritisan asli (data BPDAS) tidak diubah.
    'pulih' => ['label' => 'Telah Direhabilitasi'],

    // Penjelasan singkat bahasa awam untuk tampilan masyarakat (klasifikasi tetap mengikuti data BPDAS)
    'deskripsi' => [
        'tidak'     => 'Kondisi lahan relatif baik dan terjaga.',
        'pulih'     => 'Kegiatan rehabilitasi pada lahan ini telah selesai.',
        'potensial' => 'Masih berfungsi, tetapi berpotensi menurun bila tidak dijaga.',
        'agak'      => 'Fungsi lahan mulai menurun dan perlu penanganan.',
        'kritis'    => 'Fungsi lahan telah menurun dan perlu direhabilitasi.',
        'sangat'    => 'Fungsi lahan sangat menurun dan perlu rehabilitasi segera.',
    ],

    // kelas yang dihitung sebagai "lahan kritis" (Tidak Kritis dikecualikan)
    'kritis_dihitung' => ['sangat', 'kritis', 'agak', 'potensial'],

    'status' => ['Berjalan', 'Selesai', 'Rencana'],

    'kondisi' => ['Membaik', 'Stabil', 'Memburuk'],

    'role' => ['admin' => 'Admin', 'petugas' => 'Petugas'],

    'bpdas_default' => 'Ketahun',
];
