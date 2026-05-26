<?php
/**
 * POST api/change_password.php
 * 参数: old, new
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$me = require_login(true);

$old = (string)($_POST['old'] ?? '');
$new = (string)($_POST['new'] ?? '');

if (strlen($new) < 6) {
    json_response(['ok'=>false,'msg'=>'新密码长度至少 6 位'], 400);
}

try {
    $stmt = db()->prepare("SELECT password_hash FROM admins WHERE id = ?");
    $stmt->execute([$me['id']]);
    $hash = $stmt->fetchColumn();
    if (!$hash || !password_verify($old, $hash)) {
        json_response(['ok'=>false,'msg'=>'原密码错误'], 400);
    }
    db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")
        ->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
} catch (Throwable $e) {
    json_response(['ok'=>false,'msg'=>'修改失败：'.$e->getMessage()], 500);
}

json_response(['ok'=>true]);
