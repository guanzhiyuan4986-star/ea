<?php
/**
 * POST api/generate.php
 * 参数: account, expiry (YYYY-MM-DD), product, remark
 * 返回: {ok, id, license, account, expiry, product}
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$me = require_login(true);

$account = trim($_POST['account'] ?? '');
$expiry  = trim($_POST['expiry']  ?? '');
$product = trim($_POST['product'] ?? 'XAUUSD');
$remark  = trim($_POST['remark']  ?? '');
if (mb_strlen($remark) > 255) $remark = mb_substr($remark, 0, 255);

// 校验
if ($account === '' || !ctype_digit($account)) {
    json_response(['ok'=>false,'msg'=>'交易账号必须为纯数字'], 400);
}
$dt = DateTime::createFromFormat('Y-m-d', $expiry);
if (!$dt || $dt->format('Y-m-d') !== $expiry) {
    json_response(['ok'=>false,'msg'=>'日期格式必须为 YYYY-MM-DD'], 400);
}
$today = new DateTime('today');
if ($dt < $today) {
    json_response(['ok'=>false,'msg'=>'有效期不能早于今天'], 400);
}
if (!isset($PRODUCT_KEYS[$product])) {
    json_response(['ok'=>false,'msg'=>"不支持的产品：$product"], 400);
}

$xorKey = $PRODUCT_KEYS[$product];
$ymd    = $dt->format('Ymd');
$license = generate_license($account, $ymd, $xorKey);
$expiryDateStr = $dt->format('Y-m-d');

try {
    // ---- 去重：账号 + 有效期 + 产品 三元组若已存在，直接返回已有记录 ----
    $stmt = db()->prepare(
        "SELECT id, license_code, remark, created_by, created_at
         FROM licenses
         WHERE account = ? AND expiry_date = ? AND product = ?
         LIMIT 1"
    );
    $stmt->execute([$account, $expiryDateStr, $product]);
    $exist = $stmt->fetch();

    if ($exist) {
        json_response([
            'ok'        => true,
            'duplicate' => true,
            'id'        => (int)$exist['id'],
            'license'   => $exist['license_code'],
            'account'   => $account,
            'expiry'    => $expiryDateStr,
            'product'   => $product,
            'msg'       => '该 账号+有效期+产品 组合已存在，返回已有记录（未重复入库）',
            'existing'  => [
                'remark'     => $exist['remark'],
                'created_by' => $exist['created_by'],
                'created_at' => $exist['created_at'],
            ],
        ]);
    }

    // ---- 插入新记录 ----
    $stmt = db()->prepare(
        "INSERT INTO licenses (account, expiry_date, license_code, xor_key, product, remark, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $account,
        $expiryDateStr,
        $license,
        $xorKey,
        $product,
        $remark !== '' ? $remark : null,
        $me['username'],
    ]);
    $id = (int)db()->lastInsertId();
} catch (Throwable $e) {
    // 兜底：若并发竞争触发唯一索引冲突，则回查返回
    if (strpos($e->getMessage(), '1062') !== false) {
        $stmt = db()->prepare(
            "SELECT id, license_code FROM licenses
             WHERE account = ? AND expiry_date = ? AND product = ? LIMIT 1"
        );
        $stmt->execute([$account, $expiryDateStr, $product]);
        $exist = $stmt->fetch();
        if ($exist) {
            json_response([
                'ok'        => true,
                'duplicate' => true,
                'id'        => (int)$exist['id'],
                'license'   => $exist['license_code'],
                'account'   => $account,
                'expiry'    => $expiryDateStr,
                'product'   => $product,
                'msg'       => '该 账号+有效期+产品 组合已存在，返回已有记录',
            ]);
        }
    }
    json_response(['ok'=>false,'msg'=>'数据库写入失败：'.$e->getMessage()], 500);
}

json_response([
    'ok'        => true,
    'duplicate' => false,
    'id'        => $id,
    'license'   => $license,
    'account'   => $account,
    'expiry'    => $expiryDateStr,
    'product'   => $product,
]);
