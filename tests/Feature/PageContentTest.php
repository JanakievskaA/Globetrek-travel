<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The About and Contact pages are editable from the same machinery as the
 * homepage — one tab per page under Admin → Pages.
 */
class PageContentTest extends TestCase
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

    public function test_about_renders_with_no_saved_content(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('Trips built by people who go on them')
            ->assertSee('How we work')
            ->assertSee('Local guides, fairly paid');
    }

    public function test_contact_renders_with_no_saved_content(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Talk to a specialist')
            ->assertSee('(229) 555-0109')
            ->assertSee('hello@globetrek.travel');
    }

    public function test_live_counts_replace_the_tokens_in_the_about_copy(): void
    {
        Tour::factory()->count(3)->create(['status' => 'published']);

        $response = $this->get('/about');

        // ":tours tours across :destinations destinations" — never shown raw.
        $response->assertOk()
            ->assertDontSee(':tours')
            ->assertDontSee(':destinations')
            ->assertDontSee(':reviews')
            ->assertSee('3 tours across');
    }

    // -------------------------------------------------------------- editing

    public function test_admin_can_edit_the_about_story_and_see_it_on_the_site(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/pages/about/about_intro', [
                'heading' => 'Why we started',
                'is_visible' => '1',
                'data' => [
                    'image' => 'assets/images/travel/alps-hiker.jpg',
                    'body' => "First paragraph here.\n\nSecond paragraph here.",
                ],
            ])
            ->assertRedirect(route('admin.pages.index', 'about'));

        PageSection::flushContent();

        $this->get('/about')
            ->assertOk()
            ->assertSee('Why we started')
            ->assertSee('First paragraph here.')
            ->assertSee('Second paragraph here.')
            ->assertDontSee('Trips built by people who go on them');
    }

    public function test_admin_can_edit_the_contact_details(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/pages/contact/contact_channels', [
                'is_visible' => '1',
                'data' => [
                    'items' => [
                        ['icon' => 'assets/images/icons/mail.svg', 'label' => 'Email', 'value' => 'hi@example.test', 'href' => 'mailto:hi@example.test'],
                        // Blank row: should be dropped rather than rendered empty.
                        ['icon' => '', 'label' => '', 'value' => '', 'href' => ''],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.pages.index', 'contact'));

        $this->assertCount(1, PageSection::for('contact_channels')->rows('items'));

        PageSection::flushContent();

        // Note: the footer carries its own hardcoded phone and email, so the old
        // address still appears there — "Hours" only ever came from this section.
        $this->get('/contact')
            ->assertOk()
            ->assertSee('hi@example.test')
            ->assertDontSee('Mon–Fri 08:00–19:00 GMT');
    }

    public function test_the_contact_form_wording_is_editable(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/pages/contact/contact_form', [
                'is_visible' => '1',
                'data' => [
                    'button_label' => 'Send enquiry',
                    'subject_placeholder' => 'Your subject',
                    'message_placeholder' => 'Your message',
                    'success_message' => 'Got it, thanks.',
                ],
            ])->assertRedirect();

        PageSection::flushContent();

        $this->get('/contact')->assertOk()->assertSee('Send enquiry');

        $this->post('/contact', [
            'name' => 'Tester',
            'email' => 'tester@example.test',
            'message' => 'A message long enough to pass validation.',
        ])->assertSessionHas('success', 'Got it, thanks.');
    }

    public function test_hidden_sections_disappear_from_their_page(): void
    {
        Destination::factory()->create();
        Review::factory()->create();

        $this->actingAs($this->admin())
            ->patch('/admin/pages/about/about_principles/toggle')
            ->assertRedirect();

        PageSection::flushContent();

        $this->get('/about')->assertOk()->assertDontSee('Local guides, fairly paid');
    }

    // -------------------------------------------------------------- plumbing

    public function test_every_tab_loads(): void
    {
        $admin = $this->admin();

        foreach (['home', 'about', 'contact'] as $page) {
            $this->actingAs($admin)->get("/admin/pages/{$page}")->assertOk();
        }

        // The sidebar links here without naming a tab.
        $this->actingAs($admin)->get('/admin/pages')->assertOk()->assertSee('Homepage');
    }

    public function test_an_unknown_page_or_mismatched_section_is_a_404(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/pages/nope')->assertNotFound();
        // about_intro is real, but it does not belong to the contact page.
        $this->actingAs($admin)->get('/admin/pages/contact/about_intro')->assertNotFound();
        $this->actingAs($admin)->put('/admin/pages/contact/about_intro', ['heading' => 'X'])->assertNotFound();
    }

    public function test_managers_cannot_edit_pages(): void
    {
        $manager = User::factory()->create(['role' => \App\Enums\UserRole::Manager]);

        $this->actingAs($manager)->get('/admin/pages/about')->assertForbidden();
        $this->actingAs($manager)->put('/admin/pages/about/about_intro', ['heading' => 'Nope'])->assertForbidden();

        $this->assertDatabaseMissing('page_sections', ['heading' => 'Nope']);
    }
}
