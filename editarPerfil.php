<?php
session_start();
include 'conexion/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id      = $_SESSION['id'];
$mensaje = '';
$tipo    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo   = trim($_POST['correo']);
    $nueva_pass = trim($_POST['nueva_contrasena']);
    $confirmar  = trim($_POST['confirmar_contrasena']);

    if (empty($nombre) || empty($apellido) || empty($correo)) {
        $mensaje = "El nombre, apellido y correo son obligatorios.";
        $tipo    = "error";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El correo no tiene un formato válido.";
        $tipo    = "error";

    } else {
        // Verificar que el correo no esté en uso por otro usuario
        $check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
        $check->bind_param("si", $correo, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = "Ese correo ya está registrado por otro usuario.";
            $tipo    = "error";

        } elseif (!empty($nueva_pass)) {
            // Quiere cambiar contraseña
            if ($nueva_pass !== $confirmar) {
                $mensaje = "Las contraseñas no coinciden.";
                $tipo    = "error";
            } elseif (strlen($nueva_pass) < 6) {
                $mensaje = "La contraseña debe tener al menos 6 caracteres.";
                $tipo    = "error";
            } else {
                $hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, apellido=?, correo=?, contrasena=? WHERE id=?");
                $stmt->bind_param("ssssi", $nombre, $apellido, $correo, $hash, $id);
                $stmt->execute();
                $_SESSION['nombre'] = $nombre;
                $mensaje = "¡Perfil actualizado correctamente (incluyendo contraseña)!";
                $tipo    = "exito";
            }
        } else {
            // Solo datos personales
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, apellido=?, correo=? WHERE id=?");
            $stmt->bind_param("sssi", $nombre, $apellido, $correo, $id);
            $stmt->execute();
            $_SESSION['nombre'] = $nombre;
            $mensaje = "¡Perfil actualizado correctamente!";
            $tipo    = "exito";
        }
        $check->close();
    }
}

// ── CARGAR DATOS ACTUALES ────────────────────────────────────────────────────
$stmt = $conexion->prepare("SELECT nombre, apellido, correo, rol, fecha_registro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result  = $stmt->get_result();
$usuario = $result->fetch_assoc();

$pagina_volver = ($usuario['rol'] === 'docente') ? 'Panel_docentes.php' : 'Panel_estudiantes.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil — El Colegio Tiene Talento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="actualizar">
        
        <div class="perfil-body">
            
            <?php if ($mensaje): ?>
                <div class="alerta <?= $tipo ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
                <?php endif; ?>
                
                <div class="container mt-6">
                    <div class="row justify-content-center">
                        <div class="col-md-5">
                            <div class="card">
                                <div class="recuadro card-header text-center">
                                    <h4>Editar Perfil</h4>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="editarPerfil.php">
                                        
                                        <p class="Categorias">Datos personales</p>
                                        
                                        <div class="">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre:</label>
                                                <input type="text" name="nombre" class="form-control"
                                                value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Apellido:</label>
                                                <input type="text" name="apellido" class="form-control"
                                                value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Correo electrónico:</label>
                                            <input type="email" name="correo" class="form-control"
                                            value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                                        </div>
                                        
                                        <p class="Categorias">Cambiar contraseña <span style="color:#ccc">(opcional)</span></p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Nueva contraseña</label>
                                            <input type="password" name="nueva_contrasena" class="form-control"
                                            placeholder="Déjalo en blanco para no cambiarla">
                                            <p class="Categorias">Mínimo 6 caracteres.</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Confirmar contraseña</label>
                                            <input type="password" name="confirmar_contrasena" class="form-control"
                                            placeholder="Repite la nueva contraseña">
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn-guardar">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="<?= $pagina_volver ?>" class="btn-volver">← Volver al panel</a>
                
                <div class="info-pie">
                    Rol: <span><?= htmlspecialchars($usuario['rol']) ?></span>
                    &nbsp;·&nbsp;
                    Registrado el: <span><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></span>
                </div>
                
            </div>
        </div>
        
    </body>
    </html>