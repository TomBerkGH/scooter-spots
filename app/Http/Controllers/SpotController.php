<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpotRequest;
use App\Models\Spot;
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
        $spots = $request->user()->spots()
            ->latest()
            ->get();

        return Inertia::render('Spots/Index', [
            'spots' => $spots,
        ]);
    }

    /**
     * Toon het formulier om een nieuwe spot aan te maken.
     */
    public function create(): Response
    {
        return Inertia::render('Spots/Create');
    }

    /**
     * Sla een nieuwe spot op in de database.
     */
    public function store(StoreSpotRequest $request): RedirectResponse
    {
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
