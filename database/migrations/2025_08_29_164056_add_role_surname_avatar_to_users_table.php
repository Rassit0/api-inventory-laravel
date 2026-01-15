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
        Schema::table('users', function (Blueprint $table) {
            // Agregar la columna role_id con relación
            $table->foreignId('role_id')
                ->default(1) // rol por defecto
                ->constrained('roles') // referencia a la tabla roles
                ->onDelete('cascade'); // si borras el rol, se borran los usuarios relacionados (ajusta según tu caso)

            // Otras columnas
            $table->string('surname')->nullable()->after('name');
            $table->string('avatar')->nullable()->after('surname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar foreign key y columnas
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'surname', 'avatar']);
        });
    }
};
