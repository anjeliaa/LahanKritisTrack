<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Tanggapan sukses untuk form modal. Pesan di-flash agar tampil sebagai toast setelah
     * halaman dimuat ulang oleh app.js; permintaan non-AJAX dialihkan kembali.
     */
    protected function ok(Request $request, string $message)
    {
        $request->session()->flash('success', $message);

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back();
    }
}
