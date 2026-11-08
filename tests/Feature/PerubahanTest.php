<?php

use App\Models\DataLahan;
use App\Models\KegiatanRehabilitasi;
use App\Models\Monitoring;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function petakPerubahan(): DataLahan
{
    return DataLahan::create([
        'objectid' => random_int(1000, 99999), 'wilayah' => 'Kota Bengkulu',
        'kecamatan_id' => 1, 'kecamatan' => 'Selebar', 'bpdas' => 'Ketahun',
        'kritis' => 'Kritis', 'luas_ha' => 5,
        'geometri' => ['type' => 'Polygon', 'coordinates' => [[[102.3, -3.8], [102.31, -3.8], [102.31, -3.81], [102.3, -3.8]]]],
    ]);
}

function kegiatanPerubahan(DataLahan $l, User $petugas): KegiatanRehabilitasi
{
    return KegiatanRehabilitasi::create([
        'id_lahan' => $l->id_lahan, 'nama_kegiatan' => 'Reboisasi Uji', 'target_luas' => 10,
        'status' => 'Berjalan', 'is_published' => true, 'id_user' => $petugas->id_user,
        'tanggal_mulai' => '2026-01-10', 'tanggal_selesai' => '2026-05-10',
    ]);
}

function payloadKegiatan(KegiatanRehabilitasi $k, array $ubah = []): array
{
    return $ubah + [
        'nama_kegiatan' => $k->nama_kegiatan, 'id_lahan' => $k->id_lahan, 'target_luas' => 10,
        'tanggal_mulai' => '2026-01-10', 'tanggal_selesai' => '2026-05-10',
        'status' => 'Berjalan', 'is_published' => '1',
    ];
}

it('petugas diberi tahu bila Admin mengubah kegiatan miliknya, hanya kolom yang berubah', function () {
    $admin = User::factory()->admin()->create();
    [$a, $b] = [User::factory()->petugas()->create(), User::factory()->petugas()->create()];
    $k = kegiatanPerubahan(petakPerubahan(), $a);

    // Admin menyimpan tanpa mengubah apa pun -> tidak ada pemberitahuan
    $this->actingAs($admin)->putJson("/kegiatan/{$k->id_kegiatan}", payloadKegiatan($k))->assertOk();
    expect($a->unreadNotifications()->count())->toBe(0);

    // Admin mengubah tanggal selesai dan target
    $this->actingAs($admin)->putJson("/kegiatan/{$k->id_kegiatan}", payloadKegiatan($k, [
        'tanggal_selesai' => '2026-06-20', 'target_luas' => 12.5,
    ]))->assertOk();

    expect($a->unreadNotifications()->count())->toBe(1)->and($b->unreadNotifications()->count())->toBe(0);

    $data = $a->unreadNotifications()->first()->data;
    expect($data['jenis'])->toBe('kegiatan')->and($data['oleh'])->toBe($admin->nama)->and($data['kegiatan'])->toBe('Reboisasi Uji');

    $perubahan = collect($data['perubahan'])->keyBy('label');
    expect($perubahan->keys()->sort()->values()->all())->toBe(['Tanggal selesai (target)', 'Target luas']);
    expect($perubahan['Tanggal selesai (target)'])->toMatchArray(['lama' => '10 Mei 2026', 'baru' => '20 Juni 2026']);
    expect($perubahan['Target luas'])->toMatchArray(['lama' => '10 Ha', 'baru' => '12,5 Ha']);

    // Petugas mengubah miliknya sendiri -> tidak memicu pemberitahuan baru
    $this->actingAs($a)->putJson("/kegiatan/{$k->id_kegiatan}", payloadKegiatan($k, ['target_luas' => 20, 'tanggal_selesai' => '2026-06-20']))->assertOk();
    expect($a->unreadNotifications()->count())->toBe(1);
});

it('petugas lama dan baru sama-sama diberi tahu saat Admin memindahkan penanggung jawab', function () {
    $admin = User::factory()->admin()->create();
    [$a, $b] = [User::factory()->petugas()->create(), User::factory()->petugas()->create()];
    $k = kegiatanPerubahan(petakPerubahan(), $a);

    $this->actingAs($admin)->putJson("/kegiatan/{$k->id_kegiatan}", payloadKegiatan($k, ['id_user' => $b->id_user]))->assertOk();

    expect($a->unreadNotifications()->count())->toBe(1)->and($b->unreadNotifications()->count())->toBe(1);
    expect(collect($b->unreadNotifications()->first()->data['perubahan'])->pluck('label')->all())->toBe(['Petugas penanggung jawab']);
});

it('petugas diberi tahu bila Admin mengubah laporan monitoring yang ia catat', function () {
    $admin = User::factory()->admin()->create();
    $a = User::factory()->petugas()->create();
    $k = kegiatanPerubahan(petakPerubahan(), $a);
    $m = Monitoring::create([
        'id_kegiatan' => $k->id_kegiatan, 'id_user' => $a->id_user, 'tanggal_monitoring' => '2026-04-01',
        'progres_persen' => 10, 'kondisi_lahan' => 'Stabil', 'catatan' => 'Awal',
    ]);

    $this->actingAs($admin)->putJson("/monitoring/{$m->id_monitoring}", [
        'id_kegiatan' => $k->id_kegiatan, 'tanggal_monitoring' => '2026-04-01',
        'progres_persen' => 25, 'kondisi_lahan' => 'Stabil', 'catatan' => 'Awal (catatan dari admin) dipercepat',
    ])->assertOk();

    expect($a->unreadNotifications()->count())->toBe(1);
    $data = $a->unreadNotifications()->first()->data;
    expect($data['jenis'])->toBe('monitoring')->and($data['kegiatan'])->toBe('Reboisasi Uji');

    $perubahan = collect($data['perubahan'])->keyBy('label');
    expect($perubahan->keys()->sort()->values()->all())->toBe(['Catatan', 'Progres']);
    expect($perubahan['Progres'])->toMatchArray(['lama' => '10%', 'baru' => '25%']);
});

it('halaman petugas menampilkan perubahan dari Admin, lencana, dan bisa ditandai sudah dibaca', function () {
    $admin = User::factory()->admin()->create();
    [$a, $b] = [User::factory()->petugas()->create(), User::factory()->petugas()->create()];
    $k = kegiatanPerubahan(petakPerubahan(), $a);

    $this->actingAs($admin)->putJson("/kegiatan/{$k->id_kegiatan}", payloadKegiatan($k, ['tanggal_selesai' => '2026-06-20']))->assertOk();

    $this->actingAs($a)->get('/petugas/kegiatan')->assertOk()
        ->assertSee('data-nf-toggle', false)->assertSee('Baru (1)')
        ->assertSee('Tanggal selesai (target): 10 Mei 2026 → 20 Juni 2026', false)
        ->assertSee('data-lencana', false);

    // petugas lain tidak melihat perubahan itu
    $this->actingAs($b)->get('/petugas/kegiatan')->assertOk()->assertDontSee('Baru (1)')->assertDontSee('Tandai sudah dibaca');

    // admin tidak boleh memakai rute petugas
    $this->actingAs($admin)->post('/petugas/perubahan/baca')->assertForbidden();

    $this->actingAs($a)->post('/petugas/perubahan/baca')->assertRedirect();
    expect($a->unreadNotifications()->count())->toBe(0);

    // setelah dibaca: masuk riwayat, lencana hilang
    $this->actingAs($a)->get('/petugas/kegiatan')->assertOk()
        ->assertSee('Sebelumnya')
        ->assertDontSee('data-lencana', false)
        ->assertDontSee('Tandai sudah dibaca');
});
