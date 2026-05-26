# 金貔貅 EA · 授权管理后台

PHP + MySQL 实现的轻量后台，用于：

- 管理员登录（Session）
- 生成授权码（与 `金貔貅V1.11.mq5` / `金貔貅-EUR_V1.00.mq5` 内 `ValidateLicense` 算法对称）
- 查看 / 搜索 / 删除已生成的授权码记录

## 一、目录结构

```
build/
├── index.php                  # 入口（重定向到登录或后台）
├── login.php                  # 登录页
├── logout.php                 # 退出
├── admin.php                  # 后台主页（生成授权码 + 列表）
├── install.php                # 一键初始化（建库建表 + 默认管理员）
├── schema.sql                 # 表结构 SQL
├── api/
│   ├── generate.php           # 生成授权码（POST）
│   ├── list.php               # 授权码列表（GET）
│   ├── delete.php             # 删除（DELETE）
│   └── change_password.php    # 修改密码（POST）
└── includes/
    ├── config.php             # 数据库 / 密钥配置（部署时务必修改）
    ├── db.php                 # PDO 连接 + 授权码生成函数
    ├── auth.php               # 登录态
    └── .htaccess              # 禁止 includes 被外部直接访问
```

## 二、运行环境

- PHP **7.4+**（已用 PDO、`password_hash`、`mb_*`，建议 PHP 8.x）
- MySQL **5.7+** 或 MariaDB 10+
- Apache / Nginx（任意）

## 三、部署步骤

### 1. 修改配置

编辑 `build/includes/config.php`：

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '你的MySQL密码');
define('DB_NAME', 'jinpixiu');

define('SESSION_SECRET', '换一个32位以上的随机串');

// 与 mq5 内 LICENSE_XOR_KEY 必须保持一致：
$PRODUCT_KEYS = [
    'XAUUSD' => 'JPX2025GoldEA!@#',
    'EURUSD' => 'JPXEUR2026GridEA!@#',
];
```

### 2. 初始化数据库

浏览器访问：

```
http://你的域名/build/install.php
```

会自动：

- 创建数据库 `jinpixiu`
- 创建 `admins`、`licenses` 表
- 写入默认管理员 `admin / admin123`

> 安装成功后**请立刻删除** `install.php`，并登录后修改默认密码。

### 3. 登录

```
http://你的域名/build/login.php
```

默认账号：`admin` / `admin123`（登录后立即修改）

## 四、授权码生成算法

与 EA 端 `ValidateLicense` 完全对称：

```
plain  = "账号|YYYYMMDD"
cipher = XOR(plain, KEY)         // KEY 按字节循环异或
license= base64(cipher)
```

EA 端用同样 KEY 反向解码即可校验。

## 五、API 一览（均需登录态）

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `api/generate.php` | 参数 `account, expiry(YYYY-MM-DD), product, remark` |
| GET  | `api/list.php` | 参数 `page, size, q` 模糊搜索账号/备注/授权码 |
| DELETE | `api/delete.php?id=xxx` | 删除一条记录 |
| POST | `api/change_password.php` | 参数 `old, new` |

## 六、安全建议

1. **HTTPS** 部署（避免登录态被劫持）
2. 部署完成后**删除 `install.php`**
3. 修改 `SESSION_SECRET` 为高强度随机串
4. `includes/.htaccess` 已禁止外部直接访问（仅 Apache 生效，Nginx 需自行配置）
5. 数据库账号建议使用最小权限的专用账号，而非 root

### Nginx 屏蔽 includes 示例

```nginx
location ^~ /build/includes/ {
    deny all;
    return 403;
}
```

## 七、常见问题

**Q：装完后登录提示"系统错误"？**  
A：检查 `config.php` 里的 MySQL 连接信息，并确认 `install.php` 已成功跑过一次。

**Q：生成的授权码 EA 端校验失败？**  
A：核对 `$PRODUCT_KEYS` 中对应产品的密钥是否和 mq5 中 `LICENSE_XOR_KEY` **逐字节一致**（包括大小写和特殊符号）。

**Q：能否直接复用现有 `site/` 站点的导航跳到后台？**  
A：可以，在 `site/index.html` 的导航或 footer 加一个 `<a href="../build/login.php">管理员入口</a>` 即可。
