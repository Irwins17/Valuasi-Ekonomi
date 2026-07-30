<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('role')->paginate(15);
        return Inertia::render('Admin/Users/Index', ['users' => $users]);
    }

    public function create(): Response
    {
        $roles = Role::all();
        return Inertia::render('Admin/Users/Create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string',
            'institution' => 'nullable|string',
        ]);

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit($id): Response
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return Inertia::render('Admin/Users/Edit', ['user' => $user, 'roles' => $roles]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string',
            'institution' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus');
    }
}
