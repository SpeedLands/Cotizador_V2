<?php

namespace App\Services;

use App\Models\AdminNotificationModel;
use App\Models\MenuItemModel;
use App\Models\QuotationModel;

/**
 * Servicio para manejar la lógica de negocio de las cotizaciones.
 */
class QuotationService
{
    private QuotationModel $quotationModel;
    private AdminNotificationModel $notificationModel;
    private MenuItemModel $menuItemModel;

    /**
     * Permitir inyección de dependencias para facilitar pruebas y registro en Services.php
     * Si no se pasan instancias, crea nuevas por compatibilidad.
     */
    public function __construct(QuotationModel $quotationModel = null, AdminNotificationModel $notificationModel = null, MenuItemModel $menuItemModel = null)
    {
        $this->quotationModel = $quotationModel ?? new QuotationModel();
        $this->notificationModel = $notificationModel ?? new AdminNotificationModel();
        $this->menuItemModel = $menuItemModel ?? new MenuItemModel();
    }

    /**
     * Devuelve las reglas de validación para el formulario de cotización.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'cliente_nombre'    => 'required|max_length[100]',
            'cliente_whatsapp'  => 'required|regex_match[/^\+?\d{10,20}$/]',
            'num_invitados'     => 'required|is_natural_no_zero',
            'fecha_evento'      => 'required|valid_date',
            'menu_selection'    => 'permit_empty', // Se permite vacío ya que se puede enviar sin seleccionar nada
            'tipo_evento'       => 'required|in_list[social,empresarial,otro]',
            'hora_inicio'       => 'required',
            'hora_consumo'      => 'required',
            'hora_finalizacion' => 'required',
            'direccion_evento'  => 'required|max_length[500]',
            'mesa_mantel'       => 'required|in_list[si,no,otro]',
            'dificultad_montaje'=> 'required|max_length[500]',
            'como_nos_conocio'  => 'required',
            'tipo_consumidores' => 'required',
            'nombre_empresa'    => 'permit_empty|max_length[150]',
        ];
    }

    /**
     * Crea una nueva cotización con los datos del formulario.
     *
     * @param array $data Datos del POST.
     * @return int|false El ID de la nueva cotización o false si falla.
     */
    public function createQuotation(array $data)
    {
        // 1. Calcular el total estimado
        $menuSelection = $data['menu_selection'] ?? [];
        $numInvitados = (int)($data['num_invitados'] ?? 0);
        $totalEstimado = $this->calculateTotal($menuSelection, $numInvitados);

        // 2. Preparar los datos para la inserción
        $insertData = [
            'cliente_nombre'    => $data['cliente_nombre'],
            'cliente_whatsapp'  => $data['cliente_whatsapp'],
            'num_invitados'     => $data['num_invitados'],
            'fecha_evento'      => $data['fecha_evento'],
            'tipo_evento'       => $data['tipo_evento'],
            'nombre_empresa'    => $data['nombre_empresa'] ?? null,
            'hora_inicio'       => $data['hora_inicio'],
            'hora_consumo'      => $data['hora_consumo'],
            'hora_finalizacion' => $data['hora_finalizacion'],
            'direccion_evento'  => $data['direccion_evento'],
            'mesa_mantel'       => $data['mesa_mantel'],
            'mesa_mantel_especificar' => $data['mesa_mantel_especificar'] ?? null,
            'dificultad_montaje'=> $data['dificultad_montaje'],
            'como_nos_conocio'  => $data['como_nos_conocio'],
            'tipo_consumidores' => $data['tipo_consumidores'],
            'restricciones_alimenticias' => $data['restricciones_alimenticias'] ?? null,
            'rango_presupuesto' => $data['rango_presupuesto'] ?? null,
            'detalle_menu'      => $menuSelection,
            'notas_adicionales' => $data['notas_adicionales'] ?? null,
            // CRÍTICO: Añadir los campos faltantes
            'total_estimado'    => $totalEstimado,
            'status'            => 'pendiente', // Estado inicial por defecto
        ];

        // 3. Insertar en la base de datos
        $id_cotizacion = $this->quotationModel->insert($insertData, true);

        $this->notificationModel->insert([
            'quotation_id' => $id_cotizacion,
            'message'      => "Nueva cotización recibida: #{$id_cotizacion} de {$data['cliente_nombre']}",
            'is_read'      => 0,
        ]);

        return $id_cotizacion;
    }

