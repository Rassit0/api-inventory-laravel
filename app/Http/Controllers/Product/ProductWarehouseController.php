<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductWarehouse;
use Illuminate\Http\Request;

class ProductWarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validación de campos
        $validated = $request->validate([
            'product_id'    => 'required|integer|exists:products,id',
            'warehouse_id'  => 'required|integer|exists:warehouses,id',
            'unit_id'       => 'required|integer|exists:units,id',
            'threshold'     => 'nullable|numeric',
            'stock'         => 'nullable|numeric',
        ], [
            'product_id.required' => 'El campo producto es obligatorio.',
            'product_id.exists'   => 'El producto seleccionado no existe.',
            'warehouse_id.required' => 'El campo almacén es obligatorio.',
            'warehouse_id.exists'   => 'El almacén seleccionado no existe.',
            'unit_id.required' => 'El campo unidad es obligatorio.',
            'unit_id.exists'   => 'La unidad seleccionada no existe.',
            'threshold.numeric' => 'El campo límite debe ser un número.',
            'stock.numeric'     => 'El campo stock debe ser un número.',
        ]);

        // 🔹 Verificar si ya existe un registro con product_id, warehouse_id y unit_id
        $exists = ProductWarehouse::where('product_id', $validated['product_id'])
            ->where('warehouse_id', $validated['warehouse_id'])
            ->where('unit_id', $validated['unit_id'])
            ->first();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe una existencia para este producto con el almacén y unidad seleccionados.'
            ], 422);
        }


        // Crear el stock
        $product_warehouse = ProductWarehouse::create([
            'product_id' => $validated['product_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'unit_id' => $validated['unit_id'],
            'threshold' => $validated['threshold'] ?? 0,
            'stock' => $validated['stock'] ?? 0,
        ]);
        $product_warehouse->refresh();

        return response()->json([
            'message' => 'Existencia creada exitosamente',
            'product_warehouse' => [
                'id' => $product_warehouse->id,
                'warehouse_id' => $product_warehouse->warehouse_id,
                'warehouse' => [
                    'name' => $product_warehouse->warehouse->name,
                ],
                'unit_id' => $product_warehouse->unit_id,
                'unit' => [
                    'name' => $product_warehouse->unit->name,
                ],
                'threshold' => $product_warehouse->threshold,
                'stock' => $product_warehouse->stock,
                "created_at" => $product_warehouse->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
        // ✅ Validación de campos
        $validated = $request->validate([
            'product_id'    => 'required|integer|exists:products,id',
            'warehouse_id'  => 'required|integer|exists:warehouses,id',
            'unit_id'       => 'required|integer|exists:units,id',
            'threshold'     => 'nullable|numeric',
            'stock'         => 'nullable|numeric',
        ], [
            'product_id.required' => 'El campo producto es obligatorio.',
            'product_id.exists'   => 'El producto seleccionado no existe.',
            'warehouse_id.required' => 'El campo almacén es obligatorio.',
            'warehouse_id.exists'   => 'El almacén seleccionado no existe.',
            'unit_id.required' => 'El campo unidad es obligatorio.',
            'unit_id.exists'   => 'La unidad seleccionada no existe.',
            'threshold.numeric' => 'El campo límite debe ser un número.',
            'stock.numeric'     => 'El campo stock debe ser un número.',
        ]);

        // 🔎 Buscar el registro existente
        $productWarehouse = ProductWarehouse::find($id);

        if (!$productWarehouse) {
            return response()->json([
                'message' => 'El registro de stock no existe.',
            ], 404);
        }

        // ⚠️ Verificar si existe otro registro con la misma combinación
        $duplicate = ProductWarehouse::where('product_id', $validated['product_id'])
            ->where('warehouse_id', $validated['warehouse_id'])
            ->where('unit_id', $validated['unit_id'])
            ->where('id', '!=', $id) // Excluir el registro actual
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'Ya existe un registro con el mismo producto, almacén y unidad.',
            ], 422);
        }

        // 🔄 Actualizar el registro con los valores validados
        $productWarehouse->update([
            'product_id'   => $validated['product_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'unit_id'      => $validated['unit_id'],
            'threshold'    => $validated['threshold'] ?? 0,
            'stock'        => $validated['stock'] ?? 0,
        ]);

        // 🔁 Recargar las relaciones para la respuesta
        $productWarehouse->load(['warehouse', 'unit']);

        // ✅ Devolver la respuesta actualizada
        return response()->json([
            'message' => 'Existencia actualizada exitosamente.',
            'product_warehouse' => [
                'id' => $productWarehouse->id,
                'warehouse_id' => $productWarehouse->warehouse_id,
                'warehouse' => [
                    'id' => $productWarehouse->warehouse->id,
                    'name' => $productWarehouse->warehouse->name,
                ],
                'unit_id' => $productWarehouse->unit_id,
                'unit' => [
                    'name' => $productWarehouse->unit->name,
                ],
                'threshold' => $productWarehouse->threshold,
                'stock' => $productWarehouse->stock,
                'updated_at' => $productWarehouse->updated_at->timezone('America/La_Paz')->format('Y/m/d h:i:s A'),
            ]
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productWarehouse = ProductWarehouse::findOrFail($id);
        $productWarehouse->delete();
        return response()->json([
            'message' => 'Existencia eliminada exitosamente',
            'product_warehouse' => [
                'id' => $productWarehouse->id,
                'warehouse_id' => $productWarehouse->warehouse_id,
                'warehouse' => [
                    'id' => $productWarehouse->warehouse->id,
                    'name' => $productWarehouse->warehouse->name,
                ],
                'unit_id' => $productWarehouse->unit_id,
                'unit' => [
                    'name' => $productWarehouse->unit->name,
                ],
                'threshold' => $productWarehouse->threshold,
                'stock' => $productWarehouse->stock,
                'updated_at' => $productWarehouse->updated_at->timezone('America/La_Paz')->format('Y/m/d h:i:s A'),
            ]
        ], 200);
    }
}
