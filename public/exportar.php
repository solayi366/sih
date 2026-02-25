<?php
/**
 * SIH — Exportar a Excel
 * Punto de entrada: /public/exportar.php
 *   ?modo=individual&id=N       → ficha completa de un activo
 *   ?modo=general&tipos[]=...   → inventario filtrado por tipo(s)
 */
require_once '../controllers/exportarController.php';
ExportarController::manejar();
