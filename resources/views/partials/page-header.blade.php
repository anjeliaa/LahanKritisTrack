<section class="page-header">
    <div class="ph-left">
        <div class="ph-icon"><svg class="ic"><use href="#i-{{ $ph['icon'] }}"/></svg></div>
        <div>
            <div class="ph-path">{{ $ph['path'] }}</div>
            <h1 class="ph-title">{{ $ph['title'] }}</h1>
            <p class="ph-desc">{{ $ph['desc'] }}</p>
        </div>
    </div>
    <div class="ph-stats">
        @foreach($ph['stats'] as $s)
        <div class="ph-tile">
            <div class="l">{{ $s['label'] }}</div>
            <div class="v tone-{{ $s['tone'] }}">{{ $s['value'] }}</div>
            <div class="s">{{ $s['sub'] }}</div>
        </div>
        @endforeach
    </div>
</section>
