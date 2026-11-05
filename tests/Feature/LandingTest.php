<?php

use App\Models\DataLahan;
use App\Models\KegiatanRehabilitasi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function lp_petak(string $kritis, float $luas, int $kec = 1): DataLahan
{
    return DataLahan::create([
        'kecamatan_id' => $kec, 'kecamatan' => config("lahan.kecamatan.$kec.nama"), 'bpdas' => 'Ketahun', 'wilayah' => 'Kota Bengkulu',
        'kritis' => $kritis, 'luas_ha' => $luas,
        'geometri' => ['type' => 'Polygon', 'coordinates' => [[[102.3, -3.8], [102.31, -3.8], [102.31, -3.81], [102.3, -3.8]]]],
    ]);
}

function lp_keg(DataLahan $l, array $o = []): KegiatanRehabilitasi
{
    return KegiatanRehabilitasi::create($o + ['id_lahan' => $l->id_lahan, 'nama_kegiatan' => 'Program Uji', 'target_luas' => 5, 'status' => 'Berjalan', 'is_published' => true]);
}

it('halaman masyarakat memuat struktur desain baru dan semua kait JavaScript peta', function () {
    $html = test()->get('/')->assertOk()->getContent();

    foreach (['lp-hero', 'lp-impact', 'lp-about', 'id="peta"', 'id="kegiatan"', 'id="map"', 'id="f-q"', 'id="f-kec"', 'id="f-kritis"', 'id="f-status"',
        'id="result-list"', 'id="kec-kegiatan"', 'data-hero-search', 'data-open-chatbot', 'id="chatbot"', 'css/landing.css', 'js/landing.js', 'js/map.js'] as $kait) {
        expect($html)->toContain($kait);
    }
    expect($html)->not->toContain('Statistik Wilayah');
});

it('bagian "cara membaca warna" menampilkan jumlah petak dan luas per kelas dengan warna peta', function () {
    lp_petak('Kritis', 3.5); lp_petak('Agak Kritis', 10.25); lp_petak('Agak Kritis', 5.0);
    $pulih = lp_petak('Potensial Kritis', 40.0);
    lp_keg($pulih, ['status' => 'Selesai', 'tanggal_status' => '2026-05-20']);

    $html = test()->get('/')->getContent();
    $teks = preg_replace('/\s+/', ' ', strip_tags($html));

    expect($teks)->toContain('Kritis 1 petak · 3.5 Ha')->toContain('Agak Kritis 2 petak · 15.3 Ha')
        ->toContain('Telah Direhabilitasi 1 petak · 40.0 Ha');
    // warna sama dengan peta/legenda
    expect($html)->toContain('data-kelas="pulih" style="--c: #06b6d4"')->toContain('data-kelas="kritis" style="--c: #ef4444"')
        ->and($html)->not->toContain('data-kelas="sangat"');   // kelas tanpa data tidak ditampilkan
});

it('kartu kegiatan membawa atribut untuk filter peta dan hanya kegiatan yang dipublikasikan', function () {
    $l = lp_petak('Kritis', 5);
    lp_keg($l, ['nama_kegiatan' => 'Tampil Publik', 'is_published' => true]);
    lp_keg($l, ['nama_kegiatan' => 'Tidak Tampil', 'is_published' => false]);

    $html = test()->get('/')->getContent();
    expect($html)->toContain('Tampil Publik')->not->toContain('Tidak Tampil')
        ->and($html)->toContain('data-lahan="'.$l->id_lahan.'"')->toContain('data-status="Berjalan"');
});

it('foto hero opsional: dipakai bila public/img/hero.jpg ada, ilustrasi bawaan bila tidak', function () {
    $foto = public_path('img/hero.jpg');
    @unlink($foto);

    expect(test()->get('/')->getContent())->not->toContain('img/hero.jpg');

    file_put_contents($foto, 'x');
    try {
        expect(test()->get('/')->getContent())->toContain('img/hero.jpg');
    } finally {
        @unlink($foto);
    }
});

it('aset desain tersedia: font, ilustrasi, CSS, dan JS', function () {
    foreach (['fonts/fraunces-latin-wght-normal.woff2', 'fonts/fraunces-latin-wght-italic.woff2', 'fonts/caveat-latin-700-normal.woff2',
        'img/hero-landscape.svg', 'css/landing.css', 'js/landing.js'] as $berkas) {
        expect(file_exists(public_path($berkas)))->toBeTrue("$berkas tidak ditemukan");
    }
});
