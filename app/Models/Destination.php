<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'country', 'continent', 'summary', 'description',
        'image', 'hero_image', 'latitude', 'longitude', 'best_season',
        'currency', 'language', 'timezone', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $destination) {
            $destination->slug = $destination->slug
                ?: Str::slug($destination->name.'-'.$destination->country);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    public function publishedTours(): HasMany
    {
        return $this->tours()->where('status', 'published');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /** Free-text search across the fields a visitor would type. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('country', 'like', "%{$term}%")
                ->orWhere('continent', 'like', "%{$term}%");
        }));
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name}, {$this->country}";
    }
}
