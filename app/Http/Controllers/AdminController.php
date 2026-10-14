<?php

namespace App\Http\Controllers;

use App\Support\DummyData;

class AdminController extends InternalController
{
    protected string $role = 'admin';

    public function dashboard()  { return $this->render('admin.dashboard', 'dashboard', 'admin.dashboard', ['dash' => DummyData::get('dashboard')]); }
    public function akun()       { return $this->render('admin.akun', 'akun', 'admin.akun', ['users' => DummyData::get('users')]); }
    public function lahan()      { return $this->render('admin.lahan', 'lahan', 'admin.lahan'); }
    public function kegiatan()   { return $this->render('admin.kegiatan', 'kegiatan', 'admin.kegiatan'); }
    public function monitoring() { return $this->render('admin.monitoring', 'monitoring', 'admin.monitoring'); }
    public function laporan()    { return $this->render('admin.laporan', 'laporan', 'admin.laporan', ['lap' => DummyData::get('laporan')]); }
}
