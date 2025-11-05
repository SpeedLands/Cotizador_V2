<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?> - Mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .step-card { transition: all 0.3s ease-in-out; }
        .option-item { transition: all 0.2s ease-in-out; }
        .delete-btn { cursor: pointer; transition: color 0.2s ease; }
        .delete-btn:hover { color: #ef4444; }
    </style>
</head>
<body class="bg-gray-100 pt-16">

    <?= view('components/appbar', [ 'isLoggedIn' => true ]) ?>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= esc($titulo) ?></h1>
                <p class="text-gray-500">Modifica los detalles, pasos y opciones de este platillo.</p>
            </div>
            <div>
                <a href="<?= site_url(route_to('panel.servicios.index')) ?>" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                    Cancelar y Volver
                </a>
            </div>
        </div>

        <form id="interactive-service-form" action="<?= site_url(route_to('panel.servicios.actualizar-interactivo')) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="menu_structure" id="menu_structure">

            <!-- Sección de Categoría -->
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-6">1. Sub-Categoría del Platillo</h2>
                <select id="parent_id" class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <?php foreach ($sub_categories as $cat): ?>
                        <option value="<?= $cat['id_item'] ?>"><?= esc($cat['nombre_item']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sección de Detalles del Platillo -->
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-6">2. Detalles del Platillo</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" id="id_item">
                    <div class="md:col-span-2">
                        <label for="nombre_item" class="block text-sm font-medium text-gray-700">Nombre del Platillo <span class="text-red-500">*</span></label>
                        <input type="text" id="nombre_item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="md:col-span-2">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea id="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div>
                        <label for="tipo_comida" class="block text-sm font-medium text-gray-700">Tipo de Comida</label>
                        <select id="tipo_comida" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="ambos">Ambos</option>
                            <option value="desayuno">Desayuno</option>
                            <option value="comida">Comida</option>
                        </select>
                    </div>
                    <div>
                        <label for="precio_unitario" class="block text-sm font-medium text-gray-700">Precio Base</label>
                        <input type="number" step="0.01" id="precio_unitario" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="flex items-center mt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="per_person" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="ml-2">El precio es por persona</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Sección de Pasos de Personalización -->
            <div class="bg-white p-8 rounded-xl shadow-lg">
                 <div class="flex justify-between items-center border-b pb-4 mb-6">
                    <h2 class="text-xl font-bold text-gray-800">3. Pasos de Personalización</h2>
                    <button type="button" id="add-step-btn" class="bg-indigo-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-600 transition">
                        <i class="bi bi-plus-circle-fill mr-2"></i>Añadir Paso
                    </button>
                </div>
                <div id="steps-container" class="space-y-6"></div>
            </div>

            <div class="mt-8 text-right">
                <button type="submit" class="bg-green-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-green-700 transition text-lg">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <!-- Templates -->
    <template id="step-template">
        <div class="step-card bg-gray-50 border border-gray-200 p-6 rounded-lg">
            <div class="flex justify-between items-center mb-4">
                <div class="flex-grow">
                    <label class="block text-sm font-medium text-gray-700">Nombre del Paso</label>
                    <input type="text" class="step-name mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                </div>
                <div class="ml-6">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Selección</label>
                    <select class="step-ui-type mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="radio">Única (Radio Button)</option>
                        <option value="checkbox">Múltiple (Checkbox)</option>
                        <option value="quantity">Cantidad (Quantity)</option>
                    </select>
                </div>
                <div class="ml-6 pt-6">
                     <i class="bi bi-trash3-fill text-xl text-gray-500 delete-btn delete-step-btn" title="Eliminar este paso"></i>
                </div>
            </div>
            <div class="options-container space-y-3 pl-4 border-l-2 border-gray-200"></div>
            <div class="mt-4 pl-4">
                <button type="button" class="add-option-btn bg-sky-500 text-white font-semibold py-1 px-3 rounded-md text-sm hover:bg-sky-600 transition">
                    <i class="bi bi-plus"></i> Añadir Opción
                </button>
            </div>
        </div>
    </template>
    <template id="option-template">
        <div class="option-item flex items-center gap-4">
            <div class="flex-grow">
                <label class="block text-xs font-medium text-gray-600">Nombre Opción</label>
                <input type="text" class="option-name mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" required>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-600">Precio Adicional</label>
                <input type="number" step="0.01" value="0.00" class="option-price mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div class="pt-5">
                <i class="bi bi-x-circle-fill text-lg text-gray-400 delete-btn delete-option-btn" title="Eliminar opción"></i>
            </div>
        </div>
    </template>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemData = JSON.parse('<?= $itemJSON ?>');

        const form = document.getElementById('interactive-service-form');
        const hiddenInput = document.getElementById('menu_structure');
        const stepsContainer = document.getElementById('steps-container');
        const addStepBtn = document.getElementById('add-step-btn');
        const stepTemplate = document.getElementById('step-template');
        const optionTemplate = document.getElementById('option-template');

        // --- HYDRATION LOGIC ---
        function hydrateForm() {
            // 1. Hydrate main item details
            document.getElementById('id_item').value = itemData.id_item;
            document.getElementById('nombre_item').value = itemData.nombre_item;
            document.getElementById('descripcion').value = itemData.descripcion;
            document.getElementById('precio_unitario').value = itemData.precio_unitario;
            document.getElementById('per_person').checked = (itemData.per_person == 1);
            document.getElementById('tipo_comida').value = itemData.tipo_comida;
            document.getElementById('parent_id').value = itemData.parent_id;

            // 2. Hydrate steps and options
            if (itemData.steps && itemData.steps.length > 0) {
                itemData.steps.forEach(step => {
                    const stepEl = addStep(step.nombre_item, step.tipo_ui);
                    if (step.options && step.options.length > 0) {
                        step.options.forEach(option => {
                            addOption(stepEl, option.nombre_item, option.precio_unitario);
                        });
                    }
                });
            }
        }

        function addStep(name = '', uiType = 'radio') {
            const stepClone = stepTemplate.content.cloneNode(true);
            const stepCard = stepClone.querySelector('.step-card');
            stepCard.querySelector('.step-name').value = name;
            stepCard.querySelector('.step-ui-type').value = uiType;
            stepsContainer.appendChild(stepClone);
            return stepCard;
        }

        function addOption(stepEl, name = '', price = '0.00') {
            const optionsContainer = stepEl.querySelector('.options-container');
            const optionClone = optionTemplate.content.cloneNode(true);
            optionClone.querySelector('.option-name').value = name;
            optionClone.querySelector('.option-price').value = price;
            optionsContainer.appendChild(optionClone);
        }

        // --- DYNAMIC ADD/DELETE LOGIC ---
        addStepBtn.addEventListener('click', () => addStep());
        stepsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-option-btn')) {
                addOption(e.target.closest('.step-card'));
            }
            if (e.target.classList.contains('delete-option-btn')) {
                e.target.closest('.option-item').remove();
            }
            if (e.target.classList.contains('delete-step-btn')) {
                e.target.closest('.step-card').remove();
            }
        });

        // --- FORM SUBMISSION LOGIC ---
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const serviceData = {
                main_item: {},
                steps: []
            };

            // 1. Recopilar datos del platillo principal
            serviceData.main_item = {
                id_item: document.getElementById('id_item').value,
                nombre_item: document.getElementById('nombre_item').value,
                descripcion: document.getElementById('descripcion').value,
                parent_id: document.getElementById('parent_id').value,
                precio_unitario: document.getElementById('precio_unitario').value,
                per_person: document.getElementById('per_person').checked,
                tipo_comida: document.getElementById('tipo_comida').value,
            };

            // 2. Recopilar datos de los pasos y sus opciones
            stepsContainer.querySelectorAll('.step-card').forEach(stepCard => {
                const stepData = {
                    nombre_paso: stepCard.querySelector('.step-name').value,
                    tipo_ui: stepCard.querySelector('.step-ui-type').value,
                    options: []
                };
                stepCard.querySelectorAll('.option-item').forEach(optionItem => {
                    stepData.options.push({
                        nombre_opcion: optionItem.querySelector('.option-name').value,
                        precio_opcion: optionItem.querySelector('.option-price').value
                    });
                });
                serviceData.steps.push(stepData);
            });

            // 3. Validar que los pasos no estén vacíos
            for (const [index, step] of serviceData.steps.entries()) {
                if (step.options.length === 0) {
                    alert(`El Paso #${index + 1} ("${step.nombre_paso}") no tiene opciones.`);
                    return;
                }
            }

            hiddenInput.value = JSON.stringify(serviceData);
            form.submit();
        });

        // Initial call to populate the form
        hydrateForm();
    });
    </script>
</body>
</html>