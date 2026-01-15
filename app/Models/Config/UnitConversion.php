<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    use HasFactory;

    protected $table = 'unit_conversions';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'unit_id',
        'unit_to_id',
    ];

    // La unidad base
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // La unidad a la que se convierte
    public function unitTo()
    {
        return $this->belongsTo(Unit::class, 'unit_to_id');
    }
}
