<?php

use App\Models\DataLahan;
use App\Models\KegiatanRehabilitasi;
use App\Models\Monitoring;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function petak(array $o = []): DataLahan
{
    return DataLahan::create($o + [
        'objectid' => random_int(1000, 99999), 'wilayah' => 'Kota Bengkulu',
        'kecamatan_id' => 1, 'kecamatan' => 'Selebar', 'bpdas' => 'Ketahun',
        'kritis' => 'Kritis', 'luas_ha' => 5,
        'geometri' => ['type' => 'Polygon', 'coordinates' => [[[102.3, -3.8], [102.31, -3.8], [102.31, -3.81], [102.3, -3.8]]]],
    ]);
}

function keg(DataLahan $l, array $o = []): KegiatanRehabilitasi
{
    return KegiatanRehabilitasi::create($o + [
        'id_lahan' => $l->id_lahan, 'nama_kegiatan' => 'Reboisasi Uji', 'target_luas' => 10,
        'status' => 'Berjalan', 'is_published' => true,
    ]);
}

const JSONH = ['Accept' => 'application/json'];

// ---------------------------------------------------------------- akses & login

it('mengalihkan tamu ke login dan membatasi akses per role', function () {
    $this->get('/admin/dashboard')->assertRedirect('/login');

    $this->actingAs(User::factory()->petugas()->create())->get('/admin/akun')->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->get('/petugas/peta')->assertForbidden();
});

