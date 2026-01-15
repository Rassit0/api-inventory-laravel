<?php

namespace App\Models\Product;

use App\Models\Config\Branch;
use App\Models\Config\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductWallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_wallets';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'product_id',
        'unit_id',
        'branch_id',
        'type_client',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
