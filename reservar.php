<?php
//reservar.php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'app pilates/conexion.php';
date_default_timezone_set('America/Mexico_City');

$conn = Conexion::conectar();

// Semana y día seleccionado
if (isset($_GET['week'])) {
    $weekStart = new DateTime($_GET['week']);
} else {
    $today = new DateTime();
    if ($today->format('N') != 1) {
        $today->modify('last monday');
    }
    $weekStart = new DateTime($today->format('Y-m-d'));
}
$prevWeek    = (clone $weekStart)->modify('-7 days');
$nextWeek    = (clone $weekStart)->modify('+7 days');
$selectedDay = $_GET['day'] ?? $weekStart->format('Y-m-d');
$dayNumber   = date('N', strtotime($selectedDay));

$days_es = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mié', 'Thu' => 'Jue', 'Fri' => 'Vie', 'Sat' => 'Sáb', 'Sun' => 'Dom'];

// Obtener clases
$stmtC = $conn->prepare("SELECT * FROM clases ORDER BY id_clase");
$stmtC->execute();
$clases = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// Seleccionar la clase del día
$cls = $clases[($dayNumber - 1) % count($clases)];
$selectedClassId   = $cls['id_clase'];
$selectedClassName = $cls['nombre_clase'];
$capacity          = $cls['cupo_maximo'];
$duration          = $cls['duracion'];
$description       = $cls['descripcion'];

// Obtener tutores
$stmtT = $conn->prepare("SELECT * FROM tutores ORDER BY id_tutor");
$stmtT->execute();
$tutors = $stmtT->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservar Clase - Pilates</title>
    <link rel="stylesheet" href="styles/reservar.css">
</head>

<body>
    <div class="calendar-container">
        <header class="calendar-header">
            <h2><?php echo $weekStart->format('F Y'); ?></h2>
        </header>

        <section class="week-navigation">
            <a href="reservar.php?week=<?php echo $prevWeek->format('Y-m-d'); ?>&day=<?php echo (clone $prevWeek)->modify('+' . ($dayNumber - 1) . ' days')->format('Y-m-d'); ?>" class="week-arrow">&laquo;</a>
            <div class="week-days">
                <?php for ($i = 0; $i < 7; $i++):
                    $d    = (clone $weekStart)->modify("+{$i} days");
                    $abbr = $days_es[$d->format('D')];
                    $dStr = $d->format('Y-m-d');
                ?>
                    <a href="reservar.php?week=<?php echo $weekStart->format('Y-m-d'); ?>&day=<?php echo $dStr; ?>" class="day-item<?php if ($dStr === $selectedDay) echo ' active'; ?>">
                        <span class="day-name"><?php echo $abbr; ?></span>
                        <span class="day-number"><?php echo $d->format('d'); ?></span>
                    </a>
                <?php endfor; ?>
            </div>
            <a href="reservar.php?week=<?php echo $nextWeek->format('Y-m-d'); ?>&day=<?php echo (clone $nextWeek)->modify('+' . ($dayNumber - 1) . ' days')->format('Y-m-d'); ?>" class="week-arrow">&raquo;</a>
        </section>

        <section class="calendar-slots">
            <div class="slot-grid">
                <?php
                $cnt = 0;
                for ($h = 6; $h <= 19; $h++):
                    $cnt++;
                    $timeFmt = date('g:00 A', mktime($h, 0, 0));
                    $tutor   = $tutors[($cnt - 1) % count($tutors)];

                    // Contar reservas existentes
                    $stmtH = $conn->prepare("
            SELECT COUNT(r.id_reserva)
            FROM horarios h
            LEFT JOIN reservas r ON h.id_horario = r.id_horario
            WHERE h.id_clase = :cid
              AND DATE(h.fecha_hora) = :d
              AND TIME(h.fecha_hora) = :t
              AND r.id_reserva IS NOT NULL
          ");
                    $stmtH->execute([
                        ':cid' => $selectedClassId,
                        ':d'   => $selectedDay,
                        ':t'   => date('H:i:s', mktime($h, 0, 0))
                    ]);
                    $reserved  = (int)$stmtH->fetchColumn();
                    $available = max(0, $capacity - $reserved);
                ?>
                    <div class="slot-wrapper">
                        <div class="slot-time"><?php echo $timeFmt; ?></div>
                        <div class="slot-cell">
                            <div class="slot-details">
                                <span class="trainer"><?php echo $tutor['nombre']; ?></span>
                                <span class="class-type"><?php echo $selectedClassName; ?></span>
                                <span class="class-duration">Duración: <?php echo $duration; ?> min</span>
                                <span class="class-desc"><?php echo htmlspecialchars($description); ?></span>
                                <span class="available">Cupos: <?php echo $available; ?></span>
                            </div>
                            <button class="reserve-slot"
                                onclick="location.href='confirmar.php?day=<?php echo $selectedDay; ?>&time=<?php echo urlencode($timeFmt); ?>&tutor=<?php echo $tutor['id_tutor']; ?>&clase=<?php echo $selectedClassId; ?>'">
                                Reservar
                            </button>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>
    </div>
</body>

</html>