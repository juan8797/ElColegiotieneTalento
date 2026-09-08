<?php
session_start();

require_once '../../conexion/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login/login.php');
    exit();
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: panel_criterios.php');
    exit();
}

$error = null;

// Traemos el criterio actual
$stmt = $conexion->prepare("SELECT id, nombre_criterio, valor FROM criterios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$criterio = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$criterio) {
    header('Location: panel_criterios.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_criterio'] ?? '');
    $valor = $_POST['valor'] ?? '';

    if ($nombre === '' || $valor === '' || !is_numeric($valor)) {
        $error = "Debes ingresar un nombre y un valor numérico válido.";
    } elseif ($valor <= 0 || $valor > 100) {
        $error = "El valor debe estar entre 0 y 100.";
    } else {
        // Sumamos todos los criterios EXCEPTO el que estamos editando, y le sumamos el nuevo valor
        $sql_suma = "SELECT SUM(valor) AS total FROM criterios WHERE id != ?";
        $stmt_suma = $conexion->prepare($sql_suma);
        $stmt_suma->bind_param("i", $id);
        $stmt_suma->execute();
        $suma_otros = $stmt_suma->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt_suma->close();
        $max_permitido = max(0, 100 - $suma_otros);

        if (($suma_otros + $valor) > 100.001) {
            $error = "No puedes guardar: la suma total superaría el 100% "
                    . "(otros criterios: " . number_format($suma_otros, 2) . "%, intentas dejar este en: " . number_format($valor, 2) . "%). "
                    . "Máximo permitido para este criterio: " . number_format($max_permitido, 2) . "%.";
        } else {
            $stmt = $conexion->prepare(
                "UPDATE criterios SET nombre_criterio = ?, valor = ? WHERE id = ?"
            );
            $stmt->bind_param("sdi", $nombre, $valor, $id);

            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Criterio actualizado correctamente.";
                header('Location: panel_criterios.php');
                exit();
            } else {
                $error = "Ocurrió un error al actualizar el criterio.";
            }
            $stmt->close();
        }
    }
    // Si hubo error, mantenemos los datos que el usuario intentó guardar en el formulario
    $criterio['nombre_criterio'] = $nombre;
    $criterio['valor'] = $valor;
}

// Calculamos información si carga la página
if (!isset($suma_otros)) {
    $sql_suma = "SELECT SUM(valor) AS total FROM criterios WHERE id != ?";
    $stmt_suma = $conexion->prepare($sql_suma);
    $stmt_suma->bind_param("i", $id);
    $stmt_suma->execute();
    $suma_otros = $stmt_suma->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_suma->close();
    $max_permitido = max(0, 100 - $suma_otros);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Criterio — Panel Administrador</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_admin.php'; ?>

    <main class="container-fluid">
        <div class="seccion-usuarios" style="max-width: 620px; margin: 30px auto;">
            <h2>Editar Criterio de Evaluación</h2>

            <div class="explanation-table" style="margin-bottom: 20px;">
                <p class="text-explanation">
                    Suma de los demás criterios: <strong><?= number_format($suma_otros, 2) ?>%</strong>.
                    Valor máximo asignable a este criterio: <strong><?= number_format($max_permitido, 2) ?>%</strong>.
                </p>
            </div>

            <?php if ($error): ?>
                <div class="alerta error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="editar_criterio.php">
                <input type="hidden" name="id" value="<?= (int)$criterio['id'] ?>">

                <div class="campo-editar">
                    <label for="nombre_criterio">Nombre del Criterio:</label>
                    <input type="text" id="nombre_criterio" name="nombre_criterio" class="form-control"
                           value="<?= htmlspecialchars($criterio['nombre_criterio']) ?>" required>
                </div>

                <div class="campo-editar">
                    <label for="valor">Valor / Porcentaje (%):</label>
                    <input type="number" id="valor" name="valor" step="0.01" min="0.01" max="100" class="form-control"
                           value="<?= htmlspecialchars($criterio['valor']) ?>" required>
                    <small class="form-text text-muted" style="color: var(--color-text-muted); font-size: 13px; display: block; margin-top: 4px;">
                        Modifique el porcentaje de ponderación para la calificación final.
                    </small>
                </div>

                <div class="botones-editar">
                    <button type="submit" class="btn-admin">Actualizar Criterio</button>
                    <a href="panel_criterios.php" class="btn-eliminar">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php include '../../includes/PiePagina.php'; ?>

</body>
</html>