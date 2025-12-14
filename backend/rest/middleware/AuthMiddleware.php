<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../config/JwtConfig.php';

class AuthMiddleware
{
    public function validateToken()
    {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (!$authHeader) {
            Flight::json(['success' => false, 'error' => 'Authorization header missing'], 401);
            die;
        }

        list($jwt) = sscanf($authHeader, 'Bearer %s');

        if (!$jwt) {
            Flight::json(['success' => false, 'error' => 'Token missing'], 401);
            die;
        }

        try {
            $decoded = JWT::decode($jwt, new Key(JwtConfig::JWT_SECRET, JwtConfig::JWT_ALGORITHM));
            Flight::set('user', $decoded->data);
        } catch (Exception $e) {
            Flight::json(['success' => false, 'error' => 'Invalid token: ' . $e->getMessage()], 401);
            die;
        }
    }

    public function adminOnly()
    {
        $this->validateToken(); 
        $user = Flight::get('user');
        if (!$user || $user->role !== 'admin') {
            Flight::json(['success' => false, 'error' => 'Access denied. Admin only.'], 403);
            die;
        }
    }
}
?>