<?php
// getSchedule.php

require_once 'app pilates/conexion.php';
date_default_timezone_set('America/Mexico_City');

$conn = Conexion::conectar();

// Día seleccionado (formato YYYY-MM-DD) o hoy
$day = isset($_GET['day']) ? $_GET['day'] : date('Y-m-d');
// Número del día de la semana (1 = lunes, 7 = domingo)
$dayNumber = date("N", strtotime($day));

// 1) Obtener todas las clases ordenadas
$stmtClasses = $conn->prepare("
  SELECT id_clase, nombre_clase
  FROM clases
  ORDER BY id_clase
");
$stmtClasses->execute();
$classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

// 2) Seleccionar la clase del día (todos la imparten igual)
if (count($classes) > 0) {
  $selectedClass = $classes[($dayNumber - 1) % count($classes)]['nombre_clase'];
} else {
  $selectedClass = 'N/A';
}

// 3) Obtener todos los tutores
$stmtTutors = $conn->prepare("
  SELECT id_tutor, nombre
  FROM tutores
  ORDER BY id_tutor
");
$stmtTutors->execute();
$tutors = $stmtTutors->fetchAll(PDO::FETCH_ASSOC);

// 4) Generar el horario de 6 AM a 7 PM, asignando tutores cíclicamente
$schedule = [];
$total = 0;
for ($hour = 6; $hour <= 19; $hour++) {
  $time = date("g:00 A", mktime($hour, 0, 0));
  if (count($tutors) > 0) {
    $tutor = $tutors[$total % count($tutors)]['nombre'];
  } else {
    $tutor = 'N/A';
  }
  $schedule[] = [
    'time'      => $time,
    'trainer'   => $tutor,
    'classType' => $selectedClass
  ];
  $total++;
}

// 5) Devolver JSON
header('Content-Type: application/json');
echo json_encode($schedule);
