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
                'Aprobado',
                'En Proceso',
                'Denegado'
            ]);
            $table->unsignedBigInteger('practice_id');
            $table->foreign('practice_id')->references('id')->on('practices')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
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
