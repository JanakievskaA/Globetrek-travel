@props([
    'paginator',
    'headers' => [],
    'empty' => 'Nothing to show yet.',
    'filters' => null,
])

{{--
    Shared table chrome: filter bar, column headers, empty state and pagination.
    Each resource supplies its own rows via the default slot.
--}}
<div class="adm-panel">
    @if ($filters)
        <div class="adm-filters">{{ $filters }}</div>
    @endif

    @if ($paginator->isEmpty())
        <div class="adm-empty">
            <p>{{ $empty }}</p>
        </div>
    @else
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th @if (is_array($header) && ($header['align'] ?? null)) style="text-align:{{ $header['align'] }}" @endif>
                                {{ is_array($header) ? $header['label'] : $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        <div class="adm-table-foot">
            <span class="adm-hint">
                Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
                of {{ number_format($paginator->total()) }}
            </span>
            {{ $paginator->links() }}
        </div>
    @endif
</div>
