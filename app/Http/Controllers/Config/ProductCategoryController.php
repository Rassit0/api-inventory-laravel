<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Config\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function config()
    {
        return response()->json([
            'categories' => ProductCategory::where('state', 1)->get()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
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
        $page = $request->query("page", 1);
        $per_page = $request->query("per_page", 10);
        $categories = ProductCategory::where("name", "ilike", "%{$search}%")->orderBy("id", "desc")
        ->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            // "total_in_page" => $categories->count(),
            "total" => $categories->total(),
            'current_page' => $categories->currentPage(),
            'per_page' => $categories->perPage(),
            'last_page' => $categories->lastPage(),
            "categories" => $categories->map(function ($category) {
                return [
                    "id" => $category->id,
                    "name" => $category->name,
                    "image" => $category->image ? env('APP_URL') . '/storage/' . $category->image : null,
                    "state" => $category->state,
                    "parent_id"  => $category->parent_id,
                    "parent" => $category->parent ? [
                        "id" =>  $category->parent->id,
                        "name" =>  $category->parent->name,
                    ] : null,
                    "created_at" => $category->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
                ];
            }),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validación de campos
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:categories,name',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'state'   => 'nullable|in:0,1',
            'parent_id' => 'nullable|exists:categories,id',
        ], [
            'name.unique' => 'El nombre de la categoría ya está en uso.',
            'name.required' => 'El campo nombre es obligatorio.',
            'image.mimes' => 'La imagen debe ser un archivo de tipo: jpg, jpeg, png.',
            'image.max' => 'La imagen no debe ser mayor de 2MB.',
            'state.required' => 'El campo estado es obligatorio.',
            'parent_id.exists' => 'La categoría padre seleccionada no es válida.',
            'state.in' => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        // Crear la categoría
        $category = ProductCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            // Guardar imagen si existe y obtener la ruta
            'image' => $request->hasFile('image') ? $request->file('image')->store('categories', 'public') : null,
            'state' => $validated['state'] ?? 1,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);
        $category->refresh();

        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'category' => [
                "id" => $category->id,
                "name" => $category->name,
                "image" => $category->image ? env('APP_URL') . '/storage/' . $category->image : null,
                "state" => $category->state,
                "parent_id"  => $category->parent_id,
                "parent" => $category->parent ? [
                    "id" =>  $category->parent->id,
                    "name" =>  $category->parent->name,
                ] : null,
                "created_at" => $category->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
        // Buscar categoría
        $category = ProductCategory::findOrFail($id);

        // ✅ Validación de campos
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:categories,name,' . $id,
            'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'state'     => 'nullable|in:0,1',
            'parent_id' => 'nullable|exists:categories,id',
        ], [
            'name.unique'         => 'El nombre de la categoría ya está en uso.',
            'name.required'       => 'El campo nombre es obligatorio.',
            'image.mimes'         => 'La imagen debe ser un archivo de tipo: jpg, jpeg, png.',
            'image.max'           => 'La imagen no debe ser mayor de 2MB.',
            'state.required'      => 'El campo estado es obligatorio.',
            'parent_id.exists'    => 'La categoría padre seleccionada no es válida.',
            'state.in'            => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        // ✅ Manejo de imagen
        $imagePath = $category->image; // Mantener la actual si no suben nueva
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            // Subir nueva imagen
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        // ✅ Actualizar categoría
        $category->update([
            'name'      => $validated['name'],
            'slug'      => Str::slug($validated['name']),
            'image'     => $imagePath,
            'state'     => $validated['state'] ?? 1,
            "parent_id"  => $category->parent_id,
            "parent" => $category->parent ? [
                "id" =>  $category->parent->id,
                "name" =>  $category->parent->name,
            ] : null,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Refrescar el modelo
        $category->refresh();

        // ✅ Respuesta
        return response()->json([
            'message' => 'Categoría actualizada exitosamente',
            'category' => [
                "id"         => $category->id,
                "name"       => $category->name,
                "image"      => $category->image ? env('APP_URL') . '/storage/' . $category->image : null,
                "state"      => $category->state,
                "parent_id"  => $category->parent_id,
                "parent" => $category->parent ? [
                    "id" =>  $category->parent->id,
                    "name" =>  $category->parent->name,
                ] : null,
                "created_at" => $category->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        return response()->json([
            'message' => 'Sucursal eliminada exitosamente',
            'category' => [
                "id"         => $category->id,
                "name"       => $category->name,
                "image"      => $category->image ? env('APP_URL') . '/storage/' . $category->image : null,
                "state"      => $category->state,
                "parent_id"  => $category->parent_id,
                "parent" => $category->parent ? [
                    "id" =>  $category->parent->id,
                    "name" =>  $category->parent->name,
                ] : null,
                "created_at" => $category->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }
}
