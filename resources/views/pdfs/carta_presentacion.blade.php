<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carta de Presentación</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            color: #000;
            margin: 0.5cm 1cm;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #003366;
            padding-bottom: 4px;
            margin-bottom: 20px;
        }

        .header img {
            height: 75px;
        }

        .subtitle {
            text-align: center;
            font-size: 10pt;
            font-style: italic;
            margin-top: -8px;
            margin-bottom: 25px;
        }

        .ref {
            text-align: right;
            font-size: 11pt;
            margin-bottom: 25px;
        }

        .carta-num {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .recipient {
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .body-text {
            text-align: justify;
            line-height: 1.5;
            margin-bottom: 35px;
        }

        .body-text p {
            margin-bottom: 15px;
        }

        .farewell {
            margin-bottom: 20px;
        }

        .signature {
            text-align: start;
            margin-top: 40px;
            line-height: 1;
        }

        .signature .name {
            font-weight: bold;
        }

        .signature .title {
            font-size: 11pt;
        }

        .footer {
            position: fixed;
            bottom: 0.1cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #333;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="{{ public_path('images/logoupeu.png') }}" alt="Logo UPeU">
    </div>

    <div class="subtitle">
        "Año de la Esperanza y el Fortalecimiento de la Democracia"
    </div>

    <div class="ref">
        Villa Chullunquiani, {{ $fecha_emision }}
    </div>

    <div class="carta-num">
        {{ $numero_carta }}
    </div>

    {{-- Destinatario: representante de la empresa --}}
    <div class="recipient">
        <p>{{ $empresa->trate_represent ? $empresa->trate_represent . ' ' : '' }}{{ $empresa->name_represent }} {{ $empresa->lastname_represent }}</p>
        <p>{{ $empresa->name_empresa }}</p>
        <p>Presente.-</p>
    </div>

    <p><strong>De mi mayor consideración:</strong></p>

    <div class="body-text">
        <p>Reciba un cordial saludo a nombre de la Escuela Profesional de Administración y los mejores deseos de bendición de lo Alto a usted y familia.</p>

        <p>
            Es grato presentar al/a (la) estudiante
            <strong>{{ $estudiante->name }} {{ $estudiante->last_name }}</strong>,
            identificado(a) con código universitario <strong> N° {{ $estudiante->code }}</strong>,
            estudiante del <strong>{{ $estudiante->academic_cycle }}</strong> ciclo
            de la Escuela Profesional de <strong>{{ $estudiante->career ?? 'Administración' }}</strong>,
            quien desea realizar Prácticas Pre profesionales en la entidad asu cargo, por eso nos acercamos
            a su desapacho y solicitamos que pueda ser admitido(a), y de esa manera pueda
            cumplir con los requisitos exigidos por la escuela, logrando los objetivos necesarios en la formación del futuro administrador.
        </p>

        <p>Agradeciendo de antemano el apoyo brindado, me despido.</p>
    </div>

    <div class="farewell">
        <p>Cordialmente,</p>
    </div>

    <div class="signature">
        <p class="name">Mg. Amed Vargas Martínez</p>
        <p class="title">Coordinador EP Administración</p>
        <p class="title">UPeU Campus Juliaca</p>
    </div>

    <div class="footer">
        <p>Carretera Salida a Arequipa Km. 6 Chullunquiani, Autop. Héroes de la Guerra del Pacífico, Juliaca – Puno – Perú</p>
        <p>Web: https://www.upeu.edu.pe | Teléfono: (01) 6186902</p>
    </div>

</body>

</html>
