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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->enum('document_type', [
                'carta_presentacion',
                'carta_aceptacion',
                'plan_practicas',
                'evaluacion_practicas',
                'informe_practicas',
                'monitoreo_evaluacion'
            ]);

            $table->string('document_path');       // ruta en Storage, no URL
            $table->string('original_name');       // nombre de archivo original
            $table->enum('document_status', [
                'aprobado',
                'en_proceso',
                'denegado'
            ])->default('en_proceso');

            $table->foreignId('practice_id')->constrained()->cascadeOnDelete();

            // quién subió (útil para auditoría)
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['practice_id', 'document_type', 'document_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
