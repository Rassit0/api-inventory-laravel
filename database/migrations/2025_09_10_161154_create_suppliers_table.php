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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->unique(); // Nombre del proveedor
            $table->string('ruc')->unique(); // RUC / NIT / identificador fiscal
            $table->string('image', 255)->nullable(); // Imagen del provedor
            $table->string('email')->nullable(); // Correo electrónico
            $table->string('phone'); // Teléfono
            $table->string('address')->nullable(); // Dirección
            // 0 = inactivo, 1 = activo, puedes agregar más estados después
            $table->tinyInteger('state')->default(1)->comment('Estado de la categoría (1 es activo y 2 es inactivo)'); // estado del proveedor
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
