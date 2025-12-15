<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION['id'] ?? null;
if (!$user_id) { header("Location: login.php"); exit; }

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dias = intval($_POST['dias']);
    if ($dias > 0) {
        for ($i=0; $i<$dias; $i++) {
            $activos = $pdo->query("SELECT a.id, a.precio, c.nombre as categoria 
                                     FROM activos a 
                                     JOIN categorias c ON a.categoria_id=c.id")->fetchAll();
            foreach ($activos as $activo) {
                switch ($activo['categoria']) {
                    case 'Acciones': $min=-0.05; $max=0.05; break;
                    case 'ETFs': $min=-0.02; $max=0.02; break;
                    case 'Bonos': $min=-0.005; $max=0.005; break;
                    case 'Fondos Comunes': $min=-0.01; $max=0.015; break;
                    default: $min=-0.02; $max=0.02;
                }

                // variación aleatoria
                $pct = mt_rand($min*10000,$max*10000)/10000;
                $nuevo = $activo['precio'] * (1 + $pct);

                // actualizar en activos
                $pdo->prepare("UPDATE activos SET precio=? WHERE id=?")
                    ->execute([$nuevo, $activo['id']]);

                // fecha simulada (+$i días desde hoy)
                $fecha = date("Y-m-d H:i:s", strtotime("+$i day"));

                // guardar en historial
                $pdo->prepare("INSERT INTO historial_precios (activo_id, precio, fecha) VALUES (?, ?, ?)")
                    ->execute([$activo['id'], $nuevo, $fecha]);
            }
        }
        $mensaje = "Simulación de $dias días completada.";
    }
}

// Traer precios actuales para mostrar
$sql = "SELECT c.nombre as categoria, a.id as activo_id, a.nombre, a.precio 
        FROM activos a 
        JOIN categorias c ON a.categoria_id=c.id 
        ORDER BY c.nombre,a.nombre";
$rows = $pdo->query($sql)->fetchAll();

// Agrupar por categoría
$categorias = [];
foreach ($rows as $r) {
    $categorias[$r['categoria']][] = $r;
}

// Asegurar histórico inicial
foreach ($rows as $r) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM historial_precios WHERE activo_id=?");
    $check->execute([$r['activo_id']]);
    if ($check->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO historial_precios (activo_id, precio, fecha) VALUES (?, ?, NOW())")
            ->execute([$r['activo_id'], $r['precio']]);
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Simulación</title>
<link rel="stylesheet" href="Estilos.css">
<style>
.hidden { display: none; }
.category-btns { margin: 20px 0; display:flex; gap:12px; flex-wrap: wrap;}
.category-section { margin-top:20px; }
.chart-container { height:200px; margin-top:10px; }
</style>
</head>
<body>
<div class="container section">
  <h2>Simulación de mercado</h2>
  <?php if ($mensaje): ?><div class="card"><p><?= $mensaje ?></p></div><?php endif; ?>
  
  <form method="post">
    <label>Días a simular:</label>
    <input type="number" name="dias" min="1" max="365" value="10" required>
    <button class="btn" type="submit">Simular</button>
  </form>

  <div class="category-btns">
    <?php foreach(array_keys($categorias) as $cat): ?>
      <button type="button" class="btn" onclick="showCategory('<?= $cat ?>')"><?= $cat ?></button>
    <?php endforeach; ?>
  </div>

  <?php $charts = []; ?>
  <?php foreach ($categorias as $cat => $activos): ?>
    <div id="cat-<?= $cat ?>" class="category-section hidden">
      <div class="card">
        <h3><?= htmlspecialchars($cat) ?></h3>
        <table class="table">
          <thead><tr><th>Activo</th><th>Precio</th><th>Histórico</th></tr></thead>
          <tbody>
          <?php foreach($activos as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['nombre']) ?></td>
              <td>$<?= number_format($a['precio'],2) ?></td>
              <td>
                <div class="chart-container">
                  <canvas id="chart-<?= $a['activo_id'] ?>"></canvas>
                </div>
                <?php $charts[] = $a['activo_id']; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>

  <a href="principal.php" class="btn btn-ghost">Volver</a>
</div>

<script>
function showCategory(cat) {
  document.querySelectorAll('.category-section').forEach(el => el.classList.add('hidden'));
  document.getElementById('cat-'+cat).classList.remove('hidden');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  <?php foreach($charts as $id): ?>
    renderChart("chart-<?= $id ?>", <?= $id ?>);
  <?php endforeach; ?>
});
</script>
</body>
</html>
