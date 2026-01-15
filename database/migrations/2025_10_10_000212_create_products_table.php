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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 255)->unique(); // Slug del producto
            $table->string('image')->nullable();

            // Relación con categoría
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete()->comment('Categoría del producto');

            // Relación con unidad base
            // $table->foreignId('unit_id')->constrained()->cascadeOnDelete()->comment('Unidad base del producto');

            $table->string('sku', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_gift')->default(false)->comment('Es un producto de regalo o gratuito?');
            // Control de stock
            $table->boolean('allow_without_stock')->default(false)->comment('Permitir venta sin stock?'); // Permitir venta sin stock
            $table->enum('stock_status', ['available', 'low_stock', 'out_of_stock'])->default('available')->comment('Estado del stock'); // estado del stock

            // Precios base del producto
            $table->decimal('price_general', 10, 2);
            $table->decimal('price_company', 10, 2);
            $table->boolean('is_discount')->default(false);
            $table->decimal('max_discount', 5, 2)->default(0);

            $table->boolean('state')->default(true);
            $table->integer('warranty_day')->nullable()->comment('Días de garantía');
            // Impuestos (IVA Bolivia 13%)
            $table->boolean('is_taxable')->default(true)->comment('Es un producto con impuestos?');
            $table->decimal('iva', 5, 2)->default(13.00)->comment("IVA Bolivia por defecto, la cantidad es en porcentaje"); // 🇧🇴 IVA Bolivia por defecto
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
