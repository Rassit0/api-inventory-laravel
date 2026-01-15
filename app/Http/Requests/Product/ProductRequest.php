<?php

namespace App\Http\Requests\Product;

use App\Models\Config\UnitConversion;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('product_warehouses') && is_string($this->product_warehouses)) {
            $this->merge([
                'product_warehouses' => json_decode($this->product_warehouses, true),
            ]);
        }

        if ($this->has('product_wallets') && is_string($this->product_wallets)) {
            $this->merge([
                'product_wallets' => json_decode($this->product_wallets, true),
            ]);
        }

        // if ($this->has('product_unit_conversions') && is_string($this->product_unit_conversions)) {
        //     $this->merge([
        //         'product_unit_conversions' => json_decode($this->product_unit_conversions, true),
        //     ]);
        // }

        // Convertir campos booleanos que llegan como 1/0 a true/false
        $booleanFields = [
            'is_gift',
            'allow_without_stock',
            'state',
            'is_taxable',
            'is_discount',
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->$field, FILTER_VALIDATE_BOOLEAN)
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('id') ?? null; // Para editar

        return [
            'title' => 'required|string|max:150|unique:products,title' . ($productId ? ",$productId" : ''),
            'category_id' => 'nullable|exists:categories,id',
            // 'unit_id' => 'nullable|exists:units,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'sku' => 'required|string|max:100|unique:products,sku' . ($productId ? ",$productId" : ''),
            'description' => 'required|string',
            'is_gift' => 'boolean',
            'allow_without_stock' => 'boolean',
            'stock_status' => 'nullable|in:available,low_stock,out_of_stock',

            // Precios base del producto
            'price_general' => 'required|numeric|min:0',
            'price_company' => 'required|numeric|min:0',
            'is_discount' => 'boolean',
            'max_discount' => 'nullable|numeric|min:0|max:100',

            // Estado
            'state' => 'boolean',

            // Garantía
            'warranty_day' => 'nullable|integer|min:0',

            // Impuestos
            'is_taxable' => 'boolean',
            'iva' => 'required|numeric|min:0|max:100',

            // Stock por almacén
            'product_warehouses' => 'nullable|array',
            'product_warehouses.*.warehouse_id' => 'required|exists:warehouses,id',
            'product_warehouses.*.unit_id' => 'required|exists:units,id',
            'product_warehouses.*.threshold' => 'nullable|integer|min:0',
            'product_warehouses.*.stock' => 'nullable|integer|min:0',

            // Precios por sucursal
            'product_wallets' => 'nullable|array',
            'product_wallets.*.branch_id' => 'required|exists:branches,id',
            'product_wallets.*.unit_id' => 'required|exists:units,id',
            'product_wallets.*.type_client' => 'required|in:general,company',
            'product_wallets.*.price' => 'required|numeric|min:0',

            // Conversiones de unidad de producto, es decir, a qué otras unidades puede convertirse este producto
            // 'product_unit_conversions' => 'nullable|array',
            // 'product_unit_conversions.*.unit_to_id' => 'required|exists:units,id',
            // 'product_unit_conversions.*.conversion_factor' => 'required|numeric|min:0',
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         $unitId = $this->unit_id;
    //         $conversions = $this->product_unit_conversions ?? [];

    //         // 1️⃣ Validación de conversiones seleccionadas
    //         if ($unitId && count($conversions)) {
    //             // Traer todas las conversiones válidas desde unit_conversions
    //             $validConversions = UnitConversion::where('unit_id', $unitId)
    //                 ->pluck('unit_to_id')
    //                 ->toArray();

    //             foreach ($conversions as $conversion) {
    //                 $unitToId = $conversion['unit_to_id'] ?? null;

    //                 if ($unitToId && !in_array($unitToId, $validConversions)) {
    //                     $validator->errors()->add(
    //                         'product_unit_conversions',
    //                         "La unidad seleccionada (ID: {$unitToId}) no está permitida para la unidad base seleccionada."
    //                     );
    //                 }
    //             }
    //         }

    //         // 2️⃣ Validación de product_warehouses
    //         $allowedUnits = $conversions ? array_merge([$unitId], array_column($conversions, 'unit_to_id')) : [$unitId];

    //         if ($this->product_warehouses && is_array($this->product_warehouses)) {
    //             foreach ($this->product_warehouses as $index => $warehouse) {
    //                 $wUnitId = $warehouse['unit_id'] ?? null;
    //                 if ($wUnitId && !in_array($wUnitId, $allowedUnits)) {
    //                     $validator->errors()->add(
    //                         "product_warehouses.{$index}.unit_id",
    //                         "La unidad del almacén no está permitida para la unidad base seleccionada."
    //                     );
    //                 }
    //             }
    //         }

    //         // 3️⃣ Validación de product_wallets
    //         if ($this->product_wallets && is_array($this->product_wallets)) {
    //             foreach ($this->product_wallets as $index => $wallet) {
    //                 $wUnitId = $wallet['unit_id'] ?? null;
    //                 if ($wUnitId && !in_array($wUnitId, $allowedUnits)) {
    //                     $validator->errors()->add(
    //                         "product_wallets.{$index}.unit_id",
    //                         "La unidad del precio por sucursal no está permitida para la unidad base seleccionada."
    //                     );
    //                 }
    //             }
    //         }
    //     });
    // }


    /**
     * Mensajes de error personalizados en español.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El nombre del producto es obligatorio.',
            'title.max' => 'El nombre no puede exceder los 150 caracteres.',
            'title.unique' => 'El nombre ya está en uso.',
            'description.required' => 'La descripción es obligatoria.',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            // 'unit_id.required' => 'La unidad es obligatoria.',
            // 'unit_id.exists' => 'La unidad seleccionada no existe.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser jpg, jpeg, png, gif o webp.',
            'image.max' => 'La imagen no puede pesar más de 2MB.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'El SKU ya está en uso.',
            'sku.max' => 'El SKU no puede exceder los 100 caracteres.',
            'stock_status.required' => 'El estado del stock es obligatorio.',
            'stock_status.in' => 'El estado del stock debe ser: available, low_stock o out_of_stock.',
            'price_general.required' => 'El precio general es obligatorio.',
            'price_general.numeric' => 'El precio general debe ser un número.',
            'price_general.min' => 'El precio general no puede ser menor a 0.',
            'price_company.required' => 'El precio para empresas es obligatorio.',
            'price_company.numeric' => 'El precio para empresas debe ser un número.',
            'price_company.min' => 'El precio para empresas no puede ser menor a 0.',
            'max_discount.numeric' => 'El descuento máximo debe ser un número.',
            'is_discount.boolean' => 'El campo de descuento debe ser verdadero o falso.',
            'max_discount.min' => 'El descuento máximo no puede ser menor a 0%.',
            'max_discount.max' => 'El descuento máximo no puede ser mayor a 100%.',
            'warranty_day.integer' => 'Los días de garantía deben ser un número entero.',
            'warranty_day.min' => 'Los días de garantía no pueden ser negativos.',
            'iva.required' => 'El IVA es obligatorio.',
            'iva.numeric' => 'El IVA debe ser un número.',
            'iva.min' => 'El IVA no puede ser menor a 0%.',
            'iva.max' => 'El IVA no puede ser mayor a 100%.',
            // Campos booleanos
            'is_gift.boolean' => 'El campo "es regalo" debe ser verdadero o falso.',
            'allow_without_stock.boolean' => 'El campo "permitir sin stock" debe ser verdadero o falso.',
            'state.boolean' => 'El estado debe ser verdadero o falso.',
            'is_taxable.boolean' => 'El campo "sujeto a impuestos" debe ser verdadero o falso.',

            // Almacenes (product_warehouses)
            'product_warehouses.array' => 'El formato de las existencias por almacén no es válido.',
            'product_warehouses.*.warehouse_id.required' => 'El almacén es obligatorio.',
            'product_warehouses.*.warehouse_id.exists' => 'El almacén seleccionado no existe.',
            'product_warehouses.*.unit_id.required' => 'La unidad es obligatoria.',
            'product_warehouses.*.unit_id.exists' => 'La unidad seleccionada no existe.',
            'product_warehouses.*.threshold.integer' => 'El umbral debe ser un número entero.',
            'product_warehouses.*.threshold.min' => 'El umbral no puede ser negativo.',
            'product_warehouses.*.stock.integer' => 'El stock debe ser un número entero.',
            'product_warehouses.*.stock.min' => 'El stock no puede ser negativo.',

            // Precios por sucursal (product_wallets)
            'product_wallets.array' => 'El formato de los precios por sucursal no es válido.',
            'product_wallets.*.branch_id.required' => 'La sucursal es obligatoria.',
            'product_wallets.*.branch_id.exists' => 'La sucursal seleccionada no existe.',
            'product_wallets.*.unit_id.required' => 'La unidad es obligatoria.',
            'product_wallets.*.unit_id.exists' => 'La unidad seleccionada no existe.',
            'product_wallets.*.type_client.required' => 'El tipo de cliente es obligatorio.',
            'product_wallets.*.type_client.in' => 'El tipo de cliente debe ser "general" o "company".',
            'product_wallets.*.price.required' => 'El precio es obligatorio.',
            'product_wallets.*.price.numeric' => 'El precio debe ser un número.',
            'product_wallets.*.price.min' => 'El precio no puede ser menor a 0.',

            // Conversiones de unidad de producto (product_unit_conversions)
            // 'product_unit_conversions.array' => 'El formato de las conversiones de unidad no es válido.',
            // 'product_unit_conversions.*.unit_to_id.required' => 'La unidad de conversión es obligatoria.',
            // 'product_unit_conversions.*.unit_to_id.exists' => 'La unidad de conversión seleccionada no existe.',
            // 'product_unit_conversions.*.conversion_factor.required' => 'El factor de conversión es obligatorio.',
            // 'product_unit_conversions.*.conversion_factor.numeric' => 'El factor de conversión debe ser un número.',
            // 'product_unit_conversions.*.conversion_factor.min' => 'El factor de conversión no puede ser menor a 0.',
        ];
    }
}
