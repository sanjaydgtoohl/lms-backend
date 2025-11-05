<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission; // Assuming you have a Permission model
use App\Services\RoleService;
use App\Http\Requests\StoreRoleRequest;   // We'll create these next
use App\Http\Requests\UpdateRoleRequest; // We'll create these next
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;

    // Inject the service
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        
        // Example of applying middleware
        // $this->middleware('permission:roles-read')->only(['index', 'show']);
        // $this->middleware('permission:roles-create')->only(['create', 'store']);
        // $this->middleware('permission:roles-update')->only(['edit', 'update']);
        // $this->middleware('permission:roles-delete')->only(['destroy']);
    }

    // Display a list of roles
    public function index()
    {
        $roles = $this->roleService->getRolesPaginated(20);
        return view('admin.roles.index', compact('roles'));
    }

    // Show the form for creating a new role
    public function create()
    {
        // You need to pass permissions to the view
        $permissions = Permission::all()->groupBy('group_name'); // Example grouping
        return view('admin.roles.create', compact('permissions'));
    }

    // Store a new role
    public function store(StoreRoleRequest $request)
    {
        $this->roleService->createNewRole($request->validated());

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role created successfully.');
    }

    // Display a specific role
    public function show(Role $role)
    {
        // $role is injected via Route Model Binding (using the 'slug')
        return view('admin.roles.show', compact('role'));
    }

    // Show the form for editing a role
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy('group_name');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    // Update a specific role
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->roleService->updateRole($role->id, $request->validated());

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role updated successfully.');
    }

    // Soft delete a role
    public function destroy(Role $role)
    {
        $this->roleService->deleteRole($role->id);

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role deleted successfully.');
    }
}