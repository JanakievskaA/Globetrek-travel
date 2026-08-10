<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Tour;
use Database\Seeders\Data\TourCatalogue;
use Database\Seeders\Data\TourData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TourSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = Destination::pluck('id', 'slug');
        $categories = Category::pluck('id', 'slug');
        $defaults = TourCatalogue::defaults();

        foreach (TourData::tours() as $data) {
            $categorySlug = $data['category'];
            $fallback = $defaults[$categorySlug] ?? [];

            $tour = Tour::updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'destination_id' => $destinations[$data['destination']],
                    'category_id' => $categories[$categorySlug],
                    'summary' => $data['summary'],
                    'description' => $data['description'],
                    'image' => self::photo($data['image'], card: true),
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'] ?? null,
                    'duration_days' => $data['days'] ?? 0,
                    'duration_nights' => $data['nights'] ?? 0,
                    'duration_hours' => $data['hours'] ?? null,
                    'group_size' => $data['group_size'],
                    'min_age' => $data['min_age'],
                    'difficulty' => $data['difficulty'] ?? $fallback['difficulty'] ?? 'easy',
                    'departure_point' => $data['departure_point'] ?? null,
                    'contact_phone' => '+1 (229) 555-0109',
                    'languages' => $data['languages'] ?? ['English', 'Spanish', 'French'],
                    'includes' => $data['includes'] ?? $fallback['includes'] ?? [],
                    'excludes' => $data['excludes'] ?? $fallback['excludes'] ?? [],
                    'highlights' => $data['highlights'],
                    'amenities' => $data['amenities'] ?? $fallback['amenities'] ?? [],
                    'faqs' => TourCatalogue::commonFaqs(),
                    'extras' => TourCatalogue::EXTRAS,
                    'latitude' => null,
                    'longitude' => null,
                    'is_featured' => $data['featured'] ?? false,
                    'status' => 'published',
                ]
            );

            // Inherit the map pin from the destination so the detail page always
            // has something sensible to show.
            $destination = Destination::find($tour->destination_id);
            $tour->forceFill([
                'latitude' => $destination->latitude,
                'longitude' => $destination->longitude,
                'views' => random_int(240, 4800),
            ])->saveQuietly();

            $this->syncGallery($tour, $data);
            $this->syncItinerary($tour, $data['itinerary']);
        }
    }

    private function syncGallery(Tour $tour, array $data): void
    {
        $tour->images()->delete();

        $slugs = collect([$data['image'], ...$data['gallery']])->unique()->values();

        foreach ($slugs as $index => $slug) {
            $tour->images()->create([
                'path' => self::photo($slug),
                'alt' => $tour->title.' — photo '.($index + 1),
                'sort_order' => $index,
            ]);
        }
    }

    private function syncItinerary(Tour $tour, array $itinerary): void
    {
        $tour->itineraries()->delete();

        foreach ($itinerary as $index => $step) {
            $tour->itineraries()->create([
                'day' => $index + 1,
                'title' => $step['title'],
                'description' => $step['description'],
                'duration' => $step['duration'] ?? null,
                'meals' => $step['meals'] ?? null,
                'accommodation' => $step['accommodation'] ?? null,
            ]);
        }
    }

    private static function photo(string $slug, bool $card = false): string
    {
        return 'assets/images/travel/'.$slug.($card ? '-card' : '').'.jpg';
    }
}
