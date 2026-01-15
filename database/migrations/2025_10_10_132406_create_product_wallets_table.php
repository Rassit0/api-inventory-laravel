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
        Schema::create('product_wallets', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            // 🔸 Nullable: para precios generales sin sucursal específica
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete();

            // Tipo de cliente (por ejemplo: general, empresa, distribuidor)
            $table->enum('type_client', ['general', 'company', 'distributor'])
                ->default('general')
                ->comment('Tipo de cliente al que aplica el precio');

            // Precio configurado para ese cliente, unidad y sucursal
            $table->decimal('price', 10, 2)->default(0);

            $table->timestamps();
            $table->softDeletes(); // 👈 Eliminación lógica

            // Evita duplicados del mismo producto para la misma unidad, sucursal y tipo de cliente
            // $table->unique(['product_id', 'unit_id', 'branch_id', 'type_client'], 'product_wallet_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_wallets');
    }
};
