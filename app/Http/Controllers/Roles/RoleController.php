<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware('permission:list_role', only: ['index', 'show']),
            new Middleware('permission:register_role', only: ['store']),
            new Middleware('permission:edit_role', only: ['update']),
            new Middleware('permission:delete_role', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query("search");
        $page = $request->query("page", 1);
        $per_page = $request->query("per_page", 10);
        $roles = Role::where("name", "ilike", "%{$search}%")->orderBy("id", "desc")
            ->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            "total" => $roles->total(),
            'current_page' => $roles->currentPage(),
            'per_page' => $roles->perPage(),
            'last_page' => $roles->lastPage(),
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
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                'unique:roles,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'permissions' => [
                'sometimes',
                'array',
            ],
            'permissions.*' => [
                'string',
                'exists:permissions,name',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:api,web',
            ],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 100 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones.',
            'name.unique' => 'El nombre del rol ya está en uso.',
            'description.max' => 'La descripción no debe exceder los 255 caracteres.',
            'permissions.array' => 'Los permisos deben ser proporcionados como un arreglo.',
            'permissions.*.exists' => 'Uno o más permisos seleccionados no son válidos.',
            'permissions.*.string' => 'El nombre del permiso debe ser texto.',
            'guard_name.in' => 'El guard especificado no es válido.',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'api'
        ]);

        if ($request->has('permissions') && is_array($request->permissions)) {
            $role->syncPermissions($validated['permissions']);
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
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-]+$/',
                "unique:roles,name,{$id}",
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'permissions' => [
                'sometimes',
                'array',
            ],
            'permissions.*' => [
                'string',
                'exists:permissions,name',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:api,web',
            ],
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 100 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones.',
            'name.unique' => 'El nombre del rol ya está en uso.',
            'description.max' => 'La descripción no debe exceder los 255 caracteres.',
            'permissions.array' => 'Los permisos deben ser proporcionados como un arreglo.',
            'permissions.*.exists' => 'Uno o más permisos seleccionados no son válidos.',
            'permissions.*.string' => 'El nombre del permiso debe ser texto.',
            'guard_name.in' => 'El guard especificado no es válido.',
        ]);

        // Actualizar nombre del rol
        $role->update([
            "name" => $validated["name"],
        ]);

        // Actualizar permisos asociados
        if (isset($validated["permissions"]) && is_array($validated["permissions"])) {
            // Convertir los IDs de permisos a instancias de Permission
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

        // Verificar si hay usuarios con este rol
        if ($role->users()->exists()) {
            return response()->json([
                "message" => "No se puede eliminar el rol porque está siendo utilizado por uno o más usuarios",
                "users_count" => $role->users()->count()
            ], 409);
        }

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
