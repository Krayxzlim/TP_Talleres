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
                'cantidadAsignados' => $cantidadAsignados
            ];
        }, $agenda)), JSON_UNESCAPED_UNICODE) ?>;
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
                    left: 'prev,next today agregarTallerButton',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                customButtons: {
                    agregarTallerButton: {
                        text: '➕ Agregar Taller',
                        click: function () {
                            const modal = new bootstrap.Modal(document.getElementById('modalAgregarTaller'));
                            modal.show();
                        }
                    }
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

                    // Pasar el ID del evento a los inputs
                    document.getElementById('eventoIdDetalle').value = evento.id;
                    document.getElementById('eventoIdEditar').value = evento.id;
                    document.getElementById('eventoIdEliminar').value = evento.id;

                    // Mostrar o esconder el formulario de asignar tallerista
                    const formAsignar = document.getElementById('formAsignarTallerista');
                    const mensajeYaDos = document.getElementById('mensajeYaDos');

                    // cantidadAsignados desde evento
                    const cantidadAsignados = evento.extendedProps.cantidadAsignados ?? 0;

                    if (cantidadAsignados < 2) {
                        formAsignar.classList.remove('d-none');
                        mensajeYaDos.classList.add('d-none');
                    } else {
                        formAsignar.classList.add('d-none');
                        mensajeYaDos.classList.remove('d-none');
                    }


                    // Mostrar modal
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
                }
            });

            calendar.render();
        });
    </script>
</footer>
</body>
</html>
