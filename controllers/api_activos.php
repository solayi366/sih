<?php
/**
 * api_activos.php — GET /controllers/api_activos.php?cedula=XXXX
 * Endpoint público. Devuelve empleado y sus activos asignados en JSON.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/novedadesController.php';
NovedadesController::apiMisActivos();
