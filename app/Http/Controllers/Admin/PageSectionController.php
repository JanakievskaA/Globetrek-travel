<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\PageSection;
use App\Support\PageSections;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Pages screen: a tab per editable page, one row per section, each with its
 * own form built from App\Support\PageSections.
 */
class PageSectionController extends Controller
{
    public function index(string $page = 'home'): View
    {
        $this->page($page);

        return view('admin.pages.index', [
            'page' => $page,
            'pages' => PageSections::pages(),
            'sections' => PageSection::forPage($page),
            'definitions' => PageSections::forPage($page),
        ]);
    }

    public function edit(string $page, string $key): View
    {
        $definition = $this->definition($page, $key);

        return view('admin.pages.edit', [
            'page' => $page,
            'pages' => PageSections::pages(),
            'pageLabel' => $this->page($page)['label'],
            'key' => $key,
            'definition' => $definition,
            'section' => PageSection::for($key),
            'destinations' => Destination::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, string $page, string $key): RedirectResponse
    {
        $definition = $this->definition($page, $key);
        $validated = $request->validate($this->rules($key, $definition));

        $section = PageSection::firstOrNew(['key' => $key]);
        $section->fill([
            'heading' => PageSections::hasHeading($key) ? Arr::get($validated, 'heading') : $section->heading,
            'subtitle' => PageSections::hasSubtitle($key) ? Arr::get($validated, 'subtitle') : $section->subtitle,
            'data' => $this->cleanData($definition, Arr::get($validated, 'data', [])),
            'is_visible' => $request->boolean('is_visible'),
        ])->save();

        return redirect()
            ->route('admin.pages.index', $page)
            ->with('success', "“{$definition['label']}” has been updated.");
    }

    /** The show/hide button on the index screen. */
    public function toggle(Request $request, string $page, string $key): RedirectResponse
    {
        $definition = $this->definition($page, $key);

        $section = PageSection::firstOrNew(['key' => $key]);
        $section->is_visible = ! ($section->is_visible ?? true);
        $section->save();

        return back()->with('success', $section->is_visible
            ? "“{$definition['label']}” is showing again."
            : "“{$definition['label']}” is hidden from the page.");
    }

    private function page(string $page): array
    {
        return PageSections::pages()[$page]
            ?? throw new NotFoundHttpException("Unknown page [{$page}].");
    }

    /** Also proves the section belongs to the page in the URL. */
    private function definition(string $page, string $key): array
    {
        $this->page($page);

        $definition = PageSections::definition($key);

        if ($definition === null || $definition['page'] !== $page) {
            throw new NotFoundHttpException("Unknown section [{$key}] on page [{$page}].");
        }

        return $definition;
    }

    /** Validation rules, generated from the section's field list. */
    private function rules(string $key, array $definition): array
    {
        $rules = [
            'heading' => ['nullable', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:400'],
        ];

        foreach ($definition['fields'] ?? [] as $field => $spec) {
            if (($spec['type'] ?? 'text') === 'repeater') {
                $rules["data.{$field}"] = ['nullable', 'array', 'max:'.($spec['max'] ?? 12)];

                foreach ($spec['fields'] as $sub => $subSpec) {
                    $rules["data.{$field}.*.{$sub}"] = $this->fieldRules($subSpec);
                }

                continue;
            }

            $rules["data.{$field}"] = $this->fieldRules($spec);
        }

        return $rules;
    }

    private function fieldRules(array $spec): array
    {
        $type = $spec['type'] ?? 'text';

        $length = $spec['max_length'] ?? match ($type) {
            'url' => 400,
            'textarea' => 1000,
            default => 255,
        };

        return match ($type) {
            'number' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'url' => ['nullable', 'string', 'url', "max:{$length}"],
            'image' => ['nullable', 'string', 'max:255'],
            'textarea' => ['nullable', 'string', "max:{$length}"],
            'destination' => ['nullable', Rule::exists('destinations', 'id')],
            default => ['nullable', 'string', "max:{$length}"],
        };
    }

    /**
     * Drop repeater rows the admin left completely blank — an empty row would
     * otherwise render as an empty slide or card.
     */
    private function cleanData(array $definition, array $data): array
    {
        foreach ($definition['fields'] ?? [] as $field => $spec) {
            if (($spec['type'] ?? 'text') !== 'repeater') {
                continue;
            }

            $data[$field] = array_values(array_filter(
                $data[$field] ?? [],
                fn ($row) => is_array($row) && collect($row)->contains(fn ($value) => filled($value)),
            ));
        }

        return $data;
    }
}
