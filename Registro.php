<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Inversiones</title>
    <link rel="stylesheet" href="Estilo.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Crear Cuenta</h2>
        <form action="Guardar.php" method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="clave" placeholder="Contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
</div>
</body>
</html>
