<?php

namespace App\Models;

use App\Support\PageSections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Editable content for one section of one page.
 *
 * The front end never assumes a row exists: `PageSection::content()` fills in
 * anything missing from App\Support\PageSections, so an empty table renders
 * every page exactly as it was written.
 */
class PageSection extends Model
{
    protected $fillable = ['key', 'heading', 'subtitle', 'data', 'is_visible'];

    private static ?Collection $cache = null;

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    /** Every section, keyed, with defaults merged in. Cached per request. */
    public static function content(): Collection
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $saved = static::query()->get()->keyBy('key');

        return static::$cache = collect(PageSections::keys())->mapWithKeys(function (string $key) use ($saved) {
            $section = $saved->get($key) ?? new static(['key' => $key, 'is_visible' => true]);
            $defaults = PageSections::defaults($key);

            $section->heading ??= $defaults['heading'];
            $section->subtitle ??= $defaults['subtitle'];
            // Saved values win field by field, so a new field added to the
            // registry starts life with its default on already-saved sections.
            $section->data = array_replace(
                $defaults['data'],
                array_filter($section->data ?? [], fn ($value) => $value !== null && $value !== ''),
            );

            return [$key => $section];
        });
    }

    public static function for(string $key): self
    {
        return static::content()->get($key) ?? new static(['key' => $key, 'is_visible' => true]);
    }

    /** One page's sections, in registry order — what a page controller wants. */
    public static function forPage(string $page): Collection
    {
        $keys = array_keys(PageSections::forPage($page));

        return static::content()->only($keys);
    }

    /** Forget the per-request cache — used after saving and in tests. */
    public static function flushContent(): void
    {
        static::$cache = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushContent());
        static::deleted(fn () => static::flushContent());
    }

    /** A single value out of `data`, with the registry default as fallback. */
    public function value(string $field, mixed $fallback = null): mixed
    {
        $value = Arr::get($this->data ?? [], $field);

        if ($value === null || $value === '' || $value === []) {
            return $fallback ?? Arr::get(PageSections::defaults($this->key)['data'], $field);
        }

        return $value;
    }

    /** Repeater rows, filtered down to the ones that actually have content. */
    public function rows(string $field): array
    {
        $rows = $this->value($field, []);

        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter(
            $rows,
            fn ($row) => is_array($row) && collect($row)->contains(fn ($value) => filled($value)),
        ));
    }
}
