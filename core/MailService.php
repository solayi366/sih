<?php
/**
 * MailService.php
 * Servicio centralizado de envío de correos usando PHPMailer.
 * Todos los correos del sistema pasan por aquí.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    /**
     * Envía la notificación de nueva novedad al área de TI.
     *
     * @param array $ticket  Datos del ticket recién creado
     * @return array         ['success' => bool, 'msg' => string]
     */
    public static function notificarNuevaNovedad(array $ticket): array
    {
        $mail = self::crearMailer();

        try {
            $mail->addAddress(MAIL_NOTIFY_TO);
            $mail->Subject = '🔴 Nuevo Ticket #' . $ticket['id_novedad'] . ' — ' . $ticket['tipo_dano'];
            $mail->Body    = self::htmlNuevaNovedad($ticket);
            $mail->AltBody = self::textoNuevaNovedad($ticket);

            $mail->send();
            return ['success' => true, 'msg' => 'Correo enviado correctamente'];
        } catch (Exception $e) {
            error_log('MailService::notificarNuevaNovedad — ' . $mail->ErrorInfo);
            return ['success' => false, 'msg' => $mail->ErrorInfo];
        }
    }

    // ── Instancia base de PHPMailer ───────────────────────────────────────────
    private static function crearMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host        = MAIL_HOST;
        $mail->Port        = MAIL_PORT;
        $mail->SMTPAuth    = true;
        $mail->Username    = MAIL_USER;
        $mail->Password    = MAIL_PASS;
        $mail->SMTPSecure  = MAIL_ENCRYPTION === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->CharSet     = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);

        return $mail;
    }

    // ── Plantilla HTML del correo ─────────────────────────────────────────────
    private static function htmlNuevaNovedad(array $t): string
    {
        $id       = htmlspecialchars($t['id_novedad']  ?? '—');
        $fecha    = htmlspecialchars($t['fecha']        ?? date('Y-m-d H:i'));
        $nombre   = htmlspecialchars($t['nombre']       ?? '—');
        $cod      = htmlspecialchars($t['cod_nom']      ?? '—');
        $tipo     = htmlspecialchars($t['tipo_dano']    ?? '—');
        $desc     = nl2br(htmlspecialchars($t['descripcion'] ?? '—'));
        $activo   = htmlspecialchars($t['activo_ref']   ?? '—');
        $qr_code  = htmlspecialchars($t['activo_qr']    ?? '');
        $foto     = !empty($t['evidencia_foto']) ? htmlspecialchars($t['evidencia_foto']) : null;
        $url_ver  = rtrim(APP_URL, '/') . '/public/novedades.php';

        $fotoHtml = $foto
            ? "<tr><td style='padding:0 32px 24px'>
                 <p style='margin:0 0 8px;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em'>Evidencia fotográfica</p>
                 <img src='{$foto}' style='max-width:100%;border-radius:12px;border:2px solid #e2e8f0'>
               </td></tr>"
            : '';

        $qrHtml = $qr_code
            ? "<img src='" . rtrim(APP_URL, '/') . "/controllers/qrController.php?codigo=" . urlencode($qr_code) . "'
                    width='80' height='80' style='border-radius:8px'>"
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

  <!-- HEADER -->
  <tr>
    <td style="background:linear-gradient(135deg,#881337,#e11d48);border-radius:20px 20px 0 0;padding:28px 32px;text-align:center">
      <p style="margin:0 0 4px;font-size:11px;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.15em">
        SIH — Mesa de Ayuda
      </p>
      <h1 style="margin:0;font-size:24px;font-weight:900;color:#fff">
        🔴 Nuevo Ticket de Soporte
      </h1>
    </td>
  </tr>

  <!-- BADGE TICKET -->
  <tr>
    <td style="background:#fff;padding:24px 32px 0;text-align:center">
      <span style="display:inline-block;background:#fff1f2;color:#e11d48;font-size:13px;font-weight:900;padding:6px 20px;border-radius:50px;border:2px solid #fecdd3">
        Ticket #{$id}
      </span>
      <p style="margin:8px 0 0;font-size:12px;color:#94a3b8;font-weight:600">{$fecha}</p>
    </td>
  </tr>

  <!-- DATOS REPORTANTE -->
  <tr>
    <td style="background:#fff;padding:24px 32px 0">
      <p style="margin:0 0 12px;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em">
        Reportado por
      </p>
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="background:#f8fafc;border-radius:12px;padding:14px 18px">
            <p style="margin:0;font-size:15px;font-weight:800;color:#1e293b">{$nombre}</p>
            <p style="margin:4px 0 0;font-size:11px;font-weight:600;color:#94a3b8;font-family:monospace">{$cod}</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- ACTIVO AFECTADO -->
  <tr>
    <td style="background:#fff;padding:16px 32px 0">
      <p style="margin:0 0 12px;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em">
        Activo Afectado
      </p>
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td style="background:#f8fafc;border-radius:12px;padding:14px 18px;vertical-align:middle">
            <table cellpadding="0" cellspacing="0"><tr>
              <td style="padding-right:14px">{$qrHtml}</td>
              <td>
                <p style="margin:0;font-size:13px;font-weight:900;color:#1e293b;text-transform:uppercase">{$activo}</p>
                <p style="margin:4px 0 0;font-size:11px;color:#94a3b8;font-family:monospace">{$qr_code}</p>
              </td>
            </tr></table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- TIPO DE DAÑO -->
  <tr>
    <td style="background:#fff;padding:16px 32px 0">
      <p style="margin:0 0 8px;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em">
        Tipo de Daño
      </p>
      <span style="display:inline-block;background:#f1f5f9;color:#475569;font-size:11px;font-weight:900;padding:5px 14px;border-radius:8px;border:1px solid #e2e8f0;text-transform:uppercase">
        {$tipo}
      </span>
    </td>
  </tr>

  <!-- DESCRIPCIÓN -->
  <tr>
    <td style="background:#fff;padding:16px 32px 24px">
      <p style="margin:0 0 8px;font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em">
        Descripción del problema
      </p>
      <div style="background:#f8fafc;border-left:4px solid #e11d48;border-radius:0 12px 12px 0;padding:14px 18px">
        <p style="margin:0;font-size:14px;color:#334155;font-weight:500;line-height:1.6">{$desc}</p>
      </div>
    </td>
  </tr>

  <!-- FOTO (si existe) -->
  {$fotoHtml}

  <!-- CTA -->
  <tr>
    <td style="background:#fff;padding:0 32px 32px;text-align:center">
      <a href="{$url_ver}"
         style="display:inline-block;background:linear-gradient(135deg,#881337,#e11d48);color:#fff;font-size:13px;font-weight:900;padding:14px 32px;border-radius:12px;text-decoration:none;letter-spacing:.05em;text-transform:uppercase">
        Ver en Mesa de Ayuda →
      </a>
    </td>
  </tr>

  <!-- FOOTER -->
  <tr>
    <td style="background:#f8fafc;border-radius:0 0 20px 20px;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0">
      <p style="margin:0;font-size:11px;color:#94a3b8">
        Este correo fue generado automáticamente por <strong>SIH — Sistema de Inventario de Hardware</strong>.
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    // ── Versión texto plano (fallback) ────────────────────────────────────────
    private static function textoNuevaNovedad(array $t): string
    {
        return sprintf(
            "NUEVO TICKET #%s — %s\n\n" .
            "Fecha: %s\n" .
            "Reportado por: %s (%s)\n" .
            "Activo: %s\n" .
            "Tipo de daño: %s\n\n" .
            "Descripción:\n%s\n\n" .
            "Ver en: %s",
            $t['id_novedad']  ?? '—',
            $t['tipo_dano']   ?? '—',
            $t['fecha']       ?? date('Y-m-d H:i'),
            $t['nombre']      ?? '—',
            $t['cod_nom']     ?? '—',
            $t['activo_ref']  ?? '—',
            $t['tipo_dano']   ?? '—',
            $t['descripcion'] ?? '—',
            rtrim(APP_URL, '/') . '/public/novedades.php'
        );
    }
}
