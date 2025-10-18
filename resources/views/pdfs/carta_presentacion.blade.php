<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carta de Presentación</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            color: #000;
            margin: 0.5cm 1cm;
        }

        /* Encabezado */
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

        .header-right {
            text-align: right;
            font-size: 10pt;
            line-height: 1.2;
        }

        /* Subtítulo institucional */
        .subtitle {
            text-align: center;
            font-size: 10pt;
            font-style: italic;
            margin-top: -8px;
            margin-bottom: 25px;
        }

        /* Fecha y referencia */
        .ref {
            text-align: right;
            font-size: 11pt;
            margin-bottom: 25px;
        }

        /* Número de carta */
        .carta-num {
            font-weight: bold;
            margin-bottom: 25px;
        }

        /* Destinatario */
        .recipient {
            margin-bottom: 25px;
            line-height: 1.3;
        }

        /* Cuerpo del texto */
        .body-text {
            text-align: justify;
            line-height: 1.5;
            margin-bottom: 35px;
        }

        .body-text p {
            margin-bottom: 15px;
        }

        /* Despedida */
        .farewell {
            margin-bottom: 40px;
        }

        /* Firma */
        .signature {
            text-align: center;
            margin-top: 60px;
        }

        .signature .name {
            font-weight: bold;
        }

        .signature .title {
            font-size: 11pt;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #333;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }

        /* Sello recibido */
        .stamp {
            position: absolute;
            right: 2.5cm;
            bottom: 5cm;
            width: 140px;
            height: 70px;
            border: 2px solid #0033cc;
            text-align: center;
            font-size: 9pt;
            opacity: 0.7;
            transform: rotate(1deg);
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="{{ public_path('images/logoupeu.png') }}" alt="Logo UPeU">
    </div>

    <div class="subtitle">
        “Año de la recuperación y consolidación de la economía peruana”
    </div>

    <div class="ref">
        Villa Chullunquiani, {{ $fecha_emision }}
    </div>

    <div class="carta-num">
        CARTA N° {{ $numero_carta }}
    </div>

    <div class="recipient">
        <p>Mg. Amed Vargas Martínez</p>
        <p>Director de la EP Administración</p>
        <p>Presente.-</p>
    </div>

    <p><strong>De mi mayor consideración:</strong></p>

    <div class="body-text">
        <p>Reciba un cordial saludo a nombre de la Escuela Profesional de Ingeniería de Sistemas y los mejores deseos de bendición de lo Alto a usted y familia.</p>

        <p>Es grato presentar al(a) estudiante <strong>{{ $estudiante->name }} {{ $estudiante->last_name }}</strong>, identificado(a) con código universitario N° <strong>{{ $estudiante->code }}</strong>, estudiante del Programa de la Escuela Profesional de <strong>Ingeniería de Sistemas</strong>, quien desea realizar Prácticas Preprofesionales en la empresa <strong>{{ $empresa->name_empresa }}</strong>, bajo la supervisión de <strong>{{ $empresa->trate_represent }} {{ $empresa->name_represent }} {{ $empresa->lastname_represent }}</strong>.</p>

        <p>El estudiante realizará actividades de <strong>{{ $empresa->activity_student }}</strong> con una duración total de <strong>{{ $empresa->hourse_practice }}</strong> horas. Solicitamos que pueda ser admitido(a) para cumplir con los requisitos exigidos por la escuela, logrando así los objetivos necesarios en la formación del futuro ingeniero.</p>

        <p>Agradeciendo de antemano el apoyo brindado, me despido.</p>
    </div>

    <div class="farewell">
        <p>Cordialmente,</p>
    </div>

    <div class="signature">
        <p class="name">Dr. Danny Levano Rodríguez</p>
        <p class="title">Coordinador EP Ingeniería de Sistemas</p>
        <p class="title">UPeU Campus Juliaca</p>
    </div>

    <div class="footer">
        <p>Carretera Salida a Arequipa Km. 6 Chullunquiani, Autop. Héroes de la Guerra del Pacífico, Juliaca – Puno – Perú</p>
        <p>Web: <a href="https://www.upeu.edu.pe/">www.upeu.edu.pe</a> | Teléfono: (01) 6186902</p>
    </div>

</body>

</html>