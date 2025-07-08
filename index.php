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
$talleres_opciones = obtenerTalleres();

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
    $taller_id = intval($_POST['taller_id'] ?? 0);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    // Validaciones básicas
    if (!$colegio || !$taller_id || !$fecha || !$hora) {
        $mensaje = "Complete todos los campos.";
    } else {
        if (agregarEvento($colegio, $taller_id, $fecha, $hora)) {
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
    $taller_id = intval($_POST['taller_id'] ?? 0);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    if (!$colegio || !$taller_id || !$fecha || !$hora) {
        $mensaje = "Complete todos los campos.";
        $editando = true;
        $evento = obtenerEventoPorId($id);
    } else {
        if (editarEvento($id, $colegio, $taller_id, $fecha, $hora)) {
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
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const mensaje = <?= json_encode($mensaje) ?>;
    const modalBody = document.getElementById('mensajeModalBody');
    const modalHeader = document.querySelector('#mensajeModal .modal-header');
    const modalTitle = document.getElementById('mensajeModalLabel');

    modalBody.textContent = mensaje;

    modalHeader.classList.remove('bg-danger', 'bg-success', 'text-white');

    const palabrasError = [
      'error',
      'ya está asignado',
      'ya hay 2',
    ];

    function esError(mensaje) {
      mensaje = mensaje.toLowerCase();
      return palabrasError.some(palabra => mensaje.includes(palabra));
    }

    if (esError(mensaje)) {
      modalHeader.classList.add('bg-danger', 'text-white');
      modalTitle.textContent = 'Error';
    } else {
      modalHeader.classList.add('bg-success', 'text-white');
      modalTitle.textContent = 'Confirmado';
    }

    const mensajeModal = new bootstrap.Modal(document.getElementById('mensajeModal'));
    mensajeModal.show();
  });
</script>
<?php endif; ?>


<div id='calendar'></div>

<!-- Modal para mostrar mensajes -->
<div class="modal fade" id="mensajeModal" tabindex="-1" aria-labelledby="mensajeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mensajeModalLabel">Notificación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="mensajeModalBody">
        <!-- Aquí se inserta el mensaje dinámicamente -->
      </div>
    </div>
  </div>
</div>


<!-- Modal: Agregar Taller -->
<div class="modal fade" id="modalAgregarTaller" tabindex="-1" aria-labelledby="modalAgregarTallerLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-dark">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarTallerLabel">Agregar Nuevo Taller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-start">
                        <label for="colegio" class="form-label">Colegio</label>
                        <select name="colegio" class="form-select" required>
                            <?php foreach ($colegios_lista as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="taller_id" class="form-label">Taller</label>
                        <select name="taller_id" class="form-select" required>
                            <?php foreach ($talleres_opciones as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="hora" class="form-label">Hora</label>
                        <input type="time" name="hora" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="agregar" class="btn btn-primary">Agregar Taller</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Detalle de Evento -->
<div class="modal fade" id="modalDetalleEvento" tabindex="-1" aria-labelledby="modalDetalleEventoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleEventoLabel">Detalle del Taller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">



                <div id="contenidoDetalleEvento"></div>

                <?php if (isset($_SESSION['usuario'])): ?>
                    <!-- Este formulario se muestra//oculta -->
                    <form method="post" class="mt-3 d-flex gap-2 align-items-end d-none" id="formAsignarTallerista">
                        <input type="hidden" id="eventoIdDetalle" name="taller_id" value="">
                        <select name="nuevo_tallerista" class="form-select form-select-sm w-auto" required>
                            <option value="" selected disabled>Seleccionar tallerista</option>
                            <?php foreach ($talleristas_disponibles as $u): ?>
                                <option value="<?= htmlspecialchars($u['usuario']) ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="asignar_tallerista" class="btn btn-info btn-sm">Asignar</button>
                    </form>

                    <!-- Este lo muestra si ya hay 2 -->
                    <p class="text-muted mt-3 d-none" id="mensajeYaDos">Ya hay 2 talleristas asignados para este taller.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <form method="post" class="d-inline">
                        <input type="hidden" id="eventoIdEditar" name="editar" value="">
                        <button type="button" id="btnEditarEvento" class="btn btn-warning">Editar</button>
                    </form>
                    <form method="post" class="d-inline">
                        <input type="hidden" id="eventoIdEliminar" name="eliminar" value="">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este taller?')">Eliminar</button>
                    </form>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de bienvenida -->
<?php if (isset($_SESSION['usuario'])): ?>
<div class="modal fade" id="bienvenidaModal" tabindex="-1" aria-labelledby="bienvenidaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="bienvenidaLabel">¡Bienvenido/a!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        Hola <?= htmlspecialchars($_SESSION['usuario']['usuario']) ?>, gracias por ingresar al Portal de Talleres.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Comenzar</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include("includes/footer.php"); ?>
