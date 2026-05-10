<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestiPPP — Sistema de Gestión de Prácticas Pre Profesionales</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f0fe 0%, #f0f4ff 50%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        /* ── Card principal ── */
        .card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 30, 80, 0.14);
            max-width: 560px;
            width: 100%;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #002855 0%, #003f88 60%, #0052b4 100%);
            padding: 40px 40px 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 180px; height: 180px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -40px; left: -40px;
            width: 130px; height: 130px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .logo-wrap svg {
            width: 44px;
            height: 44px;
        }

        .app-name {
            color: #fff;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .app-full {
            color: #c5d9f0;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 6px;
        }

        .institution {
            color: #7aafd4;
            font-size: 11.5px;
            letter-spacing: 0.3px;
        }

        /* ── Body ── */
        .body {
            padding: 44px 48px;
        }

        .body h2 {
            font-size: 20px;
            color: #0f172a;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .body p {
            font-size: 14.5px;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        /* ── Feature list ── */
        .features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 32px;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .feature-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .feature-icon svg {
            width: 22px;
            height: 22px;
        }

        .feature-icon.blue   { background: #eff6ff; }
        .feature-icon.green  { background: #ecfdf5; }
        .feature-icon.amber  { background: #fffbeb; }

        .feature-text strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .feature-text span {
            font-size: 12.5px;
            color: #64748b;
        }

        /* ── Status badge ── */
        .status-badge {
            background: #ecfdf5;
            border: 1.5px solid #6ee7b7;
            border-radius: 8px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
        }

        .status-badge p {
            margin: 0;
            font-size: 13px;
            color: #065f46;
            font-weight: 500;
        }

        /* ── Footer ── */
        .footer {
            background: #f8fafd;
            border-top: 1px solid #f1f5f9;
            padding: 18px 40px;
            text-align: center;
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

    <div class="card">

        {{-- ── Header ── --}}
        <div class="header">
            <div class="logo-wrap">
                {{-- Icono maletín institucional --}}
                <svg viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="44" height="44" rx="12" fill="rgba(255,255,255,0.13)"/>
                    <path d="M17 20v-3a2.5 2.5 0 012.5-2.5h5A2.5 2.5 0 0127 17v3" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/>
                    <rect x="10" y="20" width="24" height="15" rx="3.5" stroke="#fff" stroke-width="1.8"/>
                    <path d="M10 27.5h24" stroke="#fff" stroke-width="1.4" stroke-dasharray="3 2.5"/>
                    <circle cx="22" cy="27.5" r="1.8" fill="#fff"/>
                </svg>
                <span class="app-name">GestiPPP</span>
            </div>
            <p class="app-full">Sistema de Gestión de Prácticas Pre Profesionales</p>
            <p class="institution">Escuela Profesional de Administración &bull; Universidad Peruana Unión</p>
        </div>

        {{-- ── Body ── --}}
        <div class="body">
            <h2>API en funcionamiento</h2>
            <p>
                El backend de GestiPPP está operativo. Este sistema permite gestionar
                el proceso completo de prácticas pre profesionales para estudiantes
                de la Escuela Profesional de Administración.
            </p>

            <ul class="features">
                <li>
                    <div class="feature-icon blue">
                        {{-- Icono usuario/registro --}}
                        <svg viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="11" cy="8" r="3.5" stroke="#2563eb" stroke-width="1.6"/>
                            <path d="M4 19c0-3.866 3.134-7 7-7h1c3.866 0 7 3.134 7 7" stroke="#2563eb" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="feature-text">
                        <strong>Gestión de usuarios y roles</strong>
                        <span>Registro, autenticación JWT, verificación de correo</span>
                    </div>
                </li>
                <li>
                    <div class="feature-icon green">
                        {{-- Icono documento/prácticas --}}
                        <svg viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 3h7.5L18 7.5V19a1 1 0 01-1 1H6a1 1 0 01-1-1V4a1 1 0 011-1z" stroke="#059669" stroke-width="1.6"/>
                            <path d="M13 3v5h5" stroke="#059669" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M8 11h6M8 14h4" stroke="#059669" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="feature-text">
                        <strong>Trámites de prácticas</strong>
                        <span>Solicitudes, documentos, cartas y seguimiento</span>
                    </div>
                </li>
                <li>
                    <div class="feature-icon amber">
                        {{-- Icono visitas/bitácora --}}
                        <svg viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="4" width="16" height="15" rx="2.5" stroke="#d97706" stroke-width="1.6"/>
                            <path d="M7 4V2M15 4V2" stroke="#d97706" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M3 9h16" stroke="#d97706" stroke-width="1.5"/>
                            <circle cx="8" cy="13.5" r="1.2" fill="#d97706"/>
                            <circle cx="11" cy="13.5" r="1.2" fill="#d97706"/>
                            <circle cx="14" cy="13.5" r="1.2" fill="#d97706"/>
                        </svg>
                    </div>
                    <div class="feature-text">
                        <strong>Registro de visitas</strong>
                        <span>Bitácora de visitas y supervisión empresarial</span>
                    </div>
                </li>
            </ul>

            <div class="status-badge">
                {{-- Icono check servidor --}}
                <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="10" cy="10" r="8.5" stroke="#059669" stroke-width="1.5"/>
                    <path d="M6.5 10.5l2.5 2.5 5-6" stroke="#059669" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p>Servidor activo y funcionando correctamente</p>
            </div>
        </div>

        {{-- ── Footer ── --}}
        <div class="footer">
            <p>
                <strong>Universidad Peruana Unión</strong><br>
                Escuela Profesional de Administración &mdash; Sistema GestiPPP v1.0
            </p>
        </div>

    </div>

</body>
</html>
