<?php

namespace App\Http\Controllers;

use App\Actions\Spots\DeleteSpotAction;
use App\Actions\Spots\StoreSpotAction;
use App\Http\Requests\StoreSpotRequest;
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
     * Overzichtspagina met de kaart en alle opgeslagen spots.
     */
    public function index(Request $request, SpotSearchQuery $spots): Response
    {
        $search = $spots->normalize($request->query('search'));

        return Inertia::render('Spots/Index', [
            'spots' => $spots->execute($request->user(), $search),
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
