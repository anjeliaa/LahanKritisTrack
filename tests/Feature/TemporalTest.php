<?php

use App\Models\DataLahan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('riwayat tahun lama dibuat untuk setiap petak, tanpa kolom sumber data, dan aman dijalankan ulang', function () {
    $this->seed(DatabaseSeeder::class);
    $n = DataLahan::count();

    expect(Schema::hasColumn('data_lahan_temporal', 'data_source'))->toBeFalse()
        ->and(DB::table('data_lahan_temporal')->count())->toBe($n * 6)             // 2020–2025
        ->and(DB::table('data_lahan_temporal')->min('tahun'))->toBe(2020)
        ->and((int) DB::table('data_lahan_temporal')->max('tahun'))->toBe(2025);

    $this->seed(Database\Seeders\DataLahanTemporalSeeder::class);
    expect(DB::table('data_lahan_temporal')->count())->toBe($n * 6);

    // sebagian petak berganti kelas dibanding tahun data utama, sebagian tidak
    $beda = DB::table('data_lahan_temporal as t')->join('data_lahan as d', 'd.id_lahan', '=', 't.id_lahan')
        ->where('t.tahun', 2020)->whereColumn('t.kritis', '!=', 'd.kritis')->count();
    expect($beda)->toBeGreaterThan(0)->and($beda)->toBeLessThan($n);
});

it('GeoJSON mengikuti tahun yang dipilih: tahun utama dari data_lahan, tahun lain dari riwayat', function () {
    $this->seed(DatabaseSeeder::class);
    $n = DataLahan::count();

    $utama = $this->getJson('/data/lahan.geojson')->assertOk()->json('features');
    $lama = $this->getJson('/data/lahan.geojson?tahun=2020')->assertOk()->json('features');

    expect($utama)->toHaveCount($n)->and($lama)->toHaveCount($n);

    $kelas = fn ($fs) => collect($fs)->mapWithKeys(fn ($f) => [$f['properties']['id_lahan'] => $f['properties']['kritis_asli']]);
    expect($kelas($lama)->all())->not->toEqual($kelas($utama)->all());

    // sama dengan tahun utama bila tahun tidak diberikan atau diberikan eksplisit
    $eksplisit = $this->getJson('/data/lahan.geojson?tahun=' . config('lahan.tahun_data'))->assertOk()->json('features');
    expect($eksplisit)->toEqual($utama);

    $this->getJson('/data/lahan.geojson?tahun=1999')->assertNotFound();
});

it('ringkasan temporal memuat setiap tahun dengan jumlah petak yang sama dan tahun utama sesuai data_lahan', function () {
    $this->seed(DatabaseSeeder::class);
    $n = DataLahan::count();

    $j = $this->getJson('/data/temporal')->assertOk()->json();

    expect($j['tahun'])->toBe([2020, 2021, 2022, 2023, 2024, 2025, 2026])
        ->and($j['tahun_utama'])->toBe(2026);

    foreach ($j['tahun'] as $t) {
        expect(collect($j['data'][$t])->sum('n'))->toBe($n);
    }

    $asli = DataLahan::selectRaw('kritis, count(*) as n')->groupBy('kritis')->pluck('n', 'kritis');
    $kelas = array_flip(config('lahan.kritis'));
    foreach ($asli as $label => $jumlah) {
        expect($j['data'][2026][$kelas[$label]]['n'])->toBe((int) $jumlah);
    }
});

it('peta publik memuat bar tahun data, bukan dasbor temporal terpisah, dan tanpa pilihan sumber data', function () {
    $this->seed(DatabaseSeeder::class);

    $html = $this->get('/')->assertOk()->getContent();

    foreach (['id="t-bar"', 'data-mtab="peta"', 'data-mtab="temporal"', 'id="t-range"', 'id="t-play"', 'id="t-banding"', 'data-ringkas=', 'data-tahun='] as $kait) {
        expect($html)->toContain($kait);
    }
    expect($html)->not->toContain('visualisasi-temporal')
        ->not->toContain('name="sumber"')
        ->not->toContain('Data Dummy')
        ->not->toContain('Bandingkan Tahun');

    $this->get('/visualisasi-temporal')->assertNotFound();
});
