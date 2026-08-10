<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    /**
     * Every destination in the catalogue. `image` is the card photo and
     * `hero_image` the wide shot used on the destination detail header —
     * both resolve to files fetched by tools/fetch_photos.py.
     */
    public const DESTINATIONS = [
        [
            'name' => 'Bali', 'country' => 'Indonesia', 'continent' => 'Asia',
            'image' => 'bali-ulun-danu', 'hero_image' => 'bali-kelingking',
            'summary' => 'Rice terraces, lake temples and a surf coast that keeps its rhythm all year.',
            'description' => 'Bali packs an improbable amount into one island. Mornings belong to the highlands around Ubud, where the terraces above Tegalalang still work as farmland rather than scenery. Afternoons drift to the south coast for surf and warungs, and evenings settle at water temples like Ulun Danu Beratan, where the mist comes down over the lake. It is busy in the right places and completely quiet twenty minutes off the main road.',
            'best_season' => 'April to October', 'currency' => 'IDR',
            'language' => 'Indonesian, Balinese', 'timezone' => 'UTC+8',
            'latitude' => -8.4095178, 'longitude' => 115.188916, 'is_featured' => true,
        ],
        [
            'name' => 'Santorini', 'country' => 'Greece', 'continent' => 'Europe',
            'image' => 'santorini-oia', 'hero_image' => 'santorini-village',
            'summary' => 'A drowned volcano ringed by white villages and the best sunsets in the Aegean.',
            'description' => 'Santorini is the rim of a caldera that blew apart three and a half thousand years ago, and the geology is still the main event. Oia and Imerovigli sit on the cliff edge looking west; the beaches on the far side are black and red volcanic sand. Go early or late in the season and the island returns to something close to itself, with the same light and a fraction of the crowd.',
            'best_season' => 'May, June, September', 'currency' => 'EUR',
            'language' => 'Greek', 'timezone' => 'UTC+3',
            'latitude' => 36.3931562, 'longitude' => 25.4615092, 'is_featured' => true,
        ],
        [
            'name' => 'Kyoto', 'country' => 'Japan', 'continent' => 'Asia',
            'image' => 'kyoto-sakura', 'hero_image' => 'kyoto-street',
            'summary' => 'A thousand years of capital city, best walked slowly and early in the morning.',
            'description' => 'Kyoto rewards patience more than any city in Japan. The famous sites — Fushimi Inari, Kinkaku-ji, Arashiyama — are worth the crowds if you reach them before eight. The rest of the day belongs to the machiya townhouses of Nishijin, the covered length of Nishiki market, and the temple gardens in the eastern hills where almost nobody goes. Cherry blossom in April and maple in November are spectacular and heavily booked.',
            'best_season' => 'March to May, October to November', 'currency' => 'JPY',
            'language' => 'Japanese', 'timezone' => 'UTC+9',
            'latitude' => 35.0116363, 'longitude' => 135.7680294, 'is_featured' => true,
        ],
        [
            'name' => 'Paris', 'country' => 'France', 'continent' => 'Europe',
            'image' => 'paris-eiffel', 'hero_image' => 'paris-bridge',
            'summary' => 'The obvious landmarks, plus the arrondissements where Parisians actually spend their weekends.',
            'description' => 'Paris is dense enough that a well-planned three days beats a vague week. The Louvre and the Musée d’Orsay deserve half-days rather than sprints; the Marais, Canal Saint-Martin and Belleville deserve whole afternoons with no plan at all. Our guides are residents, which mostly means you eat better and queue less.',
            'best_season' => 'April to June, September to October', 'currency' => 'EUR',
            'language' => 'French', 'timezone' => 'UTC+2',
            'latitude' => 48.8566969, 'longitude' => 2.3514616, 'is_featured' => true,
        ],
        [
            'name' => 'Rome', 'country' => 'Italy', 'continent' => 'Europe',
            'image' => 'rome-trevi', 'hero_image' => 'rome-vatican',
            'summary' => 'Twenty-eight centuries stacked on top of each other, with excellent coffee throughout.',
            'description' => 'Rome is a city where a bus stop can sit on a republican-era temple and nobody finds it remarkable. The Forum and Colosseum need a guide to come alive; the Vatican Museums need an early ticket and a plan. Between them, Trastevere and Testaccio are where the eating happens. Summer is genuinely hot — spring and autumn are much kinder.',
            'best_season' => 'April to June, September to October', 'currency' => 'EUR',
            'language' => 'Italian', 'timezone' => 'UTC+2',
            'latitude' => 41.8933203, 'longitude' => 12.4829321, 'is_featured' => true,
        ],
        [
            'name' => 'Venice', 'country' => 'Italy', 'continent' => 'Europe',
            'image' => 'venice-rialto', 'hero_image' => 'venice-canal',
            'summary' => 'A city with no cars, best understood from a boat and on foot after dark.',
            'description' => 'Venice empties out around six in the evening when the day-trippers leave, and that is when it becomes extraordinary. Stay overnight if you can. The Grand Canal and Piazza San Marco are unavoidable and worth it; Cannaregio, Dorsoduro and the outer islands of Burano and Torcello are where you get the quiet version. Acqua alta is a real consideration in November.',
            'best_season' => 'April to June, September to October', 'currency' => 'EUR',
            'language' => 'Italian', 'timezone' => 'UTC+2',
            'latitude' => 45.4408474, 'longitude' => 12.3155151, 'is_featured' => false,
        ],
        [
            'name' => 'Dubai', 'country' => 'United Arab Emirates', 'continent' => 'Asia',
            'image' => 'dubai-skyline', 'hero_image' => 'dubai-burj-al-arab',
            'summary' => 'Desert on one side, a skyline built in thirty years on the other.',
            'description' => 'Dubai is best taken as two trips in one. There is the engineered city — the Burj Khalifa, the marina, the malls that are effectively climate-controlled districts — and there is the desert an hour inland, where the dunes and the camel farms have not changed much. The old creek district of Deira, with its gold and spice souks and abra crossings, is the part most visitors skip and shouldn’t.',
            'best_season' => 'November to March', 'currency' => 'AED',
            'language' => 'Arabic, English', 'timezone' => 'UTC+4',
            'latitude' => 25.2653471, 'longitude' => 55.2924914, 'is_featured' => true,
        ],
        [
            'name' => 'Cusco & Machu Picchu', 'country' => 'Peru', 'continent' => 'South America',
            'image' => 'machu-picchu', 'hero_image' => 'machu-picchu',
            'summary' => 'The Inca capital at 3,400m, and the citadel everyone comes to see.',
            'description' => 'Cusco is the base for the Sacred Valley and for Machu Picchu, and it sits high enough that the first two days should be deliberately gentle. Acclimatise properly, drink the coca tea, and the rest of the trip is transformed. Machu Picchu itself is timed-entry and strictly capped — booking months ahead is not optional in high season. The Inca Trail requires a permit issued to licensed operators only.',
            'best_season' => 'May to September', 'currency' => 'PEN',
            'language' => 'Spanish, Quechua', 'timezone' => 'UTC-5',
            'latitude' => -13.163136, 'longitude' => -72.5471516, 'is_featured' => true,
        ],
        [
            'name' => 'Maldives', 'country' => 'Maldives', 'continent' => 'Asia',
            'image' => 'maldives-seaplane', 'hero_image' => 'maldives-seaplane',
            'summary' => 'Twenty-six atolls, a thousand-odd islands, and reefs in genuinely excellent condition.',
            'description' => 'The Maldives is a diving and snorkelling destination that happens to have very good hotels attached. Manta and whale shark aggregations in Baa and South Ari atoll are reliable and seasonal. Beyond the resort islands, the local islands opened to visitors in 2009 offer guesthouses at a fraction of the price — a different trip, but the same water.',
            'best_season' => 'November to April', 'currency' => 'MVR',
            'language' => 'Dhivehi, English', 'timezone' => 'UTC+5',
            'latitude' => 3.2027778, 'longitude' => 73.2207347, 'is_featured' => true,
        ],
        [
            'name' => 'Cape Town', 'country' => 'South Africa', 'continent' => 'Africa',
            'image' => 'cape-town-aerial', 'hero_image' => 'safari-sunset',
            'summary' => 'A mountain in the middle of the city, two oceans nearby, and safari a short flight away.',
            'description' => 'Few cities have Cape Town’s geography: Table Mountain rises straight out of the suburbs, and the Cape Peninsula runs south past Boulders Beach and Chapman’s Peak to the Cape of Good Hope. The Winelands at Stellenbosch and Franschhoek are an hour inland. Most visitors pair the city with a few nights in a private game reserve — the Eastern Cape reserves are malaria-free and reachable without a long transfer.',
            'best_season' => 'November to March', 'currency' => 'ZAR',
            'language' => 'English, Afrikaans, Xhosa', 'timezone' => 'UTC+2',
            'latitude' => -33.928992, 'longitude' => 18.417396, 'is_featured' => true,
        ],
        [
            'name' => 'Ha Long Bay', 'country' => 'Vietnam', 'continent' => 'Asia',
            'image' => 'halong-bay', 'hero_image' => 'halong-bay',
            'summary' => 'Nearly two thousand limestone karsts rising out of the Gulf of Tonkin.',
            'description' => 'Ha Long Bay works best as an overnight on a small boat rather than a day trip from Hanoi — the four-hour round transfer eats a day trip alive, and the bay is at its best at dawn before the fleet moves. Neighbouring Lan Ha Bay off Cat Ba island is quieter and increasingly where the better operators go. Kayaking through the floating villages is the highlight for most people.',
            'best_season' => 'October to April', 'currency' => 'VND',
            'language' => 'Vietnamese', 'timezone' => 'UTC+7',
            'latitude' => 20.9101, 'longitude' => 107.1839, 'is_featured' => false,
        ],
        [
            'name' => 'Banff & the Rockies', 'country' => 'Canada', 'continent' => 'North America',
            'image' => 'banff-moraine', 'hero_image' => 'alpine-forest',
            'summary' => 'Glacial lakes the colour of antifreeze, and grizzlies in the valleys below.',
            'description' => 'Banff and Jasper are joined by the Icefields Parkway, which is comfortably one of the great mountain drives. Moraine Lake and Lake Louise are the postcard stops and now require shuttle reservations in summer. The hiking is the reason to stay longer: Larch Valley in late September, when the needles turn, is worth planning a whole trip around.',
            'best_season' => 'June to September', 'currency' => 'CAD',
            'language' => 'English, French', 'timezone' => 'UTC-6',
            'latitude' => 51.1783502, 'longitude' => -115.5708317, 'is_featured' => false,
        ],
        [
            'name' => 'London', 'country' => 'United Kingdom', 'continent' => 'Europe',
            'image' => 'london-tower-bridge', 'hero_image' => 'london-big-ben',
            'summary' => 'World-class museums that cost nothing, and a different city in every postcode.',
            'description' => 'London’s great advantage is that the British Museum, the National Gallery, the Tates and the V&A are all free, which changes how you plan a visit — an hour in one is a reasonable thing to do. Beyond the centre, Borough and Broadway markets, the canal at Little Venice, and Hampstead Heath are what regulars actually recommend. The Tube is fast; walking between adjacent neighbourhoods is often faster.',
            'best_season' => 'May to September', 'currency' => 'GBP',
            'language' => 'English', 'timezone' => 'UTC+1',
            'latitude' => 51.5074456, 'longitude' => -0.1277653, 'is_featured' => false,
        ],
        [
            'name' => 'New York City', 'country' => 'United States', 'continent' => 'North America',
            'image' => 'nyc-brooklyn-bridge', 'hero_image' => 'nyc-brooklyn-bridge',
            'summary' => 'Five boroughs, and a strong argument that the best of them is not Manhattan.',
            'description' => 'New York rewards a neighbourhood-at-a-time approach. The set pieces — the Brooklyn Bridge at sunrise, the Met, the High Line, the view from the Rockefeller roof rather than the Empire State — are genuinely worth doing. The rest is Queens for the food, Brooklyn for the music, and the Staten Island Ferry for the best free harbour view in the city.',
            'best_season' => 'April to June, September to November', 'currency' => 'USD',
            'language' => 'English', 'timezone' => 'UTC-4',
            'latitude' => 40.7127281, 'longitude' => -74.0060152, 'is_featured' => false,
        ],
        [
            'name' => 'Krabi', 'country' => 'Thailand', 'continent' => 'Asia',
            'image' => 'thailand-longtail', 'hero_image' => 'thailand-resort',
            'summary' => 'Limestone cliffs straight out of the sea, and the best sport climbing in Asia.',
            'description' => 'Krabi province covers Railay, Ao Nang and the islands out towards Phi Phi. Railay is reachable only by longtail boat and is the climbing centre — several hundred bolted routes on tufa-covered limestone, with schools that take complete beginners. Four-island day trips are the standard boat outing; going out at first light avoids most of the fleet.',
            'best_season' => 'November to April', 'currency' => 'THB',
            'language' => 'Thai', 'timezone' => 'UTC+7',
            'latitude' => 8.0862997, 'longitude' => 98.9062835, 'is_featured' => true,
        ],
        [
            'name' => 'Cappadocia', 'country' => 'Türkiye', 'continent' => 'Asia',
            'image' => 'cappadocia-balloons', 'hero_image' => 'cappadocia-balloons',
            'summary' => 'Volcanic rock carved into churches, houses and whole underground cities.',
            'description' => 'Cappadocia’s landscape is soft volcanic tuff that a thousand years of inhabitants simply dug into — Byzantine cave churches with frescoes intact, and underground cities like Derinkuyu that go down eight levels. The balloon flights are the famous image and genuinely worth the pre-dawn start, though they are weather-dependent and cancel more often than people expect. Book the first morning of your stay so you have days in hand.',
            'best_season' => 'April to June, September to October', 'currency' => 'TRY',
            'language' => 'Turkish', 'timezone' => 'UTC+3',
            'latitude' => 38.6431, 'longitude' => 34.8289, 'is_featured' => true,
        ],
        [
            'name' => 'Agra', 'country' => 'India', 'continent' => 'Asia',
            'image' => 'taj-mahal', 'hero_image' => 'taj-mahal',
            'summary' => 'The Taj Mahal at sunrise, and a Mughal capital most visitors leave too quickly.',
            'description' => 'Almost everyone comes to Agra for the Taj Mahal and leaves within the day, which is a mistake. Agra Fort is a substantial Mughal complex in its own right, and the small tomb of Itimad-ud-Daulah — the "Baby Taj" — is the building that set the style. The Taj is closed on Fridays and best entered at opening; the marble changes colour for about forty minutes after sunrise.',
            'best_season' => 'October to March', 'currency' => 'INR',
            'language' => 'Hindi, English', 'timezone' => 'UTC+5:30',
            'latitude' => 27.1751448, 'longitude' => 78.0421422, 'is_featured' => false,
        ],
        [
            'name' => 'Rio de Janeiro', 'country' => 'Brazil', 'continent' => 'South America',
            'image' => 'rio-sugarloaf', 'hero_image' => 'rio-sugarloaf',
            'summary' => 'Granite peaks, Atlantic rainforest and a city beach culture with no real equivalent.',
            'description' => 'Rio’s geography does most of the work: Sugarloaf and Corcovado give you the whole bay, and Tijuca is the largest urban rainforest in the world, right behind the neighbourhoods. Ipanema and Leblon are the beaches locals use; Copacabana is the famous one. The Lapa arches and Santa Teresa on a Friday night are where the music is.',
            'best_season' => 'May to October', 'currency' => 'BRL',
            'language' => 'Portuguese', 'timezone' => 'UTC-3',
            'latitude' => -22.9110137, 'longitude' => -43.2093727, 'is_featured' => false,
        ],
    ];

    public function run(): void
    {
        foreach (self::DESTINATIONS as $index => $destination) {
            Destination::updateOrCreate(
                ['slug' => str($destination['name'].'-'.$destination['country'])->slug()->value()],
                [
                    ...$destination,
                    'image' => "assets/images/travel/{$destination['image']}-card.jpg",
                    'hero_image' => "assets/images/travel/{$destination['hero_image']}.jpg",
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
