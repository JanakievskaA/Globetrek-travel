<x-layouts.admin :title="'Edit '.$booking->reference">

    <x-admin.page-header :title="'Edit '.$booking->reference" :subtitle="$booking->tour->title">
        <a href="{{ route('admin.bookings.show', $booking) }}" class="adm-btn adm-btn--ghost">Cancel</a>
    </x-admin.page-header>

    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
        @csrf @method('PUT')

        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Customer</div></div>
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="customer_name" label="Name" :value="$booking->customer_name" span="6" required />
                    <x-admin.field name="customer_email" label="Email" type="email"
                        :value="$booking->customer_email" span="6" required />
                    <x-admin.field name="customer_phone" label="Phone" :value="$booking->customer_phone" span="6" />
                    <x-admin.field name="customer_country" label="Country" :value="$booking->customer_country" span="6" />
                </div>
            </div>
        </div>

        <div class="adm-panel">
            <div class="adm-panel__head"><div class="adm-panel__title">Trip &amp; charges</div></div>
            <div class="adm-panel__body">
                <div class="adm-form-grid">
                    <x-admin.field name="travel_date" label="Departure date" type="date"
                        :value="$booking->travel_date->toDateString()" span="4" required />
                    <x-admin.field name="travel_time" label="Departure time" :value="$booking->travel_time" span="4" />
                    <x-admin.field name="total" label="Total ($)" type="number" step="0.01" min="0"
                        :value="$booking->total" span="4" required />

                    <x-admin.field name="adults" type="number" min="1" :value="$booking->adults" span="4" required />
                    <x-admin.field name="children" type="number" min="0" :value="$booking->children" span="4" required />
                    <x-admin.field name="status" type="select" :value="$booking->status->value"
                        :options="$statuses" span="4" required />

                    <x-admin.field name="payment_status" label="Payment status" type="select"
                        :value="$booking->payment_status"
                        :options="['unpaid' => 'Unpaid', 'paid' => 'Paid', 'refunded' => 'Refunded']"
                        span="4" required />

                    <x-admin.field name="notes" type="textarea" rows="4" :value="$booking->notes" span="12" />
                </div>
            </div>
            <div class="adm-form-actions">
                <a href="{{ route('admin.bookings.show', $booking) }}" class="adm-btn adm-btn--ghost">Cancel</a>
                <button type="submit" class="adm-btn">Save changes</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
