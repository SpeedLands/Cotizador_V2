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
            <tr>
                <th>Modalidad de Servicio</th>
                <td><?= esc($cotizacion['modalidad_servicio_label']) ?></td>
            </tr>
        </table>

        <h2>Servicios Seleccionados</h2>
        <?php if (!empty($menu_details)): ?>
            <?php foreach ($menu_details as $item): ?>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th style="background-color: #e3f2fd;"><?= esc($item['nombre_item']) ?></th>
                            <th style="background-color: #e3f2fd; text-align: right;"><?= $item['quantity'] ? 'Cantidad: ' . esc($item['quantity']) : '' ?></th>
                        </tr>
                    </thead>
                    <?php if (!empty($item['sub_options'])): ?>
                    <tbody>
                        <?php foreach ($item['sub_options'] as $sub): ?>
                        <tr>
                            <td style="padding-left: 20px;">- <?= esc($sub['nombre_item']) ?></td>
                            <td style="text-align: right;"><?= $sub['quantity'] && $sub['quantity'] != $item['quantity'] ? 'Cant: ' . esc($sub['quantity']) : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php endif; ?>
                </table>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No se seleccionaron servicios en esta cotización.</p>
        <?php endif; ?>

        <h2>Total Estimado</h2>
        <p>$<?= number_format($cotizacion['total_estimado'], 2) ?></p>

        <p><strong>Aviso Importante:</strong> Este evento no está confirmado hasta que se complete el proceso de confirmación por WhatsApp. El precio mostrado es un estimado y está sujeto a cambios.</p>
    </div>
</body>
</html>