<?php

namespace App\Support;

/**
 * The editable pages, described as data.
 *
 * One entry per section of the page's Blade file. The `fields` list drives the
 * admin form, the validation rules and the defaults the front end falls back to
 * when a section has never been edited — so adding an editable field is a
 * change in this one file plus the Blade line that reads it.
 *
 * Field types: text, textarea, number, url, image, repeater (rows of fields),
 * destination (a picker of existing destinations).
 *
 * Section keys are unique across every page, which is what lets all three share
 * one table. `page` is stamped on automatically by all().
 */
class PageSections
{
    /** The tabs on the admin Pages screen, in order. */
    public static function pages(): array
    {
        return [
            'home' => ['label' => 'Homepage', 'route' => 'home', 'description' => 'Every section of the front page, top to bottom.'],
            'about' => ['label' => 'About us', 'route' => 'about', 'description' => 'The story, the principles and the numbers on /about.'],
            'contact' => ['label' => 'Contact', 'route' => 'contact', 'description' => 'The intro, contact details and enquiry form on /contact.'],
        ];
    }

    /** Every section across every page, each tagged with the page it belongs to. */
    public static function all(): array
    {
        $sections = [];

        foreach (array_keys(self::pages()) as $page) {
            foreach (self::{$page}() as $key => $definition) {
                $sections[$key] = $definition + ['page' => $page];
            }
        }

        return $sections;
    }

    /** Just one page's sections, in the order they appear on it. */
    public static function forPage(string $page): array
    {
        return array_filter(self::all(), fn (array $definition) => $definition['page'] === $page);
    }

