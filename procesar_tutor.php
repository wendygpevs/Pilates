<?php
include("../app pilates/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $especialidad = $_POST['especialidad'];
    $experiencia = $_POST['experiencia'];

    $sql = "INSERT INTO tutores (nombre, email, telefono, especialidad, experiencia) 
            VALUES ('$nombre', '$email', '$telefono', '$especialidad', '$experiencia')";

    if ($conn->query($sql) === TRUE) {
        echo "Tutor registrado correctamente.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
