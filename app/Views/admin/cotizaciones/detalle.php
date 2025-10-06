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
    <title><?= esc($titulo) ?> - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-100 pt-16">
    
    <!-- Asumo que el appbar se renderiza aquí o en un layout -->
    <!-- Si usas un layout, adapta el código de arriba -->
     <?= view('components/appbar', [
        'currentPage' => $currentPage ?? 'Dashboard', 
        'navLinks' => $navLinks = [
            'Dashboard' => ['url' => $baseURL . '/dashboard', 'active' => false],
            'Cotizaciones' => ['url' => $baseURL . '/cotizaciones', 'active' => true],
            'Calendario' => ['url' => $baseURL . '/calendario', 'active' => false],
            'Servicios' => ['url' => $baseURL . '/servicios', 'active' => false],
        ],
        'isLoggedIn' => $isLoggedIn,
    ]) ?>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Encabezado y Botones de Acción -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= esc($titulo) ?></h1>
                <p class="text-gray-500">Cotización #<?= esc($cotizacion['id_cotizacion']) ?> - Solicitada el <?= date('d/m/Y', strtotime($cotizacion['created_at'])) ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= site_url(route_to('panel.cotizaciones.edit', $cotizacion['id_cotizacion'])) ?>" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition"><i class="bi bi-pencil me-1"></i> Editar</a>
                <a href="<?= site_url(route_to('panel.dashboard')) ?>" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition">Volver</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Columna Izquierda: Detalles (8/12) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Tarjeta: Información del Cliente -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-person-circle mr-2"></i>Información del Cliente</h5>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div class="col-span-1">
                            <dt class="font-medium text-gray-700"><i class="bi bi-person-fill text-muted mr-2"></i>Nombre</dt>
                            <dd class="text-gray-900"><?= esc($cotizacion['cliente_nombre']) ?></dd>
                        </div>
                        <div class="col-span-1">
                            <dt class="font-medium text-gray-700"><i class="bi bi-whatsapp text-muted mr-2"></i>WhatsApp</dt>
                            <dd class="text-gray-900 flex items-center">
                                <span><?= esc($cotizacion['cliente_whatsapp']) ?></span>
                                <a href="https://wa.me/<?= esc($cotizacion['cliente_whatsapp']) ?>" target="_blank" class="ml-3 inline-flex items-center bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full hover:bg-green-200">
                                    <i class="bi bi-whatsapp mr-1"></i> Contactar
                                </a>
                            </dd>
                        </div>
                        <?php if(!empty($cotizacion['nombre_empresa'])): ?>
                            <div class="col-span-1">
                                <dt class="font-medium text-gray-700"><i class="bi bi-building text-muted mr-2"></i>Empresa</dt>
                                <dd class="text-gray-900"><?= esc($cotizacion['nombre_empresa']) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- Tarjeta: Detalles del Evento -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-calendar-event mr-2"></i>Detalles del Evento</h5>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-tag-fill text-muted mr-2"></i>Tipo de Evento</dt>
                            <dd class="text-gray-900"><?= translate($cotizacion['tipo_evento'], $uiLabels) ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-people-fill text-muted mr-2"></i>Invitados</dt>
                            <dd class="text-gray-900"><?= esc($cotizacion['num_invitados']) ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-calendar-check text-muted mr-2"></i>Fecha</dt>
                            <dd class="text-gray-900"><?= date('d/m/Y', strtotime($cotizacion['fecha_evento'])) ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-clock text-muted mr-2"></i>Horario</dt>
                            <dd class="text-gray-900"><?= date('h:i A', strtotime($cotizacion['hora_inicio'])) ?> - <?= date('h:i A', strtotime($cotizacion['hora_finalizacion'])) ?></dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="font-medium text-gray-700"><i class="bi bi-geo-alt-fill text-muted mr-2"></i>Dirección</dt>
                            <dd class="text-gray-900 whitespace-pre-wrap"><?= esc($cotizacion['direccion_evento']) ?></dd>
                        </div>
                    </dl>
                </div>

                <!-- Tarjeta: Servicios Seleccionados -->
                <div class="card shadow-sm border-0 bg-white p-6 rounded-xl shadow-lg">
                    <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-card-checklist mr-2"></i>Servicios Seleccionados</h5>
                    
                    <?php if(!empty($servicios_seleccionados)): ?>
                        <div class="divide-y divide-gray-100">
                            <?php foreach($servicios_seleccionados as $categoria => $items): ?>
                                <div class="py-3">
                                    <!-- Nombre de la Categoría (Nivel 1) -->
                                    <h6 class="text-base font-bold text-indigo-600 mb-2"><?= esc($categoria) ?></h6>
                                    
                                    <!-- Lista de Ítems Detallados -->
                                    <ul class="ml-4 space-y-3 text-sm">
                                        <?php foreach($items as $servicio): ?>
                                            <li class="flex justify-between items-start">
                                                <div class="text-gray-800">
                                                    <i class="bi bi-check text-green-500 mr-1"></i>
                                                    
                                                    <!-- CRÍTICO: Mostrar la ruta jerárquica -->
                                                    <?php 
                                                        $path_names = [];
                                                        // Empezar desde el Nivel 2 (índice 1) hasta el final
                                                        for ($i = 1; $i < count($servicio['path']); $i++) {
                                                            $path_names[] = esc($servicio['path'][$i]['nombre_item']);
                                                        }
                                                        // Unir los nombres con un separador
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
                        <div class="py-3 text-gray-500">No se seleccionaron servicios principales.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna Derecha: Estado, Finanzas y Acciones (4/12) -->
            <div class="lg:col-span-4 space-y-6">
                
                <?php
                    $status_value = strtolower($cotizacion['status'] ?? 'Pendiente');
                    $status_label = translate($status_value, $uiLabels);
                    $statusInfo = [
                        'pendiente' => ['color' => 'bg-yellow-500', 'icon' => 'bi-clock-history'],
                        'confirmado' => ['color' => 'bg-green-500', 'icon' => 'bi-check-circle-fill'],
                        'cancelado' => ['color' => 'bg-red-500', 'icon' => 'bi-x-circle-fill'],
                        'pagado' => ['color' => 'bg-blue-500', 'icon' => 'bi-wallet-fill'],
                        'contactado' => ['color' => 'bg-indigo-500', 'icon' => 'bi-telephone-outbound-fill'],
                        'en_revision' => ['color' => 'bg-gray-500', 'icon' => 'bi-search'],
                    ];
                    $info = $statusInfo[$status_value] ?? $statusInfo['pendiente'];
                ?>
                <!-- Tarjeta de Estado -->
                <div class="<?= $info['color'] ?> text-white p-6 rounded-xl shadow-lg text-center">
                    <i class="bi <?= $info['icon'] ?> text-5xl"></i>
                    <h4 class="text-xl font-bold mt-2 mb-0">Estado: <?= esc($status_label) ?></h4>
                </div>

                <!-- Tarjeta de Resumen Financiero -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-cash-coin mr-2"></i>Resumen Financiero</h5>
                    <ul class="divide-y divide-gray-200">
                        <li class="py-2 flex justify-between items-center">
                            <span>Costo Base</span>
                            <strong>$<?= number_format($cotizacion['total_estimado'], 2) ?></strong>
                        </li>
                        <li class="py-2 flex justify-between items-center bg-gray-50 rounded-b-lg">
                            <span class="font-bold">Total Estimado</span>
                            <strong class="text-2xl text-indigo-600">$<?= number_format($cotizacion['total_estimado'], 2) ?></strong>
                        </li>
                    </ul>
                    <div class="mt-4 pt-4 border-t">
                        <form action="<?= site_url(route_to('panel.cotizaciones.updateStatus')) ?>" method="post" class="space-y-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="cotizacion_id" value="<?= esc($cotizacion['id_cotizacion']) ?>">
                            <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" aria-label="Cambiar estado">
                                <option value="">-- Cambiar estado --</option>
                                <?php foreach(array_keys($uiLabels) as $status): ?>
                                    <?php if(in_array($status, ['pendiente', 'confirmado', 'cancelado', 'pagado', 'contactado', 'en_revision'])): ?>
                                        <option value="<?= $status ?>" <?= $status_value == $status ? 'disabled' : '' ?>>
                                            <?= translate($status, $uiLabels) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition">Guardar Estado</button>
                        </form>
                    </div>
                </div>

                <!-- Tarjeta de Logística y Requisitos -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h5 class="text-lg font-semibold mb-4 border-b pb-2"><i class="bi bi-clipboard-data mr-2"></i>Logística y Requisitos</h5>
                    <dl class="text-sm space-y-3">
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-tools text-muted mr-2"></i>Mesa y Mantel</dt>
                            <dd class="text-gray-900"><?= translate($cotizacion['mesa_mantel'], $uiLabels) ?> 
                                <?= !empty($cotizacion['mesa_mantel_especificar']) ? '('.esc($cotizacion['mesa_mantel_especificar']).')' : '' ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-truck text-muted mr-2"></i>Dificultad Montaje</dt>
                            <dd class="text-gray-900"><?= esc($cotizacion['dificultad_montaje']) ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-cash-stack text-muted mr-2"></i>Presupuesto</dt>
                            <dd class="text-gray-900"><?= esc($cotizacion['rango_presupuesto'] ?? 'No especificado') ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700"><i class="bi bi-card-text text-muted mr-2"></i>Notas Adicionales</dt>
                            <dd class="text-gray-900 whitespace-pre-wrap"><?= esc($cotizacion['notas_adicionales'] ?? 'No especificado') ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

</body>
</html>