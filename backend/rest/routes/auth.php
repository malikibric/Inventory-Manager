<?php
use Firebase\JWT\JWT;

require_once __DIR__ . '/../../config/JwtConfig.php';
require_once __DIR__ . '/../../services/UserService.php';
require_once __DIR__ . '/../../dao/UserDao.php';

Flight::route('POST /login', function () {
    $data = json_decode(Flight::request()->getBody(), true);

    if (!isset($data['email']) || !isset($data['password'])) {
        Flight::json(['success' => false, 'error' => 'Email and password are required'], 400);
        return;
    }

    $dao = new UserDao();
    $user = $dao->getByEmail($data['email']);

    if (!$user) {
        error_log("Login failed: User not found for email: " . $data['email']);
        Flight::json(['success' => false, 'error' => 'Invalid email or password'], 401);
        return;
    }

    // Verify password
    $passwordValid = password_verify($data['password'], $user['password_hash']);
    error_log("Password verification for " . $data['email'] . ": " . ($passwordValid ? 'SUCCESS' : 'FAILED'));
    error_log("Provided password length: " . strlen($data['password']));
    error_log("Stored hash: " . substr($user['password_hash'], 0, 20) . "...");

    if (!$passwordValid) {
        Flight::json(['success' => false, 'error' => 'Invalid email or password'], 401);
        return;
    }

    $payload = [
        'iss' => 'http://localhost',
        'aud' => 'http://localhost',
        'iat' => time(),
        'exp' => time() + JwtConfig::JWT_EXPIRATION,
        'data' => [
            'id' => $user['user_id'],
            'username' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ];

    $jwt = JWT::encode($payload, JwtConfig::JWT_SECRET, JwtConfig::JWT_ALGORITHM);

    Flight::json([
        'success' => true,
        'token' => $jwt,
        'user' => [
            'id' => $user['user_id'],
            'username' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ], 200);
});
?>