<x-layouts.app :title="'Book '.$tour->title">

    <x-ui.page-title title="Checkout"
        :breadcrumbs="['Tours' => route('tours.index'), $tour->title => route('tours.show', $tour), 'Checkout' => null]" />

    <div class="flat-section">
        <div class="container">
            <form action="{{ route('bookings.store', $tour) }}" method="POST" class="row">
                @csrf

                {{-- Carry the choices made in the tour-page widget. --}}
                <input type="hidden" name="travel_date" value="{{ old('travel_date', $prefill['travel_date'] ?? now()->addDays(21)->toDateString()) }}">
                <input type="hidden" name="travel_time" value="{{ old('travel_time', $prefill['travel_time']) }}">
                <input type="hidden" name="adults" value="{{ old('adults', $prefill['adults']) }}">
                <input type="hidden" name="children" value="{{ old('children', $prefill['children']) }}">
                @foreach ($prefill['extras'] as $extra)
                    <input type="hidden" name="extras[]" value="{{ $extra }}">
                @endforeach

                <div class="col-xl-7">
                    <div class="gt-card">
                        <div class="h3 mb-6">Lead traveller</div>

                        @if (session('error'))
                            <p class="gt-form-error mb-4">{{ session('error') }}</p>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="gt-field mb-4">
                                    <label for="c-name">Full name *</label>
                                    <input type="text" id="c-name" name="customer_name" required
                                        value="{{ old('customer_name', auth()->user()?->name) }}">
                                    @error('customer_name') <p class="gt-form-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="gt-field mb-4">
                                    <label for="c-email">Email *</label>
                                    <input type="email" id="c-email" name="customer_email" required
                                        value="{{ old('customer_email', auth()->user()?->email) }}">
                                    @error('customer_email') <p class="gt-form-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="gt-field mb-4">
                                    <label for="c-phone">Phone</label>
                                    <input type="tel" id="c-phone" name="customer_phone"
                                        value="{{ old('customer_phone', auth()->user()?->phone) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="gt-field mb-4">
                                    <label for="c-country">Country</label>
                                    <input type="text" id="c-country" name="customer_country"
                                        value="{{ old('customer_country', auth()->user()?->country) }}">
                                </div>
                            </div>
                        </div>

                        <div class="gt-field">
                            <label for="c-notes">Anything we should know?</label>
                            <textarea id="c-notes" name="notes" rows="4"
                                placeholder="Dietary requirements, accessibility needs, celebrating something…">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="gt-card mt-6">
                        <div class="h4 mb-4">Payment</div>
                        <p class="text-color">
                            This is a demonstration platform, so no payment is taken. Submitting the form creates a
                            pending booking that a GlobeTrek consultant would normally confirm within one working day.
                        </p>
                    </div>

                    <button type="submit" class="tf-btn primary hover-1 w-full mt-6">
                        <span>Confirm booking request</span>
                    </button>
                </div>

                <div class="col-xl-5 mt-6 mt-xl-0">
                    <div class="gt-card">
                        <div class="h4 mb-4">Your trip</div>

                        <div class="d-flex gap-3 mb-4">
                            <img src="{{ asset($tour->image) }}" alt="{{ $tour->title }}"
                                style="width:110px;height:82px;object-fit:cover;border-radius:12px">
                            <div>
                                <div class="h5">{{ $tour->title }}</div>
                                <p class="gt-hint mt-1">{{ $tour->destination->full_name }}</p>
                                <x-ui.rating :value="$tour->rating_avg" :count="$tour->reviews_count" class="rating mt-1" />
                            </div>
                        </div>

                        <ul class="gt-stack" style="border-top:1px solid var(--gt-line);padding-top:16px">
                            <li class="d-flex justify-content-between">
                                <span class="subtitle text-color">Date</span>
                                <strong>{{ \Illuminate\Support\Carbon::parse($prefill['travel_date'] ?? now()->addDays(21))->format('j M Y') }}</strong>
                            </li>
                            @if ($prefill['travel_time'])
                                <li class="d-flex justify-content-between">
                                    <span class="subtitle text-color">Departure</span>
                                    <strong>{{ $prefill['travel_time'] }}</strong>
                                </li>
                            @endif
                            <li class="d-flex justify-content-between">
                                <span class="subtitle text-color">Duration</span>
                                <strong>{{ $tour->duration_label }}</strong>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span class="subtitle text-color">Adults × {{ $prefill['adults'] }}</span>
                                <strong>${{ number_format($quote['unit_price'] * $prefill['adults'], 2) }}</strong>
                            </li>
                            @if ($prefill['children'] > 0)
                                <li class="d-flex justify-content-between">
                                    <span class="subtitle text-color">Children × {{ $prefill['children'] }}</span>
                                    <strong>${{ number_format($quote['subtotal'] - $quote['unit_price'] * $prefill['adults'], 2) }}</strong>
                                </li>
                            @endif
                            @foreach ($quote['extras'] as $extra)
                                <li class="d-flex justify-content-between">
                                    <span class="subtitle text-color">{{ $extra['name'] }}</span>
                                    <strong>${{ number_format($extra['price'], 2) }}</strong>
                                </li>
                            @endforeach
                        </ul>

                        <div class="gt-booking__total mt-4" style="border-radius:12px">
                            <span>Total</span>
                            <span>${{ number_format($quote['total'], 2) }}</span>
                        </div>

                        <p class="gt-hint mt-4">
                            Free cancellation up to 30 days before departure, 50% refund up to 14 days.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
