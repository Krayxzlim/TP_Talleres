<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once("includes/db.php");
require_once("includes/funciones.php");
include("includes/header.php");
include("includes/nav.php");

// Obtener datos necesarios
$agenda = obtenerAgendaCompleta();
$colegios_lista = obtenerColegios(); // de la tabla colegios
$talleres_opciones = ["Creatividad Digital", "Cuidado del Entorno", "Ciencia en Acción"];

// Obtener todos los usuarios con rol tallerista
$usuarios_all = obtenerUsuarios(); // función que debes crear en usuarios_db.php
$talleristas_disponibles = array_filter($usuarios_all, fn($u) => $u['rol'] === 'tallerista');

$mensaje = "";
$editando = false;
$edit_id = -1;
$evento = null; // para guardar evento al editar

// Crear evento
if (isset($_POST['agregar'])) {
    $colegio = trim(filter_input(INPUT_POST, 'colegio'));
    $taller = trim(filter_input(INPUT_POST, 'taller'));
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    // Validaciones básicas
    if (!$colegio || !$taller || !$fecha || !$hora) {
        $mensaje = "Complete todos los campos.";
    } else {
        if (agregarEvento($colegio, $taller, $fecha, $hora)) {
            $mensaje = "Taller agendado correctamente.";
            $agenda = obtenerAgendaCompleta();
        } else {
            $mensaje = "Error al agregar taller.";
        }
    }
}

// Editar evento (mostrar formulario)
if (isset($_POST['editar'])) {
    $edit_id = intval($_POST['editar']);
    $evento = obtenerEventoPorId($edit_id);
    if ($evento) {
        $editando = true;
    } else {
        $mensaje = "Evento no encontrado.";
    }
}

// Guardar edición
if (isset($_POST['guardar_edicion'])) {
    $id = intval($_POST['id']);
    $colegio = trim(filter_input(INPUT_POST, 'colegio'));
    $taller = trim(filter_input(INPUT_POST, 'taller'));
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    if (!$colegio || !$taller || !$fecha || !$hora) {
        $mensaje = "Complete todos los campos.";
        $editando = true;
        $evento = obtenerEventoPorId($id);
    } else {
        if (editarEvento($id, $colegio, $taller, $fecha, $hora)) {
            $mensaje = "Taller editado correctamente.";
            $agenda = obtenerAgendaCompleta();
            $editando = false;
        } else {
            $mensaje = "Error al editar taller.";
            $editando = true;
            $evento = obtenerEventoPorId($id);
        }
    }
}

// Eliminar evento
if (isset($_POST['eliminar'])) {
    $id = intval($_POST['eliminar']);
    if (eliminarEvento($id)) {
        header("Location: index.php");
        exit;
    } else {
        $mensaje = "Error al eliminar el taller.";
    }
}

// Asignar tallerista
if (isset($_POST['asignar_tallerista'])) {
    $agenda_id = intval($_POST['taller_id']);
    $nuevo_tallerista = trim(filter_input(INPUT_POST, 'nuevo_tallerista'));
    if (!$nuevo_tallerista) {
        $mensaje = "Seleccione un tallerista válido.";
    } else {
        $resultado = asignarTallerista($agenda_id, $nuevo_tallerista);
        if ($resultado === "ok") {
            $mensaje = "Tallerista asignado correctamente.";
            $agenda = obtenerAgendaCompleta();
        } else {
            $mensaje = $resultado;
        }
    }
}
?>

<h2 class="text-center my-4">Agenda de Talleres</h2>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-info text-center" role="alert">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista'): ?>
    <div class="container mt-4">
        <?php if ($editando && $evento): ?>
            <h3>Editar entrada de agenda</h3>
            <form method="post" class="form-group">
                <input type="hidden" name="id" value="<?= $edit_id ?>">

                <div class="form-group">
                    <label for="colegio">Colegio:</label>
                        <select name="colegio" class="form-control" required>
                            <?php foreach ($colegios_lista as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (($evento['colegio_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                </div>

                <div class="form-group">
                    <label for="taller">Taller:</label>
                    <select name="taller" class="form-control" required>
                        <?php foreach ($talleres_opciones as $taller): ?>
                            <option value="<?= htmlspecialchars($taller) ?>" <?= (($evento['taller'] ?? '') === $taller) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($taller) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($evento['fecha'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="hora">Hora:</label>
                    <input type="time" name="hora" class="form-control" value="<?= htmlspecialchars($evento['hora'] ?? '') ?>" required>
                </div>

                <button type="submit" name="guardar_edicion" class="btn btn-success">Guardar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php elseif ($editando): ?>
            <div class="alert alert-danger">No se encontró el evento que se desea editar.</div>
        <?php else: ?>
            <h3>Agregar nuevo taller</h3>
            <form method="post" class="form-group">
                <div class="form-group">
                    <label for="colegio">Colegio:</label>
                    <select name="colegio" class="form-control" required>
                        <?php foreach ($colegios_lista as $c): ?>
                            <option value="<?= htmlspecialchars($c['nombre']) ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="taller">Taller:</label>
                    <select name="taller" class="form-control" required>
                        <?php foreach ($talleres_opciones as $taller): ?>
                            <option value="<?= htmlspecialchars($taller) ?>"><?= htmlspecialchars($taller) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="hora">Hora:</label>
                    <input type="time" name="hora" class="form-control" required>
                </div>

                <button type="submit" name="agregar" class="btn btn-primary">Agregar Taller</button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>


<h3 class="mt-4 text-center">Listado de talleres agendados</h3>
<div id='calendar'></div>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Colegio</th>
                <th>Taller</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Talleristas</th>
                <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista'): ?>
                    <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($agenda as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['colegio_nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['taller']?? '') ?></td>
                    <td><?= htmlspecialchars($item['fecha']?? '') ?></td>
                    <td><?= htmlspecialchars($item['hora']?? '') ?></td>
                    <td>
                        <?php
                        $asignados = $item['talleristas'] ?? [];
                        echo empty($asignados) ? "Ninguno" : htmlspecialchars(implode(", ", $asignados));
                        ?>
                    </td>
                    <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista'): ?>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="editar" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Editar</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="eliminar" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>

                            <?php if (count($asignados) < 2): ?>
                                <form method="post" class="d-inline mt-2">
                                    <input type="hidden" name="taller_id" value="<?= $item['id'] ?>">
                                    <select name="nuevo_tallerista" class="form-control form-control-sm" required>
                                        <option value="" disabled selected>Seleccionar tallerista</option>
                                        <?php foreach ($talleristas_disponibles as $u): ?>
                                            <?php if (!in_array($u['usuario'], $asignados)): ?>
                                                <option value="<?= htmlspecialchars($u['usuario']) ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="asignar_tallerista" class="btn btn-info btn-sm mt-2">Asignar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include("includes/footer.php"); ?>
