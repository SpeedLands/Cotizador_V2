<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private $secretKey;
    private $atTtl;
    private $rtTtl;

    public function __construct()
    {
        // Obtener configuración de .env
        $this->secretKey = getenv('JWT_SECRET_KEY');
        $this->atTtl = getenv('JWT_AT_TIME_TO_LIVE');
        $this->rtTtl = getenv('JWT_RT_TIME_TO_LIVE');
    }

    /**
     * Genera un Access Token (AT) y un Refresh Token (RT)
     */
    public function generateTokenPair(int $userId): array
    {
        $issuedAt = time();
        $jti = bin2hex(random_bytes(16)); // Generar JTI único

        // 1. Access Token (AT) - Corta duración
        $atPayload = [
            'iat'  => $issuedAt,
            'exp'  => $issuedAt + $this->atTtl,
            'uid'  => $userId,
            'type' => 'access',
        ];
        $accessToken = JWT::encode($atPayload, $this->secretKey, 'HS256');

        // 2. Refresh Token (RT) - Larga duración con JTI
        $rtPayload = [
            'iat'  => $issuedAt,
            'exp'  => $issuedAt + $this->rtTtl,
            'uid'  => $userId,
            'jti'  => $jti, // CRÍTICO: Identificador único para la rotación
            'type' => 'refresh',
        ];
        $refreshToken = JWT::encode($rtPayload, $this->secretKey, 'HS256');

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'jti'           => $jti,
            'rt_expires_at' => date('Y-m-d H:i:s', $issuedAt + $this->rtTtl),
        ];
    }

    /**
     * Decodifica y valida un token.
     */
    public function decodeToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            // Logear el error (ej. expiración, firma inválida)
            log_message('error', 'JWT Decode Error: ' . $e->getMessage());
            return null;
        }
    }
}