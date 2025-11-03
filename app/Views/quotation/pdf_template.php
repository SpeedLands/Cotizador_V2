<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización</title>
    <style>
        body { font-family: sans-serif; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details-table, .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td, .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px; }
        .details-table th { background-color: #f2f2f2; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cotización #<?= esc($cotizacion['id_cotizacion']) ?></h1>
        </div>

        <h2>Información del Cliente</h2>
        <table class="details-table">
            <tr>
                <th>Nombre</th>
                <td><?= esc($cotizacion['cliente_nombre']) ?></td>
            </tr>
            <tr>
                <th>WhatsApp</th>
                <td><?= esc($cotizacion['cliente_whatsapp']) ?></td>
            </tr>
        </table>

        <h2>Detalles del Evento</h2>
        <table class="details-table">
            <tr>
                <th>Tipo de Evento</th>
                <td><?= esc($cotizacion['tipo_evento']) ?></td>
            </tr>
            <tr>
                <th>Invitados</th>
                <td><?= esc($cotizacion['num_invitados']) ?></td>
            </tr>
            <tr>
                <th>Fecha</th>
                <td><?= date('d/m/Y', strtotime($cotizacion['fecha_evento'])) ?></td>
            </tr>
        </table>

        <h2>Servicios Seleccionados</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($servicios_seleccionados as $categoria => $items): ?>
                    <tr>
                        <td colspan="3"><strong><?= esc($categoria) ?></strong></td>
                    </tr>
                    <?php foreach($items as $servicio): ?>
                        <tr>
                            <td><?= esc($servicio['full_path']) ?></td>
                            <td><?= esc($servicio['cantidad']) ?></td>
                            <td>$<?= number_format($servicio['precio_unitario'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Total Estimado</h2>
        <p>$<?= number_format($cotizacion['total_estimado'], 2) ?></p>

        <p><strong>Aviso Importante:</strong> Este evento no está confirmado hasta que se complete el proceso de confirmación por WhatsApp. El precio mostrado es un estimado y está sujeto a cambios.</p>
    </div>
</body>
</html>