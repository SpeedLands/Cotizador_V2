<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
    <?= view('components/appbar', [
        'currentPage' => $currentPage ?? 'Dashboard', 
        'navLinks' => $navLinks,
    ]) ?>
    <main class="py-16 px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-indigo-700 mb-6">Panel de Administración</h1>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <p class="text-lg">¡Bienvenido, Administrador!</p>
            <p class="mt-4">Aquí irán los KPIs y reportes de cotizaciones que implementamos en el modelo.</p>
            
            <a href="<?= base_url('admin/logout') ?>" class="mt-6 inline-block bg-red-500 text-white font-semibold py-2 px-4 rounded hover:bg-red-600">Cerrar Sesión</a>
        </div>
    </main>
</body>
</html>