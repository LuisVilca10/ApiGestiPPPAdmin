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
            $table->string('document_type');
            $table->string('document_name');
            $table->string('document_path');
            $table->enum('document_status', [
                'En Proceso',
                'Aprobado',
                'Rechazado'
            ]);
            $table->unsignedBigInteger('practice_id');
            $table->foreign('practice_id')->references('id')->on('practices')->onDelete('cascade');
            $table->unsignedBigInteger('visit_id')->nullable();
            $table->foreign('visit_id')->references('id')->on('visits')->onDelete('set null');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['practice_id', 'document_type'],   'idx_documents_practice_type');
            $table->index(['practice_id', 'document_status'], 'idx_documents_practice_status');
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
