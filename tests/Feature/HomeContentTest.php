<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\PageSection;
use App\Models\Media;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The homepage is editable from the admin panel: copy, photos, repeated cards
 * and per-section visibility.
 */
class HomeContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PageSection::flushContent();
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    // ------------------------------------------------------------- defaults

    public function test_homepage_renders_with_no_saved_content(): void
    {
        Tour::factory()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Why travel with GlobeTrek')
            ->assertSee('Small groups, always');
    }

    public function test_sections_fall_back_to_their_registry_defaults(): void
    {
        $section = PageSection::for('trending');

        $this->assertSame('Trending right now', $section->heading);
        $this->assertSame(12840, PageSection::for('stats_bar')->value('travellers'));
    }

    // -------------------------------------------------------------- editing

    public function test_admin_can_edit_a_section_heading_and_see_it_on_the_site(): void
    {
        Tour::factory()->create();

        $this->actingAs($this->admin())
            ->put('/admin/pages/home/trending', [
                'heading' => 'Everyone is booking these',
                'subtitle' => 'Updated by the marketing team.',
                'is_visible' => '1',
            ])
            ->assertRedirect(route('admin.pages.index', 'home'));

        PageSection::flushContent();
        // The admin's success toast names the section, so it would otherwise
        // still be on the page we are about to inspect.
        $this->flushSession();

        $this->get('/')
            ->assertSee('Everyone is booking these')
            ->assertDontSee('Trending right now');
    }

    public function test_admin_can_change_a_section_image(): void
    {
        Tour::factory()->create();

        $this->actingAs($this->admin())
            ->put('/admin/pages/home/video_banner', [
                'heading' => 'Journey to discover amazing nature',
                'subtitle' => null,
                'is_visible' => '1',
                'data' => [
                    'image' => 'assets/images/travel/kyoto-sakura-card.jpg',
                    'video_url' => 'https://www.youtube.com/embed/abc123',
                    'text' => 'Two minutes of why.',
                ],
            ])
            ->assertRedirect();

        PageSection::flushContent();

        $this->get('/')->assertSee('assets/images/travel/kyoto-sakura-card.jpg', false);
    }

    public function test_repeated_cards_can_be_added_and_blank_rows_are_dropped(): void
    {
        Tour::factory()->create();

        $this->actingAs($this->admin())
            ->put('/admin/pages/home/benefits', [
                'heading' => 'Why travel with us',
                'subtitle' => 'Four reasons.',
                'is_visible' => '1',
                'data' => [
                    'cards' => [
                        ['icon' => 'assets/images/icons/benefit-1.svg', 'title' => 'Small groups', 'text' => 'Twelve at most.'],
                        ['icon' => null, 'title' => null, 'text' => null],
                        ['icon' => null, 'title' => 'Carbon offset', 'text' => 'Every trip.'],
                    ],
                ],
            ])
            ->assertRedirect();

        PageSection::flushContent();

        $this->assertCount(2, PageSection::for('benefits')->rows('cards'));
        $this->get('/')->assertSee('Carbon offset')->assertSee('Small groups');
    }

    public function test_hidden_sections_disappear_from_the_homepage(): void
    {
        Tour::factory()->create();

        $this->actingAs($this->admin())
            ->patch('/admin/pages/home/testimonials/toggle')
            ->assertRedirect();

        PageSection::flushContent();

        $this->assertFalse(PageSection::for('testimonials')->is_visible);

        $this->flushSession();
        $this->get('/')->assertDontSee('What travellers say');
    }

    public function test_hero_slides_drive_the_slider(): void
    {
        $destination = Destination::factory()->create(['name' => 'Reykjavík', 'is_active' => true]);
        Tour::factory()->create();

        $this->actingAs($this->admin())
            ->put('/admin/pages/home/hero', [
                'is_visible' => '1',
                'data' => [
                    'slides' => [
                        [
                            'image' => 'assets/images/travel/iceland.jpg',
                            'destination_id' => $destination->id,
                            'eyebrow' => 'Europe',
                            'title' => 'Iceland in winter',
                            'summary' => 'Ice caves and empty roads.',
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        PageSection::flushContent();

        $this->get('/')
            ->assertSee('Iceland in winter')
            ->assertSee('assets/images/travel/iceland.jpg', false);
    }

    public function test_unknown_sections_are_not_editable(): void
    {
        $this->actingAs($this->admin())->get('/admin/pages/home/not-a-section')->assertNotFound();
    }

    public function test_editing_requires_an_admin(): void
    {
        $this->put('/admin/pages/home/trending', ['heading' => 'Nope'])->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->put('/admin/pages/home/trending', ['heading' => 'Nope'])
            ->assertForbidden();
    }

    // ---------------------------------------------------------------- media

    public function test_admin_can_upload_an_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post('/admin/media', ['file' => UploadedFile::fake()->image('Kyoto Street.jpg', 1200, 800)])
            ->assertCreated();

        $path = $response->json('item.path');

        $this->assertStringStartsWith('storage/uploads/', $path);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $path));
        $this->assertDatabaseHas('media', ['path' => $path, 'name' => 'Kyoto Street']);
    }

    public function test_uploads_must_be_images(): void
    {
        Storage::fake('public');

        // The picker posts multipart and reads `message` off the reply, so the
        // rejection has to come back as JSON even though this is a web route.
        $this->actingAs($this->admin())
            ->post('/admin/media', ['file' => UploadedFile::fake()->create('prices.pdf', 40, 'application/pdf')])
            ->assertStatus(422)
            ->assertJsonPath('message', 'That file is not an image.');
    }

    public function test_the_library_lists_uploads_and_theme_images(): void
    {
        Media::create(['path' => 'storage/uploads/2026/08/sunset.jpg', 'name' => 'Sunset']);

        $this->actingAs($this->admin())
            ->getJson('/admin/media')
            ->assertOk()
            ->assertJsonPath('items.0.name', 'Sunset');

        $theme = $this->actingAs($this->admin())->getJson('/admin/media?source=theme')->assertOk();

        $this->assertNotEmpty($theme->json('items'), 'The theme ships images the picker should offer.');
    }

    public function test_media_endpoints_are_closed_to_customers(): void
    {
        $this->actingAs(User::factory()->create())->getJson('/admin/media')->assertForbidden();
    }
}
