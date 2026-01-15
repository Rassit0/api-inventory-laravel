<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'name',
        'slug',
        'image',
        'state',
        'parent_id',
    ];

    /**
     * Relación con la categoría padre (si existe)
     */
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }
}
