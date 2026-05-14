<x-mail::message>
# Tu práctica fue aprobada

Hola **{{ $practice->user->name }} {{ $practice->user->last_name }}**,

Tu solicitud de prácticas preprofesionales ha sido **aprobada** por la Escuela Profesional.

---

**Empresa:** {{ $practice->name_empresa }}
**RUC:** {{ $practice->ruc }}
**Representante:** {{ $practice->trate_represent }} {{ $practice->name_represent }} {{ $practice->lastname_represent }}
**Horas planificadas:** {{ $practice->hourse_practice }} horas

---

Tu Carta de Presentación ya está generada. Descárgala y entrégala a la empresa.

<x-mail::button :url="$pdfUrl" color="success">
Descargar Carta de Presentación
</x-mail::button>

**Próximos pasos:**

1. Entrega la Carta de Presentación a la empresa
2. Sube la **Carta de Aceptación** una vez que la empresa te confirme
3. Sube tu **Plan de Prácticas**
4. Completa el resto del proceso en el sistema

Saludos,<br>
**{{ config('app.name') }}** — EP Administración · UPeU
</x-mail::message>
