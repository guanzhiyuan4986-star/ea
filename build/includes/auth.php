<?php
/**
 * 登录态管理
 */
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('JPX_ADMIN_SID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_admin(): ?array {
    if (!empty($_SESSION['admin_id'])) {
        return [
            'id'       => (int)$_SESSION['admin_id'],
            'username' => (string)($_SESSION['admin_username'] ?? ''),
        ];
    }
    return null;
}

function require_login(bool $isApi = false): array {
    $u = current_admin();
    if (!$u) {
        if ($isApi) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok'=>false,'msg'=>'未登录'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $next = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/admin.php';
        header('Location: login.php?next=' . urlencode($next));
        exit;
    }
    return $u;
}

function login_admin(int $id, string $username): void {
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $id;
    $_SESSION['admin_username'] = $username;
}

function logout_admin(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
