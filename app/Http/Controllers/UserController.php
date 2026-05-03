<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->select(
            'id',
            'name',
            'email',
            'role',
            'is_approved',
            'created_at',
        );

        // Search (name / email)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere(
                    'email',
                    'like',
                    "%$search%",
                );
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter approval
        if (
            $request->has('is_approved') &&
            $request->input('is_approved') !== null
        ) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'is_approved']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        // auto approve teacher created by admin
        if ($data['role'] === 'teacher') {
            $data['is_approved'] = true;
        }

        // other role is auto approved
        $data['is_approved'] = true;

        User::create($data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'User created successfully.',
        ]);

        return to_route('admin.user.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // nanti bisa untuk statistik (submission count, dll)
        // return Inertia::render('admin/UserDetail', [
        //     'user' => $user->only('id', 'name', 'email', 'role', 'is_approved'),
        // ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // handle password optional
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // reset email verification kalau email berubah
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'User updated successfully.',
        ]);

        return to_route('admin.user.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You cannot delete yourself.',
            ]);
            return to_route('admin.user.index');
        }

        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'User deleted successfully.',
        ]);

        return to_route('admin.user.index');
    }

    /**
     * Approve teacher registration
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(User $user)
    {
        $user->update([
            'is_approved' => !$user->is_approved,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Teacher registration approved successfully.',
        ]);

        return to_route('admin.user.index');
    }
}
