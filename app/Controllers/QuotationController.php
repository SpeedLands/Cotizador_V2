<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\QuotationModel;
use App\Models\MenuItemModel;
use App\Services\QuotationService;
use App\Services\QuotationViewService;

class QuotationController extends BaseController
{
    // Muestra el formulario de cotización
    public function index()
    {
        // Cargar el formulario principal que usará view_cell()
        return view('quotation/form');
    }

    public function ajax_get_menu_items()
    {
        $parentId = $this->request->getPost('parent_id');
        $mealType = $this->request->getPost('meal_type') ?? 'ambos';
        $menuService = service('menuService');

        if (!is_numeric($parentId)) {
            return $this->response->setJSON(['success' => false, 'html' => '<p>Error: ID de categoría inválido.</p>', 'token' => csrf_hash()]);
        }

        $items = $menuService->getActiveSubOptions((int)$parentId, $mealType);

        // Determinar si cada ítem tiene hijos para la lógica del frontend
        foreach ($items as $key => $item) {
            $items[$key]['has_children'] = !empty($menuService->getActiveSubOptions((int)$item['id_item']));
        }

        // Renderizar la vista parcial directamente con los datos obtenidos
        $html = view('quotation/partials/_menu_item_list', ['items' => $items]);

        return $this->response->setJSON([
            'success' => true,
            'html' => $html,
            'token' => csrf_hash()
        ]);
    }

    public function ajax_get_menu_categories()
    {
        $mealType = $this->request->getPost('meal_type') ?? 'ambos';
        $menuService = service('menuService');

        // Como ya no hay presentaciones, buscamos las categorías hijas del único nodo raíz.
        $rootItem = $menuService->getRootItem();
        if (!$rootItem) {
            return $this->response->setJSON(['success' => false, 'categories' => [], 'token' => csrf_hash()]);
        }

        $categories = $menuService->getActiveSubOptions($rootItem['id_item'], $mealType);

        return $this->response->setJSON([
            'success' => true,
            'categories' => $categories,
            'token' => csrf_hash()
        ]);
    }

    // Endpoint para peticiones AJAX de sub-opciones dependientes
    public function ajax_get_item_details()
    {
        $parent_id = $this->request->getPost('parent_id');
    $menuService = service('menuService');

        // 1. Validación básica del ID
        if (! is_numeric($parent_id)) {
            // Devolver JSON de error con el token actualizado
            return $this->response->setJSON([
                'success' => false, 
                'html' => '',
                'token' => csrf_hash()
            ]);
        }

        // 2. Consultar el modelo de menú
    $sub_options = $menuService->getActiveSubOptions((int)$parent_id);
        
        // Determinar si cada sub-opción tiene hijos (para la navegación dinámica)
        foreach ($sub_options as $key => $option) {
            $sub_options[$key]['has_children'] = !empty($menuService->getActiveSubOptions((int)$option['id_item']));
        }
        
        // OBTENER EL NOMBRE DEL ÍTEM PADRE
    $parentItem = $menuService->getById((int)$parent_id);
    $parentName = $parentItem['nombre_item'] ?? 'Opciones Detalladas';


        // --- NUEVA LÓGICA PARA MANEJAR PERSONALIZACIÓN POR PASOS ---

        // Los hijos del platillo principal son los "pasos" de personalización (ej: Salsa, Proteína)
        $potentialSteps = $sub_options;
        $isMultiStep = false;

        // Heurística: Si el primer "paso" tiene sus propios hijos, asumimos que es un flujo multi-paso.
        if (!empty($potentialSteps)) {
            $firstChildsChildren = $menuService->getActiveSubOptions((int)$potentialSteps[0]['id_item']);
            if (!empty($firstChildsChildren)) {
                $isMultiStep = true;
            }
        }

        if ($isMultiStep) {
            $stepsData = [];
            foreach ($potentialSteps as $step) {
                $stepsData[] = [
                    'stepTitle' => $step['nombre_item'],
                    'stepId' => $step['id_item'],
                    'tipo_ui' => $step['tipo_ui'],
                    'options' => $menuService->getActiveSubOptions((int)$step['id_item'])
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'isMultiStep' => true,
                'parentName' => $parentName,
                'steps' => $stepsData,
                'token' => csrf_hash()
            ]);

        } else {
            // Flujo antiguo: solo hay un nivel de opciones. Lo envolvemos para que sea compatible.
            return $this->response->setJSON([
                'success' => true,
                'isMultiStep' => false,
                'parentName' => $parentName,
                'steps' => [[
                    'stepTitle' => $parentName,
                    'stepId' => $parent_id,
                    'tipo_ui' => $parentItem['tipo_ui'] ?? 'checkbox',
                    'options' => $potentialSteps
                ]],
                'token' => csrf_hash()
            ]);
        }
    }

