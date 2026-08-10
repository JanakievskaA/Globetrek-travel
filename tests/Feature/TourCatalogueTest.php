<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        Tour::factory()->featured()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('GlobeTrek', false);
    }

    public function test_tour_list_shows_published_tours_only(): void
    {
        $published = Tour::factory()->create(['title' => 'Visible Tour']);
        Tour::factory()->draft()->create(['title' => 'Hidden Draft Tour']);

        $this->get('/tours')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Hidden Draft Tour');
    }

    public function test_search_matches_title_and_destination(): void
    {
        $kyoto = Destination::factory()->create(['name' => 'Kyoto', 'country' => 'Japan']);
        $match = Tour::factory()->for($kyoto)->create(['title' => 'Temples at dawn']);
        $other = Tour::factory()->create(['title' => 'Desert safari drive']);

        $this->get('/tours?q=Kyoto')
            ->assertOk()
            ->assertSee($match->title)
            ->assertDontSee($other->title);
    }

    public function test_category_filter_narrows_results(): void
    {
        $beach = Category::factory()->create(['name' => 'Beach']);
        $hiking = Category::factory()->create(['name' => 'Hiking']);

        $beachTour = Tour::factory()->for($beach)->create(['title' => 'Island hopping day']);
        $hikeTour = Tour::factory()->for($hiking)->create(['title' => 'Ridge line ascent']);

        $this->get('/tours?category[]='.$beach->slug)
            ->assertOk()
            ->assertSee($beachTour->title)
            ->assertDontSee($hikeTour->title);
    }

    public function test_price_filter_uses_the_discounted_price(): void
    {
        // Lists at 900 but sells at 200 — a "up to 300" filter must include it.
        $discounted = Tour::factory()->onSale(200)->create([
            'title' => 'Discounted expedition', 'price' => 900,
        ]);
        $expensive = Tour::factory()->create(['title' => 'Full price expedition', 'price' => 900]);

        $this->get('/tours?min_price=0&max_price=300')
            ->assertOk()
            ->assertSee($discounted->title)
            ->assertDontSee($expensive->title);
    }

    public function test_a_tour_above_the_price_ceiling_still_appears(): void
    {
        // The slider's top stop is labelled "$5,000+", so it must not cap.
        $expedition = Tour::factory()->create(['title' => 'Ten thousand dollar expedition', 'price' => 10000]);

        $this->get('/tours')->assertOk()->assertSee($expedition->title);

        // ...but a max the visitor actually dragged down still applies.
        $this->get('/tours?max_price=4000')->assertOk()->assertDontSee($expedition->title);
    }

    public function test_duration_bucket_filter(): void
    {
        $dayTrip = Tour::factory()->dayTrip(4)->create(['title' => 'Half day walk']);
        $longTrip = Tour::factory()->create(['title' => 'Two week crossing', 'duration_days' => 14]);

        $this->get('/tours?duration[]=3-6-hours')
            ->assertOk()
            ->assertSee($dayTrip->title)
            ->assertDontSee($longTrip->title);

        $this->get('/tours?duration[]=7-plus-days')
            ->assertOk()
            ->assertSee($longTrip->title)
            ->assertDontSee($dayTrip->title);
    }

    public function test_rating_filter_uses_half_star_thresholds(): void
    {
        $great = Tour::factory()->create(['title' => 'Highly rated crossing']);
        Review::factory()->count(2)->rated(5)->for($great)->create();

        $average = Tour::factory()->create(['title' => 'Middling crossing']);
        Review::factory()->count(2)->rated(3)->for($average)->create();

        $this->get('/tours?rating[]=4.5')
            ->assertOk()
            ->assertSee($great->title)
            ->assertDontSee($average->title);
    }

    public function test_sorting_by_price_ascending(): void
    {
        Tour::factory()->create(['title' => 'Cheapest option', 'price' => 50]);
        Tour::factory()->create(['title' => 'Priciest option', 'price' => 4000]);

        $response = $this->get('/tours?sort=price-asc')->assertOk();

        $body = $response->getContent();
        $this->assertLessThan(
            strpos($body, 'Priciest option'),
            strpos($body, 'Cheapest option'),
            'Cheapest tour should be rendered before the most expensive one.'
        );
    }

    public function test_amenity_filter_matches_json_column(): void
    {
        $withPhotos = Tour::factory()->create([
            'title' => 'Photo led journey',
            'amenities' => ['Professional guide', 'Photo package'],
        ]);
        $without = Tour::factory()->create([
            'title' => 'Plain journey',
            'amenities' => ['Professional guide'],
        ]);

        $this->get('/tours?amenity[]=Photo+package')
            ->assertOk()
            ->assertSee($withPhotos->title)
            ->assertDontSee($without->title);
    }

    public function test_tour_detail_renders_itinerary_and_reviews(): void
    {
        $tour = Tour::factory()->create();
        $tour->itineraries()->create([
            'day' => 1, 'title' => 'Arrival and orientation', 'description' => 'Meet the group.',
        ]);
        Review::factory()->for($tour)->create(['body' => 'A genuinely excellent trip from start to finish.']);

        $this->get('/tours/'.$tour->slug)
            ->assertOk()
            ->assertSee($tour->title)
            ->assertSee('Arrival and orientation')
            ->assertSee('A genuinely excellent trip from start to finish.');
    }

    public function test_draft_tour_detail_is_not_reachable(): void
    {
        $tour = Tour::factory()->draft()->create();

        $this->get('/tours/'.$tour->slug)->assertNotFound();
    }

    public function test_destination_pages_render(): void
    {
        $destination = Destination::factory()->create(['name' => 'Santorini']);
        $tour = Tour::factory()->for($destination)->create();

        $this->get('/destinations')->assertOk()->assertSee('Santorini');
        $this->get('/destinations/'.$destination->slug)
            ->assertOk()
            ->assertSee($tour->title);
    }

    public function test_hidden_destination_is_not_reachable(): void
    {
        $destination = Destination::factory()->hidden()->create();

        $this->get('/destinations/'.$destination->slug)->assertNotFound();
    }
}