    private static function home(): array
    {
        return [
            'hero' => [
                'label' => 'Hero slider',
                'description' => 'The full-screen slider at the top, with the search bar over it.',
                'has_heading' => false,
                'fields' => [
                    'slides' => [
                        'type' => 'repeater',
                        'label' => 'Slides',
                        'hint' => 'Leave empty to fall back to the featured destinations.',
                        'max' => 6,
                        'row_label' => 'Slide',
                        'fields' => [
                            'image' => ['type' => 'image', 'label' => 'Background photo', 'span' => 12],
                            'destination_id' => [
                                'type' => 'destination',
                                'label' => 'Links to destination',
                                'span' => 6,
                                'hint' => 'Fills the “12 tours here / best time” strip and the button.',
                            ],
                            'eyebrow' => ['type' => 'text', 'label' => 'Small label above the title', 'span' => 6],
                            'title' => ['type' => 'text', 'label' => 'Title', 'span' => 12],
                            'summary' => ['type' => 'textarea', 'label' => 'Sentence under the title', 'span' => 12, 'rows' => 2],
                        ],
                    ],
                ],
            ],

            'stats_bar' => [
                'label' => 'Stats bar',
                'description' => 'The four numbers under the hero. Tours, destinations and rating count themselves.',
                'has_heading' => false,
                'fields' => [
                    'travellers' => [
                        'type' => 'number',
                        'label' => 'Travellers hosted',
                        'default' => 12840,
                        'span' => 4,
                        'hint' => 'Shown as “12,840+”. The other three numbers come from the catalogue.',
                    ],
                    'tours_label' => ['type' => 'text', 'label' => 'Tours label', 'default' => 'Curated tours', 'span' => 4],
                    'destinations_label' => ['type' => 'text', 'label' => 'Destinations label', 'default' => 'Destinations', 'span' => 4],
                    'travellers_label' => ['type' => 'text', 'label' => 'Travellers label', 'default' => 'Travellers hosted', 'span' => 4],
                    'rating_label' => ['type' => 'text', 'label' => 'Rating label', 'default' => 'Average rating', 'span' => 4],
                ],
            ],

            'categories' => [
                'label' => 'Types of tours',
                'description' => 'The row of tour-type icons. The types themselves are managed under Categories.',
                'heading' => 'Types of tours',
                'subtitle' => 'Ten ways to travel, from dawn game drives to four-day treks. Pick the shape of the trip first.',
                'fields' => [],
            ],

            'benefits' => [
                'label' => 'Why travel with us',
                'description' => 'The four selling points. The sentence under the heading counts the catalogue automatically.',
                'heading' => 'Why travel with GlobeTrek',
                'fields' => [
                    'cards' => [
                        'type' => 'repeater',
                        'label' => 'Cards',
                        'max' => 8,
                        'row_label' => 'Card',
                        'fields' => [
                            'icon' => ['type' => 'image', 'label' => 'Icon', 'span' => 12],
                            'title' => ['type' => 'text', 'label' => 'Title', 'span' => 12],
                            'text' => ['type' => 'textarea', 'label' => 'Text', 'span' => 12, 'rows' => 3],
                        ],
                        'default' => [
                            [
                                'icon' => 'assets/images/icons/benefit-1.svg',
                                'title' => 'Small groups, always',
                                'text' => 'Most departures cap at twelve. Some cap at six. You will never be handed a numbered sticker and pointed at a coach.',
                            ],
                            [
                                'icon' => 'assets/images/icons/benefit-2.svg',
                                'title' => 'Guides who live there',
                                'text' => 'Every guide is local, licensed and paid properly. That is why the restaurants are good and the timings are right.',
                            ],
                            [
                                'icon' => 'assets/images/icons/benefit-3.svg',
                                'title' => 'No surprise costs',
                                'text' => 'The price you see covers what the itinerary says it covers. Optional extras are listed with their prices before you book.',
                            ],
                            [
                                'icon' => 'assets/images/icons/service-1.svg',
                                'title' => 'Fair cancellations',
                                'text' => 'Full refund up to 30 days out, half up to 14. Plans change, and we would rather you booked with confidence.',
                            ],
                        ],
                    ],
                ],
            ],

            'spotlight' => [
                'label' => 'Destination spotlight',
                'description' => 'The large photo with the destination facts beside it.',
                'has_heading' => false,
                'fields' => [
                    'destination_id' => [
                        'type' => 'destination',
                        'label' => 'Destination',
                        'span' => 6,
                        'hint' => 'Leave blank to rotate through the featured destinations.',
                    ],
                    'image' => [
                        'type' => 'image',
                        'label' => 'Photo override',
                        'span' => 6,
                        'hint' => 'Optional. Defaults to the destination’s own photo.',
                    ],
                    'phone' => ['type' => 'text', 'label' => 'Phone number', 'default' => '(229) 555-0109', 'span' => 6],
                    'phone_label' => ['type' => 'text', 'label' => 'Phone caption', 'default' => 'Speak to a specialist', 'span' => 6],
                ],
            ],

            'tour_tabs' => [
                'label' => 'Best-selling tours',
                'description' => 'The tabbed grid of tours. Tours are chosen automatically by rating.',
                'heading' => 'Our best-selling tours',
                'subtitle' => 'The departures travellers book most, rated by the people who actually went.',
                'fields' => [
                    'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Browse all tours', 'span' => 6],
                ],
            ],

            'video_banner' => [
                'label' => 'Video banner',
                'description' => 'The wide photo with the play button.',
                'heading' => 'Journey to discover amazing nature',
                'fields' => [
                    'image' => [
                        'type' => 'image',
                        'label' => 'Background photo',
                        'default' => 'assets/images/travel/mountains-clouds.jpg',
                        'span' => 12,
                    ],
                    'video_url' => [
                        'type' => 'url',
                        'label' => 'Video link',
                        'default' => 'https://www.youtube.com/embed/x7X9w_GIm1s?autoplay=1',
                        'span' => 12,
                        'hint' => 'A YouTube or Vimeo embed link. Leave blank to hide the play button.',
                    ],
                    'text' => [
                        'type' => 'textarea',
                        'label' => 'Text under the title',
                        'default' => 'Two minutes on where we go and why it is worth the early starts.',
                        'span' => 12,
                        'rows' => 2,
                        'hint' => 'The traveller and destination counts are added above this line automatically.',
                    ],
                ],
            ],

            'trending' => [
                'label' => 'Trending right now',
                'description' => 'The carousel of most-booked tours.',
                'heading' => 'Trending right now',
                'subtitle' => 'Booked more than anything else in the last thirty days.',
                'fields' => [],
            ],

            'destinations' => [
                'label' => 'Top destinations',
                'description' => 'The grid of destination cards near the bottom.',
                'heading' => 'Top choice for your trip',
                'subtitle' => 'The destinations our travellers return to most.',
                'fields' => [
                    'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'See all destinations', 'span' => 6],
                ],
            ],

            'testimonials' => [
                'label' => 'What travellers say',
                'description' => 'Reviews marked as featured under Reviews appear here.',
                'heading' => 'What travellers say',
                'subtitle' => 'Verified reviews from people who took the trip.',
                'fields' => [],
            ],
        ];
    }

    private static function about(): array
    {
        return [
            'about_hero' => [
                'label' => 'Page banner',
                'description' => 'The title strip at the top of the page.',
                'heading' => 'About GlobeTrek',
                'has_subtitle' => false,
                'fields' => [
                    'image' => [
                        'type' => 'image',
                        'label' => 'Background photo',
                        'default' => 'assets/images/travel/traveler-silhouette.jpg',
                        'span' => 12,
                    ],
                ],
            ],

            'about_intro' => [
                'label' => 'Story',
                'description' => 'The photo and the opening paragraphs.',
                'heading' => 'Trips built by people who go on them',
                'has_subtitle' => false,
                'fields' => [
                    'image' => [
                        'type' => 'image',
                        'label' => 'Photo',
                        'default' => 'assets/images/travel/alps-hiker.jpg',
                        'span' => 12,
                    ],
                    'body' => [
                        'type' => 'textarea',
                        'label' => 'Paragraphs',
                        'span' => 12,
                        'rows' => 12,
                        'max_length' => 4000,
                        'hint' => 'One paragraph per blank line. :tours, :destinations, :reviews and :rating are '
                            .'replaced with the live counts from the catalogue.',
                        'default' => "GlobeTrek started in 2014 with four guides and one idea: that the best version of a place is the one a local would show you, and that you cannot do that from a fifty-seat coach.\n\n"
                            ."Twelve years later we run :tours tours across :destinations destinations. Every departure is capped, every guide is local and licensed, and every itinerary is walked by our team before it goes on sale. When a route stops being good, we pull it rather than keep selling it.\n\n"
                            .'We publish every review we receive — :reviews of them so far, averaging :rating out of 5 — including the ones that sting.',
                    ],
                ],
            ],

            'about_stats' => [
                'label' => 'Stats bar',
                'description' => 'The four numbers across the middle. Tours, destinations and rating count themselves.',
                'has_heading' => false,
                'fields' => [
                    'travellers' => [
                        'type' => 'number',
                        'label' => 'Travellers hosted',
                        'default' => 12840,
                        'span' => 6,
                        'hint' => 'Shown as “12,840+”. The other three numbers come from the catalogue.',
                    ],
                ],
            ],

            'about_principles' => [
                'label' => 'How we work',
                'description' => 'The grid of commitments.',
                'heading' => 'How we work',
                'subtitle' => 'Four commitments that shape every itinerary we publish.',
                'fields' => [
                    'cards' => [
                        'type' => 'repeater',
                        'label' => 'Commitments',
                        'max' => 9,
                        'row_label' => 'Commitment',
                        'fields' => [
                            'title' => ['type' => 'text', 'label' => 'Title', 'span' => 12],
                            'text' => ['type' => 'textarea', 'label' => 'Text', 'span' => 12, 'rows' => 3],
                        ],
                        'default' => [
                            [
                                'title' => 'Local guides, fairly paid',
                                'text' => 'Guides are hired directly and paid above the local market rate. It costs more and it is the single biggest reason our reviews read the way they do.',
                            ],
                            [
                                'title' => 'Capped group sizes',
                                'text' => 'Twelve is our usual ceiling; climbing and food tours cap at six or eight. Small groups get into places large ones cannot.',
                            ],
                            [
                                'title' => 'Transparent pricing',
                                'text' => 'The listed price covers everything the itinerary describes. Optional extras are priced up front, before you reach checkout.',
                            ],
                            [
                                'title' => 'Routes we have walked',
                                'text' => 'Nobody sells a tour here they have not done. If the timings are wrong, we find out before you do.',
                            ],
                            [
                                'title' => 'Fair cancellation',
                                'text' => 'Full refund at 30 days, half at 14. We would rather you booked early with confidence than late out of caution.',
                            ],
                            [
                                'title' => 'Reviews left alone',
                                'text' => 'Every review is published as written once checked for spam. We never remove one for being critical.',
                            ],
                        ],
                    ],
                ],
            ],

            'about_destinations' => [
                'label' => 'Where we go',
                'description' => 'The destination cards at the bottom. The destinations themselves are managed under Destinations.',
                'heading' => 'Where we go',
                'subtitle' => 'Our most-booked destinations.',
                'fields' => [],
            ],
        ];
    }

    private static function contact(): array
    {
        return [
            'contact_hero' => [
                'label' => 'Page banner',
                'description' => 'The title strip at the top of the page.',
                'heading' => 'Contact us',
                'has_subtitle' => false,
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Background photo', 'span' => 12],
                ],
            ],

            'contact_intro' => [
                'label' => 'Intro',
                'description' => 'The few lines beside the form.',
                'heading' => 'Talk to a specialist',
                'has_subtitle' => false,
                'fields' => [
                    'body' => [
                        'type' => 'textarea',
                        'label' => 'Paragraphs',
                        'span' => 12,
                        'rows' => 6,
                        'max_length' => 2000,
                        'hint' => 'One paragraph per blank line.',
                        'default' => 'Tell us roughly where and when, and we will come back with two or three departures that actually fit — or tell you honestly if we are the wrong fit.',
                    ],
                ],
            ],

            'contact_channels' => [
                'label' => 'Contact details',
                'description' => 'Phone, email, office and opening hours.',
                'has_heading' => false,
                'fields' => [
                    'items' => [
                        'type' => 'repeater',
                        'label' => 'Details',
                        'max' => 8,
                        'row_label' => 'Detail',
                        'fields' => [
                            'icon' => ['type' => 'image', 'label' => 'Icon', 'span' => 12],
                            'label' => ['type' => 'text', 'label' => 'Label', 'span' => 6],
                            'value' => ['type' => 'text', 'label' => 'Value', 'span' => 6],
                            'href' => [
                                'type' => 'text',
                                'label' => 'Link',
                                'span' => 12,
                                'hint' => 'Optional. Use tel: or mailto: for phone and email; leave blank for plain text.',
                            ],
                        ],
                        'default' => [
                            [
                                'icon' => 'assets/images/icons/phone-call.svg',
                                'label' => 'Toll-free',
                                'value' => '(229) 555-0109',
                                'href' => 'tel:+12295550109',
                            ],
                            [
                                'icon' => 'assets/images/icons/mail.svg',
                                'label' => 'Email',
                                'value' => 'hello@globetrek.travel',
                                'href' => 'mailto:hello@globetrek.travel',
                            ],
                            [
                                'icon' => 'assets/images/icons/place.svg',
                                'label' => 'Office',
                                'value' => '32 Rivington Street, London EC2A 3LX',
                                'href' => null,
                            ],
                            [
                                'icon' => 'assets/images/icons/clock.svg',
                                'label' => 'Hours',
                                'value' => 'Mon–Fri 08:00–19:00 GMT',
                                'href' => null,
                            ],
                        ],
                    ],
                ],
            ],

            'contact_form' => [
                'label' => 'Enquiry form',
                'description' => 'The form itself. Its fields are fixed; the wording around them is not.',
                'has_heading' => false,
                'fields' => [
                    'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Send message', 'span' => 6],
                    'subject_placeholder' => [
                        'type' => 'text',
                        'label' => 'Subject placeholder',
                        'default' => 'Which trip are you thinking about?',
                        'span' => 6,
                    ],
                    'message_placeholder' => [
                        'type' => 'text',
                        'label' => 'Message placeholder',
                        'default' => 'Where, when, how long, how many of you.',
                        'span' => 6,
                    ],
                    'success_message' => [
                        'type' => 'text',
                        'label' => 'Message shown after sending',
                        'default' => 'Thanks for getting in touch — we reply within one working day.',
                        'span' => 6,
                    ],
                ],
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function definition(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /** Defaults for one section, used when it has never been saved. */
    public static function defaults(string $key): array
    {
        $definition = self::definition($key) ?? [];

        $data = [];
        foreach ($definition['fields'] ?? [] as $field => $spec) {
            $data[$field] = $spec['default'] ?? ($spec['type'] === 'repeater' ? [] : null);
        }

        return [
            'heading' => $definition['heading'] ?? null,
            'subtitle' => $definition['subtitle'] ?? null,
            'data' => $data,
        ];
    }

    /** Whether the section shows the shared heading field. */
    public static function hasHeading(string $key): bool
    {
        return self::definition($key)['has_heading'] ?? true;
    }

    /** Sections with a heading but no sentence under it opt out here. */
    public static function hasSubtitle(string $key): bool
    {
        return self::hasHeading($key) && (self::definition($key)['has_subtitle'] ?? true);
    }

    /** Which page a section belongs to. */
    public static function pageOf(string $key): ?string
    {
        return self::definition($key)['page'] ?? null;
    }
}
