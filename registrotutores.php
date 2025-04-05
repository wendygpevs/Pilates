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
    $especialidad = trim($_POST['especialidad']);

    // Validaciones básicas
    if (!empty($nombre) && !empty($email)) {

        // Comprobar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT id_tutor FROM tutores WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Este correo ya está registrado.";
        } else {
            // Insertar en la base de datos
            $stmt = $conn->prepare("INSERT INTO tutores (nombre, email, telefono, especialidad) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nombre, $email, $telefono, $especialidad])) {
                $exito = "Registro exitoso. Puedes iniciar sesión ahora.";
                header("Location: index.php"); // Redirigir a index.php
                exit();
            } else {
                $error = "Error al registrar el tutor.";
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
    <title>Registro Tutores - Pilates</title>
    <link rel="stylesheet" href="styles/registrotutores.css">
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
            <h2>Registrar Tutor</h2>

            <?php if (!empty($error)): ?>
                <div class="error" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($exito)): ?>
                <div class="exito" style="color: green; margin-bottom: 15px;"><?php echo $exito; ?></div>
            <?php endif; ?>

            <form method="POST" action="registrotutores.php">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <input type="text" name="telefono" placeholder="Teléfono (opcional)">
                <input type="text" name="especialidad" placeholder="Especialidad" required>
                <button type="submit" class="btn">Registrar</button>
            </form>
            
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Pilates. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
