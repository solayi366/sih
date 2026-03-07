<?php
/**
 * SIH — CSRF Helper
 *
 * Uso en vistas (formularios HTML):
 *   <?= Csrf::field() ?>          ← emite el <input type="hidden">
 *
 * Uso en vistas (fetch/FormData):
 *   const token = <?= Csrf::token() ?>;
 *   fd.append('csrf_token', token);
 *
 * Uso en controllers (verificar antes de procesar):
 *   Csrf::verify();               ← lanza excepción o redirige si falla
 *   Csrf::verify('activos.php');  ← redirige a esa URL si falla
 */
class Csrf {

    private const TOKEN_KEY    = '_csrf_token';
    private const FIELD_NAME   = 'csrf_token';
    private const TOKEN_LENGTH = 32; // bytes → 64 hex chars

    // ── Generar / recuperar el token de la sesión ─────────────
    public static function token(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    // ── Emitir el campo oculto listo para pegar en el form ────
    public static function field(): string {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD_NAME,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    // ── Verificar el token recibido en el POST ────────────────
    // $redirectOnFail: URL a la que redirigir si falla (null = lanzar excepción)
    public static function verify(?string $redirectOnFail = null): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $received = trim($_POST[self::FIELD_NAME] ?? '');
        $expected = $_SESSION[self::TOKEN_KEY]    ?? '';

        // hash_equals evita timing attacks
        if ($received === '' || !hash_equals($expected, $received)) {
            if ($redirectOnFail !== null) {
                header('Location: ' . $redirectOnFail . '?msg=' .
                    urlencode('Sesión expirada. Vuelve a intentarlo.') . '&tipo=danger');
                exit();
            }
            http_response_code(403);
            throw new \RuntimeException('CSRF token inválido o ausente.');
        }

        // Rotar el token tras cada uso exitoso para mayor seguridad
        unset($_SESSION[self::TOKEN_KEY]);
    }

    // ── Devolver el token fresco (tras rotate) para respuestas AJAX ──
    // Llamar DESPUÉS de verify(). Genera el nuevo token y lo retorna
    // para que el JS lo actualice en el formulario sin recargar.
    public static function newToken(): string {
        return self::token(); // token() ya genera uno nuevo si no existe
    }
}