<?php
// getSchedule.php

require_once 'app pilates/conexion.php';
date_default_timezone_set('America/Mexico_City');

$conn = Conexion::conectar();

// Día seleccionado (formato YYYY-MM-DD) — seguimos recibiéndolo por si en el futuro lo necesitas
$day = isset($_GET['day']) ? $_GET['day'] : date('Y-m-d');

// 1) Obtener *cada tutor con su clase* (antes era sólo clases)
$stmt = $conn->prepare("
  SELECT
    t.id_tutor,
    t.nombre         AS trainer,
    c.id_clase,
    c.nombre_clase   AS classType
  FROM tutores t
  JOIN clases c ON c.id_tutor = t.id_tutor
  ORDER BY t.id_tutor
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = count($rows);
$schedule = [];

// 2) Generar franjas de 6 AM a 7 PM asignando *cíclicamente* cada tutor y su clase
for ($hour = 6; $hour <= 19; $hour++) {
  $idx  = ($hour - 6) % $count;
  $time = date("g:00 A", mktime($hour, 0, 0));

  $schedule[] = [
    'time'      => $time,
    'trainer'   => $rows[$idx]['trainer'],
    'classType' => $rows[$idx]['classType']
  ];
}

// 3) Devolver JSON
header('Content-Type: application/json');
echo json_encode($schedule);
