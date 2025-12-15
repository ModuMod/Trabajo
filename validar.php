<?php
session_start();
include("conexion.php");

$usuario = $_POST['usuario'];
$clave = $_POST['clave'];

// ⚠️ OJO: esto es vulnerable a inyección SQL, lo mejor sería usar consultas preparadas.
// Pero te lo dejo adaptado a tu estilo actual:
$sql = "SELECT * FROM usuarios WHERE usuario='$usuario' AND clave='$clave'";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();

    // Guardar tanto el nombre de usuario como el id
    $_SESSION['usuario'] = $fila['usuario'];
    $_SESSION['id'] = $fila['id'];          // 👈 ahora también el id
    $_SESSION['user_id'] = $fila['id'];     // 👈 opcional, para compatibilidad

    header("Location: Principal.php");
    exit;
} else {
    echo "<script>alert('Usuario o contraseña incorrectos'); window.location='login.php';</script>";
}
?>
