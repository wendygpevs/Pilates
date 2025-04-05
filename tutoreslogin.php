<?php
require_once 'app pilates/conexion.php';

// Create connection
$conn = Conexion::conectar();

// Start session
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $especialidad = $_POST['especialidad']; // Get the specialty from the form

    // SQL query to check if the email exists
    $sql = "SELECT * FROM tutores WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->rowCount() > 0) {
        // Fetch the tutor's data
        $row = $result->fetch(PDO::FETCH_ASSOC);
        
        // Verify the specialty
        if ($row['especialidad'] === $especialidad) {
            $_SESSION['tutor_id'] = $row['id_tutor'];
            header("Location: index.php"); // Redirect to the home page
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
