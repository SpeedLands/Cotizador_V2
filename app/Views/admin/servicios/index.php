<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.css">
    <meta name="csrf_token_name" content="<?= csrf_token() ?>">
    <meta name="csrf_hash" content="<?= csrf_hash() ?>">
</head>
<body class="bg-gray-100 pt-16">

    <?= view('components/appbar', [ 'isLoggedIn' => true ]) ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestión de Servicios</h1>
                <p class="text-gray-500">Administra los ítems del menú dinámico.</p>
            </div>
            <div>
                <a href="<?= site_url(route_to('panel.servicios.crear')) ?>" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-circle-fill mr-2"></i>Añadir Servicio
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <table id="services-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Ítem</th>
                        <th>Categoría Padre</th>
                        <th>Tipo de UI</th>
                        <th>Precio</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md transform transition-all">
            <div class="flex items-start">
                <div class="flex-shrink-0 h-12 w-12 flex items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 text-xl"></i>
                </div>
                <div class="ml-4 text-left">
                    <h3 class="text-lg font-bold text-gray-900">Confirmar Eliminación</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            ¿Estás seguro de que deseas eliminar el servicio <strong id="service-name-to-delete" class="font-medium text-gray-800"></strong>?
                        </p>
                        <p class="text-xs text-red-600 mt-2">Esta acción no se puede deshacer.</p>
                    </div>
                </div>
            </div>
            <form id="delete-form" action="<?= site_url(route_to('panel.servicios.eliminar')) ?>" method="post" class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <?= csrf_field() ?>
                <input type="hidden" name="id_item" id="id_item_to_delete">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Eliminar</button>
                <button type="button" id="cancel-delete-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwindcss.js"></script>

    <script>
        $(document).ready(function() {
            let csrfTokenName = $('meta[name="csrf_token_name"]').attr('content');
            let csrfHash = $('meta[name="csrf_hash"]').attr('content');

            const table = new DataTable('#services-table', {
                processing: true,
                serverSide: true,
                serverMethod: 'post',
                ajax: {
                    url: "<?= site_url(route_to('panel.servicios.datatable')) ?>",
                    type: 'POST',
                    data: function(d) {
                        d[csrfTokenName] = csrfHash;
                    },
                    dataSrc: function(json) {
                        csrfHash = json.token;
                        return json.data;
                    }
                },
                columns: [
                    { data: 'id_item' },
                    { 
                        data: 'nombre_item',
                        render: function(data, type, row) {
                            // Añadir indentación si es un sub-ítem
                            if (row.parent_name !== 'Categoría Raíz') {
                                return `<span class="ml-4"><i class="bi bi-arrow-return-right text-gray-400 mr-2"></i>${data}</span>`;
                            }
                            return `<strong>${data}</strong>`;
                        }
                    },
                    { data: 'parent_name' },
                    { 
                        data: 'tipo_ui',
                        render: function(data, type, row) {
                            const typeMap = {
                                'nav_group': { label: 'Categoría', class: 'bg-gray-200 text-gray-800' },
                                'checkbox': { label: 'Checkbox', class: 'bg-blue-200 text-blue-800' },
                                'radio': { label: 'Radio', class: 'bg-purple-200 text-purple-800' },
                                'quantity': { label: 'Cantidad', class: 'bg-green-200 text-green-800' }
                            };
                            const typeInfo = typeMap[data] || { label: data, class: 'bg-gray-400' };
                            return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${typeInfo.class}">${typeInfo.label}</span>`;
                        }
                    },
                    { 
                        data: 'precio_unitario',
                        render: function(data, type, row) {
                            return '$' + data;
                        }
                    },
                    { 
                        data: 'activo',
                        render: function(data, type, row) {
                            if (data == 1) {
                                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sí</span>';
                            }
                            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>';
                        }
                    },
                    { 
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            const editUrl = "<?= site_url('panel/servicios/editar/') ?>" + data;
                            const deleteData = `data-id="${data}" data-name="${row.nombre_item}"`;
                            return `
                                <a href="${editUrl}" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                <button type="button" class="delete-btn text-red-600 hover:text-red-900" title="Eliminar" ${deleteData}><i class="bi bi-trash-fill"></i></button>
                            `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json',
                },
                searchDelay: 400,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                responsive: true,
                // Orden por defecto para mantener la jerarquía visual
                order: [[2, 'asc'], [1, 'asc']]
            });

            // --- Lógica del Modal de Eliminación ---
            const deleteModal = $('#delete-modal');
            const deleteForm = $('#delete-form');
            const serviceNameToDelete = $('#service-name-to-delete');
            const idItemToDelete = $('#id_item_to_delete');

            $('#services-table tbody').on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                serviceNameToDelete.text(name);
                idItemToDelete.val(id);
                deleteModal.removeClass('hidden');
            });

            $('#cancel-delete-btn').on('click', function() {
                deleteModal.addClass('hidden');
            });
        });
    </script>

</body>
</html>