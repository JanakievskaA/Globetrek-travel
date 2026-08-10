<?php

namespace App\Support;

use App\Enums\DurationBucket;
use App\Enums\TourSort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Immutable value object describing the state of the tour list sidebar.
 *
 * Built once from the request, it knows how to (a) constrain a query and
 * (b) describe itself back to the view, so the controller stays thin and the
 * Blade filter component has a single source of truth.
 */
class TourFilters
{
    public const PRICE_FLOOR = 0;

    public const PRICE_CEILING = 5000;

    /**
     * @param  array<int, string>  $categories  category slugs
     * @param  array<int, string>  $durations  DurationBucket values
     * @param  array<int, float>  $ratings  minimum average-score thresholds
     * @param  array<int, string>  $amenities
     */
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $destination = null,
        public readonly array $categories = [],
        public readonly array $durations = [],
        public readonly array $ratings = [],
        public readonly array $amenities = [],
        public readonly ?string $date = null,
        public readonly int $people = 0,
        public readonly int $minPrice = self::PRICE_FLOOR,
        public readonly int $maxPrice = self::PRICE_CEILING,
        public readonly TourSort $sort = TourSort::Recommended,
        public readonly string $layout = 'list',
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->string('q')->trim()->value() ?: null,
            destination: $request->string('destination')->value() ?: null,
            categories: self::toArray($request->input('category')),
            durations: self::toArray($request->input('duration')),
            ratings: array_map('floatval', self::toArray($request->input('rating'))),
            amenities: self::toArray($request->input('amenity')),
            date: $request->date('date')?->toDateString(),
            people: max(0, (int) $request->input('people', 0)),
            minPrice: max(self::PRICE_FLOOR, (int) $request->input('min_price', self::PRICE_FLOOR)),
            maxPrice: min(self::PRICE_CEILING, (int) $request->input('max_price', self::PRICE_CEILING)),
            sort: TourSort::tryFrom((string) $request->input('sort')) ?? TourSort::Recommended,
            layout: $request->input('layout') === 'grid' ? 'grid' : 'list',
        );
    }

    /** Normalises "a,b" strings and arrays alike into a clean list. */
    private static function toArray(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return array_values(array_filter(array_map(
            fn ($v) => is_scalar($v) ? trim((string) $v) : null,
            (array) $value
        ), fn ($v) => $v !== null && $v !== ''));
    }

    public function apply(Builder $query): Builder
    {
        $query->search($this->search);

        $query->when($this->destination, fn (Builder $q, string $slug) => $q
            ->whereHas('destination', fn (Builder $d) => $d->where('slug', $slug)));

        $query->when($this->categories, fn (Builder $q, array $slugs) => $q
            ->whereHas('category', fn (Builder $c) => $c->whereIn('slug', $slugs)));

        $query->when($this->people, fn (Builder $q, int $people) => $q
            ->where('group_size', '>=', $people));

        /*
         * Price always applies against the discounted price when there is one.
         * At the top of the slider the label reads "$5,000+", so the ceiling is
         * open-ended: capping there would hide every tour priced above it.
         */
        $query->whereRaw('COALESCE(sale_price, price) >= ?', [$this->minPrice]);

        if ($this->maxPrice < self::PRICE_CEILING) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [$this->maxPrice]);
        }

        $query->when($this->ratings, fn (Builder $q, array $ratings) => $q
            ->where('rating_avg', '>=', min($ratings)));

        $query->when($this->durations, function (Builder $q, array $buckets) {
            $q->where(function (Builder $inner) use ($buckets) {
                foreach ($buckets as $value) {
                    if ($bucket = DurationBucket::tryFrom($value)) {
                        $inner->orWhere(fn (Builder $b) => $bucket->apply($b));
                    }
                }
            });
        });

        // Amenities are stored as a JSON list; every selected one must be present.
        foreach ($this->amenities as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }

        return $this->sort->apply($query);
    }

    public function hasAny(): bool
    {
        return $this->search !== null
            || $this->destination !== null
            || $this->categories !== []
            || $this->durations !== []
            || $this->ratings !== []
            || $this->amenities !== []
            || $this->date !== null
            || $this->people > 0
            || $this->minPrice > self::PRICE_FLOOR
            || $this->maxPrice < self::PRICE_CEILING;
    }

    public function isChecked(string $group, string $value): bool
    {
        return in_array($value, match ($group) {
            'category' => $this->categories,
            'duration' => $this->durations,
            'rating' => array_map(fn (float $r) => rtrim(rtrim(number_format($r, 1), '0'), '.'), $this->ratings),
            'amenity' => $this->amenities,
            default => [],
        }, true);
    }

    /** Query-string representation, used for pagination links and sort switches. */
    public function toQuery(array $overrides = []): array
    {
        return array_filter([
            'q' => $this->search,
            'destination' => $this->destination,
            'category' => $this->categories,
            'duration' => $this->durations,
            'rating' => $this->ratings,
            'amenity' => $this->amenities,
            'date' => $this->date,
            'people' => $this->people ?: null,
            'min_price' => $this->minPrice > self::PRICE_FLOOR ? $this->minPrice : null,
            'max_price' => $this->maxPrice < self::PRICE_CEILING ? $this->maxPrice : null,
            'sort' => $this->sort !== TourSort::Recommended ? $this->sort->value : null,
            'layout' => $this->layout !== 'list' ? $this->layout : null,
            ...$overrides,
        ], fn ($v) => $v !== null && $v !== [] && $v !== '');
    }

    /** Human-readable chips shown above the results. */
    public function activeChips(array $categoryNames = [], array $destinationNames = []): array
    {
        $chips = [];

        if ($this->search) {
            $chips[] = ['label' => "“{$this->search}”", 'key' => 'q', 'value' => null];
        }
        if ($this->destination) {
            $chips[] = [
                'label' => $destinationNames[$this->destination] ?? $this->destination,
                'key' => 'destination', 'value' => null,
            ];
        }
        foreach ($this->categories as $slug) {
            $chips[] = ['label' => $categoryNames[$slug] ?? $slug, 'key' => 'category', 'value' => $slug];
        }
        foreach ($this->durations as $value) {
            $chips[] = [
                'label' => DurationBucket::tryFrom($value)?->label() ?? $value,
                'key' => 'duration', 'value' => $value,
            ];
        }
        foreach ($this->ratings as $rating) {
            $value = rtrim(rtrim(number_format($rating, 1), '0'), '.');
            $chips[] = ['label' => "{$value}★ and above", 'key' => 'rating', 'value' => $value];
        }
        foreach ($this->amenities as $amenity) {
            $chips[] = ['label' => $amenity, 'key' => 'amenity', 'value' => $amenity];
        }
        if ($this->people > 0) {
            $chips[] = ['label' => "{$this->people} travellers", 'key' => 'people', 'value' => null];
        }
        if ($this->minPrice > self::PRICE_FLOOR || $this->maxPrice < self::PRICE_CEILING) {
            $chips[] = [
                'label' => '$'.number_format($this->minPrice).' – $'.number_format($this->maxPrice),
                'key' => 'price', 'value' => null,
            ];
        }

        return $chips;
    }

    /** Removes one chip and returns the remaining query string. */
    public function without(string $key, ?string $value = null): array
    {
        $query = $this->toQuery();

        if ($key === 'price') {
            unset($query['min_price'], $query['max_price']);

            return $query;
        }

        if ($value === null) {
            unset($query[$key]);

            return $query;
        }

        if (isset($query[$key]) && is_array($query[$key])) {
            $query[$key] = array_values(array_filter(
                $query[$key],
                fn ($v) => (string) $v !== $value
            ));
            if ($query[$key] === []) {
                unset($query[$key]);
            }
        }

        return $query;
    }
}
