<?php
/**
 * 金貔貅 EA 后台配置
 * 部署后请修改本文件 / 或保持默认通过 install.php 完成初始化
 */

// ===== MySQL 配置 =====
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');             // 改为你的 MySQL 密码
define('DB_NAME', 'jinpixiu');

// ===== Session 安全 =====
define('SESSION_SECRET', 'CHANGE_ME_TO_A_RANDOM_LONG_STRING_xxxxxxxxxxxxxxxx');

// ===== 授权码 XOR 密钥（必须与 mq5 中 LICENSE_XOR_KEY 完全一致）=====
// 金貔貅V1.11 (XAUUSD) -> JPX2025GoldEA!@#
// 金貔貅-EUR (EURUSD) -> JPXEUR2026GridEA!@#
$PRODUCT_KEYS = [
    'XAUUSD' => 'JPX2025GoldEA!@#',
    'EURUSD' => 'JPXEUR2026GridEA!@#',
];

// ===== 默认管理员（仅 install.php 首次写入用）=====
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'admin123');

// ===== 时区 =====
date_default_timezone_set('Asia/Shanghai');

// ===== 错误显示（生产环境建议 0）=====
ini_set('display_errors', '0');
error_reporting(E_ALL);
