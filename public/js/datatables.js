$(document).ready(function() {
    $('.datatable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: '/js/datatables-spanish.json'
        },
        columnDefs: [
            {
                targets: 0,
                className: 'control',
                orderable: false,
                width: '30px'
            },
            {
                targets: -1,
                className: 'text-center actions-column',
                orderable: false,
                responsivePriority: 100
            }
        ],
        responsive: {
            details: {
                type: 'column'
            }
        }
    });

    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top'
    });

    // Manejar clicks en botones dentro de detalles expandidos
    $('.datatable').on('click', '.btn', function(e) {
        e.stopPropagation();
    });
});