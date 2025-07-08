<?php
session_start();
require_once("includes/db.php");
require_once("includes/funciones.php");

// Verificación de acceso
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

// Obtener usuarios
$usuarios = obtenerUsuarios();

// Eliminar o cambiar rol
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_usuario'])) {
        $usuario = $_POST['eliminar_usuario'];
        if (eliminarUsuarioPorNombre($usuario)) {
            $mensaje = "Usuario eliminado correctamente.";
            $usuarios = obtenerUsuarios();
        } else {
            $mensaje = "Error al eliminar usuario.";
        }
    }

    if (isset($_POST['cambiar_rol'])) {
        $usuario = $_POST['cambiar_rol'];
        $nuevoRol = $_POST['nuevo_rol'];
        if (cambiarRolUsuario($usuario, $nuevoRol)) {
            $mensaje = "Rol actualizado.";
            $usuarios = obtenerUsuarios();
        } else {
            $mensaje = "Error al cambiar el rol.";
        }
    }
}
?>

<?php include("includes/header.php"); ?>
<?php include("includes/nav.php"); ?>

<div class="container my-5">
    <h2 class="mb-4">Administración de Usuarios</h2>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php
                if ($_GET['msg'] === 'eliminado') echo "Usuario eliminado correctamente.";
                elseif ($_GET['msg'] === 'rol') echo "Rol del usuario actualizado.";
            ?>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php
                if ($_GET['error'] === 'eliminar') echo "Hubo un error al eliminar.";
                elseif ($_GET['error'] === 'rol') echo "No se pudo actualizar el rol.";
                elseif ($_GET['error'] === 'notfound') echo "Usuario no encontrado.";
            ?>
        </div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr class="fila-usuario" data-bs-toggle="modal" data-bs-target="#modalUsuario"
                        data-usuario="<?= htmlspecialchars($usuario['usuario']) ?>"
                        data-email="<?= htmlspecialchars($usuario['correo']) ?>"
                        data-rol="<?= htmlspecialchars($usuario['rol']) ?>">
                        <td class="fw-medium"><?= htmlspecialchars($usuario['usuario']) ?></td>
                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td><span class="badge bg-<?= $usuario['rol'] === 'admin' ? 'primary' : 'secondary' ?>">
                            <?= htmlspecialchars($usuario['rol']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal usr -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalUsuarioLabel">Información del Usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Usuario:</strong> <span id="modalUsuarioNombre"></span></p>
        <p><strong>Email:</strong> <span id="modalUsuarioEmail"></span></p>
        <p><strong>Rol:</strong> <span id="modalUsuarioRol"></span></p>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <form method="POST" action="includes/cambiar_rol.php" class="m-0">
            <input type="hidden" name="usuario" id="formRolUsuario">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-person-gear me-1"></i> Cambiar Rol
            </button>
        </form>
        <form method="POST" action="includes/eliminar_usuario.php" class="m-0" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
            <input type="hidden" name="usuario" id="formEliminarUsuario">
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-trash me-1"></i> Eliminar
            </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.fila-usuario').forEach(fila => {
    fila.addEventListener('click', () => {
        const usuario = fila.dataset.usuario;
        const email = fila.dataset.email;
        const rol = fila.dataset.rol;

        document.getElementById('modalUsuarioNombre').textContent = usuario;
        document.getElementById('modalUsuarioEmail').textContent = email;
        document.getElementById('modalUsuarioRol').textContent = rol;

        document.getElementById('formRolUsuario').value = usuario;
        document.getElementById('formEliminarUsuario').value = usuario;
    });
});
</script>

<?php include("includes/footer.php"); ?>