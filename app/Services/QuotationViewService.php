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
        $menuSeleccionado = $cotizacion['detalle_menu'] ?? [];
        if (empty($menuSeleccionado)) {
            return [];
        }

        $selectedItemIds = $this->flattenMenuSelectionIds($menuSeleccionado);
        if (empty($selectedItemIds)) {
            return [];
        }

        $fullItemTreeIds = $this->menuService->getItemIdsWithAncestors($selectedItemIds);
        $allItems = $this->menuService->getItemsByIds($fullItemTreeIds);
        $itemsById = array_column($allItems, null, 'id_item');

        // Asumimos que las cantidades están en el campo 'detalle_menu' de una forma que el frontend nos daría.
        // Como no lo tenemos, extraemos las cantidades de los platos principales del propio `detalle_menu`
        $menuQuantities = $this->extractQuantities($menuSeleccionado);

        $serviciosAgrupados = [];

        foreach ($selectedItemIds as $itemId) {
            if (!isset($itemsById[$itemId])) continue;

            $item = $itemsById[$itemId];

            // Ignorar ítems que son solo contenedores y no una selección final cuantificable.
            if ($this->menuService->hasActiveChildren($item['id_item']) && $item['tipo_ui'] !== 'quantity' && $item['precio_unitario'] == 0) {
                continue;
            }

            $path = [];
            $currentId = $itemId;
            while ($currentId !== null && isset($itemsById[$currentId])) {
                array_unshift($path, $itemsById[$currentId]);
                $currentId = $itemsById[$currentId]['parent_id'];
            }

            $baseQuantity = 1;
            $mainDishQty = $this->findMainDishQuantity($itemId, $menuQuantities, $itemsById);

            if ($mainDishQty !== null) {
                $baseQuantity = $mainDishQty;
            } elseif ($item['per_person']) {
                $baseQuantity = (int)$cotizacion['num_invitados'];
            }

            $rootName = $path[0]['nombre_item'] ?? 'Otros';
            $pathNames = array_map(fn($p) => $p['nombre_item'], array_slice($path, 1));

            $serviciosAgrupados[$rootName][] = [
                'full_path' => implode(' > ', $pathNames),
                'nombre' => $item['nombre_item'],
                'cantidad' => $baseQuantity,
                'precio_unitario' => $item['precio_unitario'],
            ];
        }

        return $serviciosAgrupados;
    }

    private function flattenMenuSelectionIds(array $selection): array
    {
        $result = [];
        foreach ($selection as $key => $value) {
            if (is_numeric($key)) $result[] = (int)$key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenMenuSelectionIds($value));
            } elseif (is_numeric($value)) {
                $result[] = (int)$value;
            }
        }
        return array_unique($result);
    }

    private function extractQuantities(array $selection) : array
    {
        $quantities = [];
        foreach($selection as $key => $value) {
            if (is_numeric($key) && !is_array($value)) {
                $quantities[$key] = $value;
            }
        }
        return $quantities;
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
}