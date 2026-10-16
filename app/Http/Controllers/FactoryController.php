<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\Scopes\FactoryScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Factory CRUD is Super Admin only. Normal users never see this screen —
 * they are permanently tied to the one factory they were created in.
 */
class FactoryController extends Controller
{
    public function index()
    {
        // Counts must ignore the "currently viewed factory" scope — this
        // screen is the one place a Super Admin sees both factories'
        // totals side by side, not just whichever one is selected.
        $factories = Factory::withCount([
            'employees' => fn ($q) => $q->withoutGlobalScope(FactoryScope::class),
            'medicines' => fn ($q) => $q->withoutGlobalScope(FactoryScope::class),
            'users',
        ])->orderBy('name')->get();

        return view('factories.index', ['factories' => $factories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'code' => ['required', 'string', 'max:20', Rule::unique('factories', 'code')],
            'address' => 'nullable|string',
        ]);

        Factory::create($data);

        return back()->with('success', 'Factory added.');
    }

    public function update(Request $request, Factory $factory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'code' => ['required', 'string', 'max:20', Rule::unique('factories', 'code')->ignore($factory->id)],
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $factory->update($data);

        return back()->with('success', 'Factory updated.');
    }

    /** Super Admin switches which single factory they are currently viewing. Never merges the two. */
    public function switch(Request $request)
    {
        if (! $request->user()?->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate(['factory_id' => 'required|exists:factories,id']);

        $request->session()->put('viewing_factory_id', $data['factory_id']);

        return back()->with('success', 'Switched factory view.');
    }
}
