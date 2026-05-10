<x-mail::message>
# ❌ Tu práctica requiere correcciones

Hola **{{ $practice->user->name }} {{ $practice->user->last_name }}**,

Lamentamos informarte que tu solicitud de prácticas preprofesionales en **{{ $practice->name_empresa }}** no ha sido aprobada en esta revisión.

---

@if($rejectionReason)
**Motivo:**

{{ $rejectionReason }}
@else
El administrador no especificó un motivo. Por favor, comunícate con la coordinación.
@endif

---

**¿Qué puedes hacer?**

- Verifica los datos ingresados (empresa, RUC, representante)
- Corrige lo indicado y registra una nueva solicitud en el sistema
- Si tienes dudas, comunícate con la coordinación de la EP

Saludos,<br>
**{{ config('app.name') }}** — EP Administración · UPeU
</x-mail::message>
