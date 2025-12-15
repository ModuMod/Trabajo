<?php
require_once 'db.php';

$activo_id = intval($_GET['id'] ?? 0);
if (!$activo_id) {
    echo json_encode([]);
    exit;
}

// obtener precio actual del activo (fallback)
$stmtAct = $pdo->prepare("SELECT precio FROM activos WHERE id = ?");
$stmtAct->execute([$activo_id]);
$precio_activo = $stmtAct->fetchColumn();
if ($precio_activo === false) $precio_activo = null;

// contar registros
$stmt = $pdo->prepare("SELECT COUNT(*) FROM historial_precios WHERE activo_id=?");
$stmt->execute([$activo_id]);
$total = intval($stmt->fetchColumn());

// si hay muchos registros, tomamos el precio de cierre por día (últimos 90 días)
// si no, traemos los últimos 200 registros crudos
if ($total > 200) {
    // obtener el último registro de cada día (precio de cierre diario) - hasta 90 días
    $sql = "
        SELECT DATE(h.fecha) AS fecha, h.precio
        FROM (
            SELECT DATE(fecha) as day, MAX(fecha) AS maxf
            FROM historial_precios
            WHERE activo_id = ?
            GROUP BY DATE(fecha)
            ORDER BY maxf DESC
            LIMIT 90
        ) m
        JOIN historial_precios h
          ON h.activo_id = ? AND h.fecha = m.maxf
        ORDER BY h.fecha DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$activo_id, $activo_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // traer los últimos 200 registros (más recientes)
    $sql = "
        SELECT DATE_FORMAT(fecha, '%Y-%m-%dT%H:%i:%s') AS fecha, precio
        FROM historial_precios
        WHERE activo_id = ?
        ORDER BY fecha DESC
        LIMIT 200
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$activo_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// invertir para que queden del más viejo al más nuevo
$data = array_reverse($data);

// Si no hay datos en historial, devolvemos el precio actual como único punto
if (empty($data)) {
    $ts = date("Y-m-d\TH:i:s");
    $data = [[ 'fecha' => $ts, 'precio' => ($precio_activo !== null ? (float)$precio_activo : 0) ]];
} else {
    // normalizar formatos de fecha: si la consulta devolvió DATE (ej: '2025-11-07') convertir a ISO (agregar hora)
    foreach ($data as &$row) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['fecha'])) {
            // fecha sin hora -> añadir hora 00:00:00 para que Chart.js lo interprete
            $row['fecha'] = $row['fecha'] . 'T00:00:00';
        } elseif (strpos($row['fecha'], 'T') === false) {
            // si viene 'YYYY-MM-DD HH:MM:SS' -> convertir a ISO con T
            $row['fecha'] = str_replace(' ', 'T', $row['fecha']);
        }
        // asegurar tipo numérico
        $row['precio'] = (float)$row['precio'];
    }
    unset($row);

    // comprobar último punto vs precio actual: si difieren, anexar el precio actual con timestamp NOW()
    if ($precio_activo !== null) {
        $last = end($data);
        $last_precio = isset($last['precio']) ? (float)$last['precio'] : null;

        // comparar con cierta tolerancia (por ejemplo centavos)
        $epsilon = 0.001;
        if ($last_precio === null || abs($last_precio - (float)$precio_activo) > $epsilon) {
            $ts_now = date("Y-m-d\TH:i:s");
            $data[] = ['fecha' => $ts_now, 'precio' => (float)$precio_activo];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($data);
