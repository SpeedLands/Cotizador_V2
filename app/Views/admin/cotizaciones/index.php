<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Cotizaciones - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.css">

    <!-- CSRF Meta Tags -->
    <meta name="csrf_token_name" content="<?= csrf_token() ?>">
    <meta name="csrf_hash" content="<?= csrf_hash() ?>">
    <style>
                /* Estilos para normalizar los inputs de DataTables */
        .dt-search input,
        .dt-length select {
            border: 1px solid #d1d5db !important;      /* Borde gris claro */
            background-color: #ffffff !important;      /* Fondo blanco */
            color: #1f2937 !important;                 /* Texto oscuro */
            background-image: none !important;         /* Opcional: Elimina la flecha de selector que a veces se pone oscura */
        }
        .dt-paging a {
            background-color: #ffffff !important;
            border: 1px solid #e5e7eb !important; /* Usamos un borde un poco más suave por defecto */
            color: #374151 !important; /* Un gris oscuro para el texto */
        }
        
        /* Para el enlace <a> de la paginación ACTIVA y en hover */
        .dt-paging a.current, 
        .dt-paging a:hover {
            background-color: #4f46e5 !important; /* Color índigo */
            border-color: #4f46e5 !important;
            color: #ffffff !important;
        }

        /* Para los enlaces <a> de paginación DESHABILITADOS */
        .dt-paging a.disabled,
        .dt-paging a.disabled:hover {
            background-color: #f3f4f6 !important; /* Gris claro de fondo */
            border-color: #d1d5db !important;
            color: #9ca3af !important; /* Texto gris claro */
            cursor: default; /* Evita que el cursor cambie a "mano" */
        }
    </style>
</head>
<body class="bg-gray-100 pt-16">

    <?= view('components/appbar', [ 'isLoggedIn' => true ]) ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Listado de Cotizaciones</h1>
            <p class="text-gray-500">Gestiona todas las cotizaciones recibidas.</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <table id="quotations-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha Evento</th>
                        <th>Total Estimado</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- El contenido se cargará vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwindcss.js"></script>

    <script>
        $(document).ready(function() {
            // Obtener el token CSRF inicial de los meta tags
            let csrfTokenName = $('meta[name="csrf_token_name"]').attr('content');
            let csrfHash = $('meta[name="csrf_hash"]').attr('content');

            const table = new DataTable('#quotations-table', {
                // --- CONFIGURACIÓN SSP ---
                processing: true,
                serverSide: true,
                serverMethod: 'post',
                ajax: {
                    url: "<?= site_url(route_to('panel.cotizaciones.datatable')) ?>",
                    type: 'POST',
                    // Inyectar el token CSRF en cada solicitud
                    data: function(d) {
                        d[csrfTokenName] = csrfHash;
                    },
                    // Actualizar el token CSRF después de cada respuesta
                    dataSrc: function(json) {
                        csrfHash = json.token;
                        return json.data;
                    }
                },

                // --- COLUMNAS Y RENDERIZADO ---
                columns: [
                    { data: 'id_cotizacion' },
                    { data: 'cliente_nombre' },
                    { data: 'fecha_evento' },
                    { 
                        data: 'total_estimado',
                        render: function(data, type, row) {
                            return '$' + data;
                        }
                    },
                    { 
                        data: 'status',
                        render: function(data, type, row) {
                            const statusMap = {
                                'pendiente': { label: 'Pendiente', class: 'bg-yellow-500 text-yellow-900' },
                                'confirmado': { label: 'Confirmado', class: 'bg-green-500 text-white' },
                                'cancelado': { label: 'Cancelado', class: 'bg-red-500 text-white' },
                                'pagado': { label: 'Pagado', class: 'bg-blue-500 text-white' },
                                'contactado': { label: 'Contactado', class: 'bg-indigo-500 text-white' },
                                'en_revision': { label: 'En Revisión', class: 'bg-gray-500 text-white' }
                            };
                            const statusInfo = statusMap[data] || { label: data, class: 'bg-gray-400' };
                            return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusInfo.class}">${statusInfo.label}</span>`;
                        }
                    },
                    { data: 'created_at' },
                    { 
                        data: 'actions',
                        orderable: false, // No se puede ordenar por esta columna
                        searchable: false, // No se puede buscar en esta columna
                        render: function(data, type, row) {
                            const viewUrl = "<?= site_url('panel/cotizaciones/ver/') ?>" + data;
                            const editUrl = "<?= site_url('panel/cotizaciones/editar/') ?>" + data;
                            return `
                                <a href="${viewUrl}" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Ver Detalle"><i class="bi bi-eye-fill"></i></a>
                                <a href="${editUrl}" class="text-blue-600 hover:text-blue-900" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                            `;
                        }
                    }
                ],

                // --- USABILIDAD Y RENDIMIENTO ---
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json',
                },
                searchDelay: 400, // Anti-rebote para la búsqueda
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                responsive: true,
            });
        });
    </script>

</body>
</html>