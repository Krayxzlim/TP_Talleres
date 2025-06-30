<?php
session_start();
include("includes/header.php");
include("includes/nav.php");
require_once("includes/funciones.php");

$mensaje = "";
$editando = false;
$edit_id = null;

$colegios = obtenerColegios();

// Eliminar colegio
if (isset($_POST['eliminar'])) {
    $id = intval($_POST['eliminar']);
    if (eliminarColegio($id)) {
        header("Location: colegios.php");
        exit;
    } else {
        $mensaje = "Error al eliminar el colegio.";
    }
}

// Iniciar edición
if (isset($_POST['editar'])) {
    $edit_id = intval($_POST['editar']);
    $editando = true;
    $colegio_edit = obtenerColegioPorId($edit_id);
    if (!$colegio_edit) {
        $mensaje = "Colegio no encontrado.";
        $editando = false;
        $edit_id = null;
    }
}

// Guardar edición
if (isset($_POST['guardar_edicion'])) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $primario = isset($_POST['primario']) ? 1 : 0;
    $secundario = isset($_POST['secundario']) ? 1 : 0;

    if (colegioExisteNombre($nombre, $id)) {
        $mensaje = "No se puede guardar. Ya existe otro colegio con ese nombre.";
        $editando = true;
        $edit_id = $id;
        $colegio_edit = ['nombre'=>$nombre,'direccion'=>$direccion,'primario'=>$primario,'secundario'=>$secundario];
    } else {
        if (editarColegio($id, $nombre, $direccion, $primario, $secundario)) {
            $mensaje = "Colegio editado correctamente.";
            $editando = false;
            $edit_id = null;
            $colegios = obtenerColegios();
        } else {
            $mensaje = "Error al editar el colegio.";
            $editando = true;
            $edit_id = $id;
        }
    }
}

// Agregar colegio
if (isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $primario = isset($_POST['primario']) ? 1 : 0;
    $secundario = isset($_POST['secundario']) ? 1 : 0;

    if (colegioExisteNombre($nombre)) {
        $mensaje = "Ya existe un colegio con ese nombre.";
    } else {
        if (agregarColegio($nombre, $direccion, $primario, $secundario)) {
            $mensaje = "Colegio agregado correctamente.";
            $colegios = obtenerColegios();
        } else {
            $mensaje = "Error al agregar el colegio.";
        }
    }
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-3">
                        <?= $editando ? "Editar Colegio" : "Agregar Nuevo Colegio" ?>
                    </h3>

                    <?php if (!empty($mensaje)): ?>
                        <div class="alert <?= strpos($mensaje, 'correctamente') !== false ? 'alert-success' : 'alert-danger' ?>">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <?php if ($editando): ?>
                            <input type="hidden" name="id" value="<?= $edit_id ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nombre:</label>
                            <input type="text" name="nombre" class="form-control" required
                                value="<?= $editando ? htmlspecialchars($colegio_edit['nombre']) : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección:</label>
                            <input type="text" name="direccion" class="form-control" required
                                value="<?= $editando ? htmlspecialchars($colegio_edit['direccion']) : '' ?>">
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="primario" id="primario"
                                <?= ($editando && $colegio_edit['primario']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="primario">Primario</label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="secundario" id="secundario"
                                <?= ($editando && $colegio_edit['secundario']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="secundario">Secundario</label>
                        </div>

                        <button type="submit" class="btn btn-primary" name="<?= $editando ? 'guardar_edicion' : 'agregar' ?>">
                            <?= $editando ? "Guardar Cambios" : "Agregar Colegio" ?>
                        </button>
                        <?php if ($editando): ?>
                            <a href="colegios.php" class="btn btn-secondary ms-2">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-3">Colegios Registrados</h3>
                    <?php if (count($colegios) === 0): ?>
                        <p class="text-muted">No hay colegios registrados aún.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Dirección</th>
                                        <th>Niveles</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($colegios as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                                            <td><?= htmlspecialchars($c['direccion']) ?></td>
                                            <td>
                                                <?= $c['primario'] ? "Primario " : "" ?>
                                                <?= $c['secundario'] ? "Secundario" : "" ?>
                                            </td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="editar" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Editar</button>
                                                </form>
                                                <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este colegio?');">
                                                    <input type="hidden" name="eliminar" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
