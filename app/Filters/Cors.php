<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Permitir orígenes. En producción, deberías restringirlo a dominios específicos.
        // Por ejemplo: header('Access-Control-Allow-Origin: https://mi-app.com');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');

        $method = $request->getMethod();

        // Si es una solicitud OPTIONS (preflight), terminamos la ejecución aquí.
        if (strtolower($method) === 'options') {
            // No es necesario establecer un código de estado 204, CI4 lo maneja.
            // Simplemente detenemos la ejecución para que no continúe al controlador.
            exit();
        }
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita ninguna acción después.
    }
}