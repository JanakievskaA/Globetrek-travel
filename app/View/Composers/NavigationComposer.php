<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Destination;
use Illuminate\View\View;

/**
 * Supplies the header and footer with their menu data. Cached for the request
 * so the two components do not run the same queries twice.
 */
class NavigationComposer
{
    private static ?array $cache = null;

    public function compose(View $view): void
    {
        $view->with(self::$cache ??= [
            'navDestinations' => Destination::active()->featured()
                ->orderBy('sort_order')->take(6)->get(),
            'navCategories' => Category::active()
                ->orderBy('sort_order')->take(8)->get(),
        ]);
    }
}
