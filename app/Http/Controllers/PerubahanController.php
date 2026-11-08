<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerubahanController extends Controller
{
    /** Petugas menandai semua perubahan dari Admin sebagai sudah dibaca. */
    public function baca(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $request->expectsJson()
            ? response()->json(['message' => 'Perubahan ditandai sudah dibaca.'])
            : back();
    }
}
