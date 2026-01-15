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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // nombre de la sucursal
            // 0 = inactivo, 1 = activo, puedes agregar más estados después
            $table->tinyInteger('state')->default(1)->comment('Estado de la sucursal(1 es activo y 2 es inactivo'); // estado de la sucursal
            $table->string('address')->nullable(); // dirección opcional
            $table->string('phone')->nullable();   // teléfono opcional
            $table->timestamps();                 // created_at y updated_at
            $table->softDeletes();                // deleted_at para soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
