# ApiGestiPPPAdmin

API REST para la gestión de Prácticas Pre-Profesionales (PPP) — Universidad Peruana Unión (UPeU).
Desarrollada en **Laravel 11** con autenticación JWT y permisos por roles (Spatie).

---

## Flujo de Prácticas Pre-Profesionales

### Regla base
Un alumno tiene **una práctica activa a la vez**. Cada práctica es un evento que se abre y se cierra.
Las horas se acumulan entre eventos hasta llegar a **1500 horas** totales.

```
[Evento 1: Empresa A → 300h → CERRADO]
[Evento 2: Empresa B → 500h → CERRADO]
[Evento 3: Empresa C → 700h → ACTIVO ]
                  Total acumulado: 1500h ✓
```

---

### Documentos por evento (6 hitos principales)

| # | Documento | Quién sube | Quién aprueba | Notas |
|---|---|---|---|---|
| 1 | `Carta Presentacion` | Sistema (auto-generada) | — | Abre el evento |
| 2 | `Carta Aceptacion` | Estudiante | Encargado de Prácticas | La empresa acepta al alumno |
| 3 | `Plan de Practicas` | Estudiante | Encargado de Prácticas | Define actividades y fechas; habilita las 3 visitas del Docente |
| 4 | `Modulo de Evaluacion` | Docente (3 fichas) + Estudiante (1 informe jefe) | Encargado de Prácticas | 3 fichas de visita (Inicio, Medio, Final) + Informe del jefe/dueño de empresa |
| 5 | `Informe de Practicas` | Estudiante | Encargado de Prácticas | Incluye autoevaluación académica |
| 6 | `Constancia de Practica` | Estudiante (empresa la firma y entrega) | Encargado de Prácticas | Certifica horas trabajadas; cierra el evento |

> El sistema suma automáticamente las horas del evento al contador global al aprobarse la Constancia.

---

### Detalle del Módulo de Evaluación (Hito 4)

El Módulo de Evaluación es un sistema estructurado de 4 documentos que miden el desempeño del practicante:

| Sub-documento | Quién genera | Cuándo |
|---|---|---|
| `Ficha Visita Inicio` | Docente (visita a la empresa) | Al inicio de la práctica |
| `Ficha Visita Medio` | Docente (visita a la empresa) | A mitad del periodo |
| `Ficha Visita Final` | Docente (visita a la empresa) | Al término del periodo |
| `Informe Jefe Empresa` | Empresa (firma/sella) → sube el Estudiante | Al finalizar la práctica |

> Las visitas se habilitan una vez aprobado el Plan de Prácticas (Hito 3).
> Cada ficha de visita se vincula a su registro en la tabla `visits` mediante `visit_id`.

---

### Hito final de carrera (fuera del bucle por evento)

```
Contador global >= 1500h con todos los eventos en estado "Aprobado"
                        ↓
        Sustentacion de Practicas (ante jurado de facultad)
                        ↓
        Estado del alumno: PPP Completadas / Apto para Grado
```

> La Sustentación **no pertenece al ciclo por evento**. Es un acto académico único al completar las 1500h.

---

## Roles del Sistema

| Rol | Responsabilidades principales |
|---|---|
| **Admin** | Gestión total del sistema |
| **Encargado de Prácticas** | Aprueba/rechaza todos los documentos, firma documentos finales, gestiona el proceso completo |
| **Coordinador** | Genera informes globales, firma la Sustentación |
| **Docente** | Sube las 3 fichas de visita (Inicio, Medio, Final) por práctica |
| **Estudiante** | Sube Carta Aceptación, Plan de Prácticas, Informe Jefe Empresa, Informe de Prácticas, Constancia |

---

## Cambios Pendientes en el Código

### 1. Modelo `Document` — tipos a actualizar

**Eliminar del flujo por evento:**
- `Plan de Practicas` → renombrar a como corresponde o mantener
- `Evaluacion Jefe Inmediato` → renombrar a `Informe Jefe Empresa`
- `Monitoreo y Evaluacion` → renombrar a `Ficha Visita` (se distingue por `visit_type`)
- `Sustentacion de Practicas` → mover al hito final de carrera (flujo separado)

**Agregar:**
- `Constancia de Practica`

**Lista final de `TIPOS_PERMITIDOS`:**
```php
const TIPOS_PERMITIDOS = [
    'Carta Presentacion',   // 1 - Auto-generada
    'Carta Aceptacion',     // 2 - Estudiante
    'Plan de Practicas',    // 3 - Estudiante
    'Ficha Visita',         // 4 - Docente (linked to visit_id)
    'Informe Jefe Empresa', // 4d - Estudiante
    'Informe de Practicas', // 5 - Estudiante
    'Constancia de Practica', // 6 - Estudiante
];
```

### 2. Migración `visits` — `visit_type` tiene 3 valores correctos
```php
$table->enum('visit_type', ['Inicio', 'Medio', 'Final']); // correcto
```

### 3. Migración `documents` — `visit_id` ya agregado
```php
$table->unsignedBigInteger('visit_id')->nullable();
$table->foreign('visit_id')->references('id')->on('visits')->onDelete('set null');
```

### 4. Lógica de prerequisitos en `PracticeController`

Orden secuencial a respetar:
```
Carta Presentacion → Carta Aceptacion → Plan de Practicas
→ (habilita visitas del Docente)
→ Informe Jefe Empresa → Informe de Practicas → Constancia de Practica
```

### 5. Seeder de roles y permisos — pendiente implementar
- Crear roles: Encargado de Prácticas, Coordinador, Docente
- Asignar permisos por documento según la tabla de roles
- Actualizar rutas con middleware de rol correcto

---

## Stack Tecnológico

- **Laravel 11**
- **JWT Auth** — php-open-source-saver/jwt-auth
- **Spatie Laravel Permission** — roles y permisos
- **MySQL** — con softDeletes e índices de rendimiento en todas las tablas principales

---

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan storage:link
```
