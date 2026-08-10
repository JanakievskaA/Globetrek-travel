<?php

namespace App\Models;

use App\Enums\TourStatus;
use App\Support\TourFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'destination_id', 'category_id', 'summary', 'description',
        'image', 'price', 'sale_price', 'duration_days', 'duration_nights', 'duration_hours',
        'group_size', 'min_age', 'difficulty', 'departure_point', 'contact_phone',
        'languages', 'includes', 'excludes', 'highlights', 'amenities', 'faqs', 'extras',
        'latitude', 'longitude', 'is_featured', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TourStatus::class,
            'languages' => 'array',
            'includes' => 'array',
            'excludes' => 'array',
            'highlights' => 'array',
            'amenities' => 'array',
            'faqs' => 'array',
            'extras' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'rating_avg' => 'float',
            'is_featured' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $tour) {
            $tour->slug = $tour->slug ?: Str::slug($tour->title);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---------------------------------------------------------------- relations

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(TourImage::class)->orderBy('sort_order');
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(TourItinerary::class)->orderBy('day');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', 'approved')->latest();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ------------------------------------------------------------------ scopes

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', TourStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Adds the price actually charged (sale price when present) as a sortable
     * and filterable column, so price filtering respects discounts.
     */
    public function scopeWithEffectivePrice(Builder $query): Builder
    {
        return $query->select('tours.*')
            ->selectRaw('COALESCE(sale_price, price) as effective_price');
    }

    /** Applies every visitor-facing filter in one pass. */
    public function scopeFilter(Builder $query, TourFilters $filters): Builder
    {
        return $filters->apply($query);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('tours.title', 'like', "%{$term}%")
                ->orWhere('tours.summary', 'like', "%{$term}%")
                ->orWhereHas('destination', fn (Builder $d) => $d
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('country', 'like', "%{$term}%"));
        }));
    }

    // ---------------------------------------------------------------- accessors

    public function getEffectivePriceAttribute(): float
    {
        // Present when the query used withEffectivePrice(), computed otherwise.
        return (float) ($this->attributes['effective_price'] ?? $this->sale_price ?? $this->price);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->is_on_sale || (float) $this->price <= 0) {
            return null;
        }

        return (int) round(100 - ((float) $this->sale_price / (float) $this->price * 100));
    }

    /** "5 days 4 nights" for multi-day tours, "6 hours" for day trips. */
    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_days > 0) {
            $label = $this->duration_days.' '.Str::plural('day', $this->duration_days);

            return $this->duration_nights > 0
                ? $label.' '.$this->duration_nights.' '.Str::plural('night', $this->duration_nights)
                : $label;
        }

        return ($this->duration_hours ?? 0).' '.Str::plural('hour', $this->duration_hours ?? 0);
    }

    public function getShortDurationAttribute(): string
    {
        return $this->duration_days > 0
            ? $this->duration_days.' '.Str::plural('day', $this->duration_days)
            : ($this->duration_hours ?? 0).'h';
    }

    /** Cover image plus gallery, used by the detail page lightbox. */
    public function getGalleryAttribute()
    {
        return $this->images->pluck('path')->prepend($this->image)->unique()->values();
    }

    /** Recomputes the cached rating aggregates from approved reviews. */
    public function refreshRatingCache(): void
    {
        $stats = $this->reviews()
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as total, AVG(rating) as average')
            ->first();

        $this->forceFill([
            'reviews_count' => (int) $stats->total,
            'rating_avg' => round((float) $stats->average, 2),
        ])->saveQuietly();
    }
}