it('login memakai username dan kata sandi dari database', function () {
    $u = User::factory()->admin()->create(['username' => 'budi', 'password' => 'rahasia123']);

    $this->post('/login', ['username' => 'budi', 'password' => 'salah'])->assertSessionHasErrors('username');
    $this->assertGuest();

    $this->post('/login', ['username' => 'budi', 'password' => 'rahasia123'])->assertRedirect('/admin/dashboard');
    $this->assertAuthenticatedAs($u);

    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

it('login dibatasi setelah 5 percobaan gagal', function () {
    User::factory()->create(['username' => 'x']);
    foreach (range(1, 5) as $i) {
        $this->post('/login', ['username' => 'x', 'password' => 'salah']);
    }
    $this->post('/login', ['username' => 'x', 'password' => 'password'])->assertSessionHasErrors('username');
    $this->assertGuest();
});

// ---------------------------------------------------------------- publik

it('halaman publik hanya menampilkan kegiatan yang dipublikasikan', function () {
    $l = petak();
    keg($l, ['nama_kegiatan' => 'Program Terbuka', 'is_published' => true]);
    keg($l, ['nama_kegiatan' => 'Program Internal Rahasia', 'is_published' => false]);

    $this->get('/')->assertOk()->assertSee('Program Terbuka')->assertDontSee('Program Internal Rahasia');
});

it('endpoint GeoJSON memuat petak dari database', function () {
    petak(['kritis' => 'Sangat Kritis', 'luas_ha' => 7.5]);

    $r = $this->get('/data/lahan.geojson')->assertOk()->json();

    expect($r['type'])->toBe('FeatureCollection')
        ->and($r['features'])->toHaveCount(1)
        ->and($r['features'][0]['properties'])->toMatchArray(['kritis' => 'Sangat Kritis', 'kritis_class' => 'sangat', 'kec_slug' => 'selebar', 'luas' => 7.5]);
});

it('seeder memuat 80 petak asli dan 3 akun', function () {
    $this->seed(DatabaseSeeder::class);

    expect(DataLahan::count())->toBe(80)->and(User::count())->toBe(3)
        ->and(round(DataLahan::sum('luas_ha')))->toBe(15028.0);
});

// ---------------------------------------------------------------- kegiatan

it('petugas membuat & mengubah kegiatan miliknya, validasi berjalan, hapus hanya admin', function () {
    $l = petak();
    $petugas = User::factory()->petugas()->create();

    $this->actingAs($petugas)->postJson('/kegiatan', [])->assertStatus(422)->assertJsonValidationErrors(['nama_kegiatan', 'id_lahan', 'target_luas']);

    $this->actingAs($petugas)->postJson('/kegiatan', [
        'nama_kegiatan' => 'Mangrove Baru', 'id_lahan' => $l->id_lahan, 'target_luas' => 12.5,
        'tanggal_mulai' => '2026-03-01', 'tanggal_selesai' => '2026-02-01',
    ])->assertStatus(422)->assertJsonValidationErrors('tanggal_selesai');

    $this->actingAs($petugas)->postJson('/kegiatan', [
        'nama_kegiatan' => 'Mangrove Baru', 'id_lahan' => $l->id_lahan, 'target_luas' => 12.5, 'is_published' => '1',
    ])->assertOk();

    $k = KegiatanRehabilitasi::firstOrFail();
    expect($k->id_user)->toBe($petugas->id_user)->and($k->status)->toBe('Rencana')->and($k->is_published)->toBeTrue();

    $this->actingAs($petugas)->putJson("/kegiatan/{$k->id_kegiatan}", [
        'nama_kegiatan' => 'Mangrove Diubah', 'id_lahan' => $l->id_lahan, 'target_luas' => 20, 'status' => 'Berjalan', 'is_published' => '0',
    ])->assertOk();
    expect($k->fresh())->nama_kegiatan->toBe('Mangrove Diubah')->status->toBe('Berjalan')->is_published->toBeFalse();

    $this->actingAs($petugas)->deleteJson("/kegiatan/{$k->id_kegiatan}")->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/kegiatan/{$k->id_kegiatan}")->assertOk();
    expect(KegiatanRehabilitasi::count())->toBe(0);
});

// ---------------------------------------------------------------- monitoring

it('monitoring menyimpan foto & menurunkan progres dan realisasi kegiatan', function () {
    Storage::fake('public');
    $petugas = User::factory()->petugas()->create();
    $k = keg(petak(), ['target_luas' => 20, 'id_user' => $petugas->id_user]);

    $this->actingAs($petugas)->post('/monitoring', [
        'id_kegiatan' => $k->id_kegiatan, 'tanggal_monitoring' => '2026-04-01', 'progres_persen' => 30,
        'kondisi_lahan' => 'Membaik', 'foto_dokumentasi' => UploadedFile::fake()->image('lahan.jpg'),
    ], JSONH)->assertOk();

    $m = Monitoring::firstOrFail();
    expect($m->id_user)->toBe($petugas->id_user)->and($m->foto_dokumentasi)->not->toBeNull();
    Storage::disk('public')->assertExists($m->foto_dokumentasi);

    // catatan terbaru (tanggal paling baru) menentukan progres
    $this->actingAs($petugas)->post('/monitoring', ['id_kegiatan' => $k->id_kegiatan, 'progres_persen' => 75, 'kondisi_lahan' => 'Stabil'], JSONH)->assertOk();

    $k = KegiatanRehabilitasi::with('monitoringTerakhir')->find($k->id_kegiatan);
    expect($k->progres)->toBe(75)->and($k->realisasi)->toBe(15.0);
    expect(Monitoring::latest('id_monitoring')->first()->tanggal_monitoring->toDateString())->toBe(today()->toDateString());
});

it('monitoring menolak berkas bukan gambar, progres > 100, dan tanggal masa depan', function () {
    Storage::fake('public');
    $petugas = User::factory()->petugas()->create();
    $k = keg(petak(), ['id_user' => $petugas->id_user]);

    $this->actingAs($petugas)->post('/monitoring', [
        'id_kegiatan' => $k->id_kegiatan, 'progres_persen' => 150, 'kondisi_lahan' => 'Membaik',
        'tanggal_monitoring' => now()->addDays(3)->toDateString(),
        'foto_dokumentasi' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
    ], JSONH)->assertStatus(422)->assertJsonValidationErrors(['progres_persen', 'tanggal_monitoring', 'foto_dokumentasi']);
});

it('petugas hanya boleh mengubah monitoring miliknya; admin boleh mengubah semua dan menghapus', function () {
    Storage::fake('public');
    [$a, $b] = [User::factory()->petugas()->create(), User::factory()->petugas()->create()];
    $k = keg(petak(), ['id_user' => $a->id_user]);
    $m = Monitoring::create(['id_kegiatan' => $k->id_kegiatan, 'id_user' => $a->id_user, 'tanggal_monitoring' => '2026-04-01', 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil', 'foto_dokumentasi' => 'monitoring/lama.jpg']);
    Storage::disk('public')->put('monitoring/lama.jpg', 'x');
    $payload = ['id_kegiatan' => $k->id_kegiatan, 'progres_persen' => 55, 'kondisi_lahan' => 'Membaik'];

    $this->actingAs($b)->put("/monitoring/{$m->id_monitoring}", $payload, JSONH)->assertForbidden();
    $this->actingAs($a)->put("/monitoring/{$m->id_monitoring}", $payload, JSONH)->assertOk();
    expect($m->fresh()->progres_persen)->toBe(55)->and($m->fresh()->foto_dokumentasi)->toBe('monitoring/lama.jpg'); // foto dipertahankan

    $this->actingAs($a)->delete("/monitoring/{$m->id_monitoring}", [], JSONH)->assertForbidden();
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->put("/monitoring/{$m->id_monitoring}", $payload + ['catatan' => 'Dikoreksi Admin'], JSONH)->assertOk();
    expect($m->fresh()->catatan)->toBe('Dikoreksi Admin');
    $this->actingAs($admin)->delete("/monitoring/{$m->id_monitoring}", [], JSONH)->assertOk();
    Storage::disk('public')->assertMissing('monitoring/lama.jpg');
});

it('menghapus kegiatan ikut menghapus monitoring dan fotonya', function () {
    Storage::fake('public');
    $k = keg(petak());
    Storage::disk('public')->put('monitoring/a.jpg', 'x');
    Monitoring::create(['id_kegiatan' => $k->id_kegiatan, 'tanggal_monitoring' => '2026-04-01', 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil', 'foto_dokumentasi' => 'monitoring/a.jpg']);

    $this->actingAs(User::factory()->admin()->create())->deleteJson("/kegiatan/{$k->id_kegiatan}")->assertOk();

    expect(Monitoring::count())->toBe(0);
    Storage::disk('public')->assertMissing('monitoring/a.jpg');
});

// ---------------------------------------------------------------- akun

it('admin mengelola akun dengan pengaman diri sendiri', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson('/admin/akun', ['nama' => 'Siti', 'username' => 'siti.petugas', 'role' => 'petugas', 'password' => 'abc12345'])->assertOk();
    $siti = User::where('username', 'siti.petugas')->firstOrFail();
    expect(password_verify('abc12345', $siti->password))->toBeTrue();

    $this->actingAs($admin)->postJson('/admin/akun', ['nama' => 'Dup', 'username' => 'siti.petugas', 'role' => 'petugas', 'password' => 'abc12345'])->assertJsonValidationErrors('username');
    $this->actingAs($admin)->postJson('/admin/akun', ['nama' => 'Pendek', 'username' => 'p', 'role' => 'petugas', 'password' => '123'])->assertJsonValidationErrors('password');

    // ubah tanpa mengisi sandi => sandi lama dipertahankan
    $this->actingAs($admin)->putJson("/admin/akun/{$siti->id_user}", ['nama' => 'Siti Baru', 'username' => 'siti.petugas', 'role' => 'admin', 'password' => ''])->assertOk();
    expect(password_verify('abc12345', $siti->fresh()->password))->toBeTrue()->and($siti->fresh()->role)->toBe('admin');

    $this->actingAs($admin)->putJson("/admin/akun/{$admin->id_user}", ['nama' => 'X', 'username' => $admin->username, 'role' => 'petugas'])->assertJsonValidationErrors('role');
    $this->actingAs($admin)->deleteJson("/admin/akun/{$admin->id_user}")->assertJsonValidationErrors('akun');
    $this->actingAs($admin)->deleteJson("/admin/akun/{$siti->id_user}")->assertOk();
});

it('menghapus akun tidak menghapus riwayat kegiatan/monitoring yang dicatatnya', function () {
    $u = User::factory()->petugas()->create();
    $k = keg(petak(), ['id_user' => $u->id_user]);
    Monitoring::create(['id_kegiatan' => $k->id_kegiatan, 'id_user' => $u->id_user, 'tanggal_monitoring' => '2026-04-01', 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil']);

    $this->actingAs(User::factory()->admin()->create())->deleteJson("/admin/akun/{$u->id_user}")->assertOk();

    expect($k->fresh()->id_user)->toBeNull()->and(Monitoring::first()->id_user)->toBeNull();
});

// ---------------------------------------------------------------- lahan

it('impor GeoJSON menambah, memperbarui berdasarkan objectid, dan melewati fitur rusak', function () {
    $admin = User::factory()->admin()->create();
    $poly = ['type' => 'Polygon', 'coordinates' => [[[102.30, -3.80], [102.31, -3.80], [102.31, -3.81], [102.30, -3.80]]]];
    $fc = fn (array $f) => UploadedFile::fake()->createWithContent('data.geojson', json_encode(['type' => 'FeatureCollection', 'features' => $f]));
    $feat = fn ($props, $g = null) => ['type' => 'Feature', 'properties' => $props, 'geometry' => $g ?? $poly];

    $this->actingAs($admin)->post('/admin/lahan/import', ['geojson' => $fc([
        $feat(['objectid' => 7, 'kecamatan' => 'Selebar', 'kritis' => 'Agak Kritis', 'luas' => 3.5]),
        $feat(['objectid' => 8, 'kecamatan' => 'Singaran Pati', 'kritis_class' => 'sangat']),        // ejaan sumber data; luas dihitung dari geometri
        $feat(['objectid' => 9, 'kecamatan' => 'Kota Antah Berantah', 'kritis' => 'Kritis']),      // kecamatan tak dikenal
        $feat(['objectid' => 10, 'kecamatan' => 'Selebar', 'kritis' => 'Kritis'], ['type' => 'Point', 'coordinates' => [0, 0]]),
    ])], JSONH)->assertOk();

    expect(DataLahan::count())->toBe(2);
    $b = DataLahan::where('objectid', 8)->first();
    expect($b->kecamatan)->toBe('Singgaran Pati')->and($b->kritis)->toBe('Sangat Kritis')->and($b->luas_ha)->toBeGreaterThan(0);

    // impor ulang: objectid 7 diperbarui, bukan digandakan
    $this->actingAs($admin)->post('/admin/lahan/import', ['geojson' => $fc([$feat(['objectid' => 7, 'kecamatan' => 'Selebar', 'kritis' => 'Tidak Kritis', 'luas' => 9])])], JSONH)->assertOk();
    expect(DataLahan::count())->toBe(2)->and(DataLahan::where('objectid', 7)->first()->kritis)->toBe('Tidak Kritis');

    $this->actingAs($admin)->post('/admin/lahan/import', ['geojson' => UploadedFile::fake()->createWithContent('x.geojson', 'bukan json')], JSONH)->assertStatus(422)->assertJsonValidationErrors('geojson');
});

it('petak dapat diubah, dan tidak dapat dihapus bila masih punya kegiatan', function () {
    $admin = User::factory()->admin()->create();
    $l = petak();
    keg($l);

    $this->actingAs($admin)->putJson("/admin/lahan/{$l->id_lahan}", ['kecamatan_id' => 7, 'bpdas' => 'Ketahun', 'kritis' => 'Sangat Kritis', 'luas_ha' => 11.25])->assertOk();
    expect($l->fresh())->kecamatan->toBe('Sungai Serut')->kritis->toBe('Sangat Kritis');

    $this->actingAs($admin)->putJson("/admin/lahan/{$l->id_lahan}", ['kecamatan_id' => 7, 'bpdas' => 'K', 'kritis' => 'Ngawur', 'luas_ha' => 1])->assertJsonValidationErrors('kritis');
    $this->actingAs($admin)->deleteJson("/admin/lahan/{$l->id_lahan}")->assertJsonValidationErrors('lahan');
    expect(DataLahan::count())->toBe(1);

    $kosong = petak();
    $this->actingAs($admin)->deleteJson("/admin/lahan/{$kosong->id_lahan}")->assertOk();
});

// ---------------------------------------------------------------- halaman & laporan

it('semua halaman internal dapat dibuka dengan data nyata', function () {
    $this->seed(DatabaseSeeder::class);
    $this->artisan('db:seed', ['--class' => 'DemoDataSeeder']);

    $admin = User::where('role', 'admin')->first();
    foreach (['dashboard', 'akun', 'lahan', 'kegiatan', 'monitoring', 'laporan'] as $p) {
        $this->actingAs($admin)->get("/admin/{$p}")->assertOk();
    }

    $petugas = User::where('role', 'petugas')->first();
    foreach (['peta', 'kegiatan', 'monitoring'] as $p) {
        $this->actingAs($petugas)->get("/petugas/{$p}")->assertOk();
    }
});

it('laporan memfilter status & periode dan mengekspor Excel', function () {
    $l = petak(['kecamatan_id' => 2, 'kecamatan' => 'Kampung Melayu']);
    keg($l, ['nama_kegiatan' => 'Lama Selesai', 'status' => 'Selesai', 'tanggal_mulai' => '2024-01-10', 'tanggal_selesai' => '2024-06-10']);
    keg($l, ['nama_kegiatan' => 'Baru Berjalan', 'status' => 'Berjalan', 'tanggal_mulai' => '2026-02-01', 'tanggal_selesai' => '2026-11-01']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/laporan')->assertSee('Lama Selesai')->assertSee('Baru Berjalan');
    $this->actingAs($admin)->get('/admin/laporan?status=Selesai')->assertSee('Lama Selesai')->assertDontSee('Baru Berjalan');
    $this->actingAs($admin)->get('/admin/laporan?periode=y:2026')->assertSee('Baru Berjalan')->assertDontSee('Lama Selesai');
    $this->actingAs($admin)->get('/admin/laporan?kecamatan_id=1')->assertDontSee('Baru Berjalan');

    $r = $this->actingAs($admin)->get('/admin/laporan/excel?periode=y:2026');
    $r->assertOk();
    $body = $r->baseResponse instanceof Symfony\Component\HttpFoundation\BinaryFileResponse ? file_get_contents($r->baseResponse->getFile()->getPathname()) : $r->getContent();
    expect(str_starts_with($body, 'PK') || str_contains($body, 'Baru Berjalan'))->toBeTrue();
});

it('donut dashboard memakai warna peta, hanya kelas yang ada, dan jumlah kecamatan seragam', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::where('role', 'admin')->first();

    $html = $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->getContent();
    preg_match('/window\.APP = (\{.*?\});<\/script>/s', $html, $m);
    $app = json_decode($m[1], true);

    // data asli: 4 kelas (tanpa Sangat Kritis), urutan & warna sama dengan legenda peta
    expect($app['dashboard']['donut']['labels'])->toBe(['Tidak Kritis', 'Potensial Kritis', 'Agak Kritis', 'Kritis'])
        ->and($app['dashboard']['donut']['colors'])->toBe(['#22c55e', '#eab308', '#f97316', '#ef4444'])
        ->and($app['warna']['kritis'])->toBe('#ef4444');

    // APP memuat 9 kecamatan (untuk filter); 7 di antaranya memiliki data -> angka seragam di dashboard & publik
    expect($app['kecamatan'])->toHaveCount(9)
        ->and(collect($app['kecamatan'])->where('ada_data', true))->toHaveCount(7);
    $this->actingAs($admin)->get('/admin/dashboard')->assertSee('Berdasarkan data 7 Kecamatan');
    auth()->logout();
    $this->get('/')->assertSee('Berdasarkan data 7 Kecamatan');
});


it('filter peta menampilkan semua kecamatan, tetapi tingkat kekritisan hanya yang ada pada data', function () {
    $this->seed(DatabaseSeeder::class);

    $html = $this->get('/')->assertOk()->getContent();

    // 9 kecamatan, termasuk Ratu Samban & Teluk Segara yang belum punya data
    foreach (config('lahan.kecamatan') as $k) {
        expect($html)->toContain($k['nama']);
    }
    expect($html)->toContain('Teluk Segara (belum ada data)')->and($html)->toContain('Ratu Samban (belum ada data)')
        ->and($html)->not->toContain('Selebar (belum ada data)');

    // kelas pada data: Tidak, Potensial, Agak, Kritis  -> "Sangat Kritis" tidak jadi opsi
    preg_match('/<select id="f-kritis".*?<\/select>/s', $html, $m);
    expect($m[0])->toContain('Potensial Kritis')->toContain('Agak Kritis')->toContain('Tidak Kritis')
        ->and($m[0])->not->toContain('Sangat Kritis');

    // halaman petugas: filter kecamatan tersedia dan sama aturannya
    $petugas = User::where('role', 'petugas')->first();
    $peta = $this->actingAs($petugas)->get('/petugas/peta')->assertOk()->getContent();
    expect($peta)->toContain('id="f-kec"')->toContain('Teluk Segara (belum ada data)');
    preg_match('/<select id="f-kritis".*?<\/select>/s', $peta, $m2);
    expect($m2[0])->not->toContain('Sangat Kritis');
});

it('opsi Sangat Kritis muncul otomatis begitu ada petaknya', function () {
    $this->seed(DatabaseSeeder::class);
    DataLahan::first()->update(['kritis' => 'Sangat Kritis']);

    $this->get('/')->assertSee('Sangat Kritis');
});

it('kegiatan terhubung ke petak (id_lahan), bukan ke kecamatan', function () {
    $a = petak(['kecamatan_id' => 1, 'kecamatan' => 'Selebar']);
    $b = petak(['kecamatan_id' => 1, 'kecamatan' => 'Selebar']);   // kecamatan sama, petak berbeda
    keg($a, ['nama_kegiatan' => 'Hanya di Petak A']);

    $html = $this->get('/')->getContent();
    preg_match('/window\.APP = (\{.*?\});<\/script>/s', $html, $m);
    $kg = json_decode($m[1], true)['kegiatan'];

    expect($kg)->toHaveCount(1)
        ->and($kg[0]['id_lahan'])->toBe($a->id_lahan)
        ->and($kg[0]['kec_slug'])->toBe('selebar');
    // kartu publik menempel pada petak A saja
    expect($html)->toContain('data-lahan="'.$a->id_lahan.'"')->not->toContain('data-lahan="'.$b->id_lahan.'"');
});

it('halaman publik memuat widget chatbot, halaman internal tidak', function () {
    $this->get('/')->assertOk()->assertSee('id="chatbot"', false)->assertSee('js/chatbot.js', false)->assertSee('Asisten LahanKritis');

    $this->actingAs(User::factory()->admin()->create())->get('/admin/dashboard')->assertOk()->assertDontSee('id="chatbot"', false);
});

it('navbar publik tidak lagi memuat menu Statistik Wilayah', function () {
    $this->get('/')->assertOk()->assertDontSee('Statistik Wilayah')
        ->assertSee('Peta Lahan Kritis')->assertSee('Kegiatan Rehabilitasi');
});

it('filter peta petugas identik dengan filter peta masyarakat', function () {
    $this->seed(DatabaseSeeder::class);
    $ids = ['f-q', 'f-kec', 'f-kritis', 'f-status', 'f-reset', 'result-count', 'result-list'];

    $publik = $this->get('/')->getContent();
    $petugas = $this->actingAs(User::where('role', 'petugas')->first())->get('/petugas/peta')->assertOk()->getContent();

    $blok = fn ($h) => preg_replace('/\s+/', ' ', preg_match('/<div class="filters">.*?id="result-count"><\/span>\s*<\/div>/s', $h, $m) ? $m[0] : '');
    foreach ($ids as $id) {
        expect($publik)->toContain('id="'.$id.'"');
        expect($petugas)->toContain('id="'.$id.'"');
    }
    // markup blok filter sama persis (opsi kecamatan, kekritisan, status)
    expect($blok($petugas))->not->toBe('')->toBe($blok($publik));
    // petugas juga mendapat opsi status & kecamatan tanpa data, dan tanpa Sangat Kritis
    expect($petugas)->toContain('Semua Status')->toContain('Teluk Segara (belum ada data)')->not->toContain('value="sangat"');
});


// =====================================================================================
// Penyesuaian dengan proposal: hak akses, kepemilikan kegiatan, status, profil
// =====================================================================================

it('Admin tidak dapat mencatat monitoring (hanya Petugas); Admin hanya meninjau, mengubah, menghapus', function () {
    $k = keg(petak());
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson('/monitoring', ['id_kegiatan' => $k->id_kegiatan, 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil'])->assertForbidden();
    expect(Monitoring::count())->toBe(0);

    // tombol "Catat Monitoring" tidak tampil bagi Admin, tampil bagi Petugas
    $this->actingAs($admin)->get('/admin/monitoring')->assertOk()->assertDontSee('Catat Monitoring');
    $this->actingAs(User::factory()->petugas()->create())->get('/petugas/monitoring')->assertOk()->assertSee('Catat Monitoring');
});

it('Petugas hanya mengelola kegiatan miliknya (relasi mengelola); Admin menugaskan petugas', function () {
    $l = petak();
    [$a, $b] = [User::factory()->petugas()->create(), User::factory()->petugas()->create()];
    $admin = User::factory()->admin()->create();
    $milikA = keg($l, ['nama_kegiatan' => 'Milik A', 'id_user' => $a->id_user]);
    $payload = ['nama_kegiatan' => 'Diubah', 'id_lahan' => $l->id_lahan, 'target_luas' => 5];

    // petugas lain ditolak: ubah, update status, dan catat monitoring
    $this->actingAs($b)->putJson("/kegiatan/{$milikA->id_kegiatan}", $payload)->assertForbidden();
    $this->actingAs($b)->postJson('/kegiatan/status', ['kegiatan' => $milikA->id_kegiatan, 'status' => 'Berjalan'])->assertForbidden();
    $this->actingAs($b)->postJson('/monitoring', ['id_kegiatan' => $milikA->id_kegiatan, 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil'])->assertStatus(422)->assertJsonValidationErrors('id_kegiatan');

    // pemilik boleh
    $this->actingAs($a)->postJson('/kegiatan/status', ['kegiatan' => $milikA->id_kegiatan, 'status' => 'Berjalan'])->assertOk();
    expect($milikA->fresh()->status)->toBe('Berjalan');

    // halaman petugas hanya memuat kegiatan miliknya
    $keg = keg($l, ['nama_kegiatan' => 'Milik B', 'id_user' => $b->id_user]);
    $this->actingAs($a)->get('/petugas/kegiatan')->assertSee('Milik A')->assertDontSee('Milik B');
    $this->actingAs($b)->get('/petugas/kegiatan')->assertSee('Milik B')->assertDontSee('Milik A');

    // Admin menugaskan petugas saat membuat & memindahkan penanggung jawab saat mengubah
    $this->actingAs($admin)->postJson('/kegiatan', $payload + ['id_user' => $b->id_user])->assertOk();
    expect(KegiatanRehabilitasi::where('nama_kegiatan', 'Diubah')->value('id_user'))->toBe($b->id_user);
    $this->actingAs($admin)->putJson("/kegiatan/{$milikA->id_kegiatan}", $payload + ['id_user' => $b->id_user, 'status' => 'Berjalan'])->assertOk();
    expect($milikA->fresh()->id_user)->toBe($b->id_user);

    // hanya akun ber-role Petugas yang boleh ditugaskan
    $this->actingAs($admin)->postJson('/kegiatan', $payload + ['id_user' => $admin->id_user])->assertJsonValidationErrors('id_user');
    // petugas tidak dapat memindahkan penanggung jawab
    $this->actingAs($b)->putJson("/kegiatan/{$milikA->id_kegiatan}", $payload + ['id_user' => $a->id_user, 'status' => 'Berjalan'])->assertOk();
    expect($milikA->fresh()->id_user)->toBe($b->id_user);
});

it('status kegiatan mengikuti progres monitoring dan tanggal selesai terisi otomatis', function () {
    $petugas = User::factory()->petugas()->create();
    $k = keg(petak(), ['status' => 'Rencana', 'id_user' => $petugas->id_user, 'tanggal_mulai' => '2026-01-10', 'tanggal_selesai' => null]);
    $post = fn ($p, $tgl = null) => $this->actingAs($petugas)->postJson('/monitoring', array_filter(['id_kegiatan' => $k->id_kegiatan, 'progres_persen' => $p, 'kondisi_lahan' => 'Membaik', 'tanggal_monitoring' => $tgl]));

    // Selesai ditolak selama progres < 100
    $this->actingAs($petugas)->postJson('/kegiatan/status', ['kegiatan' => $k->id_kegiatan, 'status' => 'Selesai'])->assertStatus(422)->assertJsonValidationErrors('status');

    // monitoring pertama (> 0%) -> Berjalan otomatis
    $post(40, '2026-03-01')->assertOk();
    expect($k->fresh())->status->toBe('Berjalan');
    expect($k->fresh()->tanggal_status->toDateString())->toBe('2026-03-01');

    // pada 40% status tidak boleh dikembalikan ke Rencana / dipaksa Selesai
    $this->actingAs($petugas)->postJson('/kegiatan/status', ['kegiatan' => $k->id_kegiatan, 'status' => 'Rencana'])->assertStatus(422);
    $this->actingAs($petugas)->postJson('/kegiatan/status', ['kegiatan' => $k->id_kegiatan, 'status' => 'Selesai'])->assertStatus(422);

    // 100% -> Selesai otomatis; tanggal selesai kosong diisi tanggal monitoring; tanggal_status = tanggal selesai aktual
    $post(100, '2026-05-20')->assertOk();
    $k = $k->fresh();
    expect($k->status)->toBe('Selesai')
        ->and($k->tanggal_status->toDateString())->toBe('2026-05-20')
        ->and($k->tanggal_selesai->toDateString())->toBe('2026-05-20');

    // menghapus monitoring 100% (oleh Admin) -> status kembali mengikuti progres terakhir
    $m100 = Monitoring::where('progres_persen', 100)->first();
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/monitoring/{$m100->id_monitoring}")->assertOk();
    expect($k->fresh()->status)->toBe('Berjalan');
});

it('target tanggal selesai yang sudah diisi dipertahankan; tanggal selesai aktual tetap tercatat', function () {
    $petugas = User::factory()->petugas()->create();
    $k = keg(petak(), ['status' => 'Berjalan', 'id_user' => $petugas->id_user, 'tanggal_selesai' => '2026-12-31']);

    $this->actingAs($petugas)->postJson('/monitoring', ['id_kegiatan' => $k->id_kegiatan, 'progres_persen' => 100, 'kondisi_lahan' => 'Membaik', 'tanggal_monitoring' => '2026-06-15'])->assertOk();

    $k = $k->fresh();
    expect($k->status)->toBe('Selesai')
        ->and($k->tanggal_selesai->toDateString())->toBe('2026-12-31')   // target tidak ditimpa
        ->and($k->tanggal_status->toDateString())->toBe('2026-06-15');  // penyelesaian sebenarnya

    $this->actingAs($petugas)->get('/petugas/kegiatan')->assertSee('pada 15 Jun 2026');
});

it('kegiatan baru tidak boleh langsung berstatus Selesai', function () {
    $this->actingAs(User::factory()->petugas()->create())->postJson('/kegiatan', ['nama_kegiatan' => 'X', 'id_lahan' => petak()->id_lahan, 'target_luas' => 1, 'status' => 'Selesai'])
        ->assertStatus(422)->assertJsonValidationErrors('status');
});

it('topbar memakai ikon profil (bukan label role) dan tab Profil Petugas tidak ada', function () {
    foreach (['admin' => '/admin/dashboard', 'petugas' => '/petugas/peta'] as $role => $url) {
        $html = $this->actingAs(User::factory()->$role()->create())->get($url)->assertOk()->getContent();
        expect($html)->toContain('class="profile-btn')->toContain('href="'.url('/profil').'"')
            ->and($html)->not->toContain('role-pill')->and($html)->not->toContain('Profil Petugas');
    }
});

it('halaman pengaturan akun: lihat profil dan ubah kata sandi (Admin & Petugas)', function () {
    foreach (['admin', 'petugas'] as $role) {
        $u = User::factory()->$role()->create(['nama' => 'Budi Uji', 'username' => "budi.$role", 'password' => 'lama12345']);

        $this->actingAs($u)->get('/profil')->assertOk()->assertSee('Budi Uji')->assertSee("budi.$role")->assertSee('Ubah Kata Sandi')
            ->assertDontSee('name="nama"', false)->assertDontSee('name="username"', false); // identitas tidak dapat diubah sendiri

        $this->actingAs($u)->putJson('/profil/password', ['password_lama' => 'salah', 'password_baru' => 'baru12345', 'password_baru_confirmation' => 'baru12345'])->assertJsonValidationErrors('password_lama');
        $this->actingAs($u)->putJson('/profil/password', ['password_lama' => 'lama12345', 'password_baru' => 'baru12345', 'password_baru_confirmation' => 'beda'])->assertJsonValidationErrors('password_baru');
        $this->actingAs($u)->putJson('/profil/password', ['password_lama' => 'lama12345', 'password_baru' => 'lama12345', 'password_baru_confirmation' => 'lama12345'])->assertJsonValidationErrors('password_baru');
        $this->actingAs($u)->putJson('/profil/password', ['password_lama' => 'lama12345', 'password_baru' => 'baru12345', 'password_baru_confirmation' => 'baru12345'])->assertOk();

        expect(password_verify('baru12345', $u->fresh()->password))->toBeTrue()
            ->and($u->fresh())->nama->toBe('Budi Uji')->username->toBe("budi.$role"); // identitas tidak berubah
    }
    auth()->logout();
    $this->get('/profil')->assertRedirect('/login');
});

it('laporan memuat hasil monitoring (tabel pratinjau dan Excel terpisah), khusus Admin', function () {
    $k = keg(petak(['kecamatan_id' => 2, 'kecamatan' => 'Kampung Melayu']), ['nama_kegiatan' => 'Program Laporan']);
    Monitoring::create(['id_kegiatan' => $k->id_kegiatan, 'tanggal_monitoring' => '2026-04-01', 'progres_persen' => 40, 'kondisi_lahan' => 'Membaik', 'catatan' => 'Catatan lapangan uji']);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/laporan')->assertOk()->assertSee('Catatan lapangan uji')->assertSee('Excel Monitoring');
    $this->actingAs($admin)->get('/admin/laporan/monitoring/excel')->assertOk();
    $this->actingAs(User::factory()->petugas()->create())->get('/admin/laporan/monitoring/excel')->assertForbidden();
});

// =====================================================================================
// Lahan yang sudah selesai direhabilitasi tidak lagi tampil sebagai lahan kritis
// =====================================================================================

it('petak dengan kegiatan Selesai tampil "Telah Direhabilitasi"; kelas BPDAS asli tidak diubah', function () {
    $a = petak(['kritis' => 'Kritis', 'luas_ha' => 4]);
    $b = petak(['kritis' => 'Agak Kritis', 'luas_ha' => 6]);
    keg($a, ['status' => 'Selesai', 'tanggal_status' => '2026-05-20', 'is_published' => true]);
    keg($b, ['status' => 'Berjalan', 'is_published' => true]);

    $f = collect($this->getJson('/data/lahan.geojson')->assertOk()->json('features'))->keyBy('properties.id_lahan');

    expect($f[$a->id_lahan]['properties'])->toMatchArray([
        'kritis' => 'Telah Direhabilitasi', 'kritis_class' => 'pulih', 'kritis_asli' => 'Kritis', 'rehab' => true, 'rehab_tanggal' => '20 Mei 2026',
    ]);
    expect($f[$b->id_lahan]['properties'])->toMatchArray(['kritis' => 'Agak Kritis', 'kritis_class' => 'agak', 'rehab' => false]);

    // data sumber (BPDAS) tidak ditimpa
    expect($a->fresh()->kritis)->toBe('Kritis');
});

it('tampilan publik hanya memperhitungkan kegiatan yang dipublikasikan; pengguna login melihat semuanya', function () {
    $a = petak(['kritis' => 'Kritis']);
    keg($a, ['status' => 'Selesai', 'tanggal_status' => '2026-05-20', 'is_published' => false]);

    expect($this->getJson('/data/lahan.geojson')->json('features.0.properties.kritis_class'))->toBe('kritis');

    $this->actingAs(User::factory()->petugas()->create());
    expect($this->getJson('/data/lahan.geojson')->json('features.0.properties.kritis_class'))->toBe('pulih');
});

it('filter, dashboard, dan statistik ikut menganggap petak direhabilitasi bukan lahan kritis', function () {
    $a = petak(['kritis' => 'Kritis', 'luas_ha' => 4]);
    petak(['kritis' => 'Agak Kritis', 'luas_ha' => 6]);
    keg($a, ['status' => 'Selesai', 'tanggal_status' => '2026-05-20', 'is_published' => true]);

    // opsi filter: "Telah Direhabilitasi" muncul, "Kritis" (satu-satunya petaknya sudah pulih) tidak
    preg_match('/<select id="f-kritis".*?<\/select>/s', $this->get('/')->getContent(), $m);
    expect($m[0])->toContain('Telah Direhabilitasi')->toContain('Agak Kritis')->not->toContain('value="kritis"');

    // dashboard: donut memakai warna peta, KPI luas kritis hanya 6 Ha (petak 4 Ha sudah pulih)
    $html = $this->actingAs(User::factory()->admin()->create())->get('/admin/dashboard')->assertOk()->getContent();
    preg_match('/window\.APP = (\{.*?\});<\/script>/s', $html, $mm);
    $donut = json_decode($mm[1], true)['dashboard']['donut'];
    expect($donut['labels'])->toBe(['Telah Direhabilitasi', 'Agak Kritis'])
        ->and(array_map('floatval', $donut['values']))->toBe([4.0, 6.0])
        ->and($donut['colors'])->toBe(['#06b6d4', '#f97316']);
    expect(preg_replace('/\s+/', ' ', strip_tags($html)))->toContain('6.0')->not->toContain('10.0 Ha');
});

// =====================================================================================
// Monitoring: satu baris per kegiatan, riwayat lengkap pada tombol Detail
// =====================================================================================

function riwayatDariHalaman(string $html): array
{
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
    $out = [];
    foreach ((new DOMXPath($dom))->query('//button[@data-riwayat]') as $b) {
        $out[] = ['nama' => $b->getAttribute('data-name'), 'riwayat' => json_decode($b->getAttribute('data-riwayat'), true)];
    }

    return $out;
}

it('monitoring tampil satu baris per kegiatan (terbaru) dan seluruh catatan ada di Detail', function () {
    $petugas = User::factory()->petugas()->create();
    $l = petak();
    $k1 = keg($l, ['nama_kegiatan' => 'Kegiatan Satu', 'id_user' => $petugas->id_user]);
    $k2 = keg(petak(), ['nama_kegiatan' => 'Kegiatan Dua', 'id_user' => $petugas->id_user]);
    foreach ([['2026-03-01', 10, 'Stabil', 'Catatan awal'], ['2026-04-01', 40, 'Membaik', 'Catatan tengah'], ['2026-05-01', 70, 'Membaik', 'Catatan terbaru']] as [$t, $p, $kd, $c]) {
        Monitoring::create(['id_kegiatan' => $k1->id_kegiatan, 'id_user' => $petugas->id_user, 'tanggal_monitoring' => $t, 'progres_persen' => $p, 'kondisi_lahan' => $kd, 'catatan' => $c]);
    }
    Monitoring::create(['id_kegiatan' => $k2->id_kegiatan, 'id_user' => $petugas->id_user, 'tanggal_monitoring' => '2026-04-10', 'progres_persen' => 20, 'kondisi_lahan' => 'Stabil', 'catatan' => 'Dua satu']);

    $html = $this->actingAs($petugas)->get('/petugas/monitoring')->assertOk()->getContent();

    // 2 kegiatan -> 2 baris (bukan 4 catatan); baris menampilkan catatan TERBARU
    expect(substr_count($html, 'class="act act-detail"'))->toBe(2);
    expect($html)->toContain('Catatan terbaru')->toContain('3 catatan')->not->toContain('Catatan awal</td>');

    // Detail memuat semua catatan, terbaru di atas
    $r = collect(riwayatDariHalaman($html))->firstWhere('nama', 'Kegiatan Satu')['riwayat'];
    expect($r)->toHaveCount(3)->and(array_column($r, 'progres'))->toBe([70, 40, 10])
        ->and($r[0]['can_edit'])->toBeTrue()->and($r[0]['can_delete'])->toBeFalse();   // petugas: ubah ya, hapus tidak
});

it('Admin melihat riwayat dengan hak ubah dan hapus pada setiap catatan', function () {
    $k = keg(petak(), ['nama_kegiatan' => 'Kegiatan Admin']);
    Monitoring::create(['id_kegiatan' => $k->id_kegiatan, 'tanggal_monitoring' => '2026-03-01', 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil']);
    Monitoring::create(['id_kegiatan' => $k->id_kegiatan, 'tanggal_monitoring' => '2026-04-01', 'progres_persen' => 30, 'kondisi_lahan' => 'Membaik']);

    $html = $this->actingAs(User::factory()->admin()->create())->get('/admin/monitoring')->assertOk()->getContent();
    $r = riwayatDariHalaman($html)[0]['riwayat'];

    expect($r)->toHaveCount(2)->and(array_column($r, 'can_edit'))->toBe([true, true])->and(array_column($r, 'can_delete'))->toBe([true, true]);
    expect($html)->not->toContain('Catat Monitoring');
});

it('petugas tidak melihat monitoring pada kegiatan milik petugas lain', function () {
    [$a, $b] = [User::factory()->petugas()->create(), User::factory()->petugas()->create()];
    $milikB = keg(petak(), ['nama_kegiatan' => 'Rahasia Petugas B', 'id_user' => $b->id_user]);
    Monitoring::create(['id_kegiatan' => $milikB->id_kegiatan, 'id_user' => $b->id_user, 'tanggal_monitoring' => '2026-03-01', 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil']);

    $this->actingAs($a)->get('/petugas/monitoring')->assertOk()->assertDontSee('Rahasia Petugas B');
    $this->actingAs($b)->get('/petugas/monitoring')->assertOk()->assertSee('Rahasia Petugas B');
});

// =====================================================================================
// Tambah data petak lahan secara manual
// =====================================================================================

it('Admin menambah petak lahan manual; luas dihitung otomatis dari polygon bila dikosongkan', function () {
    $admin = User::factory()->admin()->create();
    $poly = ['type' => 'Polygon', 'coordinates' => [[[102.30, -3.80], [102.31, -3.80], [102.31, -3.81], [102.30, -3.81], [102.30, -3.80]]]];
    $form = ['kecamatan_id' => 7, 'bpdas' => 'Ketahun', 'kritis' => 'Kritis'];

    $this->actingAs($admin)->postJson('/admin/lahan', $form + ['geometri' => json_encode($poly)])->assertOk();

    $l = DataLahan::firstOrFail();
    expect($l->kecamatan)->toBe('Sungai Serut')->and($l->wilayah)->toBe('Kota Bengkulu')->and($l->kritis)->toBe('Kritis')
        ->and($l->objectid)->toBeNull()
        ->and($l->luas_ha)->toBeGreaterThan(110)->toBeLessThan(125)   // ~0,01° x 0,01° ≈ 120 Ha
        ->and($l->geometri['type'])->toBe('Polygon');

    // luas yang diisi manual dipakai apa adanya
    $this->actingAs($admin)->postJson('/admin/lahan', $form + ['luas_ha' => '7.5', 'geometri' => json_encode($poly)])->assertOk();
    expect(DataLahan::latest('id_lahan')->first()->luas_ha)->toBe(7.5);

    // tampil di peta
    expect($this->getJson('/data/lahan.geojson')->json('features'))->toHaveCount(2);
});

it('geometri manual: ring otomatis ditutup, Feature/MultiPolygon diterima, bentuk tidak valid ditolak', function () {
    $admin = User::factory()->admin()->create();
    $form = ['kecamatan_id' => 1, 'bpdas' => 'Ketahun', 'kritis' => 'Agak Kritis'];
    $terbuka = ['type' => 'Polygon', 'coordinates' => [[[102.30, -3.80], [102.31, -3.80], [102.31, -3.81]]]];   // belum tertutup (3 titik)

    $this->actingAs($admin)->postJson('/admin/lahan', $form + ['geometri' => json_encode(['type' => 'Feature', 'properties' => [], 'geometry' => $terbuka])])->assertOk();
    $ring = DataLahan::first()->geometri['coordinates'][0];
    expect($ring)->toHaveCount(4)->and($ring[0])->toBe($ring[3]);

    $multi = ['type' => 'MultiPolygon', 'coordinates' => [[[[102.30, -3.80], [102.31, -3.80], [102.31, -3.81], [102.30, -3.80]]]]];
    $this->actingAs($admin)->postJson('/admin/lahan', $form + ['geometri' => json_encode($multi)])->assertOk();

    foreach ([
        'bukan json',
        json_encode(['type' => 'Point', 'coordinates' => [102.3, -3.8]]),
        json_encode(['type' => 'Polygon', 'coordinates' => [[[102.30, -3.80], [102.31, -3.80]]]]),          // < 3 titik
        json_encode(['type' => 'Polygon', 'coordinates' => [[[300, -3.80], [102.31, -3.80], [102.31, -3.81]]]]), // bujur di luar jangkauan
    ] as $buruk) {
        $this->actingAs($admin)->postJson('/admin/lahan', $form + ['geometri' => $buruk])->assertStatus(422)->assertJsonValidationErrors('geometri');
    }
    expect(DataLahan::count())->toBe(2);
});

it('tambah petak manual: validasi isian dan hanya Admin', function () {
    $poly = json_encode(['type' => 'Polygon', 'coordinates' => [[[102.30, -3.80], [102.31, -3.80], [102.31, -3.81], [102.30, -3.80]]]]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->postJson('/admin/lahan', [])->assertStatus(422)->assertJsonValidationErrors(['kecamatan_id', 'bpdas', 'kritis', 'geometri']);
    $this->actingAs($admin)->postJson('/admin/lahan', ['kecamatan_id' => 99, 'bpdas' => 'K', 'kritis' => 'Ngawur', 'geometri' => $poly])->assertJsonValidationErrors(['kecamatan_id', 'kritis']);

    $this->actingAs(User::factory()->petugas()->create())->postJson('/admin/lahan', ['kecamatan_id' => 1, 'bpdas' => 'K', 'kritis' => 'Kritis', 'geometri' => $poly])->assertForbidden();
    expect(DataLahan::count())->toBe(0);

    // tombol & modal tampil di halaman Data Lahan
    $this->actingAs($admin)->get('/admin/lahan')->assertOk()->assertSee('Tambah Data')->assertSee('id="lahan-draw-map"', false)->assertSee('Impor GeoJSON');
});
