<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

require_auth();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$payload = file_get_contents('php://input');
if ($payload === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Solicitud inválida']);
    exit;
}

try {
    $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Solicitud inválida']);
    exit;
}

$action = (string) ($data['action'] ?? '');

if ($action !== 'change_password') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Acción inválida']);
    exit;
}

$user = current_user();
if ($user === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Sesión inválida']);
    exit;
}

$currentPassword = (string) ($data['current_password'] ?? '');
$newPassword = (string) ($data['new_password'] ?? '');
$confirmPassword = (string) ($data['confirm_password'] ?? '');

if (!isset($user['password_hash']) || !password_verify($currentPassword, (string) $user['password_hash'])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'La contraseña actual es incorrecta.']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'La nueva contraseña y la confirmación no coinciden.']);
    exit;
}

if (
    strlen($newPassword) < 10
    || !preg_match('/[A-Z]/', $newPassword)
    || !preg_match('/[a-z]/', $newPassword)
    || !preg_match('/[0-9]/', $newPassword)
    || !preg_match('/[^A-Za-z0-9]/', $newPassword)
) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'La nueva contraseña debe tener al menos 10 caracteres, mayúscula, minúscula, número y símbolo.']);
    exit;
}

$users = all_users();
foreach ($users as &$storedUser) {
    if ((string) ($storedUser['id'] ?? '') === (string) ($user['id'] ?? '')) {
        $storedUser['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        break;
    }
}
unset($storedUser);

if (!save_users($users)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo actualizar la contraseña.']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Contraseña actualizada correctamente.']);
