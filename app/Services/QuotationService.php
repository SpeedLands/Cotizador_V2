<?php

namespace App\Services;

use App\Models\AdminNotificationModel;
use App\Models\MenuItemModel;
use App\Models\QuotationModel;

class QuotationService
{
    private QuotationModel $quotationModel;
    private AdminNotificationModel $notificationModel;
    private MenuItemModel $menuItemModel;

    public function __construct(QuotationModel $quotationModel = null, AdminNotificationModel $notificationModel = null, MenuItemModel $menuItemModel = null)
    {
        $this->quotationModel = $quotationModel ?? new QuotationModel();
        $this->notificationModel = $notificationModel ?? new AdminNotificationModel();
        $this->menuItemModel = $menuItemModel ?? new MenuItemModel();
    }

    public function getValidationRules(): array
    {
        return [
            'cliente_nombre'    => 'required|max_length[100]',
            'cliente_whatsapp'  => 'required|regex_match[/^\+?\d{10,20}$/]',
            'num_invitados'     => 'required|integer|greater_than_equal_to[15]',
            'fecha_evento'      => 'required|valid_date',
            'menu_selection'    => 'permit_empty',
            'tipo_evento'       => 'required|in_list[social,empresarial,otro]',
            'hora_inicio'       => 'required',
            'hora_consumo'      => 'required',
            'hora_finalizacion' => 'required',
            'direccion_evento'  => 'required|max_length[500]',
            'mesa_mantel'       => 'required|in_list[si,no,otro]',
            'modalidad_servicio'=> 'required|in_list[buffet_self_service,buffet_asistido,servicio_a_la_mesa]',
            'dificultad_montaje'=> 'required|max_length[500]',
            'como_nos_conocio'  => 'required',
            'tipo_consumidores' => 'required',
            'nombre_empresa'    => 'permit_empty|max_length[150]',
        ];
    }

    public function createQuotation(array $data)
    {
        $menuSelection = $data['menu_selection'] ?? [];
        $menuQuantities = $data['menu_quantities'] ?? [];
        $numInvitados = (int)($data['num_invitados'] ?? 0);
        $totalEstimado = $this->calculateTotal($menuSelection, $menuQuantities, $numInvitados);

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
            'modalidad_servicio'=> $data['modalidad_servicio'],
            'mesa_mantel'       => $data['mesa_mantel'],
            'mesa_mantel_especificar' => $data['mesa_mantel_especificar'] ?? null,
            'dificultad_montaje'=> $data['dificultad_montaje'],
            'como_nos_conocio'  => $data['como_nos_conocio'],
            'tipo_consumidores' => $data['tipo_consumidores'],
            'restricciones_alimenticias' => $data['restricciones_alimenticias'] ?? null,
            'rango_presupuesto' => $data['rango_presupuesto'] ?? null,
            'detalle_menu'      => [
                'selection' => $menuSelection,
                'quantities' => $menuQuantities,
            ],
            'notas_adicionales' => $data['notas_adicionales'] ?? null,
            'download_token'    => bin2hex(random_bytes(32)),
            'total_estimado'    => $totalEstimado,
            'status'            => 'pendiente',
        ];

        $id_cotizacion = $this->quotationModel->insert($insertData, true);

        if ($id_cotizacion) {
            $this->notificationModel->insert([
                'quotation_id' => $id_cotizacion,
                'message'      => "Nueva cotización recibida: #{$id_cotizacion} de {$data['cliente_nombre']}",
                'is_read'      => 0,
            ]);
        }
        return $id_cotizacion;
    }

    private function calculateTotal(array $menuSelectionData, array $menuQuantities, int $numInvitados): float
    {
        if (empty($menuSelectionData) || $numInvitados <= 0) {
            return 0.00;
        }

        $menuService = service('menuService');

        // This new method correctly extracts only the billable item IDs.
        $selectedItemIds = $this->extractBillableItemIds($menuSelectionData);

        if (empty($selectedItemIds)) {
            return 0.00;
        }

        // We still need all ancestors to correctly determine quantities.
        $fullItemTreeIds = $menuService->getItemIdsWithAncestors($selectedItemIds);
        $allItems = $menuService->getItemsByIds($fullItemTreeIds);
        $itemsById = array_column($allItems, null, 'id_item');

        $total = 0.00;

        // Iterate only over the items that should be part of the total.
        foreach ($selectedItemIds as $itemId) {
            if (!isset($itemsById[$itemId])) continue;

            $item = $itemsById[$itemId];
            $baseQuantity = 1;

            // Use the same robust quantity-finding logic as the AJAX endpoint.
            $mainDishQty = $this->findMainDishQuantity($itemId, $menuQuantities, $itemsById);

            if ($mainDishQty !== null) {
                $baseQuantity = $mainDishQty;
            } elseif ($item['per_person']) {
                $baseQuantity = $numInvitados;
            }

            $subtotal = $item['precio_unitario'] * $baseQuantity;
            $total += $subtotal;
        }

        return $total;
    }

    private function findMainDishQuantity($itemId, $mainDishQuantities, $itemsById)
    {
        if (isset($mainDishQuantities[$itemId])) {
            return (int)$mainDishQuantities[$itemId];
        }

        $currentItem = $itemsById[$itemId] ?? null;
        if ($currentItem && $currentItem['parent_id']) {
            return $this->findMainDishQuantity($currentItem['parent_id'], $mainDishQuantities, $itemsById);
        }

        return null;
    }

    /**
     * Extracts all billable item IDs from the nested menu selection array.
     *
     * This includes:
     * 1. The main dish IDs (top-level keys), which may have a base price.
     * 2. The final selected option IDs (leaf node values).
     * It correctly ignores intermediate step IDs.
     */
    private function extractBillableItemIds(array $selection): array
    {
        $itemIds = [];

        // Add all top-level keys, which represent the main dishes or simple items.
        $mainDishIds = array_keys($selection);
        foreach ($mainDishIds as $id) {
            if (is_numeric($id)) {
                $itemIds[] = (int)$id;
            }
        }

        // Recursively find all leaf-node values, which are the final selections.
        array_walk_recursive($selection, function ($value, $key) use (&$itemIds) {
            if (is_numeric($value)) {
                $itemIds[] = (int)$value;
            }
        });

        return array_unique($itemIds);
    }
}