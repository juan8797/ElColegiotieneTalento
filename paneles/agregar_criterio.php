<?php
session_start();

require_once '../conexion/db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login/login.php');
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_criterio'] ?? '');
    $valor = $_POST['valor'] ?? '';

    if ($nombre === '' || $valor === '' || !is_numeric($valor)) {
        $error = "Debes ingresar un nombre y un valor numérico válido.";
    } elseif ($valor <= 0 || $valor > 100) {
        $error = "El valor debe estar entre 0 y 100.";
    } else {
        $sql_suma = "SELECT SUM(valor) AS total FROM criterios";
        $resultado_suma = $conexion->query($sql_suma);
        $suma_actual = $resultado_suma->fetch_assoc()['total'] ?? 0;
        $disponible = max(0, 100 - $suma_actual);

        if (($suma_actual + $valor) > 100.001) {
            $error = "No puedes agregar este criterio: la suma total superaría el 100% "
                    . "(actual: " . number_format($suma_actual, 2) . "%, intentas sumar: " . number_format($valor, 2) . "%). "
                    . "Máximo disponible para asignar: " . number_format($disponible, 2) . "%.";
        } else {
            $stmt = $conexion->prepare(
                "INSERT INTO criterios (nombre_criterio, valor) VALUES (?, ?)"
            );
            $stmt->bind_param("sd", $nombre, $valor);

            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Criterio agregado correctamente.";
                header('Location: panel_criterios.php');
                exit();
            } else {
                $error = "Ocurrió un error al guardar el criterio.";
            }
            $stmt->close();
        }
    }
}

if (!isset($suma_actual)) {
    $sql_suma = "SELECT SUM(valor) AS total FROM criterios";
    $resultado_suma = $conexion->query($sql_suma);
    $suma_actual = $resultado_suma->fetch_assoc()['total'] ?? 0;
    $disponible = max(0, 100 - $suma_actual);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Criterio — Panel Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <?php include '../includes/menu_admin.php'; ?>

    <main class="container-fluid">
        <div class="seccion-usuarios" style="max-width: 620px; margin: 30px auto;">
            <h2>Agregar Nuevo Criterio</h2>

            <div class="explanation-table" style="margin-bottom: 20px;">
                <p class="text-explanation">
                    Suma actual acumulada: <strong><?= number_format($suma_actual, 2) ?>%</strong>.
                    Porcentaje disponible para asignar: <strong><?= number_format($disponible, 2) ?>%</strong>.
                </p>
            </div>

            <?php if ($error): ?>
                <div class="alerta error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="agregar_criterio.php">
                <div class="campo-editar">
                    <label for="nombre_criterio">Nombre del Criterio:</label>
                    <input type="text" id="nombre_criterio" name="nombre_criterio" class="form-control"
                           placeholder="Ej: Coreografía y Sincronización"
                           value="<?= htmlspecialchars($_POST['nombre_criterio'] ?? '') ?>" required>
                </div>

                <div class="campo-editar">
                    <label for="valor">Valor / Porcentaje (%):</label>
                    <input type="number" id="valor" name="valor" step="0.01" min="0.01" max="100" class="form-control"
                           placeholder="Ej: 25.00"
                           value="<?= htmlspecialchars($_POST['valor'] ?? '') ?>" required>
                    <small class="form-text text-muted" style="color: var(--color-text-muted); font-size: 13px; display: block; margin-top: 4px;">
                        Indique el porcentaje con el que este criterio ponderará en la calificación final.
                    </small>
                </div>

                <div class="botones-editar">
                    <button type="submit" class="btn-admin">Guardar Criterio</button>
                    <a href="panel_criterios.php" class="btn-eliminar">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php include '../includes/PiePagina.php'; ?>

</body>
</html>