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
            $table->date('fecha_nacimiento')->nullable()->after('specialty');
            $table->string('sexo')->nullable()->after('fecha_nacimiento');
            $table->string('telefono')->nullable()->after('sexo');
            $table->decimal('peso', 6, 2)->nullable()->after('telefono');
            $table->decimal('altura', 6, 2)->nullable()->after('peso');
            $table->string('direccion')->nullable()->after('altura');
            $table->string('ciudad')->nullable()->after('direccion');
            $table->string('contacto_emergencia')->nullable()->after('ciudad');
            $table->string('telefono_emergencia')->nullable()->after('contacto_emergencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_nacimiento',
                'sexo',
                'telefono',
                'peso',
                'altura',
                'direccion',
                'ciudad',
                'contacto_emergencia',
                'telefono_emergencia',
            ]);
        });
    }
};
