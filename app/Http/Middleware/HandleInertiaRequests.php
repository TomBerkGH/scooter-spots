<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $siteNames = [
            'scooterspots' => 'Scooter Spots',
            'wandelspots' => 'Wandel Spots',
            'localhost' => 'Scooter Spots (DEV)',
        ];
        $host = Str::lower($request->getHost());
        $host = Str::replaceStart('www.', '', $host);
        $domainName = Str::before($host, '.');

        return [
            ...parent::share($request),
            'name' => $siteNames[$domainName] ?? config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'toast' => fn () => $request->session()->get('toast'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
