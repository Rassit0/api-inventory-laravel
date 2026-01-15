<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Config\Branch;
use App\Models\Config\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{

    public function config()
    {
        return response()->json([
            // 'branches' => Role::all()->map(function ($role) {
            //     return [
            //         'id' => $role->id,
            //         'name' => $role->name,
            //     ];
            // }),
            'branches' => Branch::where('state', 1)->get()->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ];
            }),
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query("search");
        $warehouses = Warehouse::where("name", "ilike", "%{$search}%")->orderBy("id", "desc")->get();
        return response()->json([
            "warehouses" => $warehouses->map(function ($warehouse) {
                return [
                    "id" => $warehouse->id,
                    "name" => $warehouse->name,
                    "address" => $warehouse->address,
                    "phone" => $warehouse->phone,
                    "branch_id" => $warehouse->branch_id,
                    "branch" => [
                        "id" => $warehouse->branch->id,
                        "name" => $warehouse->branch->name,
                    ],
                    "state" => $warehouse->state,
                    "created_at" => $warehouse->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validación de campos
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:warehouses,name',
            'address' => 'required|string|max:500',
            'branch_id' => 'required|exists:branches,id',
            'state'   => 'nullable|in:0,1',
            'phone'   => 'nullable|string|max:20',
        ], [
            'name.unique' => 'El nombre del almacén ya está en uso.',
            'name.required' => 'El campo nombre es obligatorio.',
            'address.required' => 'El campo dirección es obligatorio.',
            'state.required' => 'El campo estado es obligatorio.',
            'branch_id.required' => 'El campo sucursal es obligatorio.',
            'branch_id.exists' => 'La sucursal seleccionada no es válida.',
            'state.in' => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        // Crear la sucursal
        $warehouse = Warehouse::create($validated);
        $warehouse->refresh();

        return response()->json([
            'message' => 'Sucursal creada exitosamente',
            'warehouse' => [
                "id" => $warehouse->id,
                "name" => $warehouse->name,
                "address" => $warehouse->address,
                "phone" => $warehouse->phone,
                "branch_id" => $warehouse->branch_id,
                "branch" => [
                    "id" => $warehouse->branch->id,
                    "name" => $warehouse->branch->name,
                ],
                "state" => $warehouse->state,
                "created_at" => $warehouse->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:warehouses,name,' . $id,
            'address' => 'required|string|max:500',
            'branch_id' => 'required|exists:branches,id',
            'state'   => 'required|in:0,1',
            'phone'   => 'nullable|string|max:20',
        ], [
            'name.unique'   => 'El nombre del almacén ya está en uso.',
            'name.required' => 'El campo nombre es obligatorio.',
            'address.required' => 'El campo dirección es obligatorio.',
            'branch_id.required' => 'El campo sucursal es obligatorio.',
            'branch_id.exists' => 'La sucursal seleccionada no es válida.',
            'state.required'   => 'El campo estado es obligatorio.',
            'state.in'         => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        $warehouse = Warehouse::findOrFail($id);

        $warehouse->update($validated);
        $warehouse->refresh();

        return response()->json([
            'message' => 'Sucursal actualizada exitosamente',
            'warehouse' => [
                "id" => $warehouse->id,
                "name" => $warehouse->name,
                "address" => $warehouse->address,
                "phone" => $warehouse->phone,
                "branch_id" => $warehouse->branch_id,
                "branch" => [
                    "id" => $warehouse->branch->id,
                    "name" => $warehouse->branch->name,
                ],
                "state" => $warehouse->state,
                "created_at" => $warehouse->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        return response()->json([
            'message' => 'Sucursal eliminada exitosamente',
            'warehouse' => [
                "id" => $warehouse->id,
                "name" => $warehouse->name,
                "address" => $warehouse->address,
                "phone" => $warehouse->phone,
                "branch_id" => $warehouse->branch_id,
                "branch" => [
                    "id" => $warehouse->branch->id,
                    "name" => $warehouse->branch->name,
                ],
                "state" => $warehouse->state,
                "created_at" => $warehouse->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }
}
