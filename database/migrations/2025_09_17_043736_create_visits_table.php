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

            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visited_by')->constrained('users')->cascadeOnDelete(); // quién visita
            $table->foreignId('practice_id')->nullable()->constrained()->nullOnDelete(); // si aplica

            $table->dateTime('visit_date');
            $table->enum('visit_type', ['inicio', 'medio', 'final']);
            $table->text('visit_notes')->nullable();

            // Si es calificación, puede ser entero/decimal; hazlo nullable
            $table->decimal('visit_result', 5, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id', 'visit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
