<?php

namespace App\Http\Controllers;

class PetugasController extends InternalController
{
    protected string $role = 'petugas';

    public function peta()       { return $this->render('petugas.peta', 'peta', 'petugas.peta'); }
    public function kegiatan()   { return $this->render('petugas.kegiatan', 'kegiatan', 'petugas.kegiatan'); }
    public function monitoring() { return $this->render('petugas.monitoring', 'monitoring', 'petugas.monitoring'); }
}
