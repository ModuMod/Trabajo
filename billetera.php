<?php
// === 1. Configuración y Conexión a la BD (usando PDO) ===

// 1. INICIAR LA SESIÓN (¡NUEVO!)
session_start(); 
require_once 'db.php'; 

// 2. OBTENER ID DEL USUARIO DESDE LA SESIÓN (¡CORREGIDO!)
$user_id = $_SESSION['id'] ?? null; 

// 3. VERIFICAR LA SESIÓN (¡NUEVO!)
if (!$user_id) { 
    // Si no hay ID de usuario en la sesión, redirige al login o muestra un error.
    die("Error: Debes iniciar sesión para ver la billetera.");
    // Opcional: header("Location: login.php"); exit;
} 


// Inicializa variables
$usuario = null;
$activos = [];
$movimientos = [];
$valor_total_portafolio = 0; 

// --- CONSULTAS (El resto del código PHP se mantiene igual, usando la variable $user_id) ---

// 2. Consulta de Datos del Usuario
try {
    // La consulta ahora usa el ID de la sesión
    $sql_usuario = "SELECT id, usuario, email, balance, fecha_registro FROM usuarios WHERE id = :user_id";
    $stmt_usuario = $pdo->prepare($sql_usuario);
    $stmt_usuario->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_usuario->execute();
    $usuario = $stmt_usuario->fetch();

    if ($usuario === false) {
        // Este mensaje solo debería aparecer si el ID existe en sesión pero no en la BD (caso raro)
        die("Error: Usuario con ID $user_id no encontrado en la base de datos.");
    }
} catch (PDOException $e) {
    die("Error al consultar datos del usuario: " . $e->getMessage());
}

// 3. Consulta de Activos del Portafolio (Usa $user_id de la sesión)
try {
    $sql_activos = "
        SELECT 
            p.cantidad, 
            a.nombre AS activo_nombre, 
            a.precio AS precio_actual
        FROM 
            portafolio p
        JOIN 
            activos a ON p.activo_id = a.id
        WHERE 
            p.user_id = :user_id AND p.cantidad > 0
    ";
    $stmt_activos = $pdo->prepare($sql_activos);
    $stmt_activos->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_activos->execute();
    $activos = $stmt_activos->fetchAll();
    
} catch (PDOException $e) {
    die("Error al consultar activos: " . $e->getMessage());
}

// 4. Consulta del Historial de Transacciones (Usa $user_id de la sesión)
try {
    $sql_movimientos = "
        SELECT 
            tipo, 
            monto, 
            descripcion, 
            fecha
        FROM 
            movimientos
        WHERE 
            user_id = :user_id
        ORDER BY 
            fecha DESC
        LIMIT 5 
    "; 
    $stmt_movimientos = $pdo->prepare($sql_movimientos);
    $stmt_movimientos->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_movimientos->execute();
    $movimientos = $stmt_movimientos->fetchAll();
    
} catch (PDOException $e) {
    die("Error al consultar movimientos: " . $e->getMessage());
}

