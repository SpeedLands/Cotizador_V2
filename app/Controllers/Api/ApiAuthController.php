<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\JwtService;
use App\Models\RefreshTokenModel;
use CodeIgniter\API\ResponseTrait;

class ApiAuthController extends BaseController
{
    use ResponseTrait;

    protected $jwtService;
    protected $rtModel;

    public function __construct()
    {
        $this->jwtService = new JwtService();
        $this->rtModel = new RefreshTokenModel();
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

        $userModel = new \App\Models\AdminUserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->failUnauthorized('Invalid login credentials.');
        }

        $userId = $user['id']; // ID del usuario validado

        // 1. Generar el par de tokens
        $tokens = $this->jwtService->generateTokenPair($userId);

        // 2. Persistir el JTI del Refresh Token (RT)
        $this->rtModel->insert([
            'user_id'    => $userId,
            'jti'        => $tokens['jti'],
            'expires_at' => $tokens['rt_expires_at'],
            'revoked'    => 0,
        ]);

        // 3. Respuesta al cliente
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

        // 1. Decodificar y validar el RT
        $decodedRt = $this->jwtService->decodeToken($refreshToken);

        if (!$decodedRt || $decodedRt->type !== 'refresh') {
            return $this->failUnauthorized('Invalid or expired Refresh Token.');
        }

        // 2. Verificar el JTI en la base de datos (Detección de Replay Attack)
        $jti = $decodedRt->jti;
        $rtRecord = $this->rtModel->where('jti', $jti)->first();

        if (!$rtRecord || $rtRecord['revoked'] == 1) {
            // CRÍTICO: Si el token es inválido/revocado, es un posible ataque.
            return $this->failUnauthorized('Refresh Token has been revoked or used.');
        }

        // 3. Ejecutar Rotación: Invalidar el JTI actual
        $this->rtModel->update($rtRecord['id'], ['revoked' => 1]);

        // 4. Generar el nuevo par de tokens
        $userId = $decodedRt->uid;
        $newTokens = $this->jwtService->generateTokenPair($userId);

        // 5. Persistir el NUEVO JTI
        $this->rtModel->insert([
            'user_id'    => $userId,
            'jti'        => $newTokens['jti'],
            'expires_at' => $newTokens['rt_expires_at'],
            'revoked'    => 0,
        ]);

        // 6. Respuesta con el nuevo par de tokens
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
        if ($refreshToken && ($decoded = $this->jwtService->decodeToken($refreshToken))) {
            $this->rtModel->where('jti', $decoded->jti)->set(['revoked' => 1])->update();
        }

        return $this->respond(['message' => 'Refresh token revoked.']);
    }
}