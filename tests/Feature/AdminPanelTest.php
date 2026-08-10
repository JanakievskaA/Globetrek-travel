<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    // ------------------------------------------------------------ access

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_customers_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_managers_may_enter(): void
    {
        $this->actingAs(User::factory()->manager()->create())->get('/admin')->assertOk();
    }

    public function test_every_index_screen_renders(): void
    {
        Tour::factory()->create();
        Booking::factory()->create();
        Review::factory()->create();

        $admin = $this->admin();

        foreach (['tours', 'destinations', 'categories', 'bookings', 'reviews', 'users'] as $resource) {
            $this->actingAs($admin)->get("/admin/{$resource}")->assertOk();
        }
    }

    // -------------------------------------------------------------- tours

    public function test_admin_can_create_a_tour(): void
    {
        $destination = Destination::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($this->admin())->post('/admin/tours', [
            'title' => 'Northern lights weekend',
            'destination_id' => $destination->id,
            'category_id' => $category->id,
            'summary' => 'Three nights chasing the aurora.',
            'description' => 'A longer description of the departure.',
            'image' => 'assets/images/travel/beach-palm-card.jpg',
            'price' => 890,
            'duration_days' => 3,
            'duration_nights' => 2,
            'group_size' => 10,
            'min_age' => 12,
            'difficulty' => 'moderate',
            'status' => 'published',
            'highlights' => "Aurora hunting\nHusky sledding",
            'includes' => "Guide\nTransfers",
        ])->assertRedirect();

        $tour = Tour::firstOrFail();
        $this->assertSame('northern-lights-weekend', $tour->slug);
        // Newline-separated editors become JSON lists.
        $this->assertSame(['Aurora hunting', 'Husky sledding'], $tour->highlights);
        $this->assertSame(['Guide', 'Transfers'], $tour->includes);
    }

    public function test_sale_price_must_be_below_the_regular_price(): void
    {
        $tour = Tour::factory()->create(['price' => 100]);

        $this->actingAs($this->admin())
            ->put("/admin/tours/{$tour->slug}", [
                'title' => $tour->title,
                'destination_id' => $tour->destination_id,
                'category_id' => $tour->category_id,
                'summary' => 'x', 'description' => 'y',
                'image' => 'a.jpg', 'price' => 100, 'sale_price' => 500,
                'duration_days' => 1, 'duration_nights' => 0, 'group_size' => 5,
                'min_age' => 0, 'difficulty' => 'easy', 'status' => 'published',
            ])
            ->assertSessionHasErrors('sale_price');
    }

    public function test_admin_can_delete_a_tour(): void
    {
        $tour = Tour::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/tours/{$tour->slug}")
            ->assertRedirect(route('admin.tours.index'));

        $this->assertSoftDeleted($tour);
    }

    // ------------------------------------------------------- destinations

    public function test_destination_with_tours_cannot_be_deleted(): void
    {
        $destination = Destination::factory()->create();
        Tour::factory()->for($destination)->create();

        $this->actingAs($this->admin())
            ->delete("/admin/destinations/{$destination->slug}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('destinations', ['id' => $destination->id]);
    }

    public function test_empty_destination_can_be_deleted(): void
    {
        $destination = Destination::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/destinations/{$destination->slug}")
            ->assertRedirect(route('admin.destinations.index'));

        $this->assertDatabaseMissing('destinations', ['id' => $destination->id]);
    }

    // ----------------------------------------------------------- bookings

    public function test_booking_status_can_be_changed_inline(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.bookings.status', $booking), ['status' => 'confirmed'])
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame('paid', $booking->payment_status);
    }

    public function test_cancelling_a_booking_marks_it_refunded(): void
    {
        $booking = Booking::factory()->confirmed()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.bookings.status', $booking), ['status' => 'cancelled']);

        $this->assertSame('refunded', $booking->fresh()->payment_status);
    }

    // ------------------------------------------------------------ reviews

    public function test_approving_a_review_publishes_it_and_updates_the_rating(): void
    {
        $tour = Tour::factory()->create();
        $review = Review::factory()->for($tour)->pending()->rated(5)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.reviews.status', $review), ['status' => 'approved']);

        $this->assertSame(ReviewStatus::Approved, $review->fresh()->status);
        $this->assertSame(5.0, $tour->fresh()->rating_avg);
    }

    // -------------------------------------------------------------- users

    public function test_admin_can_create_and_update_a_user(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Staffer',
            'email' => 'staffer@example.com',
            'password' => 'secret-password',
            'role' => 'manager',
            'status' => 'active',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'staffer@example.com')->firstOrFail();
        $original = $user->password;

        // Blank password on update must leave the hash untouched.
        $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Renamed Staffer',
            'email' => 'staffer@example.com',
            'password' => '',
            'role' => 'manager',
            'status' => 'suspended',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('Renamed Staffer', $user->name);
        $this->assertSame('suspended', $user->status);
        $this->assertSame($original, $user->password);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($admin);
    }

    public function test_a_deleted_users_email_can_be_reused(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['email' => 'returning@example.com']);
        $user->delete();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Returning Customer',
            'email' => 'returning@example.com',
            'password' => 'secret-password',
            'role' => 'customer',
            'status' => 'active',
        ])->assertSessionHasNoErrors();
    }
}
