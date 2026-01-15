<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'full_name',
        'ruc',
        'image',
        'phone',
        'address',
        'state',
    ];
}
