<?php

namespace App\Support;

use App\Models\DataLahan;
use InvalidArgumentException;

/**
 * Mengimpor FeatureCollection GeoJSON (Polygon / MultiPolygon) ke tabel data_lahan.
 *
 * Atribut dikenali tanpa membedakan huruf besar/kecil:
 *   objectid | id_lahan | id   -> data_lahan.objectid (kunci upsert bila ada)
 *   kritis | kritis_class       -> tingkat kekritisan
 *   kecamatan | kec_slug        -> kecamatan_id
 *   luas | luas_ha              -> luas_ha (dihitung dari geometri bila tidak ada)
 *   bpdas, wilayah              -> opsional
 */
class LahanImporter
{
    /** @return array{created:int,updated:int,skipped:int,errors:array<int,string>} */
    public static function fromJson(string $json): array
    {
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new InvalidArgumentException('Berkas bukan JSON yang valid.');
        }

        if (($data['type'] ?? null) === 'Feature') {
            $data = ['type' => 'FeatureCollection', 'features' => [$data]];
        }

        if (($data['type'] ?? null) !== 'FeatureCollection' || ! is_array($data['features'] ?? null)) {
            throw new InvalidArgumentException('Berkas harus berupa GeoJSON FeatureCollection.');
        }

        $r = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($data['features'] as $i => $feature) {
            $no = $i + 1;

            try {
                [$key, $attrs] = self::parse($feature);
            } catch (InvalidArgumentException $e) {
                $r['skipped']++;
                $r['errors'][] = "Fitur #{$no}: ".$e->getMessage();

                continue;
            }

            if ($key !== null) {
                $model = DataLahan::updateOrCreate(['objectid' => $key], $attrs);
                $model->wasRecentlyCreated ? $r['created']++ : $r['updated']++;
            } else {
                DataLahan::create($attrs);
                $r['created']++;
            }
        }

        return $r;
    }

    /** @return array{0:?int,1:array<string,mixed>} */
    private static function parse(mixed $feature): array
    {
        if (! is_array($feature) || ($feature['type'] ?? null) !== 'Feature') {
            throw new InvalidArgumentException('bukan objek Feature.');
        }

        $geom = $feature['geometry'] ?? null;
        if (! is_array($geom) || ! in_array($geom['type'] ?? null, ['Polygon', 'MultiPolygon'], true) || ! is_array($geom['coordinates'] ?? null)) {
            throw new InvalidArgumentException('geometri harus Polygon atau MultiPolygon.');
        }
        self::assertCoordinates($geom['coordinates'], $geom['type'] === 'Polygon' ? 3 : 4);

        $p = array_change_key_case((array) ($feature['properties'] ?? []), CASE_LOWER);

        // kecamatan
        $kecId = self::kecamatanId($p);
        if ($kecId === null) {
            $nama = $p['kecamatan'] ?? $p['kec_slug'] ?? '(kosong)';
            throw new InvalidArgumentException("kecamatan \"{$nama}\" tidak dikenali.");
        }

        // tingkat kekritisan
        $kritis = self::kritisLabel($p['kritis'] ?? $p['kritis_class'] ?? null);
        if ($kritis === null) {
            throw new InvalidArgumentException('tingkat kekritisan tidak dikenali.');
        }

        // luas (Ha)
        $luas = $p['luas'] ?? $p['luas_ha'] ?? null;
        $luas = is_numeric($luas) ? round((float) $luas, 2) : round(self::areaHa($geom), 2);

        $key = $p['objectid'] ?? $p['id_lahan'] ?? $p['id'] ?? null;

        return [
            is_numeric($key) ? (int) $key : null,
            [
                'wilayah'      => ($p['wilayah'] ?? null) ?: 'Kota Bengkulu',
                'kecamatan_id' => $kecId,
                'kecamatan'    => config("lahan.kecamatan.{$kecId}.nama"),
                'bpdas'        => ($p['bpdas'] ?? null) ?: config('lahan.bpdas_default'),
                'kritis'       => $kritis,
                'luas_ha'      => $luas,
                'geometri'     => $geom,
            ],
        ];
    }

    public static function kecamatanId(array $props): ?int
    {
        $norm = fn ($s) => str_replace('singaran', 'singgaran', strtolower(trim((string) $s)));
        $nama = $norm($props['kecamatan'] ?? '');
        $slug = $norm($props['kec_slug'] ?? '');

        foreach (config('lahan.kecamatan') as $id => $k) {
            if (($nama !== '' && $norm($k['nama']) === $nama) || ($slug !== '' && $k['slug'] === $slug)) {
                return $id;
            }
        }

        return null;
    }

    public static function kritisLabel(mixed $v): ?string
    {
        $v = strtolower(trim((string) $v));
        if ($v === '') {
            return null;
        }

        foreach (config('lahan.kritis') as $class => $label) {
            if ($v === $class || $v === strtolower($label)) {
                return $label;
            }
        }

        return null;
    }

    private static function assertCoordinates(array $c, int $depth): void
    {
        if ($depth === 1) {
            if (count($c) < 2 || ! is_numeric($c[0]) || ! is_numeric($c[1]) || abs($c[0]) > 180 || abs($c[1]) > 90) {
                throw new InvalidArgumentException('koordinat tidak valid (harus [bujur, lintang] dalam WGS84).');
            }

            return;
        }

        if ($c === []) {
            throw new InvalidArgumentException('koordinat kosong.');
        }

        foreach ($c as $child) {
            if (! is_array($child)) {
                throw new InvalidArgumentException('struktur koordinat tidak valid.');
            }
            self::assertCoordinates($child, $depth - 1);
        }
    }

    /** Luas geodesik (Ha) dari geometri WGS84 (rumus luas poligon bola). */
    public static function areaHa(array $geom): float
    {
        $polys = $geom['type'] === 'Polygon' ? [$geom['coordinates']] : $geom['coordinates'];
        $m2 = 0.0;

        foreach ($polys as $rings) {
            foreach ($rings as $idx => $ring) {
                $a = self::ringArea($ring);
                $m2 += $idx === 0 ? $a : -$a; // cincin pertama = luar, sisanya lubang
            }
        }

        return abs($m2) / 10000;
    }

    private static function ringArea(array $ring): float
    {
        $R = 6378137.0;
        $n = count($ring);
        if ($n < 4) {
            return 0.0;
        }

        $sum = 0.0;
        for ($i = 0; $i < $n - 1; $i++) {
            [$x1, $y1] = $ring[$i];
            [$x2, $y2] = $ring[$i + 1];
            $sum += deg2rad($x2 - $x1) * (2 + sin(deg2rad($y1)) + sin(deg2rad($y2)));
        }

        return abs($sum * $R * $R / 2);
    }
}
