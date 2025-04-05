<?php
require_once 'app pilates/conexion.php'; // Adjusted path to conexion.php
session_start();

$error = '';
$exito = '';

// Establecer la conexión a la base de datos
$conn = Conexion::conectar(); // Get the database connection

// Cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);

    // Validaciones básicas
    if (!empty($nombre) && !empty($email) && !empty($password)) {

        // Comprobar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Este correo ya está registrado.";
        } else {
            // Hashear la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar en la base de datos
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contraseña, telefono, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$nombre, $email, $hashed_password, $telefono])) {
                $exito = "Registro exitoso. Puedes iniciar sesión ahora.";
                header("Location: index.php"); // Redirigir a index.php
                exit();
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Pilates</title>
    <link rel="stylesheet" href="styles/registro.css">
    <link rel="icon" href="imagenes/reform.ico" type="image/x-icon">
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="logo">Pilates</div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="login.php">Iniciar Sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <div class="form-box">
            <h2>Crear Cuenta</h2>

            <?php if (!empty($error)): ?>
                <div class="error" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($exito)): ?>
                <div class="exito" style="color: green; margin-bottom: 15px;"><?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST" action="registro.php">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <input type="text" name="telefono" placeholder="Teléfono (opcional)">
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" class="btn">Registrarse</button>
            </form>
            <div class="links">
                <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Pilates. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
