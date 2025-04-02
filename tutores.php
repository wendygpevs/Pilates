<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Tutores</title>
    <link rel="stylesheet" href="../styles/tutores.css">
</head>
<body>
    <h2>Registro de Tutores</h2>
    <form action="procesar_tutor.php" method="POST">
        <label for="nombre">Nombre completo:</label>
        <input type="text" id="nombre" name="nombre" required>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>

        <label for="telefono">Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" required>

        <label for="especialidad">Especialidad:</label>
        <input type="text" id="especialidad" name="especialidad" required>

        <label for="experiencia">Años de experiencia:</label>
        <input type="number" id="experiencia" name="experiencia" min="0" required>

        <button type="submit">Registrar</button>
    </form>
</body>
</html>
