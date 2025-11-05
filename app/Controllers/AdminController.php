<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MenuItemModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\QuotationModel;
use App\Services\QuotationService;
use App\Services\AdminDashboardService;
use App\Services\QuotationViewService;
use App\Services\MenuService;

class AdminController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        // El constructor se deja vacío para no cargar servicios innecesariamente.
    }

    /**
     * Muestra el formulario interactivo para crear un nuevo ítem de menú complejo.
     */
    public function createServiceInteractive()
    {
        $menuService = service('menuService');
        $rootItem = $menuService->getRootItem();

        if (!$rootItem) {
            return redirect()->to(route_to('panel.servicios.index'))->with('error', 'No se encontró la categoría raíz. Por favor, ejecuta el MenuSeeder.');
        }

        // Obtener las subcategorías (hijas directas de la raíz)
        $subCategories = $menuService->getActiveSubOptions($rootItem['id_item']);

        $data = [
            'titulo' => 'Añadir Nuevo Platillo Interactivo',
            'root_category_id' => $rootItem['id_item'],
            'sub_categories' => $subCategories,
        ];

        return view('admin/servicios/crear_interactivo', $data);
    }

    /**
     * Procesa y guarda un nuevo ítem de menú complejo desde el builder interactivo.
     */
    public function editServiceInteractive($id)
    {
        $menuService = service('menuService');
        $itemData = $menuService->getItemWithFullHierarchy((int)$id);

        if (!$itemData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Obtener subcategorías para el dropdown (por si el usuario quiere cambiarla)
        $rootItem = $menuService->getRootItem();
        $subCategories = $rootItem ? $menuService->getActiveSubOptions($rootItem['id_item']) : [];

        $data = [
            'titulo' => 'Editar Platillo: ' . esc($itemData['nombre_item']),
            'itemJSON' => json_encode($itemData),
            'sub_categories' => $subCategories,
        ];

        return view('admin/servicios/editar_interactivo', $data);
    }

    public function updateServiceInteractive()
    {
        $menuService = service('menuService');
        $db = \Config\Database::connect();

        $jsonPayload = $this->request->getPost('menu_structure');
        $serviceData = json_decode($jsonPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($serviceData['main_item'])) {
            return redirect()->back()->withInput()->with('error', 'Error en los datos enviados.');
        }

        $mainItemId = $serviceData['main_item']['id_item'];

        $db->transStart();

        try {
            // 1. Actualizar el platillo principal
            $menuService->updateItem($mainItemId, $serviceData['main_item']);

            // 2. Eliminar toda la personalización existente
            $menuService->deleteAllChildren($mainItemId);

            // 3. Re-crear la personalización desde cero
            if (isset($serviceData['steps'])) {
                foreach ($serviceData['steps'] as $stepData) {
                    $stepId = $menuService->createItem([
                        'nombre_item' => $stepData['nombre_paso'],
                        'parent_id'   => $mainItemId,
                        'tipo_ui'     => $stepData['tipo_ui'],
                        'activo'      => 1,
                    ]);

                    if (!$stepId) throw new \Exception('No se pudo re-crear un paso de personalización.');

                    foreach ($stepData['options'] as $optionData) {
                        $menuService->createItem([
                            'nombre_item'     => $optionData['nombre_opcion'],
                            'parent_id'       => $stepId,
                            'tipo_ui'         => $stepData['tipo_ui'],
                            'precio_unitario' => $optionData['precio_opcion'],
                            'activo'          => 1,
                        ]);
                    }
                }
            }

            $db->transCommit();
            return redirect()->to(site_url(route_to('panel.servicios.index')))->with('success', 'Platillo actualizado exitosamente.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en updateServiceInteractive: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar el platillo.');
        }
    }

    /**
     * Procesa y guarda un nuevo ítem de menú complejo desde el builder interactivo.
     */
    public function storeServiceInteractive()
    {
        $menuService = service('menuService');
        $db = \Config\Database::connect();

        $jsonPayload = $this->request->getPost('menu_structure');
        $serviceData = json_decode($jsonPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($serviceData['main_item'])) {
            return redirect()->back()->withInput()->with('error', 'Error en los datos enviados. La estructura del menú es inválida.');
        }

        $db->transStart();

        try {
            // 1. Determinar el Parent ID (crear nueva subcategoría si es necesario)
            $parentId = null;
            if (!empty($serviceData['category']['new_category_name'])) {
                // Crear la nueva subcategoría bajo la raíz
                $newCategoryData = [
                    'nombre_item' => $serviceData['category']['new_category_name'],
                    'parent_id'   => $serviceData['category']['root_category_id'],
                    'tipo_ui'     => 'checkbox', // Las subcategorías son contenedores
                    'activo'      => 1,
                ];
                $parentId = $menuService->createItem($newCategoryData);
                if (!$parentId) {
                    throw new \Exception('No se pudo crear la nueva subcategoría.');
                }
            } else {
                $parentId = $serviceData['category']['existing_category_id'];
            }

            // 2. Crear el platillo principal (main_item)
            $mainItemData = $serviceData['main_item'];
            $mainItemId = $menuService->createItem([
                'nombre_item'     => $mainItemData['nombre_item'],
                'descripcion'     => $mainItemData['descripcion'],
                'parent_id'       => $parentId,
                'tipo_ui'         => 'checkbox',
                'precio_unitario' => $mainItemData['precio_unitario'],
                'per_person'      => isset($mainItemData['per_person']) ? 1 : 0,
                'activo'          => 1,
                'tipo_comida'     => $mainItemData['tipo_comida'],
            ]);

            if (!$mainItemId) {
                throw new \Exception("No se pudo crear el platillo principal.");
            }

            // 2. Iterar y crear los pasos de personalización y sus opciones
            if (isset($serviceData['steps'])) {
                foreach ($serviceData['steps'] as $stepData) {
                    $stepId = $menuService->createItem([
                        'nombre_item' => $stepData['nombre_paso'],
                        'parent_id'   => $mainItemId,
                        'tipo_ui'     => $stepData['tipo_ui'],
                        'descripcion' => '',
                        'precio_unitario' => 0,
                        'activo'      => 1,
                    ]);

                    if (!$stepId) {
                        throw new \Exception("No se pudo crear un paso de personalización.");
                    }

                    foreach ($stepData['options'] as $optionData) {
                        $menuService->createItem([
                            'nombre_item'     => $optionData['nombre_opcion'],
                            'parent_id'       => $stepId,
                            'tipo_ui'         => $stepData['tipo_ui'], // Las opciones heredan el tipo de UI del paso
                            'precio_unitario' => $optionData['precio_opcion'],
                            'activo'          => 1,
                        ]);
                    }
                }
            }

            $db->transCommit();
            return redirect()->to(site_url(route_to('panel.servicios.index')))->with('success', 'Servicio complejo añadido exitosamente.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en storeServiceInteractive: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al guardar el servicio: ' . $e->getMessage());
        }
    }


    /**
     * Muestra la vista del formulario de Login de Administración.
     */
    public function login()
    {
        // Si el usuario ya tiene una sesión válida (ej. cookie), redirigir al dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to(route_to('panel.dashboard'));
        }
        return view('admin/login');
    }

    /**
     * Maneja la autenticación del formulario de login.
     */
    public function authenticate()
    {
        // 1. Definir y validar las reglas de forma local
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Por favor, verifica tus credenciales.');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // 2. Preparar la petición HTTP al Servidor de Autorización (/api/login)
        
        // CRÍTICO: Usar el Service Locator para obtener una instancia correcta del cliente HTTP
        $client = \Config\Services::curlrequest(); 
        
        $apiUrl = base_url('api/v1/login'); 
        
        try {
            $response = $client->post($apiUrl, [
                'form_params' => [
                    'email' => $email,
                    'password' => $password,
                ],
                // Desactivar la verificación SSL si es necesario en entornos de prueba (NO en producción)
                'verify' => false, 
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            log_message('debug', 'API Status Code: ' . $statusCode);
            log_message('debug', 'API Response Body: ' . $body);

            $responseBody = json_decode($body);

            if ($statusCode === 200) {
                // 3. Autenticación Exitosa: Recibir tokens y establecer la sesión BFF
                $session = session();
                
                // Establecer la sesión BFF
                $session->set('isLoggedIn', true);
                $session->set('accessToken', $responseBody->access_token);
                $session->set('refreshToken', $responseBody->refresh_token);
                
                // 4. Redirigir al Dashboard
                return redirect()->route('panel.dashboard')->with('success', 'Bienvenido al Dashboard.');
            } else {
                // 5. Fallo de Autenticación
                $errorMessage = $responseBody->messages->error ?? 'Credenciales inválidas.';
                return redirect()->back()->withInput()->with('error', $errorMessage);
            }

        } catch (\Exception $e) {
            // Manejo de errores de conexión (ej. API no disponible)
            log_message('error', 'API Login Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error de conexión con el servidor de autenticación.');
        }
    }

    /**
     * Muestra el Dashboard (Ruta Protegida).
     */
    public function dashboard()
    {
        $dashboardService = service('adminDashboardService');
        $data = $dashboardService->getDashboardData();
        return view('admin/dashboard', $data);
    }

    /**
     * Endpoint para obtener datos de la gráfica de distribución de totales.
     */
    public function getQuoteTotalDistributionData()
    {
        $quotationModel = new \App\Models\QuotationModel();
        $data = $quotationModel->getQuoteTotalDistribution();

        return $this->respond($data);
    }

    /**
     * Muestra la vista del listado de cotizaciones con DataTables.
     */
    public function listQuotations()
    {
        return view('admin/cotizaciones/index');
    }

    /**
     * Muestra la vista del listado de servicios (ítems de menú) con DataTables.
     */
    public function listServices()
    {
        return view('admin/servicios/index');
    }

    /**
     * Muestra el formulario para crear un nuevo ítem de menú.
     */
    public function createService()
    {
        $menuService = service('menuService');

        // Obtener items raíz y de nivel 2 de forma centralizada
        $parentItems = $menuService->getActiveSubOptions(0);

        $data = [
            'titulo' => 'Añadir Nuevo Servicio',
            'parent_items' => $parentItems,
        ];
        return view('admin/servicios/crear', $data);
    }

    /**
     * Procesa el formulario y guarda el nuevo ítem de menú.
     */
    public function storeService()
    {
        $rules = [
            'nombre_item' => 'required|max_length[255]',
            'tipo_ui' => 'required|in_list[nav_group,checkbox,radio,quantity]',
            'parent_id' => 'permit_empty|is_natural',
            'precio_unitario' => 'permit_empty|decimal',
            'activo' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $menuService = service('menuService');
        $data = $this->request->getPost();

        $id = $menuService->createItem($data);
        if ($id) {
            return redirect()->to(site_url(route_to('panel.servicios.index')))->with('success', 'Servicio añadido exitosamente.');
        }

        return redirect()->back()->withInput()->with('error', 'No se pudo guardar el servicio.');
    }

    /**
     * Muestra el formulario para editar un ítem de menú existente.
     */
    public function editService($id)
    {
        $menuService = service('menuService');
        $service = $menuService->getById((int)$id);

        if (!$service) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

    // Lógica para obtener solo padres de Nivel 1 y Nivel 2
    $parentItems = $menuService->getActiveSubOptions(0);
    // Excluir el ítem actual de la lista de posibles padres se hace en la vista o aquí filtrando
    $parentItems = array_filter($parentItems, fn($p) => $p['id_item'] != $id);

        $data = [
            'titulo' => 'Editar Servicio #' . $id,
            'service' => $service,
            'parent_items' => $parentItems,
        ];

        return view('admin/servicios/editar', $data);
    }

    /**
     * Procesa el formulario y actualiza un ítem de menú.
     */
    public function updateService()
    {
        $id = $this->request->getPost('id_item');
        $rules = [
            'id_item' => 'required|is_natural_no_zero',
            'nombre_item' => 'required|max_length[255]',
            'tipo_ui' => 'required|in_list[nav_group,checkbox,radio,quantity]',
            'parent_id' => 'permit_empty|is_natural',
            'precio_unitario' => 'permit_empty|decimal',
            'activo' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $menuService = service('menuService');
        $data = $this->request->getPost();

        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        // Prevenir que un ítem sea su propio padre
        if ($data['parent_id'] == $id) {
            return redirect()->back()->withInput()->with('error', 'Un servicio no puede ser su propia categoría padre.');
        }

        if ($menuService->updateItem($id, $data)) {
            return redirect()->to(site_url(route_to('panel.servicios.index')))->with('success', 'Servicio actualizado exitosamente.');
        }

        return redirect()->back()->withInput()->with('error', 'No se pudo actualizar el servicio.');
    }

    /**
     * Elimina un ítem de menú.
     */
    public function deleteService()
    {
        $id = $this->request->getPost('id_item');

        if (!$this->validate(['id_item' => 'required|is_natural_no_zero'])) {
            return redirect()->to(site_url(route_to('panel.servicios.index')))->with('error', 'ID de servicio inválido.');
        }

        $menuService = service('menuService');

        if (! $menuService->deleteItem($id)) {
            return redirect()->to(site_url(route_to('panel.servicios.index')))
                             ->with('error', 'No se puede eliminar una categoría que contiene sub-servicios o ocurrió un error.');
        }

        return redirect()->to(site_url(route_to('panel.servicios.index')))
                         ->with('success', 'Servicio eliminado exitosamente.');
    }

    /**
     * Muestra la vista de detalle de una cotización específica.
     */
    public function viewCotizacion(int $id_cotizacion)
    {
        $viewService = service('quotationViewService');
        $data = $viewService->getDataForQuotationDetail($id_cotizacion);
        return view('admin/cotizaciones/detalle', $data);
    }

    public function editCotizacion(int $id_cotizacion)
    {
        $cotizacionModel = new \App\Models\QuotationModel();
        $cotizacion = $cotizacionModel->find($id_cotizacion);

        if (!$cotizacion) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 1. Deserializar el menú para precargar el JS
        $menuSeleccionado = $cotizacion['detalle_menu']; 
        
        // 2. Preparar los datos para la vista
        $data = [
            'cotizacion' => $cotizacion,
            'menuSeleccionadoJson' => json_encode($menuSeleccionado), // CRÍTICO: Pasar el menú como JSON
            'isEditing' => true,
            'titulo' => 'Editar Cotización #' . $id_cotizacion,
        ];

        return view('admin/cotizaciones/editar', $data); 
    }

    public function updateCotizacion()
    {
        $quotationService = service('quotationService');
        $validationRules = $quotationService->getValidationRules();
        $cotizacionId = $this->request->getPost('id_cotizacion');

        // Añadir regla para el ID de la cotización
        $validationRules['id_cotizacion'] = 'required|is_natural_no_zero';

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = $this->request->getPost();

        $success = $quotationService->updateQuotation($cotizacionId, $postData);

        if ($success) {
            return redirect()->to(site_url(route_to('panel.cotizaciones.view', $cotizacionId)))
                             ->with('success', 'La cotización ha sido actualizada exitosamente.');
        }

        return redirect()->back()->withInput()
                         ->with('error', 'Hubo un error al actualizar la cotización. Por favor, inténtalo de nuevo.');
    }

    /**
     * Actualiza el estado de una cotización.
     */
    public function updateStatus()
    {
        $cotizacionId = $this->request->getPost('cotizacion_id');
        $newStatus = $this->request->getPost('status');

        $rules = [
            'cotizacion_id' => 'required|is_natural_no_zero',
            'status'        => 'required|in_list[pendiente,confirmado,cancelado,pagado,contactado,en_revision]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Datos inválidos para actualizar el estado.');
        }

    $quotationModel = new QuotationModel();
    $updateResult = $quotationModel->update($cotizacionId, ['status' => $newStatus]);

        if ($updateResult) {
            return redirect()->to(route_to('panel.cotizaciones.view', $cotizacionId))->with('success', 'El estado de la cotización ha sido actualizado.');
        }

        return redirect()->back()->withInput()->with('error', 'No se pudo actualizar el estado de la cotización.');
    }

    public function addAnticipo()
    {
        $cotizacionId = $this->request->getPost('id_cotizacion');
        $anticipo = $this->request->getPost('anticipo');

        $quotationModel = new \App\Models\QuotationModel();
        $cotizacion = $quotationModel->find($cotizacionId);

        if ($cotizacion) {
            $total = $cotizacion['total_estimado'];
            $resta = $total - $anticipo;

            $data = [
                'anticipo' => $anticipo,
                'resta' => $resta,
            ];

            $quotationModel->update($cotizacionId, $data);
        }

        return redirect()->back();
    }

   public function logout()
    {
        $session = session();
        $refreshToken = $session->get('refreshToken');
        
        // 1. Llamar al endpoint de la API para revocar el Refresh Token
        if ($refreshToken) {
            // CRÍTICO: Usar el Service Locator para obtener una instancia correcta del cliente HTTP
            $client = \Config\Services::curlrequest(); 
            
            $apiUrl = base_url('api/v1/logout'); 
            
            try {
                $client->post($apiUrl, [
                    'form_params' => [
                        'refresh_token' => $refreshToken,
                    ],
                    'verify' => false, 
                ]);
                // No importa si la API falla, el paso 2 es más importante para el usuario.
            } catch (\Exception $e) {
                log_message('error', 'API Logout Error: ' . $e->getMessage());
            }
        }

        // 2. Limpiar la sesión local (BFF)
        $session->remove(['isLoggedIn', 'accessToken', 'refreshToken']);
        $session->destroy(); // Destruir la sesión de CI4

        // 3. Redirigir a la página de login
        // Usamos route_to('admin.login') para ser más robustos, aunque base_url('/admin') también funciona.
        return redirect()->to(base_url('/admin'))->with('success', 'Sesión cerrada exitosamente.');
    }
}