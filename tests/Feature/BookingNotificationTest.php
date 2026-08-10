<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use App\Notifications\BookingConfirmed;
use App\Notifications\NewBookingReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function booking(array $attributes = []): Booking
    {
        return Booking::factory()->create([
            'tour_id' => Tour::factory()->create()->id,
            'status' => BookingStatus::Pending,
            ...$attributes,
        ]);
    }

    public function test_a_new_booking_notifies_admins_and_managers(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->booking(['user_id' => $customer->id]);

        Notification::assertSentTo([$admin, $manager], NewBookingReceived::class);
        Notification::assertNotSentTo($customer, NewBookingReceived::class);
    }

    public function test_suspended_staff_are_left_out(): void
    {
        Notification::fake();

        $suspended = User::factory()->create(['role' => UserRole::Manager, 'status' => 'suspended']);

        $this->booking();

        Notification::assertNotSentTo($suspended, NewBookingReceived::class);
    }

    public function test_confirming_a_booking_notifies_the_customer(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $booking = $this->booking(['user_id' => $customer->id]);

        Notification::fake();
        $booking->update(['status' => BookingStatus::Confirmed]);

        Notification::assertSentTo($customer, BookingConfirmed::class);
    }

    public function test_editing_a_confirmed_booking_does_not_notify_again(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $booking = $this->booking(['user_id' => $customer->id, 'status' => BookingStatus::Confirmed]);

        Notification::fake();
        $booking->update(['customer_phone' => '+44 7700 900000']);

        Notification::assertNothingSent();
    }

    public function test_other_statuses_do_not_notify_the_customer(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $booking = $this->booking(['user_id' => $customer->id]);

        Notification::fake();
        $booking->update(['status' => BookingStatus::Cancelled]);

        Notification::assertNotSentTo($customer, BookingConfirmed::class);
    }

    public function test_a_guest_booking_confirms_without_error(): void
    {
        Notification::fake();

        // Staff still hear about it; the guest has no account to receive anything.
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $booking = $this->booking(['user_id' => null]);
        $booking->update(['status' => BookingStatus::Confirmed]);

        Notification::assertSentTo($admin, NewBookingReceived::class);
        Notification::assertSentTimes(BookingConfirmed::class, 0);
        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }

    public function test_the_admin_status_dropdown_notifies_the_customer(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $booking = $this->booking(['user_id' => $customer->id]);

        Notification::fake();

        $this->actingAs(User::factory()->create(['role' => UserRole::Manager]))
            ->patch(route('admin.bookings.status', $booking), ['status' => BookingStatus::Confirmed->value])
            ->assertRedirect();

        Notification::assertSentTo($customer, BookingConfirmed::class);
    }

    public function test_a_customer_can_read_and_clear_their_notifications(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $booking = $this->booking(['user_id' => $customer->id]);
        $booking->update(['status' => BookingStatus::Confirmed]);

        $entry = $customer->unreadNotifications()->firstOrFail();

        $this->actingAs($customer)
            ->post(route('notifications.read', $entry->id))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertNotNull($entry->fresh()->read_at);
    }

    public function test_mark_all_read_clears_the_badge(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $this->booking();
        $this->booking();

        $this->assertSame(2, $manager->unreadNotifications()->count());

        $this->actingAs($manager)
            ->from(route('admin.bookings.index'))
            ->post(route('notifications.readAll'))
            ->assertRedirect(route('admin.bookings.index'));

        $this->assertSame(0, $manager->unreadNotifications()->count());
        $this->assertSame(2, $manager->notifications()->count());
    }

    public function test_one_user_cannot_open_another_users_notification(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $booking = $this->booking(['user_id' => $customer->id]);
        $booking->update(['status' => BookingStatus::Confirmed]);

        $entry = $customer->unreadNotifications()->firstOrFail();
        $intruder = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($intruder)
            ->post(route('notifications.read', $entry->id))
            ->assertNotFound();

        $this->assertNull($entry->fresh()->read_at);
    }

    public function test_seeding_history_does_not_ring_the_bell(): void
    {
        Notification::fake();

        User::factory()->create(['role' => UserRole::Admin]);

        Booking::withoutNotifications(fn () => $this->booking());

        Notification::assertNothingSent();
    }
}
