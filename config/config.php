<?php
/**
 * SIH - Sistema de Inventario de Hardware
 * Configuración de Base de Datos
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'db_sih');
define('DB_USER', 'postgres');
define('DB_PASS', '0621');
define('CHARSET', 'utf8');

/**
 * URL BASE DEL SISTEMA — IMPORTANTE PARA LOS QR
 * ─────────────────────────────────────────────
 * Pon aquí la IP o dominio con el que acceden los demás dispositivos
 * en la red (el celular que escanea el QR necesita esta URL).
 *
 * Ejemplos:
 *   'http://192.168.1.50/sih_qr'   ← IP local de tu servidor
 *   'http://inventario.miempresa.com'
 *   'http://localhost/sih_qr'       ← solo funciona en el mismo PC
 */
define('APP_URL', 'http://192.168.40.7/sih_qr/');
/**
 * CONFIGURACIÓN DE CORREO — PHPMailer
 * ─────────────────────────────────────────────
 * Rellena estos datos con los de tu cuenta SMTP.
 *
 * Para envia.co normalmente:
 *   MAIL_HOST = mail.envia.co  (o smtp.envia.co)
 *   MAIL_PORT = 465 (SSL) o 587 (TLS)
 */
define('MAIL_HOST',       'smtp.gmail.com');       // Servidor SMTP Gmail
define('MAIL_PORT',        465);                   // Gmail usa 587 con TLS
define('MAIL_ENCRYPTION', 'ssl');                  // Gmail requiere TLS
define('MAIL_USER',       'solayitapias1@gmail.com'); // Cuenta Gmail remitente
define('MAIL_PASS',       'chdlndlxgudxtseh'); // Contraseña de aplicación Gmail (no la normal)
define('MAIL_FROM_NAME',  'SIH — Mesa de Ayuda'); // Nombre del remitente

// ── Destinatario de notificaciones de nuevas novedades ──────────────────────
define('MAIL_NOTIFY_TO',  'solayitapias@gmail.com');     // Correo que recibe las notificaciones
