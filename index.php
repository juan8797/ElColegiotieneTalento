<!DOCTYPE html>
<html lang="en">
  <head>
  <title>El colegio tiene talento</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/menu.php'; ?>

<?php
require_once 'conexion/db.php';

// Traemos las 3 secciones en el orden fijo que siempre han tenido
$secciones = [];
$resultadoSecciones = $conexion->query("SELECT * FROM secciones_index");
while ($fila = $resultadoSecciones->fetch_assoc()) {
    $secciones[$fila['clave']] = $fila;
}

// Traemos la información institucional más reciente primero
$resultadoInfo = $conexion->query("SELECT * FROM informacion_institucional ORDER BY fecha_publicacion DESC");
?>

<div class="container text-center">
  <h3 class="Categorias">Festival El Colegio Tiene Talentos</h3><br>
  <div class="row">
    <img src="img/ImagenPrincipal.jpeg" alt="Objetivos" class="imgcentral">

    <div class="col-sm-4 col-text">
      <img src="<?= htmlspecialchars($secciones['bailes_grupales']['imagen']) ?>" alt="Baile grupal" class="img-responsive">
      <div class="well">
        <h4 class="Color-text"><?= htmlspecialchars($secciones['bailes_grupales']['titulo']) ?></h4>
        <p class="Color-text"><?= htmlspecialchars($secciones['bailes_grupales']['descripcion']) ?></p>
      </div>
    </div>

    <div class="col-sm-4">
      <img src="<?= htmlspecialchars($secciones['talento_individual']['imagen']) ?>" alt="Talento individual" class="img-responsive">
      <div class="well">
        <h4 class="Color-text"><?= htmlspecialchars($secciones['talento_individual']['titulo']) ?></h4>
        <p class="Color-text"><?= htmlspecialchars($secciones['talento_individual']['descripcion']) ?></p>
      </div>
    </div>

    <div class="col-sm-4">
      <img src="<?= htmlspecialchars($secciones['talento_deportivo']['imagen']) ?>" alt="Talento deportivo" class="img-responsive">
      <div class="well">
        <h4 class="Color-text"><?= htmlspecialchars($secciones['talento_deportivo']['titulo']) ?></h4>
        <p class="Color-text"><?= htmlspecialchars($secciones['talento_deportivo']['descripcion']) ?></p>
      </div>
    </div>
  </div>
</div><br>

<?php if ($resultadoInfo->num_rows > 0): ?>
<div class="container text-center">
  <h3 class="Categorias">Información Institucional</h3><br>
  <div class="row">
    <?php while ($info = $resultadoInfo->fetch_assoc()): ?>
      <div class="col-sm-4 col-text">
        <div class="well">
          <h4 class="Color-text"><?= htmlspecialchars($info['titulo']) ?></h4>
          <p class="Color-text"><?= nl2br(htmlspecialchars($info['contenido'])) ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div><br>
<?php endif; ?>

<?php include 'includes/PiePagina.php'; ?>
</body>
</html>
