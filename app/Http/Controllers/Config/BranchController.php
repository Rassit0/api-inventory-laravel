<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Config\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query("search");
        $page = $request->query("page", 1);
        $per_page = $request->query("per_page", 10);
        $branches = Branch::where("name", "ilike", "%{$search}%")->orderBy("id", "desc")
            ->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            "total" => $branches->total(),
            'current_page' => $branches->currentPage(),
            'per_page' => $branches->perPage(),
            'last_page' => $branches->lastPage(),
            "branches" => $branches->map(function ($branch) {
                return [
                    "id" => $branch->id,
                    "name" => $branch->name,
                    "address" => $branch->address,
                    "phone" => $branch->phone,
                    "state" => $branch->state,
                    "created_at" => $branch->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
            'name'    => 'required|string|max:255|unique:branches,name',
            'address' => 'required|string|max:500',
            'state'   => 'nullable|in:0,1',
            'phone'   => 'nullable|string|max:20',
        ], [
            'name.unique' => 'El nombre de la sucursal ya está en uso.',
            'name.required' => 'El campo nombre es obligatorio.',
            'address.required' => 'El campo dirección es obligatorio.',
            'state.required' => 'El campo estado es obligatorio.',
            'state.in' => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        // Crear la sucursal
        $branch = Branch::create($validated);
        $branch->refresh();

        return response()->json([
            'message' => 'Sucursal creada exitosamente',
            'branch' => [
                "id" => $branch->id,
                "name" => $branch->name,
                "address" => $branch->address,
                "phone" => $branch->phone,
                "state" => $branch->state,
                "created_at" => $branch->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
            'name'    => 'required|string|max:255|unique:branches,name,' . $id,
            'address' => 'required|string|max:500',
            'state'   => 'required|in:0,1',
            'phone'   => 'nullable|string|max:20',
        ], [
            'name.unique'   => 'El nombre de la sucursal ya está en uso.',
            'name.required' => 'El campo nombre es obligatorio.',
            'address.required' => 'El campo dirección es obligatorio.',
            'state.required'   => 'El campo estado es obligatorio.',
            'state.in'         => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        $branch = Branch::findOrFail($id);

        $branch->update($validated);
        $branch->refresh();

        return response()->json([
            'message' => 'Sucursal actualizada exitosamente',
            'branch'  => [
                "id"         => $branch->id,
                "name"       => $branch->name,
                "address"    => $branch->address,
                "phone"      => $branch->phone,
                "state"      => $branch->state,
                "created_at" => $branch->updated_at
                    ->timezone("America/La_Paz")
                    ->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();
        return response()->json([
            'message' => 'Sucursal eliminada exitosamente',
            'branch'  => [
                "id"         => $branch->id,
                "name"       => $branch->name,
                "address"    => $branch->address,
                "phone"      => $branch->phone,
                "state"      => $branch->state,
                "created_at" => $branch->updated_at
                    ->timezone("America/La_Paz")
                    ->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }
}
