<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique(); // Nombre del almacén
            $table->string('address', 500); // Dirección
            $table->string('phone', 20)->nullable(); // Teléfono           
            $table->foreignId('branch_id')                  // Relación con sucursal
                ->constrained('branches')               // referencia a tabla branches
                ->onDelete('cascade');                  // si se elimina la sucursal, se eliminan los almacenes
            // 0 = inactivo, 1 = activo, puedes agregar más estados después
            $table->tinyInteger('state')->default(1)->comment('Estado de la sucursal(1 es activo y 2 es inactivo'); // estado de la sucursal
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
