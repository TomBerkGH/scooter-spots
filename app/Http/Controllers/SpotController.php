<?php

namespace App\Http\Controllers;

use App\Actions\Spots\DeleteSpotAction;
use App\Actions\Spots\StoreSpotAction;
use App\Http\Requests\StoreSpotRequest;
use App\Http\Requests\UpdateSpotRequest;
use App\Models\Spot;
use App\Queries\AvailableTagsQuery;
use App\Queries\SpotSearchQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpotController extends Controller
{
    /**
     * Openbaar overzicht van alle spots.
     */
    public function home(Request $request, SpotSearchQuery $spots): Response
    {
        $search = $spots->normalize($request->query('search'));

        return Inertia::render('Home', [
            'spots' => $spots->executeAll($search),
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Overzichtspagina met de kaart en alle opgeslagen spots.
     */
    public function index(
        Request $request,
        SpotSearchQuery $spots,
        AvailableTagsQuery $tags,
    ): Response
    {
        $search = $spots->normalize($request->query('search'));

        return Inertia::render('Spots/Index', [
            'spots' => $spots->execute($request->user(), $search),
            'tags' => $tags->execute(),
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Toon het formulier om een nieuwe spot aan te maken.
     */
    public function create(AvailableTagsQuery $tags): Response
    {
        return Inertia::render('Spots/Create', [
            'tags' => $tags->execute(),
            'locationRequired' => app()->environment('production'),
        ]);
    }

    /**
     * Sla een nieuwe spot op in de database.
     */
    public function store(
        StoreSpotRequest $request,
        StoreSpotAction $storeSpot,
    ): RedirectResponse {
        $storeSpot->execute($request->user(), $request->toData());

        return redirect()->route('spots.index')
            ->with('toast', ['type' => 'success', 'message' => 'Plek opgeslagen.']);
    }

    /**
     * Werk de tekst van een eigen spot bij.
     */
    public function update(UpdateSpotRequest $request, Spot $spot): RedirectResponse
    {
        $data = $request->validated();

        $spot->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);
        $spot->tags()->sync($data['tags'] ?? []);

        return redirect()->route('spots.index')
            ->with('toast', ['type' => 'success', 'message' => 'Plek bijgewerkt.']);
    }

    /**
     * Verwijder een spot inclusief de foto van storage.
     */
    public function destroy(
        Request $request,
        Spot $spot,
        DeleteSpotAction $deleteSpot,
    ): RedirectResponse {
        $deleteSpot->execute($request->user(), $spot);

        return redirect()->route('spots.index')
            ->with('toast', ['type' => 'success', 'message' => 'Plek verwijderd.']);
    }
}
