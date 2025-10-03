<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Cotización - mapolato</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-2xl text-center">
        <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <h1 class="text-3xl font-bold mt-4 mb-2 text-green-700">¡Cotización Enviada con Éxito!</h1>
        <p class="text-gray-600 mb-6">Tu solicitud #<?= esc($quote['id_cotizacion']) ?> ha sido guardada. Hemos eliminado la notificación por correo electrónico al cliente.</p>

        <!-- CTA PRIMORDIAL: WhatsApp Deep Link [cite: V.B] -->
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Siguiente Paso: Confirma por WhatsApp</h2>
        <p class="text-sm text-gray-500 mb-6">Haz clic en el botón para iniciar una conversación directa con nuestro equipo y finalizar los detalles de tu cotización.</p>

        <a href="<?= esc($whatsapp_link) ?>" target="_blank" class="inline-flex items-center justify-center w-full py-4 px-6 border border-transparent rounded-xl shadow-lg text-xl font-extrabold text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-green-400 transition duration-150">
            <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.24-1.37c1.45.79 3.08 1.21 4.8.12 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm3.17 13.56c-.14.23-.84.58-1.18.63-.3.05-.67.08-1.04.08-.4 0-1.03-.15-1.5-.62-.56-.58-1.42-1.73-1.42-3.32 0-1.59.86-2.45 1.18-2.77.32-.32.69-.4.93-.4.24 0 .48.05.67.45.2.4.67 1.62.73 1.73.06.11.1.23 0 .35-.06.11-.4.45-.56.61-.16.16-.3.32-.45.48-.14.14-.3.3-.14.56.16.26.58.88.93 1.23.4.4.79.56 1.04.67.24.11.38.08.52.05.14-.03.45-.17.58-.3.14-.14.28-.3.45-.4.16-.1.32-.14.52-.08.2.06 1.23.58 1.42.67.2.1.35.16.4.23.05.07.05.4.05.45 0 .05-.05.1-.1.15z"/></svg>
            Confirmar Cotización por WhatsApp
        </a>
    </div>
</body>
</html>