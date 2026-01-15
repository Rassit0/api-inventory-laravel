<?php

namespace App\Models\Product;

use App\Models\Config\ProductCategory;
use App\Models\Product\ProductUnitConversion;
use App\Models\Config\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'title',
        'slug',
        'image',
        'category_id',
        // 'unit_id',
        'sku',
        'description',
        'is_gift',
        'allow_without_stock',
        'stock_status',
        'price_general',
        'price_company',
        'is_discount',
        'max_discount',
        'state',
        'warranty_day',
        'is_taxable',
        'iva',
    ];

    public function warehouses()
    {
        return $this->hasMany(ProductWarehouse::class, 'product_id')->orderByDesc('id');
    }

    public function wallets()
    {
        return $this->hasMany(ProductWallet::class, 'product_id')->orderByDesc('id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // public function unitConversions()
    // {
    //     return $this->hasMany(ProductUnitConversion::class, 'product_id');
    // }

    public function scopeFilterAdvance($query, $search, $category_id, $warehouse_id, $unit_id, $branch_id, $allow_without_stock, $is_gift)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'ILIKE', "%$search%")
                    ->orWhere('sku', 'ILIKE', "%$search%");
            });
        }
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
        if ($warehouse_id) {
            $query->whereHas('warehouses', function ($warehouse) use ($warehouse_id) {
                $warehouse->where('warehouse_id', $warehouse_id);
            });
        }
        // if ($unit_id) {
        //     $query->where('unit_id', $unit_id)
        //         // ✅ O con alguna de las unidades de conversión asociadas
        //         ->orWhereHas('unitConversions', function ($conversion) use ($unit_id) {
        //             $conversion->where('unit_to_id', $unit_id);
        //         });;
        // }
        // if ($unit_id) {
        //     $query->whereHas('unitConversions', function ($conversion) use ($unit_id) {
        //         $conversion->where('unit_to_id', $unit_id);
        //     });;
        // }
        if ($branch_id) {
            $query->whereHas('wallets', function ($wallet) use ($branch_id) {
                $wallet->where('branch_id', $branch_id);
            });
        }
        if ($allow_without_stock) {
            $query->where('allow_without_stock', $allow_without_stock);
        }
        if ($is_gift) {
            $query->where('is_gift', $is_gift);
        }
        return $query;
    }
}
