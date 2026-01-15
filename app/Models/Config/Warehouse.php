<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouses';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'branch_id',
        'name',
        'address',
        'phone',
        'state',
    ];

    /**
     * Relación con Branch
     * Cada almacén pertenece a una sucursal
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
