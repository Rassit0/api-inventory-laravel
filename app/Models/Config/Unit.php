<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'units';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'description',
        'state',
    ];

    public function conversions()
    {
        return $this->hasMany(UnitConversion::class, 'unit_id');
    }
}
