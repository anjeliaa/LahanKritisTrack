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

it('petugas membuat & mengubah kegiatan, validasi berjalan, hapus hanya admin', function () {
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

    $this->actingAs($petugas)->postJson('/kegiatan/status', ['kegiatan' => $k->id_kegiatan, 'status' => 'Selesai'])->assertOk();
    expect($k->fresh()->status)->toBe('Selesai');

    $this->actingAs($petugas)->deleteJson("/kegiatan/{$k->id_kegiatan}")->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->deleteJson("/kegiatan/{$k->id_kegiatan}")->assertOk();
    expect(KegiatanRehabilitasi::count())->toBe(0);
});

// ---------------------------------------------------------------- monitoring

it('monitoring menyimpan foto & menurunkan progres dan realisasi kegiatan', function () {
    Storage::fake('public');
    $k = keg(petak(), ['target_luas' => 20]);
    $petugas = User::factory()->petugas()->create();

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
    $k = keg(petak());

    $this->actingAs(User::factory()->petugas()->create())->post('/monitoring', [
        'id_kegiatan' => $k->id_kegiatan, 'progres_persen' => 150, 'kondisi_lahan' => 'Membaik',
        'tanggal_monitoring' => now()->addDays(3)->toDateString(),
        'foto_dokumentasi' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
    ], JSONH)->assertStatus(422)->assertJsonValidationErrors(['progres_persen', 'tanggal_monitoring', 'foto_dokumentasi']);
});

it('petugas hanya boleh mengubah monitoring miliknya; admin boleh semua dan menghapus', function () {
    Storage::fake('public');
    $k = keg(petak());
    [$a, $b] = [User::factory()->petugas()->create(), User::factory()->petugas()->create()];
    $m = Monitoring::create(['id_kegiatan' => $k->id_kegiatan, 'id_user' => $a->id_user, 'tanggal_monitoring' => '2026-04-01', 'progres_persen' => 10, 'kondisi_lahan' => 'Stabil', 'foto_dokumentasi' => 'monitoring/lama.jpg']);
    Storage::disk('public')->put('monitoring/lama.jpg', 'x');
    $payload = ['id_kegiatan' => $k->id_kegiatan, 'progres_persen' => 55, 'kondisi_lahan' => 'Membaik'];

    $this->actingAs($b)->put("/monitoring/{$m->id_monitoring}", $payload, JSONH)->assertForbidden();
    $this->actingAs($a)->put("/monitoring/{$m->id_monitoring}", $payload, JSONH)->assertOk();
    expect($m->fresh()->progres_persen)->toBe(55)->and($m->fresh()->foto_dokumentasi)->toBe('monitoring/lama.jpg'); // foto dipertahankan

    $this->actingAs($a)->delete("/monitoring/{$m->id_monitoring}", [], JSONH)->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->delete("/monitoring/{$m->id_monitoring}", [], JSONH)->assertOk();
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

    // 7 kecamatan memiliki data -> angka yang sama di dashboard dan halaman publik
    expect($app['kecamatan'])->toHaveCount(7);
    $this->actingAs($admin)->get('/admin/dashboard')->assertSee('Berdasarkan data 7 Kecamatan');
    auth()->logout();
    $this->get('/')->assertSee('Berdasarkan data 7 Kecamatan');
});
