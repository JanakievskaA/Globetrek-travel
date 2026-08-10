<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $destinations = Destination::query()
            ->withCount('tours')
            ->search($request->string('q')->trim()->value() ?: null)
            ->when($request->input('continent'), fn ($q, $c) => $q->where('continent', $c))
            ->when($request->input('active') !== null && $request->input('active') !== '',
                fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('admin.destinations.index', [
            'destinations' => $destinations,
            'continents' => Destination::distinct()->orderBy('continent')->pluck('continent')->filter(),
        ]);
    }

    public function create(): View
    {
        return view('admin.destinations.form', ['destination' => new Destination(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $destination = Destination::create($this->validated($request));

        return redirect()
            ->route('admin.destinations.index')
            ->with('success', "“{$destination->name}” has been created.");
    }

    public function edit(Destination $destination): View
    {
        return view('admin.destinations.form', ['destination' => $destination]);
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $destination->update($this->validated($request, $destination));

        return redirect()
            ->route('admin.destinations.index')
            ->with('success', 'Destination updated.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        if ($destination->tours()->exists()) {
            return back()->with('error',
                "“{$destination->name}” still has tours attached. Move or delete those first.");
        }

        $name = $destination->name;
        $destination->delete();

        return redirect()
            ->route('admin.destinations.index')
            ->with('success', "“{$name}” was deleted.");
    }

    private function validated(Request $request, ?Destination $destination = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160',
                Rule::unique('destinations', 'slug')->ignore($destination?->id)],
            'country' => ['required', 'string', 'max:120'],
            'continent' => ['nullable', 'string', 'max:80'],
            'summary' => ['required', 'string', 'max:400'],
            'description' => ['nullable', 'string'],
            'image' => ['required', 'string', 'max:255'],
            'hero_image' => ['nullable', 'string', 'max:255'],
            'best_season' => ['nullable', 'string', 'max:120'],
            'currency' => ['nullable', 'string', 'max:12'],
            'language' => ['nullable', 'string', 'max:120'],
            'timezone' => ['nullable', 'string', 'max:40'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        return [
            ...$data,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
