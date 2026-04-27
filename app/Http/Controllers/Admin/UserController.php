<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'boolean',
        ]);
        $validated['password'] = bcrypt($validated['password']);
        User::create($validated);
        return redirect()->route('admin.users.index')->with('success', 'Пользователь добавлен');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'is_admin' => 'boolean',
        ]);
        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        }
        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Пользователь удалён');
    }
}