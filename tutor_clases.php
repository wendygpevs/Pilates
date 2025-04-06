<?php
session_start();

// Simulamos que el tutor ya inició sesión (nombre visible en la parte superior)
$_SESSION['tutor'] = 'Victoria';

// Leer datos de alumnos
$archivo = 'alumnos.json';
$alumnos = [];

if (file_exists($archivo)) {
  $alumnos = json_decode(file_get_contents($archivo), true);
}

function alumnosPorDia($dia, $alumnos) {
  $lista = [];
  foreach ($alumnos as $alumno) {
    if ($alumno['dia'] === $dia) {
      $lista[] = $alumno['nombre'];
    }
  }
  return $lista;
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
        <?php echo isset($_SESSION['tutor']) ? $_SESSION['tutor'] : 'Invitado'; ?>
        <i class="icono-user"></i>
      </span>
    </nav>
  </header>

  <main>
    <h1 class="titulo">PILATES REFORM</h1>
    <p class="info"></p>

    <section class="clases-contenedor">
      <!-- LUNES -->
      <div class="clase-box">
        <h2>Lunes</h2>
        <p>Horario</p>
        <p><strong>:</strong></p>
        <ol>
          <?php foreach (alumnosPorDia("lunes", $alumnos) as $i => $nombre): ?>
            <li><?php echo htmlspecialchars($nombre); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <!-- MIÉRCOLES -->
      <div class="clase-box">
        <h2>Miércoles</h2>
        <p>Horario</p>
        <p><strong></strong></p>
        <ol>
          <?php foreach (alumnosPorDia("miércoles", $alumnos) as $i => $nombre): ?>
            <li><?php echo htmlspecialchars($nombre); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <!-- VIERNES -->
      <div class="clase-box">
        <h2>Viernes</h2>
        <p>Horario</p>
        <p><strong></strong></p>
        <ol>
          <?php foreach (alumnosPorDia("viernes", $alumnos) as $i => $nombre): ?>
            <li><?php echo htmlspecialchars($nombre); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>
    </section>
  </main>
</body>
</html>
