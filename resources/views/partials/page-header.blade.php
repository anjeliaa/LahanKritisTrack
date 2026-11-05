@php $adaStat = ! empty($ph['stats']); @endphp

<section class="page-header {{ $adaStat ? 'has-stats' : '' }}">
    <div class="ph-left">
        <div class="ph-icon"><svg class="ic" aria-hidden="true"><use href="#i-{{ $ph['icon'] }}"/></svg></div>
        <div>
            <div class="ph-path">{{ $ph['path'] }}</div>
            <h1 class="ph-title">{{ $ph['title'] }}</h1>
            <p class="ph-desc">{{ $ph['desc'] }}</p>
        </div>
    </div>
</section>

@if($adaStat)
<section class="hb-stats">
    @foreach($ph['stats'] as $s)
    <div class="card hb-stat">
        <div class="hb-l">{{ $s['label'] }}</div>
        <div class="hb-v">{{ $s['value'] }}</div>
        <span class="stat-sub {{ $s['tone'] }}">{{ $s['sub'] }}</span>
    </div>
    @endforeach
</section>
@endif