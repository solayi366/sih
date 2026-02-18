<?php
require_once __DIR__ . '/../core/database.php';

// RUTA ABSOLUTA PARA EVITAR ERRORES
$libreria = __DIR__ . '/../vendor/SimpleXLSX.php';

if (file_exists($libreria)) {
    require_once $libreria;
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'msg' => 'ERROR CRITICO: No se encuentra el archivo en: ' . $libreria]);
    exit();
}

// Si la librería usa namespace (las versiones nuevas lo hacen), 
// añadimos este alias para que tu código SimpleXLSX::parse funcione:
use Shuchkin\SimpleXLSX; 


class ActivoController {
    
    // Trae toda la información para los selectores del formulario
    public static function getFormData() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::conectar();

        return [
            'tipos'    => $db->query("SELECT id_tipoequi, nom_tipo FROM tab_tipos WHERE activo = TRUE ORDER BY nom_tipo")->fetchAll(),
            'marcas'   => $db->query("SELECT id_marca, nom_marca FROM tab_marca WHERE activo = TRUE ORDER BY nom_marca")->fetchAll(),
            'modelos'  => $db->query("SELECT id_modelo, nom_modelo, id_tipoequi FROM tab_modelo WHERE activo = TRUE")->fetchAll(),
            'areas'    => $db->query("SELECT id_area, nom_area FROM tab_area WHERE activo = TRUE ORDER BY nom_area")->fetchAll(),
            'padres'   => $db->query("SELECT id_activo, serial, referencia FROM tab_activotec WHERE activo = TRUE AND id_padre_activo IS NULL")->fetchAll()
        ];
    }

    /**
     * MOTOR DE ANÁLISIS INTELIGENTE (Rastreo de Patrones)
     * Busca por etiquetas sin coordenadas fijas y detecta contextos (Principal vs Accesorios).
     */
    public static function analizarExcel($fileTmp) {
        if ($xlsx = SimpleXLSX::parse($fileTmp)) {
            $grid = $xlsx->rows();
            
            $res = [
                'principal' => [
                    'tipo' => '', 'marca' => '', 'referencia' => '', 
                    'serial' => '', 'ip' => '', 'mac' => '', 
                    'hostname' => '', 'responsable' => ''
                ],
                'accesorios' => []
            ];

            // Diccionarios de rastreo
            $primarios = ['CPU', 'COMPUTADOR', 'TABLET'];
            $accesorios = ['LECTOR', 'MOUSE', 'TECLADO', 'CARGADOR', 'TELEFONO', 'IMPRESORA', 'MONITOR'];
            
            // Almacén temporal para consolidar accesorios encontrados
            $accEncontrados = []; 

            foreach ($grid as $r => $row) {
                foreach ($row as $c => $cell) {
                    $val = trim(strtoupper((string)$cell));
                    if (empty($val)) continue;

                    // 1. DETERMINAR CONTEXTO (¿A quién pertenece la información de esta columna/zona?)
                    // Buscamos hacia arriba en la misma columna el título de la categoría
                    $categoriaActual = "";
                    for ($i = $r; $i >= 0; $i--) {
                        $checkCat = trim(strtoupper((string)($grid[$i][$c] ?? '')));
                        // ¿Es un activo principal?
                        foreach ($primarios as $p) {
                            if (strpos($checkCat, $p) !== false) { $categoriaActual = $p; break 2; }
                        }
                        // ¿Es un accesorio?
                        foreach ($accesorios as $a) {
                            if (strpos($checkCat, $a) !== false) { $categoriaActual = $a; break 2; }
                        }
                    }

                    // 2. EXTRACCIÓN DE DATOS SEGÚN REGLAS INTELIGENTES
                    $valorDerecha = trim((string)($row[$c + 1] ?? ''));
                    $valorAbajo   = trim((string)($grid[$r + 1][$c] ?? ''));

                    // Regla: SERIAL / SERIE (A la derecha)
                    if (strpos($val, 'SERIAL') !== false || strpos($val, 'SERIE') !== false) {
                        if (strpos($val, 'ENVIA') !== false) continue; // Ignorar seriales de envío
                        $this->asignarDatoInteligente($res, $accEncontrados, $categoriaActual, 'serial', $valorDerecha, $primarios);
                    }

                    // Regla: REFERENCIA (A la derecha)
                    if (strpos($val, 'REFERENCIA') !== false) {
                        $this->asignarDatoInteligente($res, $accEncontrados, $categoriaActual, 'referencia', $valorDerecha, $primarios);
                    }

                    // Regla: MARCA (A la derecha)
                    if (strpos($val, 'MARCA') !== false) {
                        $this->asignarDatoInteligente($res, $accEncontrados, $categoriaActual, 'marca', $valorDerecha, $primarios);
                    }

                    // Regla: NOMBRE DEL EQUIPO / HOSTNAME (A la derecha)
                    if (strpos($val, 'NOMBRE') !== false && strpos($val, 'EQUIPO') !== false) {
                        $res['principal']['hostname'] = $valorDerecha;
                    }

                    // Regla: RESPONSABLE -> CUSTODIO (Abajo)
                    if (strpos($val, 'RESPONSABLE') !== false) {
                        $respParts = explode(' - ', $valorAbajo);
                        $res['principal']['responsable'] = trim(end($respParts)); // Extrae la cédula/ID
                    }

                    // Reglas de Red (IP/MAC)
                    if ($val === 'IP')  $res['principal']['ip'] = $valorDerecha;
                    if ($val === 'MAC') $res['principal']['mac'] = $valorDerecha;

                    // Detectar Tipo Principal si se encuentra la palabra sola
                    foreach ($primarios as $p) {
                        if ($val === $p && empty($res['principal']['tipo'])) $res['principal']['tipo'] = $p;
                    }
                }
            }

            // Convertir accesorios temporales al formato final (Filtrando N/A)
            foreach ($accEncontrados as $tipo => $datos) {
                $s = trim($datos['serial'] ?? '');
                if (!empty($s) && $s !== 'N/A' && $s !== 'N.A') {
                    $res['accesorios'][] = [
                        'tipo'   => $tipo,
                        'serial' => $s,
                        'ref'    => $datos['referencia'] ?? ($datos['marca'] ?? '')
                    ];
                }
            }

            return ['success' => true, 'principal' => $res['principal'], 'accesorios' => $res['accesorios']];
        }
        return ['success' => false, 'msg' => 'No se pudo leer el archivo XLSX.'];
    }

    /**
     * Helper privado para decidir si el dato es del Activo Base o de un Accesorio
     */
    private static function asignarDatoInteligente(&$res, &$accs, $categoria, $campo, $valor, $primarios) {
        if (empty($valor) || strtoupper($valor) === 'N/A' || strtoupper($valor) === 'N.A') return;

        // Si la categoría detectada es de un activo principal
        $esPrincipal = false;
        foreach ($primarios as $p) { if ($categoria === $p) $esPrincipal = true; }

        if ($esPrincipal || empty($categoria)) {
            $res['principal'][$campo] = $valor;
            if (!empty($categoria)) $res['principal']['tipo'] = $categoria;
        } else {
            // Es un accesorio
            $accs[$categoria][$campo] = $valor;
        }
    }

    // Procesa el POST del formulario
    public static function store($postData) {
        try {
            $db = Database::conectar();
            $db->beginTransaction();

            // 1. Verificar/Crear Empleado
            $cod = $postData['cod_responsable'];
            $nom = $postData['nom_nuevo_empleado'];
            $id_area = $postData['id_area_nuevo'];

            if (!empty($nom) && !empty($id_area)) {
                $stmtEmp = $db->prepare("SELECT * FROM fun_create_empleado(:c, :n, :a)");
                $stmtEmp->execute([':c' => $cod, ':n' => $nom, ':a' => $id_area]);
            }

            // 2. Crear Activo Principal
            $stmtAct = $db->prepare("SELECT * FROM fun_create_activo(
                :ser, :qr, :host, :ref, :mac, :ip, :tipo, :marca, :mod, :est, :resp, :padre
            )");
            
            $qr = "QR-" . strtoupper(bin2hex(random_bytes(3)));

            $stmtAct->execute([
                ':ser'   => $postData['serial'],
                ':qr'    => $qr,
                ':host'  => $postData['hostname'] ?? null,
                ':ref'   => $postData['referencia'] ?? null,
                ':mac'   => $postData['mac_activo'] ?? null,
                ':ip'    => $postData['ip_equipo'] ?? null,
                ':tipo'  => $postData['id_tipoequi'],
                ':marca' => $postData['id_marca'],
                ':mod'   => !empty($postData['id_modelo']) ? $postData['id_modelo'] : null,
                ':est'   => $postData['estado'],
                ':resp'  => $cod,
                ':padre' => !empty($postData['id_padre_activo']) ? $postData['id_padre_activo'] : null
            ]);

            $resPrincipal = $stmtAct->fetch();
            $id_padre_nuevo = $resPrincipal['id_res'];

            // 3. Procesar Accesorios (Heredan Marca y Responsable)
            if (!empty($postData['accesorios_json_final']) && $id_padre_nuevo > 0) {
                $accesorios = json_decode($postData['accesorios_json_final'], true);
                foreach ($accesorios as $acc) {
                    $qr_acc = "QR-" . strtoupper(bin2hex(random_bytes(3)));
                    $stmtAcc = $db->prepare("SELECT * FROM fun_create_activo(:ser, :qr, NULL, :ref, NULL, NULL, :tipo, :marca, NULL, :est, :resp, :padre)");
                    $stmtAcc->execute([
                        ':ser'   => !empty($acc['serial']) ? $acc['serial'] : 'S/N-GEN-' . strtoupper(bin2hex(random_bytes(2))),
                        ':qr'    => $qr_acc,
                        ':ref'   => !empty($acc['referencia']) ? $acc['referencia'] : $acc['tipo_nombre'],
                        ':tipo'  => $acc['tipo_id'],
                        ':marca' => $postData['id_marca'],
                        ':est'   => 'Bueno',
                        ':resp'  => $cod,
                        ':padre' => $id_padre_nuevo
                    ]);
                }
            }

            $db->commit();
            header("Location: ../public/activos.php?msg=Activo registrado correctamente&tipo=success");
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            header("Location: ../public/crear_activo.php?msg=" . urlencode($e->getMessage()) . "&tipo=danger");
        }
        exit();
    }
}

// ROUTER DE ACCIONES
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] == 'analizar') {
        header('Content-Type: application/json');
        echo json_encode(ActivoController::analizarExcel($_FILES['file']['tmp_name']));
        exit();
    } else {
        ActivoController::store($_POST);
    }
}