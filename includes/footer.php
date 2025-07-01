<footer class="bg-dark text-white text-center py-3 mt-auto">
    <div class="container">
        <p class="mb-0">© <?= date("Y"); ?> Portal de Talleres</p>
    </div>

    <!-- Modales Bootstrap -->

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
                    <form method="post" class="mt-3 d-flex gap-2 align-items-end">
                        <input type="hidden" id="eventoIdDetalle" name="taller_id" value="">
                        <select name="nuevo_tallerista" class="form-select form-select-sm w-auto" required>
                            <option value="" selected disabled>Seleccionar tallerista</option>
                            <?php foreach ($talleristas_disponibles as $u): ?>
                                <option value="<?= htmlspecialchars($u['usuario']) ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="asignar_tallerista" class="btn btn-info btn-sm">Asignar</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <form method="post" class="d-inline">
                        <input type="hidden" id="eventoIdEditar" name="editar" value="">
                        <button type="submit" class="btn btn-warning">Editar</button>
                    </form>
                    <form method="post" class="d-inline">
                        <input type="hidden" id="eventoIdEliminar" name="eliminar" value="">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este taller?')">Eliminar</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Eventos desde PHP -->
    <script>
        const eventos = <?= json_encode(array_values(array_map(function($item) {
            $fechaHoraInicio = $item['fecha'] . 'T' . $item['hora'];
            $timestampInicio = strtotime($fechaHoraInicio);
            $timestampFin = $timestampInicio + 3600; // 1 hora después
            $fechaHoraFin = date('Y-m-d\TH:i', $timestampFin);
            return [
                'id' => $item['id'],
                'title' => $item['taller_nombre'] . " - " . $item['colegio_nombre'],
                'start' => $fechaHoraInicio,
                'end' => $fechaHoraFin,
                'tallerista' => (empty($item['talleristas']) ? 'Ninguno' : implode(', ', $item['talleristas']))
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

                    if (modalAbierto) modalAbierto.hide();

                    document.getElementById('contenidoDetalleEvento').innerHTML = `
                        <div class="mb-2 text-start"><strong>Título:</strong> ${evento.title}</div>
                        <div class="mb-2 text-start"><strong>Inicio:</strong> ${evento.start.toLocaleString()}</div>
                        <div class="mb-2 text-start"><strong>Fin:</strong> ${evento.end ? evento.end.toLocaleString() : 'No especificado'}</div>
                        <div class="mb-2 text-start"><strong>Talleristas:</strong> ${evento.extendedProps.tallerista ?? 'Sin descripción'}</div>
                    `;

                    // Pasamos el ID del evento al formulario
                    document.getElementById('eventoIdDetalle').value = evento.id;
                    document.getElementById('eventoIdEditar').value = evento.id;
                    document.getElementById('eventoIdEliminar').value = evento.id;

                    const modal = new bootstrap.Modal(document.getElementById('modalDetalleEvento'));
                    modal.show();
                    modalAbierto = modal;

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
