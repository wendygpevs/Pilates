<?php
session_start();

// Verificar que el tutor esté logueado
if (!isset($_SESSION['tutor']) || !isset($_SESSION['tutor_id'])) {
    header("Location: tutoreslogin.php");
    exit();
}

require_once 'app pilates/conexion.php';
$conn = Conexion::conectar();

// Función para obtener alumnos por día a partir de la base de datos
function alumnosPorDia($dia_nombre, $conn, $tutor_id) {
    $alumnos = [];
    $sql = "
        SELECT DISTINCT u.nombre 
        FROM usuarios u
        INNER JOIN reservas r ON u.id_usuario = r.id_usuario
        INNER JOIN horarios h ON r.id_horario = h.id_horario
        INNER JOIN clases c ON h.id_clase = c.id_clase
        WHERE DAYNAME(h.fecha_hora) = ? AND c.id_tutor = ?
        ORDER BY u.nombre
    ";
    // Usamos PDO para preparar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $dia_nombre);
    $stmt->bindParam(2, $tutor_id, PDO::PARAM_INT);
    $stmt->execute();
    $alumnos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $alumnos;
}
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
        </nav>
    </header>

    <main>
        <h1 class="titulo">PILATES REFORM</h1>
        <p class="info">Clases asignadas al tutor: <?php echo htmlspecialchars($_SESSION['tutor']); ?></p>

        <section class="clases-contenedor">
            <!-- LUNES -->
            <div class="clase-box">
                <h2>Lunes</h2>
                <p>Horario</p>
                <p><strong>:</strong></p>
                <ol>
                    <?php foreach (alumnosPorDia("Monday", $conn, $_SESSION['tutor_id']) as $nombre): ?>
                        <li><?php echo htmlspecialchars($nombre); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <!-- MIÉRCOLES -->
            <div class="clase-box">
                <h2>Miércoles</h2>
                <p>Horario</p>
                <p><strong>:</strong></p>
                <ol>
                    <?php foreach (alumnosPorDia("Wednesday", $conn, $_SESSION['tutor_id']) as $nombre): ?>
                        <li><?php echo htmlspecialchars($nombre); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <!-- VIERNES -->
            <div class="clase-box">
                <h2>Viernes</h2>
                <p>Horario</p>
                <p><strong>:</strong></p>
                <ol>
                    <?php foreach (alumnosPorDia("Friday", $conn, $_SESSION['tutor_id']) as $nombre): ?>
                        <li><?php echo htmlspecialchars($nombre); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>
    </main>
</body>
</html>
