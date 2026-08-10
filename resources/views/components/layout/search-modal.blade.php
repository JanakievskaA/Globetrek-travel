<div class="modal fade modalCenter" id="modalSearch" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <form action="{{ route('tours.index') }}" method="GET" class="gt-search-modal">
                    <label for="gt-modal-q" class="h4 mb-4">Where would you like to go?</label>
                    <div class="gt-search-modal__row">
                        <input type="search" id="gt-modal-q" name="q" class="form-control"
                            placeholder="Try “Kyoto”, “safari” or “sailing”" autocomplete="off">
                        <button type="submit" class="tf-btn primary hover-1"><span>Search</span></button>
                    </div>
                    <p class="subtitle text-color mt-3">
                        Search across {{ \App\Models\Tour::published()->count() }} tours in
                        {{ \App\Models\Destination::active()->count() }} destinations.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
