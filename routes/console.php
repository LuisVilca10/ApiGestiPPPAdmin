<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Avanza el ciclo académico de todos los estudiantes cada 6 meses (1 Ene y 1 Jul)
// Ciclo I:  16 marzo  → 3 julio   (inicia el 16 de marzo)
// Ciclo II: 11 agosto → 28 noviembre (inicia el 11 de agosto)
Schedule::command('academic:advance-cycle')->cron('0 0 16 3 *');  // 16 de marzo
Schedule::command('academic:advance-cycle')->cron('0 0 11 8 *');  // 11 de agosto
