<?php
session_start();

// Cerrar sesión anterior si existía
session_unset();
session_destroy();
session_start();

require_once 'app pilates/conexion.php';
$conn = Conexion::conectar();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Por favor ingresa email y contraseña.";
    } else {
        try {
            // Asegúrate de que el campo 'email' sea único en la base de datos
            $stmt = $conn->prepare("SELECT id_tutor, nombre, password FROM tutores WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $tutor = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($tutor) {
                // Verifica la contraseña con password_verify (requiere que esté encriptada con password_hash)
                if (password_verify($password, $tutor['password'])) {
                    $_SESSION['tutor'] = $tutor['nombre'];
                    $_SESSION['tutor_id'] = $tutor['id_tutor'];
                    $_SESSION['tipo_usuario'] = 'tutor';

                    header("Location: tutor_clases.php");
                    exit();
                } else {
                    $error = "Contraseña incorrecta.";
                }
            } else {
                $error = "No existe un tutor con ese correo.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Tutores - Pilates</title>
    <link rel="stylesheet" href=tutoreslogin.css>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .error {
            color: #d9534f;
            background-color: #f2dede;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #ebccd1;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color:rgb(17, 31, 221);
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color:rgb(17, 31, 221);
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        a {
            color:rgb(217, 238, 183);
            color: red;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión - Tutores</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Ingresar</button>
        </form>
        <div class="register-link">
            ¿No tienes cuenta? <a href="registrotutores.php">Regístrate aquí</a>
        </div>
    </div>
</body>
</html>
