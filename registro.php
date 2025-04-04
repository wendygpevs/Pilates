<?php
require_once 'conexion.php'; // tu archivo de conexión
session_start();

$error = '';
$exito = '';

// Cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validaciones básicas
    if (!empty($email) && !empty($password)) {

        // Comprobar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Este correo ya está registrado.";
        } else {
            // Hashear la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar en la base de datos
            $stmt = $conn->prepare("INSERT INTO usuarios (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hashed_password);

            if ($stmt->execute()) {
                $exito = "Registro exitoso. Puedes iniciar sesión ahora.";
            } else {
                $error = "Error al registrar el usuario.";
            }
        }

        $stmt->close();
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>

<!-- HTML del formulario -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="registro.css"> <!-- Tu archivo CSS si tienes -->
</head>
<body>
    <div class="container">
        <h2>Crear Cuenta</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="exito"><?php echo $exito; ?></div>
        <?php endif; ?>

        <form method="POST" action="registro.php">
            <input type="email" name="email" placeholder="Correo Electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn">Registrarse</button>
        </form>
        <div class="links">
            <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>
</body>
</html>
