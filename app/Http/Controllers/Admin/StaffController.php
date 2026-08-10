<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('staff.view');

        return response()->json([
            'success' => true,
            'data' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', '!=', 'customer'))
                ->with('roles:id,name')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('staff.manage');
        $validated = $request->validate($this->rules());
        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => $validated['status'],
        ]);
        $user->syncRoles([$validated['role']]);

        return response()->json(['success' => true, 'message' => 'Staff member created successfully.', 'data' => ['staff' => $user->load('roles:id,name')]], 201);
    }

    public function update(Request $request, User $staff): JsonResponse
    {
        Gate::authorize('staff.manage');
        abort_if($staff->hasRole('customer'), 404);
        $validated = $request->validate($this->rules($staff));

        if ($staff->is($request->user()) && $validated['status'] !== 'active') {
            return response()->json(['success' => false, 'message' => 'You cannot deactivate your own account.'], 422);
        }

        $staff->update(array_filter([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ?? null,
            'status' => $validated['status'],
        ], fn ($value) => $value !== null));
        $staff->syncRoles([$validated['role']]);

        return response()->json(['success' => true, 'message' => 'Staff member updated successfully.', 'data' => ['staff' => $staff->load('roles:id,name')]]);
    }

    private function rules(?User $staff = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff)],
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:8'],
            'status' => ['required', Rule::in(['active', 'inactive', 'blocked', 'pending'])],
            'role' => ['required', Rule::exists(Role::class, 'name')->where('guard_name', 'web'), Rule::notIn(['customer'])],
        ];
    }
}
