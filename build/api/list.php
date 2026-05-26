<?php
/**
 * GET api/list.php?page=1&size=20&q=xxx
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_login(true);

$page = max(1, (int)($_GET['page'] ?? 1));
$size = min(100, max(1, (int)($_GET['size'] ?? 20)));
$kw   = trim($_GET['q'] ?? '');
$offset = ($page - 1) * $size;

$where = 'WHERE 1=1';
$params = [];
if ($kw !== '') {
    $where .= ' AND (account LIKE ? OR remark LIKE ? OR license_code LIKE ?)';
    $like = '%' . $kw . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}

try {
    $stmt = db()->prepare("SELECT COUNT(*) FROM licenses $where");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $sql = "SELECT id, account, expiry_date, license_code, product, remark,
                   created_by, created_at
            FROM licenses $where
            ORDER BY id DESC LIMIT $size OFFSET $offset";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    json_response(['ok'=>false,'msg'=>'查询失败：'.$e->getMessage()], 500);
}

$today = new DateTime('today');
foreach ($rows as &$r) {
    $exp = new DateTime($r['expiry_date']);
    $diff = (int)$today->diff($exp)->format('%r%a');
    if ($diff < 0)       $r['status'] = 'expired';
    elseif ($diff <= 7)  $r['status'] = 'soon';
    else                 $r['status'] = 'active';
}
unset($r);

json_response([
    'ok'    => true,
    'total' => $total,
    'page'  => $page,
    'size'  => $size,
    'items' => $rows,
]);
