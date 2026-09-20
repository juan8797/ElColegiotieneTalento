<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

require_once '../../conexion/db.php';

$mensaje = "";

// Función para calcular la profundidad de una categoría (1 = raíz, 2 = subcategoría, 3 = sub-sub)
function calcularProfundidad($conexion, $id) {
    $profundidad = 1;
    while ($id !== null) {
        $stmt = $conexion->prepare("SELECT padre_id FROM categorias_participacion WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($fila && $fila['padre_id'] !== null) {
            $profundidad++;
            $id = $fila['padre_id'];
        } else {
            $id = null;
        }
    }
    return $profundidad;
}

// Agregar categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $modalidad = $_POST['modalidad'];
    $nombre = trim($_POST['nombre']);
    $padre_id = $_POST['padre_id'] !== '' ? (int)$_POST['padre_id'] : null;

    if ($nombre === '') {
        $mensaje = "El nombre no puede estar vacío.";
    } elseif ($padre_id !== null && calcularProfundidad($conexion, $padre_id) >= 3) {
        $mensaje = "No puedes agregar más niveles: el máximo permitido es 3.";
    } else {
        $stmt = $conexion->prepare(
            "INSERT INTO categorias_participacion (modalidad, nombre, padre_id) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("ssi", $modalidad, $nombre, $padre_id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Categoría agregada correctamente.";
    }
}

// Eliminar categoría (el ON DELETE CASCADE se encarga de los hijos)
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM categorias_participacion WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Categoría eliminada correctamente (junto con sus subcategorías, si tenía).";
}

// Traemos TODAS las categorías para armar el árbol en PHP
$todas = [];
$resultado = $conexion->query("SELECT * FROM categorias_participacion ORDER BY modalidad, nombre ASC");
while ($fila = $resultado->fetch_assoc()) {
    $todas[] = $fila;
}

// Función recursiva para imprimir el árbol con indentación según el nivel
function imprimirArbol($categorias, $padre_id, $modalidad, $nivel = 0) {
    foreach ($categorias as $cat) {
        if ($cat['modalidad'] === $modalidad && $cat['padre_id'] == $padre_id) {
            $nivelNum = $nivel + 1;
            echo '<tr>';
            echo '<td>';
            echo '<div class="arbol-categoria-item nivel-' . $nivelNum . '">';
            if ($nivel > 0) {
                echo '<span class="arbol-guia">' . str_repeat('&mdash; ', $nivel) . '</span>';
            }
            echo '<span class="arbol-nombre">' . htmlspecialchars($cat['nombre']) . '</span>';
            echo '</div>';
            echo '</td>';
            echo '<td><span class="badge-nivel badge-nivel-' . $nivelNum . '">Nivel ' . $nivelNum . '</span></td>';
            echo '<td class="col-accion"><a href="?eliminar=' . $cat['id'] . '" class="btn-eliminar btn-sm">Eliminar</a></td>';
            echo '</tr>';
            imprimirArbol($categorias, $cat['id'], $modalidad, $nivel + 1);
        }
    }
}

// Para el <select> de "categoría padre", solo mostramos las que tienen profundidad menor a 3
$opcionesPadre = [];
foreach ($todas as $cat) {
    if (calcularProfundidad($conexion, $cat['id']) < 3) {
        $opcionesPadre[] = $cat;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Categorías</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <?php include '../../includes/menu_admin.php'; ?>

<main class="container-fluid">
    <div class="encabezado-panel">
        <h2>Panel Administrador — Categorías de Participación</h2>
    </div>
    <div class="explanation-table">
        <p class="text-explanation">
            Aquí puedes crear las categorías y subcategorías (hasta 3 niveles) que los
            estudiantes elegirán al registrarse en Talento Individual o Demostración Deportiva.
        </p>
    </div>

    <?php if ($mensaje): ?>
        <p class="mensaje-admin"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <div class="seccion-usuarios">
        <div class="seccion-usuarios-cabecera">
            <div class="seccion-usuarios-titulo">
                <h3>Agregar nueva categoría</h3>
                <span class="subtitulo-seccion-admin">Crea una categoría principal (nivel 1) o una subcategoría (hasta nivel 3)</span>
            </div>
        </div>
        <form action="" method="POST" class="form-grid-categorias">
            <div class="form-grid-item">
                <label for="modalidad">Modalidad:</label>
                <select name="modalidad" id="modalidad" class="form-control" required>
                    <option value="talento_individual">Talento Individual</option>
                    <option value="demostracion_deportiva">Demostración Deportiva</option>
                </select>
            </div>

            <div class="form-grid-item">
                <label for="nombre">Nombre de la categoría:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Instrumento, Cuerdas, Guitarra" required>
            </div>

            <div class="form-grid-item">
                <label for="padre_id">Categoría padre (opcional):</label>
                <select name="padre_id" id="padre_id" class="form-control">
                    <option value="">-- Sin padre (nivel 1) --</option>
                    <?php foreach ($opcionesPadre as $op): ?>
                        <option value="<?= $op['id'] ?>">
                            [<?= $op['modalidad'] === 'talento_individual' ? 'Individual' : 'Deportiva' ?>]
                            <?= htmlspecialchars($op['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-grid-btn">
                <button type="submit" name="agregar" class="btn-primary">Agregar Categoría</button>
            </div>
        </form>
    </div>

    <div class="seccion-usuarios">
        <div class="seccion-usuarios-cabecera">
            <div class="seccion-usuarios-titulo">
                <h3>Árbol de Categorías — Talento Individual</h3>
                <span class="subtitulo-seccion-admin">Jerarquía estructurada de categorías y subcategorías</span>
            </div>
        </div>
        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th style="width: 120px;">Nivel</th>
                    <th style="width: 130px;" class="col-accion">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php imprimirArbol($todas, null, 'talento_individual'); ?>
            </tbody>
        </table>
    </div>

    <div class="seccion-usuarios">
        <div class="seccion-usuarios-cabecera">
            <div class="seccion-usuarios-titulo">
                <h3>Árbol de Categorías — Demostración Deportiva</h3>
                <span class="subtitulo-seccion-admin">Jerarquía estructurada de categorías y subcategorías</span>
            </div>
        </div>
        <table class="tabla-participaciones">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th style="width: 120px;">Nivel</th>
                    <th style="width: 130px;" class="col-accion">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php imprimirArbol($todas, null, 'demostracion_deportiva'); ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../../includes/PiePagina.php'; ?>
</body>
</html>