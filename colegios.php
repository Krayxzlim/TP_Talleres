<?php
session_start();
include("includes/header.php");
include("includes/nav.php");

$file = "data/colegios.json";
$colegios = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

$mensaje = "";
$editando = false;
$edit_id = -1;

// Eliminar colegio
if (isset($_POST['eliminar'])) {
    $id = $_POST['eliminar'];
    unset($colegios[$id]);
    $colegios = array_values($colegios);
    file_put_contents($file, json_encode($colegios, JSON_PRETTY_PRINT));
    header("Location: colegios.php");
    exit;
}

// Iniciar edición
if (isset($_POST['editar'])) {
    $edit_id = $_POST['editar'];
    $editando = true;
}

// Guardar edición
if (isset($_POST['guardar_edicion'])) {
    $id = $_POST['id'];
    $nuevo_nombre = trim($_POST["nombre"]);
    $nueva_direccion = trim($_POST["direccion"]);

    $duplicado = false;
    foreach ($colegios as $idx => $c) {
        if ($idx != $id && strtolower($c["nombre"]) === strtolower($nuevo_nombre)) {
            $duplicado = true;
            break;
        }
    }

    if ($duplicado) {
        $mensaje = "No se puede guardar. Ya existe otro colegio con ese nombre.";
        $editando = true;
        $edit_id = $id;
    } else {
        $colegios[$id] = [
            "nombre" => $nuevo_nombre,
            "direccion" => $nueva_direccion,
            "primario" => isset($_POST["primario"]),
            "secundario" => isset($_POST["secundario"])
        ];
        file_put_contents($file, json_encode($colegios, JSON_PRETTY_PRINT));
        $mensaje = "Colegio editado correctamente.";
        $editando = false;
    }
}

// Agregar colegio
if (isset($_POST['agregar'])) {
    $nuevo_nombre = trim($_POST["nombre"]);
    $nueva_direccion = trim($_POST["direccion"]);

    $duplicado = false;
    foreach ($colegios as $c) {
        if (strtolower($c["nombre"]) === strtolower($nuevo_nombre)) {
            $duplicado = true;
            break;
        }
    }

    if ($duplicado) {
        $mensaje = "Ya existe un colegio con ese nombre.";
    } else {
        $nuevo = [
            "nombre" => $nuevo_nombre,
            "direccion" => $nueva_direccion,
            "primario" => isset($_POST["primario"]),
            "secundario" => isset($_POST["secundario"])
        ];
        $colegios[] = $nuevo;
        file_put_contents($file, json_encode($colegios, JSON_PRETTY_PRINT));
        $mensaje = "Colegio agregado correctamente.";
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
                        <div class="alert <?= str_contains($mensaje, 'correctamente') ? 'alert-success' : 'alert-danger' ?>">
                            <?= $mensaje ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <?php if ($editando): ?>
                            <input type="hidden" name="id" value="<?= $edit_id ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nombre:</label>
                            <input type="text" name="nombre" class="form-control" required
                                value="<?= $editando ? $colegios[$edit_id]['nombre'] : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección:</label>
                            <input type="text" name="direccion" class="form-control" required
                                value="<?= $editando ? $colegios[$edit_id]['direccion'] : '' ?>">
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="primario"
                                <?= $editando && $colegios[$edit_id]['primario'] ? 'checked' : '' ?>>
                            <label class="form-check-label">Primario</label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="secundario"
                                <?= $editando && $colegios[$edit_id]['secundario'] ? 'checked' : '' ?>>
                            <label class="form-check-label">Secundario</label>
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

        <!-- Tabla de colegios -->
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
                                    <?php foreach ($colegios as $idx => $c): ?>
                                        <tr>
                                            <td><?= $c['nombre'] ?></td>
                                            <td><?= $c['direccion'] ?></td>
                                            <td>
                                                <?= $c['primario'] ? "Primario " : "" ?>
                                                <?= $c['secundario'] ? "Secundario" : "" ?>
                                            </td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="editar" value="<?= $idx ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Editar</button>
                                                </form>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="eliminar" value="<?= $idx ?>">
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
