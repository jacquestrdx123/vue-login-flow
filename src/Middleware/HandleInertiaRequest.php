<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware\HandleInertiaRequests;

class {MIDDLEWARE_CLASS} extends HandleInertiaRequests
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                '{GUARD_NAME}' => [
                    'user' => $request->user('{GUARD_NAME}') ? [
                        'id' => $request->user('{GUARD_NAME}')->id,
                        'name' => $request->user('{GUARD_NAME}')->name ?? null,
                        'email' => $request->user('{GUARD_NAME}')->email ?? null,
                    ] : null,
                ],
            ],
        ]);
    }
}

