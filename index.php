<?php
session_start();
include("includes/header.php");
include("includes/nav.php");

$archivo = "data/agenda.json";
$agenda = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];

// Cargar colegios desde JSON
$colegios_json = "data/colegios.json";
$colegios_lista = file_exists($colegios_json) ? json_decode(file_get_contents($colegios_json), true) : [];

// Talleres predefinidos
$talleres_opciones = [
    "Creatividad Digital",
    "Cuidado del Entorno",
    "Ciencia en Acción"
];

// Cargar usuarios tipo tallerista
$usuarios_file = "data/usuarios_registrados.json";
$usuarios_all = file_exists($usuarios_file) ? json_decode(file_get_contents($usuarios_file), true) : [];
$talleristas_disponibles = array_filter($usuarios_all, fn($u) => $u['rol'] === 'tallerista');

$mensaje = "";
$editando = false;
$edit_id = -1;

// Eliminar
if (isset($_POST['eliminar']) && isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista') {
    unset($agenda[$_POST['eliminar']]);
    $agenda = array_values($agenda);
    file_put_contents($archivo, json_encode($agenda, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// Editar
if (isset($_POST['editar']) && isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista') {
    $edit_id = $_POST['editar'];
    $editando = true;
}

// Guardar edición
if (isset($_POST['guardar_edicion']) && isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista') {
    $id = $_POST['id'];
    $agenda[$id]["colegio"] = $_POST["colegio"];
    $agenda[$id]["taller"] = $_POST["taller"];
    $agenda[$id]["fecha"] = $_POST["fecha"];
    $agenda[$id]["hora"] = $_POST["hora"];
    file_put_contents($archivo, json_encode($agenda, JSON_PRETTY_PRINT));
    $mensaje = "Taller editado correctamente.";
    $editando = false;
}

// Agregar
if (isset($_POST['agregar']) && isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista') {
    $nuevo = [
        "colegio" => $_POST["colegio"],
        "taller" => $_POST["taller"],
        "fecha" => $_POST["fecha"],
        "hora" => $_POST["hora"],
        "talleristas" => []
    ];
    $agenda[] = $nuevo;
    file_put_contents($archivo, json_encode($agenda, JSON_PRETTY_PRINT));
    $mensaje = "Taller agendado correctamente.";
}

// Asignar tallerista
if (isset($_POST['asignar_tallerista']) && isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista') {
    $id = $_POST['taller_id'];
    $nuevo_tallerista = $_POST['nuevo_tallerista'];

    if (!isset($agenda[$id]['talleristas'])) {
        $agenda[$id]['talleristas'] = [];
    }

    if (in_array($nuevo_tallerista, $agenda[$id]['talleristas'])) {
        $mensaje = "Ese tallerista ya está asignado.";
    } elseif (count($agenda[$id]['talleristas']) >= 2) {
        $mensaje = "Ya hay 2 talleristas asignados.";
    } else {
        $agenda[$id]['talleristas'][] = $nuevo_tallerista;
        file_put_contents($archivo, json_encode($agenda, JSON_PRETTY_PRINT));
        $mensaje = "Tallerista asignado correctamente.";
    }
}
?>

<h2 class="text-center my-4">Agenda de Talleres</h2>

<!-- Mensajes -->
<?php if (!empty($mensaje)): ?>
    <div class="alert alert-info text-center" role="alert">
        <?= $mensaje ?>
    </div>
<?php endif; ?>


<!-- FORMULARIO CRUD (solo talleristas) -->
<?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista'): ?>
    <div class="container mt-4">
        <?php if ($editando): ?>
            <h3>Editar entrada de agenda</h3>
            <form method="post" class="form-group">
                <input type="hidden" name="id" value="<?= $edit_id ?>">
                
                <div class="form-group">
                    <label for="colegio">Colegio:</label>
                    <select name="colegio" class="form-control" required>
                        <?php foreach ($colegios_lista as $c): ?>
                            <option value="<?= $c['nombre'] ?>" <?= ($agenda[$edit_id]['colegio'] === $c['nombre']) ? 'selected' : '' ?>>
                                <?= $c['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="taller">Taller:</label>
                    <select name="taller" class="form-control" required>
                        <?php foreach ($talleres_opciones as $taller): ?>
                            <option value="<?= $taller ?>" <?= ($agenda[$edit_id]['taller'] === $taller) ? 'selected' : '' ?>>
                                <?= $taller ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" class="form-control" value="<?= $agenda[$edit_id]['fecha'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="hora">Hora:</label>
                    <input type="time" name="hora" class="form-control" value="<?= $agenda[$edit_id]['hora'] ?>" required>
                </div>

                <button type="submit" name="guardar_edicion" class="btn btn-success">Guardar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php else: ?>
            <h3>Agregar nuevo taller</h3>
            <form method="post" class="form-group">
                <div class="form-group">
                    <label for="colegio">Colegio:</label>
                    <select name="colegio" class="form-control" required>
                        <?php foreach ($colegios_lista as $c): ?>
                            <option value="<?= $c['nombre'] ?>"><?= $c['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="taller">Taller:</label>
                    <select name="taller" class="form-control" required>
                        <?php foreach ($talleres_opciones as $taller): ?>
                            <option value="<?= $taller ?>"><?= $taller ?></option>
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


<!-- LISTADO VISIBLE PARA TODOS -->
<h3 class="mt-4">Listado de talleres agendados</h3>
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
            <?php foreach ($agenda as $idx => $item): ?>
                <tr>
                    <td><?= $item['colegio'] ?></td>
                    <td><?= $item['taller'] ?></td>
                    <td><?= $item['fecha'] ?></td>
                    <td><?= $item['hora'] ?></td>
                    <td>
                        <?php
                        $asignados = $item['talleristas'] ?? [];
                        echo empty($asignados) ? "Ninguno" : implode(", ", $asignados);
                        ?>
                    </td>
                    <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'tallerista'): ?>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="editar" value="<?= $idx ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Editar</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="eliminar" value="<?= $idx ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>

                            <?php if (count($item['talleristas'] ?? []) < 2): ?>
                                <form method="post" class="d-inline mt-2">
                                    <input type="hidden" name="taller_id" value="<?= $idx ?>">
                                    <select name="nuevo_tallerista" class="form-control form-control-sm" required>
                                        <option value="" disabled selected>Seleccionar tallerista</option>
                                        <?php foreach ($talleristas_disponibles as $u): ?>
                                            <?php if (!in_array($u['usuario'], $item['talleristas'] ?? [])): ?>
                                                <option value="<?= $u['usuario'] ?>"><?= $u['usuario'] ?></option>
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
