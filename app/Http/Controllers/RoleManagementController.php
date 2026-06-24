<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount('permissions')->orderByDesc('protected')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => new Role(['active' => true]),
            'permissions' => Permission::orderBy('name')->get(),
            'selectedPermissions' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug(($data['slug'] ?? '') ?: $data['name'], '_');

        $role = Role::create([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => true,
            'protected' => false,
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.edit', $role)->with('admin_success', 'Role cree avec succes.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => Permission::orderBy('name')->get(),
            'selectedPermissions' => $role->permissions()->pluck('permissions.id')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validated($request, $role);
        $data['slug'] = $role->protected ? $role->slug : Str::slug(($data['slug'] ?? '') ?: $data['name'], '_');
        $data['active'] = $role->protected ? true : (bool) ($data['active'] ?? false);
        $data['permissions'] = array_map('intval', $data['permissions'] ?? []);

        if ($request->user()->role === $role->slug && (! $data['active'] || ! in_array($this->usersPermissionId(), $data['permissions'], true))) {
            return back()->withErrors(['permissions' => 'Vous ne pouvez pas retirer vos propres droits de gestion des utilisateurs.'])->withInput();
        }

        $role->update([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => $data['active'],
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('admin_success', 'Role et permissions mis a jour.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($role->protected) {
            return back()->withErrors(['role' => 'Ce role systeme ne peut pas etre supprime.']);
        }

        if ($request->user()->role === $role->slug) {
            return back()->withErrors(['role' => 'Vous ne pouvez pas supprimer votre propre role.']);
        }

        if (User::where('role', $role->slug)->exists()) {
            return back()->withErrors(['role' => 'Ce role est encore attribue a un ou plusieurs utilisateurs.']);
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('admin_success', 'Role supprime.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'slug' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('roles', 'slug')->ignore($role)],
            'description' => 'nullable|string|max:500',
            'active' => 'nullable|boolean',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
    }

    private function usersPermissionId(): ?int
    {
        return Permission::where('slug', 'users')->value('id');
    }
}
