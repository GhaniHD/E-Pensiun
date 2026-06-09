<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

        $users   = $query->latest()->paginate(20)->withQueryString();
        $roles   = UserRole::cases();
        $offices = User::whereNotNull('office')->distinct()->pluck('office');

        return view('users.index', compact('users', 'roles', 'offices'));
    }

    public function create()
    {
        $roles   = UserRole::cases();
        $offices = User::whereNotNull('office')->distinct()->pluck('office');

        return view('users.create', compact('roles', 'offices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'nip'      => ['nullable', 'string', 'unique:users,nip'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'in:' . implode(',', array_column(UserRole::cases(), 'value'))],
            'office'   => ['nullable', 'string', 'max:255'],
            'password' => ['required', Password::min(8)],
            'is_active'=> ['boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles   = UserRole::cases();
        $offices = User::whereNotNull('office')->distinct()->pluck('office');

        return view('users.edit', compact('user', 'roles', 'offices'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'nip'       => ['nullable', 'string', 'unique:users,nip,' . $user->id],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['required', 'in:' . implode(',', array_column(UserRole::cases(), 'value'))],
            'office'    => ['nullable', 'string', 'max:255'],
            'password'  => ['nullable', Password::min(8)],
            'is_active' => ['boolean'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
                         ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->applications()->exists()) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena memiliki data pengajuan.');
        }

        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Pengguna berhasil {$status}.");
    }
}
