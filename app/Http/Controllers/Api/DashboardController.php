<?php

namespace App\Http\Controllers\Api;

use App\Models\Document;
use App\Models\Practice;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $isAdmin = Auth::user()->hasRole('Admin');

        // Closure reutilizable para el scope base
        $q = fn() => $isAdmin
            ? Practice::query()
            : Practice::where('user_id', Auth::id());

        $dq = fn() => $isAdmin
            ? Document::query()
            : Document::whereHas('practice', fn($p) => $p->where('user_id', Auth::id()));

        // ── 1. KPIs ──────────────────────────────────────────────────────────
        $total      = $q()->count();
        $pendientes = $q()->where('status', 'Pendiente')->count();
        $aprobadas  = $q()->where('status', 'Aprobado')->count();
        $rechazadas = $q()->where('status', 'Rechazado')->count();

        $kpis = [
            'total_practicas'   => $total,
            'pendientes'        => $pendientes,
            'aprobadas'         => $aprobadas,
            'rechazadas'        => $rechazadas,
            'total_documentos'  => $dq()->count(),
            'total_estudiantes' => $isAdmin ? User::role('Estudiante')->count() : null,
        ];

        // ── 2. Prácticas por estado — dona/pie ────────────────────────────────
        $porEstado = [
            ['estado' => 'Pendiente', 'total' => $pendientes],
            ['estado' => 'Aprobado',  'total' => $aprobadas],
            ['estado' => 'Rechazado', 'total' => $rechazadas],
        ];

        // ── 3. Prácticas por periodo — barras ─────────────────────────────────
        $porPeriodo = $q()->select('created_at')->get()
            ->groupBy(fn($p) => Practice::calcularPeriodo($p->created_at))
            ->map(fn($group, $periodo) => ['periodo' => $periodo, 'total' => $group->count()])
            ->values()
            ->sortByDesc('periodo')
            ->values();

        // ── 4. Prácticas por mes — últimos 12 meses — línea ──────────────────
        $porMes = $q()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // ── 5. Top 5 empresas con más prácticas ───────────────────────────────
        $topEmpresas = $q()
            ->selectRaw('name_empresa, COUNT(*) as total')
            ->groupBy('name_empresa')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── 6. Documentos por tipo — barras ───────────────────────────────────
        $porTipoDocumento = $dq()
            ->selectRaw('document_type, COUNT(*) as total')
            ->groupBy('document_type')
            ->orderByDesc('total')
            ->get();

        // ── 7. Últimas 5 prácticas registradas ───────────────────────────────
        $ultimasPracticas = $q()
            ->with('user:id,name,last_name,code')
            ->latest()
            ->limit(5)
            ->get(['id', 'name_empresa', 'status', 'user_id', 'created_at'])
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name_empresa'=> $p->name_empresa,
                    'status'      => $p->status,
                    'periodo'     => Practice::calcularPeriodo($p->created_at),
                    'created_at'  => $p->created_at?->toISOString(),
                    'estudiante'  => $p->user ? [
                        'id'        => $p->user->id,
                        'name'      => $p->user->name,
                        'last_name' => $p->user->last_name,
                        'code'      => $p->user->code,
                    ] : null,
                ];
            });

        // ── 8. Top 5 estudiantes con más horas (solo Admin) ──────────────────
        $topEstudiantes = $isAdmin
            ? User::role('Estudiante')
                ->select('id', 'name', 'last_name', 'code', 'academic_cycle', 'hours_of_practice')
                ->orderByDesc('hours_of_practice')
                ->limit(5)
                ->get()
            : null;

        return $this->successResponse([
            'kpis'                  => $kpis,
            'practicas_por_estado'  => $porEstado,
            'practicas_por_periodo' => $porPeriodo,
            'practicas_por_mes'     => $porMes,
            'top_empresas'          => $topEmpresas,
            'documentos_por_tipo'   => $porTipoDocumento,
            'ultimas_practicas'     => $ultimasPracticas,
            'top_estudiantes_horas' => $topEstudiantes,
        ]);
    }
}
