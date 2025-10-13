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

    // Endpoint para peticiones AJAX de sub-opciones dependientes
    public function loadSubOptionsAjax()
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


        // 3. Renderizar la vista parcial para inyectar en el DOM
        $html = view('quotation/partials/_sub_menu', [
            'options' => $sub_options,
            'parentName' => $parentName // Pasar el nombre del padre a la vista
        ]);
        
        // Devolver JSON de éxito con el HTML y el token actualizado
        return $this->response->setJSON([
            'success' => true, 
            'html' => $html,
            'token' => csrf_hash()
        ]);
    }

    // Procesamiento del formulario POST
    public function submitQuote()
    {
        // Obtener el servicio desde el contenedor de servicios
        $quotationService = service('quotationService');
        $validation = $quotationService->getValidationRules();

        if (! $this->validate($validation)) {
            // Falla la validación: repoblar campos y mostrar errores
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = $this->request->getPost();
        $id_cotizacion = $quotationService->createQuotation($postData);

        if (!$id_cotizacion) {
            return redirect()->back()->withInput()->with('error', 'Hubo un error al guardar la cotización.');
        }

        // Establecer un flashdata para permitir el acceso a la página de confirmación
        session()->setFlashdata('from_submission', true);

        return redirect()->to("/cotizacion/confirmacion/{$id_cotizacion}");
    }

    // Página de confirmación y generación del Deep Link de WhatsApp
    public function confirmation($id_cotizacion)
    {
        // Verificar si el usuario viene del formulario de envío
        if (!session()->getFlashdata('from_submission')) {
            // Redirigir si se intenta acceder directamente a la URL
            return redirect()->to('/');
        }

        // Usamos el servicio de vista para obtener todos los datos procesados
        $viewService = service('quotationViewService');
        $data = $viewService->getDataForQuotationDetail($id_cotizacion);

        // Extraemos la cotización para usarla en el mensaje de WhatsApp
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
        // Obtener la cantidad de invitados
        $numInvitados = (int) $this->request->getPost('num_invitados'); 
        
        $total = 0.00;
        $summary = [];

        if (empty($menuSelections)) {
            return $this->response->setJSON([
                'success' => true,
                'total_formatted' => '$0.00',
                'summary' => [],
                'token' => csrf_hash()
            ]);
        }

        // Obtener los IDs de los ítems seleccionados
    $itemIds = array_keys($menuSelections);
    $items = $menuService->getItemsByIds($itemIds);

        foreach ($items as $item) {
            $itemId = $item['id_item'];
            $value = $menuSelections[$itemId];
            $quantity = 0;
            $isPerPerson = false; // Flag para ítems por persona

            // Lógica para determinar la cantidad base
            if ($item['tipo_ui'] === 'quantity') {
                // Si es quantity, el valor es la cantidad ingresada (ej. 100 mini brownies)
                $quantity = (int) $value;
            } elseif ($item['tipo_ui'] === 'checkbox' || $item['tipo_ui'] === 'radio') {
                // Si es checkbox/radio, el valor es el ID del ítem (asumimos cantidad = 1)
                $quantity = 1;
                // Asumimos que todos los ítems de Nivel 2 y 3 son "por persona"
                // a menos que sean un costo fijo (como la modalidad de servicio)
                
                // Lógica para determinar si es por persona (basada en el nombre/descripción)
                // Usaremos una heurística simple: si el precio es bajo, es por persona.
                // Si el precio es alto (costo fijo), no es por persona.
                if ($item['precio_unitario'] < 50.00) { // Ajusta este umbral según tu lógica de negocio
                    $isPerPerson = true;
                }
            }

                if ($quantity > 0) {
                // Multiplicar por la cantidad de invitados si es "por persona"
                $finalQuantity = $isPerPerson ? $numInvitados : $quantity;
                
                // Si el ítem es un costo fijo (como la modalidad de servicio), la cantidad es 1
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

        return $this->response->setJSON([
            'success' => true,
            'total_formatted' => '$' . number_format($total, 2),
            'summary' => $summary,
            'token' => csrf_hash()
        ]);
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
}