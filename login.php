<?php
session_start();
require_once 'conexion.php'; // Tu archivo de conexión

// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe los datos del formulario
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Verifica que no estén vacíos
    if (!empty($email) && !empty($password)) {
        // Prepara la consulta para evitar inyecciones SQL
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // Verifica el password con password_verify
            if (password_verify($password, $usuario['password'])) {
                // Autenticación correcta: inicia sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['email'] = $email;

                // Redirecciona a página principal
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El correo no está registrado.";
        }

        $stmt->close();
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<form method="POST" action="login.php">
    <input type="email" name="email" placeholder="Correo Electrónico" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit" class="btn">→ INGRESAR</button>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
</form>
