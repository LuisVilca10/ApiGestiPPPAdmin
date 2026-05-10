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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'academic_cycle')) {
                $table->string('academic_cycle')->default('I')->after('code');
            }
            if (!Schema::hasColumn('users', 'hours_of_practice')) {
                $table->unsignedInteger('hours_of_practice')->default(0)->after('academic_cycle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('academic_cycle');
            $table->dropColumnIfExists('hours_of_practice');
        });
    }
};
