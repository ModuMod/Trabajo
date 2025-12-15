<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION['id'] ?? null;
if (!$user_id) { header("Location: login.php"); exit; }

// Traer usuario
$stmt = $pdo->prepare("SELECT usuario, balance FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Traer categorías con activos (código sin cambios)
$sql = "SELECT c.id AS cat_id, c.nombre AS categoria, a.id AS activo_id, a.nombre AS activo, a.precio
        FROM categorias c
        JOIN activos a ON a.categoria_id = c.id
        ORDER BY c.nombre, a.nombre";
$rows = $pdo->query($sql)->fetchAll();

$categorias = [];
foreach ($rows as $r) {
    $categorias[$r['categoria']][] = $r;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activo_id = intval($_POST['activo_id']);
    $accion = $_POST['accion'];
    $cantidad = floatval($_POST['cantidad']);

    $stmt = $pdo->prepare("SELECT * FROM activos WHERE id=?");
    $stmt->execute([$activo_id]);
    $activo = $stmt->fetch();
    if (!$activo) die("Activo inválido");

    $costo = $cantidad * $activo['precio'];

    if ($accion === 'comprar') {
        if ($costo > $user['balance']) {
            $mensaje = "Saldo insuficiente.";
        } else {
            $pdo->beginTransaction();
            try {
                // 1. Actualizar Balance
                $pdo->prepare("UPDATE usuarios SET balance = balance - ? WHERE id=?")
                    ->execute([$costo, $user_id]);
                
                // 2. Actualizar Portafolio
                $pdo->prepare("INSERT INTO portafolio (user_id, activo_id, cantidad)
                               VALUES (?,?,?)
                               ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)")
                    ->execute([$user_id, $activo_id, $cantidad]);
                
                // 3. REGISTRAR MOVIMIENTO (¡NUEVO!)
                $descripcion = "Compra de " . number_format($cantidad, 4) . " unidades de {$activo['nombre']}";
                $pdo->prepare("INSERT INTO movimientos (user_id, tipo, monto, descripcion, fecha) 
                               VALUES (?, 'compra', ?, ?, NOW())")
                    ->execute([$user_id, $costo, $descripcion]);
                
                $pdo->commit();
                $mensaje = "Compraste $cantidad de {$activo['nombre']}";
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = "Error en la compra: " . $e->getMessage(); // Añadir mensaje de error para depuración
            }
        }
    } elseif ($accion === 'vender') {
        $stmt = $pdo->prepare("SELECT cantidad FROM portafolio WHERE user_id=? AND activo_id=?");
        $stmt->execute([$user_id, $activo_id]);
        $posee = $stmt->fetchColumn() ?: 0;
        if ($posee < $cantidad) {
            $mensaje = "No tienes suficientes unidades.";
        } else {
            $pdo->beginTransaction();
            try {
                // 1. Actualizar Balance
                $pdo->prepare("UPDATE usuarios SET balance = balance + ? WHERE id=?")
                    ->execute([$costo, $user_id]);
                
                // 2. Actualizar Portafolio
                $pdo->prepare("UPDATE portafolio SET cantidad = cantidad - ? WHERE user_id=? AND activo_id=?")
                    ->execute([$cantidad, $user_id, $activo_id]);
                
                // 3. Eliminar si la cantidad llega a cero
                $pdo->prepare("DELETE FROM portafolio WHERE user_id=? AND activo_id=? AND cantidad<=0")
                    ->execute([$user_id, $activo_id]);
                
                // 4. REGISTRAR MOVIMIENTO (¡NUEVO!)
                $descripcion = "Venta de " . number_format($cantidad, 4) . " unidades de {$activo['nombre']}";
                $pdo->prepare("INSERT INTO movimientos (user_id, tipo, monto, descripcion, fecha) 
                               VALUES (?, 'Venta', ?, ?, NOW())")
                    ->execute([$user_id, $costo, $descripcion]);

                $pdo->commit();
                $mensaje = "Vendiste $cantidad de {$activo['nombre']}";
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = "Error en la venta: " . $e->getMessage(); // Añadir mensaje de error para depuración
            }
        }
    }

    // Actualiza el balance del usuario para la vista (código sin cambios)
    $stmt = $pdo->prepare("SELECT balance FROM usuarios WHERE id=?");
    $stmt->execute([$user_id]);
    $user['balance'] = $stmt->fetchColumn();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invertir</title>
  <link rel="stylesheet" href="Estilos.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container section">
  <h2>Invertir</h2>
  <p>Saldo disponible: <strong>$<?= number_format($user['balance'],2) ?></strong></p>
  
  <?php if ($mensaje): ?>
    <div class="card"><p><?= htmlspecialchars($mensaje) ?></p></div>
  <?php endif; ?>

  <?php foreach ($categorias as $cat => $activos): ?>
    <div class="card" style="margin:18px 0;">
      <h3><?= htmlspecialchars($cat) ?></h3>
      <table class="table">
        <thead>
          <tr><th>Activo</th><th>Precio</th><th>Acción</th></tr>
        </thead>
        <tbody>
        <?php foreach ($activos as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['activo']) ?></td>
            <td>$<?= number_format($a['precio'],2) ?></td>
            <td>
              <form method="post" style="display:inline-flex;gap:6px;align-items:center;">
                <input type="hidden" name="activo_id" value="<?= $a['activo_id'] ?>">
                <input type="number" name="cantidad" min="0.01" step="0.01" style="width:80px;" required>
                <button class="btn btn-small" type="submit" name="accion" value="comprar">Comprar</button>
                <button class="btn btn-ghost btn-small" type="submit" name="accion" value="vender">Vender</button>
              </form>
            </td>
          </tr>
          <tr>
            <td colspan="3">
              <canvas id="chart-<?= $a['activo_id'] ?>" height="100"></canvas>
              <script>
              fetch('historico.php?id=<?= $a['activo_id'] ?>')
                .then(res => res.json())
                .then(data => {
                  const ctx = document.getElementById('chart-<?= $a['activo_id'] ?>').getContext('2d');
                  new Chart(ctx, {
                    type: 'line',
                    data: {
                      labels: data.map(d => new Date(d.fecha).toLocaleDateString('es-ES', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'})),
                      datasets: [{
                        data: data.map(d => d.precio),
                        borderColor: 'blue',
                        backgroundColor: 'rgba(0,0,255,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                      }]
                    },
                    options: {
                      plugins: {
                        legend: { display: false }
                      },
                      scales: {
                        x: {
                          ticks: { maxTicksLimit: 6, autoSkip: true }
                        }
                      }
                    }
                  });
                });
              </script>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>

  <div class="cta-row">
    <a href="billetera.php" class="btn">Ver Billetera</a>
    <a href="principal.php" class="btn btn-ghost">Volver</a>
  </div>
</div>
</body>
</html>