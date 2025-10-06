<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MenuItemModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\QuotationModel;
use App\Services\QuotationService;
use App\Services\AdminDashboardService;
use App\Services\QuotationViewService;

class AdminController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        // El constructor se deja vacío para no cargar servicios innecesariamente.
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
        $dashboardService = new AdminDashboardService();
        $data = $dashboardService->getDashboardData();
        return view('admin/dashboard', $data);
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
        $menuItemModel = new MenuItemModel();

        // Lógica para obtener solo padres de Nivel 1 y Nivel 2
        // 1. Obtener IDs de los ítems de Nivel 1 (raíz)
        $level1_ids = $menuItemModel->where('parent_id IS NULL')->findColumn('id_item') ?? [];

        // 2. Obtener ítems de Nivel 1 y Nivel 2
        $query = $menuItemModel->where('parent_id IS NULL');
        if (!empty($level1_ids)) {
            $query->orWhereIn('parent_id', $level1_ids);
        }
        
        $data = [
            'titulo' => 'Añadir Nuevo Servicio',
            'parent_items' => $query->orderBy('nombre_item', 'ASC')->findAll(),
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

        $menuItemModel = new MenuItemModel();
        $data = $this->request->getPost();

        // Asegurarse de que parent_id sea null si está vacío
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        if ($menuItemModel->save($data)) {
            return redirect()->to(site_url(route_to('panel.servicios.index')))->with('success', 'Servicio añadido exitosamente.');
        }

        return redirect()->back()->withInput()->with('error', 'No se pudo guardar el servicio.');
    }

    /**
     * Muestra el formulario para editar un ítem de menú existente.
     */
    public function editService($id)
    {
        $menuItemModel = new MenuItemModel();
        $service = $menuItemModel->find($id);

        if (!$service) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Lógica para obtener solo padres de Nivel 1 y Nivel 2
        $level1_ids = $menuItemModel->where('parent_id IS NULL')->findColumn('id_item') ?? [];
        $query = $menuItemModel->where('parent_id IS NULL');
        if (!empty($level1_ids)) {
            $query->orWhereIn('parent_id', $level1_ids);
        }
        // Excluir el ítem actual de la lista de posibles padres
        $query->where('id_item !=', $id);

        $data = [
            'titulo' => 'Editar Servicio #' . $id,
            'service' => $service,
            'parent_items' => $query->orderBy('nombre_item', 'ASC')->findAll(),
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

        $menuItemModel = new MenuItemModel();
        $data = $this->request->getPost();

        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        // Prevenir que un ítem sea su propio padre
        if ($data['parent_id'] == $id) {
            return redirect()->back()->withInput()->with('error', 'Un servicio no puede ser su propia categoría padre.');
        }

        if ($menuItemModel->update($id, $data)) {
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

        $menuItemModel = new MenuItemModel();

        // Lógica de seguridad: Verificar si el ítem tiene hijos
        $childCount = $menuItemModel->where('parent_id', $id)->countAllResults();

        if ($childCount > 0) {
            return redirect()->to(site_url(route_to('panel.servicios.index')))
                             ->with('error', 'No se puede eliminar una categoría que contiene sub-servicios. Por favor, elimina o reasigna los sub-servicios primero.');
        }

        // Proceder con la eliminación
        if ($menuItemModel->delete($id)) {
            return redirect()->to(site_url(route_to('panel.servicios.index')))
                             ->with('success', 'Servicio eliminado exitosamente.');
        }

        return redirect()->to(site_url(route_to('panel.servicios.index')))
                         ->with('error', 'No se pudo eliminar el servicio.');
    }

    /**
     * Muestra la vista de detalle de una cotización específica.
     */
    public function viewCotizacion(int $id_cotizacion)
    {
        $viewService = new QuotationViewService();
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
        $quotationService = new QuotationService();
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