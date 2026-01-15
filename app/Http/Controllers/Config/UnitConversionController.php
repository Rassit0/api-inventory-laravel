<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Config\Unit;
use App\Models\Config\UnitConversion;
use Illuminate\Http\Request;

class UnitConversionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el ID de la unidad desde el query o la ruta
        $unitId = $request->get('unit_id');

        if (!$unitId) {
            return response()->json([
                'message' => 'El parámetro unit_id es requerido.'
            ], 400);
        }

        // Verificar que la unidad exista
        $unit = Unit::find($unitId);

        if (!$unit) {
            return response()->json([
                'message' => 'Unidad no encontrada.'
            ], 404);
        }

        // Obtener todas las conversiones donde participe esa unidad
        $conversions = UnitConversion::with(['unit', 'unitTo'])
            ->where('unit_id', $unitId)
            // ->orWhere('unit_to_id', $unitId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'unit_conversions' => $conversions->map(function ($conversion) {
                return [
                    'id' => $conversion->id,
                    'unit' => [
                        'id' => $conversion->unit->id,
                        'name' => $conversion->unit->name,
                    ],
                    'unit_to' => [
                        'id' => $conversion->unitTo->id,
                        'name' => $conversion->unitTo->name,
                    ],
                    'created_at' => $conversion->created_at->timezone('America/La_Paz')->format('Y/m/d h:i:s A'),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id|different:unit_to_id',
            'unit_to_id' => 'required|exists:units,id',
        ]);

        // Verificar que no exista ya una conversión igual
        $exists = UnitConversion::where('unit_id', $validated['unit_id'])
            ->where('unit_to_id', $validated['unit_to_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'La conversión entre esas unidades ya existe.'
            ], 409);
        }

        // Crear la conversión
        $conversion = UnitConversion::create($validated);

        // Cargar las relaciones
        $conversion->load(['unit', 'unitTo']);

        return response()->json([
            'message' => 'Conversión creada correctamente.',
            'unit_conversion' => [
                'id' => $conversion->id,
                'unit' => [
                    'id' => $conversion->unit->id,
                    'name' => $conversion->unit->name,
                ],
                'unit_to' => [
                    'id' => $conversion->unitTo->id,
                    'name' => $conversion->unitTo->name,
                ],
                'created_at' => $conversion->created_at->timezone('America/La_Paz')->format('Y/m/d h:i:s A'),
            ],
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $conversion = UnitConversion::find($id);

        if (!$conversion) {
            return response()->json([
                'message' => 'La conversión no existe.'
            ], 404);
        }

        $conversion->delete();

        return response()->json([
            'message' => 'Conversión eliminada correctamente.',
            'unit_conversion' => [
                'id' => $conversion->id,
                'unit' => [
                    'id' => $conversion->unit->id,
                    'name' => $conversion->unit->name,
                ],
                'unit_to' => [
                    'id' => $conversion->unitTo->id,
                    'name' => $conversion->unitTo->name,
                ],
                'created_at' => $conversion->created_at->timezone('America/La_Paz')->format('Y/m/d h:i:s A'),
            ],
        ], 200);
    }
}
