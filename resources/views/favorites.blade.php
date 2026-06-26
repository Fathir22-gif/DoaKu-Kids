@extends('layouts.app')

@section('title', 'Favorit Saya')

@section('content')
<div class="section-header">
    <h1 class="section-title">
        <span class="icon">❤️</span> Favorit Saya
    </h1>
    <p class="section-subtitle">Doa-doa pilihan yang kamu simpan</p>
</div>

@if($favorites->isEmpty())
    {{-- Empty State --}}
    <div class="empty-state">
        <div class="empty-icon">❤️</div>
        <h2 class="empty-title">Belum ada doa favorit.</h2>
        <p class="empty-desc">Yuk tambahkan doa favoritmu dari halaman utama.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">
            🏠 Ke Halaman Utama
        </a>
    </div>
@else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        @foreach($favorites as $favorite)
        <div class="card" id="favorite-card-{{ $favorite->prayer_id }}"
             style="border-radius: var(--radius-xl); overflow: hidden; transition: var(--transition);">

            {{-- Top color bar --}}
            <div style="height: 4px; background: linear-gradient(90deg, var(--primary), var(--primary-light), var(--secondary));"></div>

            <div class="card-body" style="display: flex; flex-direction: column; gap: 1rem;">
                {{-- Prayer ID badge --}}
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span class="badge badge-green">Doa #{{ $favorite->prayer_id }}</span>
                    <span style="font-size: 1.5rem; opacity: 0.7;">🤲</span>
                </div>

                {{-- Title --}}
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); line-height: 1.4;">
                    {{ $favorite->prayer_title }}
                </h3>

                {{-- Actions --}}
                <div style="display: flex; gap: 0.75rem; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <a href="{{ route('doa.detail', $favorite->prayer_id) }}"
                       class="btn btn-primary btn-sm"
                       style="flex: 1; text-align: center; justify-content: center;">
                        📖 Buka Doa
                    </a>

                    <form method="POST" action="{{ route('favorites.toggle') }}"
                          onsubmit="handleRemoveFavorite(event, '{{ $favorite->prayer_id }}')">
                        @csrf
                        <input type="hidden" name="prayer_id" value="{{ $favorite->prayer_id }}">
                        <input type="hidden" name="prayer_title" value="{{ $favorite->prayer_title }}">
                        <button type="submit" class="btn btn-danger btn-sm"
                                title="Hapus dari favorit">
                            ❤️ Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection

@push('scripts')
<script>
function handleRemoveFavorite(event, prayerId) {
    event.preventDefault();
    var form = event.target;
    var card = document.getElementById('favorite-card-' + prayerId);

    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    }

    setTimeout(function() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) { form.submit(); return; }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(new FormData(form)),
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (card) card.remove();
            var remaining = document.querySelectorAll('[id^="favorite-card-"]');
            if (remaining.length === 0) {
                location.reload();
            }
        })
        .catch(function() {
            form.submit();
        });
    }, 300);
}
</script>
@endpush
