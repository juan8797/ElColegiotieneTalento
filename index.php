<?php
require_once 'conexion/db.php';

$secciones = [];
$resultadoSecciones = $conexion->query("SELECT * FROM secciones_index");
while ($fila = $resultadoSecciones->fetch_assoc()) {
    $secciones[$fila['clave']] = $fila;
}

$resultadoInfo = $conexion->query("SELECT * FROM informacion_institucional ORDER BY fecha_publicacion DESC");

// Imágenes adjuntas de cada información institucional
$imagenesInfo = [];
$resultadoImagenesInfo = $conexion->query("SELECT id_informacion, imagen FROM informacion_imagenes ORDER BY id ASC");
while ($fila = $resultadoImagenesInfo->fetch_assoc()) {
    $imagenesInfo[(int) $fila['id_informacion']][] = $fila['imagen'];
}

// Imágenes del carrusel (se administran desde el panel de administrador)
$imagenesCarrusel = [];
$resultadoCarrusel = $conexion->query(
    "SELECT c.imagen, i.id AS id_info
     FROM carrusel_imagenes c
     LEFT JOIN informacion_institucional i ON i.id = c.id_informacion
     ORDER BY c.id ASC"
);
while ($fila = $resultadoCarrusel->fetch_assoc()) {
    $imagenesCarrusel[] = $fila;
}
$totalCarrusel = count($imagenesCarrusel);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <title>El colegio tiene talento</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">

  <?php if ($totalCarrusel > 1): ?>
  <?php
    $segundosPorImagen = 5;                          // tiempo que se ve cada imagen
    $duracionTotal     = $totalCarrusel * $segundosPorImagen;
    $tramo             = 100 / $totalCarrusel;       // parte de la animación que le toca a cada imagen
    $pausa             = $tramo * 0.88;              // el resto del tramo se usa para deslizar a la siguiente
  ?>
  <style>
    /* Reglas del carrusel generadas según la cantidad de imágenes */
    <?php for ($i = 0; $i < $totalCarrusel; $i++): ?>
    #carrusel-<?= $i ?>:checked ~ .carrusel-ventana .carrusel-pista { transform: translateX(-<?= $i * 100 ?>%); }
    #carrusel-<?= $i ?>:checked ~ .carrusel-puntos label[for="carrusel-<?= $i ?>"] { background-color: var(--color-primary); transform: scale(1.25); }
    <?php endfor; ?>

    /* Cambio automático: funciona mientras nadie haya usado las flechas o los puntos */
    @keyframes carrusel-auto {
      <?php for ($i = 0; $i < $totalCarrusel; $i++): ?>
      <?= number_format($i * $tramo, 3, '.', '') ?>%, <?= number_format($i * $tramo + $pausa, 3, '.', '') ?>% { transform: translateX(-<?= $i * 100 ?>%); }
      <?php endfor; ?>
      100% { transform: translateX(-<?= $totalCarrusel * 100 ?>%); }
    }

    @keyframes carrusel-punto {
      0%, <?= number_format($tramo, 3, '.', '') ?>% { background-color: var(--color-primary); transform: scale(1.25); }
      <?= number_format($tramo + 0.001, 3, '.', '') ?>%, 100% { background-color: var(--color-light-white); transform: scale(1); }
    }

    .carrusel:not(:has(.carrusel-radio:checked)) .carrusel-pista {
      animation: carrusel-auto <?= $duracionTotal ?>s ease-in-out infinite;
    }

    <?php for ($i = 0; $i < $totalCarrusel; $i++): ?>
    .carrusel:not(:has(.carrusel-radio:checked)) .carrusel-puntos label:nth-child(<?= $i + 1 ?>) {
      animation: carrusel-punto <?= $duracionTotal ?>s linear <?= $i * $segundosPorImagen ?>s infinite;
    }
    <?php endfor; ?>
  </style>
  <?php endif; ?>
</head>
<body>
  <?php include 'includes/menu.php'; ?>

