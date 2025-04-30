
<?php
// reservar.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}
require_once 'app pilates/conexion.php';
date_default_timezone_set('America/Mexico_City');
$conn = Conexion::conectar();

// Semana y día
if (isset($_GET['week'])) {
  $weekStart = new DateTime($_GET['week']);
} else {
  $t = new DateTime();
  if ($t->format('N') != 1) $t->modify('last monday');
  $weekStart = new DateTime($t->format('Y-m-d'));
}
$prev = (clone $weekStart)->modify('-7 days');
$next = (clone $weekStart)->modify('+7 days');
$selDay = $_GET['day'] ?? $weekStart->format('Y-m-d');
$dayNum = date('N', strtotime($selDay));
$days_es = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mié', 'Thu' => 'Jue', 'Fri' => 'Vie', 'Sat' => 'Sáb', 'Sun' => 'Dom'];

// --- NUEVO: obtenemos TUTORES junto a SU CLASE ---
$stmt = $conn->prepare("
  SELECT
    t.id_tutor,
    t.nombre   AS tutor_name,
    c.id_clase,
    c.nombre_clase,
    c.duracion,
    c.descripcion,
    c.cupo_maximo
  FROM tutores t
  JOIN clases c ON c.id_tutor = t.id_tutor
  ORDER BY t.id_tutor
");
$stmt->execute();
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reservar Clase - Pilates</title>
  <link rel="stylesheet" href="styles/reservar.css">
</head>

<body>
  <div class="calendar-container">
    <header class="calendar-header">
      <h2><?= $weekStart->format('F Y') ?></h2>
    </header>
    <section class="week-navigation">
      <a href="reservar.php?week=<?= $prev->format('Y-m-d') ?>&day=<?= (clone $prev)->modify('+' . ($dayNum - 1) . ' days')->format('Y-m-d') ?>" class="week-arrow">&laquo;</a>
      <div class="week-days">
        <?php for ($i = 0; $i < 7; $i++):
          $d = (clone $weekStart)->modify("+{$i} days");
          $dStr = $d->format('Y-m-d');
        ?>
          <a href="reservar.php?week=<?= $weekStart->format('Y-m-d') ?>&day=<?= $dStr ?>" class="day-item<?= $dStr == $selDay ? ' active' : '' ?>">
            <span class="day-name"><?= $days_es[$d->format('D')] ?></span>
            <span class="day-number"><?= $d->format('d') ?></span>
          </a>
        <?php endfor; ?>
      </div>
      <a href="reservar.php?week=<?= $next->format('Y-m-d') ?>&day=<?= (clone $next)->modify('+' . ($dayNum - 1) . ' days')->format('Y-m-d') ?>" class="week-arrow">&raquo;</a>
    </section>

    <section class="calendar-slots">
      <div class="slot-grid">
        <?php
        $cnt = 0;
        for ($h = 6; $h <= 19; $h++):
          $cnt++;
          $slot = $slots[($cnt - 1) % count($slots)];
          $timeFmt = date('g:00 A', mktime($h, 0, 0));

          // Contar reservas
          $stmtR = $conn->prepare("
            SELECT COUNT(*) FROM horarios h
            JOIN reservas r ON r.id_horario=h.id_horario
            WHERE h.id_clase=:cid
              AND DATE(h.fecha_hora)=:d
              AND TIME(h.fecha_hora)=:t
          ");
          $stmtR->execute([
            ':cid' => $slot['id_clase'],
            ':d' => $selDay,
            ':t' => date('H:i:s', mktime($h, 0, 0))
          ]);
          $reserved  = (int)$stmtR->fetchColumn();
          $available = max(0, $slot['cupo_maximo'] - $reserved);
        ?>
          <div class="slot-wrapper">
            <div class="slot-time"><?= $timeFmt ?></div>
            <div class="slot-cell">
              <div class="slot-details">
                <span class="trainer"><?= $slot['tutor_name'] ?></span>
                <span class="class-type"><?= $slot['nombre_clase'] ?></span>
                <span class="class-duration">Duración: <?= $slot['duracion'] ?> min</span>
                <span class="class-desc"><?= htmlspecialchars($slot['descripcion']) ?></span>
                <span class="available">Cupos: <?= $available ?></span>
              </div>
              <button class="reserve-slot"
                onclick="location.href='confirmar.php?day=<?= $selDay ?>&time=<?= urlencode($timeFmt) ?>&tutor=<?= $slot['id_tutor'] ?>&clase=<?= $slot['id_clase'] ?>'">
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