<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{
    /** @use HasFactory<\Database\Factories\PracticeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'activity_student',
        'hourse_practice',
        'status',
        'rejection_reason',
        'user_id',
        'docente_id',
    ];

    protected $appends = ['periodo'];
    /*
     * Cobertura completa del año (ningún mes queda sin periodo):
     *
     *  YYYY-I       : 16 mar → 3  jul   (Semestre I)
     *  YYYY-VAC-I   :  4 jul → 10 ago   (Vacaciones inter-semestres)
     *  YYYY-II      : 11 ago → 28 nov   (Semestre II)
     *  YYYY-VAC-II  : 29 nov → 15 mar del año siguiente (Vacaciones fin/inicio de año)
     */

    // Accessor: calcula el periodo (ej. "2026-I") a partir de created_at
    protected function periodo(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->created_at ? self::calcularPeriodo($this->created_at) : null
        );
    }

    // Dado un Carbon, retorna el label del periodo correspondiente
    public static function calcularPeriodo(Carbon $date): string
    {
        $year = $date->year;

        // Semestre I: 16 marzo → 3 julio
        if ($date->between(
            Carbon::createFromDate($year, 3, 16)->startOfDay(),
            Carbon::createFromDate($year, 7, 3)->endOfDay()
        )) return "{$year}-I";

        // Vacaciones I: 4 julio → 10 agosto
        if ($date->between(
            Carbon::createFromDate($year, 7, 4)->startOfDay(),
            Carbon::createFromDate($year, 8, 10)->endOfDay()
        )) return "{$year}-VAC-I";

        // Semestre II: 11 agosto → 28 noviembre
        if ($date->between(
            Carbon::createFromDate($year, 8, 11)->startOfDay(),
            Carbon::createFromDate($year, 11, 28)->endOfDay()
        )) return "{$year}-II";

        // Vacaciones II-A: 29 noviembre → 31 diciembre (pertenece al año actual)
        if ($date->between(
            Carbon::createFromDate($year, 11, 29)->startOfDay(),
            Carbon::createFromDate($year, 12, 31)->endOfDay()
        )) return "{$year}-VAC-II";

        // Vacaciones II-B: 1 enero → 15 marzo (pertenece al año anterior)
        if ($date->between(
            Carbon::createFromDate($year, 1, 1)->startOfDay(),
            Carbon::createFromDate($year, 3, 15)->endOfDay()
        )) return ($year - 1) . '-VAC-II';

        return "{$year}-?";
    }

    // Genera todos los periodos desde $startYear hasta el periodo actual (hoy)
    public static function generarPeriodosHasta(int $startYear): array
    {
        $periodos  = [];
        $hoy       = Carbon::now();
        $periodoHoy = self::calcularPeriodo($hoy);
        $yearHoy   = $hoy->year;

        $ordenPorAnio = ['I', 'VAC-I', 'II', 'VAC-II'];

        for ($year = $startYear; $year <= $yearHoy; $year++) {
            foreach ($ordenPorAnio as $sem) {
                // Para VAC-II el label pertenece al año actual aunque termine en marzo del siguiente
                $label = "{$year}-{$sem}";
                $rango = self::rangoFechasDePeriodo($label);

                if (!$rango) continue;

                // Solo incluir si el periodo ya comenzó (su fecha de inicio <= hoy)
                if ($rango[0]->gt($hoy)) break 2;

                $periodos[] = $label;

                // Parar justo al llegar al periodo actual
                if ($label === $periodoHoy) break 2;
            }
        }

        return array_reverse($periodos); // más reciente primero
    }

    // Convierte "2026-I" / "2026-VAC-I" / "2026-II" / "2026-VAC-II" en rango de fechas
    public static function rangoFechasDePeriodo(string $periodo): ?array
    {
        // Formato: "YYYY-SEMESTRE" donde SEMESTRE puede ser I, VAC-I, II, VAC-II
        $parts    = explode('-', $periodo, 2); // ["2026", "VAC-I"] ó ["2026", "I"]
        $year     = (int)$parts[0];
        $semestre = $parts[1] ?? null;

        return match ($semestre) {
            'I'      => [
                Carbon::createFromDate($year, 3, 16)->startOfDay(),
                Carbon::createFromDate($year, 7, 3)->endOfDay(),
            ],
            'VAC-I'  => [
                Carbon::createFromDate($year, 7, 4)->startOfDay(),
                Carbon::createFromDate($year, 8, 10)->endOfDay(),
            ],
            'II'     => [
                Carbon::createFromDate($year, 8, 11)->startOfDay(),
                Carbon::createFromDate($year, 11, 28)->endOfDay(),
            ],
            'VAC-II' => [
                Carbon::createFromDate($year, 11, 29)->startOfDay(),
                Carbon::createFromDate($year + 1, 3, 15)->endOfDay(),
            ],
            default  => null,
        };
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }
    public function documents()
    {
        return $this->hasMany(Document::class); // Una práctica puede tener muchos documentos
    }
    public function visits()
    {
        return $this->hasMany(Visit::class); // Una práctica puede tener muchas visitas
    }
}
