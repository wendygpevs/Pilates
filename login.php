<?php
session_start();
require_once 'app pilates/conexion.php'; // Adjusted path to conexion.php

// Establecer la conexión a la base de datos
$conn = Conexion::conectar(); // Get the database connection

// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe los datos del formulario
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Verifica que no estén vacíos
    if (!empty($email) && !empty($password)) {
        // Prepara la consulta para evitar inyecciones SQL
        $stmt = $conn->prepare("SELECT id_usuario, contraseña FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $resultado = $stmt->fetch();

        if ($resultado) {
            // Verifica el password con password_verify
            if (password_verify($password, $resultado['contraseña'])) {
                // Autenticación correcta: inicia sesión
                $_SESSION['usuario_id'] = $resultado['id_usuario'];
                $_SESSION['email'] = $email;

                // Redirecciona a página principal
                header("Location: index.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El correo no está registrado.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/login.css">
    <title>Iniciar Sesión</title>
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Correo Electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn">→ INGRESAR</button>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
        </form>
        <div class="links">
            <a href="registro.php" >No tengo cuenta. Registrarme</a>
        </div>
    </div>
</body>
