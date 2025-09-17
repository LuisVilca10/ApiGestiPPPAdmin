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
            $table->string('name_empresa');
            $table->string('ruc');
            $table->string('name_represent');
            $table->string('lastname_represent');
            $table->enum('trate_represent', [
                'Dr',       // Doctor
                'Lic',      // Licenciado
                'Ing',      // Ingeniero
                'Mgs',      // Magíster
                'Arq',      // Arquitecto
                'Abog',     // Abogado
                'Psic',     // Psicólogo
                'Enf',      // Enfermero
                'PhD',      // Doctorado (PhD)
                'Tec',      // Técnico
                'MBA',      // Master of Business Administration (Maestría en Administración)
                'Otros'     // Opción para ingresar otro título o tratamiento
            ]);

            $table->string('phone_represent');
            $table->string('activity_student');
            $table->integer('hourse_practice');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
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
