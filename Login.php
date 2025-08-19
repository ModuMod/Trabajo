<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Inversiones</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Iniciar Sesión</h2>
        <form action="Validar.php" method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="clave" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <p>¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
    </div>
</div>
</body>
</html>
