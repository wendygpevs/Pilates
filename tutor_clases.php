<?php
session_start();

// Verificar que el tutor esté logueado
if (!isset($_SESSION['tutor'])) {
    header("Location: tutoreslogin.php");
    exit();
}

require_once 'app pilates/conexion.php';
$conn = Conexion::conectar();

/**
 * Función que obtiene solo el nombre del alumno
 */
function alumnosPorDia($dia_nombre, $conn, $tutor_id) {
    $dia_numero = 0;
    switch($dia_nombre) {
        case "Monday": $dia_numero = 2; break;
        case "Wednesday": $dia_numero = 4; break;
        case "Friday": $dia_numero = 6; break;
    }
    
    $sql = "
        SELECT u.nombre, u.id_usuario
        FROM usuarios u
        INNER JOIN reservas r ON u.id_usuario = r.id_usuario
        INNER JOIN horarios h ON r.id_horario = h.id_horario
        INNER JOIN clases c ON h.id_clase = c.id_clase
        WHERE DAYOFWEEK(h.fecha_hora) = ? AND c.id_tutor = ?
        ORDER BY u.nombre
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $dia_numero, PDO::PARAM_INT);
    $stmt->bindParam(2, $tutor_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->errorCode() != '00000') {
        $errorInfo = $stmt->errorInfo();
        error_log("Error en la consulta SQL (alumnosPorDia): " . $errorInfo[2]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Función para obtener reservas (id_reserva, nombre del alumno y fecha/hora)
 * para un día específico vinculado al tutor.
 */
function reservasPorDia($dia_nombre, $conn, $tutor_id) {
    $dia_numero = 0;
    switch($dia_nombre) {
        case "Monday": $dia_numero = 2; break;
        case "Wednesday": $dia_numero = 4; break;
        case "Friday": $dia_numero = 6; break;
    }

    $sql = "
        SELECT r.id_reserva, u.nombre, h.fecha_hora
        FROM usuarios u
        INNER JOIN reservas r ON u.id_usuario = r.id_usuario
        INNER JOIN horarios h ON r.id_horario = h.id_horario
        WHERE DAYOFWEEK(h.fecha_hora) = ? AND h.id_tutor = ?
        ORDER BY h.fecha_hora, u.nombre
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $dia_numero, PDO::PARAM_INT);
    $stmt->bindParam(2, $tutor_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Función para obtener los horarios asignados al tutor para un día específico.
 */
function obtenerHorariosTutor($conn, $tutor_id, $dia_numero) {
    $sql = "
        SELECT TIME_FORMAT(TIME(h.fecha_hora), '%H:%i') AS hora
        FROM horarios h
        WHERE h.id_tutor = ? AND DAYOFWEEK(h.fecha_hora) = ?
        GROUP BY hora
        ORDER BY hora
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $tutor_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $dia_numero, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}


// Mapeo de días para mostrar horarios (MySQL: 1 = Domingo, 2 = Lunes, etc.)
$dias_numero = [
    "Monday"    => 2,
    "Wednesday" => 4,
    "Friday"    => 6
];

// Obtener el ID del tutor desde la sesión
$tutor_id = $_SESSION['tutor_id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tutor Clase - Pilates</title>
    <link rel="stylesheet" href="styles/tutor.css">
</head>
<body>
    <header class="header">
        <div class="logo">Reform <span class="sub">estudio de pilates</span></div>
        <nav class="nav">
            <a href="index.php">Home</a>
            <a href="reservar.php">Reserve</a>
            <span class="tutor-nombre">
                Bienvenido/a, <?php echo htmlspecialchars($_SESSION['tutor']); ?>
                <i class="icono-user"></i>
            </span>
            <a href="login.php">Cerrar Sesión</a>
        </nav>
    </header>

    <main>
        <h1 class="titulo">PILATES REFORM</h1>
        <p class="info">Clases asignadas al tutor: <?php echo htmlspecialchars($_SESSION['tutor']); ?></p>

        <section class="clases-contenedor">
            <?php
            // Usamos un array con el mapeo para recorrer los días de la semana y sus etiquetas en español
            $dias = [
                'Monday' => 'Lunes',
                'Wednesday' => 'Miércoles',
                'Friday' => 'Viernes'
            ];

            foreach ($dias as $clave => $nombreDia) {
                // Obtener el número de día a partir del mapeo
                $numeroDia = $dias_numero[$clave];

                // Obtener los horarios asignados al tutor para el día correspondiente
                $horarios = obtenerHorariosTutor($conn, $tutor_id, $numeroDia);

                // Obtener las reservas para el día
                $reservas = reservasPorDia($clave, $conn, $tutor_id);
            ?>
                <div class="clase-box">
                    <h2><?php echo $nombreDia; ?></h2>
                    <p>Horario</p>
                    <p><strong>
                        <?php echo !empty($horarios) ? implode(', ', $horarios) : 'No hay horarios asignados'; ?>
                    </strong></p>
                    <ol>
                        <?php 
                        if (empty($reservas)) {
                            echo "<li>No hay alumnos registrados</li>";
                        } else {
                            foreach ($reservas as $reserva):
                                // Formatear la hora de la reserva
                                $hora = date("H:i", strtotime($reserva['fecha_hora']));
                        ?>
                            <li>
                                <a href="detalle_reserva.php?reserva=<?php echo $reserva['id_reserva']; ?>">
                                    <?php echo htmlspecialchars($reserva['nombre']) . " - " . $hora; ?>
                                </a>
                            </li>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </ol>
                </div>
            <?php } ?>
        </section>
    </main>
</body>
</html>
