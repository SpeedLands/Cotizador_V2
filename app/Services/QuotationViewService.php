<?php

namespace App\Services;

use App\Models\MenuItemModel;
use App\Models\QuotationModel;

/**
 * Servicio para preparar los datos para las vistas de cotizaciones.
 */
class QuotationViewService
{
    private QuotationModel $quotationModel;
    private MenuItemModel $menuItemModel;

    public function __construct()
    {
        $this->quotationModel = new QuotationModel();
        $this->menuItemModel = new MenuItemModel();
    }

    /**
     * Obtiene y procesa todos los datos necesarios para la vista de detalle de una cotización.
     *
     * @param int $id_cotizacion
     * @return array
     */
    public function getDataForQuotationDetail(int $id_cotizacion): array
    {
        $cotizacion = $this->quotationModel->find($id_cotizacion);
        if (!$cotizacion) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $baseURL = base_url('panel');

        $navLinks = [
            'Dashboard' => ['url' => $baseURL . '/dashboard', 'active' => false],
            'Cotizaciones' => ['url' => '#', 'active' => true],
            'Calendario' => ['url' => '#', 'active' => false],
            'Servicios' => ['url' => '#', 'active' => false],
        ];

        $uiLabels = [
            'social' => 'Evento Social',
            'empresarial' => 'Evento Empresarial',
            'otro' => 'Otro Evento',
            'recomendacion' => 'Recomendación',
            'redes' => 'Redes Sociales',
            'restaurante' => 'Por el Restaurante',
            'hombres' => 'Hombres',
            'mujeres' => 'Mujeres',
            'ninos' => 'Niños',
            'mixto' => 'Mixto',
            'si' => 'Sí',
            'no' => 'No',
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado',
            'cancelado' => 'Cancelado',
            'pagado' => 'Pagado',
            'contactado' => 'Contactado',
            'en_revision' => 'En Revisión',
        ];

        // Procesar y agrupar los servicios seleccionados
        $serviciosAgrupados = $this->groupSelectedServices($cotizacion['detalle_menu']);

        return [
            'titulo' => 'Detalle de Cotización',
            'cotizacion' => $cotizacion,
            'servicios_seleccionados' => $serviciosAgrupados,
            'uiLabels' => $uiLabels,
            'navLinks' => $navLinks,
            'baseURL' => $baseURL,
            'isLoggedIn' => session()->get('isLoggedIn') ?? false,
        ];
    }

    /**
     * Agrupa los ítems de menú seleccionados en una estructura jerárquica para la vista.
     *
     * @param array $menuSeleccionado
     * @return array
     */
    private function groupSelectedServices(array $menuSeleccionado): array
    {
        if (empty($menuSeleccionado)) {
            return [];
        }

        $allMenuItems = $this->menuItemModel->findAll();
        $menuMap = [];
        foreach ($allMenuItems as $item) {
            $menuMap[$item['id_item']] = $item;
        }

        $serviciosAgrupados = [];
        foreach ($menuSeleccionado as $itemId => $cantidad) {
            if ($cantidad <= 0 || !isset($menuMap[$itemId])) continue;

            $item = $menuMap[$itemId];
            $hasChildren = $this->menuItemModel->where('parent_id', $itemId)->countAllResults() > 0;

            if ($item['parent_id'] === null || $hasChildren) {
                continue; // Ignorar categorías raíz y contenedores intermedios
            }

            $path = [];
            $currentId = $itemId;
            while ($currentId !== null && isset($menuMap[$currentId])) {
                array_unshift($path, $menuMap[$currentId]); // Construir la ruta en orden
                $currentId = $menuMap[$currentId]['parent_id'];
            }

            $rootName = $path[0]['nombre_item'] ?? 'Otros';
            $serviciosAgrupados[$rootName][] = [
                'path' => $path, // CRÍTICO: Añadir la ruta jerárquica para la vista.
                'nombre' => $item['nombre_item'],
                'cantidad' => ($item['tipo_ui'] === 'quantity') ? $cantidad : 'Sí',
                'precio' => $item['precio_unitario'],
                'subtotal' => $item['precio_unitario'] * $cantidad,
            ];
        }

        return $serviciosAgrupados;
    }
}