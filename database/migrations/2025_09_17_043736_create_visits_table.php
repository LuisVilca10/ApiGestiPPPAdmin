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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            // columna user_id (estudiante) y FK a users
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // quien visitó (usuario)
            $table->foreignId('visited_by')->constrained('users')->cascadeOnDelete();

            // práctica opcional
            $table->foreignId('practice_id')->nullable()->constrained()->nullOnDelete();

            // datos de la visita
            $table->dateTime('visit_date');
            $table->enum('visit_type', ['inicio', 'medio', 'final']);
            $table->text('visit_notes')->nullable();
            $table->decimal('visit_result', 5, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // índices útiles
            $table->index('visit_date');
            $table->index('user_id');
            $table->index('visited_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
