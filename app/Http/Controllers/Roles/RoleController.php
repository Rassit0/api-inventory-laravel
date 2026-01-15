<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query("search");
        $roles = Role::where("name", "ilike", "%{$search}%")->orderBy("id", "desc")->get();
        return response()->json([
            "roles" => $roles->map(function ($role) {
                return [
                    "id" => $role->id,
                    "name" => $role->name,
                    "description" => $role->description,
                    "created_at" => $role->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                    "updated_at" => $role->updated_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                    // Permisos
                    "permissions" => $role->permissions->map(function ($permission) {
                        return [
                            "id" => $permission->id,
                            "name" => $permission->name,
                        ];
                    }),
                    "permissions_pluck" => $role->permissions->pluck("name"),

                    'users' => $role->users->sortBy('name')->take(4)->map(function ($user) {
                        return [
                            "id" => $user->id,
                            "name" => $user->name,
                            "avatar" => $user->avatar ? env('APP_URL') . '/storage/' . $user->avatar : null,
                        ];
                    })->values(),
                    'users_count' => $role->users->count(),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $name = $request->name;

        // Preguntar directo a la DB
        $exists = Role::where('name', $name)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'El nombre ya existe'
            ], 409); // 409 Conflict
        }

        $role = Role::create([
            'name' => $name,
            'guard_name' => 'api'
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role' => [
                "id" => $role->id,
                "name" => $role->name,
                "created_at" => $role->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                "updated_at" => $role->updated_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                // Permisos
                "permissions" => $role->permissions->map(function ($permission) {
                    return [
                        "id" => $permission->id,
                        "name" => $permission->name,
                    ];
                }),
                "permissions_pluck" => $role->permissions->pluck("name"),
            ]
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Que no exista otro rol con el mismo nombre excluyendo el actual
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            "name" => "required|string|max:255|unique:roles,name,{$id}",
            "permissions" => "nullable|array",
            "permissions.*" => "exists:permissions,name"
        ]);

        // Actualizar nombre del rol
        $role->update([
            "name" => $validated["name"],
        ]);

        // Actualizar permisos asociados
        if (isset($validated["permissions"])) {
            $role->syncPermissions($validated["permissions"]);
        }

        return response()->json([
            "message" => "Role updated successfully",
            'role' => [
                "id" => $role->id,
                "name" => $role->name,
                "created_at" => $role->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                "updated_at" => $role->updated_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                // Permisos
                "permissions" => $role->permissions->map(function ($permission) {
                    return [
                        "id" => $permission->id,
                        "name" => $permission->name,
                    ];
                }),
                "permissions_pluck" => $role->permissions->pluck("name"),
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        $role->delete();

        return response()->json([
            "message" => "Role deleted successfully",
            'role' => [
                "id" => $role->id,
                "name" => $role->name,
                "created_at" => $role->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                "updated_at" => $role->updated_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                // Permisos
                "permissions" => $role->permissions->map(function ($permission) {
                    return [
                        "id" => $permission->id,
                        "name" => $permission->name,
                    ];
                }),
                "permissions_pluck" => $role->permissions->pluck("name"),
            ]
        ], 200);
    }
}
