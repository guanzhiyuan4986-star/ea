<?php
/**
 * DELETE api/delete.php?id=123
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_login(true);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    json_response(['ok'=>false,'msg'=>'参数错误'], 400);
}

try {
    $stmt = db()->prepare("DELETE FROM licenses WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        json_response(['ok'=>false,'msg'=>'记录不存在'], 404);
    }
} catch (Throwable $e) {
    json_response(['ok'=>false,'msg'=>'删除失败：'.$e->getMessage()], 500);
}

json_response(['ok'=>true]);
