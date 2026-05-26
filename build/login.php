<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');
    if ($u === '' || $p === '') {
        $err = '请输入账号和密码';
    } else {
        try {
            $stmt = db()->prepare("SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$u]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($p, $row['password_hash'])) {
                $err = '账号或密码错误';
            } else {
                login_admin((int)$row['id'], $row['username']);
                db()->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = ?")
                    ->execute([$row['id']]);
                $next = $_GET['next'] ?? 'admin.php';
                if (!preg_match('#^/?[\w\-/.?=&%]+$#', $next)) $next = 'admin.php';
                header('Location: ' . $next);
                exit;
            }
        } catch (Throwable $e) {
            $err = '系统错误，请检查后台是否已安装：' . htmlspecialchars($e->getMessage());
        }
    }
}

if (current_admin()) { header('Location: admin.php'); exit; }
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>金貔貅 · 后台登录</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700;900&display=swap');
  :root{
    --gold:#d4a745;--gold-light:#f0d68a;--gold-dark:#8b6914;
    --dark:#0a0a0f;--card:rgba(20,20,35,0.85);
    --text:#e0e0e8;--text2:#a0a0b8;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{
    font-family:'Noto Sans SC',sans-serif;background:var(--dark);color:var(--text);
    min-height:100vh;display:flex;align-items:center;justify-content:center;
    background-image:radial-gradient(ellipse at 50% 30%,rgba(212,167,69,0.10),transparent 60%);
  }
  .login-card{
    width:380px;background:var(--card);
    border:1px solid rgba(255,255,255,0.08);border-radius:20px;
    padding:42px 36px;box-shadow:0 20px 60px rgba(0,0,0,0.5);
    backdrop-filter:blur(20px);
  }
  .login-logo{text-align:center;margin-bottom:24px;}
  .login-logo img{height:64px;filter:drop-shadow(0 0 24px rgba(212,167,69,0.5));}
  .login-logo h1{
    font-size:24px;font-weight:700;letter-spacing:6px;margin-top:10px;
    background:linear-gradient(135deg,var(--gold-light),var(--gold),var(--gold-dark));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  }
  .login-logo p{font-size:12px;color:var(--text2);letter-spacing:3px;margin-top:4px;}
  .field{margin-bottom:18px;}
  .field label{display:block;font-size:12px;color:var(--text2);
       letter-spacing:2px;margin-bottom:6px;text-transform:uppercase;}
  .field input{
    width:100%;padding:12px 14px;background:rgba(0,0,0,0.35);
    border:1px solid rgba(255,255,255,0.08);border-radius:10px;
    color:var(--text);font-size:14px;font-family:Consolas,monospace;
    letter-spacing:1px;transition:border-color .3s,box-shadow .3s;
  }
  .field input:focus{outline:none;border-color:var(--gold);
       box-shadow:0 0 0 3px rgba(212,167,69,0.15);}
  .btn-submit{
    width:100%;padding:13px;margin-top:6px;
    background:linear-gradient(135deg,var(--gold),var(--gold-dark));
    color:#000;border:0;border-radius:10px;cursor:pointer;
    font-size:15px;font-weight:700;letter-spacing:4px;
    transition:transform .2s,box-shadow .3s;
  }
  .btn-submit:hover{transform:translateY(-1px);
       box-shadow:0 8px 24px rgba(212,167,69,0.35);}
  .err{background:rgba(224,64,64,0.12);border:1px solid rgba(224,64,64,0.4);
       color:#ff8080;font-size:13px;padding:10px 14px;border-radius:8px;
       margin-bottom:16px;text-align:center;}
  .tip{text-align:center;font-size:11px;color:#555;margin-top:18px;letter-spacing:1px;}
</style>
</head>
<body>
<div class="login-card">
  <div class="login-logo">
    <img src="../site/images/logo.png" alt="金貔貅" onerror="this.style.display='none'">
    <h1>金 貔 貅</h1>
    <p>ADMIN BACKEND</p>
  </div>

  <?php if ($err): ?>
    <div class="err"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <div class="field">
      <label>账号</label>
      <input type="text" name="username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="field">
      <label>密码</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn-submit">登 录</button>
  </form>

  <div class="tip">&copy; 2026 金貔貅 EA · 仅限授权管理员</div>
</div>
</body>
</html>
