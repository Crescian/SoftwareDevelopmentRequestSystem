<?php

namespace App\Http\Controllers;

use App\Enums\DashboardRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * System Administrator's own screen for assigning which dashboard each
     * user lands on — the only place dashboard_role can be changed today.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => DashboardRole::cases(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'dashboard_role' => ['required', Rule::in(array_column(DashboardRole::cases(), 'value'))],
        ]);

        $user->update(['dashboard_role' => $validated['dashboard_role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name}'s dashboard is now " . DashboardRole::from($validated['dashboard_role'])->label() . '.');
    }
}
