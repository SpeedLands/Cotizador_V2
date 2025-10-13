<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\JwtService;
use App\Models\RefreshTokenModel;
use CodeIgniter\API\ResponseTrait;

class ApiAuthController extends BaseController
{
    use ResponseTrait;

    protected $authService;

    public function __construct()
    {
        $this->authService = service('authService');
    }

    /**
     * Endpoint de inicio de sesión
     * POST /api/v1/login
     */
    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $tokens = $this->authService->login(['email' => $email, 'password' => $password]);
        if (!$tokens) {
            return $this->failUnauthorized('Invalid login credentials.');
        }

        return $this->respond([
            'message'       => 'Login successful',
            'access_token'  => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in'    => getenv('JWT_AT_TIME_TO_LIVE'),
        ]);
    }

    /**
     * Endpoint de Refresco de Tokens (Rotación)
     * POST /api/v1/token/refresh
     */
    public function refresh()
    {
        $refreshToken = $this->request->getPost('refresh_token');

        if (!$refreshToken) {
            return $this->failUnauthorized('Refresh Token is required.');
        }

        $newTokens = $this->authService->refresh($refreshToken);
        if (!$newTokens) {
            return $this->failUnauthorized('Invalid or expired Refresh Token.');
        }

        return $this->respond([
            'message'       => 'Token refreshed successfully',
            'access_token'  => $newTokens['access_token'],
            'refresh_token' => $newTokens['refresh_token'],
            'expires_in'    => getenv('JWT_AT_TIME_TO_LIVE'),
        ]);
    }

    /**
     * Endpoint para revocar un refresh token (logout de API)
     * POST /api/v1/logout
     */
    public function logout()
    {
        $refreshToken = $this->request->getPost('refresh_token');
        if ($refreshToken) {
            $this->authService->revoke($refreshToken);
        }

        return $this->respond(['message' => 'Refresh token revoked.']);
    }
}