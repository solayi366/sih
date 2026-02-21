<?php
/**
 * test_mail.php — Diagnóstico de envío de correo
 * IMPORTANTE: Elimina este archivo del servidor una vez confirmado que funciona.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$resultado = null;
$error     = null;
$log       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = new PHPMailer(true);

    try {
        // Debug detallado — capturamos la salida
        $mail->SMTPDebug   = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) use (&$log) {
            $log[] = htmlspecialchars(trim($str));
        };

        $mail->isSMTP();
        $mail->Host        = MAIL_HOST;
        $mail->Port        = MAIL_PORT;
        $mail->SMTPAuth    = true;
        $mail->Username    = MAIL_USER;
        $mail->Password    = MAIL_PASS;
        $mail->SMTPSecure  = MAIL_ENCRYPTION === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->CharSet  = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
        $mail->addAddress(MAIL_NOTIFY_TO);

        $mail->Subject = '✅ Prueba de correo — SIH Mesa de Ayuda';
        $mail->Body    = '
            <div style="font-family:Arial,sans-serif;padding:30px;background:#f1f5f9">
                <div style="background:#fff;border-radius:16px;padding:30px;max-width:500px;margin:auto;border-left:5px solid #10b981">
                    <h2 style="color:#10b981;margin:0 0 12px">✅ ¡Correo funcionando!</h2>
                    <p style="color:#475569;margin:0">Este es un mensaje de prueba enviado desde <strong>SIH — Mesa de Ayuda</strong>.</p>
                    <hr style="border:none;border-top:1px solid #e2e8f0;margin:20px 0">
                    <p style="color:#94a3b8;font-size:12px;margin:0">
                        Remitente: ' . MAIL_USER . '<br>
                        Destinatario: ' . MAIL_NOTIFY_TO . '<br>
                        Servidor: ' . MAIL_HOST . ':' . MAIL_PORT . ' (' . MAIL_ENCRYPTION . ')
                    </p>
                </div>
            </div>';
        $mail->AltBody = 'Prueba de correo SIH — funcionando correctamente.';

        $mail->send();
        $resultado = 'success';

    } catch (Exception $e) {
        $resultado = 'error';
        $error     = $mail->ErrorInfo;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Correo — SIH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6" style="font-family:'Segoe UI',sans-serif">
<div class="bg-white rounded-3xl shadow-xl w-full max-w-xl p-8">

    <div class="flex items-center gap-3 mb-6">
        <div class="p-3 bg-blue-100 rounded-2xl text-blue-600">
            <i class="fas fa-paper-plane text-xl"></i>
        </div>
        <div>
            <h1 class="text-xl font-black text-slate-800">Test de Correo SMTP</h1>
            <p class="text-xs text-slate-400 font-medium">Diagnóstico de envío — SIH Mesa de Ayuda</p>
        </div>
    </div>

    <!-- Configuración detectada -->
    <div class="bg-slate-50 rounded-2xl p-4 mb-6 space-y-2 text-xs font-mono">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Configuración detectada</p>
        <div class="flex justify-between"><span class="text-slate-500">Servidor</span> <span class="font-bold text-slate-700"><?= MAIL_HOST ?>:<?= MAIL_PORT ?> (<?= MAIL_ENCRYPTION ?>)</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Remitente</span> <span class="font-bold text-slate-700"><?= MAIL_USER ?></span></div>
        <div class="flex justify-between"><span class="text-slate-500">Contraseña</span> <span class="font-bold text-slate-700"><?= str_repeat('●', strlen(MAIL_PASS) - 4) . substr(MAIL_PASS, -4) ?></span></div>
        <div class="flex justify-between"><span class="text-slate-500">Destinatario</span> <span class="font-bold text-slate-700"><?= MAIL_NOTIFY_TO ?></span></div>
    </div>

    <?php if ($resultado === 'success'): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-6 flex items-start gap-3">
        <i class="fas fa-check-circle text-emerald-500 text-2xl mt-0.5"></i>
        <div>
            <p class="font-black text-emerald-700 text-sm">¡Correo enviado correctamente!</p>
            <p class="text-emerald-600 text-xs mt-1">Revisa la bandeja de <strong><?= MAIL_NOTIFY_TO ?></strong> (incluyendo spam).</p>
        </div>
    </div>
    <?php elseif ($resultado === 'error'): ?>
    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-6 flex items-start gap-3">
        <i class="fas fa-times-circle text-red-500 text-2xl mt-0.5"></i>
        <div>
            <p class="font-black text-red-700 text-sm">Error al enviar</p>
            <p class="text-red-600 text-xs mt-1 font-mono"><?= $error ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($log)): ?>
    <div class="mb-6">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Log de conexión SMTP</p>
        <div class="bg-slate-900 rounded-xl p-4 max-h-60 overflow-y-auto">
            <?php foreach ($log as $line): ?>
            <p class="text-[10px] font-mono text-slate-300 leading-relaxed
                <?= str_contains($line, 'ERROR') || str_contains($line, 'FAILED') ? 'text-red-400' : '' ?>
                <?= str_contains($line, '250') || str_contains($line, 'OK') ? 'text-emerald-400' : '' ?>">
                <?= $line ?>
            </p>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST">
        <button type="submit"
                class="w-full py-4 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-sm transition-all shadow-lg flex items-center justify-center gap-2">
            <i class="fas fa-paper-plane"></i>
            <?= $resultado ? 'Enviar de nuevo' : 'Enviar correo de prueba' ?>
        </button>
    </form>

    <p class="text-center text-[10px] text-slate-300 mt-4 font-bold">
        ⚠️ Elimina este archivo del servidor una vez confirmado que funciona.
    </p>

</div>
</body>
</html>
