<?php 
// app/Views/quotation/form.php
// ... (Todo el código HTML y campos de formulario) ...
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización de Catering - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.11.2/build/css/intlTelInput.css">
    <style>
        /* Fuerza al contenedor de la librería a ocupar el 100% del espacio disponible */
        .iti-container {
            width: 100%;
        }
        /* Asegura que el input interno y el contenedor de la bandera se ajusten */
        .iti {
            width: 100%;
        }
        /* Ajusta el padding para que el texto no se superponga con la bandera */
        .iti__flag-container {
            padding-right: 10px; /* Espacio extra para la bandera */
        }
        /* Ajusta el input para que el texto comience después de la bandera */
        #whatsapp {
            padding-left: 52px !important; /* Ajusta este valor si es necesario */
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.11.2/build/js/intlTelInput.min.js"></script>
</head>
<body class="bg-slate-50">
    <?= view('components/appbar', [ 'showNavLinks' => false]) ?>
    <main class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto max-w-7xl">
            <div class="text-center mb-12 pt-16">
                <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 tracking-tight">Cotiza tu Evento al Instante</h1>
                <p class="mt-4 text-xl text-gray-600">Recibe un estimado inmediato y una cotización formal en menos de 24 horas.</p>
            </div>
        </div>

        <div class="container mx-auto max-w-7xl lg:grid lg:grid-cols-3 lg:gap-12">
            <div class="lg:col-span-2">
                <div class="bg-white p-8 md:p-12 rounded-2xl shadow-xl">
                    <form id="quotation-form" action="<?= url_to('QuotationController::submitQuote') ?>" method="post">
                        <?= csrf_field() ?>

                        <!-- 1. Información de Contacto -->
                        <fieldset class="mb-10">
                            <legend class="text-2xl font-bold text-gray-800 border-b pb-4 mb-6 w-full">1. Información de Contacto</legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="nombre_completo" class="block text-base font-semibold text-gray-800 mb-2">Nombre Completo<span class="text-red-500">*</span></label>
                                    <input type="text" name="cliente_nombre" id="nombre_completo" value="<?= old('cliente_nombre') ?>" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                                <div class="iti-container w-full">
                                    <label for="whatsapp" class="block text-base font-semibold text-gray-800 mb-2">WhatsApp<span class="text-red-500">*</span></label>
                                    <input type="tel" name="cliente_whatsapp" id="whatsapp" value="<?= old('cliente_whatsapp') ?>" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                            </div>
                        </fieldset>

                        <!-- 2. Detalles del Evento -->
                        <fieldset class="mb-10">
                            <legend class="text-2xl font-bold text-gray-800 border-b pb-4 mb-6 w-full">2. Detalles del Evento</legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="tipo_evento" class="block text-base font-semibold text-gray-800 mb-2">Tipo de Evento<span class="text-red-500">*</span></label>
                                    <select id="tipo_evento" name="tipo_evento" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="social">Evento Social</option>
                                        <option value="empresarial">Evento Empresarial</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div id="campo_empresa" style="display: none;">
                                    <label for="nombre_empresa" class="block text-base font-semibold text-gray-800 mb-2">Nombre de la empresa</label>
                                    <input type="text" name="nombre_empresa" id="nombre_empresa" value="<?= old('nombre_empresa') ?>" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="cantidad_invitados" class="block text-base font-semibold text-gray-800 mb-2">Cantidad de Invitados<span class="text-red-500">*</span></label>
                                    <input type="number" name="num_invitados" id="cantidad_invitados" min="10" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" value="<?= old('num_invitados', 30) ?>" required>
                                </div>
                                <div>
                                    <label for="fecha_evento" class="block text-base font-semibold text-gray-800 mb-2">Fecha del Evento<span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_evento" id="fecha_evento" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                                <div>
                                    <label for="hora_inicio" class="block text-base font-semibold text-gray-800 mb-2">Hora de Inicio<span class="text-red-500">*</span></label>
                                    <input type="time" name="hora_inicio" id="hora_inicio" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                                <div>
                                    <label for="hora_consumo" class="block text-base font-semibold text-gray-800 mb-2">Hora de Consumo<span class="text-red-500">*</span></label>
                                    <input type="time" name="hora_consumo" id="hora_consumo" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                                <div>
                                    <label for="hora_finalizacion" class="block text-base font-semibold text-gray-800 mb-2">Hora de Finalización<span class="text-red-500">*</span></label>
                                    <input type="time" name="hora_finalizacion" id="hora_finalizacion" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="direccion_evento" class="block text-base font-semibold text-gray-800 mb-2">Dirección del Evento<span class="text-red-500">*</span></label>
                                    <textarea id="direccion_evento" name="direccion_evento" rows="3" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Calle, número, colonia y referencias." required><?= old('direccion_evento') ?></textarea>
                                </div>
                            </div>
                        </fieldset>

                        <!-- 3. Selección de Servicios (Ahora con Modales) -->
                        <fieldset class="mb-10">
                            <legend class="text-2xl font-bold text-gray-800 border-b pb-4 mb-6 w-full">3. Selección de Servicios</legend>
                            <!-- Contenedor para ítems raíz (Renderizado por View Cell) -->
                            <div id="menu-options-root" class="space-y-4 mb-6">
                                <?= view_cell('\App\Libraries\MenuCell::renderRootItems') ?>
                            </div>
                            <!-- Contenedor para ítems seleccionados (para mantener el estado) -->
                            <div id="selected-items-container" class="hidden">
                                <!-- Aquí se inyectarán los inputs ocultos de los ítems seleccionados en la modal -->
                            </div>
                        </fieldset>

                        <!-- 4. Detalles Finales -->
                        <fieldset>
                            <legend class="text-2xl font-bold text-gray-800 border-b pb-4 mb-6 w-full">4. Detalles Finales</legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label for="mesa_mantel" class="block text-base font-semibold text-gray-800 mb-2">¿Gusta agregar mesa y mantel?<span class="text-red-500">*</span></label>
                                    <select id="mesa_mantel" name="mesa_mantel" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="si">Sí</option>
                                        <option value="no">No</option>
                                        <option value="otro">Otro (especificar)</option>
                                    </select>
                                </div>
                                <div id="campo_mesa_mantel_especificar" style="display: none;">
                                    <label for="mesa_mantel_especificar" class="block text-base font-semibold text-gray-800 mb-2">Por favor especifica</label>
                                    <input type="text" name="mesa_mantel_especificar" id="mesa_mantel_especificar" value="<?= old('mesa_mantel_especificar') ?>" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="dificultad_montaje" class="block text-base font-semibold text-gray-800 mb-2">Dificultad de Montaje<span class="text-red-500">*</span></label>
                                    <textarea id="dificultad_montaje" name="dificultad_montaje" rows="3" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: Es un 5to piso sin elevador, hay que caminar 200m, etc." required><?= old('dificultad_montaje') ?></textarea>
                                </div>
                                <div>
                                    <label for="como_nos_conocio" class="block text-base font-semibold text-gray-800 mb-2">¿Cómo supiste de Nosotros?<span class="text-red-500">*</span></label>
                                    <select id="como_nos_conocio" name="como_nos_conocio" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="recomendacion">Recomendación</option>
                                        <option value="redes">Redes Sociales</option>
                                        <option value="restaurante">Por el restaurante</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="tipo_consumidores" class="block text-base font-semibold text-gray-800 mb-2">Tipo de consumidores<span class="text-red-500">*</span></label>
                                    <select id="tipo_consumidores" name="tipo_consumidores" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="hombres">Hombres</option>
                                        <option value="mujeres">Mujeres</option>
                                        <option value="ninos">Niños</option>
                                        <option value="mixto">Mixto</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="restricciones_alimenticias" class="block text-base font-semibold text-gray-800 mb-2">¿Alguna restricción alimenticia?</label>
                                    <input type="text" name="restricciones_alimenticias" id="restricciones_alimenticias" value="<?= old('restricciones_alimenticias') ?>" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: Alergias, veganos, etc.">
                                </div>
                                <div>
                                    <label for="rango_presupuesto" class="block text-base font-semibold text-gray-800 mb-2">Rango de presupuesto (opcional)</label>
                                    <input type="text" name="rango_presupuesto" id="rango_presupuesto" value="<?= old('rango_presupuesto') ?>" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="$5,000 - $10,000">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="notas_adicionales" class="block text-base font-semibold text-gray-800 mb-2">Requisitos adicionales o especiales</label>
                                    <textarea name="notas_adicionales" id="notas_adicionales" rows="3" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"><?= old('notas_adicionales') ?></textarea>
                                </div>
                            </div>
                        </fieldset>

                        <div class="mt-12 text-right">
                             <button type="submit" class="bg-blue-600 text-white font-bold text-lg px-8 py-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                                Enviar Cotización
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Lateral para el Estimado Instantáneo -->
            <div class="lg:col-span-1 mt-12 lg:mt-0">
                <div class="sticky top-20 bg-white p-6 rounded-2xl shadow-xl border border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Estimado Instantáneo</h3>
                    <div class="border-t pt-4">
                        <div class="flex justify-between text-lg font-semibold text-gray-700">
                            <span>Total Estimado:</span>
                            <span id="instant-quote-total" class="text-blue-600">$0.00</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Este es un estimado basado en tus selecciones. El costo final se confirmará en la cotización formal.</p>
                    </div>
                    <div id="quote-summary" class="mt-4 text-sm text-gray-600 space-y-1">
                        <!-- Aquí se mostrará el resumen de ítems seleccionados -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- ESTRUCTURA DE LA MODAL ÚNICA -->
    <div id="menu-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all">
                <div class="p-6 md:p-8">
                    <div class="flex justify-between items-center border-b pb-4 mb-4">
                        <h2 id="modal-title" class="text-2xl font-bold text-gray-800">Selección de Opciones</h2>
                        <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <!-- Contenedor para la navegación por paneles -->
                    <div id="modal-content" class="min-h-[300px] relative">
                        <!-- El contenido AJAX (Nivel 2 o Nivel 3) se inyectará aquí -->
                    </div>

                    <div class="mt-6 flex justify-between pt-4 border-t">
                        <button id="back-modal-btn" class="bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-lg hover:bg-gray-300 transition hidden">
                            ← Regresar
                        </button>
                        <button id="confirm-modal-btn" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition ml-auto">
                            Confirmar y Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- FIN ESTRUCTURA DE LA MODAL -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        // --- Lógica para campo condicional: Nombre de Empresa ---
        const campoEmpresa = $('#campo_empresa');
        const inputNombreEmpresa = $('#nombre_empresa'); 
            
        $('#tipo_evento').on('change', function() {
            if ($(this).val() === 'empresarial') {
                campoEmpresa.slideDown(); // Muestra el campo
                inputNombreEmpresa.prop('required', true); // Hace el campo requerido
            } else {
                campoEmpresa.slideUp(); // Oculta el campo
                inputNombreEmpresa.prop('required', false); // Quita el atributo requerido
            }
        }).trigger('change'); // Dispara al cargar para establecer el estado inicial

        // --- Lógica para campo condicional: Mesa y Mantel ---
        const campoMesaMantel = $('#campo_mesa_mantel_especificar');
        const inputMesaMantel = $('#mesa_mantel_especificar');
            
        $('#mesa_mantel').on('change', function() {
            if ($(this).val() === 'otro') {
                campoMesaMantel.slideDown(); // Muestra el campo
                inputMesaMantel.prop('required', true); // Hace el campo requerido
            } else {
                campoMesaMantel.slideUp(); // Oculta el campo
                inputMesaMantel.prop('required', false); // Quita el atributo requerido
            }
        }).trigger('change'); // Dispara al cargar para establecer el estado inicial
        // =================================================================
        // INICIALIZACIÓN DE intl-tel-input (Usando CDN)
        // =================================================================
        const input = document.querySelector("#whatsapp");
        const iti = window.intlTelInput(input, {
            // Usamos 'mx' como país inicial por defecto
            initialCountry: "mx", 
            strictMode: true,
            loadUtils: () => import("https://cdn.jsdelivr.net/npm/intl-tel-input@25.11.2/build/js/utils.js"),
        });

        // =================================================================
        // LÓGICA DE LA MODAL Y NAVEGACIÓN (Nivel 2 <-> Nivel 3)
        // =================================================================
        const menuModal = $('#menu-modal');
        const modalTitle = $('#modal-title');
        const modalContent = $('#modal-content');
        const backModalBtn = $('#back-modal-btn');
        const confirmModalBtn = $('#confirm-modal-btn');
        const selectedItemsContainer = $('#selected-items-container');
        
        // Stack para la navegación (guarda el ID del padre anterior)
        let navigationStack = []; 
        let currentParentId = null;

        // --- Eventos de la Modal ---
        $('#close-modal-btn, #confirm-modal-btn').on('click', function() {
            menuModal.addClass('hidden');
            navigationStack = []; // Limpiar el stack al cerrar
            backModalBtn.addClass('hidden');
            
            updateParentState(); // Actualizar el estado de los padres al cerrar
            updateInstantQuote(); // Recalcular al cerrar la modal
        });

        backModalBtn.on('click', function() {
            if (navigationStack.length > 1) {
                navigationStack.pop(); // Eliminar el nivel actual
                const previousParentId = navigationStack[navigationStack.length - 1];
                loadModalContent(previousParentId);
            } else {
                // Si solo queda el Nivel 1, volver al Nivel 1 y ocultar el botón
                menuModal.addClass('hidden');
                navigationStack = [];
                backModalBtn.addClass('hidden');
            }
        });

        // --- Evento de Apertura de Modal (Al hacer clic en el CONTENEDOR de Nivel 1) ---
        $('#menu-options-root').on('click', '.block.cursor-pointer', function(e) {
            const $input = $(this).find('input[type="checkbox"], input[type="radio"]');
            const parentId = $input.val();
            
            // Si el click fue directamente en el input, dejamos que el evento 'change' lo maneje
            // if ($(e.target).is('input')) {
            //     // Si es un radio, limpiamos los otros grupos antes de abrir la modal
            //     // if ($input.attr('type') === 'radio' && $input.is(':checked')) {
            //     //     $('#menu-options-root input[type="radio"]').not($input).each(function() {
            //     //         const otherId = $(this).val();
            //     //         // selectedItemsContainer.find(`input[data-parent-id="${otherId}"]`).remove();
            //     //     });
            //     // }
            //     // Si es un checkbox y se desmarca, la lógica de limpieza está en el evento 'change'
            //     if ($input.attr('type') === 'checkbox' && !$input.is(':checked')) {
            //         return; 
            //     }
            // } else {
            //     // Si el click fue en el div/label, marcamos el input y abrimos la modal
            //     if (!$input.is(':checked')) {
            //         $input.prop('checked', true).trigger('change');
            //     }
            // }
            
            navigationStack = [parentId]; // Iniciar el stack con el ID del Nivel 1
            loadModalContent(parentId);
        });
        
        // --- Evento de Limpieza (Al desmarcar un checkbox de Nivel 1) ---
        // $('#menu-options-root').on('change', 'input[type="checkbox"]', function() {
        //     if (!$(this).is(':checked')) {
        //         const parentId = $(this).val();
        //         // selectedItemsContainer.find(`input[data-parent-id="${parentId}"]`).remove();
        //         updateInstantQuote();
        //     }
        // });
        
        // --- Evento de Navegación Interna (Al hacer clic en un ítem de Nivel 2 dentro de la Modal) ---
        menuModal.on('click', '.modal-nav-item', function(e) {
            e.preventDefault();
            const nextParentId = $(this).data('item-id');
            navigationStack.push(nextParentId); // Añadir el nuevo nivel al stack
            loadModalContent(nextParentId);
        });

        // --- Función Principal de Carga de Contenido AJAX ---
        function loadModalContent(parentId) {
            currentParentId = parentId;
            const csrfTokenName = $('input[name="csrf_test_name"]').attr('name');
            const csrfTokenValue = $('input[name="csrf_test_name"]').val();
            
            modalContent.html('<p class="text-center text-blue-600 mt-10">Cargando opciones...</p>');
            menuModal.removeClass('hidden');
            
            // Mostrar/Ocultar botón de regresar
            if (navigationStack.length > 1) {
                backModalBtn.removeClass('hidden');
            } else {
                backModalBtn.addClass('hidden');
            }

            $.ajax({
                url: '<?= url_to('QuotationController::loadSubOptionsAjax') ?>',
                type: 'POST',
                data: {
                    parent_id: parentId,
                    [csrfTokenName]: csrfTokenValue
                },
                dataType: 'json',
                success: function(response) {
                    $('input[name="csrf_test_name"]').val(response.token);
                    
                    if (response.success) {
                        modalContent.html(response.html);
                        
                        // 1. Restaurar el estado de los inputs (checkboxes/quantities)
                        restoreInputState(parentId);
                        
                        // 2. Actualizar el título de la modal
                        const parentName = modalContent.find('#modal-content-title').data('parent-name');
                        modalTitle.text(parentName || 'Selección de Opciones');
                        
                        // 3. Escuchar cambios dentro de la modal para guardar el estado
                        modalContent.off('change', 'input[name^="menu_selection"]').on('change', 'input[name^="menu_selection"]', function() {
                            saveInputState($(this));
                        });
                        
                    } else {
                        modalContent.html('<p class="text-red-500 text-center mt-10">Error al cargar las opciones.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al cargar sub-opciones:", error);
                    modalContent.html('<p class="text-red-500 text-center mt-10">Error de conexión. Inténtalo de nuevo.</p>');
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.token) {
                            $('input[name="csrf_test_name"]').val(response.token);
                        }
                    } catch (e) {}
                }
            });
        }
        
        // --- Funciones de Mantenimiento de Estado (CRÍTICO para la Modal) ---
        
        // Guarda el estado de un input en el contenedor oculto del formulario principal
        function saveInputState($input) {
            const itemId = $input.val();
            const parentId = currentParentId;
            const inputName = $input.attr('name'); // Esto es 'menu_selection[ID]'
            const inputType = $input.attr('type');
            let value = null;
            
            // Asignar el valor a 'value'
            if (inputType === 'radio' || inputType === 'checkbox') {
                value = $input.is(':checked') ? itemId : null;
            } else if (inputType === 'number') {
                value = $input.val() > 0 ? $input.val() : null;
            }
            
            // 1. Eliminar el input anterior con el mismo nombre
            selectedItemsContainer.find(`input[name="${inputName}"]`).remove();
            
            if (value !== null && value !== '0') {
                // 2. Añadir el input oculto al formulario principal
                const hiddenInput = `<input type="hidden" name="${inputName}" value="${value}" data-parent-id="${parentId}">`;
                selectedItemsContainer.append(hiddenInput);
            }
            
            // 3. Lógica de Radio Button (Mutuamente Excluyente)
            if (inputType === 'radio' && value !== null) {
                // Eliminar TODOS los otros inputs ocultos que pertenecen al MISMO GRUPO DE RADIO.
                selectedItemsContainer.find(`input[data-parent-id="${parentId}"][type="hidden"]`).not(`[name="${inputName}"]`).remove();
            }
            
            // Recalcular el estimado inmediatamente
            updateInstantQuote();
        }
        
        // Restaura el estado de los inputs al cargar la modal
        function restoreInputState(parentId) {
            console.log("res modl")
            // Iterar sobre los inputs en la modal
            modalContent.find('input[name^="menu_selection"]').each(function() {
                const $input = $(this);
                const inputName = $input.attr('name');
                const inputType = $input.attr('type');
                
                // Buscar el input oculto correspondiente en el formulario principal
                const $hiddenInput = selectedItemsContainer.find(`input[name="${inputName}"]`);
                
                if ($hiddenInput.length > 0) {
                    const hiddenValue = $hiddenInput.val();
                    
                    if (inputType === 'radio' || inputType === 'checkbox') {
                        // Para radio/checkbox, si el valor del input visible coincide con el valor guardado
                        if ($input.val() === hiddenValue) {
                            $input.prop('checked', true);
                        }
                    } else if (inputType === 'number') {
                        // Para number, simplemente restaurar el valor guardado
                        $input.val(hiddenValue);
                    }
                } else {
                    // Asegurar que los inputs no seleccionados estén limpios
                    if (inputType === 'number') {
                        $input.val(0);
                    } else {
                        $input.prop('checked', false);
                    }
                }
            });
        }
        
        // --- Lógica de Dependencia Padre-Hijo ---
        function updateParentState() {
            // Iterar sobre todos los ítems de Nivel 1 (Categorías Raíz)
            $('#menu-options-root input[type="checkbox"]').each(function() {
                const $parentInput = $(this);
                const parentId = $parentInput.val();
                
                // Buscar si existe AL MENOS UN ítem hijo seleccionado en el contenedor oculto
                const hasChildrenSelected = selectedItemsContainer.find(`input[data-parent-id="${parentId}"]`).length > 0;
                
                // Si tiene hijos seleccionados, debe estar marcado. Si no tiene, debe desmarcarse.
                if (hasChildrenSelected) {
                    $parentInput.prop('checked', true);
                } else {
                    $parentInput.prop('checked', false);
                }
            });
        }
        
        // =================================================================
        // LÓGICA DE ESTIMADO INSTANTÁNEO (Cálculo)
        // =================================================================
        
        // Escuchar cambios en todo el formulario para recalcular
        $('#quotation-form').on('change', 'input, select, textarea', function() {
            // Solo recalcular si no estamos en medio de una navegación AJAX
            if (!menuModal.hasClass('hidden')) return;
            updateInstantQuote();
        });

         $('#quotation-form').on('submit', function(e) {
            // 1. Obtener el número completo en formato internacional
            const fullNumber = iti.getNumber();
            
            // 2. Reemplazar el valor del input con el número completo antes de enviar
            $('#whatsapp').val(fullNumber);
            
            // 3. Opcional: Validar si el número es válido antes de enviar
            if (!iti.isValidNumber()) {
                alert("Por favor, introduce un número de WhatsApp válido.");
                e.preventDefault(); // Detener el envío del formulario
                return false;
            }
            
            // Si la validación de JS pasa, el formulario se envía con el número correcto.
            // La validación de CI4 en el controlador se encargará del resto.
        });
        
        // Asegurar que el cambio en la cantidad de invitados dispare el cálculo
        $('#cantidad_invitados').on('input', function() {
            updateInstantQuote();
        });
        
        function updateInstantQuote() {
            const csrfTokenName = $('input[name="csrf_test_name"]').attr('name');
            const csrfTokenValue = $('input[name="csrf_test_name"]').val();
            const numInvitados = $('#cantidad_invitados').val();
            
            // Recopilar todos los ítems seleccionados (incluyendo los ocultos)
            const menuSelections = {};
            selectedItemsContainer.find('input[name^="menu_selection"]').each(function() {
                const name = $(this).attr('name'); // menu_selection[ID]
                const id = name.match(/\[(\d+)\]/)[1];
                menuSelections[id] = $(this).val();
            });
            
            // Recopilar cantidades de inputs visibles (si se usa el campo quantity en el Nivel 1)
            $('#menu-options-root').find('input[type="number"]').each(function() {
                const name = $(this).attr('name');
                const id = name.match(/\[(\d+)\]/)[1];
                if ($(this).val() > 0) {
                    menuSelections[id] = $(this).val();
                }
            });

            const postData = {
                num_invitados: numInvitados,
                menu_selection: menuSelections,
                [csrfTokenName]: csrfTokenValue
            };
            
            // Si no hay ítems seleccionados, mostrar 0.00
            if (Object.keys(menuSelections).length === 0) {
                $('#instant-quote-total').text('$0.00');
                $('#quote-summary').html('<p class="text-gray-500">Aún no has seleccionado ningún servicio.</p>');
                return;
            }

            // Llamada AJAX al nuevo endpoint de cálculo
            $.ajax({
                url: '<?= url_to('QuotationController::calculateQuoteAjax') ?>',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    $('input[name="csrf_test_name"]').val(response.token);
                    if (response.success) {
                        $('#instant-quote-total').text(response.total_formatted);
                        
                        // Mostrar resumen de ítems
                        let summaryHtml = '';
                        for (const item of response.summary) {
                            summaryHtml += `<div class="flex justify-between"><span>${item.name}</span><span class="font-medium">${item.subtotal_formatted}</span></div>`;
                        }
                        $('#quote-summary').html(summaryHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al calcular cotización:", error);
                    $('#instant-quote-total').text('Error');
                }
            });
        }
        
        // Ejecutar al cargar la página para inicializar el estimado
        updateParentState(); // Inicializar el estado de los padres
        updateInstantQuote();
    </script>
</body>
</html>