<?php
/**
 * crear_ticket.php — POST /controllers/crear_ticket.php
 * Endpoint público. Recibe el formulario del portal y crea la novedad.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/novedadesController.php';
NovedadesController::crearTicket();
