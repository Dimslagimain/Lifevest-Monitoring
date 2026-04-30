<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::query()->orderBy('role')->orderBy('name')->get();
        return view('superadmin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:superadmin,admin,user',
        ]);

        \App\Models\User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:superadmin,admin,user',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->password) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(\App\Models\User $user)
    {
        if (\Illuminate\Support\Facades\Auth::id() === $user->id) {
            return redirect()->back()->with('error', "You cannot delete your own account.");
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function suspend(Request $request, \App\Models\User $user)
    {
        if (\Illuminate\Support\Facades\Auth::id() === $user->id) {
            return redirect()->back()->with('error', "You cannot suspend your own account.");
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user->update([
            'is_suspended' => true,
            'suspension_reason' => $request->reason,
        ]);

        // Log the activity
        \App\Models\ActivityLog::query()->create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'action' => 'suspend_user',
            'details' => [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'reason' => $request->reason,
            ]
        ]);

        return redirect()->back()->with('success', "User {$user->name} has been suspended.");
    }

    public function unsuspend(\App\Models\User $user)
    {
        $user->update([
            'is_suspended' => false,
            'suspension_reason' => null,
        ]);

        // Log the activity
        \App\Models\ActivityLog::query()->create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'action' => 'unsuspend_user',
            'details' => [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
            ]
        ]);

        return redirect()->back()->with('success', "User {$user->name} has been unsuspended.");
    }
}