// Cálculo del Valor Total
foreach ($activos as $activo) { 
    $valor_total_portafolio += $activo['cantidad'] * $activo['precio_actual'];
}
$valor_neto_total = $valor_total_portafolio + $usuario['balance'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Billetera Detallada</title>
    <style>
        /* [Aquí va el CSS del tema oscuro que definimos antes] */
        /* Lo he omitido aquí por brevedad, pero debe ser el código completo de la respuesta anterior. */
    </style>
    <style>
        /* Paleta de colores Dark Mode (similar a Estilos.css) */
        :root {
            --bg-dark: #0b0d10;     /* Fondo principal */
            --panel-dark: #11151a;  /* Fondo de cajas/tarjetas */
            --text-light: #e6edf3;  /* Color del texto */
            --accent-blue: #4f8cff; /* Azul de acento/botón */
            --green-dark: #1e6e34;  /* Verde oscuro para balances */
        }
        
        /* Estilos ajustados al tema oscuro */
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: var(--bg-dark); 
            color: var(--text-light); 
        }
        
        /* Contenedor principal: Mismo color de fondo y sin sombra */
        .container { 
            max-width: 1000px; 
            margin: auto; 
            background: var(--bg-dark); 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: none; 
        }
        
        /* Cajas internas (Activos, Transacciones, Tarjeta de Usuario): Fondo más claro que el body */
        .section-box, .user-card { 
            background-color: var(--panel-dark); 
            padding: 20px; 
            border-radius: 8px; 
            border: 1px solid #1a1f26; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.2); 
        }
        
        /* Botón de navegación */
        .nav-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #1a1f26; padding-bottom: 15px; }
        .btn-principal {
            padding: 8px 15px;
            background-color: var(--accent-blue); 
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            font-size: 0.9em;
        }
        .btn-principal:hover {
            background-color: #3f7de0; 
        }
        
        /* Títulos */
        h1, h2, h3 { 
            color: var(--text-light); 
        }
        h2 { 
            border-bottom: 2px solid #1a1f26; 
            padding-bottom: 5px; 
            margin-top: 25px;
            font-size: 1.5em;
        }
        
        /* Layout */
        .grid-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 20px; }
        
        /* Tarjeta de Datos y Balances */
        .user-card strong { font-weight: bold; color: var(--accent-blue); }
        .user-card span:first-child { color: var(--text-light); } 
        
        /* Balance Total (Ajustado para dark mode) */
        .total-balance { 
            text-align: center; 
            background-color: var(--green-dark); 
            color: white; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            border: 1px solid #288f46;
        }
        .total-balance p { font-size: 1.8em; font-weight: bold; margin: 0; }
        
        /* Tablas */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #1a1f26; font-size: 1em; }
        th { background-color: var(--accent-blue); color: white; } 
        tr { background-color: var(--panel-dark); } 
        tr:nth-child(even) { background-color: #15191f; } 
        
        /* Colores de movimientos y valores (Asegurando que sean brillantes) */
        .movement-inversion { color: #ffc107; font-weight: bold; } 
        .movement-other { color: var(--accent-blue); font-weight: bold; }
        .asset-value { color: #7ee787; font-weight: bold; } 
    </style>
</head>
<body>
    <div class="container">
        
        <div class="nav-header">
            <h1> Billetera</h1>
            <a href="principal.php" class="btn-principal">
                Volver a Principal
            </a>
        </div>
        
        <div class="grid-layout">
            
            <div class="main-content">
                
                <div class="section-box">
                    <h2>Activos de Inversión</h2>
                    <?php if (count($activos) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Activo</th>
                                    <th>Cantidad</th>
                                    <th>Precio Actual </th>
                                    <th>Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activos as $activo): 
                                    $valor_activo = $activo['cantidad'] * $activo['precio_actual'];
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activo['activo_nombre']); ?></td>
                                        <td><?php echo number_format($activo['cantidad'], 4); ?></td>
                                        <td>$<?php echo number_format($activo['precio_actual'], 2); ?></td>
                                        <td class="asset-value">$<?php echo number_format($valor_activo, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Aún no tienes activos de inversión en tu portafolio.</p>
                    <?php endif; ?>
                </div>

                <br>
                
                <div class="section-box">
                    <h2> Historial de Transacciones </h2>
                    <?php if (count($movimientos) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Monto </th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos as $movimiento): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></td>
                                        <td class="<?php echo strtolower($movimiento['tipo']) === 'inversion' ? 'movement-inversion' : 'movement-other'; ?>">
                                            <?php echo htmlspecialchars(ucwords($movimiento['tipo'])); ?>
                                        </td>
                                        <td>$<?php echo number_format($movimiento['monto'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($movimiento['descripcion'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No se encontraron transacciones recientes.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar">
                
                <div class="total-balance">
                    <h3>VALOR TOTAL NETO</h3>
                    <p>$<?php echo number_format($valor_neto_total, 2); ?></p>
                </div>

                <div class="user-card">
                    <h2> Datos y Balances</h2>
                    <p>
                        <span>Usuario:</span> 
                        <strong><?php echo htmlspecialchars($usuario['usuario']); ?></strong>
                    </p>
                    <p>
                        <span>Email:</span> 
                        <strong><?php echo htmlspecialchars($usuario['email']); ?></strong>
                    </p>
                    <hr style="border-color: #1a1f26; margin: 15px 0;">
                    <p>
                        <span>Efectivo Disponible:</span> 
                        <strong>$<?php echo number_format($usuario['balance'], 2); ?></strong>
                    </p>
                    <p>
                        <span>Valor Activos:</span> 
                        <strong>$<?php echo number_format($valor_total_portafolio, 2); ?></strong>
                    </p>
                    <p>
                        <span>Miembro desde:</span> 
                        <span style="color:#8b95a7;"><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></span>
                    </p>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>