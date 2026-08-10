<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('staff.view');

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => Role::query()->where('name', '!=', 'customer')->with('permissions:id,name')->orderBy('name')->get(),
                'permissions' => Permission::query()->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('staff.manage');
        $validated = $request->validate($this->rules());
        $role = Role::query()->create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions']);

        return response()->json(['success' => true, 'message' => 'Role created successfully.', 'data' => ['role' => $role->load('permissions:id,name')]], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        Gate::authorize('staff.manage');
        abort_if($role->name === 'customer', 404);
        $validated = $request->validate($this->rules($role));

        if ($role->name === 'super_admin') {
            $role->syncPermissions(Permission::all());
        } else {
            $role->update(['name' => $validated['name']]);
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json(['success' => true, 'message' => 'Role updated successfully.', 'data' => ['role' => $role->load('permissions:id,name')]]);
    }

    private function rules(?Role $role = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role)],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', Rule::exists(Permission::class, 'name')->where('guard_name', 'web')],
        ];
    }
}
