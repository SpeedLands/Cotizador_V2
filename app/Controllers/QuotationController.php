<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\QuotationModel;
use App\Models\MenuItemModel;
use App\Models\AdminNotificationModel; // Para la notificación interna

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
        $menuModel = new \App\Models\MenuItemModel(); // Usar el FQCN o el 'use'

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
        $sub_options = $menuModel->where('parent_id', $parent_id)
                                 ->where('activo', 1)
                                 ->findAll();
        
        // Determinar si cada sub-opción tiene hijos (para la navegación dinámica)
        foreach ($sub_options as $key => $option) {
            // Consulta rápida para ver si existen ítems con este ID como padre
            // Esto asegura que la lógica de navegación sea 100% dinámica de la DB.
            $sub_options[$key]['has_children'] = $menuModel->where('parent_id', $option['id_item'])->countAllResults() > 0;
        }
        
        // OBTENER EL NOMBRE DEL ÍTEM PADRE
        $parentItem = $menuModel->find($parent_id);
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
        $data = $this->request->getPost();
        $quotationModel = new QuotationModel();
        $notificationModel = new AdminNotificationModel();

        // 1. Definición de Reglas de Validación Críticas
        $rules = [
            'cliente_nombre'    => 'required|max_length[100]',
            'cliente_whatsapp'  => 'required|regex_match[/^\+?\d{10,20}$/]', 
            'num_invitados'     => 'required|is_natural_no_zero',
            'fecha_evento'      => 'required|valid_date',
            'menu_selection'    => 'required', 
            
            // NUEVAS REGLAS DE VALIDACIÓN
            'tipo_evento'       => 'required|in_list[social,empresarial,otro]',
            'hora_inicio'       => 'required',
            'hora_consumo'      => 'required',
            'hora_finalizacion' => 'required',
            'direccion_evento'  => 'required|max_length[500]',
            'mesa_mantel'       => 'required|in_list[si,no,otro]',
            'dificultad_montaje'=> 'required|max_length[500]',
            'como_nos_conocio'  => 'required',
            'tipo_consumidores' => 'required',
            
            // Reglas condicionales (opcionales, pero recomendadas)
            'nombre_empresa'    => 'permit_empty|max_length[150]',
            'mesa_mantel_especificar' => 'permit_empty|max_length[255]',
            'restricciones_alimenticias' => 'permit_empty|max_length[255]',
            'rango_presupuesto' => 'permit_empty|max_length[50]',
        ];

        if (! $this->validate($rules)) {
            // Falla la validación: repoblar campos y mostrar errores
            return view('quotation/form', [
                'validation' => $this->validator,
            ]);
        }

        // 2. Preparar los datos para la inserción atómica
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

            // El modelo se encarga de serializar 'detalle_menu' a JSON
            'detalle_menu'      => $data['menu_selection'], 
            'notas_adicionales' => $data['notas_adicionales'] ?? null,
        ];

        // 3. Inserción Atómica
        $quotationModel->insert($insertData);
        $id_cotizacion = $quotationModel->getInsertID();

        // 4. Notificación Interna (Basada en DB)
        $notificationModel->insert([
            'quotation_id' => $id_cotizacion,
            'message'      => "Nueva cotización recibida: #{$id_cotizacion} de {$data['cliente_nombre']}",
            'is_read'      => 0,
        ]);

        // 5. Redirección a la página de confirmación/conversión
        return redirect()->to("/cotizacion/confirmacion/{$id_cotizacion}");
    }

    // Página de confirmación y generación del Deep Link de WhatsApp
    public function confirmation($id_cotizacion)
    {
        $quotationModel = new QuotationModel();
        $quote = $quotationModel->find($id_cotizacion);

        if (! $quote) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Número de negocio del catering (ejemplo)
        $business_whatsapp = '+528781453793'; 

        // Mensaje pre-llenado (URL-encoded) [cite: V.B]
        $message = urlencode(
            "*¡Hola!* He completado la cotización número *{$id_cotizacion}*. Mi nombre es {$quote['cliente_nombre']} y me gustaría confirmar los detalles."
        );

        // Generación del Deep Link
        $whatsapp_link = "https://wa.me/{$business_whatsapp}?text={$message}";

        return view('quotation/confirmation', [
            'quote' => $quote,
            'whatsapp_link' => $whatsapp_link,
        ]);
    }

    public function calculateQuoteAjax()
    {
        $menuModel = new MenuItemModel();
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
        $items = $menuModel->whereIn('id_item', $itemIds)->findAll();

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
}