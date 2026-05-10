<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Correo — GestiPPP</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 50%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .wrapper {
            max-width: 500px;
            width: 100%;
        }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 30, 80, 0.13);
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #002855 0%, #003f88 60%, #0052b4 100%);
            padding: 32px 36px 28px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 140px; height: 140px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -30px; left: -30px;
            width: 100px; height: 100px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .header-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .header-logo svg {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
        }

        .header h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .header .subtitle {
            color: #a8c4e8;
            font-size: 12px;
            margin-top: 6px;
            line-height: 1.5;
        }

        .header .institution {
            color: #7aafd4;
            font-size: 11px;
            margin-top: 4px;
            letter-spacing: 0.3px;
        }

        /* ── Body ── */
        .body {
            padding: 44px 36px 36px;
            text-align: center;
        }

        /* ── Icon circle ── */
        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 96px;
            height: 96px;
            border-radius: 50%;
            margin-bottom: 24px;
        }

        .icon-wrap svg {
            width: 52px;
            height: 52px;
        }

        /* Success */
        .state-success .icon-wrap  { background: #e8faf1; border: 3px solid #a7f3d0; }
        .state-success .title      { color: #065f46; }
        .state-success .badge      { background: #ecfdf5; color: #065f46; border-color: #6ee7b7; }

        /* Already verified */
        .state-already .icon-wrap  { background: #eff6ff; border: 3px solid #bfdbfe; }
        .state-already .title      { color: #1e40af; }
        .state-already .badge      { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }

        /* Invalid / expired */
        .state-invalid .icon-wrap  { background: #fff5f5; border: 3px solid #fecaca; }
        .state-invalid .title      { color: #991b1b; }
        .state-invalid .badge      { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }

        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .message {
            font-size: 15px;
            line-height: 1.7;
            color: #4b5563;
            margin-bottom: 28px;
            max-width: 360px;
            margin-left: auto;
            margin-right: auto;
        }

        .badge {
            display: inline-block;
            border: 1.5px solid;
            border-radius: 999px;
            padding: 8px 28px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* ── Divider ── */
        .divider {
            height: 1px;
            background: #f1f5f9;
            margin: 28px 0 0;
        }

        /* ── Footer ── */
        .footer {
            padding: 18px 36px;
            text-align: center;
            background: #f8fafd;
        }

        .footer p {
            font-size: 11.5px;
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
                {{-- Icono maletín/portafolio institucional --}}
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="10" fill="rgba(255,255,255,0.12)"/>
                    <path d="M15 17v-2a2 2 0 012-2h6a2 2 0 012 2v2" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/>
                    <rect x="9" y="17" width="22" height="14" rx="3" stroke="#fff" stroke-width="1.8"/>
                    <path d="M9 24h22" stroke="#fff" stroke-width="1.5" stroke-dasharray="3 2"/>
                    <circle cx="20" cy="24" r="1.5" fill="#fff"/>
                </svg>
                <h1>GestiPPP</h1>
            </div>
            <p class="subtitle">Sistema de Gestión de Prácticas Pre Profesionales</p>
            <p class="institution">Escuela Profesional de Administración &bull; UPeU</p>
        </div>

        {{-- ── Body ── --}}
        @if ($status === 'success')
        <div class="body state-success">
            <div class="icon-wrap">
                {{-- Escudo con paloma/check: verificación completada --}}
                <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M26 4L8 11v14c0 10.5 7.6 20.3 18 23 10.4-2.7 18-12.5 18-23V11L26 4z"
                          fill="#d1fae5" stroke="#059669" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M17 26l7 7 11-13"
                          stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="title">¡Correo verificado!</div>
            <p class="message">{{ $message }}</p>
            <span class="badge">Cuenta habilitada &mdash; ya puedes ingresar a la app</span>
        </div>

        @elseif ($status === 'already_verified')
        <div class="body state-already">
            <div class="icon-wrap">
                {{-- Escudo con candado: ya estaba protegido/verificado --}}
                <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M26 4L8 11v14c0 10.5 7.6 20.3 18 23 10.4-2.7 18-12.5 18-23V11L26 4z"
                          fill="#dbeafe" stroke="#2563eb" stroke-width="2" stroke-linejoin="round"/>
                    <rect x="20" y="26" width="12" height="9" rx="2" fill="#2563eb"/>
                    <path d="M22 26v-3a4 4 0 018 0v3"
                          stroke="#2563eb" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="26" cy="30.5" r="1.5" fill="#fff"/>
                </svg>
            </div>
            <div class="title">Ya verificado</div>
            <p class="message">{{ $message }}</p>
            <span class="badge">Tu cuenta está activa</span>
        </div>

        @else
        <div class="body state-invalid">
            <div class="icon-wrap">
                {{-- Escudo con reloj: enlace expirado --}}
                <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M26 4L8 11v14c0 10.5 7.6 20.3 18 23 10.4-2.7 18-12.5 18-23V11L26 4z"
                          fill="#fee2e2" stroke="#dc2626" stroke-width="2" stroke-linejoin="round"/>
                    <circle cx="26" cy="27" r="8" stroke="#dc2626" stroke-width="2"/>
                    <path d="M26 23v5l3 3" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="title">Enlace inválido o expirado</div>
            <p class="message">{{ $message }}</p>
            <span class="badge">Solicita un nuevo enlace desde la aplicación</span>
        </div>
        @endif

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
