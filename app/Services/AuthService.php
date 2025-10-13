<?php

namespace App\Services;

use App\Libraries\JwtService;
use App\Models\RefreshTokenModel;
use App\Models\AdminUserModel;

class AuthService
{
    private JwtService $jwtService;
    private RefreshTokenModel $rtModel;
    private AdminUserModel $userModel;

    public function __construct(JwtService $jwtService = null, RefreshTokenModel $rtModel = null, AdminUserModel $userModel = null)
    {
        $this->jwtService = $jwtService ?? new JwtService();
        $this->rtModel = $rtModel ?? new RefreshTokenModel();
        $this->userModel = $userModel ?? new AdminUserModel();
    }

    /**
     * Intenta autenticar y devuelve tokens o false
     *
     * @param array $credentials ['email' => '', 'password' => '']
     * @return array|false
     */
    public function login(array $credentials)
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;
        if (!$email || !$password) return false;

        $user = $this->userModel->where('email', $email)->first();
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        $userId = $user['id'];
        $tokens = $this->jwtService->generateTokenPair($userId);

        $this->rtModel->insert([
            'user_id' => $userId,
            'jti' => $tokens['jti'],
            'expires_at' => $tokens['rt_expires_at'],
            'revoked' => 0,
        ]);

        return $tokens;
    }

    /**
     * Refresca el token y retorna nuevos tokens o false
     *
     * @param string $refreshToken
     * @return array|false
     */
    public function refresh(string $refreshToken)
    {
        $decoded = $this->jwtService->decodeToken($refreshToken);
        if (!$decoded || $decoded->type !== 'refresh') return false;

        $jti = $decoded->jti;
        $record = $this->rtModel->where('jti', $jti)->first();
        if (!$record || $record['revoked'] == 1) return false;

        // Revoke current
        $this->rtModel->update($record['id'], ['revoked' => 1]);

        $userId = $decoded->uid;
        $tokens = $this->jwtService->generateTokenPair($userId);

        $this->rtModel->insert([
            'user_id' => $userId,
            'jti' => $tokens['jti'],
            'expires_at' => $tokens['rt_expires_at'],
            'revoked' => 0,
        ]);

        return $tokens;
    }

    /**
     * Revoca (marca como revoked) un Refresh Token dado su valor.
     * Usado por el endpoint de logout de la API.
     *
     * @param string $refreshToken
     * @return bool True si se revocó, false en caso contrario
     */
    public function revoke(string $refreshToken): bool
    {
        $decoded = $this->jwtService->decodeToken($refreshToken);
        if (!$decoded || $decoded->type !== 'refresh') return false;

        $jti = $decoded->jti;
        $record = $this->rtModel->where('jti', $jti)->first();
        if (!$record) return false;

        $this->rtModel->update($record['id'], ['revoked' => 1]);
        return true;
    }
}
