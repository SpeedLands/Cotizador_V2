<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\QuotationService;
use CodeIgniter\API\ResponseTrait;

class PublicController extends BaseController
{
    use ResponseTrait;

    /**
     * Endpoint público para crear una nueva cotización.
     * POST /api/v1/public/quotations
     */
    public function createQuotation()
    {
    $quotationService = service('quotationService');
    $rules = $quotationService->getValidationRules();

        // La API espera datos JSON
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $id = $quotationService->createQuotation($data);

        if ($id) {
            return $this->respondCreated(['id' => $id, 'message' => 'Cotización creada exitosamente.'], 'Cotización creada');
        }

        return $this->failServerError('No se pudo crear la cotización.');
    }

    /**
     * Endpoint público para obtener un historial de cotizaciones basado en una lista de IDs.
     * POST /api/v1/public/quotations/history
     */
    public function getHistory()
    {
        $data = $this->request->getJSON(true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return $this->respond([]); // Devolver un array vacío si no se envían IDs
        }

        // Sanitizar los IDs para asegurarse de que son números
        $sanitizedIds = array_filter($ids, 'is_numeric');

        if (empty($sanitizedIds)) {
            return $this->respond([]);
        }

    $quotationModel = new \App\Models\QuotationModel();
    $quotations = $quotationModel->whereIn('id_cotizacion', $sanitizedIds)->orderBy('created_at', 'DESC')->findAll();

        return $this->respond($quotations);
    }

    /**
     * Endpoint público para obtener los ítems de menú de Nivel 1 (raíz).
     * GET /api/v1/public/menu/root-items
     */
    public function getRootMenuItems()
    {
        $menuService = service('menuService');
        $rootItems = $menuService->getActiveSubOptions(0); // obtener items raíz (parent_id null -> representado por 0)

        // En el caso que uses null en BD para parent_id, fallback a consulta directa
        if (empty($rootItems)) {
            $menuModel = new \App\Models\MenuItemModel();
            $rootItems = $menuModel->where('parent_id', null)->where('activo', 1)->findAll();
        }

        foreach ($rootItems as $key => $item) {
            $rootItems[$key]['has_children'] = $menuService->getActiveSubOptions($item['id_item']) ? true : false;
        }

        return $this->respond($rootItems);
    }

    /**
     * Endpoint público para obtener los sub-ítems de un menú.
     * GET /api/v1/public/menu/sub-items/{parentId}
     */
    public function getSubMenuItems($parentId = null)
    {
        // 1. Validación del ID
        if (!is_numeric($parentId)) {
            return $this->failNotFound('ID de padre inválido o no proporcionado.');
        }

        $menuService = service('menuService');
        $children = $menuService->getActiveSubOptions((int)$parentId);

        foreach ($children as $key => $option) {
            $children[$key]['has_children'] = $menuService->getActiveSubOptions($option['id_item']) ? true : false;
        }

        return $this->respond($children);
    }

    /**
     * Endpoint público para obtener todos los servicios activos (lista plana).
     * GET /api/v1/public/services
     */
    public function getServices()
    {
        $menuService = service('menuService');
        $items = $menuService->getAllMenuItems();

        // Filtrar solo activos
        $active = array_filter($items, fn($i) => (int) ($i['activo'] ?? 0) === 1);

        return $this->respond(array_values($active));
    }

    /**
     * Endpoint público para obtener el detalle de un servicio por ID
     * GET /api/v1/public/services/{id}
     */
    public function getService($id = null)
    {
        if (!$id || !is_numeric($id)) {
            return $this->failNotFound('ID inválido.');
        }

        $menuService = service('menuService');
        $item = $menuService->getById((int)$id);

        if (!$item) {
            return $this->failNotFound('Servicio no encontrado.');
        }

        return $this->respond($item);
    }

    /**
     * Endpoint API para calcular una cotización desde selecciones de menú (JSON)
     * POST /api/v1/public/calculate-quote
     */
    public function calculateQuote()
    {
        $menuService = service('menuService');
        $payload = $this->request->getJSON(true) ?? [];

        $menuSelections = $payload['menu_selection'] ?? [];
        $numInvitados = isset($payload['num_invitados']) ? (int)$payload['num_invitados'] : 0;

        $total = 0.00;
        $summary = [];

        if (empty($menuSelections)) {
            return $this->respond([
                'success' => true,
                'total_formatted' => '$0.00',
                'summary' => [],
            ]);
        }

        $itemIds = array_keys($menuSelections);
        $items = $menuService->getItemsByIds($itemIds);

        foreach ($items as $item) {
            $itemId = $item['id_item'];
            $value = $menuSelections[$itemId] ?? null;
            $quantity = 0;
            $isPerPerson = false;

            if ($item['tipo_ui'] === 'quantity') {
                $quantity = (int) $value;
            } elseif ($item['tipo_ui'] === 'checkbox' || $item['tipo_ui'] === 'radio') {
                $quantity = 1;
                if ($item['precio_unitario'] < 50.00) {
                    $isPerPerson = true;
                }
            }

            if ($quantity > 0) {
                $finalQuantity = $isPerPerson ? $numInvitados : $quantity;
                if (strpos($item['nombre_item'], 'Modalidad:') !== false || strpos($item['nombre_item'], 'Enchufes:') !== false) {
                    $finalQuantity = 1;
                }

                $subtotal = $item['precio_unitario'] * $finalQuantity;
                $total += $subtotal;

                $summary[] = [
                    'name' => $item['nombre_item'],
                    'quantity' => $finalQuantity,
                    'subtotal_formatted' => '$' . number_format($subtotal, 2)
                ];
            }
        }

        return $this->respond([
            'success' => true,
            'total_formatted' => '$' . number_format($total, 2),
            'summary' => $summary,
        ]);
    }

    /**
     * Endpoint API para devolver fechas ocupadas (JSON)
     * GET /api/v1/public/fechas-ocupadas
     */
    public function fechasOcupadas()
    {
        $cotizacionModel = new \App\Models\QuotationModel();
        $fechasDb = $cotizacionModel->select('fecha_evento')
                                    ->where('status', 'Confirmado')
                                    ->findAll();
        $fechasOcupadas = array_column($fechasDb, 'fecha_evento');

        return $this->respond($fechasOcupadas);
    }
}