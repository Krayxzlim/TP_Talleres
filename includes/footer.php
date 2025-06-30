<footer class="bg-dark text-white text-center py-3 mt-auto">
    <div class="container">
        <p class="mb-0">© <?= date("Y"); ?> Portal de Talleres</p>
    </div>

    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const eventos = <?= json_encode(array_map(function($item) {
            $fechaHoraInicio = $item['fecha'] . 'T' . $item['hora'];
            $timestampInicio = strtotime($fechaHoraInicio);
            $timestampFin = $timestampInicio + 3600; // 1 hora después
            $fechaHoraFin = date('Y-m-d\TH:i', $timestampFin);

            return [
                'title' => $item['taller'] . " - " . $item['colegio'],
                'start' => $fechaHoraInicio,
                'end' => $fechaHoraFin,
                'tallerista' => (empty($item['talleristas']) ? 'Ninguno' : implode(', ', $item['talleristas']))
            ];
        }, $agenda)) ?>;
    </script>

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
                    // Abrimos el modal Bootstrap
                    const modal = new bootstrap.Modal(document.getElementById('modalAgregarTaller'));
                    modal.show();
                }
                }
            },
            events: eventos,
            
            eventClick: function(info) {
                const evento = info.event;
                if (modalAbierto) {
                    modalAbierto.hide();
                }
                const contenido = `
                    <div class="mb-2">
                        <strong>Título:</strong> ${evento.title}
                    </div>
                    <div class="mb-2">
                        <strong>Inicio:</strong> ${evento.start.toLocaleString()}
                    </div>
                    <div class="mb-2">
                        <strong>Fin:</strong> ${evento.end ? evento.end.toLocaleString() : 'No especificado'}
                    </div>
                    <div class="mb-2">
                        <strong>Talleristas:</strong> ${evento.extendedProps.tallerista ?? 'Sin descripción'}
                    </div>
                `;
                
                document.getElementById('contenidoDetalleEvento').innerHTML = contenido;

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
