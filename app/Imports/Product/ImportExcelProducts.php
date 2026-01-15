<?php

namespace App\Imports\Product;

use App\Models\Config\Branch;
use App\Models\Config\ProductCategory;
use App\Models\Config\Unit;
use App\Models\Config\Warehouse;
use App\Models\Product\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ImportExcelProducts implements ToCollection, WithHeadingRow, WithValidation //,ToModel
{
    use Importable, SkipsErrors;

    // public function model(array $row)
    // {
    //     try {
    //         DB::beginTransaction();
    //         // 1. Crear el producto
    //         $category = ProductCategory::whereRaw('LOWER(name) = ?', [strtolower($row['categoria'])])->first();
    //         if (!$category) throw new \Exception("La categoría '{$row['categoria']}' no existe.");

    //         $unit = Unit::whereRaw('LOWER(name) = ?', [strtolower($row['unidad_base'])])->first();
    //         if (!$unit) throw new \Exception("La unidad '{$row['unidad_base']}' no existe.");

    //         $warehouse = Warehouse::whereRaw('LOWER(name) = ?', [strtolower($row['almacen'])])->first();
    //         if (!$warehouse) throw new \Exception("El almacén '{$row['sucursal']}' no existe.");

    //         $branch = Branch::whereRaw('LOWER(name) = ?', [strtolower($row['sucursal'])])->first();
    //         if (!$branch) throw new \Exception("La sucursal '{$row['sucursal']}' no existe.");

    //         // 1️⃣ Crear la instancia del producto
    //         $product = new Product([
    //             'title' => $row['nombre_producto'],
    //             'slug' => Str::slug($row['nombre_producto'], '-'),
    //             'image' => $row['imagen'],
    //             'category_id' => $category->id,
    //             'unit_id' => $unit->id,
    //             'sku' => $row['sku'],
    //             'description' => $row['descripcion'],
    //             'is_gift' => $row['es_regalo'] == 'SI' ? true : false,
    //             'allow_without_stock' => $row['disponibilidad'] == 'VENDER SIN STOCK' ? true : false,
    //             // 'stock_status',
    //             'price_general' => $row['precio_general'],
    //             'price_company' => $row['precio_empresa'],
    //             'is_discount' => $row['descuento'] ? true : false,
    //             'max_discount' => $row['descuento'] ?? 0,
    //             'state' => $row['estado'] == 'ACTIVO' ? true : false,
    //             'warranty_day' => $row['dias_garantia'],
    //             'is_taxable' => $row['tipo_impuesto'] == 'SUJETO A IMPUESTO' ? true : false,
    //             'iva' => $row['importe_iva'],
    //         ]);

    //         // 2️⃣ Guardar el producto en la base de datos
    //         $product->save();

    //         // 3️⃣ Crear la relación con warehouses (almacenes)
    //         $product->warehouses()->create([
    //             'warehouse_id' => $warehouse->id, // ⚠️ usar el ID, no el nombre
    //             'unit_id' => $unit->id,
    //             'stock' => $row['stock'],
    //         ]);

    //         // 4️⃣ Crear la relación de precios por sucursal (wallets)
    //         $product->wallets()->create([
    //             'branch_id' => $branch->id, // ⚠️ usar el ID, no el nombre
    //             'unit_id' => $unit->id,
    //             'type_client' => $row['type_client'] == 'CLIENTE FINAL' ? 'general' : 'company',
    //             'price' => $row['price'],
    //         ]);

    //         DB::commit();

    //         return $product->fresh(['warehouses', 'wallets']);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         // solo logueamos, no retornamos JsonResponse
    //         Log::error('Error al crear producto: ' . $th->getMessage());
    //         throw $th; // esto lo captura SkipsErrors
    //     }
    // }
    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // Validaciones
                $category = ProductCategory::whereRaw('LOWER(name) = ?', [strtolower($row['categoria'])])->first();
                if (!$category) throw new \Exception("Fila " . ($index + 2) . ": La categoría '{$row['categoria']}' no existe.");

                $unit = Unit::whereRaw('LOWER(name) = ?', [strtolower($row['unidad_base'])])->first();
                if (!$unit) throw new \Exception("Fila " . ($index + 2) . ": La unidad '{$row['unidad_base']}' no existe.");

                $warehouse = Warehouse::whereRaw('LOWER(name) = ?', [strtolower($row['almacen'])])->first();
                if (!$warehouse) throw new \Exception("Fila " . ($index + 2) . ": El almacén '{$row['almacen']}' no existe.");

                $branch = Branch::whereRaw('LOWER(name) = ?', [strtolower($row['sucursal'])])->first();
                if (!$branch) throw new \Exception("Fila " . ($index + 2) . ": La sucursal '{$row['sucursal']}' no existe.");

                // Crear el producto
                $product = Product::create([
                    'title' => $row['nombre_producto'],
                    'slug' => Str::slug($row['nombre_producto'], '-'),
                    'image' => $row['imagen'],
                    'category_id' => $category->id,
                    // 'unit_id' => $unit->id,
                    'sku' => $row['sku'],
                    'description' => $row['descripcion'],
                    'is_gift' => $row['es_regalo'] == 'SI',
                    'allow_without_stock' => $row['disponibilidad'] == 'VENDER SIN STOCK',
                    'price_general' => $row['precio_general'],
                    'price_company' => $row['precio_empresa'],
                    'is_discount' => !empty($row['descuento']),
                    'max_discount' => $row['descuento'] ?? 0,
                    'state' => $row['estado'] == 'ACTIVO',
                    'warranty_day' => $row['dias_garantia'],
                    'is_taxable' => $row['tipo_impuesto'] == 'SUJETO A IMPUESTO',
                    'iva' => $row['importe_iva'],
                ]);

                $product->warehouses()->create([
                    'warehouse_id' => $warehouse->id,
                    'unit_id' => $unit->id,
                    'stock' => $row['stock'],
                ]);

                $product->wallets()->create([
                    'branch_id' => $branch->id,
                    'unit_id' => $unit->id,
                    'type_client' => $row['tipo_de_cliente'] == 'CLIENTE FINAL' ? 'general' : 'company',
                    'price' => $row['precio'],
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error en importación: " . $th->getMessage());
            throw $th; // Maatwebsite Excel capturará y SkipsErrors también
        }
    }

    public function rules(): array
    {
        return [
            '*.nombre_producto' => ['required', 'unique:products,title'],
            '*.sku' => ['required', 'unique:products,sku'],
            // Insensitive
            '*.categoria' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('categories')
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->exists();

                    if (!$exists) {
                        $fail("La categoría '{$value}' no existe en el sistema.");
                    }
                },
            ],
            '*.imagen' => ['required'],
            '*.precio_general' => ['required'],
            '*.precio_empresa' => ['required'],
            '*.descripcion' => ['required'],
            '*.es_regalo' => ['required', 'in:SI,NO'],
            '*.descuento' => ['nullable'],
            '*.disponibilidad' => ['required', 'in:VENDER SIN STOCK,NO VENDER SIN STOCK'],
            '*.tipo_impuesto' => ['required', 'in:SUJETO A IMPUESTO,LIBRE DE IMPUESTO'],
            '*.importe_iva' => ['required'],
            '*.estado' => ['required', 'in:ACTIVO,INACTIVO'],
            '*.dias_garantia' => ['required'],
            '*.disponibilidad' => ['required'],
            // Unidad base del producto
            '*.unidad_base' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('units')
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->exists();

                    if (!$exists) {
                        $fail("La unidad base '{$value}' no existe en el sistema.");
                    }
                },
            ],
            // Existencia inicial
            '*.almacen' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('warehouses')
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->exists();

                    if (!$exists) {
                        $fail("El almacén '{$value}' no existe en el sistema.");
                    }
                },
            ],
            '*.stock' => ['required'],
            '*.umbral' => ['required'],
            // Precio inicial
            '*.sucursal' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('branches')
                        ->whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->exists();

                    if (!$exists) {
                        $fail("La sucursal '{$value}' no existe en el sistema.");
                    }
                },
            ],
            '*.tipo_de_cliente' => ['required', 'in:CLIENTE FINAL,CLIENTE EMPRESA'],
            '*.precio' => ['required'],
        ];
    }
    public function customValidationMessages(): array
    {
        return [
            '*.nombre_producto.required' => 'El nombre del producto es obligatorio.',
            '*.nombre_producto.unique' => 'El producto ya existe en el sistema.',
            '*.sku.required' => 'El SKU del producto es obligatorio.',
            '*.categoria.required' => 'La categoría es obligatoria.',
            '*.imagen.required' => 'La imagen del producto es obligatoria.',
            '*.precio_general.required' => 'El precio general es obligatorio.',
            '*.precio_empresa.required' => 'El precio para empresas es obligatorio.',
            '*.descripcion.required' => 'La descripción del producto es obligatoria.',
            '*.es_regalo.required' => 'El campo "es regalo" es obligatorio.',
            '*.es_regalo.in' => 'El campo "es regalo" debe ser SI o NO.',
            '*.descuento.nullable' => 'El descuento es opcional.',
            '*.disponibilidad.required' => 'El campo disponibilidad es obligatorio.',
            '*.disponibilidad.in' => 'La disponibilidad debe ser "VENDER SIN STOCK" o "NO VENDER SIN STOCK".',
            '*.tipo_impuesto.required' => 'El tipo de impuesto es obligatorio.',
            '*.tipo_impuesto.in' => 'El tipo de impuesto debe ser "SUJETO A IMPUESTO" o "LIBRE DE IMPUESTO".',
            '*.importe_iva.required' => 'El importe del IVA es obligatorio.',
            '*.estado.required' => 'El estado del producto es obligatorio.',
            '*.estado.in' => 'El estado debe ser ACTIVO o INACTIVO.',
            '*.dias_garantia.required' => 'Los días de garantía son obligatorios.',
            '*.unidad_base.required' => 'La unidad base es obligatoria.',
            '*.almacen.required' => 'El almacén es obligatorio.',
            '*.stock.required' => 'El stock es obligatorio.',
            '*.umbral.required' => 'El umbral es obligatorio.',
            '*.sucursal.required' => 'La sucursal es obligatoria.',
            '*.tipo_de_cliente.required' => 'El tipo de cliente es obligatorio.',
            '*.tipo_de_cliente.in' => 'El tipo de cliente debe ser "CLIENTE FINAL" o "CLIENTE EMPRESA".',
            '*.precio.required' => 'El precio es obligatorio.',
        ];
    }
}
