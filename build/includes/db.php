<?php
/**
 * MySQL PDO 连接 + 公用工具
 */
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST, DB_PORT, DB_NAME);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('数据库连接失败: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/**
 * 生成授权码（与 mq5 中 ValidateLicense 算法对称）
 *   plain = "account|YYYYMMDD"
 *   cipher = XOR(plain, key)
 *   return = base64(cipher)
 */
function generate_license(string $account, string $expiryYmd, string $xorKey): string {
    $plain = $account . '|' . $expiryYmd;
    $klen  = strlen($xorKey);
    $out   = '';
    for ($i = 0, $n = strlen($plain); $i < $n; $i++) {
        $out .= chr(ord($plain[$i]) ^ ord($xorKey[$i % $klen]));
    }
    return base64_encode($out);
}

/**
 * JSON 响应
 */
function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
