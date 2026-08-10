<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\PageSection;
use App\Support\PageSections;
use Illuminate\Database\Seeder;

/**
 * Writes the copy that used to live in the Blade files into the database, so
 * the admin opens the Homepage screen and sees exactly what the site shows.
 */
class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PageSections::keys() as $key) {
            $defaults = PageSections::defaults($key);

            PageSection::updateOrCreate(
                ['key' => $key],
                [
                    'heading' => $defaults['heading'],
                    'subtitle' => $defaults['subtitle'],
                    'data' => $key === 'hero' ? ['slides' => $this->heroSlides()] : $defaults['data'],
                    'is_visible' => true,
                ],
            );
        }
    }

    /**
     * Seed the slider with the three destinations it was already showing, so
     * the first edit is a tweak rather than a blank page.
     */
    private function heroSlides(): array
    {
        return Destination::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(3)
            ->get()
            ->map(fn (Destination $destination) => [
                'image' => $destination->hero_image ?: $destination->image,
                'destination_id' => (string) $destination->id,
                'eyebrow' => $destination->continent,
                'title' => $destination->name,
                'summary' => $destination->summary,
            ])
            ->all();
    }
}
