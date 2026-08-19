<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpotRequest;
use App\Models\Spot;
use App\Models\Tag;
use App\Services\NominatimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SpotController extends Controller
{
    /**
     * Overzichtspagina met de kaart en alle opgeslagen spots.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $search = mb_substr($search, 0, 100);

        $spots = $request->user()->spots()
            ->with(['tags:id,name', 'locationData'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('tags', fn ($query) => $query
                            ->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('locationData', function ($query) use ($search) {
                            $query->where('display_name', 'like', "%{$search}%")
                                ->orWhere('road', 'like', "%{$search}%")
                                ->orWhere('city', 'like', "%{$search}%")
                                ->orWhere('town', 'like', "%{$search}%")
                                ->orWhere('village', 'like', "%{$search}%")
                                ->orWhere('municipality', 'like', "%{$search}%")
                                ->orWhere('postcode', 'like', "%{$search}%")
                                ->orWhere('state', 'like', "%{$search}%")
                                ->orWhere('country', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        return Inertia::render('Spots/Index', [
            'spots' => $spots,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Toon het formulier om een nieuwe spot aan te maken.
     */
    public function create(): Response
    {
        return Inertia::render('Spots/Create', [
            'tags' => Tag::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Sla een nieuwe spot op in de database.
     */
    public function store(
        StoreSpotRequest $request,
        NominatimService $nominatim,
    ): RedirectResponse {
        $validated = $request->validated();

        $disk = config('filesystems.spots_disk');
        [$metadata, $encodedImage] = explode(',', $validated['image'], 2);
        $image = base64_decode($encodedImage, true);
        abort_if($image === false, 422, 'De foto kon niet worden verwerkt.');

        $extension = str_contains($metadata, 'image/png') ? 'png' : 'jpg';
        $imagePath = 'spots/'.Str::uuid().'.'.$extension;
        Storage::disk($disk)->put($imagePath, $image);

        // Spot opslaan
        $spot = $request->user()->spots()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'image_path' => $imagePath,
        ]);

        $spot->tags()->sync($validated['tags'] ?? []);
        $nominatim->enrich($spot);

        return redirect()->route('spots.index')
            ->with('toast', ['type' => 'success', 'message' => 'Plek opgeslagen.']);
    }

    /**
     * Verwijder een spot inclusief de foto van storage.
     */
    public function destroy(Request $request, Spot $spot): RedirectResponse
    {
        // Autorisatie-check
        if ($spot->user_id !== $request->user()->id) {
            abort(403);
        }

        Storage::disk(config('filesystems.spots_disk'))->delete($spot->image_path);

        $spot->delete();

        return redirect()->route('spots.index')
            ->with('toast', ['type' => 'success', 'message' => 'Plek verwijderd.']);
    }
}
