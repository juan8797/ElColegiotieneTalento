<?php
session_start();
include '../../conexion/db.php';

$id_estudiante = $_SESSION['id'];

// La modalidad puede venir por GET (primer clic) o por POST (pasos siguientes)
$modalidad = $_GET['modalidad'] ?? $_POST['modalidad'] ?? null;

if (!in_array($modalidad, ['talento_individual', 'demostracion_deportiva'])) {
    header("Location: Panel_estudiantes.php");
    exit();
}

// Evitar que alguien vuelva a entrar si ya está registrado en esta modalidad
$stmt = $conexion->prepare("SELECT id FROM participaciones WHERE usuario_id = ? AND modalidad = ?");
$stmt->bind_param("is", $id_estudiante, $modalidad);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: Panel_estudiantes.php");
    exit();
}
$stmt->close();

// El "camino" es la lista de ids de categorías ya elegidas, separados por coma
$camino = [];
if (isset($_POST['camino']) && $_POST['camino'] !== '') {
    $camino = array_map('intval', explode(',', $_POST['camino']));
}

// Si el estudiante acaba de elegir una categoría en este paso, la agregamos al camino
if (isset($_POST['categoria_elegida']) && $_POST['categoria_elegida'] !== '') {
    $camino[] = (int)$_POST['categoria_elegida'];
}

$ultimo_id = end($camino) ?: null;

// Buscamos los hijos de la última categoría elegida (o las raíces si el camino está vacío)
if ($ultimo_id === null) {
    $stmt = $conexion->prepare(
        "SELECT id, nombre FROM categorias_participacion WHERE modalidad = ? AND padre_id IS NULL ORDER BY nombre ASC"
    );
    $stmt->bind_param("s", $modalidad);
} else {
    $stmt = $conexion->prepare(
        "SELECT id, nombre FROM categorias_participacion WHERE modalidad = ? AND padre_id = ? ORDER BY nombre ASC"
    );
    $stmt->bind_param("si", $modalidad, $ultimo_id);
}
$stmt->execute();
$hijos = $stmt->get_result();
$tieneHijos = $hijos->num_rows > 0;
$stmt->close();

$caminoTexto = implode(',', $camino);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Categoría</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="encabezado-panel">
        <h1>Registrar participación</h1>
    </div>

    <main class="container-fluid">

        <?php if ($tieneHijos): ?>
            <!-- Aún hay más niveles: mostramos las opciones de este nivel -->
            <div class="card-wizard">
                <div class="wizard-header">
                    <h2>Selecciona una categoría</h2>
                    <p class="wizard-subtitle">
                        Modalidad: <strong><?= $modalidad === 'talento_individual' ? 'Talento Individual' : 'Demostración Deportiva' ?></strong>
                        <?php if (!empty($camino)): ?>
                            &bull; Paso <?= count($camino) + 1 ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="wizard-body">
                    <p class="wizard-instruccion">Selecciona una de las siguientes opciones para continuar:</p>

                    <form method="POST" action="seleccionar_categoria.php">
                        <input type="hidden" name="modalidad" value="<?= htmlspecialchars($modalidad) ?>">
                        <input type="hidden" name="camino" value="<?= htmlspecialchars($caminoTexto) ?>">

                        <div class="radio-cards-group">
                            <?php while ($cat = $hijos->fetch_assoc()): ?>
                                <label class="radio-card-item" for="cat_<?= $cat['id'] ?>">
                                    <input type="radio" name="categoria_elegida" value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>" required>
                                    <div class="radio-card-content">
                                        <span class="radio-card-title"><?= htmlspecialchars($cat['nombre']) ?></span>
                                    </div>
                                </label>
                            <?php endwhile; ?>
                        </div>

                        <div class="wizard-actions">
                            <a href="Panel_estudiantes.php" class="btn-volver">Cancelar</a>
                            <button type="submit" class="btn-primary">Siguiente &rarr;</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($ultimo_id !== null): ?>
            <!-- Ya no hay más niveles: esta es la categoría final elegida -->
            <div class="card-wizard">
                <div class="wizard-header">
                    <h2>Confirmar Registro</h2>
                    <p class="wizard-subtitle">
                        Modalidad: <strong><?= $modalidad === 'talento_individual' ? 'Talento Individual' : 'Demostración Deportiva' ?></strong>
                    </p>
                </div>
                <div class="wizard-body">
                    <div class="alerta-resumen-pasos">
                        <p style="margin: 0;">Has seleccionado todas las categorías requeridas. Por favor confirma tu registro para completar la inscripción.</p>
                    </div>

                    <form method="POST" action="../../usuarios/guardar_participacion.php">
                        <input type="hidden" name="modalidad" value="<?= htmlspecialchars($modalidad) ?>">
                        <input type="hidden" name="categoria_id" value="<?= $ultimo_id ?>">

                        <?php if ($modalidad === 'talento_individual'): ?>
                            <div class="campo-editar">
                                <label for="nombre_acto" class="form-label">Nombre de tu acto o presentación:</label>
                                <input type="text" name="nombre_acto" id="nombre_acto" class="form-control" placeholder="Ej: Solo de guitarra acústica, Rutina de gimnasia..." required>
                                <span class="form-text-admin">Escribe un nombre o descripción breve de lo que presentarás.</span>
                            </div>
                        <?php endif; ?>

                        <div class="wizard-actions">
                            <a href="Panel_estudiantes.php" class="btn-volver">Cancelar</a>
                            <button type="submit" class="btn-primary">Guardar participación</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- No hay ninguna categoría creada todavía para esta modalidad -->
            <div class="card-wizard">
                <div class="wizard-header">
                    <h2>Sin categorías disponibles</h2>
                </div>
                <div class="wizard-body" style="text-align: center;">
                    <div class="alerta error">
                        <p style="margin: 0;">Aún no hay categorías disponibles para esta modalidad. Contacta al administrador del festival.</p>
                    </div>
                    <div class="wizard-actions justify-center">
                        <a href="Panel_estudiantes.php" class="btn-primary">Volver al Panel</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <?php include '../../includes/PiePagina.php'; ?>
</body>
</html>