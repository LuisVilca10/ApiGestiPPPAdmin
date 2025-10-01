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
        Schema::create('practices', function (Blueprint $table) {
            $table->id();

            // Relación: esta práctica es del estudiante (no del user)
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();

            // Empresa
            $table->string('company_name');              // antes name_empresa
            $table->string('ruc', 11);                  // Perú: 11 dígitos, guárdalo como string
            // Representante
            $table->string('represent_first_name');
            $table->string('represent_last_name');
            $table->enum('represent_title', [
                'Dr',
                'Lic',
                'Ing',
                'Mgs',
                'Arq',
                'Abog',
                'Psic',
                'Enf',
                'PhD',
                'Tec',
                'MBA',
                'Otros'
            ]);
            $table->string('represent_phone', 20);      // permite + prefijos

            // Info de la práctica
            $table->string('student_activity');         // antes activity_student
            $table->unsignedSmallInteger('required_hours'); // antes hourse_practice

            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_profile_id']);
            $table->index(['ruc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practices');
    }
};
