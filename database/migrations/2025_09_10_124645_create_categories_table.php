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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            // Campos principales
            $table->string('name', 255)->unique(); // Nombre de la categoría
            $table->string('slug', 255)->unique(); // Slug de la categoría
            $table->string('image', 255)->nullable(); // Imagen de la categoría
            // 0 = inactivo, 1 = activo, puedes agregar más estados después
            $table->tinyInteger('state')->default(1)->comment('Estado de la categoría (1 es activo y 2 es inactivo)'); // estado de la categoría

            // Para jerarquías (opcional, subcategorías)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
