<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id', 'user_id', 'author_name', 'author_email', 'author_avatar',
        'rating', 'title', 'body', 'status', 'helpful_count', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'rating' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Keep the tour's cached rating in step with its approved reviews.
        $sync = fn (self $review) => $review->tour?->refreshRatingCache();

        static::saved($sync);
        static::deleted($sync);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Approved);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('author_name', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%")
                ->orWhereHas('tour', fn (Builder $t) => $t->where('title', 'like', "%{$term}%"));
        }));
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->author_avatar
            ? asset($this->author_avatar)
            : asset('assets/images/teams/user-0'.(($this->id % 8) + 1).'.jpg');
    }
}