<div class="container text-center">
  <h3 class="Categorias">Festival El Colegio Tiene Talentos</h3><br>

  <?php if ($totalCarrusel > 0): ?>
  <div class="carrusel">
    <?php for ($i = 0; $i < $totalCarrusel; $i++): ?>
      <input type="radio" name="carrusel" id="carrusel-<?= $i ?>" class="carrusel-radio" aria-label="Imagen <?= $i + 1 ?>">
    <?php endfor; ?>

    <div class="carrusel-ventana">
      <div class="carrusel-pista">
        <?php foreach ($imagenesCarrusel as $i => $itemCarrusel): ?>
          <?php
            $anterior  = ($i - 1 + $totalCarrusel) % $totalCarrusel;
            $siguiente = ($i + 1) % $totalCarrusel;
          ?>
          <div class="carrusel-slide">
            <?php if ($itemCarrusel['id_info']): ?>
              <a href="#info-<?= (int) $itemCarrusel['id_info'] ?>" class="carrusel-enlace">
                <img src="<?= htmlspecialchars($itemCarrusel['imagen']) ?>" alt="Imagen <?= $i + 1 ?> del festival. Toca para ver la información">
                <span class="carrusel-etiqueta">Más información &#8595;</span>
              </a>
            <?php else: ?>
              <img src="<?= htmlspecialchars($itemCarrusel['imagen']) ?>" alt="Imagen <?= $i + 1 ?> del festival">
            <?php endif; ?>
            <?php if ($totalCarrusel > 1): ?>
              <label for="carrusel-<?= $anterior ?>" class="carrusel-flecha carrusel-anterior" aria-label="Imagen anterior">&#10094;</label>
              <label for="carrusel-<?= $siguiente ?>" class="carrusel-flecha carrusel-siguiente" aria-label="Imagen siguiente">&#10095;</label>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?php if ($totalCarrusel > 1): ?>
          <?php $primeraImagen = $imagenesCarrusel[0]; ?>
          <div class="carrusel-slide" aria-hidden="true">
            <?php if ($primeraImagen['id_info']): ?>
              <a href="#info-<?= (int) $primeraImagen['id_info'] ?>" class="carrusel-enlace" tabindex="-1">
                <img src="<?= htmlspecialchars($primeraImagen['imagen']) ?>" alt="">
                <span class="carrusel-etiqueta">Más información &#8595;</span>
              </a>
            <?php else: ?>
              <img src="<?= htmlspecialchars($primeraImagen['imagen']) ?>" alt="">
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($totalCarrusel > 1): ?>
      <div class="carrusel-puntos">
        <?php for ($i = 0; $i < $totalCarrusel; $i++): ?>
          <label for="carrusel-<?= $i ?>" aria-label="Ir a la imagen <?= $i + 1 ?>"></label>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="row fila-talentos">

    <div class="col-sm-4 col-text">
      <div class="well">
        <h4 class="Color-text"><?= htmlspecialchars($secciones['bailes_grupales']['titulo']) ?></h4>
        <p class="Color-text"><?= htmlspecialchars($secciones['bailes_grupales']['descripcion']) ?></p>
      </div>
    </div>

    <div class="col-sm-4">
      <div class="well">
        <h4 class="Color-text"><?= htmlspecialchars($secciones['talento_individual']['titulo']) ?></h4>
        <p class="Color-text"><?= htmlspecialchars($secciones['talento_individual']['descripcion']) ?></p>
      </div>
    </div>

    <div class="col-sm-4">
      <div class="well">
        <h4 class="Color-text"><?= htmlspecialchars($secciones['talento_deportivo']['titulo']) ?></h4>
        <p class="Color-text"><?= htmlspecialchars($secciones['talento_deportivo']['descripcion']) ?></p>
      </div>
    </div>
  </div>
</div><br>

<?php if ($resultadoInfo->num_rows > 0): ?>
<div class="container text-center" id="informacion">
  <h3 class="Categorias">Información Institucional</h3><br>
  <div class="row">
    <?php while ($info = $resultadoInfo->fetch_assoc()): ?>
      <div class="col-sm-4 col-text" id="info-<?= (int) $info['id'] ?>">
        <div class="well">
          <h4 class="Color-text"><?= htmlspecialchars($info['titulo']) ?></h4>
          <p class="Color-text"><?= nl2br(htmlspecialchars($info['contenido'])) ?></p>

          <?php $imagenesDeEstaInfo = $imagenesInfo[(int) $info['id']] ?? []; ?>
          <?php if (!empty($imagenesDeEstaInfo)): ?>
            <div class="info-galeria<?= count($imagenesDeEstaInfo) === 1 ? ' info-galeria-una' : '' ?>">
              <?php foreach ($imagenesDeEstaInfo as $rutaImagenInfo): ?>
                <a href="<?= htmlspecialchars($rutaImagenInfo) ?>" target="_blank" rel="noopener">
                  <img src="<?= htmlspecialchars($rutaImagenInfo) ?>" alt="Imagen de <?= htmlspecialchars($info['titulo']) ?>">
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div><br>
<?php endif; ?>

<div class="container text-center">
  <h3 class="Categorias">Síguenos en nuestras redes</h3><br>
  <div class="redes-sociales">
    <a href="https://www.facebook.com/profile.php?id=100064278424507" target="_blank" class="icono-red">
      <img src="img/icono-facebook.png" alt="Facebook">
    </a>
    <a href="#" target="_blank" class="icono-red">
      <img src="img/icono-instagram.png" alt="Instagram">
    </a>
    <a href="#" target="_blank" class="icono-red">
      <img src="img/icono-youtube.png" alt="YouTube">
    </a>
  </div>
</div><br>

<?php include 'includes/PiePagina.php'; ?>
</body>
</html>