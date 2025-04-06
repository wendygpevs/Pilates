<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilates</title>
    <link rel="stylesheet" href="styles/index.css">
    <script src="https://kit.fontawesome.com/cf09b76418.js" crossorigin="anonymous"></script>

    <link rel="shortcut icon" href="imagenes/reform.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Great+Vibes&family=M+PLUS+Rounded+1c&family=Merienda:wght@300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav>
    <div class="logo">
        <h4 id="nombre-pilates">REFORM <img src="imagenes/reform.ico" alt="icono"></h4>
    </div>
    <input type="checkbox" id="click">
    <label for="click" class="menu-btn">
        <i class="fas fa-bars"></i>
    </label>
    <ul>
        <br>
        <li><a href="index.php">Home</a><i class="fas fa-home" style="color: white;"></i></li> 
        <br><br>
        <li><a href="reservar.php">Reserve</a><i class="fas fa-calendar-check" style="color: white;"></i></li> 
        <br><br>
        <li><a href="login.php">Usuario</a><i class="fas fa-user" style="color: white;"></i></li>  
        <br><br>
        <li><a href="tutor_clases.php">Ver Alumnos</a><i class="fas fa-users" style="color: white;"></i></li>  
        <br><br>
    </ul>
</nav>

<main>
    <br>
    <h1 style="text-align: center;">PILATES REFORM</h1>
    <br>
    <div style="text-align: center;">
        <img src="imagenes/reform.png" alt="logo" width="220" height="100">
    </div>
    <br><br>

    <div class="container">
        <div class="box">
            <h4>CONCENTRACIÓN</h4>
        </div>
        <div class="box">
            <h4>FLUIDEZ</h4>
        </div>
        <div class="box">
            <h4>PRECISIÓN</h4>
        </div>
        <div class="box">
            <h4>CONTROL</h4>
        </div>
    </div>
    <br>
    <div class="container">
        <div class="texto">
            <p>Pilates en Reform con Torre es una forma eficaz de trabajar en la fuerza, la flexibilidad y la postura, todo en un ambiente controlado y de bajo impacto.
                Ideal para quienes buscan mejorar su condición física sin sobrecargar las articulaciones.
            </p><br>
        </div>
        <div class="imagen-container">
            <img id="imagen" src="imagenes/reform 1.jpg" alt="zona">
            <div class="arrows">
                <button class="flecha flecha-izquierda" onclick="cambiarImagen('anterior')">←</button>
                <button class="flecha flecha-derecha" onclick="cambiarImagen('siguiente')">→</button>
            </div>
        </div>
    </div>
</main>

<br>
<footer>
    <h3>&copy; 2025 PILATES REFORM </h3>
    <h3>¡Síguenos en nuestras redes sociales!</h3>
    <div class="redes-sociales">
        <a href="https://www.facebook.com/"><img src="imagenes/fb.png" alt="Facebook"></a>
        <a href="https://www.instagram.com/reformerpilates.lm?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="><img src="imagenes/ig.png" alt="Instagram"></a>
        <a href="https://www.whatsapp.com/"><img src="imagenes/whatsapp.png" alt="WhatsApp"></a>
    </div>
</footer>

<script>
    const nombrePilates = document.getElementById('nombre-pilates');
    nombrePilates.addEventListener('click', function () {
        window.location.href = 'index.php';
    });

    var imagenes = ["imagenes/reform 1.jpg", "imagenes/reform 2.jpg", "imagenes/reform 3.jpg", "imagenes/reform 4.jpg", "imagenes/reform 5.jpg", "imagenes/reform 6.jpg"];
    var indiceImagen = 0;

    function cambiarImagen(direccion) {
        if (direccion === "anterior") {
            indiceImagen = (indiceImagen - 1 + imagenes.length) % imagenes.length;
        } else if (direccion === "siguiente") {
            indiceImagen = (indiceImagen + 1) % imagenes.length;
        }

        var imagen = document.getElementById("imagen");
        imagen.classList.add("oculto");
        setTimeout(function () {
            imagen.src = imagenes[indiceImagen];
            imagen.classList.remove("oculto");
        }, 500);
    }
</script>

</body>
</html>
