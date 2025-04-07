<?php
require_once 'app pilates/conexion.php';

$conn = Conexion::conectar();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $especialidad = $_POST['especialidad'];

    // Consulta preparada para obtener el tutor por email
    $sql = "SELECT * FROM tutores WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Obtener los datos del tutor
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la especialidad
        if ($row['especialidad'] === $especialidad) {
            // Guardar el ID y el nombre del tutor en sesión
            $_SESSION['tutor_id'] = $row['id_tutor'];
            $_SESSION['tutor'] = $row['nombre'];

            // Redirigir a la página de ver estudiantes
            header("Location: tutor_clases.php");
            exit();
        } else {
            echo "Especialidad incorrecta.";
        }
    } else {
        echo "No hay tutores con ese email.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/tutoreslogin.css">
    <title>Login Tutores</title>
</head>
<body>
    <div class="container">
        <h2>Login Tutores</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Correo" required>
            <input type="text" name="especialidad" placeholder="Especialidad" required>
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        <div class="links">
            <a href="registro.php">No soy tutor. Regresar</a>
        </div>
    </div>
</body>
</html>
