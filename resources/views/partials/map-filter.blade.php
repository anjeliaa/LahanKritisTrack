{{-- Filter peta — dipakai bersama oleh halaman masyarakat dan petugas (id harus sesuai public/js/map.js).
     Variabel: $kecamatan, $kritisFilter, $status_opts --}}
<div class="filters">
    <div class="field">
        <label for="f-q">Pencarian</label>
        <div class="search"><svg class="ic"><use href="#i-search"/></svg><input id="f-q" class="input" type="search" placeholder="Cari kecamatan atau nama kegiatan..." autocomplete="off"></div>
    </div>
    <div class="field">
        <label for="f-kec">Kecamatan</label>
        <select id="f-kec" class="select" autocomplete="off">
            <option value="">Semua Kecamatan</option>
            @foreach($kecamatan as $k)
            <option value="{{ $k['slug'] }}">{{ $k['nama'] }}@unless($k['ada_data']) (belum ada data)@endunless</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-kritis">Tingkat Kekritisan</label>
        <select id="f-kritis" class="select" autocomplete="off">
            <option value="">Semua Tingkat</option>
            @foreach($kritisFilter as $o)
            <option value="{{ $o['v'] }}">{{ $o['l'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-status">Status Kegiatan</label>
        <select id="f-status" class="select" autocomplete="off">
            <option value="">Semua Status</option>
            @foreach($status_opts as $s)
            <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <button type="button" id="f-reset" class="btn btn-outline" style="height:41px"><svg class="ic"><use href="#i-refresh"/></svg> Reset</button>
</div>

<div class="legend">
    <span class="count" id="result-count"></span>
</div>
