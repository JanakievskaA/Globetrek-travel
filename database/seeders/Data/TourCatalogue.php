<?php

namespace Database\Seeders\Data;

/**
 * The hand-written tour catalogue.
 *
 * Keys that every tour must provide: title, destination, category, price,
 * image, gallery, summary, description, highlights, itinerary.
 * Everything else falls back to the category defaults in {@see self::defaults()}.
 *
 * `destination` matches a Destination slug and `category` a Category slug.
 * `image`/`gallery` entries are photo slugs from tools/fetch_photos.py.
 */
class TourCatalogue
{
    /** Reusable amenity vocabulary — also drives the sidebar amenity filter. */
    public const AMENITIES = [
        'Professional guide',
        'Hotel pickup',
        'Airport transfer',
        'Breakfast included',
        'Wi-Fi on board',
        'Air conditioning',
        'Travel insurance',
        'Photo package',
    ];

    /** Optional paid add-ons offered in the booking widget. */
    public const EXTRAS = [
        ['name' => 'Private guide upgrade', 'price' => 85],
        ['name' => 'Airport transfer (return)', 'price' => 40],
        ['name' => 'Travel insurance', 'price' => 28],
        ['name' => 'Professional photo package', 'price' => 55],
    ];

    /** Per-category fallbacks so each tour only states what makes it different. */
    public static function defaults(): array
    {
        $baseIncludes = ['Licensed local guide', 'All entrance fees listed in the itinerary', 'Bottled water'];
        $baseExcludes = ['International flights', 'Travel insurance', 'Personal expenses', 'Gratuities'];

        return [
            'adventure' => [
                'includes' => [...$baseIncludes, 'Safety equipment and briefing', 'Transport to trailheads'],
                'excludes' => [...$baseExcludes, 'Technical clothing hire'],
                'amenities' => ['Professional guide', 'Hotel pickup', 'Travel insurance'],
                'difficulty' => 'challenging',
            ],
            'beach-islands' => [
                'includes' => [...$baseIncludes, 'Snorkelling gear', 'Lunch on board', 'Boat transfers'],
                'excludes' => [...$baseExcludes, 'Alcoholic drinks', 'Dive certification courses'],
                'amenities' => ['Professional guide', 'Hotel pickup', 'Wi-Fi on board', 'Photo package'],
                'difficulty' => 'easy',
            ],
            'cultural' => [
                'includes' => [...$baseIncludes, 'Temple and museum admissions', 'Traditional lunch'],
                'excludes' => [...$baseExcludes, 'Optional evening performances'],
                'amenities' => ['Professional guide', 'Hotel pickup', 'Air conditioning'],
                'difficulty' => 'easy',
            ],
            'discovery' => [
                'includes' => [...$baseIncludes, 'Accommodation as listed', 'Daily breakfast', 'Internal transfers'],
                'excludes' => [...$baseExcludes, 'Visa fees', 'Meals not listed'],
                'amenities' => ['Professional guide', 'Airport transfer', 'Breakfast included', 'Air conditioning'],
                'difficulty' => 'moderate',
            ],
            'wildlife-safari' => [
                'includes' => [...$baseIncludes, 'Game drives in open 4x4', 'Park conservation fees', 'Full board'],
                'excludes' => [...$baseExcludes, 'Premium spirits', 'Scenic flights'],
                'amenities' => ['Professional guide', 'Airport transfer', 'Breakfast included', 'Photo package'],
                'difficulty' => 'easy',
            ],
            'food-culinary' => [
                'includes' => [...$baseIncludes, 'All tastings and dishes', 'Market shopping', 'Recipe booklet'],
                'excludes' => [...$baseExcludes, 'Additional drinks'],
                'amenities' => ['Professional guide', 'Hotel pickup'],
                'difficulty' => 'easy',
            ],
            'hiking-trekking' => [
                'includes' => [...$baseIncludes, 'Permits and park fees', 'Porter support', 'Teahouse accommodation'],
                'excludes' => [...$baseExcludes, 'Sleeping bag hire', 'Emergency evacuation costs'],
                'amenities' => ['Professional guide', 'Travel insurance'],
                'difficulty' => 'challenging',
            ],
            'sailing-cruise' => [
                'includes' => [...$baseIncludes, 'Cabin accommodation', 'All meals on board', 'Kayaks and paddleboards'],
                'excludes' => [...$baseExcludes, 'Bar tab', 'Marina fees for private charters'],
                'amenities' => ['Professional guide', 'Wi-Fi on board', 'Breakfast included', 'Air conditioning'],
                'difficulty' => 'easy',
            ],
            'city-breaks' => [
                'includes' => [...$baseIncludes, 'Skip-the-line tickets', 'Public transport pass'],
                'excludes' => [...$baseExcludes, 'Hotel accommodation', 'Meals not listed'],
                'amenities' => ['Professional guide', 'Air conditioning'],
                'difficulty' => 'easy',
            ],
            'camping' => [
                'includes' => [...$baseIncludes, 'Tents and sleeping mats', 'All camp meals', 'Campfire permits'],
                'excludes' => [...$baseExcludes, 'Sleeping bags', 'Alcoholic drinks'],
                'amenities' => ['Professional guide', 'Hotel pickup'],
                'difficulty' => 'moderate',
            ],
        ];
    }

    /** Frequently asked questions appended to every tour. */
    public static function commonFaqs(): array
    {
        return [
            [
                'question' => 'How far in advance should I book?',
                'answer' => 'Small-group departures typically fill four to eight weeks out, and high-season dates go earlier. Booking sooner also locks in the current price — we never charge more if rates rise after you have confirmed.',
            ],
            [
                'question' => 'What is your cancellation policy?',
                'answer' => 'Cancel more than 30 days before departure for a full refund, or between 30 and 14 days for a 50% refund. Inside 14 days the booking is non-refundable, which is what travel insurance is for.',
            ],
            [
                'question' => 'Do I need travel insurance?',
                'answer' => 'It is not compulsory but we strongly recommend it, and for trekking and adventure departures we require proof of cover including emergency evacuation before you join the group.',
            ],
            [
                'question' => 'Can you accommodate dietary requirements?',
                'answer' => 'Yes — vegetarian, vegan, halal, coeliac and most allergies are straightforward if you tell us at the time of booking. Let us know in the notes field and we will confirm with the local kitchens.',
            ],
        ];
    }
}
