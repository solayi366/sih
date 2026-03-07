<?php
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
    <title>SIH — Sistema de Identificación de Hardware por QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }

        body { background: #0a0a0f; position: relative; }

        .bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }

        .orb {
            position: absolute; border-radius: 50%;
            filter: blur(80px); opacity: .55;
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
            animation-direction: alternate;
        }
        .orb-1 { width:500px;height:500px;background:#be123c;top:-100px;left:-80px;animation:orb1 9s infinite alternate; }
        .orb-2 { width:400px;height:400px;background:#1e1b4b;bottom:-80px;right:-60px;animation:orb2 11s infinite alternate; }
        .orb-3 { width:300px;height:300px;background:#9f1239;bottom:100px;left:30%;animation:orb3 7s infinite alternate;opacity:.3; }

        @keyframes orb1 { from{transform:translate(0,0) scale(1)} to{transform:translate(60px,80px) scale(1.15)} }
        @keyframes orb2 { from{transform:translate(0,0) scale(1)} to{transform:translate(-50px,-60px) scale(1.1)} }
        @keyframes orb3 { from{transform:translate(0,0) scale(1)} to{transform:translate(40px,-50px) scale(1.2)} }

        .container {
            position:relative;z-index:1;height:100vh;
            display:flex;flex-direction:column;
            align-items:center;justify-content:center;
            padding:2rem;gap:2.5rem;
        }

        .glass-panel {
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.1);
            border-radius:2rem;
            backdrop-filter:blur(24px);
            -webkit-backdrop-filter:blur(24px);
            padding:3rem 2.5rem 2.5rem;
            width:100%;max-width:520px;
            box-shadow:0 0 0 1px rgba(255,255,255,.05) inset,0 32px 64px rgba(0,0,0,.4);
            animation:fadeUp .8s cubic-bezier(.16,1,.3,1) both;
        }

        .chip {
            display:inline-flex;align-items:center;gap:.5rem;
            background:rgba(225,29,72,.15);
            border:1px solid rgba(225,29,72,.3);
            border-radius:50px;padding:.35rem 1rem;
            font-size:.68rem;font-weight:800;
            letter-spacing:.1em;text-transform:uppercase;
            color:#fda4af;margin-bottom:1.5rem;
        }

        .title-block { margin-bottom:2rem; }

        .title-small {
            font-size:.7rem;font-weight:700;
            letter-spacing:.15em;text-transform:uppercase;
            color:rgba(255,255,255,.35);margin-bottom:.4rem;
        }

        h1 {
            font-size:1.75rem;font-weight:900;
            line-height:1.2;color:#fff;letter-spacing:-.02em;
        }
        h1 span { color:#e11d48; }

        .divider {
            height:1px;
            background:linear-gradient(90deg,rgba(225,29,72,.4),rgba(255,255,255,.05),transparent);
            margin-bottom:2rem;
        }

        .cards { display:flex;flex-direction:column;gap:1rem; }

        .card {
            display:flex;align-items:center;gap:1.25rem;
            padding:1.25rem 1.5rem;border-radius:1.25rem;
            text-decoration:none;color:#fff;
            border:1px solid rgba(255,255,255,.08);
            background:rgba(255,255,255,.04);
            transition:all .25s cubic-bezier(.34,1.56,.64,1);
            position:relative;overflow:hidden;
        }

        .card:hover {
            transform:translateY(-3px);
            border-color:rgba(255,255,255,.16);
            background:rgba(255,255,255,.07);
            box-shadow:0 16px 40px rgba(0,0,0,.3);
        }

        .card-admin { border-color:rgba(225,29,72,.3);background:rgba(225,29,72,.08); }
        .card-admin:hover {
            border-color:rgba(225,29,72,.5);
            background:rgba(225,29,72,.13);
            box-shadow:0 16px 40px rgba(225,29,72,.2);
        }

        .card-icon {
            flex-shrink:0;width:3rem;height:3rem;
            border-radius:1rem;display:flex;
            align-items:center;justify-content:center;font-size:1.1rem;
        }
        .card-admin  .card-icon { background:rgba(225,29,72,.2);border:1px solid rgba(225,29,72,.35);color:#f43f5e; }
        .card-portal .card-icon { background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.7); }

        .card-body { flex:1; }

        .card-tag {
            font-size:.6rem;font-weight:800;
            letter-spacing:.1em;text-transform:uppercase;
            color:rgba(255,255,255,.35);margin-bottom:.2rem;
        }
        .card-admin .card-tag { color:rgba(244,63,94,.6); }

        .card-title { font-size:1rem;font-weight:800;color:#fff;line-height:1.3; }
        .card-desc  { font-size:.78rem;color:rgba(255,255,255,.45);font-weight:500;margin-top:.2rem; }

        .card-chevron {
            flex-shrink:0;width:2rem;height:2rem;border-radius:50%;
            background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
            display:flex;align-items:center;justify-content:center;
            font-size:.7rem;color:rgba(255,255,255,.4);transition:all .25s;
        }
        .card:hover .card-chevron { background:rgba(255,255,255,.12);color:#fff;transform:translateX(2px); }
        .card-admin .card-chevron { background:rgba(225,29,72,.15);border-color:rgba(225,29,72,.3);color:#f43f5e; }
        .card-admin:hover .card-chevron { background:rgba(225,29,72,.3);color:#fff; }

        .panel-footer {
            margin-top:2rem;
            display:flex;align-items:center;justify-content:space-between;
        }
        .footer-text { font-size:.68rem;font-weight:600;color:rgba(255,255,255,.2);letter-spacing:.05em; }

        .footer-dot {
            width:.4rem;height:.4rem;border-radius:50%;
            background:#e11d48;box-shadow:0 0 8px #e11d48;
            animation:pulse 2s infinite;
        }

        @keyframes pulse {
            0%,100%{opacity:1;transform:scale(1)}
            50%{opacity:.5;transform:scale(.8)}
        }

        @keyframes fadeUp {
            from{opacity:0;transform:translateY(30px) scale(.97)}
            to{opacity:1;transform:translateY(0) scale(1)}
        }

        .btn-manual {
            position:fixed;bottom:1.5rem;right:1.5rem;z-index:10;
            display:flex;align-items:center;gap:.5rem;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.12);
            border-radius:50px;padding:.55rem 1rem;
            color:rgba(255,255,255,.55);
            text-decoration:none;font-family:'Plus Jakarta Sans',sans-serif;
            font-size:.72rem;font-weight:700;letter-spacing:.04em;
            backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
            transition:all .25s ease;
        }
        .btn-manual:hover {
            background:rgba(255,255,255,.12);
            border-color:rgba(255,255,255,.25);
            color:#fff;
            box-shadow:0 8px 24px rgba(0,0,0,.3);
            transform:translateY(-2px);
        }
        .btn-manual i { font-size:.7rem; }

        @media (max-width:480px) {
            html,body{overflow:auto;}
            .glass-panel{padding:2rem 1.5rem;}
            h1{font-size:1.4rem;}
            .btn-manual{bottom:1rem;right:1rem;}
        }
    </style>
</head>
<body>

<div class="bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="container">
    <div class="glass-panel">

        <div class="chip">
            <i class="fas fa-qrcode"></i>
            SIH &mdash; v1.0
        </div>

        <div class="title-block">
            <div class="title-small">Bienvenido al sistema</div>
            <h1>Sistema de Identificación<br>de Hardware <span>por QR</span></h1>
        </div>

        <div class="divider"></div>

        <div class="cards">

            <a href="public/login.php" class="card card-admin">
                <div class="card-icon"><i class="fa-solid fa-box-archive"></i></div>
                <div class="card-body">
                    <div class="card-tag">Personal TI</div>
                    <div class="card-title">Panel de Administración</div>
                    <div class="card-desc">Gestiona elementos tecnológicos, inventario y novedades</div>
                </div>
                <div class="card-chevron"><i class="fas fa-chevron-right"></i></div>
            </a>

            <a href="public/portal_reportes.php" class="card card-portal">
                <div class="card-icon"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="card-body">
                    <div class="card-tag">Usuarios</div>
                    <div class="card-title">Reportar una Novedad</div>
                    <div class="card-desc">Reporta fallas sin necesidad de iniciar sesión</div>
                </div>
                <div class="card-chevron"><i class="fas fa-chevron-right"></i></div>
            </a>

        </div>

        <div class="panel-footer">
            <span class="footer-text">ENVIA &nbsp;·&nbsp; <?php echo date('Y'); ?></span>
            <div class="footer-dot"></div>
        </div>

    </div>
</div>

<a href="https://envia06.com/sih_qr/documentos/Manual.pdf" target="_blank" class="btn-manual">
    <i class="fas fa-book-open"></i> Manual de Usuario
</a>

</body>
</html>