    // Procesamiento del formulario POST
    public function submitQuote()
    {
        $quotationService = service('quotationService');
        $validation = $quotationService->getValidationRules();

        if (! $this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = $this->request->getPost();

        $id_cotizacion = $quotationService->createQuotation($postData);

        if (!$id_cotizacion) {
            return redirect()->back()->withInput()->with('error', 'Hubo un error al guardar la cotización.');
        }

        session()->setFlashdata('from_submission', true);
        // Pasamos el ID a la confirmación, el token se leerá desde la BD
        return redirect()->to("/cotizacion/confirmacion/{$id_cotizacion}");
    }

    // Página de confirmación y generación del Deep Link de WhatsApp
    public function confirmation($id_cotizacion)
    {
        if (!session()->getFlashdata('from_submission')) {
            return redirect()->to('/');
        }

        $viewService = service('quotationViewService');
        $data = $viewService->getDataForQuotationDetail($id_cotizacion);

        if (empty($data['cotizacion'])) {
            // Manejar el caso en que la cotización no se encuentre
            return redirect()->to('/')->with('error', 'La cotización solicitada no existe.');
        }

        $quote = $data['cotizacion'];

        // Usar el número de WhatsApp desde el archivo .env
        $business_whatsapp = getenv('app.businessWhatsapp') ?: '+5215512345678'; // Fallback

        // Mensaje pre-llenado (URL-encoded) [cite: V.B]
        $message = urlencode(
            "*¡Hola!* He completado la cotización en línea con el folio *#{$id_cotizacion}*. Mi nombre es *{$quote['cliente_nombre']}* y me gustaría confirmar los detalles."
        );

        // Generación del Deep Link
        $data['whatsapp_link'] = "https://wa.me/{$business_whatsapp}?text={$message}";

        return view('quotation/confirmation', $data);
    }

    public function calculateQuoteAjax()
    {
        $menuService = service('menuService');
        $menuSelections = $this->request->getPost('menu_selection') ?? [];
        $menuQuantities = $this->request->getPost('menu_quantities') ?? [];
        $numInvitados = (int) $this->request->getPost('num_invitados');

        if (empty($menuSelections)) {
            return $this->response->setJSON(['success' => true, 'total_formatted' => '$0.00', 'summary' => [], 'token' => csrf_hash()]);
        }

        $initialItemIds = array_keys($menuSelections);

        // Cargar no solo los items seleccionados, sino todos sus ancestros también.
        $fullItemTreeIds = $menuService->getItemIdsWithAncestors($initialItemIds);
        $allItems = $menuService->getItemsByIds($fullItemTreeIds);
        $itemsById = array_column($allItems, null, 'id_item');

        $total = 0.00;
        $summaryItems = [];

        $mainDishQuantities = $menuQuantities;

        // Iterar solo sobre los items que el usuario seleccionó explícitamente.
        foreach ($initialItemIds as $itemId) {
            if (!isset($itemsById[$itemId])) continue;

            $item = $itemsById[$itemId];
            $baseQuantity = 1;
            $displayQuantity = null;

            $mainDishQty = $this->findMainDishQuantity($itemId, $mainDishQuantities, $itemsById);

            if ($mainDishQty !== null) {
                $baseQuantity = $mainDishQty;
                if (isset($mainDishQuantities[$itemId])) {
                    $displayQuantity = $baseQuantity;
                }
            } elseif ($item['per_person']) {
                $baseQuantity = $numInvitados > 0 ? $numInvitados : 1;
                $displayQuantity = $baseQuantity;
            }

            $actualQuantity = $baseQuantity;

            $subtotal = $item['precio_unitario'] * $actualQuantity;
            $total += $subtotal;

            $summaryItems[] = [
                'id' => $itemId,
                'name' => $item['nombre_item'],
                'quantity' => $displayQuantity,
                'subtotal_formatted' => '$' . number_format($subtotal, 2)
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'total_formatted' => '$' . number_format($total, 2),
            'summary' => $summaryItems,
            'token' => csrf_hash()
        ]);
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

    public function fechasOcupadas()
    {
        $cotizacionModel = new \App\Models\QuotationModel();
        $fechasDb = $cotizacionModel->select('fecha_evento')
                                    ->where('status', 'Confirmado');
            $fechasDb = $cotizacionModel->select('fecha_evento')
                                        ->where('status', 'Confirmado')
                                        ->findAll();
        $fechasOcupadas = array_column($fechasDb, 'fecha_evento');

        return $this->response->setJSON($fechasOcupadas);
    }

    public function loadRootItemsAjax()
    {
        $mealType = $this->request->getPost('meal_type');
        $presentationId = $this->request->getPost('presentation_id');

        // Validación de entradas
        if (!in_array($mealType, ['desayuno', 'comida', 'ambos']) || !is_numeric($presentationId)) {
            return $this->response->setJSON([
                'success' => false,
                'html' => '<p class="text-red-500">Error: Datos de filtrado inválidos.</p>',
                'token' => csrf_hash()
            ]);
        }

        // --- Lógica de consulta movida directamente aquí para evitar problemas con View Cell en AJAX ---
        $menuModel = new \App\Models\MenuItemModel();
        $query = $menuModel->where('activo', 1);

        // Filtrar por el ID de la presentación seleccionada (el padre)
        $query->where('parent_id', (int)$presentationId);

        // Además, filtrar por tipo de comida (desayuno/comida)
        if ($mealType !== 'ambos') {
            $query->groupStart()
                  ->where('tipo_comida', $mealType)
                  ->orWhere('tipo_comida', 'ambos')
                  ->groupEnd();
        }

        $items = $query->findAll();

        // Renderizar la vista parcial directamente con los datos obtenidos
        $html = view('quotation/partials/_menu_root', ['rootItems' => $items]);
        // --- Fin de la lógica movida ---

        return $this->response->setJSON([
            'success' => true,
            'html' => $html,
            'token' => csrf_hash()
        ]);
    }

    public function downloadPdf($token)
    {
        if (empty($token) || !is_string($token)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $quotationModel = new QuotationModel();
        $quotation = $quotationModel->where('download_token', $token)->first();

        if (!$quotation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $viewService = service('quotationViewService');
        $data = $viewService->getDataForQuotationDetail($quotation['id_cotizacion']);

        $pdf = new \App\Libraries\Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->generatePdfContent($pdf, $data);

        $this->response->setHeader('Content-Type', 'application/pdf');
        $pdf->Output("cotizacion_{$quotation['id_cotizacion']}.pdf", 'I'); // 'I' para inline
    }

    private function generatePdfContent($pdf, $data)
    {
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('mapolato');
        $pdf->SetTitle('Cotización');
        $pdf->SetSubject('Detalles de la Cotización');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $html = view('quotation/pdf_template', $data);
        $pdf->writeHTML($html, true, false, true, false, '');
    }
}