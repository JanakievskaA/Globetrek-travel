<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /** Tour types offered across the catalogue, in menu order. */
    public const CATEGORIES = [
        [
            'name' => 'Adventure',
            'icon' => 'climbing.svg',
            'image' => 'alps-hiker',
            'description' => 'Adrenaline-first itineraries for travellers who would rather climb it than photograph it from the bus.',
            'is_featured' => true,
        ],
        [
            'name' => 'Beach & Islands',
            'icon' => 'beach.svg',
            'image' => 'maldives-seaplane',
            'description' => 'Barefoot mornings, reef snorkelling and the kind of water that does not look real in photos.',
            'is_featured' => true,
        ],
        [
            'name' => 'Cultural',
            'icon' => 'national.svg',
            'image' => 'kyoto-sakura',
            'description' => 'Temples, old towns and the local historians who make them make sense.',
            'is_featured' => true,
        ],
        [
            'name' => 'Discovery',
            'icon' => 'discovery.svg',
            'image' => 'machu-picchu',
            'description' => 'Slow, wide-ranging routes that trade checklists for a real sense of a place.',
            'is_featured' => true,
        ],
        [
            'name' => 'Wildlife & Safari',
            'icon' => 'wild.svg',
            'image' => 'safari-sunset',
            'description' => 'Dawn game drives and expert trackers, with plenty of room in the vehicle for a long lens.',
            'is_featured' => true,
        ],
        [
            'name' => 'Food & Culinary',
            'icon' => 'cuisine.svg',
            'image' => 'tokyo-alley',
            'description' => 'Market walks, family kitchens and the back-street places that never make the guidebooks.',
            'is_featured' => true,
        ],
        [
            'name' => 'Hiking & Trekking',
            'icon' => 'long-dis.svg',
            'image' => 'himalaya-peaks',
            'description' => 'Multi-day trails graded honestly, with porters, permits and teahouses handled for you.',
            'is_featured' => true,
        ],
        [
            'name' => 'Sailing & Cruise',
            'icon' => 'sailboat.svg',
            'image' => 'thailand-longtail',
            'description' => 'Island hops and coastal passages on small boats that can reach the quiet anchorages.',
            'is_featured' => true,
        ],
        [
            'name' => 'City Breaks',
            'icon' => 'trip.svg',
            'image' => 'nyc-brooklyn-bridge',
            'description' => 'Two or three sharp days in a great city, planned by someone who actually lives there.',
            'is_featured' => false,
        ],
        [
            'name' => 'Camping',
            'icon' => 'camping.svg',
            'image' => 'camping-tent-stars',
            'description' => 'Wild pitches, proper campfires and skies with no light pollution in them.',
            'is_featured' => false,
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $index => $category) {
            Category::updateOrCreate(
                ['slug' => str($category['name'])->slug()->value()],
                [
                    ...$category,
                    'image' => "assets/images/travel/{$category['image']}-card.jpg",
                    'icon' => "assets/images/icons/{$category['icon']}",
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
