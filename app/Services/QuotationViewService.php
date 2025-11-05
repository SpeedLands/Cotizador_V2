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

        // Procesar detalles del menú
        $processedMenu = $this->getProcessedMenuDetails($cotizacion);
        // Traducir modalidad de servicio
        $cotizacion['modalidad_servicio_label'] = $uiLabels[$cotizacion['modalidad_servicio']] ?? $cotizacion['modalidad_servicio'];

        return [
            'titulo' => 'Detalle de Cotización',
            'cotizacion' => $cotizacion,
            'menu_details' => $processedMenu, // Usar la nueva estructura
            'uiLabels' => $uiLabels,
            'baseURL' => $baseURL,
            'isLoggedIn' => session()->get('isLoggedIn') ?? false,
        ];
    }

    private function getProcessedMenuDetails(array $cotizacion): array
    {
        $menuData = $cotizacion['detalle_menu'] ?? ['selection' => [], 'quantities' => []];
        $selection = $menuData['selection'] ?? [];
        $quantities = $menuData['quantities'] ?? [];

        if (empty($selection) && empty($quantities)) {
            return [];
        }

        // 1. Extraer todos los IDs únicos de la selección y las cantidades.
        $itemIds = array_keys($quantities);
        array_walk_recursive($selection, function ($value) use (&$itemIds) {
            if (is_numeric($value)) {
                $itemIds[] = (int)$value;
            }
        });
        $uniqueItemIds = array_unique($itemIds);

        if (empty($uniqueItemIds)) {
            return [];
        }

        // 2. Obtener todos los items de la DB en una sola consulta
        $allItems = $this->menuService->getItemsByIds($uniqueItemIds);
        $itemsById = array_column($allItems, null, 'id_item');

        // 3. Construir la estructura jerárquica
        $structuredMenu = [];
        $processedIds = []; // Mantener un registro de los IDs ya procesados

        // Unificar `selection` y `quantities` para procesar cada ID una sola vez
        $mainItemIds = array_unique(array_merge(array_keys($selection), array_keys($quantities)));

        foreach($mainItemIds as $itemId) {
            if (!isset($itemsById[$itemId]) || in_array($itemId, $processedIds)) {
                continue;
            }

            $item = $itemsById[$itemId];
            $item['quantity'] = $quantities[$itemId] ?? null;
            $item['sub_options'] = [];

            // Procesar sub-opciones si existen en la sección 'selection'
            if (isset($selection[$itemId]) && is_array($selection[$itemId])) {
                $subItems = [];
                array_walk_recursive($selection[$itemId], function($subId) use (&$subItems, $itemsById, $quantities, $item, &$processedIds){
                     if (isset($itemsById[$subId])) {
                        $subItem = $itemsById[$subId];
                        // La cantidad de la sub-opción es la suya propia, o hereda la del padre
                        $subItem['quantity'] = $quantities[$subId] ?? $item['quantity'];
                        $subItems[] = $subItem;
                        $processedIds[] = $subId; // Marcar como procesado
                    }
                });
                $item['sub_options'] = $subItems;
            }

            // Añadir el item principal al resultado final
            $structuredMenu[] = $item;
            $processedIds[] = $itemId; // Marcar como procesado
        }

        return $structuredMenu;
    }
}