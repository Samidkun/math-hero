<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
        $player = null;
        if ($playerId = $request->session()->get('player_id')) {
            $player = \App\Models\Player::with(['classroom', 'progress.level'])->find($playerId);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'player' => $player ? [
                    'id' => $player->id,
                    'nickname' => $player->nickname,
                    'class_id' => $player->class_id,
                    'class_code' => $player->classroom?->code,
                    'class_name' => $player->classroom?->name,
                    'total_stars' => $player->total_stars,
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}
