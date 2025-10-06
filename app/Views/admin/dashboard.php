<?php
// app/Views/admin/dashboard.php
// Variables disponibles: $pendientes, $confirmadas_mes, $ingresos_mes, $kpi_conversion, $ultimas_cotizaciones, $grafica_ingresos_json, $stats_canal_origen_json, $stats_tipo_evento_json, $navLinks, $isLoggedIn, etc.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Incluir Chart.js aquí para las gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Opcional: Incluir un CDN de iconos si usas Bootstrap Icons (bi bi-...) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-100 pt-16">
    
    <!-- Renderizar el appbar (ya adaptado en el paso anterior) -->
    <?= view('components/appbar', [
        'currentPage' => $currentPage ?? 'Dashboard', 
        'navLinks' => $navLinks = [
            'Dashboard' => ['url' => $baseURL. '/dashboard', 'active' => true],
            'Cotizaciones' => ['url' => $baseURL . '/cotizaciones', 'active' => false],
            'Calendario' => ['url' => $baseURL . '/calendario', 'active' => false],
            'Servicios' => ['url' => $baseURL . '/servicios', 'active' => false],
        ],
        'isLoggedIn' => $isLoggedIn,
    ]) ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-500">¡Bienvenido de nuevo! Este es el resumen de tu negocio.</p>
        </div>

        <!-- Fila de Tarjetas (KPIs) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Tarjeta: Cotizaciones Pendientes -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <div class="bg-yellow-100 text-yellow-600 rounded-full w-12 h-12 flex items-center justify-center">
                            <i class="bi bi-clock-history text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pendientes</p>
                        <p class="text-2xl font-bold text-gray-900"><?= esc($pendientes) ?></p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta: Eventos Confirmados (Mes) -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <div class="bg-green-100 text-green-600 rounded-full w-12 h-12 flex items-center justify-center">
                            <i class="bi bi-calendar-check text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Confirmados (Mes)</p>
                        <p class="text-2xl font-bold text-gray-900"><?= esc($confirmadas_mes) ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta: Ingresos del Mes -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                         <div class="bg-blue-100 text-blue-600 rounded-full w-12 h-12 flex items-center justify-center">
                            <i class="bi bi-cash-stack text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Ingresos (Mes)</p>
                        <p class="text-2xl font-bold text-gray-900">$<?= number_format($ingresos_mes, 2) ?></p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta: Tasa de Conversión -->
            <div class="bg-white p-5 rounded-xl shadow-lg border-l-4 border-indigo-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                         <div class="bg-indigo-100 text-indigo-600 rounded-full w-12 h-12 flex items-center justify-center">
                            <i class="bi bi-bullseye text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tasa de Conversión</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($kpi_conversion['tasa'], 1) ?>%</p>
                        <small class="text-gray-500"><?= $kpi_conversion['confirmadas'] ?> de <?= $kpi_conversion['total'] ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila de Gráficas y Tablas -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Columna para la Gráfica de Ingresos (7/12) -->
            <div class="lg:col-span-7">
                <div class="bg-white p-6 rounded-xl shadow-lg h-full">
                    <h5 class="text-lg font-semibold mb-4 border-b pb-2">Ingresos Confirmados (Últimos 6 Meses)</h5>
                    <div class="relative h-80">
                        <canvas id="graficaIngresos"></canvas>
                    </div>
                </div>
            </div>

            <!-- Columna para las Últimas Cotizaciones (5/12) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-xl shadow-lg h-full">
                    <div class="p-6 border-b">
                        <h5 class="text-lg font-semibold mb-0">Últimas Cotizaciones</h5>
                    </div>
                    
                    <?php if (!empty($ultimas_cotizaciones)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($ultimas_cotizaciones as $cotizacion): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?= esc($cotizacion['cliente_nombre']) ?></div>
                                                <div class="text-xs text-gray-500"><?= date('d M Y', strtotime($cotizacion['fecha_evento'])) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                    // CRÍTICO: Obtener el valor interno en minúsculas
                                                    $status_value = strtolower($cotizacion['status']); 
                                                    
                                                    // CRÍTICO: Traducir el valor a la etiqueta de UI
                                                    $status_label = $uiLabels[$status_value] ?? $cotizacion['status']; 
                                                    
                                                    $badge_class = 'bg-gray-500';
                                                    switch ($status_value) { // Usar el valor interno para las clases
                                                        case 'pendiente': $badge_class = 'bg-yellow-500 text-yellow-900'; break;
                                                        case 'confirmado': $badge_class = 'bg-green-500'; break;
                                                        case 'pagado': $badge_class = 'bg-blue-500'; break;
                                                        case 'cancelado': $badge_class = 'bg-red-500'; break;
                                                        default: $badge_class = 'bg-indigo-500'; break;
                                                    }
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badge_class ?> text-white">
                                                    <?= esc($status_label) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="<?= site_url(route_to('panel.cotizaciones.view', $cotizacion['id_cotizacion'])) ?>" class="text-indigo-600 hover:text-indigo-900" title="Ver Detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-6">
                            <p class="text-gray-500 mb-0">No hay cotizaciones recientes para mostrar.</p>
                        </div>
                    <?php endif; ?>
                    <div class="p-4 text-center border-t">
                         <a href="<?= site_url(route_to('panel.cotizaciones.index')) ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">Ver todas las cotizaciones</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila de Gráficas de Distribución -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            
            <!-- Gráfica: Canal de Origen -->
            <div class="bg-white p-6 rounded-xl shadow-lg h-full">
                <h5 class="text-lg font-semibold mb-4 border-b pb-2">Canal de Origen de Clientes</h5>
                <div class="flex items-center justify-center h-80">
                    <div class="relative w-64 h-64">
                        <canvas id="graficaCanalOrigen"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfica: Tipo de Evento -->
            <div class="bg-white p-6 rounded-xl shadow-lg h-full">
                <h5 class="text-lg font-semibold mb-4 border-b pb-2">Distribución por Tipo de Evento</h5>
                <div class="flex items-center justify-center h-80">
                    <div class="relative w-64 h-64">
                        <canvas id="graficaTipoEvento"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Bloque de Scripts (Gráficas) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- GRÁFICA DE INGRESOS ---
        const datosGrafica = <?= $grafica_ingresos_json ?>;
        const ctx = document.getElementById('graficaIngresos').getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');   
        gradient.addColorStop(1, 'rgba(54, 162, 235, 0.2)');

        if (datosGrafica.data.some(d => d > 0)) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: datosGrafica.labels,
                    datasets: [{
                        label: 'Ingresos Mensuales',
                        data: datosGrafica.data,
                        backgroundColor: gradient,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        hoverBackgroundColor: 'rgba(54, 162, 235, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + new Intl.NumberFormat('es-MX').format(value);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                                }
                            }
                        }
                    }
                }
            });
        } else {
            ctx.font = "16px Arial";
            ctx.fillStyle = "#6b7280";
            ctx.textAlign = "center";
            ctx.fillText("No hay datos de ingresos para mostrar.", 200, 150);
        }

        // --- GRÁFICA DE DONA: CANAL DE ORIGEN ---
        const rawCanalOrigen = <?= json_encode($stats_canal_origen) ?>;
        const uiLabels = <?= json_encode($uiLabels) ?>;

        const canalLabels = rawCanalOrigen.labels.map(label => uiLabels[label] || label);
        const canalData = rawCanalOrigen.data;

        const ctxCanal = document.getElementById('graficaCanalOrigen');

        if (ctxCanal && canalData.some(d => d > 0)) {
            new Chart(ctxCanal.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: canalLabels,
                    datasets: [{
                        data: canalData,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#60616f'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.chart.getDatasetMeta(0).total;
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                },
            });
        } else if (ctxCanal) {
            ctxCanal.getContext('2d').font = "16px Arial";
            ctxCanal.getContext('2d').fillStyle = "#6b7280";
            ctxCanal.getContext('2d').textAlign = "center";
            ctxCanal.getContext('2d').fillText("No hay datos de origen para mostrar.", 125, 125);
        }

        // --- GRÁFICA DE DONA: TIPO DE EVENTO ---
        const rawTipoEvento = <?= json_encode($stats_tipo_evento) ?>;
        // Mapear los valores internos a etiquetas de UI
        const eventoLabels = rawTipoEvento.labels.map(label => uiLabels[label] || label);
        const eventoData = rawTipoEvento.data;

        const ctxTipoEvento = document.getElementById('graficaTipoEvento');

        if (ctxTipoEvento && eventoData.some(d => d > 0)) {
            new Chart(ctxTipoEvento.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: eventoLabels,
                    datasets: [{
                        data: eventoData,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.chart.getDatasetMeta(0).total;
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                },
            });
        } else if (ctxTipoEvento) {
            ctxTipoEvento.getContext('2d').font = "16px Arial";
            ctxTipoEvento.getContext('2d').fillStyle = "#6b7280";
            ctxTipoEvento.getContext('2d').textAlign = "center";
            ctxTipoEvento.getContext('2d').fillText("No hay datos de eventos para mostrar.", 125, 125);
        }

    });
</script>

</body>
</html>