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
        Schema::create('parent_modules', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('code')->nullable();
            $table->string('subtitle', 100);
            $table->string('type', 100);
            $table->string('icon', 100)->nullable();
            $table->boolean('status');
            $table->integer('moduleOrder');
            $table->string('link', 500);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_modules');
    }
};
