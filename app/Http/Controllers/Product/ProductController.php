<?php

namespace App\Http\Controllers\Product;

use App\Exports\Product\ProductDownloadExcel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Imports\Product\ImportExcelProducts;
use App\Models\Config\Branch;
use App\Models\Config\ProductCategory;
use App\Models\Config\Unit;
use App\Models\Config\UnitConversion;
use App\Models\Config\Warehouse;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{

    public function config()
    {
        return response()->json([
            'branches' => Branch::where('state', 1)->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ];
            }),
            'warehouses' => Warehouse::where('state', 1)->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ];
            }),
            'units' => Unit::where('state', 1)->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ];
            }),
            // 'unit_conversions' => UnitConversion::get()->map(function ($conversion) {
            //     return [
            //         'id' => $conversion->id,
            //         'unit_id' => $conversion->unit_id,
            //         'unit_name' => $conversion->unit ? $conversion->unit->name : null,
            //         'unit_to_id' => $conversion->unit_to_id,
            //         'unit_to_name' => $conversion->unitTo ? $conversion->unitTo->name : null,
            //     ];
            // }),
            'categories' => ProductCategory::where('state', 1)->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ];
            }),
        ]);
    }

    public function download_excel(Request $request)
    {
        try {
            $search = $request->search;
            $category_id = $request->category_id;
            $warehouse_id = $request->warehouse_id;
            // $unit_id = $request->unit_id;
            $branch_id = $request->branch_id;
            $allow_without_stock = $request->allow_without_stock;
            $is_gift = $request->is_gift;
            // Filtrar usandoo scope en el Model
            $products = Product::filterAdvance($search, $category_id, $warehouse_id, $branch_id, $allow_without_stock, $is_gift)
                ->orderBy('id', 'desc')
                ->get();

            return Excel::download(new ProductDownloadExcel($products), 'list_products.xlsx');
        } catch (\Throwable $th) {
            // 🔹 Registrar el error en el log de Laravel
            Log::error('Error al obtener productos: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // 🔹 Retornar respuesta JSON con el error
            return response()->json([
                'message' => 'Ocurrió un error al obtener los productos.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function import_excel(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,csv,xls', // Acepta Excel moderno, antiguo y CSV
                'max:5120' // (Opcional) máximo 5 MB
            ],
        ], [
            'file.required' => 'El archivo es obligatorio.',
            'file.file' => 'Debe subir un archivo válido.',
            'file.mimes' => 'Solo se permiten archivos con formato .xlsx, .xls o .csv.',
            'file.max' => 'El tamaño máximo permitido es de 5MB.',
        ]);

        // crear el archivo de importación $php artisan make:import Product/ImportExcelProducts
        // ✅ Aquí ya tienes el archivo validado:
        $file = $request->file('file');
        $data = Excel::import(new ImportExcelProducts, $file);
        return response()->json([
            "message" => "La importación se realizó con exito"
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->search;
            $page = $request->query("page", 1);
            $per_page = $request->query("per_page", 10);
            $category_id = $request->category_id;
            $warehouse_id = $request->warehouse_id;
            $unit_id = $request->unit_id;
            $branch_id = $request->branch_id;
            $allow_without_stock = $request->allow_without_stock;
            $is_gift = $request->is_gift;
            // Filtrar usandoo scope en el Model
            $products = Product::filterAdvance($search, $category_id, $warehouse_id, $unit_id, $branch_id, $allow_without_stock, $is_gift)
                ->orderBy('id', 'desc')
                ->paginate($per_page, ['*'], 'page', $page);

            return response()->json([
                "products" => ProductCollection::make($products),
                "meta" => [
                    "current_page" => $products->currentPage(),
                    "last_page" => $products->lastPage(),
                    "per_page" => $products->perPage(),
                    "total" => $products->total(),
                ],
            ], 200);
        } catch (\Throwable $th) {
            // 🔹 Registrar el error en el log de Laravel
            Log::error('Error al obtener productos: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // 🔹 Retornar respuesta JSON con el error
            return response()->json([
                'message' => 'Ocurrió un error al obtener los productos.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function s3_image(Request $request)
    {
        $filePath = $request->file('image')->store('uploads', 's3'); // 's3' es el disco configurado en filesystems.php instalando con el comando $composer require aws/aws-sdk-php

        /** @var Filesystem $disk */  // 👈 Tipado explícito para Intelephense
        $disk = Storage::disk('s3');

        // Obtener la URL pública o temporal
        $url = $disk->url($filePath);

        $optimizedUrl = str_replace(
            env('AWS_BUCKET'),
            env('AWS_BUCKET_OPTIMIZED'),
            $url
        );

        return response()->json([
            'message' => 'Imagen recibida',
            'optimizedUrl' => $optimizedUrl,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        // ✅ Validar datos
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['title'], '-');

        DB::beginTransaction();

        try {
            if ($validated['image']) {
                $file = $request->file('image');

                try {
                    // Subir al bucket original
                    $filePath = $file->store('uploads/products', 's3');

                    if (!$filePath) {
                        throw new \Exception("La imagen no se pudo subir al bucket S3");
                    }
                    // Se sube al bucket original s3 pero con un lambda en AWS se transfiere al bucket optimizado optimized_s3

                    /** @var Filesystem $disk */  // 👈 Tipado explícito para Intelephens
                    // $diskOptimized = Storage::disk('optimized_s3');
                    // $url = $diskOptimized->url($filePath);

                    // Asignamos al validated para guardar en la BD
                    $validated['image'] = $filePath;
                } catch (\Exception $e) {
                    throw new \Exception("Error al subir la imagen al almacenamiento: " . $e->getMessage());
                }
            }

            // 🟢 Crear el producto principal
            $product = Product::create($validated);

            // Crear las conversiones de unidad (si existen) para este producto
            // if (!empty($validated['product_unit_conversions'])) {
            //     foreach ($validated['product_unit_conversions'] as $conversion) {
            //         $product->unitConversions()->create([
            //             'unit_to_id' => $conversion['unit_to_id'],
            //             'conversion_factor' => $conversion['conversion_factor'],
            //         ]);
            //     }
            // }

            // 🔵 Crear existencias por almacén (si existen)
            if (!empty($validated['product_warehouses'])) {
                foreach ($validated['product_warehouses'] as $warehouse) {
                    $product->warehouses()->create([
                        'warehouse_id' => $warehouse['warehouse_id'],
                        'unit_id' => $warehouse['unit_id'],
                        'threshold' => $warehouse['threshold'] ?? 0,
                        'stock' => $warehouse['stock'] ?? 0,
                    ]);
                }
            }

            // 🟣 Crear precios por sucursal (si existen)
            if (!empty($validated['product_wallets'])) {
                foreach ($validated['product_wallets'] as $wallet) {
                    $product->wallets()->create([
                        'branch_id' => $wallet['branch_id'],
                        'unit_id' => $wallet['unit_id'],
                        'type_client' => $wallet['type_client'],
                        'price' => $wallet['price'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Producto creado exitosamente',
                'product' => ProductResource::make($product->fresh(['warehouses', 'wallets']))
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al crear producto: ' . $th->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al crear el producto',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json([
            'product' => ProductResource::make($product)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, string $id)
    {
        // ✅ Validación de campos
        $validated = $request->validated();

        $product = Product::findOrFail($id);

        DB::beginTransaction();

        try {
            // Actualizar el slug si cambia el título
            if (isset($validated['title'])) {
                $validated['slug'] = Str::slug($validated['title'], '-');
            }
            // 🔹 Manejar imagen si se envía una nueva
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                try {
                    /** @var \Illuminate\Filesystem\FilesystemAdapter $diskOptimized */
                    $diskOptimized = Storage::disk('optimized_s3');

                    // 🔹 Eliminar imagen anterior del bucket optimizado si existe
                    if ($product->image && $diskOptimized->exists($product->image)) {
                        $diskOptimized->delete($product->image);
                    }

                    // Subir al bucket original
                    $filePath = $file->store('uploads/products', 's3');

                    if (!$filePath) {
                        throw new \Exception("La imagen no se pudo subir al bucket S3");
                    }
                    // Se sube al bucket original s3 pero con un lambda en AWS se transfiere al bucket optimizado optimized_s3

                    /** @var Filesystem $diskOptimized */  // 👈 Tipado explícito para Intelephens
                    // $diskOptimized = Storage::disk('optimized_s3');
                    // $url = $diskOptimized->url($filePath);

                    // Asignamos al validated
                    $validated['image'] = $filePath;
                } catch (\Exception $e) {
                    throw new \Exception("Error al subir la imagen al almacenamiento: " . $e->getMessage());
                }
            }

            // 🟢 Actualizar producto principal
            $product->update($validated);

            // Actualizar unidades de conversion
            // if (isset($validated['product_unit_conversions'])) {
            //     $product->unitConversions()->delete();
            //     foreach ($validated['product_unit_conversions'] as $conversion) {
            //         $product->unitConversions()->create([
            //             'unit_to_id' => $conversion['unit_to_id'],
            //             'conversion_factor' => $conversion['conversion_factor'],
            //         ]);
            //     }
            // }

            // 🔵 Actualizar existencias por almacén
            if (isset($validated['product_warehouses'])) {
                $product->warehouses()->delete();
                foreach ($validated['product_warehouses'] as $warehouse) {
                    $product->warehouses()->create([
                        'warehouse_id' => $warehouse['warehouse_id'],
                        'unit_id' => $warehouse['unit_id'],
                        'threshold' => $warehouse['threshold'] ?? 0,
                        'stock' => $warehouse['stock'] ?? 0,
                    ]);
                }
            }

            // 🟣 Actualizar precios por sucursal
            if (isset($validated['product_wallets'])) {
                $product->wallets()->delete();
                foreach ($validated['product_wallets'] as $wallet) {
                    $product->wallets()->create([
                        'branch_id' => $wallet['branch_id'],
                        'unit_id' => $wallet['unit_id'],
                        'type_client' => $wallet['type_client'],
                        'price' => $wallet['price'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Producto actualizado exitosamente',
                'product' => ProductResource::make($product->fresh(['warehouses', 'wallets']))
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ocurrió un error al actualizar el producto',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([
            'message' => 'Sucursal eliminada exitosamente',
            'product' => ProductResource::make($product)
        ], 200);
    }
}
