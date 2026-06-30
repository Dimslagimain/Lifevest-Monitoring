<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\SuspendUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()->orderBy('role')->orderBy('name')->get();

        return view('superadmin.users', compact('users'));
    }

    public function store(StoreUserRequest $request)
    {

        User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function suspend(SuspendUserRequest $request, User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot suspend your own account.');
        }

        $user->update([
            'is_suspended' => true,
            'suspension_reason' => $request->reason,
        ]);

        // Log the activity
        ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'action' => 'suspend_user',
            'details' => [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'reason' => $request->reason,
            ],
        ]);

        return redirect()->back()->with('success', "User {$user->name} has been suspended.");
    }

    public function unsuspend(User $user)
    {
        $user->update([
            'is_suspended' => false,
            'suspension_reason' => null,
        ]);

        // Log the activity
        ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'action' => 'unsuspend_user',
            'details' => [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
            ],
        ]);

        return redirect()->back()->with('success', "User {$user->name} has been unsuspended.");
    }
}
