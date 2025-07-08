<footer class="bg-dark text-white text-center py-3 mt-auto">
    <div class="container">
        <p class="mb-0">© <?= date("Y"); ?> Portal de Talleres</p>
    </div>    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Eventos -->
    <script>
        const eventos = <?= json_encode(array_values(array_map(function($item) {
            $fechaHoraInicio = $item['fecha'] . 'T' . $item['hora'];
            $timestampInicio = strtotime($fechaHoraInicio);
            $timestampFin = $timestampInicio + 3600; // 1 hora después
            $fechaHoraFin = date('Y-m-d\TH:i', $timestampFin);
            $talleristas = empty($item['talleristas']) ? [] : $item['talleristas'];
            $cantidadAsignados = count($talleristas);
            return [
                'id' => $item['id'],
                'title' => $item['taller_nombre'] . " - " . $item['colegio_nombre'],
                'start' => $fechaHoraInicio,
                'end' => $fechaHoraFin,
                'tallerista' => empty($talleristas) ? 'Ninguno' : implode(', ', $talleristas),
                'cantidadAsignados' => $cantidadAsignados,
                'colegio_id' => $item['colegio_id'],
                'taller_id' => $item['taller_id']
            ];
        }, $agenda)), JSON_UNESCAPED_UNICODE) ?>;        
    </script>
    <!-- booleano para permitir o no event drop -->
    <script>
    const usuarioLogueado = <?= isset($_SESSION['usuario']) ? 'true' : 'false' ?>;
    </script>

    <!-- FullCalendar -->
    <script>
        let modalAbierto = null;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                navLinks: true,
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today<?= isset($_SESSION["usuario"]) ? " agregarTallerButton" : "" ?>',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                customButtons: {
                    <?php if (isset($_SESSION['usuario'])): ?>
                    agregarTallerButton: {
                        text: '➕ Agregar Taller',
                        click: function () {
                            const modal = new bootstrap.Modal(document.getElementById('modalAgregarTaller'));
                            modal.show();
                        }
                    }
                    <?php endif; ?>
                },
                events: eventos,
                eventClick: function(info) {
                    const evento = info.event;

                    // Mostrar detalles
                    document.getElementById('contenidoDetalleEvento').innerHTML = `
                        <div class="mb-2 text-start"><strong>Título:</strong> ${evento.title}</div>
                        <div class="mb-2 text-start"><strong>Inicio:</strong> ${evento.start.toLocaleString()}</div>
                        <div class="mb-2 text-start"><strong>Fin:</strong> ${evento.end ? evento.end.toLocaleString() : 'No especificado'}</div>
                        <div class="mb-2 text-start"><strong>Talleristas:</strong> ${evento.extendedProps.tallerista ?? 'Sin descripción'}</div>
                    `;
                    // Mostrar o esconder el formulario de asignar tallerista
                    const formAsignar = document.getElementById('formAsignarTallerista');
                    const mensajeYaDos = document.getElementById('mensajeYaDos');

                    // cantidadAsignados desde evento
                    const cantidadAsignados = evento.extendedProps.cantidadAsignados ?? 0;

                    // Solo si existen los elementos (cuando hay sesión)
                    if (formAsignar && mensajeYaDos) {
                        if (cantidadAsignados < 2) {
                            formAsignar.classList.remove('d-none');
                            mensajeYaDos.classList.add('d-none');
                        } else {
                            formAsignar.classList.add('d-none');
                            mensajeYaDos.classList.remove('d-none');
                        }
                    }

                    document.getElementById('eventoIdEditar').value = evento.id;
                    document.getElementById('eventoIdEliminar').value = evento.id;
                    document.getElementById('eventoIdDetalle').value = evento.id;
                    const modal = new bootstrap.Modal(document.getElementById('modalDetalleEvento'));
                    modal.show();

                    info.jsEvent.preventDefault();
                },
                eventDidMount: function(info) {
                    if (info.event.extendedProps.description) {
                        new bootstrap.Tooltip(info.el, {
                            title: info.event.extendedProps.description,
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body'
                        });
                    }
                },
                editable: true,
                eventDrop: function(info) {
                    if (!usuarioLogueado) {
                        alert('Debes estar logueado para modificar eventos.');
                        info.revert(); // Revierte cambio para que no se actualice la nueva fecha si no se esta logueado
                        return;
                    }
                    const evento = info.event;

                    // Datos nuevos
                    const nuevoDia = evento.startStr.substring(0, 10); // YYYY-MM-DD
                    const nuevaHora = evento.startStr.substring(11, 16); // HH:MM

                    // Enviá los datos al servidor para actualizar
                    fetch('includes/actualizar_evento.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: evento.id,
                            fecha: nuevoDia,
                            hora: nuevaHora
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.ok) {
                            alert('Error al actualizar evento: ' + data.error);
                            info.revert();
                        }
                    })
                    .catch(err => {
                        alert('Fallo en el servidor.');
                        info.revert();
                    });
                }
                
            });           

            calendar.render();
            const btnEditar = document.getElementById('btnEditarEvento');
            if (btnEditar) {
                btnEditar.addEventListener('click', function () {
                    const eventoId = document.getElementById('eventoIdEditar').value;
                    const evento = eventos.find(e => e.id == eventoId);
                    if (!evento) {
                        console.error("No se encontró evento con ID:", eventoId);
                        return;
                    }

                    // Cerrar el modal de detalle
                    const modalDetalle = bootstrap.Modal.getInstance(document.getElementById('modalDetalleEvento'));
                    if (modalDetalle) modalDetalle.hide();

                    
                    setTimeout(() => {
                        const modalEditar = new bootstrap.Modal(document.getElementById('modalAgregarTaller'));
                        document.querySelector('#modalAgregarTallerLabel').textContent = "Editar Taller";
                        document.querySelector('[name="colegio"]').value = evento.colegio_id ?? "";
                        document.querySelector('[name="taller_id"]').value = evento.taller_id ?? "";
                        document.querySelector('[name="fecha"]').value = evento.start.substring(0, 10);
                        document.querySelector('[name="hora"]').value = evento.start.substring(11, 16);

                        const btnSubmit = document.querySelector('#modalAgregarTaller button[type="submit"]');
                        btnSubmit.textContent = "Guardar Cambios";
                        btnSubmit.name = "guardar_edicion";

                        let inputId = document.querySelector('#modalAgregarTaller input[name="agenda_id"]');
                        if (!inputId) {
                            inputId = document.createElement('input');
                            inputId.type = 'hidden';
                            inputId.name = 'agenda_id';
                            document.querySelector('#modalAgregarTaller form').appendChild(inputId);
                        }
                        inputId.value = eventoId;

                        modalEditar.show();
                    }, 300);
                });
            }
            // Restaurar modal a Agregar
            document.getElementById('modalAgregarTaller').addEventListener('hidden.bs.modal', function () {
                document.querySelector('#modalAgregarTallerLabel').textContent = "Agregar Nuevo Taller";
                const btnSubmit = document.querySelector('#modalAgregarTaller button[type="submit"]');
                btnSubmit.textContent = "Agregar Taller";
                btnSubmit.name = "agregar";

                // limpia hidden input
                const inputId = document.querySelector('#modalAgregarTaller input[name="agenda_id"]');
                if (inputId) inputId.remove();
            });
        });
    </script>
    
    <?php if (isset($_SESSION['usuario'])): ?>
    <script>
        window.addEventListener('load', function () {
            const modalElement = document.getElementById('bienvenidaModal');
            if (modalElement && !sessionStorage.getItem('bienvenidaMostrada')) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                sessionStorage.setItem('bienvenidaMostrada', 'true');
            }
        });
    </script>
    <?php endif; ?>
</footer>
</body>
</html>
