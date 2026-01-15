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
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('type_document', 50)->nullable()->after('phone');
            $table->string('n_document', 50)->nullable()->after('type_document');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('n_document');

            // Soft deletes
            $table->softDeletes()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'type_document', 'n_document', 'gender']);
            $table->dropSoftDeletes();
        });
    }
};
