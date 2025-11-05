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
        .quantity-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #e2e8f0;
            color: #4a5568;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .quantity-btn:hover {
            background-color: #cbd5e0;
        }
        .quantity-display {
            min-width: 20px;
            text-align: center;
            font-weight: 500;
        }
        .meal-type-filter-btn {
            padding: 8px 16px;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.2s;
            background-color: #f3f4f6;
            color: #4b5563;
        }
        .meal-type-filter-btn.active, .meal-type-filter-btn:hover {
            background-color: #3b82f6;
            color: white;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@25.11.2/build/js/intlTelInput.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                                    <input type="number" name="num_invitados" id="cantidad_invitados" min="15" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" value="<?= old('num_invitados', 30) ?>" required>
                                </div>
                                <div>
                                    <label for="fecha_evento" class="block text-base font-semibold text-gray-800 mb-2">Fecha del Evento<span class="text-red-500">*</span></label>
                                    <input type="text" name="fecha_evento" id="fecha_evento" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                </div>
                                <div>
                                    <label for="hora_inicio" class="block text-base font-semibold text-gray-800 mb-2">Hora de Inicio<span class="text-red-500">*</span></label>
                                    <input type="text" name="hora_inicio" id="hora_inicio" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 time-picker" required>
                                </div>
                                <div>
                                    <label for="hora_consumo" class="block text-base font-semibold text-gray-800 mb-2">Hora de Consumo<span class="text-red-500">*</span></label>
                                    <input type="text" name="hora_consumo" id="hora_consumo" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 time-picker" required>
                                </div>
                                <div>
                                    <label for="hora_finalizacion" class="block text-base font-semibold text-gray-800 mb-2">Hora de Finalización<span class="text-red-500">*</span></label>
                                    <input type="text" name="hora_finalizacion" id="hora_finalizacion" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 time-picker" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="direccion_evento" class="block text-base font-semibold text-gray-800 mb-2">Dirección del Evento<span class="text-red-500">*</span></label>
                                    <textarea id="direccion_evento" name="direccion_evento" rows="3" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Calle, número, colonia y referencias." required><?= old('direccion_evento') ?></textarea>
                                </div>
                            </div>
                        </fieldset>

                        <!-- 3. Selección de Servicios (Flujo por Pasos) -->
                        <fieldset class="mb-10">
                            <legend class="text-2xl font-bold text-gray-800 border-b pb-4 mb-6 w-full">3. Selección de Servicios</legend>

                            <!-- Contenedor del Menú Principal -->
                            <div id="step-2-menu" class="mt-8">
                                <div class="mb-4">
                                     <h4 class="text-xl font-semibold">Arma tu Menú</h4>
                                </div>

                                <!-- Filtro de Tipo de Comida (solo para Platillos Individuales) -->
                                <div id="meal-type-filter-container" class="hidden my-4 p-3 bg-gray-50 rounded-lg">
                                    <p class="font-semibold text-gray-700 mb-2">Filtrar por tipo de comida:</p>
                                    <div class="flex items-center gap-3">
                                        <button type="button" class="meal-type-filter-btn active" data-meal-type="ambos">Todos</button>
                                        <button type="button" class="meal-type-filter-btn" data-meal-type="desayuno">Desayuno</button>
                                        <button type="button" class="meal-type-filter-btn" data-meal-type="comida">Comida</button>
                                    </div>
                                </div>

                                <!-- Selector de Categorías (Renderizado dinámicamente) -->
                                <div class="mb-6">
                                    <div id="category-tabs" class="flex flex-wrap gap-3">
                                        <!-- Las pestañas de categoría se cargarán aquí -->
                                    </div>
                                </div>

                                <!-- Contenedor para ítems raíz (Renderizado dinámicamente) -->
                                <div id="menu-options-root" class="space-y-4 mb-6">
                                    <!-- El contenido se cargará aquí vía AJAX -->
                                </div>
                            </div>

                            <!-- Contenedor para ítems seleccionados (para mantener el estado, no cambia) -->
                            <div id="selected-items-container" class="hidden">
                                <!-- Aquí se inyectarán los inputs ocultos de los ítems seleccionados -->
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
                                <div>
                                    <label for="modalidad_servicio" class="block text-base font-semibold text-gray-800 mb-2">Modalidad de Servicio<span class="text-red-500">*</span></label>
                                    <select id="modalidad_servicio" name="modalidad_servicio" class="w-full px-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="buffet_self_service">Buffet / Self Service (Menores a 20 personas)</option>
                                        <option value="buffet_asistido">Buffet asistido por staff</option>
                                        <option value="servicio_a_la_mesa">Servicio a la mesa</option>
                                    </select>
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

                    <div class="mt-6 flex justify-between items-center pt-4 border-t">
                        <button id="back-modal-btn" class="bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-lg hover:bg-gray-300 transition hidden">
                            ← Regresar
                        </button>

                        <!-- Campo de Cantidad -->
                        <div class="flex items-center gap-2">
                            <label for="modal-quantity" class="text-sm font-medium text-gray-700">Cantidad:</label>
                            <input type="number" id="modal-quantity" min="1" class="w-20 px-2 py-1 text-center border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <script>

        const fechaInput = document.getElementById('fecha_evento');
        const fechasUrl = "<?= site_url('cotizacion/fechas-ocupadas') ?>";

        if (fechaInput && fechasUrl) {
            // 1. Hacemos una petición para obtener las fechas ocupadas
            fetch(fechasUrl)
                .then(response => response.json())
                .then(fechasOcupadas => {
                    // 2. Una vez que tenemos las fechas, inicializamos Flatpickr
                    flatpickr(fechaInput, {
                        locale: "es", // Usar el idioma español
                        dateFormat: "Y-m-d", // Formato que se envía al servidor
                        altInput: true, // Muestra un formato amigable al usuario
                        altFormat: "F j, Y", // ej: "Agosto 16, 2025"

                        // --- ¡AQUÍ ESTÁ LA MAGIA! ---
                        minDate: "today", // No permite seleccionar fechas pasadas
                        disable: fechasOcupadas, // Deshabilita las fechas que vienen del servidor
                    });
                })
                .catch(error => {
                    // Si falla la carga de fechas, al menos bloqueamos las pasadas
                    console.error("Error al cargar las fechas ocupadas:", error);
                    flatpickr(fechaInput, {
                        locale: "es",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "F j, Y",
                        minDate: "today",
                    });
                });
        }

        const horaInicioInput = document.getElementById('hora_inicio');
        const horaConsumoInput = document.getElementById('hora_consumo');
        const horaFinalizacionInput = document.getElementById('hora_finalizacion');

       // 1. Define la configuración una sola vez para todos los selectores de hora
        const timePickerConfig = {
            enableTime: true,       // Esencial: Habilita la selección de hora
            noCalendar: true,       // Esencial: Oculta el calendario, dejando solo la hora
            dateFormat: "h:i K",      // Formato que se envía al servidor (ej: "16:30")
            time_24hr: false,        // Usa el formato de 24 horas en la interfaz
            minuteIncrement: 15,    // Permite seleccionar en intervalos de 15 minutos (más amigable)
            locale: "es",           // Idioma español
        };

        // 2. Aplica la configuración a todos los campos que tengan la clase "time-picker"
        // Flatpickr es lo suficientemente inteligente para inicializar cada uno de ellos.
        flatpickr(".time-picker", timePickerConfig);

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
        $('#close-modal-btn, #confirm-modal-btn').on('click', function(e) {
            // Si se confirma, guardar la cantidad del platillo principal
            if (e.currentTarget.id === 'confirm-modal-btn') {
                const $quantitySelector = $('#modal-quantity').closest('.flex.items-center.gap-2');

                // Solo guardar la cantidad principal si el selector está visible
                if ($quantitySelector.is(':visible')) {
                    const quantity = $('#modal-quantity').val();

                    if (mainDishParentId && quantity > 0) {
                        const mainDishInput = `<input type="hidden"
                                                      class="main-dish-item"
                                                      name="menu_quantities[${mainDishParentId}]"
                                                      value="${quantity}"
                                                      data-item-id="${mainDishParentId}">`;

                        selectedItemsContainer.find(`.main-dish-item[data-item-id="${mainDishParentId}"]`).remove();
                        selectedItemsContainer.append(mainDishInput);
                    }
                } else {
                    // Si el selector está oculto, la cantidad se define por las sub-opciones.
                    // Nos aseguramos de eliminar cualquier input de cantidad para el platillo padre para evitar confusiones en el resumen.
                    selectedItemsContainer.find(`.main-dish-item[data-item-id="${mainDishParentId}"]`).remove();
                }
            }

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

        // --- LÓGICA DE LA MODAL REFACTORIZADA PARA FLUJO MULTI-PASO ---

        let modalStepsData = [];
        let currentStepIndex = 0;
        let mainDishParentId = null;

        // --- Evento de Apertura de Modal ---
        $('#menu-options-root').on('click', '.menu-item-selectable', function(e) {
            const itemId = $(this).data('id');
            const hasChildren = $(this).data('has-children');

            if (hasChildren) {
                loadAndInitializeModal(itemId);
            } else {
                // Lógica para ítems simples (sin personalización)
                // Por ejemplo, agregarlo directamente al resumen
                const hiddenInput = `<input type="hidden" name="menu_selection[${itemId}]" value="${itemId}" data-main-dish="${itemId}">`;
                selectedItemsContainer.append(hiddenInput);
                updateInstantQuote();
            }
        });

        // --- Botón de "Regresar" y "Siguiente" dentro de la modal ---
        backModalBtn.on('click', function() {
            if (currentStepIndex > 0) {
                currentStepIndex--;
                renderCurrentStep();
            }
        });
        
        $('#modal-content').on('click', '#next-step-btn', function() {
            if (currentStepIndex < modalStepsData.length - 1) {
                currentStepIndex++;
                renderCurrentStep();
            }
        });

        // --- Función Principal de Carga y Renderizado ---
        function loadAndInitializeModal(itemId) {
            mainDishParentId = itemId;
            const csrfTokenName = $('input[name="csrf_test_name"]').attr('name');
            const csrfTokenValue = $('input[name="csrf_test_name"]').val();

            // Rellenar la cantidad por defecto
            const defaultQuantity = $('#cantidad_invitados').val() || 1;
            $('#modal-quantity').val(defaultQuantity);

            modalContent.html('<p class="text-center text-blue-600 mt-10">Cargando personalización...</p>');
            menuModal.removeClass('hidden');

            $.ajax({
                url: '<?= site_url('cotizacion/ajax/item-details') ?>',
                type: 'POST',
                data: { parent_id: itemId, [csrfTokenName]: csrfTokenValue },
                dataType: 'json',
                success: function(response) {
                    $('input[name="csrf_test_name"]').val(response.token);
                    if (response.success) {
                        modalStepsData = response.steps;
                        currentStepIndex = 0;
                        modalTitle.text(response.parentName || 'Personaliza tu platillo');
                        renderCurrentStep();

                        // --- LÓGICA DE VISIBILIDAD DEL SELECTOR DE CANTIDAD PRINCIPAL ---
                        // Comprobar si alguno de los pasos renderizados contiene un selector de cantidad.
                        // Esto indica que la cantidad se define a nivel de sub-opción.
                        if (modalContent.find('.simple-quantity-item').length > 0) {
                            // Ocultar el contenedor del input de cantidad en el footer de la modal
                             $('#modal-quantity').closest('.flex.items-center.gap-2').hide();
                        } else {
                            // Mostrar para ítems personalizables estándar
                             $('#modal-quantity').closest('.flex.items-center.gap-2').show();
                        }
                        // --- FIN LÓGICA DE VISIBILIDAD ---

                    } else {
                        modalContent.html('<p class="text-red-500">Error al cargar opciones.</p>');
                    }
                },
                error: function() {
                    modalContent.html('<p class="text-red-500">Error de conexión.</p>');
                }
            });
        }
        
        function renderCurrentStep() {
            const step = modalStepsData[currentStepIndex];
            let html = `<h4 class="text-lg font-semibold text-gray-700 mb-4">${step.stepTitle}</h4>`;
            const optionsContainerClass = (step.tipo_ui === 'quantity') ? 'space-y-3' : 'grid grid-cols-1 md:grid-cols-2 gap-4';
            html += `<div class="${optionsContainerClass}">`;

            step.options.forEach(opt => {
                const inputType = step.tipo_ui;
                const name = `menu_selection[${mainDishParentId}][${step.stepId}]`;
                const id = `option-${opt.id_item}`;

                if (inputType === 'quantity') {
                    html += `
                        <div class="simple-quantity-item p-3 border-2 border-gray-200 rounded-lg" data-item-id="${opt.id_item}" data-main-dish="${mainDishParentId}" data-step-id="${step.stepId}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold text-gray-800">${opt.nombre_item}</h3>
                                    ${opt.precio_unitario > 0 ? `<p class="text-sm text-indigo-500">+$${parseFloat(opt.precio_unitario).toFixed(2)}</p>` : ''}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="quantity-btn quantity-decrease-btn">-</button>
                                    <input type="text" readonly class="quantity-input w-10 text-center bg-transparent font-medium" value="0" name="quantity_${opt.id_item}">
                                    <button type="button" class="quantity-btn quantity-increase-btn">+</button>
                                </div>
                            </div>
                        </div>
                    `;
                } else { // 'radio' or 'checkbox'
                    html += `
                        <label for="${id}" class="block cursor-pointer">
                            <input type="${inputType}" id="${id}" name="${name}" value="${opt.id_item}" class="peer sr-only">
                            <div class="p-3 border-2 border-gray-200 rounded-lg hover:border-indigo-400 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-800">${opt.nombre_item}</span>
                                    ${opt.precio_unitario > 0 ? `<span class="text-sm text-indigo-500">+$${parseFloat(opt.precio_unitario).toFixed(2)}</span>` : ''}
                                </div>
                            </div>
                        </label>
                    `;
                }
            });
            html += '</div>';

            if (currentStepIndex < modalStepsData.length - 1) {
                html += '<div class="text-right mt-6"><button id="next-step-btn" class="bg-indigo-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-indigo-700">Siguiente →</button></div>';
            }

            modalContent.html(html);
            backModalBtn.toggleClass('hidden', currentStepIndex === 0);

            restoreInputState(mainDishParentId, step.stepId);

            modalContent.find(`input[type="radio"], input[type="checkbox"]`).off('change').on('change', function() {
                saveInputState($(this), mainDishParentId, step.stepId);
            });

            modalContent.find('.simple-quantity-item .quantity-btn').off('click').on('click', function() {
                const $container = $(this).closest('.simple-quantity-item');
                const $input = $container.find('.quantity-input');
                let currentValue = parseInt($input.val(), 10);
                const itemId = $container.data('item-id').toString();
                const mainDishId = $container.data('main-dish').toString();
                const stepId = $container.data('step-id').toString();

                if ($(this).hasClass('quantity-increase-btn')) {
                    if (currentValue === 0) {
                        currentValue = parseInt($('#cantidad_invitados').val(), 10) || 1;
                    } else {
                        currentValue++;
                    }
                } else if ($(this).hasClass('quantity-decrease-btn')) {
                    currentValue = Math.max(0, currentValue - 1);
                }

                $input.val(currentValue);
                saveQuantitySubOptionState(itemId, mainDishId, stepId, currentValue);
            });
        }
        
        function saveQuantitySubOptionState(itemId, mainDishId, stepId, quantity) {
            // Remove both selection and quantity inputs for this item to avoid duplicates
            selectedItemsContainer.find(`input[data-item-id="${itemId}"]`).remove();

            if (quantity > 0) {
                const name = `menu_selection[${mainDishId}][${stepId}]`;

                // Input for SELECTION
                const selectionInput = `<input type="hidden"
                                               name="${name}"
                                               value="${itemId}"
                                               data-main-dish="${mainDishId}"
                                               data-item-id="${itemId}">`;

                // Input for QUANTITY
                const quantityInput = `<input type="hidden"
                                              name="menu_quantities[${itemId}]"
                                              value="${quantity}"
                                              data-main-dish="${mainDishId}"
                                              data-item-id="${itemId}">`;

                selectedItemsContainer.append(selectionInput).append(quantityInput);
            }

            updateInstantQuote();
        }

        function saveInputState($input, mainDishId, stepId) {
            const inputName = $input.attr('name');
            const inputType = $input.attr('type');
            const value = $input.is(':checked') ? $input.val() : null;

            if (inputType === 'radio') {
                selectedItemsContainer.find(`input[name^="menu_selection[${mainDishId}][${stepId}]"]`).remove();
            }

            selectedItemsContainer.find(`input[name="${inputName}"][value="${$input.val()}"]`).remove();

            if (value) {
                const hiddenInput = `<input type="hidden" name="${inputName}" value="${value}" data-main-dish="${mainDishId}">`;
                selectedItemsContainer.append(hiddenInput);
            }
            updateInstantQuote();
        }

        function restoreInputState(mainDishId, stepId) {
            const stepSelectionInputs = selectedItemsContainer.find(`input[name^="menu_selection[${mainDishId}][${stepId}]"]`);
            stepSelectionInputs.each(function() {
                const savedValue = $(this).val();
                modalContent.find(`input[value="${savedValue}"]`).prop('checked', true);
            });

            modalContent.find('.simple-quantity-item').each(function() {
                const $container = $(this);
                const itemId = $container.data('item-id').toString();
                const $savedQuantityInput = selectedItemsContainer.find(`input[name="menu_quantities[${itemId}]"]`);

                if ($savedQuantityInput.length) {
                    const savedQuantity = $savedQuantityInput.val();
                    $container.find('.quantity-input').val(savedQuantity);
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
            // Validación para asegurar que se ha seleccionado al menos un platillo.
            if ($('#selected-items-container').children().length === 0) {
                alert('Por favor, selecciona al menos un platillo o servicio del menú antes de enviar la cotización.');
                e.preventDefault(); // Detener el envío del formulario
                return;
            }

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
            
            const menuQuantities = {};
            const selectedIds = new Set();

            // 1. Recopilar cantidades y IDs de todos los inputs en el contenedor.
            // Esto se convierte en la única fuente de verdad.
            selectedItemsContainer.find('input[name^="menu_quantities"]').each(function() {
                const itemId = $(this).attr('name').match(/\[(\d+)\]/)[1];
                const quantity = $(this).val();
                menuQuantities[itemId] = quantity;
                selectedIds.add(itemId); // Un item con cantidad siempre está seleccionado.
            });

            // 2. Recopilar selecciones (checkbox/radio) y sus platillos principales.
            selectedItemsContainer.find('input[name^="menu_selection"]').each(function() {
                // Agregar el ID de la opción/sub-opción seleccionada
                selectedIds.add($(this).val());

                // Agregar el ID del platillo principal al que pertenece esta opción
                const mainDishId = $(this).data('main-dish');
                if (mainDishId) {
                    selectedIds.add(mainDishId.toString());
                }
            });

            // 3. Convertir el Set a un objeto plano para el backend.
            const flatMenuSelection = {};
            selectedIds.forEach(id => {
                flatMenuSelection[id] = id;
            });

            const postData = {
                num_invitados: numInvitados,
                menu_selection: flatMenuSelection,
                menu_quantities: menuQuantities,
                [csrfTokenName]: csrfTokenValue
            };
            
            // Si no hay ítems seleccionados, mostrar 0.00
            if (selectedIds.size === 0) {
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
                        
                        // --- START NEW GROUPING LOGIC ---

                        // 1. Create a map of parent-child relationships from the hidden inputs
                        const itemRelationships = {}; // { childId: parentId }
                        selectedItemsContainer.find('input[data-main-dish]').each(function() {
                            const childId = $(this).val();
                            const parentId = $(this).data('main-dish');
                            // Ensure the relationship is not self-referential
                            if (childId != parentId) {
                                itemRelationships[childId] = parentId.toString();
                            }
                        });

                        // 2. Group summary items from the AJAX response
                        const mainItems = {};
                        const childItems = [];

                        response.summary.forEach(item => {
                            const itemId = item.id.toString();
                            const parentId = itemRelationships[itemId];

                            if (parentId && parentId !== itemId) {
                                item.parentId = parentId; // Tag item with its parent
                                childItems.push(item);
                            } else {
                                mainItems[itemId] = item;
                                mainItems[itemId].children = []; // Prepare children array
                            }
                        });

                        // 3. Associate children with their parents
                        childItems.forEach(child => {
                            if (mainItems[child.parentId]) {
                                mainItems[child.parentId].children.push(child);
                            } else {
                                // Orphan child: render it as a main item to prevent it from disappearing
                                mainItems[child.id.toString()] = child;
                                mainItems[child.id.toString()].children = [];
                            }
                        });

                        // 4. Build the new HTML from the grouped structure
                        let summaryHtml = '';
                        for (const itemId in mainItems) {
                            if (!mainItems.hasOwnProperty(itemId)) continue;

                            const mainItem = mainItems[itemId];

                            summaryHtml += `<div class="py-2 border-b border-gray-100 group">`;
                            // Main item display
                            summaryHtml += `
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-gray-800">${mainItem.name}</span>
                                    <div class="flex items-center">
                                        <span class="font-bold text-gray-900 mr-3">${mainItem.subtotal_formatted}</span>
                                        <button type="button" class="remove-item-btn text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity" data-item-id="${mainItem.id}">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        </button>
                                    </div>
                                </div>`;

                            // Quantity controls for main item ONLY
                            if (mainItem.quantity) {
                                summaryHtml += `
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-sm text-gray-500">Cantidad:</span>
                                        <button type="button" class="quantity-btn quantity-decrease-btn" data-item-id="${mainItem.id}">-</button>
                                        <span class="quantity-display">${mainItem.quantity}</span>
                                        <button type="button" class="quantity-btn quantity-increase-btn" data-item-id="${mainItem.id}">+</button>
                                    </div>`;
                            }

                            // Display children, if any
                            if (mainItem.children && mainItem.children.length > 0) {
                                summaryHtml += `<div class="pl-4 mt-2 space-y-1 border-l-2 border-gray-200">`;
                                mainItem.children.forEach(child => {
                                    summaryHtml += `
                                        <div class="flex justify-between items-center text-sm group">
                                            <span class="text-gray-600 pl-2"> - ${child.name}</span>
                                            <div class="flex items-center">
                                                <span class="text-gray-800 mr-3">${child.subtotal_formatted}</span>
                                                <button type="button" class="remove-item-btn text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity" data-item-id="${child.id}">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                </button>
                                            </div>
                                        </div>`;
                                });
                                summaryHtml += `</div>`;
                            }

                            summaryHtml += `</div>`;
                        }

                        $('#quote-summary').html(summaryHtml);
                        // --- END NEW GROUPING LOGIC ---
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

        // --- Lógica para Filtrar Menú por Tipo de Comida ---
        $('#category-tabs').on('click', '.meal-type-btn', function() {
            const parentId = $(this).data('category-id');

            // Actualizar estilo de los botones
            $('#category-tabs .meal-type-btn').removeClass('bg-blue-600 text-white').addClass('bg-gray-200 text-gray-600');
            $(this).removeClass('bg-gray-200 text-gray-600').addClass('bg-blue-600 text-white');

            // Cargar los ítems del menú correspondientes
            loadMenuItems(parentId);
        });

        function loadMenuItems(parentId) {
            const csrfTokenName = $('input[name="csrf_test_name"]').attr('name');
            const csrfTokenValue = $('input[name="csrf_test_name"]').val();

            $('#menu-options-root').html('<p class="text-center text-blue-600">Cargando platillos...</p>');

            $.ajax({
                url: '<?= site_url('cotizacion/ajax/menu-items') ?>',
                type: 'POST',
                data: {
                    parent_id: parentId,
                    [csrfTokenName]: csrfTokenValue
                },
                dataType: 'json',
                success: function(response) {
                    $('input[name="csrf_test_name"]').val(response.token);
                    if (response.success) {
                        $('#menu-options-root').html(response.html);
                    } else {
                        $('#menu-options-root').html('<p class="text-red-500 text-center">Error al cargar el menú.</p>');
                    }
                },
                error: function() {
                    $('#menu-options-root').html('<p class="text-red-500 text-center">Error de conexión.</p>');
                }
            });
        }

        // =================================================================
        // LÓGICA DE CARGA INICIAL DEL MENÚ
        // =================================================================
        function loadMenuCategories(mealType = 'ambos') {
            const csrfTokenName = $('input[name="csrf_test_name"]').attr('name');
            const csrfTokenValue = $('input[name="csrf_test_name"]').val();

            $('#category-tabs').html('<p class="text-center text-blue-600">Cargando categorías...</p>');

            $.ajax({
                url: '<?= site_url('cotizacion/ajax/menu-categories') ?>', // Nuevo endpoint simplificado
                type: 'POST',
                data: {
                    meal_type: mealType,
                    [csrfTokenName]: csrfTokenValue
                },
                dataType: 'json',
                success: function(response) {
                    $('input[name="csrf_test_name"]').val(response.token);
                    if (response.success && response.categories.length > 0) {
                        $('#category-tabs').empty();

                        response.categories.forEach((cat, index) => {
                             const button = `
                                <button type="button"
                                        class="meal-type-btn px-4 py-2 text-sm font-semibold rounded-full transition-colors duration-200 ${index === 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'}"
                                        data-category-id="${cat.id_item}">
                                    ${cat.nombre_item}
                                </button>
                            `;
                            $('#category-tabs').append(button);
                        });

                        // Cargar los platillos de la primera categoría por defecto
                        loadMenuItems(response.categories[0].id_item);

                    } else {
                        $('#category-tabs').html('<p class="text-red-500 text-center">No se encontraron categorías.</p>');
                    }
                },
                error: function() {
                    $('#category-tabs').html('<p class="text-red-500 text-center">Error de conexión.</p>');
                }
            });
        }

        // --- Lógica para el Filtro de Tipo de Comida ---
        $('#meal-type-filter-container').on('click', '.meal-type-filter-btn', function() {
            // Estilo de botones
            $('.meal-type-filter-btn').removeClass('active');
            $(this).addClass('active');

            // Volver a cargar las categorías con el filtro
            const mealType = $(this).data('meal-type');
            loadMenuCategories(mealType);
        });

        // --- Carga Inicial ---
        $(document).ready(function() {
            // El filtro "ambos" está activo por defecto
            $('#meal-type-filter-container').slideDown();
            loadMenuCategories('ambos');
        });

        // --- Lógica para Eliminar Ítems desde el Resumen ---
        $('#quote-summary').on('click', '.remove-item-btn', function() {
            const itemIdToRemove = $(this).data('item-id').toString();

            // Remove the quantity input for this item.
            selectedItemsContainer.find(`input[name="menu_quantities[${itemIdToRemove}]"]`).remove();

            // If it's a main dish, remove all its sub-options as well.
            const isMainDish = selectedItemsContainer.find(`input[data-main-dish="${itemIdToRemove}"]`).length > 0;
            if (isMainDish) {
                selectedItemsContainer.find(`input[data-main-dish="${itemIdToRemove}"]`).each(function() {
                    const subOptionId = $(this).val();
                    // Also remove any quantity inputs associated with sub-options
                    selectedItemsContainer.find(`input[name="menu_quantities[${subOptionId}]"]`).remove();
                    $(this).remove();
                });
            }

            // Remove the selection input itself.
            selectedItemsContainer.find(`input[value="${itemIdToRemove}"]`).remove();

            // Visually uncheck the corresponding checkbox/radio if it exists.
            $(`.menu-item-selectable input[value="${itemIdToRemove}"]`).prop('checked', false);

            // 3. Recalcula y actualiza la UI
            updateInstantQuote();
            updateParentState();
        });

        // --- Lógica para los botones de Cantidad en el Resumen ---
        $('#quote-summary').on('click', '.quantity-btn', function() {
            const itemId = $(this).data('item-id').toString();
            // Find the quantity input by its name.
            const $quantityInput = selectedItemsContainer.find(`input[name="menu_quantities[${itemId}]"]`);

            if ($quantityInput.length) {
                let currentQuantity = parseInt($quantityInput.val(), 10);

                if ($(this).hasClass('quantity-increase-btn')) {
                    currentQuantity++;
                } else if ($(this).hasClass('quantity-decrease-btn') && currentQuantity > 1) {
                    currentQuantity--;
                }

                // Update the input's value and recalculate.
                $quantityInput.val(currentQuantity);
                updateInstantQuote();
            }
        });

        // --- LÓGICA PARA EL NUEVO SELECTOR DE CANTIDAD DE PLATILLOS SIMPLES ---
        $('#menu-options-root').on('click', '.simple-quantity-item .quantity-btn', function() {
            const $container = $(this).closest('.simple-quantity-item');
            const $input = $container.find('.quantity-input');
            let currentValue = parseInt($input.val(), 10);

            if ($(this).hasClass('quantity-increase-btn')) {
                if (currentValue === 0) {
                    // Si es el primer incremento, usar la cantidad de invitados
                    currentValue = parseInt($('#cantidad_invitados').val(), 10) || 1;
                } else {
                    currentValue++;
                }
            } else if ($(this).hasClass('quantity-decrease-btn')) {
                currentValue = Math.max(0, currentValue - 1);
            }

            $input.val(currentValue).trigger('change');
        });

        $('#menu-options-root').on('change', '.simple-quantity-item .quantity-input', function() {
            const $container = $(this).closest('.simple-quantity-item');
            const itemId = $container.data('item-id').toString();
            const quantity = parseInt($(this).val(), 10);

            // Remover inputs existentes para este item para evitar duplicados.
            selectedItemsContainer.find(`input[data-item-id="${itemId}"]`).remove();

            if (quantity > 0) {
                // Input para la SELECCIÓN (para que el backend sepa que se eligió)
                const selectionInput = `<input type="hidden"
                                               name="menu_selection[${itemId}]"
                                               value="${itemId}"
                                               data-item-id="${itemId}">`;

                // Input para la CANTIDAD
                const quantityInput = `<input type="hidden"
                                              name="menu_quantities[${itemId}]"
                                              value="${quantity}"
                                              data-item-id="${itemId}">`;

                selectedItemsContainer.append(selectionInput).append(quantityInput);
            }

            updateInstantQuote();
        });
    </script>
</body>
</html>