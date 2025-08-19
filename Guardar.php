<?php
include("conexion.php");

$usuario = $_POST['usuario'];
$email = $_POST['email'];
$clave = $_POST['clave'];

$sql = "INSERT INTO usuarios (usuario, email, clave) VALUES ('$usuario', '$email', '$clave')";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Registro exitoso'); window.location='login.php';</script>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
