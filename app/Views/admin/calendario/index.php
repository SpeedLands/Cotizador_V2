<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Eventos - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- FullCalendar JS (usando ESM para seguir las mejores prácticas) -->
    <script type="importmap">
    {
        "imports": {
            "@fullcalendar/core": "https://cdn.skypack.dev/@fullcalendar/core@6.1.15",
            "@fullcalendar/daygrid": "https://cdn.skypack.dev/@fullcalendar/daygrid@6.1.15",
            "@fullcalendar/interaction": "https://cdn.skypack.dev/@fullcalendar/interaction@6.1.15",
            "@fullcalendar/core/locales-all": "https://cdn.skypack.dev/@fullcalendar/core@6.1.15/locales-all"
        }
    }
    </script>
</head>
<body class="bg-gray-100 pt-16">

    <?= view('components/appbar', [ 'isLoggedIn' => true ]) ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Calendario de Eventos</h1>
            <p class="text-gray-500">Visualiza todas las cotizaciones programadas.</p>
        </div>

        <div class="relative bg-white p-6 rounded-xl shadow-lg">
            <!-- Indicador de Carga (Spinner) -->
            <div id="loading-spinner" class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm hidden">
                <svg class="w-12 h-12 text-blue-600 animate-spin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2.99988V5.99988" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 18V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.63604 5.63623L7.75736 7.75755" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16.2426 16.2427L18.364 18.3641" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18 12H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.63604 18.3641L7.75736 16.2427" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16.2426 7.75755L18.364 5.63623" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <!-- Contenedor del Calendario -->
            <div id='calendar'></div>
        </div>

        <!-- Leyenda de Colores -->
        <div class="mt-6 bg-white p-4 rounded-xl shadow-lg">
            <h4 class="text-md font-semibold mb-3">Leyenda de Estados</h4>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center"><span class="w-4 h-4 rounded-full mr-2" style="background-color: #f59e0b;"></span>Pendiente</div>
                <div class="flex items-center"><span class="w-4 h-4 rounded-full mr-2" style="background-color: #10b981;"></span>Confirmado</div>
                <div class="flex items-center"><span class="w-4 h-4 rounded-full mr-2" style="background-color: #3b82f6;"></span>Pagado</div>
                <div class="flex items-center"><span class="w-4 h-4 rounded-full mr-2" style="background-color: #ef4444;"></span>Cancelado</div>
                <div class="flex items-center"><span class="w-4 h-4 rounded-full mr-2" style="background-color: #6366f1;"></span>Contactado</div>
                <div class="flex items-center"><span class="w-4 h-4 rounded-full mr-2" style="background-color: #6b7280;"></span>En Revisión</div>
            </div>
        </div>

    </div>

    <script type="module">
        import { Calendar } from '@fullcalendar/core';
        import dayGridPlugin from '@fullcalendar/daygrid';
        import interactionPlugin from '@fullcalendar/interaction';
        import allLocales from '@fullcalendar/core/locales-all';

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const spinnerEl = document.getElementById('loading-spinner');

            const calendar = new Calendar(calendarEl, {
                plugins: [ dayGridPlugin, interactionPlugin ],
                locales: allLocales,
                locale: 'es', // Traducción al español
                initialView: 'dayGridMonth',
                
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                
                // Fuente de eventos AJAX
                events: {
                    url: '<?= site_url(route_to('panel.calendario.eventos')) ?>',
                    method: 'GET',
                },
                
                // Manejo del indicador de carga
                loading: function(isLoading) {
                    if (isLoading) {
                        spinnerEl.classList.remove('hidden');
                    } else {
                        setTimeout(() => spinnerEl.classList.add('hidden'), 200);
                    }
                },

                dayMaxEvents: true, // Habilita el enlace "+X más"
            });

            calendar.render();
        });
    </script>

</body>
</html>
