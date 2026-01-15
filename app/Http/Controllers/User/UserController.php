<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Models\Config\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::where(function ($query) use ($search) {
            // Usamos COALESCE para manejar valores NULL de forma segura y portable
            $query->whereRaw("CONCAT(name, ' ', COALESCE(surname, '')) ILIKE ?", ["%$search%"])
                ->orWhere('email', 'ILIKE', "%$search%")
                ->orWhere('phone', 'ILIKE', "%$search%");
        })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'full_name' => $user->name . ' ' . $user->surname,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'role' => [
                        'name' => $user->role->name,
                    ],
                    'phone' => $user->phone,
                    'state' => $user->state,
                    'branch_id' => $user->branch_id,
                    'branch' => $user->branch_id ? [
                        'name' => $user->branch->name,
                    ] : null,
                    'avatar' => $user->avatar ? env('APP_URL') . '/storage/' . $user->avatar : null,
                    'type_document' => $user->type_document,
                    'n_document' => $user->n_document,
                    'gender' => $user->gender,
                    'created_at' => $user->created_at->format('Y-m-d H:i A'),
                    'updated_at' => $user->updated_at->format('Y-m-d H:i A'),
                    'deleted_at' => $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i A') : null,
                ];
            }),
        ], 200);
    }

    public function config()
    {
        return response()->json([
            'roles' => Role::all()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            }),
            'branches' => Branch::all()->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {

        // ✅ Obtiene los datos validados directamente del FormRequest
        $validated = $request->validated();

        // if ($request->hasFile('imagen')) {
        //     $path = Storage::putFile('users', $request->file('imagen'));
        //     $request->request->add(['avatar' => $path]);
        // }
        // 🔹 Guardar imagen si existe
        if ($request->hasFile('imagen')) {
            $validated['avatar'] = $request->file('imagen')->store('users', 'public');
        }

        // if ($request->password) {
        //     $request->request->add(['password' => bcrypt($request->password)]);
        // }
        // 🔹 Encriptar password
        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);
        // ⚡ Asignar rol correctamente usando Spatie
        if (!empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }

        // Para que los defaults de DB se reflejen en el objeto
        $user->refresh();

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'full_name' => $user->name . ' ' . $user->surname,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role' => [
                    'name' => $user->role->name,
                ],
                'phone' => $user->phone,
                'state' => $user->state,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch_id ? [
                    'name' => $user->branch->name,
                ] : null,
                'avatar' => $user->avatar ? env('APP_URL') . '/storage/' . $user->avatar : null,
                'type_document' => $user->type_document,
                'n_document' => $user->n_document,
                'gender' => $user->gender,
                'created_at' => $user->created_at->format('Y-m-d H:i A'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i A'),
                'deleted_at' => $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i A') : null,
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
    public function update(UserRequest $request, string $id)
    {
        // ✅ Obtiene el usuario a actualizar
        $user = User::findOrFail($id);

        // ✅ Obtiene los datos validados
        $validated = $request->validated();

        // 🔹 Guardar imagen si existe
        if ($request->hasFile('imagen')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('imagen')->store('users', 'public');
        }

        // 🔹 Encriptar password solo si viene en el request
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']); // ❌ evita sobrescribir con null
        }

        // ✅ Actualizar usuario
        $user->update($validated);

        // ⚡ Asignar rol correctamente usando Spatie
        if (!empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role) {
                $user->syncRoles([$role->name]); // elimina roles anteriores y asigna el nuevo
            }
        }

        $user->refresh();

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'full_name' => $user->name . ' ' . $user->surname,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role' => [
                    'name' => $user->role->name,
                ],
                'phone' => $user->phone,
                'state' => $user->state,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch_id ? [
                    'name' => $user->branch->name,
                ] : null,
                'avatar' => $user->avatar ? env('APP_URL') . '/storage/' . $user->avatar : null,
                'type_document' => $user->type_document,
                'n_document' => $user->n_document,
                'gender' => $user->gender,
                'created_at' => $user->created_at->format('Y-m-d H:i A'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i A'),
                'deleted_at' => $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i A') : null,
            ]
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $user->refresh();
        return response()->json([
            'message' => 'Usuario eliminado exitosamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'full_name' => $user->name . ' ' . $user->surname,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role' => [
                    'name' => $user->role->name,
                ],
                'phone' => $user->phone,
                'state' => $user->state,
                'branch_id' => $user->branch_id,
                'branch' => $user->branch_id ? [
                    'name' => $user->branch->name,
                ] : null,
                'avatar' => $user->avatar ? env('APP_URL') . '/storage/' . $user->avatar : null,
                'type_document' => $user->type_document,
                'n_document' => $user->n_document,
                'gender' => $user->gender,
                'created_at' => $user->created_at->format('Y-m-d H:i A'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i A'),
                'deleted_at' => $user->deleted_at ? $user->deleted_at->format('Y-m-d H:i A') : null,
            ]
        ]);
    }
}
