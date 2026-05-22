<?php

namespace App\Http\Controllers\Api\PPP;

use App\Http\Requests\PPP\StoreDocumentRequest;
use App\Http\Requests\PPP\StorePracticeRequest;
use App\Http\Requests\PPP\UpdatePracticeRequest;
use App\Http\Resources\PPP\DocumentResource;
use App\Models\Document;
use App\Models\Empresa;
use App\Models\Practice;
use App\Services\DocumentService;
use App\Services\PracticeService;
use App\Services\SunatService;
use App\Traits\ApiResponseTrait;
use App\Mail\PracticeApproved;
use App\Mail\PracticeRejected;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PracticeController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly PracticeService $practiceService,
        private readonly DocumentService $documentService,
        private readonly SunatService $sunatService,
    ) {}

    public function index(Request $request)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $search       = $request->input('search');
        $periodo      = $request->input('periodo');
        $status       = $request->input('status');
        $isAdmin      = Auth::user()->hasRole('Admin');

        $query = Practice::withCount('documents')->with(['user:id,name,last_name,code', 'empresa']);

        if (!$isAdmin) {
            $query->where('user_id', Auth::id());
        }

        if ($search) {
            $query->where(function ($q) use ($search, $isAdmin) {
                $q->whereHas('empresa', function ($e) use ($search) {
                    $e->where('name_empresa', 'like', "%{$search}%")
                      ->orWhere('ruc', 'like', "%{$search}%");
                });

                // Admin también puede buscar por datos del estudiante
                if ($isAdmin) {
                    $q->orWhereHas('user', function ($u) use ($search) {
                        $u->where('code', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    });
                }
            });
        }

        if ($periodo) {
            $rango = Practice::rangoFechasDePeriodo($periodo);
            if ($rango) {
                $query->whereBetween('created_at', $rango);
            }
        }

        if ($status && in_array($status, ['Pendiente', 'Aprobado', 'Rechazado', 'Cerrado'])) {
            $query->where('status', $status);
        }

        $data = $query->paginate($size, ['*'], 'page', $frontendPage + 1);

        return $this->successResponse([
            'content'       => $data->items(),
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function periodos(Request $request)
    {
        $isAdmin = Auth::user()->hasRole('Admin');

        // Año más antiguo con prácticas (para este usuario o global si es admin)
        $query = Practice::selectRaw('MIN(YEAR(created_at)) as min_year');

        if (!$isAdmin) {
            $query->where('user_id', Auth::id());
        }

        $minYear = $query->value('min_year') ?? Carbon::now()->year;

        $periodos = Practice::generarPeriodosHasta((int)$minYear);

        return $this->successResponse($periodos);
    }

    public function documentsByPractice(Request $request, $practiceId)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $search       = $request->input('search');

        $query = Document::where('practice_id', $practiceId);

        if ($search) {
            $query->where('document_name', 'like', "%{$search}%");
        }

        $data = $query->paginate($size, ['*'], 'page', $frontendPage + 1);

        return $this->successResponse([
            'content'       => $data->items(),
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function tiposDocumento()
    {
        return $this->successResponse(Document::TIPOS_PERMITIDOS);
    }

    public function downloadPlanTemplate(Request $request)
    {
        $format = strtolower($request->input('format', 'pdf'));

        if ($format === 'docx') {
            $path     = public_path('templates/plan_practicas_template.docx');
            $fileName = 'Plan_Practicas_Preprofesionales_UPeU.docx';
            $mime     = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

            if (!file_exists($path)) {
                $path = $this->generateDocxTemplate();
            }

            return response()->download($path, $fileName, ['Content-Type' => $mime]);
        }

        $path = public_path('templates/plan_practicas_template.pdf');

        if (!file_exists($path)) {
            return $this->errorResponse('Plantilla PDF no disponible.', 404);
        }

        return response()->download($path, 'Plan_Practicas_Preprofesionales_UPeU.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function generateDocxTemplate(): string
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $boldStyle   = ['bold' => true];
        $centerStyle = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $blueColor   = ['bold' => true, 'color' => '003399', 'size' => 14];

        // ── PORTADA ───────────────────────────────────────────
        $portada = $phpWord->addSection([
            'marginTop'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginLeft'   => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3),
            'marginRight'  => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3),
        ]);

        $portada->addText('UNIVERSIDAD PERUANA UNIÓN',
            ['bold' => true, 'size' => 18],
            $centerStyle
        );
        $portada->addText('FACULTAD DE INGENIERÍA Y ARQUITECTURA',
            ['bold' => true, 'size' => 13],
            $centerStyle
        );
        $portada->addText('Escuela Profesional de Administración',
            ['bold' => true, 'size' => 12],
            $centerStyle
        );

        $portada->addTextBreak(3);

        $portada->addText('PLAN DE PRÁCTICAS PREPROFESIONALES',
            ['bold' => true, 'size' => 14, 'allCaps' => true],
            $centerStyle
        );

        $portada->addTextBreak(3);

        // Tabla de datos
        $table = $portada->addTable(['borderSize' => 6, 'borderColor' => '888888', 'width' => 9000, 'unit' => 'dxa']);

        foreach (['Lugar', 'Área', 'Línea de carrera'] as $label) {
            $table->addRow(600);
            $cell = $table->addCell(3000, ['bgColor' => 'F5F5F5']);
            $cell->addText($label, $boldStyle);
            $table->addCell(6000)->addText('');
        }

        $portada->addTextBreak(3);
        $portada->addText('Emitido por:', ['bold' => true, 'size' => 14], $centerStyle);
        $portada->addText('________________________________________________', [], $centerStyle);
        $portada->addText('Nombre del alumno / Código', ['size' => 11], $centerStyle);

        $portada->addTextBreak(4);
        $portada->addText('UPeU Juliaca, ' . now()->locale('es')->isoFormat('MMMM YYYY'),
            $blueColor,
            $centerStyle
        );

        // ── PÁGINA 2 — ÍNDICE ─────────────────────────────────
        $indice = $phpWord->addSection([
            'breakType'    => 'nextPage',
            'marginTop'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginLeft'   => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3),
            'marginRight'  => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
        ]);

        $indice->addText('PLANTILLA PROYECTO',
            ['bold' => true, 'size' => 12, 'allCaps' => true],
            $centerStyle
        );
        $indice->addTextBreak(1);

        $secciones = [
            '1.'  => 'INTRODUCCIÓN',
            '2.'  => 'OBJETIVO',
            ''    => 'OBJETIVOS ESPECÍFICOS',
            '3.'  => 'ALCANCE',
            '4.'  => 'NORMATIVIDAD / PROCESOS',
            '5.'  => 'JUSTIFICACIÓN',
            '6.'  => 'METODOLOGÍA DE TRABAJO',
            '7.'  => 'EQUIPO DE TRABAJO',
            '8.'  => 'ACTIVIDADES, CRONOGRAMA Y PRESUPUESTO',
            '9.'  => 'DETALLE DEL PRESUPUESTO',
            '10.' => 'ESTRATEGIAS POR IMPLEMENTAR',
            '11.' => 'EVIDENCIAS QUE GENERAR',
        ];

        foreach ($secciones as $num => $titulo) {
            $p = $indice->addTextRun(['spaceBefore' => 120, 'spaceAfter' => 120]);
            if ($num) {
                $p->addText("$num  ", $boldStyle);
            } else {
                $p->addText('      ', []);
            }
            $p->addText($titulo, $boldStyle);
        }

        // Guardar
        $destPath = public_path('templates/plan_practicas_template.docx');
        $writer   = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($destPath);

        return $destPath;
    }

    public function practicesforselect(Request $request)
    {
        $practices = Practice::where('user_id', Auth::id())
            ->with('empresa:id,name_empresa')
            ->select('id', 'empresa_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            $practices->map(fn($p) => ['id' => $p->id, 'name_empresa' => $p->empresa?->name_empresa])->toArray()
        );
    }

    public function show($id)
    {
        $practice = Practice::with('empresa', 'user:id,name,last_name,code')->find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        return $this->successResponse($practice->toArray());
    }

    public function store(StorePracticeRequest $request)
    {
        $validated = $request->validated();

        $practicaActiva = Practice::where('user_id', Auth::id())
            ->where('status', 'Aprobado')
            ->exists();

        if ($practicaActiva) {
            return $this->errorResponse(
                'Ya tienes una práctica activa. Debes completarla antes de registrar una nueva.',
                422
            );
        }

        $empresa = Empresa::updateOrCreate(
            ['ruc' => $validated['ruc']],
            [
                'name_empresa'       => $validated['name_empresa'],
                'name_represent'     => $validated['name_represent'],
                'lastname_represent' => $validated['lastname_represent'],
                'trate_represent'    => $validated['trate_represent'] ?? null,
                'phone_represent'    => $validated['phone_represent'],
            ]
        );

        $practice = Practice::create([
            'empresa_id'       => $empresa->id,
            'activity_student' => $validated['activity_student'],
            'hourse_practice'  => $validated['hourse_practice'],
            'user_id'          => Auth::id(),
            'status'           => 'Pendiente',
        ]);

        return $this->successResponse(
            $practice->load('empresa')->toArray(),
            'Práctica registrada. Pendiente de aprobación.',
            201
        );
    }

    public function approve($id)
    {
        $practice = Practice::with('user', 'empresa')->find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        if ($practice->status === 'Aprobado') {
            return $this->errorResponse('La práctica ya fue aprobada', 422);
        }

        if (!$practice->user) {
            return $this->errorResponse('El estudiante asociado no existe', 422);
        }

        // Verificar RUC en SUNAT antes de aprobar
        $ruc = $practice->empresa?->ruc;
        if ($ruc && config('services.sunat.token')) {
            $verificacion = $this->sunatService->verificarActivo($ruc);

            if (!$verificacion['valido']) {
                return $this->errorResponse($verificacion['mensaje'], 422);
            }

            // Actualizar datos de ubicación de la empresa con la info más reciente de SUNAT
            $practice->empresa->update([
                'departamento' => $verificacion['data']['departamento'],
                'provincia'    => $verificacion['data']['provincia'],
                'distrito'     => $verificacion['data']['distrito'],
            ]);
        }

        try {
            $pdf = $this->practiceService->generateCartaPresentacion($practice);

            $practice->update(['status' => 'Aprobado', 'rejection_reason' => null]);

            if ($practice->user->email) {
                Mail::to($practice->user->email)
                    ->queue(new PracticeApproved($practice, $pdf['url'], $pdf['path']));
            }

            return $this->successResponse(
                array_merge($practice->fresh()->toArray(), ['pdf_url' => $pdf['url']]),
                'Práctica aprobada y carta de presentación generada.'
            );
        } catch (\Throwable $th) {
            Log::error('Error al aprobar práctica:', ['error' => $th->getMessage(), 'line' => $th->getLine()]);
            return $this->errorResponse('Error al generar la carta de presentación', 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        if ($practice->status === 'Aprobado') {
            return $this->errorResponse('No se puede rechazar una práctica ya aprobada', 422);
        }

        $rejectionReason = $request->input('rejection_reason');

        $practice->update([
            'status'           => 'Rechazado',
            'rejection_reason' => $rejectionReason,
        ]);

        $practice->load('user');

        // Notificar al estudiante si tiene correo registrado
        if ($practice->user?->email) {
            Mail::to($practice->user->email)
                ->send(new PracticeRejected($practice, $rejectionReason));
        }

        return $this->successResponse($practice->toArray(), 'Práctica rechazada.');
    }

    public function storeDocumentPractice(StoreDocumentRequest $request)
    {
        $user         = Auth::user();
        $documentType = $request->document_type;
        $practice     = Practice::with('user')->find($request->practice_id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        if ($practice->status !== 'Aprobado') {
            return $this->errorResponse('Solo se pueden subir documentos a prácticas aprobadas', 422);
        }

        // Solo el Docente puede subir Ficha Visita
        if (in_array($documentType, Document::TIPOS_DOCENTE) && !$user->hasAnyRole(['Admin', 'Docente'])) {
            return $this->errorResponse('No tienes permiso para subir este tipo de documento', 403);
        }

        // Solo Admin puede subir Sustentacion de Practicas
        if (in_array($documentType, Document::TIPOS_ADMIN) && !$user->hasRole('Admin')) {
            return $this->errorResponse('No tienes permiso para subir este tipo de documento', 403);
        }

        // Solo el Estudiante puede subir sus tipos
        if (in_array($documentType, Document::TIPOS_ESTUDIANTE) && !$user->hasAnyRole(['Admin', 'Estudiante'])) {
            return $this->errorResponse('No tienes permiso para subir este tipo de documento', 403);
        }

        // Ficha Visita requiere visit_id obligatorio
        if ($documentType === 'Ficha Visita' && !$request->visit_id) {
            return $this->errorResponse('Debes indicar el visit_id para subir una Ficha de Visita.', 422);
        }

        // Orden secuencial: cada documento requiere que el anterior esté aprobado
        $prerequisitos = [
            'Carta Aceptacion'       => 'Carta Presentacion',
            'Plan de Practicas'      => 'Carta Aceptacion',
            'Ficha Visita'           => 'Plan de Practicas',
            'Informe Jefe Empresa'   => 'Plan de Practicas',
            'Constancia de Practica' => 'Informe de Practicas',
        ];

        if (isset($prerequisitos[$documentType])) {
            $tipoRequerido  = $prerequisitos[$documentType];
            $prerequisitoOk = $practice->documents()
                ->where('document_type', $tipoRequerido)
                ->where('document_status', 'Aprobado')
                ->exists();

            if (!$prerequisitoOk) {
                return $this->errorResponse(
                    "Debes tener el documento \"{$tipoRequerido}\" aprobado antes de subir este documento.",
                    422
                );
            }
        }

        // Informe de Practicas requiere las 3 Fichas de Visita + Informe Jefe Empresa aprobados
        if ($documentType === 'Informe de Practicas') {
            $fichasAprobadas = $practice->documents()
                ->where('document_type', 'Ficha Visita')
                ->where('document_status', 'Aprobado')
                ->count();

            if ($fichasAprobadas < 3) {
                return $this->errorResponse(
                    'Debes tener las 3 Fichas de Visita (Inicio, Medio, Final) aprobadas antes de subir el Informe de Prácticas.',
                    422
                );
            }

            $informeJefeOk = $practice->documents()
                ->where('document_type', 'Informe Jefe Empresa')
                ->where('document_status', 'Aprobado')
                ->exists();

            if (!$informeJefeOk) {
                return $this->errorResponse(
                    'Debes tener el "Informe Jefe Empresa" aprobado antes de subir el Informe de Prácticas.',
                    422
                );
            }
        }

        $document = $this->documentService->upload(
            $request->practice_id,
            $documentType,
            $request->file('file'),
            $request->visit_id ? (int) $request->visit_id : null
        );

        return (new DocumentResource($document->load('practice')))->response()->setStatusCode(201);
    }

    public function update(UpdatePracticeRequest $request, $id)
    {
        $practice = Practice::with('empresa')->find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        $validated = $request->validated();

        // Si viene algún campo de empresa, actualizar o crear empresa
        $empresaFields = array_intersect_key($validated, array_flip([
            'ruc', 'name_empresa', 'name_represent', 'lastname_represent', 'trate_represent', 'phone_represent',
        ]));

        if (!empty($empresaFields)) {
            $ruc = $empresaFields['ruc'] ?? $practice->empresa->ruc;
            $empresa = Empresa::updateOrCreate(['ruc' => $ruc], array_filter($empresaFields, fn($v) => $v !== null));
            $practice->empresa_id = $empresa->id;
        }

        $practiceFields = array_intersect_key($validated, array_flip(['activity_student', 'hourse_practice']));
        if (!empty($practiceFields)) {
            $practice->fill($practiceFields);
        }

        $practice->save();

        return $this->successResponse($practice->fresh()->load('empresa')->toArray(), 'Práctica actualizada correctamente');
    }

    public function destroy($id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        $practice->delete();

        return $this->successResponse([], 'Práctica eliminada correctamente');
    }
}
