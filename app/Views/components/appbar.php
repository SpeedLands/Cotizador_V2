<?php
// --- Variables de Control para el Componente ---

// Define si los enlaces de navegación principales deben mostrarse.
// Pasa `false` desde el controlador en páginas como el login.
$showNavLinks = $showNavLinks ?? true;

// Define si el usuario ha iniciado sesión.
// Pasa `true` o `false` desde el controlador.
$isLoggedIn = $isLoggedIn ?? false;

// Obtiene el segmento de la URL actual para determinar el enlace activo.
// Esta lógica es interna al componente y no necesita pasarse como variable.
$currentUri = current_url(true);

$uri = $currentUri->getTotalSegments() >= 2 ? $currentUri->getSegment(2) : '';
?>

<nav class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- 1. Sección de Logo (Izquierda) -->
            <div class="flex items-center">
                <a href="<?= site_url('admin') ?>">
                    <img 
                        src="<?= base_url('logo.svg'); ?>" 
                        alt="Mapolato Logo" 
                        class="block h-10 w-auto"
                    >
                </a>
            </div>

            <!-- 2. Enlaces de Navegación (Centro - para pantallas grandes) -->
            <?php if ($showNavLinks): ?>
            <div class="hidden lg:flex lg:items-center lg:space-x-8">
                <a href="<?= site_url('panel/dashboard') ?>" class="<?= ($uri == 'dashboard') ? 'bg-slate-800 text-white rounded-full px-4 py-2 text-sm font-medium' : 'text-gray-500 hover:text-gray-900 text-sm font-medium' ?>">Dashboard</a>
                <a href="<?= site_url(route_to('panel.cotizaciones.index')) ?>" class="<?= ($uri == 'cotizaciones') ? 'bg-slate-800 text-white rounded-full px-4 py-2 text-sm font-medium' : 'text-gray-500 hover:text-gray-900 text-sm font-medium' ?>">Cotizaciones</a>
                <a href="<?= site_url(route_to('panel.calendario.index')) ?>" class="<?= ($uri == 'calendario') ? 'bg-slate-800 text-white rounded-full px-4 py-2 text-sm font-medium' : 'text-gray-500 hover:text-gray-900 text-sm font-medium' ?>">Calendario</a>
                <a href="<?= site_url(route_to('panel.servicios.index')) ?>" class="<?= ($uri == 'servicios') ? 'bg-slate-800 text-white rounded-full px-4 py-2 text-sm font-medium' : 'text-gray-500 hover:text-gray-900 text-sm font-medium' ?>">Servicios</a>
            </div>
            <?php endif; ?>

            <!-- 3. Sección de Usuario o Login (Derecha) -->
            <div class="flex items-center">
                <div class="hidden lg:ml-4 lg:flex lg:items-center">
                    <?php if ($isLoggedIn): ?>
                        <!-- Avatar de Usuario (Logueado) -->
                        <div class="relative ml-3" id="user-menu-container">
                             <button type="button" class="flex max-w-xs items-center rounded-full bg-slate-800 text-sm text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="sr-only">Abrir menú de usuario</span>
                                <span class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-sm font-semibold text-white">AD</span>
                            </button>
                            <!-- Menú desplegable, si lo quieres añadir en el futuro -->
                            <div class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" id="user-menu">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Mi Perfil</a>
                                <a href="<?= site_url(route_to('admin.logout')) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Cerrar Sesión</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Botón de Login (No Logueado) -->
                         <a href="<?= base_url('/admin') ?>" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg text-sm hover:bg-indigo-700 transition duration-150">
                            Admin Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botón de menú para móviles -->
            <div class="-mr-2 flex items-center lg:hidden">
                <button type="button" class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false" id="mobile-menu-button">
                    <span class="sr-only">Abrir menú principal</span>
                    <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" id="hamburger-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" id="close-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú para móviles (adaptado al tema claro) -->
    <div class="lg:hidden hidden bg-white border-t border-gray-200" id="mobile-menu">
        <?php if ($showNavLinks): ?>
        <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
            <a href="<?= site_url(route_to('panel.dashboard')) ?>" class="<?= ($uri == 'dashboard') ? 'bg-slate-100 text-slate-800' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> block rounded-md px-3 py-2 text-base font-medium">Dashboard</a>
            <a href="<?= site_url(route_to('panel.cotizaciones.index')) ?>" class="<?= ($uri == 'cotizaciones') ? 'bg-slate-100 text-slate-800' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> block rounded-md px-3 py-2 text-base font-medium">Cotizaciones</a>
            <a href="<?= site_url(route_to('panel.calendario.index')) ?>" class="<?= ($uri == 'calendario') ? 'bg-slate-100 text-slate-800' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> block rounded-md px-3 py-2 text-base font-medium">Calendario</a>
            <a href="<?= site_url(route_to('panel.servicios.index')) ?>" class="<?= ($uri == 'servicios') ? 'bg-slate-100 text-slate-800' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?> block rounded-md px-3 py-2 text-base font-medium">Servicios</a>
        </div>
        <?php endif; ?>
        
        <div class="border-t border-gray-200 pt-4 pb-3">
            <?php if ($isLoggedIn): ?>
                <div class="flex items-center px-5">
                    <div class="flex-shrink-0">
                        <span class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-sm font-semibold text-white">AD</span>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium leading-none text-gray-800">Usuario</div>
                        <div class="text-sm font-medium leading-none text-gray-500">usuario@email.com</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1 px-2">
                    <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Mi Perfil</a>
                    <a href="<?= site_url(route_to('admin.logout')) ?>" class="block rounded-md px-3 py-2 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                 <div class="px-2">
                     <a href="<?= base_url('/admin') ?>" class="block text-center w-full bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg text-base hover:bg-indigo-700 transition duration-150">
                        Admin Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- JavaScript para la interactividad de los menús -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Menú Móvil (Hamburguesa) ---
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');

        mobileMenuButton.addEventListener('click', () => {
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.classList.toggle('hidden');
            hamburgerIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        // --- Menú Desplegable de Usuario ---
        const userMenuContainer = document.getElementById('user-menu-container');
        if (userMenuContainer) { // Solo ejecutar si el menú de usuario existe (si está logueado)
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');

            userMenuButton.addEventListener('click', (event) => {
                event.stopPropagation(); // Evita que el clic se propague al documento
                const isExpanded = userMenuButton.getAttribute('aria-expanded') === 'true';
                userMenuButton.setAttribute('aria-expanded', !isExpanded);
                userMenu.classList.toggle('hidden');
            });

            // Cerrar el menú si se hace clic fuera de él
            document.addEventListener('click', function(event) {
                if (!userMenuContainer.contains(event.target)) {
                    userMenu.classList.add('hidden');
                    userMenuButton.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
</script>