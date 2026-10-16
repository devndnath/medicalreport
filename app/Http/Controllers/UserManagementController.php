<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\Role;
use App\Models\User;
use App\Support\CurrentFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        // A factory-bound Admin sees only this factory's users. A Super
        // Admin sees this factory's users too (the current switcher
        // selection), plus the other Super Admin accounts, which belong
        // to no single factory and so are always listed.
        $users = User::with(['role', 'factory'])
            ->where(function ($q) {
                $q->where('factory_id', CurrentFactory::id())
                    ->orWhereHas('role', fn ($r) => $r->where('key', 'super_admin'));
            })
            ->orderBy('name')->paginate(20);

        return view('users.index', [
            'users' => $users,
            'roles' => $this->assignableRoles(),
            'factories' => auth()->user()->isSuperAdmin() ? Factory::orderBy('name')->get() : null,
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'roles' => $this->assignableRoles(),
            'factories' => auth()->user()->isSuperAdmin() ? Factory::orderBy('name')->get() : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'factory_id' => 'nullable|exists:factories,id',
        ]);

        $role = Role::find($data['role_id']);
        $this->guardRoleAssignment($role);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'factory_id' => $this->resolveFactoryIdFor($role, $data['factory_id'] ?? null),
        ]);

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'factory_id' => 'nullable|exists:factories,id',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $role = Role::find($data['role_id']);
        $this->guardRoleAssignment($role);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role_id = $data['role_id'];
        $user->factory_id = $this->resolveFactoryIdFor($role, $data['factory_id'] ?? $user->factory_id);
        $user->is_active = $request->boolean('is_active');
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User removed.');
    }

    /** Only a Super Admin may create or hand out the super_admin role itself. */
    protected function guardRoleAssignment(?Role $role): void
    {
        if ($role && $role->key === 'super_admin' && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Only a Super Admin can create another Super Admin account.');
        }
    }

    /**
     * super_admin users belong to no single factory. Everyone else must
     * belong to exactly one: a Super Admin picks it on the form, while a
     * factory-bound Admin can only ever create users in their own
     * factory regardless of what the form posted.
     */
    protected function resolveFactoryIdFor(?Role $role, ?int $postedFactoryId): ?int
    {
        if ($role && $role->key === 'super_admin') {
            return null;
        }

        return auth()->user()->isSuperAdmin() ? $postedFactoryId : CurrentFactory::id();
    }

    protected function assignableRoles()
    {
        $roles = Role::orderBy('name')->get();

        return auth()->user()->isSuperAdmin() ? $roles : $roles->where('key', '!=', 'super_admin')->values();
    }
}
