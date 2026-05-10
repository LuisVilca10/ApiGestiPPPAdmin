<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación — GestiPPP</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            max-width: 520px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 30, 80, 0.10);
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #002855 0%, #003f88 60%, #0052b4 100%);
            padding: 30px 36px 26px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 160px; height: 160px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .header-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .header-logo svg {
            width: 32px;
            height: 32px;
        }

        .header h1 {
            color: #fff;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .header .subtitle {
            color: #a8c4e8;
            font-size: 11.5px;
            margin-top: 5px;
            line-height: 1.5;
        }

        .header .institution {
            color: #7aafd4;
            font-size: 10.5px;
            margin-top: 3px;
        }

        /* ── Body ── */
        .body {
            padding: 36px 40px 30px;
            text-align: center;
        }

        /* Icono sobre el código */
        .envelope-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            background: #eff6ff;
            border: 2.5px solid #bfdbfe;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .envelope-icon svg {
            width: 36px;
            height: 36px;
        }

        .greeting {
            font-size: 16px;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .description {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* ── Código ── */
        .code-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .code-box {
            display: inline-block;
            background: #f0f7ff;
            border: 2.5px dashed #3b82f6;
            border-radius: 12px;
            padding: 18px 44px;
            font-size: 40px;
            font-weight: 800;
            letter-spacing: 12px;
            color: #1d4ed8;
            margin-bottom: 24px;
            font-variant-numeric: tabular-nums;
        }

        /* ── Info card ── */
        .info-card {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            text-align: left;
            margin-bottom: 4px;
        }

        .info-card svg {
            flex-shrink: 0;
            margin-top: 1px;
            width: 18px;
            height: 18px;
        }

        .info-card p {
            font-size: 13px;
            color: #92400e;
            line-height: 1.5;
        }

        .info-card strong {
            color: #78350f;
        }

        /* ── Divider ── */
        .divider {
            height: 1px;
            background: #f1f5f9;
            margin: 0;
        }

        /* ── Footer ── */
        .footer {
            background: #f8fafd;
            padding: 16px 36px;
            text-align: center;
        }

        .footer p {
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.6;
        }

        .footer strong {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            {{-- ── Header ── --}}
            <div class="header">
                <div class="header-logo">
                    <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="32" height="32" rx="8" fill="rgba(255,255,255,0.12)"/>
                        <path d="M11 13v-2a2 2 0 012-2h6a2 2 0 012 2v2" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
                        <rect x="7" y="13" width="18" height="11" rx="2.5" stroke="#fff" stroke-width="1.6"/>
                        <path d="M7 19h18" stroke="#fff" stroke-width="1.3" stroke-dasharray="2.5 2"/>
                        <circle cx="16" cy="19" r="1.2" fill="#fff"/>
                    </svg>
                    <h1>GestiPPP</h1>
                </div>
                <p class="subtitle">Sistema de Gestión de Prácticas Pre Profesionales</p>
                <p class="institution">Escuela Profesional de Administración &bull; UPeU</p>
            </div>

            {{-- ── Body ── --}}
            <div class="body">

                <div class="envelope-icon">
                    {{-- Icono: sobre con candado (verificación de correo) --}}
                    <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="9" width="23" height="16" rx="3" stroke="#2563eb" stroke-width="1.8"/>
                        <path d="M3 12l11.5 8.5L26 12" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="28.5" cy="21.5" r="5.5" fill="#eff6ff" stroke="#2563eb" stroke-width="1.5"/>
                        <path d="M26.5 21.5v-1.2a2 2 0 014 0v1.2" stroke="#2563eb" stroke-width="1.4" stroke-linecap="round"/>
                        <rect x="25.8" y="21.5" width="5.4" height="3.8" rx="1" fill="#2563eb"/>
                        <circle cx="28.5" cy="23.2" r="0.7" fill="#fff"/>
                    </svg>
                </div>

                <p class="greeting">Verifica tu correo electrónico</p>
                <p class="description">
                    Hola, usa el siguiente código para confirmar tu correo<br>y activar tu cuenta en GestiPPP.
                </p>

                <div class="code-label">Código de verificación</div>
                <div class="code-box">{{ $code }}</div>

                <div class="info-card">
                    {{-- Icono reloj --}}
                    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="9" cy="9" r="7.5" stroke="#d97706" stroke-width="1.5"/>
                        <path d="M9 5.5V9.5l3 2" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p>Este código expira en <strong>10 minutos</strong>. Si no solicitaste esto, puedes ignorar este mensaje.</p>
                </div>

            </div>

            <div class="divider"></div>

            {{-- ── Footer ── --}}
            <div class="footer">
                <p>
                    <strong>Universidad Peruana Unión</strong><br>
                    Escuela Profesional de Administración &mdash; Sistema GestiPPP
                </p>
            </div>

        </div>
    </div>
</body>
</html>
