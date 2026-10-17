<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Format tampilan bahasa Indonesia. */
class Fmt
{
    /** 05 Mar 2026 */
    public static function tanggal(?CarbonInterface $d, string $format = 'd M Y'): string
    {
        return $d ? $d->copy()->locale('id')->translatedFormat($format) : '-';
    }

    /** 1,482.5 (gaya yang dipakai antarmuka: koma ribuan, titik desimal) */
    public static function ha(float|int|null $n, int $dec = 1): string
    {
        return number_format((float) $n, $dec, '.', ',');
    }
}
