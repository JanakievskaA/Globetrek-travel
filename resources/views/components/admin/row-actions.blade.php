@props([
    'edit' => null,
    'destroy' => null,
    'view' => null,
    'confirm' => 'Delete this record permanently?',
])

<div class="adm-row-actions">
    @if ($view)
        <a href="{{ $view }}" class="adm-icon-btn" title="View on site" target="_blank" rel="noopener">
            <i class="icon icon-arrow-right"></i>
        </a>
    @endif

    @if ($edit)
        <a href="{{ $edit }}" class="adm-icon-btn" title="Edit">
            <img src="{{ asset('assets/images/icons/pen.svg') }}" alt="Edit" width="15">
        </a>
    @endif

    {{ $slot }}

    {{-- Deleting is admin-only (see routes/web.php); managers never see the button. --}}
    @if ($destroy && auth()->user()?->isAdmin())
        <form action="{{ $destroy }}" method="POST" data-confirm="{{ $confirm }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="adm-icon-btn adm-icon-btn--danger" title="Delete">
                <i class="icon icon-X"></i>
            </button>
        </form>
    @endif
</div>
