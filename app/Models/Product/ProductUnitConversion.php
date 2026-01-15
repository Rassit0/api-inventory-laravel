<?php

namespace App\Models\Product;

use App\Models\Config\Unit;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnitConversion extends Model
{
    use HasFactory;

    protected $table = 'product_unit_conversions';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'product_id',
        'unit_to_id',
        'conversion_factor',
    ];

    // El producto al que pertenece la conversión
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // La unidad a la que se convierte
    public function unitTo()
    {
        return $this->belongsTo(Unit::class, 'unit_to_id');
    }
}
