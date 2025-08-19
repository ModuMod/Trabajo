<?php
session_start();
include("conexion.php");

$usuario = $_POST['usuario'];
$clave = $_POST['clave'];

$sql = "SELECT * FROM usuarios WHERE usuario='$usuario' AND clave='$clave'";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $_SESSION['usuario'] = $usuario;
    header("Location: Principal.php");
} else {
    echo "<script>alert('Usuario o contraseña incorrectos'); window.location='login.php';</script>";
}
?>
