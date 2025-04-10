<?php
require_once 'app pilates/conexion.php';
session_start();

$error = '';
$exito = '';

// Establecer la conexión a la base de datos
$conn = Conexion::conectar();

// Obtener todas las clases disponibles
$stmt_clases = $conn->prepare("SELECT id_clase, nombre_clase FROM clases ORDER BY nombre_clase");
$stmt_clases->execute();
$clases = $stmt_clases->fetchAll(PDO::FETCH_ASSOC);

// Cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Variables para la clase
    $clase_opcion = $_POST['clase_opcion'];
    $id_clase = 0;
    $nombre_clase = '';
    $descripcion = '';
    $duracion = 0;
    $cupo_maximo = 0;
    
    // Validar campos principales
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Por favor completa todos los campos obligatorios del tutor.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Comprobar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT id_tutor FROM tutores WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Este correo ya está registrado.";
        } else {
            // Validar opciones de clase
            if ($clase_opcion === 'existente') {
                $id_clase = isset($_POST['id_clase']) ? (int)$_POST['id_clase'] : 0;
                if ($id_clase <= 0) {
                    $error = "Por favor selecciona una clase existente.";
                }
            } else if ($clase_opcion === 'nueva') {
                $nombre_clase = trim($_POST['nombre_clase']);
                $descripcion = trim($_POST['descripcion']);
                $duracion = isset($_POST['duracion']) ? (int)$_POST['duracion'] : 0;
                $cupo_maximo = isset($_POST['cupo_maximo']) ? (int)$_POST['cupo_maximo'] : 0;
                
                if (empty($nombre_clase) || $duracion <= 0 || $cupo_maximo <= 0) {
                    $error = "Por favor completa todos los campos de la nueva clase.";
                }
            } else {
                $error = "Por favor selecciona una opción válida para la clase.";
            }
            
            // Si no hay errores, continuar con el registro
            if (empty($error)) {
                try {
                    $conn->beginTransaction();
                    
                    // Hash de la contraseña antes de guardarla
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertar tutor
                    $stmt = $conn->prepare("INSERT INTO tutores (nombre, email, telefono, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nombre, $email, $telefono, $password_hash]);
                    $tutor_id = $conn->lastInsertId();
                    
                    // Procesar la clase según la opción seleccionada
                    if ($clase_opcion === 'existente') {
                        // Actualizar la clase existente
                        $stmt_update = $conn->prepare("UPDATE clases SET id_tutor = ? WHERE id_clase = ?");
                        $stmt_update->execute([$tutor_id, $id_clase]);
                    } else {
                        // Crear una nueva clase
                        $stmt_clase = $conn->prepare("INSERT INTO clases (id_tutor, nombre_clase, descripcion, duracion, cupo_maximo) VALUES (?, ?, ?, ?, ?)");
                        $stmt_clase->execute([$tutor_id, $nombre_clase, $descripcion, $duracion, $cupo_maximo]);
                    }
                    
                    // Establecer la sesión de usuario
                    $_SESSION['tutor'] = $nombre;  // Cambiado de 'nombre' a 'tutor' para consistencia
                    $_SESSION['tutor_id'] = $tutor_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['tipo_usuario'] = 'tutor';
                    
                    $conn->commit();
                    header("Location: tutor_clases.php");
                    exit();
                    
                } catch (PDOException $e) {
                    $conn->rollBack();
                    $error = "Error en la base de datos: " . $e->getMessage();
                }
            }
        }
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
    <style>
        .hidden {
            display: none;
        }
        .form-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .radio-group {
            margin-bottom: 15px;
        }
        .radio-group label {
            margin-right: 15px;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .required:after {
            content: ' *';
            color: red;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="logo">Pilates</div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="login_tutores.php">Iniciar Sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <div class="form-box">
            <h2>Registrar Tutor</h2>

            <?php if (!empty($error)): ?>
                <div class="error" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="registrotutores.php">
                <!-- Sección de información del tutor -->
                <div class="form-section">
                    <h3>Información del Tutor</h3>
                    <label for="nombre" class="required">Nombre</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" required value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                    
                    <label for="email" class="required">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" placeholder="Teléfono de contacto" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                    
                    <label for="password" class="required">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <p class="password-requirements">La contraseña debe tener al menos 6 caracteres</p>
                    
                    <label for="confirm_password" class="required">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required>
                </div>
                
                <!-- Sección de clases -->
                <div class="form-section">
                    <h3>Información de la Clase</h3>
                    
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="clase_opcion" value="existente" <?php echo (!isset($_POST['clase_opcion']) || $_POST['clase_opcion'] === 'existente') ? 'checked' : ''; ?>> Seleccionar clase existente
                        </label>
                        <label>
                            <input type="radio" name="clase_opcion" value="nueva" <?php echo (isset($_POST['clase_opcion']) && $_POST['clase_opcion'] === 'nueva') ? 'checked' : ''; ?>> Crear nueva clase
                        </label>
                    </div>
                    
                    <!-- Selector de clase existente -->
                    <div id="clase-existente" <?php echo (isset($_POST['clase_opcion']) && $_POST['clase_opcion'] === 'nueva') ? 'class="hidden"' : ''; ?>>
                        <label for="id_clase" class="required">Clase</label>
                        <select name="id_clase" id="id_clase">
                            <option value="">-- Selecciona una clase --</option>
                            <?php foreach ($clases as $clase): ?>
                                <?php 
                                // Verificar si la clase ya tiene tutor asignado
                                $stmt_check = $conn->prepare("SELECT id_tutor FROM clases WHERE id_clase = ? AND id_tutor IS NOT NULL");
                                $stmt_check->execute([$clase['id_clase']]);
                                $tiene_tutor = $stmt_check->rowCount() > 0;
                                
                                $selected = (isset($_POST['id_clase']) && $_POST['id_clase'] == $clase['id_clase']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $clase['id_clase']; ?>" <?php echo $tiene_tutor ? 'disabled' : $selected; ?>>
                                    <?php echo htmlspecialchars($clase['nombre_clase']); ?>
                                    <?php echo $tiene_tutor ? ' (Ya asignada)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Formulario para nueva clase -->
                    <div id="clase-nueva" <?php echo (!isset($_POST['clase_opcion']) || $_POST['clase_opcion'] !== 'nueva') ? 'class="hidden"' : ''; ?>>
                        <label for="nombre_clase" class="required">Nombre de la clase</label>
                        <input type="text" id="nombre_clase" name="nombre_clase" placeholder="Nombre de la clase" value="<?php echo isset($_POST['nombre_clase']) ? htmlspecialchars($_POST['nombre_clase']) : ''; ?>">
                        
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" placeholder="Descripción detallada de la clase" rows="3"><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                        
                        <label for="duracion" class="required">Duración (minutos)</label>
                        <input type="number" id="duracion" name="duracion" placeholder="Duración en minutos" min="1" value="<?php echo isset($_POST['duracion']) ? (int)$_POST['duracion'] : ''; ?>">
                        
                        <label for="cupo_maximo" class="required">Cupo máximo</label>
                        <input type="number" id="cupo_maximo" name="cupo_maximo" placeholder="Número máximo de alumnos" min="1" value="<?php echo isset($_POST['cupo_maximo']) ? (int)$_POST['cupo_maximo'] : ''; ?>">
                    </div>
                </div>
                
                <p><small>Los campos marcados con * son obligatorios</small></p>
                <button type="submit" class="btn">Registrar Tutor</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Pilates. Todos los derechos reservados.</p>
    </footer>
    
    <script>
        // Script para alternar entre selector de clase y formulario de nueva clase
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('input[name="clase_opcion"]');
            const claseExistenteDiv = document.getElementById('clase-existente');
            const claseNuevaDiv = document.getElementById('clase-nueva');
            
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'existente') {
                        claseExistenteDiv.classList.remove('hidden');
                        claseNuevaDiv.classList.add('hidden');
                    } else {
                        claseExistenteDiv.classList.add('hidden');
                        claseNuevaDiv.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>