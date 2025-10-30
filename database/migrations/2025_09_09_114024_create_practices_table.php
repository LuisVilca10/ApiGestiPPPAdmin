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

            // Empresa
            $table->string('company_name');
            $table->string('ruc', 11)->unique(); // RUC único (11 dígitos)

            // Representante legal
            $table->string('representative_first_name')->nullable();
            $table->string('representative_last_name')->nullable();
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
            ])->nullable();
            $table->string('represent_phone', 20)->nullable();
            $table->string('represent_email')->nullable();

            // Actividad y detalles de la práctica
            $table->text('student_activity')->nullable(); // descripción larga
            $table->unsignedInteger('hours_practice')->default(0); // horas (sin signo)

            // Supervisor interno en la empresa (si aplica)
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_phone', 20)->nullable();
            $table->string('supervisor_email')->nullable();

            // Ubicación / contacto de la empresa
            $table->string('company_address')->nullable();

            // Fechas de la práctica
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Estado de la práctica (workflow)
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])
                ->default('pending');

            // Documentos / evidencias (ruta a archivos)
            $table->string('agreement_path')->nullable(); // convenio / documento firmado
            $table->string('report_path')->nullable(); // informe final (opcional)
            $table->unsignedBigInteger('user_id');      // <-- añadir esto si falta

            // Relación con usuario (estudiante)
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Auditoría
            $table->timestamps();
            $table->softDeletes();

            // Índices útiles
            $table->index('user_id');
            $table->index('company_name'); // opcional: búsquedas por nombre de empresa

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
