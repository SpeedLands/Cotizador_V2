<?php

namespace App\Services;

use App\Models\MenuItemModel;
use App\Models\QuotationModel;

class QuotationViewService
{
    private QuotationModel $quotationModel;
    private MenuItemModel $menuItemModel;
    private MenuService $menuService;

    public function __construct()
    {
        $this->quotationModel = new QuotationModel();
        $this->menuItemModel = new MenuItemModel();
        $this->menuService = service('menuService');
    }

    public function getDataForQuotationDetail(int $id_cotizacion): array
    {
        $cotizacion = $this->quotationModel->find($id_cotizacion);
        if (!$cotizacion) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $baseURL = base_url('panel');
        $uiLabels = [
            'social' => 'Evento Social', 'empresarial' => 'Evento Empresarial', 'otro' => 'Otro Evento',
            'recomendacion' => 'Recomendación', 'redes' => 'Redes Sociales', 'restaurante' => 'Por el Restaurante',
            'hombres' => 'Hombres', 'mujeres' => 'Mujeres', 'ninos' => 'Niños', 'mixto' => 'Mixto',
            'si' => 'Sí', 'no' => 'No', 'pendiente' => 'Pendiente', 'confirmado' => 'Confirmado',
            'cancelado' => 'Cancelado', 'pagado' => 'Pagado', 'contactado' => 'Contactado', 'en_revision' => 'En Revisión',
            'buffet_self_service' => 'Buffet Self-Service', 'buffet_asistido' => 'Buffet Asistido por Personal', 'servicio_a_la_mesa' => 'Servicio a la Mesa',
        ];

        $serviciosAgrupados = $this->groupSelectedServices($cotizacion);

        return [
            'titulo' => 'Detalle de Cotización',
            'cotizacion' => $cotizacion,
            'servicios_seleccionados' => $serviciosAgrupados,
            'uiLabels' => $uiLabels,
            'baseURL' => $baseURL,
            'isLoggedIn' => session()->get('isLoggedIn') ?? false,
        ];
    }

    private function groupSelectedServices(array $cotizacion): array
    {
        $menuData = $cotizacion['detalle_menu'] ?? ['selection' => [], 'quantities' => []];
        $menuSeleccionado = $menuData['selection'] ?? [];
        $menuQuantities = $menuData['quantities'] ?? [];

        if (empty($menuSeleccionado)) {
            return [];
        }

        // Use the corrected billable item extraction from the 'selection' part.
        $selectedItemIds = $this->extractBillableItemIds($menuSeleccionado);
        if (empty($selectedItemIds)) {
            return [];
        }

        // Fetch all necessary items and their ancestors to build paths.
        $fullItemTreeIds = $this->menuService->getItemIdsWithAncestors($selectedItemIds);
        $allItems = $this->menuService->getItemsByIds($fullItemTreeIds);
        $itemsById = array_column($allItems, null, 'id_item');

        $numInvitados = (int) $cotizacion['num_invitados'];
        $flatSummary = [];

        foreach ($selectedItemIds as $itemId) {
            if (!isset($itemsById[$itemId])) continue;

            $item = $itemsById[$itemId];
            $baseQuantity = $item['per_person'] ? $numInvitados : 1;

            // This logic now uses the saved quantities to find the correct quantity.
            $mainDishQty = $this->findMainDishQuantity($itemId, $menuSeleccionado, $itemsById, $menuQuantities);

            // The quantity for any sub-option is inherited from its main dish.
            $finalQuantity = $mainDishQty ?? $baseQuantity;

            // Build the full path for context (e.g., "Category > Item > Option").
            $path = [];
            $currentId = $itemId;
            while ($currentId !== null && isset($itemsById[$currentId])) {
                // Prepend to build the path from root to leaf.
                array_unshift($path, $itemsById[$currentId]['nombre_item']);
                $currentId = $itemsById[$currentId]['parent_id'];
            }
            // The very first element is the root node, which we can skip for a cleaner display.
            if (count($path) > 1) {
                array_shift($path);
            }

            $flatSummary[] = [
                'full_path' => implode(' > ', $path),
                'nombre' => $item['nombre_item'],
                'cantidad' => $finalQuantity,
                'precio_unitario' => $item['precio_unitario'],
            ];
        }

        // Group by the first part of the path (the category).
        $serviciosAgrupados = [];
        foreach ($flatSummary as $item) {
            $category = strtok($item['full_path'], ' > ');
            $serviciosAgrupados[$category][] = $item;
        }

        return $serviciosAgrupados;
    }

    private function findMainDishQuantity($itemId, $menuSelection, $itemsById, $menuQuantities)
    {
        $currentItem = $itemsById[$itemId] ?? null;

        // Base case: The quantity is explicitly saved for this item.
        if (isset($menuQuantities[$itemId])) {
            return (int)$menuQuantities[$itemId];
        }

        // Recursive step: Traverse up the hierarchy to find the parent's quantity.
        if ($currentItem && $currentItem['parent_id']) {
            return $this->findMainDishQuantity($currentItem['parent_id'], $menuSelection, $itemsById, $menuQuantities);
        }

        return null; // Return null if no quantity is found up the chain.
    }

    private function extractBillableItemIds(array $selection): array
    {
        $itemIds = [];
        $mainDishIds = array_keys($selection);
        foreach ($mainDishIds as $id) {
            if (is_numeric($id)) $itemIds[] = (int)$id;
        }
        array_walk_recursive($selection, function ($value) use (&$itemIds) {
            if (is_numeric($value)) $itemIds[] = (int)$value;
        });
        return array_unique($itemIds);
    }
}