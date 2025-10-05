<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\CURLRequest;
use App\Models\QuotationModel;

class AdminController extends BaseController
{
    use ResponseTrait;

    /**
     * Muestra la vista del formulario de Login de Administración.
     */
    public function login()
    {
        // Si el usuario ya tiene una sesión válida (ej. cookie), redirigir al dashboard
        // NOTA: La lógica de sesión/cookie se implementaría aquí en un sistema BFF.
        
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
        
        $apiUrl = base_url('api/login'); 
        
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
            $responseBody = json_decode($response->getBody());

            if ($statusCode === 200) {
                // 3. Autenticación Exitosa: Recibir tokens y establecer la sesión BFF
                $session = session();
                
                // Establecer la sesión BFF
                $session->set('isLoggedIn', true);
                $session->set('accessToken', $responseBody->access_token);
                $session->set('refreshToken', $responseBody->refresh_token);
                
                // 4. Redirigir al Dashboard
                return redirect()->to(route_to('admin.dashboard'))->with('success', 'Bienvenido al Dashboard.');
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
     * Función auxiliar para contar cotizaciones confirmadas en el mes actual.
     */
    private function contarConfirmadasMesActual(QuotationModel $model): int
    {
        // Contar cotizaciones confirmadas creadas el mes actual
        return $model->where('status', 'Confirmado')
                     ->where('YEAR(created_at)', date('Y'))
                     ->where('MONTH(created_at)', date('m'))
                     ->countAllResults(); 
    }

    /**
     * Muestra el Dashboard (Ruta Protegida).
     */
    public function dashboard()
    {
        $session = session();
        $cotizacionModel = new QuotationModel();
        // 1. Obtener la URL base para la navegación
        $baseURL = base_url('admin'); // La base para todas las rutas de administración

        // 2. Definir los enlaces de navegación
        $navLinks = [
            'Dashboard' => ['url' => $baseURL, 'active' => true],
            'Cotizaciones' => ['url' => $baseURL . '/cotizaciones', 'active' => false],
            'Calendario' => ['url' => $baseURL . '/calendario', 'active' => false],
            'Servicios' => ['url' => $baseURL . '/servicios', 'active' => false],
        ];

        $uiLabels = [
            'social' => 'Evento Social',
            'empresarial' => 'Evento Empresarial',
            'otro' => 'Otro',
            'recomendacion' => 'Recomendación',
            'redes' => 'Redes Sociales',
            'restaurante' => 'Por el Restaurante',
            'hombres' => 'Hombres',
            'mujeres' => 'Mujeres',
            'ninos' => 'Niños',
            'mixto' => 'Mixto',
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado',
            'cancelado' => 'Cancelado',
            'pagado' => 'Pagado',
            'contactado' => 'Contactado',
            'en_revision' => 'En Revision',
            'No especificado' => 'No especificado', // Para valores nulos/vacíos
        ];

        $stats_canal_origen = $cotizacionModel->getStatsPorCanalOrigen();
        $stats_tipo_evento = $cotizacionModel->getStatsPorTipoEvento();

        // 3. Pasar los datos a la vista
        $data = [
            'currentPage' => 'Dashboard',
            'baseURL' => $baseURL,
            'navLinks' => $navLinks,
            'isLoggedIn' => $session->get('isLoggedIn') ?? false,
            
            // --- DATOS DEL DASHBOARD (KPIs) ---
            'pendientes' => $cotizacionModel->contarPorEstado('Pendiente'),
            'confirmadas_mes' => $this->contarConfirmadasMesActual($cotizacionModel),
            'ingresos_mes' => $cotizacionModel->ingresosConfirmadosPorMes(date('Y'), date('m')),
            'kpi_conversion' => $cotizacionModel->getConversionRateKpi(),
            
            // --- DATOS DE TABLAS ---
            'ultimas_cotizaciones' => $cotizacionModel->getUltimasCotizaciones(5),

            // --- DATOS DE GRÁFICAS (JSON) ---
            'grafica_ingresos' => $cotizacionModel->getIngresosUltimosMeses(6),
            'grafica_ingresos_json' => json_encode($cotizacionModel->getIngresosUltimosMeses(6)),
            'uiLabels' => $uiLabels, 

            'stats_canal_origen' => $stats_canal_origen,
            'stats_tipo_evento' => $stats_tipo_evento,
        ];
        
        return view('admin/dashboard', $data);
    }

    /**
     * Muestra la vista de detalle de una cotización específica.
     * @param int $id_cotizacion ID de la cotización a mostrar.
     */
    public function viewCotizacion(int $id_cotizacion)
    {
        $session = session();

        $cotizacionModel = new \App\Models\QuotationModel();
        $menuItemModel = new \App\Models\MenuItemModel();

        $baseURL = base_url('admin');

        $navLinks = [
            'Dashboard' => ['url' => $baseURL, 'active' => false],
            'Cotizaciones' => ['url' => $baseURL . '/cotizaciones', 'active' => true],
            'Calendario' => ['url' => $baseURL . '/calendario', 'active' => false],
            'Servicios' => ['url' => $baseURL . '/servicios', 'active' => false],
        ];
        
        $cotizacion = $cotizacionModel->find($id_cotizacion);
        if (!$cotizacion) { throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }

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
        
        // 1. Obtener todos los ítems de menú y construir el mapa de jerarquía
        $allMenuItems = $menuItemModel->findAll();
        $menuMap = [];
        foreach ($allMenuItems as $item) {
            $menuMap[$item['id_item']] = $item;
        }
        
        // 2. Procesar y AGREGAR los servicios seleccionados a una estructura jerárquica
        $menuSeleccionado = $cotizacion['detalle_menu']; 
        $serviciosAgrupados = [];
        
        if (!empty($menuSeleccionado)) {
            foreach ($menuSeleccionado as $itemId => $cantidad) {
                if ($cantidad <= 0 || !isset($menuMap[$itemId])) continue;

                $item = $menuMap[$itemId];

                // 1. Determinar si el ítem tiene hijos (es un contenedor de navegación)
                $hasChildren = $menuItemModel->where('parent_id', $itemId)->countAllResults() > 0;

                // 2. Regla de Filtrado: Excluir si es una Categoría Raíz O un Contenedor de Navegación
                if ($item['parent_id'] === null || $hasChildren) {
                    continue; // Ignorar categorías raíz y contenedores intermedios
                }
                
                // --- CRÍTICO: CONSTRUIR LA RUTA JERÁRQUICA COMPLETA ---
                $path = [];
                $currentId = $itemId;
                
                // Recorrer hacia arriba hasta encontrar el Nivel 1 (parent_id = null)
                while ($currentId !== null && isset($menuMap[$currentId])) {
                    $path[] = $menuMap[$currentId];
                    $currentId = $menuMap[$currentId]['parent_id'];
                }
                
                // Invertir el path para tener Nivel 1 > Nivel 2 > Nivel 3
                $path = array_reverse($path);
                
                $rootName = $path[0]['nombre_item']; // Nivel 1
                
                // Inicializar la categoría si no existe
                if (!isset($serviciosAgrupados[$rootName])) {
                    $serviciosAgrupados[$rootName] = [];
                }

                // Añadir el ítem detallado con su ruta completa
                $serviciosAgrupados[$rootName][] = [
                    'path' => $path, // Array con todos los nodos del camino
                    'nombre' => $menuMap[$itemId]['nombre_item'],
                    'cantidad' => ($menuMap[$itemId]['tipo_ui'] === 'quantity') ? $cantidad : 'Sí',
                    'precio' => $menuMap[$itemId]['precio_unitario'],
                    'subtotal' => $menuMap[$itemId]['precio_unitario'] * $cantidad,
                ];
            }
        }

        // 3. Preparar los datos para la vista
        $data = [
            'titulo' => 'Detalle de Cotización',
            'cotizacion' => $cotizacion,
            'servicios_seleccionados' => $serviciosAgrupados, 
            'uiLabels' => $uiLabels,
            'navLinks' => $navLinks,
            'baseURL' => $baseURL,
            'isLoggedIn' => $session->get('isLoggedIn') ?? false,
        ];

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

        // Reutilizamos la vista del formulario principal
        return view('admin/cotizaciones/editar', $data); 
    }


   public function logout()
    {
        $session = session();
        $refreshToken = $session->get('refreshToken');
        
        // 1. Llamar al endpoint de la API para revocar el Refresh Token
        if ($refreshToken) {
            // CRÍTICO: Usar el Service Locator para obtener una instancia correcta del cliente HTTP
            $client = \Config\Services::curlrequest(); 
            
            $apiUrl = base_url('api/logout'); 
            
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
        $session->remove(['isLoggedIn', 'userId', 'accessToken', 'refreshToken']);
        $session->destroy(); // Destruir la sesión de CI4

        // 3. Redirigir a la página de login
        // Usamos route_to('admin.login') para ser más robustos, aunque base_url('/admin') también funciona.
        return redirect()->to(base_url('/admin'))->with('success', 'Sesión cerrada exitosamente.');
    }
}