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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            // Claves foráneas
            $table->foreignId('user_id')->constrained();
            $table->foreignId('category_id')->constrained();

            // Datos
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('Pendiente');
            $table->string('citizen_name')->nullable();
            $table->ipAddress('ip_address')->nullable();

            //Fechas y deletes
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes(); // Crea el campo deleted_at automáticamente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
