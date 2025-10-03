<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\CURLRequest;

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
     * Muestra el Dashboard (Ruta Protegida).
     */
    public function dashboard()
    {
        $session = session();
        // 1. Obtener la URL base para la navegación
        $baseURL = base_url('admin'); // La base para todas las rutas de administración

        // 2. Definir los enlaces de navegación
        $navLinks = [
            'Dashboard' => ['url' => $baseURL, 'active' => true],
            'Cotizaciones' => ['url' => $baseURL . '/cotizaciones', 'active' => false],
            'Calendario' => ['url' => $baseURL . '/calendario', 'active' => false],
            'Servicios' => ['url' => $baseURL . '/servicios', 'active' => false],
        ];

        // 3. Pasar los datos a la vista
        $data = [
            'currentPage' => 'Dashboard',
            'baseURL' => $baseURL,
            'navLinks' => $navLinks,
            'isLoggedIn' => $session->get('isLoggedIn') ?? false,
        ];
        
        return view('admin/dashboard', $data);
    }

    /**
     * Cierra la sesión del usuario (BFF Logout).
     */
    public function logout()
    {
        $session = session();
        $refreshToken = $session->get('refreshToken');
        
        // 1. Llamar al endpoint de la API para revocar el Refresh Token
        if ($refreshToken) {
            $client = new \CodeIgniter\HTTP\CURLRequest(new \Config\App());
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
        return redirect()->to(route_to('admin.login'))->with('success', 'Sesión cerrada exitosamente.');
    }
}