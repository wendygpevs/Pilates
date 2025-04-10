<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}
require_once 'app pilates/conexion.php';
date_default_timezone_set('America/Mexico_City');

$conn   = Conexion::conectar();
$userId = $_SESSION['usuario_id'];

$days_full = [
  'Mon' => 'Lunes',
  'Tue' => 'Martes',
  'Wed' => 'Miércoles',
  'Thu' => 'Jueves',
  'Fri' => 'Viernes',
  'Sat' => 'Sábado',
  'Sun' => 'Domingo'
];

// 1) Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $day     = $_POST['day'];
  $time    = $_POST['time'];    // e.g. "7:00 AM"
  $tutorId = $_POST['tutor'];
  $classId = $_POST['clase'];
  $seats   = $_POST['seats'] ?? [];

  // Convertir a DATETIME
  $dt = "$day " . date('H:i:s', strtotime($time));

  // Buscar o crear horario
  $stmtH = $conn->prepare("
      SELECT id_horario FROM horarios 
      WHERE id_clase = :cid AND fecha_hora = :fh
    ");
  $stmtH->execute([':cid' => $classId, ':fh' => $dt]);
  $hid = $stmtH->fetchColumn();
  if (!$hid) {
    $conn->prepare("
          INSERT INTO horarios (id_clase, fecha_hora)
          VALUES (:c, :fh)
        ")->execute([':c' => $classId, ':fh' => $dt]);
    $hid = $conn->lastInsertId();
  }

  // Insertar reservas
  $stmtR = $conn->prepare("
      INSERT INTO reservas (id_usuario, id_horario)
      VALUES (:u, :h)
    ");
  foreach ($seats as $s) {
    $stmtR->execute([':u' => $userId, ':h' => $hid]);
  }

  header("Location: confirmar.php?day=$day&time=" . urlencode($time) . "&tutor=$tutorId&clase=$classId&success=1");
  exit;
}

// 2) Mostrar GET
$day        = $_GET['day'];
$time       = $_GET['time'];
$tutorId    = $_GET['tutor'];
$classId    = $_GET['clase'];
$showSuccess = isset($_GET['success']);

// Datos tutor
$stmtT = $conn->prepare("SELECT nombre FROM tutores WHERE id_tutor = :i");
$stmtT->execute([':i' => $tutorId]);
$tname = $stmtT->fetchColumn();

// Datos clase
$stmtC = $conn->prepare("SELECT nombre_clase, cupo_maximo FROM clases WHERE id_clase = :i");
$stmtC->execute([':i' => $classId]);
$c = $stmtC->fetch(PDO::FETCH_ASSOC);

// Obtener id_horario
$dt = "$day " . date('H:i:s', strtotime($time));
$stmtH = $conn->prepare("SELECT id_horario FROM horarios WHERE id_clase=:cid AND fecha_hora=:fh");
$stmtH->execute([':cid' => $classId, ':fh' => $dt]);
$hid = $stmtH->fetchColumn();

// Contar ocupados
$occ = 0;
if ($hid) {
  $stmtRO = $conn->prepare("SELECT COUNT(*) FROM reservas WHERE id_horario = :h");
  $stmtRO->execute([':h' => $hid]);
  $occ = (int)$stmtRO->fetchColumn();
}

$dayName = $days_full[date('D', strtotime($day))] ?? '';
$cleanUrl = "confirmar.php?day=$day&time=" . urlencode($time) . "&tutor=$tutorId&clase=$classId";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Confirmar Reserva</title>
  <link rel="stylesheet" href="styles/confirmar.css">
</head>

<body>
  <div class="calendar-container">
    <header class="calendar-header">
      <h2>Reserva para <?php echo "{$dayName}, {$day} — {$time}"; ?></h2>
      <p>
        Entrenador: <strong><?php echo htmlspecialchars($tname); ?></strong>
        &nbsp;|&nbsp;
        Clase: <strong><?php echo htmlspecialchars($c['nombre_clase']); ?></strong>
      </p>
    </header>

    <form method="POST">
      <input type="hidden" name="day" value="<?php echo $day; ?>">
      <input type="hidden" name="time" value="<?php echo $time; ?>">
      <input type="hidden" name="tutor" value="<?php echo $tutorId; ?>">
      <input type="hidden" name="clase" value="<?php echo $classId; ?>">

      <div class="seats">
        <?php for ($i = 1; $i <= $c['cupo_maximo']; $i++):
          $isOcc = $i <= $occ;
        ?>
          <input type="checkbox" id="seat-<?php echo $i; ?>" name="seats[]" value="<?php echo $i; ?>"
            class="seat-input" <?php if ($isOcc) echo 'disabled'; ?>>
          <label for="seat-<?php echo $i; ?>" class="seat-label"><?php echo $i; ?></label>
        <?php endfor; ?>
      </div>

      <div class="legend">
        <div class="legend-item legend-available">
          <div class="legend-color"></div><span>Disponible</span>
        </div>
        <div class="legend-item legend-selected">
          <div class="legend-color"></div><span>Seleccionado</span>
        </div>
        <div class="legend-item legend-occupied">
          <div class="legend-color"></div><span>Ocupado</span>
        </div>
      </div>

      <div class="confirm-wrapper">
        <button type="submit" class="confirm-btn">Confirmar Reservación</button>
      </div>
    </form>
  </div>

  <div id="success-modal" class="modal">
    <div class="modal-content">
      <p>¡Reservación confirmada!</p>
      <button id="modal-ok" class="modal-ok-btn">Aceptar</button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php if ($showSuccess): ?>
        document.getElementById('success-modal').style.display = 'flex';
      <?php endif; ?>
      document.getElementById('modal-ok').addEventListener('click', function() {
        window.location.replace('<?php echo $cleanUrl; ?>');
      });
    });
  </script>
</body>

</html>