<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Config\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function config()
    {
        return response()->json([
            'units' => Unit::where('state', 1)->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
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
        $units = Unit::where("name", "ilike", "%{$search}%")->orderBy("id", "desc")->get();
        return response()->json([
            "units" => $units->map(function ($unit) {
                return [
                    "id" => $unit->id,
                    "name" => $unit->name,
                    "description" => $unit->description,
                    "state" => $unit->state,
                    "created_at" => $unit->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
            'name'    => 'required|string|max:250|unique:units,name',
            'description' => 'required|string|max:300',
            'state'   => 'nullable|in:0,1',
        ], [
            'name.unique' => 'El nombre de la unidad ya está en uso.',
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El campo nombre no debe exceder los 250 caracteres.',
            'description.required' => 'El campo descripción es obligatorio.',
            'description.max' => 'El campo descripción no debe exceder los 300 caracteres.',
            'state.required' => 'El campo estado es obligatorio.',
            'state.in' => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        // Crear la unidad
        $unit = Unit::create($validated);
        $unit->refresh();

        return response()->json([
            'message' => 'Unidad creada exitosamente',
            'unit' => [
                "id" => $unit->id,
                "name" => $unit->name,
                "description" => $unit->description,
                "state" => $unit->state,
                "created_at" => $unit->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
            'name'    => 'required|string|max:250|unique:units,name,' . $id,
            'description' => 'required|string|max:300',
            'state'   => 'nullable|in:0,1',
        ], [
            'name.unique' => 'El nombre de la unidad ya está en uso.',
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El campo nombre no debe exceder los 250 caracteres.',
            'description.required' => 'El campo descripción es obligatorio.',
            'description.max' => 'El campo descripción no debe exceder los 300 caracteres.',
            'state.required' => 'El campo estado es obligatorio.',
            'state.in' => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        $unit = Unit::findOrFail($id);

        $unit->update($validated);
        $unit->refresh();

        return response()->json([
            'message' => 'Unidad actualizada exitosamente',
            'unit' => [
                "id" => $unit->id,
                "name" => $unit->name,
                "description" => $unit->description,
                "state" => $unit->state,
                "created_at" => $unit->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return response()->json([
            'message' => 'Unidad eliminada exitosamente',
            'unit' => [
                "id" => $unit->id,
                "name" => $unit->name,
                "description" => $unit->description,
                "state" => $unit->state,
                "created_at" => $unit->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }
}
