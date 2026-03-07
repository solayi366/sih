<?php
// Si ya hay sesión activa, redirigir directo al dashboard
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: public/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIH — Sistema de Inventario de Hardware</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:       #e11d48;
            --red-dark:  #881337;
            --red-light: #fff1f2;
            --slate-900: #0f172a;
            --slate-400: #94a3b8;
            --white:     #ffffff;
        }

        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--slate-900);
            color: var(--white);
            overflow: hidden;
        }

        .bg { position: fixed; inset: 0; z-index: 0; }

        .bg-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(225,29,72,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(225,29,72,.06) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            from { background-position: 0 0; }
            to   { background-position: 48px 48px; }
        }

        .bg-glow-1 {
            position: absolute;
            width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(225,29,72,.18) 0%, transparent 70%);
            top: -150px; left: -150px;
            animation: float1 8s ease-in-out infinite;
        }

        .bg-glow-2 {
            position: absolute;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(136,19,55,.15) 0%, transparent 70%);
            bottom: -100px; right: -100px;
            animation: float2 10s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translate(0,0); }
            50%       { transform: translate(40px,30px); }
        }
        @keyframes float2 {
            0%, 100% { transform: translate(0,0); }
            50%       { transform: translate(-30px,-40px); }
        }

        .container {
            position: relative; z-index: 1;
            height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 2rem; gap: 3rem;
        }

        /* Header */
        .header { text-align: center; animation: fadeDown .7s ease both; }

        .logo-badge {
            display: inline-flex; align-items: center; gap: .6rem;
            background: rgba(225,29,72,.12);
            border: 1px solid rgba(225,29,72,.3);
            border-radius: 50px; padding: .4rem 1.2rem;
            font-size: .72rem; font-weight: 800;
            letter-spacing: .12em; text-transform: uppercase;
            color: #fda4af; margin-bottom: 1.5rem;
        }

        h1 {
            font-size: clamp(2.8rem, 6vw, 5rem);
            font-weight: 900; line-height: 1; letter-spacing: -.03em;
        }

        h1 .accent {
            background: linear-gradient(135deg, #e11d48, #fb7185);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            margin-top: 1rem; font-size: 1rem;
            color: var(--slate-400); font-weight: 500;
            max-width: 400px; margin-inline: auto; line-height: 1.6;
        }

        /* Cards */
        .cards {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 1.25rem; width: 100%; max-width: 600px;
            animation: fadeUp .7s .2s ease both;
        }

        .card {
            position: relative;
            display: flex; flex-direction: column;
            align-items: flex-start; gap: 1rem;
            padding: 2rem; border-radius: 1.5rem;
            text-decoration: none; overflow: hidden;
            transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
        }

        .card:hover { transform: translateY(-6px) scale(1.02); }

        .card-admin {
            background: linear-gradient(135deg, #e11d48 0%, #881337 100%);
            box-shadow: 0 8px 32px rgba(225,29,72,.35);
        }
        .card-admin:hover { box-shadow: 0 20px 48px rgba(225,29,72,.5); }

        .card-portal {
            background: rgba(255,255,255,.04);
            border: 1.5px solid rgba(255,255,255,.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,.2);
        }
        .card-portal:hover {
            background: rgba(255,255,255,.07);
            border-color: rgba(255,255,255,.2);
            box-shadow: 0 20px 48px rgba(0,0,0,.3);
        }

        .card-icon {
            width: 3rem; height: 3rem; border-radius: .875rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .card-admin  .card-icon { background: rgba(255,255,255,.2); color: white; }
        .card-portal .card-icon { background: rgba(225,29,72,.15); color: #fb7185; border: 1px solid rgba(225,29,72,.25); }

        .card-label {
            font-size: .65rem; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase; opacity: .65;
        }
        .card-title  { font-size: 1.25rem; font-weight: 800; line-height: 1.2; }
        .card-desc   { font-size: .82rem; line-height: 1.5; opacity: .7; font-weight: 500; }

        .card-arrow {
            position: absolute; bottom: 1.5rem; right: 1.5rem;
            width: 2rem; height: 2rem; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; transition: transform .3s;
        }
        .card:hover .card-arrow            { transform: translate(3px,-3px); }
        .card-admin  .card-arrow           { background: rgba(255,255,255,.2); color: white; }
        .card-portal .card-arrow           { background: rgba(225,29,72,.15); color: #fb7185; }

        /* Footer */
        .footer {
            font-size: .75rem; color: rgba(148,163,184,.4);
            font-weight: 600; letter-spacing: .05em;
            animation: fadeUp .7s .4s ease both;
        }

        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 500px) {
            .cards { grid-template-columns: 1fr; max-width: 340px; }
            h1 { font-size: 2.5rem; }
            html, body { overflow: auto; }
        }
    </style>
</head>
<body>

<div class="bg">
    <div class="bg-grid"></div>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>
</div>

<div class="container">

    <div class="header">
        <div class="logo-badge">
            <i class="fas fa-microchip"></i>
            Sistema de Inventario de Hardware
        </div>
        <h1>SIH<span class="accent">QR</span></h1>
        <p class="subtitle">Gestión inteligente de activos tecnológicos. Selecciona tu acceso.</p>
    </div>

    <div class="cards">

        <a href="public/login.php" class="card card-admin">
            <div class="card-icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div>
                <div class="card-label">Personal TI</div>
                <div class="card-title">Panel de Administración</div>
            </div>
            <div class="card-desc">Gestiona activos, inventario, novedades y parámetros del sistema.</div>
            <div class="card-arrow"><i class="fas fa-arrow-up-right"></i></div>
        </a>

        <a href="public/portal_reportes.php" class="card card-portal">
            <div class="card-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="card-label">Usuarios</div>
                <div class="card-title">Reportar un Problema</div>
            </div>
            <div class="card-desc">Reporta daños o fallas en equipos sin necesidad de iniciar sesión.</div>
            <div class="card-arrow"><i class="fas fa-arrow-up-right"></i></div>
        </a>

    </div>

    <div class="footer">
        <?php echo date('Y'); ?> &nbsp;·&nbsp; ENVIA &nbsp;·&nbsp; SIH v1.0
    </div>

</div>

</body>
</html>