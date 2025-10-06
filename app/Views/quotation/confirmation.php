<?php
// Mapeo de la función de traducción
function translate($value, $labels) {
    $key = strtolower($value);
    return $labels[$key] ?? $value;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cotización - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-100">
    <?= view('components/appbar', [ 'showNavLinks' => false]) ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-28">
        
        <!-- Encabezado de Éxito -->
        <div class="bg-white p-8 rounded-xl shadow-2xl text-center mb-8">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h1 class="text-4xl font-extrabold mt-4 mb-2 text-green-700">¡Cotización Enviada!</h1>
            <p class="text-lg text-gray-600">Tu solicitud con folio <strong class="text-gray-800">#<?= esc($cotizacion['id_cotizacion']) ?></strong> ha sido recibida. Aquí tienes el resumen:</p>
        </div>

        <div class="space-y-6">
            <!-- Tarjeta: Información del Cliente -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-person-circle mr-2"></i>Información del Cliente</h5>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div class="col-span-1">
                        <dt class="font-medium text-gray-700">Nombre</dt>
                        <dd class="text-gray-900"><?= esc($cotizacion['cliente_nombre']) ?></dd>
                    </div>
                    <div class="col-span-1">
                        <dt class="font-medium text-gray-700">WhatsApp</dt>
                        <dd class="text-gray-900"><?= esc($cotizacion['cliente_whatsapp']) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Tarjeta: Detalles del Evento -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-calendar-event mr-2"></i>Detalles del Evento</h5>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-gray-700">Tipo de Evento</dt>
                        <dd class="text-gray-900"><?= translate($cotizacion['tipo_evento'], $uiLabels) ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Invitados</dt>
                        <dd class="text-gray-900"><?= esc($cotizacion['num_invitados']) ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Fecha</dt>
                        <dd class="text-gray-900"><?= date('d/m/Y', strtotime($cotizacion['fecha_evento'])) ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Horario</dt>
                        <dd class="text-gray-900"><?= date('h:i A', strtotime($cotizacion['hora_inicio'])) ?> - <?= date('h:i A', strtotime($cotizacion['hora_finalizacion'])) ?></dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="font-medium text-gray-700">Dirección</dt>
                        <dd class="text-gray-900 whitespace-pre-wrap"><?= esc($cotizacion['direccion_evento']) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Tarjeta: Servicios Seleccionados -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-card-checklist mr-2"></i>Servicios Seleccionados</h5>
                <?php if(!empty($servicios_seleccionados)): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach($servicios_seleccionados as $categoria => $items): ?>
                            <div class="py-3">
                                <h6 class="text-base font-bold text-indigo-600 mb-2"><?= esc($categoria) ?></h6>
                                <ul class="ml-4 space-y-3 text-sm">
                                    <?php foreach($items as $servicio): ?>
                                        <li class="flex justify-between items-start">
                                            <div class="text-gray-800">
                                                <i class="bi bi-check text-green-500 mr-1"></i>
                                                <?php 
                                                    $path_names = [];
                                                    for ($i = 1; $i < count($servicio['path']); $i++) {
                                                        $path_names[] = esc($servicio['path'][$i]['nombre_item']);
                                                    }
                                                    echo implode(' > ', $path_names);
                                                ?>
                                            </div>
                                            <span class="text-gray-600 text-right flex-shrink-0 ml-4">
                                                <?php if($servicio['cantidad'] !== 'Sí'): ?>
                                                    Cant: <span class="font-medium"><?= esc($servicio['cantidad']) ?></span>
                                                <?php endif; ?>
                                                ($<?= number_format($servicio['precio'], 2) ?> c/u)
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="py-3 text-gray-500">No se seleccionaron servicios.</div>
                <?php endif; ?>
            </div>

            <!-- Tarjeta de Resumen Financiero y CTA -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-cash-coin mr-2"></i>Resumen Financiero</h5>
                <ul class="divide-y divide-gray-200">
                    <li class="py-3 flex justify-between items-center bg-gray-50 rounded-lg px-4">
                        <span class="font-bold text-lg">Total Estimado</span>
                        <strong class="text-2xl text-indigo-600">$<?= number_format($cotizacion['total_estimado'], 2) ?></strong>
                    </li>
                </ul>
                <div class="mt-6 pt-6 border-t text-center">
                    <h2 class="text-xl font-semibold mb-2 text-gray-800">Siguiente Paso: Confirma por WhatsApp</h2>
                    <p class="text-sm text-gray-500 mb-4">Haz clic en el botón para iniciar una conversación con nuestro equipo y finalizar los detalles.</p>
                    <a href="<?= esc($whatsapp_link) ?>" target="_blank" class="inline-flex items-center justify-center w-full sm:w-auto py-3 px-8 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-green-400 transition duration-150">
                        <i class="bi bi-whatsapp mr-3"></i>
                        Confirmar Cotización
                    </a>
                </div>
            </div>
        </div>
    </div>
</body> 
</html>