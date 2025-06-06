$(document).ready(function() {
    function initializeDataTable(table) {
        try {
            if ($(table).length) {
                $(table).DataTable({
                    responsive: true,
                    autoWidth: false,
                    language: {
                        processing:     "Procesando...",
                        search:         "Buscar:",
                        lengthMenu:    "Mostrar _MENU_ registros",
                        info:           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        infoEmpty:      "Mostrando registros del 0 al 0 de un total de 0 registros",
                        infoFiltered:   "(filtrado de un total de _MAX_ registros)",
                        infoPostFix:    "",
                        loadingRecords: "Cargando...",
                        zeroRecords:    "No se encontraron registros coincidentes",
                        emptyTable:     "No hay datos disponibles en la tabla",
                        paginate: {
                            first:      "Primero",
                            previous:   "Anterior",
                            next:       "Siguiente",
                            last:       "Último"
                        },
                        aria: {
                            sortAscending:  ": activar para ordenar la columna de manera ascendente",
                            sortDescending: ": activar para ordenar la columna de manera descendente"
                        }
                    },
                    dom: '<"top"f>rt<"bottom"ip><"clear">',
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                    initComplete: function() {
                        $('.dataTables_filter input').attr('placeholder', 'Buscar...');
                    }
                });
            }
        } catch (error) {
            console.error('Error al inicializar la tabla:', error);
        }
    }

    $('.datatable:not(.dataTable)').each(function() {
        initializeDataTable(this);
    });
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top'
    });
});
