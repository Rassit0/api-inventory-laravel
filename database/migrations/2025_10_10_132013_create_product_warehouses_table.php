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
        Schema::create('product_warehouses', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            // Control de stock
            $table->decimal('threshold', 10, 2)->default(0)->comment('Nivel mínimo para alerta de stock');
            $table->decimal('stock', 10, 2)->default(0)->comment('Cantidad actual en stock');

            $table->timestamps();
            $table->softDeletes(); // Eliminación lógica

            // Evitar duplicidad del mismo producto en el mismo almacén con la misma unidad
            // $table->unique(['product_id', 'warehouse_id', 'unit_id'], 'product_warehouse_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_warehouses');
    }
};
