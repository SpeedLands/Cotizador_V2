<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?> - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-100 pt-16">

    <?= view('components/appbar', [ 'isLoggedIn' => true ]) ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= esc($titulo) ?></h1>
                <p class="text-gray-500">Completa los detalles del nuevo ítem del menú.</p>
            </div>
            <div>
                <a href="<?= site_url(route_to('panel.servicios.index')) ?>" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                    Cancelar y Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna del Formulario -->
            <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-lg">
                <?php if (session()->has('errors')): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Error de Validación</p>
                        <ul>
                            <?php foreach (session('errors') as $error): ?>
                                <li>- <?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url(route_to('panel.servicios.guardar')) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre del Ítem -->
                        <div class="md:col-span-2">
                            <label for="nombre_item" class="block text-sm font-medium text-gray-700">Nombre del Ítem <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_item" id="nombre_item" value="<?= old('nombre_item') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?= old('descripcion') ?></textarea>
                        </div>

                        <!-- Categoría Padre -->
                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-gray-700">Categoría Padre</label>
                            <select name="parent_id" id="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Ninguna (Categoría Raíz) --</option>
                                <?php foreach ($parent_items as $item): ?>
                                    <option value="<?= $item['id_item'] ?>" data-parent-text="<?= esc($item['nombre_item']) ?>" <?= old('parent_id') == $item['id_item'] ? 'selected' : '' ?>>
                                        <?= esc($item['nombre_item']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tipo de UI -->
                        <div>
                            <label for="tipo_ui" class="block text-sm font-medium text-gray-700">Tipo de UI <span class="text-red-500">*</span></label>
                            <select name="tipo_ui" id="tipo_ui" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="checkbox" <?= old('tipo_ui') == 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
                                <option value="radio" <?= old('tipo_ui') == 'radio' ? 'selected' : '' ?>>Radio Button</option>
                                <option value="quantity" <?= old('tipo_ui') == 'quantity' ? 'selected' : '' ?>>Campo de Cantidad</option>
                            </select>
                        </div>

                        <!-- Precio Unitario -->
                        <div>
                            <label for="precio_unitario" class="block text-sm font-medium text-gray-700">Precio Unitario</label>
                            <input type="number" step="0.01" name="precio_unitario" id="precio_unitario" value="<?= old('precio_unitario', '0.00') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Activo -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="activo" value="1" class="text-indigo-600" <?= old('activo', '1') == '1' ? 'checked' : '' ?>>
                                    <span class="ml-2">Activo</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="activo" value="0" class="text-indigo-600" <?= old('activo') == '0' ? 'checked' : '' ?>>
                                    <span class="ml-2">Inactivo</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 text-right">
                        <button type="submit" class="bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700 transition">
                            Guardar Servicio
                        </button>
                    </div>
                </form>
            </div>

            <!-- Columna de Previsualización -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Previsualización de Jerarquía</h3>
                    <div id="preview-container" class="bg-gray-50 p-4 rounded-lg min-h-[100px] text-sm">
                        <!-- El contenido se generará con JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const parentSelect = document.getElementById('parent_id');
            const uiTypeSelect = document.getElementById('tipo_ui');
            const nameInput = document.getElementById('nombre_item');
            const previewContainer = document.getElementById('preview-container');
            const allUiOptions = Array.from(uiTypeSelect.options);

            // Mapeo de la jerarquía para la previsualización
            const parentHierarchy = <?= json_encode(array_column($parent_items, 'parent_id', 'id_item')) ?>;
            const parentNames = <?= json_encode(array_column($parent_items, 'nombre_item', 'id_item')) ?>;

            function updatePreview() {
                const parentId = parentSelect.value;
                const itemName = nameInput.value || '[Nuevo Servicio]';
                let html = '';

                if (!parentId) { // Nivel 1
                    html = `<div class="font-bold">1. ${itemName}</div>`;
                } else {
                    const grandParentId = parentHierarchy[parentId];
                    if (grandParentId === null) { // Nivel 2
                        html = `<div class="font-bold">${parentNames[parentId]}</div>`;
                        html += `<div class="ml-4 text-gray-700"><span class="text-gray-400">↳</span> 2. ${itemName}</div>`;
                    } else { // Nivel 3
                        html = `<div class="font-bold">${parentNames[grandParentId]}</div>`;
                        html += `<div class="ml-4 text-gray-700"><span class="text-gray-400">↳</span> ${parentNames[parentId]}</div>`;
                        html += `<div class="ml-8 text-gray-500"><span class="text-gray-300">↳</span> 3. ${itemName}</div>`;
                    }
                }
                previewContainer.innerHTML = html;
            }

            function toggleUiOptions() {
                const isRootLevel = parentSelect.value === '';
                uiTypeSelect.innerHTML = '';
                allUiOptions.forEach(option => {
                    if (isRootLevel && (option.value === 'radio' || option.value === 'quantity')) {
                        // No añadir
                    } else {
                        uiTypeSelect.add(option.cloneNode(true));
                    }
                });
            }

            parentSelect.addEventListener('change', () => {
                toggleUiOptions();
                updatePreview();
            });
            nameInput.addEventListener('input', updatePreview);

            // Inicializar al cargar la página
            toggleUiOptions();
            updatePreview();
        });
    </script>
</body>
</html>