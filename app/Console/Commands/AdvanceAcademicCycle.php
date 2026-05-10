<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AdvanceAcademicCycle extends Command
{
    protected $signature   = 'academic:advance-cycle';
    protected $description = 'Avanza el ciclo académico de todos los estudiantes (I→X). Se ejecuta el 16 de marzo y el 11 de agosto.';

    const CYCLES = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

    public function handle(): void
    {
        $students = User::whereHas('roles', fn($q) => $q->where('name', 'Estudiante'))->get();

        $advanced = 0;
        $skipped  = 0;

        foreach ($students as $student) {
            $currentIndex = array_search($student->getRawOriginal('academic_cycle'), self::CYCLES);

            if ($currentIndex === false || $currentIndex >= count(self::CYCLES) - 1) {
                $skipped++;
                continue;
            }

            $student->update(['academic_cycle' => self::CYCLES[$currentIndex + 1]]);
            $advanced++;
        }

        $this->info("Ciclo avanzado: {$advanced} estudiantes.");
        if ($skipped > 0) {
            $this->warn("Omitidos (ya en ciclo X o ciclo inválido): {$skipped} estudiantes.");
        }
    }
}
