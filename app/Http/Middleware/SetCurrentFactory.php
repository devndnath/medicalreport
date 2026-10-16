<?php

namespace App\Http\Middleware;

use App\Models\Factory;
use App\Support\CurrentFactory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves which single factory the rest of this request should see, and
 * stores it in App\Support\CurrentFactory so every factory-scoped model
 * query is filtered automatically:
 *
 * - A normal user is always locked to their own factory_id.
 * - A Super Admin has no factory_id of their own; they view exactly one
 *   factory at a time, remembered in session('viewing_factory_id') and
 *   changeable via the factory switcher (FactoryController::switch).
 *   The two factories are always shown separately, never merged.
 */
class SetCurrentFactory
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->isSuperAdmin()) {
                $factoryId = $request->session()->get('viewing_factory_id');

                if (! $factoryId || ! Factory::where('id', $factoryId)->exists()) {
                    $factoryId = Factory::orderBy('id')->value('id');
                    if ($factoryId) {
                        $request->session()->put('viewing_factory_id', $factoryId);
                    }
                }
            } else {
                $factoryId = $user->factory_id;
            }

            CurrentFactory::set($factoryId);
        }

        return $next($request);
    }
}
