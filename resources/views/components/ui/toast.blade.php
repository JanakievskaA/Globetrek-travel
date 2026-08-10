@php
    $flashes = collect([
        'success' => session('success'),
        'error' => session('error'),
        'info' => session('info'),
    ])->filter();
@endphp

<div class="gt-toast-stack" id="gt-toast-stack" aria-live="polite">
    @foreach ($flashes as $tone => $message)
        <div class="gt-toast gt-toast--{{ $tone }}">
            <span class="gt-toast__text">{{ $message }}</span>
            <button type="button" class="gt-toast__close" aria-label="Dismiss">&times;</button>
        </div>
    @endforeach
</div>
