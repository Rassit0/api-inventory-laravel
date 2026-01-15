<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public static $wrap = 'product';
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        /** @var Filesystem $diskOptimized */  // 👈 Tipado explícito para Intelephens
        $diskOptimized = Storage::disk('optimized_s3');
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image' => $diskOptimized->exists($this->image) ? $diskOptimized->url($this->image) : $this->image,
            'category_id' => $this->category_id,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            // 'unit_id' => $this->unit_id,
            // 'unit' => [
            //     'id' => $this->unit->id,
            //     'name' => $this->unit->name,
            // ],
            'sku' => $this->sku,
            'description' => $this->description,
            'is_gift' => $this->is_gift,
            'allow_without_stock' => $this->allow_without_stock,
            'stock_status' => $this->stock_status,
            'price_general' => $this->price_general,
            'price_company' => $this->price_company,
            'is_discount' => $this->is_discount,
            'max_discount' => $this->max_discount,
            'state' => $this->state,
            'warranty_day' => $this->warranty_day,
            'is_taxable' => $this->is_taxable,
            'iva' => $this->iva,
            'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'deleted_at' => $this->deleted_at
            'product_warehouses' => $this->warehouses->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'warehouse_id' => $warehouse->warehouse_id,
                    'warehouse' => [
                        'name' => $warehouse->warehouse->name,
                    ],
                    'unit_id' => $warehouse->unit_id,
                    'unit' => [
                        'name' => $warehouse->unit->name,
                    ],
                    'threshold' => $warehouse->threshold,
                    'stock' => $warehouse->stock,
                ];
            }),
            'product_wallets' => $this->wallets->map(function ($wallet) {
                return [
                    'id' => $wallet->id,
                    'unit_id' => $wallet->unit_id,
                    'unit' => [
                        'name' => $wallet->unit->name,
                    ],
                    'branch_id' => $wallet->branch_id,
                    'branch' => $wallet->branch ? [
                        'name' => $wallet->branch->name,
                    ] : null,
                    'type_client' => $wallet->type_client,
                    'price' => $wallet->price,
                ];
            }),
            // 'product_unit_conversions' => $this->unitConversions->map(function ($wallet) {
            //     return [
            //         'id' => $wallet->id,
            //         'unit_to_id' => $wallet->unit_to_id,
            //         'unit_to' => [
            //             'name' => $wallet->unitTo->name,
            //         ],
            //         'conversion_factor' => $wallet->conversion_factor,
            //     ];
            // }),
        ];
    }
}
