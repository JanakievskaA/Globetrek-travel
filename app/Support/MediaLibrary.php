<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The picker's two sources of images.
 *
 * "Uploads" are rows in the media table. "Theme" images are the files the
 * template ships in public/assets/images — they were the only images the site
 * had before uploading existed, and every seeded tour still points at them, so
 * the picker has to offer them too.
 */
class MediaLibrary
{
    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'avif'];

    private const THEME_ROOT = 'assets/images';

    private const THEME_CACHE_KEY = 'media.theme-images';

    /** Store an upload and record it. Returns the saved model. */
    public function store(UploadedFile $file, ?int $userId = null, ?string $alt = null): Media
    {
        $name = Str::of($file->getClientOriginalName())->beforeLast('.')->slug()->limit(60, '')->value();
        $name = $name !== '' ? $name : 'image';

        $directory = 'uploads/'.now()->format('Y/m');
        $filename = $name.'-'.Str::lower(Str::random(6)).'.'.Str::lower($file->getClientOriginalExtension());

        $file->storeAs($directory, $filename, 'public');

        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];

        return Media::create([
            'path' => 'storage/'.$directory.'/'.$filename,
            'name' => Str::of($file->getClientOriginalName())->beforeLast('.')->headline()->value(),
            'alt' => $alt,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?: null,
            'height' => $dimensions[1] ?: null,
            'uploaded_by' => $userId,
        ]);
    }

    /** Uploaded images, newest first, shaped for the picker. */
    public function uploads(?string $search = null): Collection
    {
        return Media::query()
            ->search($search)
            ->latest()
            ->take(300)
            ->get()
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'path' => $media->path,
                'url' => $media->url,
                'name' => $media->name,
                'meta' => $media->size_label.($media->width ? " · {$media->width}×{$media->height}" : ''),
                'source' => 'upload',
            ]);
    }

    /** Images shipped with the theme, discovered on disk. */
    public function themeImages(?string $search = null): Collection
    {
        $files = Cache::remember(self::THEME_CACHE_KEY, now()->addMinutes(10), function () {
            $root = public_path(self::THEME_ROOT);

            if (! File::isDirectory($root)) {
                return [];
            }

            return collect(File::allFiles($root))
                ->filter(fn ($file) => in_array(Str::lower($file->getExtension()), self::EXTENSIONS, true))
                ->map(fn ($file) => [
                    'path' => self::THEME_ROOT.'/'.str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname()),
                    'name' => Str::of($file->getFilenameWithoutExtension())->replace(['-', '_'], ' ')->headline()->value(),
                    'folder' => str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePath()) ?: 'images',
                    'size' => $file->getSize(),
                ])
                ->sortBy('path')
                ->values()
                ->all();
        });

        return collect($files)
            ->when($search, fn (Collection $items) => $items->filter(
                fn (array $item) => Str::contains(Str::lower($item['name'].' '.$item['path']), Str::lower($search))))
            ->map(fn (array $item) => [
                'id' => null,
                'path' => $item['path'],
                'url' => asset($item['path']),
                'name' => $item['name'],
                'meta' => $item['folder'],
                'source' => 'theme',
            ])
            ->values();
    }

    public static function forgetThemeCache(): void
    {
        Cache::forget(self::THEME_CACHE_KEY);
    }
}
