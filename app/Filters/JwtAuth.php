<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtService;
use Config\Services;

class JwtAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $jwtService = new JwtService();
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader) {
            return Services::response()
                ->setJSON(['error' => 'Token de autorización no proporcionado.'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Extraer el token del encabezado "Bearer <token>"
        if (sscanf($authHeader, 'Bearer %s', $token) !== 1) {
             return Services::response()
                ->setJSON(['error' => 'Formato de token inválido.'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $decoded = $jwtService->decodeToken($token);
        if (!$decoded || $decoded->type !== 'access') {
             return Services::response()
                ->setJSON(['error' => 'Token inválido o expirado.'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada después
    }
}