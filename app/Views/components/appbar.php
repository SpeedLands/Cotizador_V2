<?php
// Define la URL base para las rutas si es necesario
$baseURL = base_url();

// Obtener el estado de autenticación (pasado desde el controlador)
$isLoggedIn = $isLoggedIn ?? false; 

// Define el enlace activo.
$navLinks = $navLinks ?? [];

// Opcionalmente, puedes recibir el nombre de la página actual desde el controlador
$currentPage = $currentPage ?? 'Dashboard'; 
?>

<!-- Contenedor principal de la AppBar (fijo en la parte superior y con sombra) -->
<nav class="bg-white shadow-md fixed w-full top-0 z-10">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <!-- 1. Sección de Logo/Marca (Izquierda) -->
            <div class="flex items-center">
                <a href="<?= $baseURL ?>">
                    <img 
                        src="<?= $baseURL ?>/logo.svg" 
                        alt="mapolato logo" 
                        class="block h-16 w-auto"
                    >
                </a>
            </div>

            <!-- 2. Sección de Enlaces de Navegación (Centro) -->
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8 items-center">
                <?php if ($isLoggedIn): // Mostrar enlaces solo si está logueado ?>
                    <?php foreach ($navLinks as $label => $link): ?>
                        <?php
                            // Clases condicionales para el enlace activo
                            $isActive = $label == $currentPage;
                            $linkClasses = $isActive
                                ? 'text-black inline-flex items-center px-1 pt-1 border-b-2 border-pink-600 text-sm font-medium transition-colors duration-200'
                                : 'text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium transition-colors duration-200';
                        ?>
                        <a href="<?= $link['url'] ?>" class="<?= $linkClasses ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- 3. Sección de Usuario/Perfil (Derecha) -->
            <div class="flex items-center">
                <div class="ml-4 flex items-center md:ml-6">
                    <?php if ($isLoggedIn): ?>
                        <!-- Botón de Perfil/Iniciales -->
                        <span class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-sm font-semibold text-pink-800 cursor-pointer hover:bg-pink-200 transition duration-150">
                            AD
                        </span>
                        
                        <!-- Botón de Cerrar Sesión (Logout) -->
                        <a href="<?= base_url('/admin/logout') ?>" class="ml-4 bg-red-500 text-white font-semibold py-1 px-3 rounded-lg text-sm hover:bg-red-600 transition duration-150">
                            Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <!-- Botón de Login (Si no está logueado) -->
                        <a href="<?= base_url('/admin') ?>" class="ml-4 bg-indigo-600 text-white font-semibold py-1 px-3 rounded-lg text-sm hover:bg-indigo-700 transition duration-150">
                            Admin Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</nav>