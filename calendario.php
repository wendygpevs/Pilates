<?php
// calendario.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('America/Mexico_City');
require_once 'app pilates/conexion.php';

// Conectar
$conn = Conexion::conectar();

// Obtener la semana actual desde GET o calcular la actual (el lunes de la semana actual)
if (isset($_GET['week'])) {
    $weekStart = new DateTime($_GET['week']);
} else {
    $today = new DateTime();
    if ($today->format('N') != 1) {
        $today->modify('last monday');
    }
    $weekStart = new DateTime($today->format('Y-m-d'));
}

// Calcular la semana anterior y siguiente
$prevWeek = clone $weekStart;
$prevWeek->modify('-7 days');

$nextWeek = clone $weekStart;
$nextWeek->modify('+7 days');

// Array de abreviaturas en español para los días
$days_es = array(
    "Mon" => "Lun",
    "Tue" => "Mar",
    "Wed" => "Mié",
    "Thu" => "Jue",
    "Fri" => "Vie",
    "Sat" => "Sáb",
    "Sun" => "Dom"
);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendario de Reservas</title>
    <link rel="stylesheet" href="styles/calendario.css">
    <script>
        // Función para actualizar la sección de franjas horarias
        function fetchSchedule(selectedDay) {
            fetch('getSchedule.php?day=' + selectedDay)
                .then(response => response.json())
                .then(data => {
                    const slotGrid = document.querySelector('.slot-grid');
                    slotGrid.innerHTML = '';
                    data.forEach(slot => {
                        const cell = document.createElement('div');
                        cell.classList.add('slot-cell');
                        const timeDiv = document.createElement('div');
                        timeDiv.classList.add('slot-time');
                        timeDiv.textContent = slot.time;
                        cell.appendChild(timeDiv);
                        const detailsDiv = document.createElement('div');
                        detailsDiv.classList.add('slot-details');
                        const trainerSpan = document.createElement('span');
                        trainerSpan.classList.add('trainer');
                        trainerSpan.textContent = slot.trainer;
                        detailsDiv.appendChild(trainerSpan);
                        const classSpan = document.createElement('span');
                        classSpan.classList.add('class-type');
                        classSpan.textContent = slot.classType;
                        detailsDiv.appendChild(classSpan);
                        cell.appendChild(detailsDiv);
                        slotGrid.appendChild(cell);
                    });
                })
                .catch(error => console.error('Error al obtener el horario:', error));
        }

        // Función para marcar el día seleccionado y actualizar el horario
        function selectDay(dayElement) {
            document.querySelectorAll('.day-item').forEach(item => item.classList.remove('active'));
            dayElement.classList.add('active');
            const selectedDay = dayElement.getAttribute('data-day');
            fetchSchedule(selectedDay);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const activeDay = document.querySelector('.day-item');
            if (activeDay) selectDay(activeDay);
        });
    </script>
</head>

<body>
    <div class="calendar-container">
        <header class="calendar-header">
            <h2><?php echo $weekStart->format('F Y'); ?></h2>
        </header>
        <section class="week-navigation">
            <a href="calendario.php?week=<?php echo $prevWeek->format('Y-m-d'); ?>" class="nav-arrow week-arrow">&laquo;</a>
            <div class="week-days">
                <?php for ($i = 0; $i < 7; $i++):
                    $currentDay = clone $weekStart;
                    $currentDay->modify("+$i day");
                    $dayAbbr = $days_es[$currentDay->format('D')];
                ?>
                    <div class="day-item" data-day="<?php echo $currentDay->format('Y-m-d'); ?>" onclick="selectDay(this)">
                        <span class="day-name"><?php echo $dayAbbr; ?></span>
                        <span class="day-number"><?php echo $currentDay->format('d'); ?></span>
                    </div>
                <?php endfor; ?>
            </div>
            <a href="calendario.php?week=<?php echo $nextWeek->format('Y-m-d'); ?>" class="nav-arrow week-arrow">&raquo;</a>
        </section>
        <section class="calendar-slots">
            <div class="slot-grid"></div>
        </section>
        <div class="reserve-button">
            <button onclick="window.location.href='reservar.php'">Reservar</button>
        </div>
    </div>
</body>

</html>