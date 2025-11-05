<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?> - Mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .step-card {
            transition: all 0.3s ease-in-out;
        }
        .option-item {
            transition: all 0.2s ease-in-out;
        }
        .delete-btn {
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .delete-btn:hover {
            color: #ef4444; /* red-500 */
        }
    </style>
</head>
<body class="bg-gray-100 pt-16">

    <?= view('components/appbar', [ 'isLoggedIn' => true ]) ?>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= esc($titulo) ?></h1>
                <p class="text-gray-500">Añade un nuevo platillo y sus personalizaciones a una sub-categoría.</p>
            </div>
            <div>
                <a href="<?= site_url(route_to('panel.servicios.index')) ?>" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                    Cancelar y Volver
                </a>
            </div>
        </div>

        <form id="interactive-service-form" action="<?= site_url(route_to('panel.servicios.guardar-interactivo')) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="menu_structure" id="menu_structure">
            <input type="hidden" id="root_category_id" value="<?= esc($root_category_id) ?>">

            <!-- Sección de Categoría -->
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-6">1. Elige la Sub-Categoría</h2>
                <div class="space-y-4">
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="category_choice" value="existing" class="text-indigo-600" checked>
                            <span class="ml-2">Seleccionar sub-categoría existente</span>
                        </label>
                    </div>
                    <div id="existing-category-wrapper">
                        <select id="existing_category_id" class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <?php foreach ($sub_categories as $cat): ?>
                                <option value="<?= $cat['id_item'] ?>"><?= esc($cat['nombre_item']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="category_choice" value="new" class="text-indigo-600">
                            <span class="ml-2">Crear nueva sub-categoría</span>
                        </label>
                    </div>
                    <div id="new-category-wrapper" class="hidden">
                        <input type="text" id="new_category_name" placeholder="Ej: Ensaladas" class="mt-1 block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                    </div>
                </div>
            </div>

            <!-- Sección de Detalles del Platillo -->
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-6">2. Detalles del Platillo</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <option value="ambos" selected>Ambos</option>
                            <option value="desayuno">Desayuno</option>
                            <option value="comida">Comida</option>
                        </select>
                    </div>
                    <div>
                        <label for="precio_unitario" class="block text-sm font-medium text-gray-700">Precio Base del Platillo</label>
                        <input type="number" step="0.01" id="precio_unitario" value="0.00" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="flex items-center mt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="per_person" class="rounded border-gray-300 text-indigo-600 shadow-sm" checked>
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
                    Guardar Platillo Completo
                </button>
            </div>
        </form>
    </div>

    <!-- Templates -->
    <template id="step-template">
        <div class="step-card bg-gray-50 border border-gray-200 p-6 rounded-lg">
            <div class="flex justify-between items-center mb-4">
                <div class="flex-grow">
                    <label class="block text-sm font-medium text-gray-700">Nombre del Paso (ej. "Elige tu salsa")</label>
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
                <label class="block text-xs font-medium text-gray-600">Nombre de la Opción</label>
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
        // Form elements
        const form = document.getElementById('interactive-service-form');
        const hiddenInput = document.getElementById('menu_structure');

        // Category selection elements
        const categoryChoiceRadios = document.querySelectorAll('input[name="category_choice"]');
        const existingCategoryWrapper = document.getElementById('existing-category-wrapper');
        const newCategoryWrapper = document.getElementById('new-category-wrapper');
        const existingCategorySelect = document.getElementById('existing_category_id');
        const newCategoryInput = document.getElementById('new_category_name');

        // Dynamic steps and options elements
        const addStepBtn = document.getElementById('add-step-btn');
        const stepsContainer = document.getElementById('steps-container');
        const stepTemplate = document.getElementById('step-template');
        const optionTemplate = document.getElementById('option-template');

        // --- CATEGORY SELECTION LOGIC ---
        categoryChoiceRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'existing') {
                    existingCategoryWrapper.classList.remove('hidden');
                    newCategoryWrapper.classList.add('hidden');
                    existingCategorySelect.disabled = false;
                    newCategoryInput.disabled = true;
                } else {
                    existingCategoryWrapper.classList.add('hidden');
                    newCategoryWrapper.classList.remove('hidden');
                    existingCategorySelect.disabled = true;
                    newCategoryInput.disabled = false;
                }
            });
        });

        // --- DYNAMIC STEPS AND OPTIONS LOGIC ---
        addStepBtn.addEventListener('click', () => {
            stepsContainer.appendChild(stepTemplate.content.cloneNode(true));
        });

        stepsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-option-btn')) {
                const optionsContainer = e.target.closest('.step-card').querySelector('.options-container');
                optionsContainer.appendChild(optionTemplate.content.cloneNode(true));
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
                category: {},
                main_item: {},
                steps: []
            };

            // 1. Recopilar datos de la categoría
            const categoryChoice = document.querySelector('input[name="category_choice"]:checked').value;
            serviceData.category.root_category_id = document.getElementById('root_category_id').value;

            if (categoryChoice === 'existing') {
                serviceData.category.existing_category_id = existingCategorySelect.value;
            } else {
                const newCatName = newCategoryInput.value.trim();
                if (newCatName === '') {
                    alert('Por favor, introduce un nombre para la nueva sub-categoría.');
                    return;
                }
                serviceData.category.new_category_name = newCatName;
            }

            // 2. Recopilar datos del platillo principal
            serviceData.main_item = {
                nombre_item: document.getElementById('nombre_item').value,
                descripcion: document.getElementById('descripcion').value,
                precio_unitario: document.getElementById('precio_unitario').value,
                per_person: document.getElementById('per_person').checked,
                tipo_comida: document.getElementById('tipo_comida').value,
            };

            // 3. Recopilar datos de los pasos y sus opciones
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

            // 4. Validar que los pasos no estén vacíos
            for (const [index, step] of serviceData.steps.entries()) {
                if (step.options.length === 0) {
                    alert(`El Paso #${index + 1} ("${step.nombre_paso}") no tiene opciones. Por favor, añade opciones o elimina el paso.`);
                    return;
                }
            }

            hiddenInput.value = JSON.stringify(serviceData);
            form.submit();
        });
    });
    </script>
</body>
</html>