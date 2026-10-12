<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    //
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $search = $request->string('search')->trim()->value();
        $roleId = $request->integer('role_id');

        $users = User::query()
            ->with('role')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleId > 0, function ($query) use ($roleId): void {
                $query->where('role_id', $roleId);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'search', 'roleId'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role_id' => $request->validated('role_id'),
            'password' => Hash::make((string) $request->validated('password')),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $payload = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role_id' => $request->validated('role_id'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make((string) $request->validated('password'));
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
