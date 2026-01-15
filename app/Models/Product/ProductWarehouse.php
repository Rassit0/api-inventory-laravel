<?php

namespace App\Models\Product;

use App\Models\Config\Unit;
use App\Models\Config\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductWarehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_warehouses';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'unit_id',
        'threshold', // limite o stock min
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
