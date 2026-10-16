<?php

namespace Database\Seeders\Concerns;

use App\Models\Factory;
use App\Models\Medicine;
use App\Models\User;
use App\Support\CurrentFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Shared plumbing for demo seeders that go through the real services
 * (StockService, PurchaseService, IssueService). Those services read the
 * "current factory" and the logged-in user from global state, exactly as
 * they do during a web request, so the seeder has to set that state per
 * factory and always put it back afterwards.
 */
trait SeedsPerFactory
{
    protected function eachFactory(callable $callback): void
    {
        foreach (Factory::orderBy('id')->get() as $factory) {
            CurrentFactory::set($factory->id);

            try {
                $callback($factory);
            } finally {
                CurrentFactory::clear();
                Carbon::setTestNow();
                Auth::forgetUser();
            }
        }
    }

    protected function userFor(Factory $factory, string $roleKey): ?User
    {
        return User::where('factory_id', $factory->id)
            ->whereHas('role', fn ($q) => $q->where('key', $roleKey))
            ->orderBy('id')
            ->first();
    }

    protected function actingAs(Factory $factory, string $roleKey): ?User
    {
        $user = $this->userFor($factory, $roleKey);
        if ($user) {
            Auth::setUser($user);
        }

        return $user;
    }

    protected function medicine(string $name, ?string $strength = null): Medicine
    {
        return Medicine::where('name', $name)
            ->when(
                $strength === null,
                fn ($q) => $q->whereNull('strength'),
                fn ($q) => $q->where('strength', $strength)
            )
            ->firstOrFail();
    }
}
