<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * An uploaded image.
 *
 * Consumers (tours.image, home section content, …) store the plain `path`
 * string rather than a foreign key, so a picked upload and one of the theme's
 * shipped files behave identically and nothing breaks if a record is deleted.
 */
class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $fillable = ['path', 'name', 'alt', 'mime', 'size', 'width', 'height', 'uploaded_by'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('alt', 'like', "%{$term}%"));
    }

    public function getUrlAttribute(): string
    {
        return asset($this->path);
    }

    /** Human-readable size for the picker ("284 KB"). */
    public function getSizeLabelAttribute(): string
    {
        $bytes = (int) $this->size;

        return $bytes >= 1048576
            ? round($bytes / 1048576, 1).' MB'
            : max(1, (int) round($bytes / 1024)).' KB';
    }

    /** Remove the file behind this record; missing files are not an error. */
    public function deleteFile(): void
    {
        $relative = Str::after($this->path, 'storage/');

        if ($relative !== $this->path && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
