<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Managers run the catalogue and the desk work. Staff accounts, the homepage
 * and every delete belong to admins.
 */
class StaffPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->manager()->create();
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    // ------------------------------------------------------- what both share

    public function test_managers_reach_the_panel_and_the_catalogue(): void
    {
        Tour::factory()->create();
        Booking::factory()->create();
        Review::factory()->create();

        $manager = $this->manager();

        foreach (['', '/tours', '/destinations', '/categories', '/bookings', '/reviews'] as $screen) {
            $this->actingAs($manager)->get("/admin{$screen}")->assertOk();
        }
    }

    public function test_managers_can_create_and_edit_tours(): void
    {
        $tour = Tour::factory()->create(['title' => 'Old title']);

        $this->actingAs($this->manager())
            ->put(route('admin.tours.update', $tour), [
                ...$tour->only([
                    'destination_id', 'category_id', 'summary', 'description', 'image', 'price',
                    'duration_days', 'duration_nights', 'group_size', 'min_age', 'difficulty',
                ]),
                'title' => 'Retitled by a manager',
                'status' => $tour->status->value,
            ])
            ->assertRedirect();

        $this->assertSame('Retitled by a manager', $tour->fresh()->title);
    }

    public function test_managers_can_upload_images(): void
    {
        $this->actingAs($this->manager())->getJson('/admin/media')->assertOk();
    }

    // ------------------------------------------------------------ users only

    public function test_managers_cannot_touch_staff_accounts(): void
    {
        $victim = User::factory()->create();

        $manager = $this->manager();

        $this->actingAs($manager)->get('/admin/users')->assertForbidden();
        $this->actingAs($manager)->get(route('admin.users.edit', $victim))->assertForbidden();
        $this->actingAs($manager)->delete(route('admin.users.destroy', $victim))->assertForbidden();
    }

    public function test_a_manager_cannot_promote_themselves(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)
            ->put(route('admin.users.update', $manager), [
                'name' => $manager->name,
                'email' => $manager->email,
                'role' => 'admin',
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->assertTrue($manager->fresh()->role->value === 'manager');
    }

    public function test_admins_can_manage_staff_accounts(): void
    {
        $this->actingAs($this->admin())->get('/admin/users')->assertOk();
    }

    // --------------------------------------------------------- homepage only

    public function test_managers_cannot_edit_the_homepage(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->get('/admin/pages/home')->assertForbidden();
        $this->actingAs($manager)->get('/admin/pages/home/hero')->assertForbidden();
        $this->actingAs($manager)->put('/admin/pages/home/trending', ['heading' => 'Nope'])->assertForbidden();
        $this->actingAs($manager)->patch('/admin/pages/home/trending/toggle')->assertForbidden();

        $this->assertDatabaseMissing('page_sections', ['heading' => 'Nope']);
    }

    public function test_the_sidebar_hides_admin_only_areas_from_managers(): void
    {
        $this->actingAs($this->manager())->get('/admin')
            ->assertOk()
            ->assertDontSee('admin/pages', false)
            ->assertDontSee('admin/users', false);

        $this->actingAs($this->admin())->get('/admin')
            ->assertSee('admin/pages', false)
            ->assertSee('admin/users', false);
    }

    // ----------------------------------------------------------- deletes only

    public function test_managers_cannot_delete_catalogue_records(): void
    {
        $tour = Tour::factory()->create();
        $destination = Destination::factory()->create();
        $category = Category::factory()->create();
        $review = Review::factory()->create();

        $manager = $this->manager();

        $this->actingAs($manager)->delete(route('admin.tours.destroy', $tour))->assertForbidden();
        $this->actingAs($manager)->delete(route('admin.destinations.destroy', $destination))->assertForbidden();
        $this->actingAs($manager)->delete(route('admin.categories.destroy', $category))->assertForbidden();
        $this->actingAs($manager)->delete(route('admin.reviews.destroy', $review))->assertForbidden();

        $this->assertDatabaseHas('tours', ['id' => $tour->id]);
        $this->assertDatabaseHas('destinations', ['id' => $destination->id]);
    }

    public function test_admins_can_delete(): void
    {
        $tour = Tour::factory()->create();

        $this->actingAs($this->admin())->delete(route('admin.tours.destroy', $tour))->assertRedirect();

        // Tours are soft-deleted, so the row stays; what matters is that it is
        // gone from the catalogue.
        $this->assertSoftDeleted($tour);
        $this->assertNull(Tour::find($tour->id));
    }

    public function test_the_delete_button_is_hidden_from_managers(): void
    {
        Tour::factory()->create();

        $this->actingAs($this->manager())->get('/admin/tours')
            ->assertOk()
            ->assertDontSee('title="Delete"', false);

        $this->actingAs($this->admin())->get('/admin/tours')
            ->assertSee('title="Delete"', false);
    }

    // ------------------------------------------------------------- customers

    public function test_customers_are_still_shut_out_entirely(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/tours')->assertForbidden();
        $this->actingAs($customer)->getJson('/admin/media')->assertForbidden();
    }

    public function test_role_helpers_say_what_they_mean(): void
    {
        $this->assertTrue($this->admin()->isAdmin());
        $this->assertTrue($this->admin()->isStaff());

        $this->assertFalse($this->manager()->isAdmin());
        $this->assertTrue($this->manager()->isStaff());

        $customer = User::factory()->create();
        $this->assertFalse($customer->isAdmin());
        $this->assertFalse($customer->isStaff());
    }

    public function test_home_content_still_renders_for_everyone(): void
    {
        PageSection::flushContent();
        Tour::factory()->create();

        $this->get('/')->assertOk()->assertSee('Why travel with GlobeTrek');
    }

    // ----------------------------------------------------------- signing in

    public function test_a_customer_is_not_redirected_into_the_staff_area(): void
    {
        $customer = User::factory()->create(['password' => bcrypt('password')]);

        // Opening /admin while logged out stores it as the intended URL.
        $this->get('/admin')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('account.bookings'));
    }

    public function test_staff_still_land_on_the_page_they_asked_for(): void
    {
        $manager = User::factory()->manager()->create(['password' => bcrypt('password')]);

        $this->get('/admin/tours')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $manager->email, 'password' => 'password'])
            ->assertRedirect('/admin/tours');
    }

    public function test_a_customer_keeps_a_public_intended_url(): void
    {
        $customer = User::factory()->create(['password' => bcrypt('password')]);

        $this->get('/account/bookings')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('account.bookings'));
    }
}