    /**
     * Actualiza una cotización existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateQuotation(int $id, array $data): bool
    {
        // 1. Calcular el nuevo total estimado
        $menuSelection = $data['menu_selection'] ?? [];
        $numInvitados = (int)($data['num_invitados'] ?? 0);
        $totalEstimado = $this->calculateTotal($menuSelection, $numInvitados);

        // 2. Preparar los datos para la actualización
        $updateData = [
            'cliente_nombre'    => $data['cliente_nombre'],
            'cliente_whatsapp'  => $data['cliente_whatsapp'],
            'num_invitados'     => $data['num_invitados'],
            'fecha_evento'      => $data['fecha_evento'],
            'tipo_evento'       => $data['tipo_evento'],
            'nombre_empresa'    => $data['nombre_empresa'] ?? null,
            'hora_inicio'       => $data['hora_inicio'],
            'hora_consumo'      => $data['hora_consumo'],
            'hora_finalizacion' => $data['hora_finalizacion'],
            'direccion_evento'  => $data['direccion_evento'],
            'mesa_mantel'       => $data['mesa_mantel'],
            'mesa_mantel_especificar' => $data['mesa_mantel_especificar'] ?? null,
            'dificultad_montaje'=> $data['dificultad_montaje'],
            'como_nos_conocio'  => $data['como_nos_conocio'],
            'tipo_consumidores' => $data['tipo_consumidores'],
            'restricciones_alimenticias' => $data['restricciones_alimenticias'] ?? null,
            'rango_presupuesto' => $data['rango_presupuesto'] ?? null,
            'detalle_menu'      => $menuSelection,
            'notas_adicionales' => $data['notas_adicionales'] ?? null,
            'total_estimado'    => $totalEstimado,
            // El status no se actualiza desde este formulario, se maneja por separado.
        ];

        // 3. Ejecutar la actualización
        $result = $this->quotationModel->update($id, $updateData);

        // Opcional: Registrar un log o notificación de la actualización
        // ...

        return $result;
    }

    /**
     * Calcula el costo total estimado basado en la selección del menú y el número de invitados.
     *
     * @param array $menuSelections
     * @param int $numInvitados
     * @return float
     */
    private function calculateTotal(array $menuSelections, int $numInvitados): float
    {
        if (empty($menuSelections) || $numInvitados <= 0) {
            return 0.00;
        }

        $total = 0.00;
        $itemIds = array_keys($menuSelections);
        $items = $this->menuItemModel->whereIn('id_item', $itemIds)->findAll();

        foreach ($items as $item) {
            $itemId = $item['id_item'];
            $value = $menuSelections[$itemId];
            $quantity = 0;
            $isPerPerson = false;

            if ($item['tipo_ui'] === 'quantity') {
                $quantity = (int) $value;
            } elseif ($item['tipo_ui'] === 'checkbox' || $item['tipo_ui'] === 'radio') {
                $quantity = 1;
                // Heurística simple: si el precio es bajo, es por persona.
                // Si es un costo fijo (ej. modalidad), no es por persona.
                if ($item['precio_unitario'] < 100.00) { // Umbral para diferenciar costo por persona de costo fijo
                    $isPerPerson = true;
                }
            }

            if ($quantity > 0) {
                $finalQuantity = $quantity;

                // Si el ítem es por persona, se multiplica por el número de invitados.
                if ($isPerPerson) {
                    $finalQuantity = $numInvitados;
                }

                // Excepción para costos fijos que no dependen de la cantidad (ej. modalidad de servicio)
                if (strpos($item['nombre_item'], 'Modalidad:') !== false || strpos($item['nombre_item'], 'Enchufes:') !== false) {
                    $finalQuantity = 1;
                }

                $subtotal = $item['precio_unitario'] * $finalQuantity;
                $total += $subtotal;
            }
        }

        return $total;
    }
}