<!-- Esta vista es el contenedor para el formulario de edición -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25.11.2/build/css/intlTelInput.css">
    <!-- ... (Estilos CSS para intl-tel-input) ... -->
</head>
<body class="bg-slate-50">
    <main class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto max-w-7xl lg:grid lg:grid-cols-3 lg:gap-12">
            <div class="lg:col-span-2">
                <div class="bg-white p-8 md:p-12 rounded-2xl shadow-xl">
                    <h1 class="text-3xl font-bold mb-6 text-indigo-700"><?= esc($titulo) ?></h1>
                    
                    <!-- Incluir el formulario principal, pasándole los datos de la cotización -->
                    <?= view('quotation/form_content', [
                        'cotizacion' => $cotizacion,
                        'menuSeleccionadoJson' => $menuSeleccionadoJson,
                        'actionUrl' => route_to('admin.cotizaciones.update'), // Nueva URL de POST
                    ]) ?>
                </div>
            </div>
            <!-- ... (Panel Lateral de Estimado Instantáneo - se puede incluir aquí si se adapta) ... -->
        </div>
    </main>
</body>
</html>