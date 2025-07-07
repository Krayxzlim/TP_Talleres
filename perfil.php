<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario_actual = $_SESSION['usuario'];
$mensaje = "";

$carpeta_subida = "uploads/"; // carpeta de las fotos
if (!is_dir($carpeta_subida)) {
    mkdir($carpeta_subida, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevo_correo = filter_var(trim($_POST["correo"]), FILTER_SANITIZE_EMAIL);
    $nueva_pass = $_POST["nueva_pass"] ?? "";
    $confirmar_pass = $_POST["confirmar_pass"] ?? "";
    $foto_subida = $_FILES['foto'] ?? null;

    try {
        $pdo = conectarDB();

        // Validación correo y pass
        if (!filter_var($nuevo_correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = '<div class="alert alert-danger">Correo no válido.</div>';
        } elseif (!empty($nueva_pass) && $nueva_pass !== $confirmar_pass) {
            $mensaje = '<div class="alert alert-danger">Las contraseñas no coinciden.</div>';
        } else {
            
            $pdo->beginTransaction();

            // Actualizar correo
            $stmt = $pdo->prepare("UPDATE usuarios SET correo = ? WHERE usuario = ?");
            $stmt->execute([$nuevo_correo, $usuario_actual['usuario']]);

            // Actualizar contraseña
            if (!empty($nueva_pass)) {
                $hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE usuario = ?");
                $stmt->execute([$hash, $usuario_actual['usuario']]);
            }

            // subida de foto
            if ($foto_subida && $foto_subida['error'] === UPLOAD_ERR_OK) {
                $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($foto_subida['type'], $tipos_permitidos)) {
                    throw new Exception("Tipo de archivo no permitido. Solo JPG, PNG, GIF.");
                }

                if ($foto_subida['size'] > 2 * 1024 * 1024) { // 2MB max
                    throw new Exception("Archivo demasiado grande. Máximo 2MB.");
                }

                // Generar nombre único
                $ext = pathinfo($foto_subida['name'], PATHINFO_EXTENSION);
                $nombre_archivo = uniqid('perfil_', true) . "." . $ext;

                $ruta_destino = $carpeta_subida . $nombre_archivo;

                if (!move_uploaded_file($foto_subida['tmp_name'], $ruta_destino)) {
                    throw new Exception("Error al guardar la imagen.");
                }

                // Guardar nombre archivo en DB
                $stmt = $pdo->prepare("UPDATE usuarios SET foto = ? WHERE usuario = ?");
                $stmt->execute([$nombre_archivo, $usuario_actual['usuario']]);

                // Actualizar
                $_SESSION['usuario']['foto'] = $nombre_archivo;
            }

            $pdo->commit();

            // Actualizar correo
            $_SESSION['usuario']['correo'] = $nuevo_correo;

            $mensaje = '<div class="alert alert-success">Perfil actualizado correctamente.</div>';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
include("includes/header.php");
include("includes/nav.php");
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Mi Perfil</h2>

        <?= $mensaje ?>

        <div class="text-center mb-4">
          <?php if (!empty($usuario_actual['foto']) && file_exists($carpeta_subida . $usuario_actual['foto'])): ?>
            <img src="<?= $carpeta_subida . htmlspecialchars($usuario_actual['foto']) ?>" alt="Foto de perfil" class="rounded-circle" width="120" height="120">
          <?php else: ?>
            <img src="https://www.gravatar.com/avatar?d=mp" alt="Foto de perfil" class="rounded-circle" width="120" height="120">
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label">Usuario</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($usuario_actual['usuario']) ?>" disabled>
        </div>

        <div class="mb-3">
          <label for="correo" class="form-label">Correo</label>
          <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($usuario_actual['correo']) ?>" required>
        </div>

        <hr>

        <h5>Cambiar contraseña</h5>
        <div class="mb-3">
          <label for="nueva_pass" class="form-label">Nueva contraseña</label>
          <input type="password" class="form-control" name="nueva_pass" placeholder="Dejar en blanco para no cambiar">
        </div>

        <div class="mb-3">
          <label for="confirmar_pass" class="form-label">Confirmar nueva contraseña</label>
          <input type="password" class="form-control" name="confirmar_pass" placeholder="Dejar en blanco para no cambiar">
        </div>

        <hr>

        <h5>Foto de perfil</h5>
        <div class="mb-3">
          <label for="foto" class="form-label">Subir nueva foto</label>
          <input type="file" class="form-control" name="foto" accept="image/*">
          <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</small>
        </div>

        <?php if ($usuario_actual['rol'] === 'admin'): ?>
        <div class="mb-3">
          <label class="form-label">Rol</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($usuario_actual['rol']) ?>" disabled>
        </div>
        <?php endif; ?>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>
