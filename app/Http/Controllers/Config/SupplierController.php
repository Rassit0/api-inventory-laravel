<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Config\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query("search");
        $suppliers = Supplier::where(function ($query) use ($search) {
            $query->where('full_name', 'ILIKE', "%$search%")
                ->orWhere('ruc', 'ILIKE', "%$search%")
                ->orWhere('email', 'ILIKE', "%$search%")
                ->orWhere('phone', 'ILIKE', "%$search%");
        })
            ->orderBy('id', 'desc')
            ->get();
        return response()->json([
            "suppliers" => $suppliers->map(function ($supplier) {
                return [
                    "id" => $supplier->id,
                    "full_name" => $supplier->full_name,
                    "ruc" => $supplier->ruc,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    "state" => $supplier->state,
                    "image" => $supplier->image ? env('APP_URL') . '/storage/' . $supplier->image : null,
                    "created_at" => $supplier->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
            'full_name' => 'required|string|max:255|unique:suppliers,full_name',
            'ruc'       => 'nullable|string|max:20|unique:suppliers,ruc',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'email'     => 'nullable|email|max:255',
            'phone'     => 'required|string|max:50',
            'address'   => 'nullable|string|max:500',
            'state'     => 'nullable|in:0,1',
        ], [
            'full_name.required' => 'El nombre del proveedor es obligatorio.',
            'full_name.unique'   => 'El nombre del proveedor ya está en uso.',
            'ruc.unique'         => 'El RUC/NIT ya está registrado.',
            'image.mimes'        => 'La imagen debe ser un archivo de tipo: jpg, jpeg, png.',
            'image.max'          => 'La imagen no debe ser mayor a 2MB.',
            'email.email'        => 'El correo electrónico no es válido.',
            'phone.required'     => 'El teléfono es obligatorio.',
            'state.in'           => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        // Crear la categoría
        $supplier = Supplier::create([
            'full_name' => $validated['full_name'],
            'ruc' => $validated['ruc'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            // Guardar imagen si existe y obtener la ruta
            'image' => $request->hasFile('image') ? $request->file('image')->store('suppliers', 'public') : null,
            'state' => $validated['state'] ?? 1,
        ]);
        $supplier->refresh();

        return response()->json([
            'message' => 'Proveedor creado exitosamente',
            'supplier' => [
                "id" => $supplier->id,
                "full_name" => $supplier->full_name,
                "ruc" => $supplier->ruc,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                "state" => $supplier->state,
                "image" => $supplier->image ? env('APP_URL') . '/storage/' . $supplier->image : null,
                "created_at" => $supplier->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
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
        // Buscar proveedor
        $supplier = Supplier::findOrFail($id);

        // ✅ Validación de campos
        $validated = $request->validate([
            'full_name' => 'required|string|max:255|unique:suppliers,full_name,' . $id,
            'ruc'       => 'nullable|string|max:20|unique:suppliers,ruc,' . $id,
            'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'email'     => 'nullable|email|max:255',
            'phone'     => 'required|string|max:50',
            'address'   => 'nullable|string|max:500',
            'state'     => 'nullable|in:0,1',
        ], [
            'full_name.required' => 'El nombre del proveedor es obligatorio.',
            'full_name.unique'   => 'El nombre del proveedor ya está en uso.',
            'ruc.unique'         => 'El RUC/NIT ya está registrado.',
            'image.mimes'        => 'La imagen debe ser un archivo de tipo: jpg, jpeg, png.',
            'image.max'          => 'La imagen no debe ser mayor a 2MB.',
            'email.email'        => 'El correo electrónico no es válido.',
            'phone.required'     => 'El teléfono es obligatorio.',
            'state.in'           => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        // ✅ Manejo de imagen
        $imagePath = $supplier->image; // Mantener la actual si no suben nueva
        if ($request->hasFile('image')) {
            if ($supplier->image) {
                Storage::disk('public')->delete($supplier->image);
            }

            // Subir nueva imagen
            $imagePath = $request->file('image')->store('suppliers', 'public');
        }

        // Crear la categoría
        $supplier->update([
            'full_name' => $validated['full_name'],
            'ruc' => $validated['ruc'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            // Guardar imagen si existe y obtener la ruta
            'image' =>  $imagePath,
            'state' => $validated['state'] ?? 1,
        ]);
        $supplier->refresh();

        return response()->json([
            'message' => 'Proveedor actualizado exitosamente',
            'supplier' => [
                "id" => $supplier->id,
                "full_name" => $supplier->full_name,
                "ruc" => $supplier->ruc,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                "state" => $supplier->state,
                "image" => $supplier->image ? env('APP_URL') . '/storage/' . $supplier->image : null,
                "created_at" => $supplier->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json([
            'message' => 'Proveedor eliminado exitosamente',
            'supplier' => [
                "id" => $supplier->id,
                "full_name" => $supplier->full_name,
                "ruc" => $supplier->ruc,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                "state" => $supplier->state,
                "image" => $supplier->image ? env('APP_URL') . '/storage/' . $supplier->image : null,
                "created_at" => $supplier->created_at->timezone("America/La_Paz")->format("Y/m/d h:i:s A"),
            ]
        ], 200);
    }
}
