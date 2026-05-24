<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
$me = require_login();
$products = array_keys($PRODUCT_KEYS);
$default_expiry = (new DateTime('+30 days'))->format('Y-m-d');
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>金貔貅 · 授权管理后台</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700;900&display=swap');
  :root{
    --gold:#d4a745;--gold-light:#f0d68a;--gold-dark:#8b6914;
    --dark:#0a0a0f;--dark2:#12121a;--dark3:#1a1a28;
    --card:rgba(20,20,35,0.85);
    --text:#e0e0e8;--text2:#a0a0b8;
    --green:#5fc488;--red:#e04040;--orange:#e2a93c;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Noto Sans SC',sans-serif;background:var(--dark);color:var(--text);
       min-height:100vh;line-height:1.7;}

  /* 顶部 */
  nav{position:sticky;top:0;z-index:100;
       display:flex;align-items:center;justify-content:space-between;
       padding:14px 32px;background:rgba(10,10,18,0.95);
       backdrop-filter:blur(20px);
       border-bottom:1px solid rgba(212,167,69,0.18);}
  nav .brand{display:flex;align-items:center;gap:12px;font-weight:700;
       font-size:18px;color:var(--gold);letter-spacing:2px;}
  nav .brand img{height:32px;}
  nav .right{display:flex;align-items:center;gap:18px;font-size:13px;}
  nav .right .user{color:var(--text2);}
  nav .right a{color:var(--gold);text-decoration:none;
       border:1px solid rgba(212,167,69,0.35);padding:6px 14px;
       border-radius:50px;transition:all .3s;}
  nav .right a:hover{background:var(--gold);color:#000;}

  main{max-width:1280px;margin:0 auto;padding:32px;}

  /* 卡片 */
  .card{background:var(--card);border:1px solid rgba(255,255,255,0.06);
       border-radius:20px;padding:28px 32px;margin-bottom:24px;}
  .card h2{font-size:18px;color:#fff;margin-bottom:18px;
       display:flex;align-items:center;gap:10px;}
  .card h2::before{content:'';width:4px;height:18px;background:var(--gold);
       border-radius:3px;}

  /* 生成表单 */
  .gen-grid{display:grid;grid-template-columns:repeat(4,1fr) auto;gap:14px;align-items:end;}
  @media(max-width:900px){.gen-grid{grid-template-columns:1fr 1fr;}}
  .field label{display:block;font-size:12px;color:var(--text2);
       letter-spacing:1px;margin-bottom:6px;text-transform:uppercase;}
  .field input,.field select{
    width:100%;padding:11px 12px;background:rgba(0,0,0,0.3);
    border:1px solid rgba(255,255,255,0.08);border-radius:10px;
    color:var(--text);font-size:14px;font-family:Consolas,monospace;
    transition:border-color .3s;
  }
  .field input:focus,.field select:focus{outline:none;border-color:var(--gold);}
  .field select{cursor:pointer;}
  .btn{padding:11px 24px;border:0;border-radius:10px;cursor:pointer;
       font-size:14px;font-weight:600;letter-spacing:2px;
       transition:transform .2s,box-shadow .3s;font-family:inherit;}
  .btn-gold{background:linear-gradient(135deg,var(--gold),var(--gold-dark));color:#000;}
  .btn-gold:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(212,167,69,0.4);}
  .btn-blue{background:rgba(60,140,220,0.18);color:#7fbfff;
       border:1px solid rgba(60,140,220,0.4);}
  .btn-blue:hover{background:rgba(60,140,220,0.28);}
  .btn-red{background:rgba(224,64,64,0.15);color:#ff8080;
       border:1px solid rgba(224,64,64,0.4);}
  .btn-red:hover{background:rgba(224,64,64,0.25);}
  .btn-sm{padding:6px 12px;font-size:12px;letter-spacing:1px;}

  /* 生成结果 */
  .result-box{margin-top:20px;padding:18px;border-radius:12px;
       background:rgba(95,196,136,0.08);border:1px solid rgba(95,196,136,0.3);
       display:none;}
  .result-box.show{display:block;}
  .result-box.err{background:rgba(224,64,64,0.10);border-color:rgba(224,64,64,0.35);}
  .result-box .lbl{font-size:12px;color:var(--text2);margin-bottom:6px;}
  .result-box .lic{font-family:Consolas,monospace;font-size:14px;
       color:var(--gold-light);word-break:break-all;
       padding:10px 12px;background:rgba(0,0,0,0.4);
       border-radius:8px;margin-bottom:10px;}

  /* 工具条 */
  .toolbar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;
       margin-bottom:18px;}
  .toolbar input{flex:1;min-width:200px;padding:10px 14px;
       background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.08);
       border-radius:10px;color:var(--text);font-size:13px;}
  .toolbar input:focus{outline:none;border-color:var(--gold);}
  .stats{display:flex;gap:18px;font-size:13px;color:var(--text2);}
  .stats b{color:var(--gold);font-weight:700;}

  /* 表格 */
  .table-wrap{overflow-x:auto;border-radius:12px;
       border:1px solid rgba(255,255,255,0.05);}
  table{width:100%;border-collapse:collapse;font-size:13px;}
  thead th{padding:12px 14px;text-align:left;
       background:rgba(0,0,0,0.4);color:var(--text2);
       font-size:11px;text-transform:uppercase;letter-spacing:1px;
       border-bottom:1px solid rgba(255,255,255,0.06);}
  tbody td{padding:12px 14px;
       border-bottom:1px solid rgba(255,255,255,0.04);}
  tbody tr:hover{background:rgba(212,167,69,0.05);}
  td .lic-cell{font-family:Consolas,monospace;font-size:12px;
       color:var(--gold-light);max-width:280px;overflow:hidden;
       text-overflow:ellipsis;white-space:nowrap;cursor:pointer;
       display:inline-block;vertical-align:middle;}
  .tag{display:inline-block;padding:3px 10px;border-radius:50px;
       font-size:11px;letter-spacing:1px;}
  .tag.active{background:rgba(95,196,136,0.15);color:var(--green);}
  .tag.soon{background:rgba(226,169,60,0.15);color:var(--orange);}
  .tag.expired{background:rgba(224,64,64,0.15);color:var(--red);}
  .tag.product{background:rgba(60,140,220,0.15);color:#7fbfff;}

  /* 分页 */
  .pagination{display:flex;justify-content:center;align-items:center;
       gap:10px;margin-top:20px;font-size:13px;color:var(--text2);}
  .pagination button{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);
       color:var(--text);padding:6px 14px;border-radius:8px;cursor:pointer;}
  .pagination button:disabled{opacity:0.3;cursor:not-allowed;}
  .pagination button:not(:disabled):hover{border-color:var(--gold);color:var(--gold);}

  .empty{text-align:center;padding:40px;color:var(--text2);font-size:14px;}
  .toast{position:fixed;top:30px;right:30px;padding:12px 20px;border-radius:10px;
       background:var(--card);border:1px solid rgba(95,196,136,0.4);
       color:var(--green);z-index:9999;display:none;font-size:13px;
       box-shadow:0 8px 24px rgba(0,0,0,0.4);}
  .toast.show{display:block;animation:slide .3s;}
  .toast.err{border-color:rgba(224,64,64,0.4);color:#ff8080;}
  @keyframes slide{from{transform:translateX(20px);opacity:0;}to{transform:translateX(0);opacity:1;}}
</style>
</head>
<body>

<nav>
  <div class="brand">
    <img src="../site/images/logo.png" alt="" onerror="this.style.display='none'">
    金貔貅 · 授权管理后台
  </div>
  <div class="right">
    <span class="user">👤 <?= htmlspecialchars($me['username']) ?></span>
    <a href="javascript:void(0)" onclick="changePassword()">修改密码</a>
    <a href="logout.php">退出登录</a>
  </div>
</nav>

<main>

  <!-- 生成授权码 -->
  <div class="card">
    <h2>🔑 生成授权码</h2>
    <form id="genForm" class="gen-grid">
      <div class="field">
        <label>交易账号</label>
        <input type="text" name="account" placeholder="例如 165447734" pattern="\d+" required>
      </div>
      <div class="field">
        <label>有效期至</label>
        <input type="date" name="expiry" value="<?= $default_expiry ?>" required>
      </div>
      <div class="field">
        <label>产品</label>
        <select name="product">
          <?php foreach ($products as $p): ?>
            <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>备注（客户名 / TG 等）</label>
        <input type="text" name="remark" maxlength="255" placeholder="可选">
      </div>
      <button type="submit" class="btn btn-gold">生成授权码</button>
    </form>

    <div id="result" class="result-box">
      <div class="lbl">✓ 已生成授权码（已自动入库）</div>
      <div class="lic" id="resultLic"></div>
      <button class="btn btn-blue btn-sm" onclick="copyResult()">复制到剪贴板</button>
    </div>
  </div>

  <!-- 列表 -->
  <div class="card">
    <h2>📋 授权码记录</h2>
    <div class="toolbar">
      <input type="text" id="kw" placeholder="🔍 搜索：账号 / 备注 / 授权码片段">
      <button class="btn btn-blue btn-sm" onclick="loadList(1)">搜 索</button>
      <div class="stats">总数：<b id="total">0</b></div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>账号</th>
            <th>产品</th>
            <th>有效期</th>
            <th>状态</th>
            <th>授权码</th>
            <th>备注</th>
            <th>生成人</th>
            <th>生成时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>
    <div class="pagination">
      <button id="btnPrev" onclick="loadList(currentPage - 1)">‹ 上一页</button>
      <span>第 <b id="curPage">1</b> 页 / 共 <b id="pages">1</b> 页</span>
      <button id="btnNext" onclick="loadList(currentPage + 1)">下一页 ›</button>
    </div>
  </div>

</main>

<div id="toast" class="toast"></div>

<script>
const PAGE_SIZE = 20;
let currentPage = 1;

function toast(msg, isErr=false){
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.toggle('err', isErr);
  t.classList.add('show');
  clearTimeout(t._tid);
  t._tid = setTimeout(()=>t.classList.remove('show'), 2400);
}

function escapeHtml(s){
  if(s==null) return '';
  return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ---- 生成 ----
document.getElementById('genForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const body = new URLSearchParams();
  fd.forEach((v,k)=>body.append(k,v));
  try{
    const r = await fetch('api/generate.php', {method:'POST', body});
    const j = await r.json();
    const box = document.getElementById('result');
    if(!j.ok){
      box.className = 'result-box show err';
      box.innerHTML = '<div class="lbl">✗ 生成失败</div><div class="lic" style="color:#ff8080;">'+escapeHtml(j.msg||'未知错误')+'</div>';
      return;
    }
    box.className = 'result-box show';
    const dupTip = j.duplicate
      ? '<div class="lbl" style="color:#e2a93c;">⚠ 该 账号+有效期+产品 组合在数据库中已存在，下面为已有授权码（未重复入库 ID #'+j.id+'）</div>'
      : '<div class="lbl">✓ 已生成授权码（已自动入库 ID #'+j.id+'）</div>';
    box.innerHTML = dupTip
                  + '<div class="lic" id="resultLic">'+escapeHtml(j.license)+'</div>'
                  + '<button class="btn btn-blue btn-sm" onclick="copyResult()">复制到剪贴板</button>';
    toast(j.duplicate ? ('返回已有记录 ID #'+j.id) : ('授权码已生成 (ID #'+j.id+')'));
    loadList(1);
  }catch(err){ toast('请求失败：'+err.message, true); }
});

function copyResult(){
  const lic = document.getElementById('resultLic').textContent;
  navigator.clipboard.writeText(lic).then(()=>toast('已复制到剪贴板')).catch(()=>toast('复制失败',true));
}

// ---- 列表 ----
async function loadList(page){
  if(page<1) return;
  currentPage = page;
  const kw = document.getElementById('kw').value.trim();
  const url = `api/list.php?page=${page}&size=${PAGE_SIZE}&q=${encodeURIComponent(kw)}`;
  try{
    const r = await fetch(url);
    const j = await r.json();
    if(!j.ok){ toast(j.msg||'加载失败', true); return; }
    document.getElementById('total').textContent = j.total;
    document.getElementById('curPage').textContent = j.page;
    const pages = Math.max(1, Math.ceil(j.total / j.size));
    document.getElementById('pages').textContent = pages;
    document.getElementById('btnPrev').disabled = j.page <= 1;
    document.getElementById('btnNext').disabled = j.page >= pages;

    const tbody = document.getElementById('tbody');
    if(j.items.length === 0){
      tbody.innerHTML = '<tr><td colspan="10" class="empty">— 暂无数据 —</td></tr>';
      return;
    }
    tbody.innerHTML = j.items.map(it => {
      const tagCls = it.status; // active / soon / expired
      const tagText = it.status==='active' ? '生效中' : it.status==='soon' ? '即将到期' : '已过期';
      return `<tr>
        <td>${it.id}</td>
        <td><b>${escapeHtml(it.account)}</b></td>
        <td><span class="tag product">${escapeHtml(it.product)}</span></td>
        <td>${escapeHtml(it.expiry_date)}</td>
        <td><span class="tag ${tagCls}">${tagText}</span></td>
        <td><span class="lic-cell" title="点击复制" onclick="copyLic(this)" data-lic="${escapeHtml(it.license_code)}">${escapeHtml(it.license_code)}</span></td>
        <td>${escapeHtml(it.remark||'')}</td>
        <td>${escapeHtml(it.created_by)}</td>
        <td>${escapeHtml(it.created_at)}</td>
        <td>
          <button class="btn btn-blue btn-sm" onclick='copyLicById(${it.id}, ${JSON.stringify(it.license_code)})'>复制</button>
          <button class="btn btn-red btn-sm" onclick="delItem(${it.id})">删除</button>
        </td>
      </tr>`;
    }).join('');
  }catch(err){ toast('请求失败：'+err.message, true); }
}

function copyLic(el){
  const v = el.getAttribute('data-lic');
  navigator.clipboard.writeText(v).then(()=>toast('已复制授权码')).catch(()=>toast('复制失败',true));
}
function copyLicById(id, lic){
  navigator.clipboard.writeText(lic).then(()=>toast('已复制 ID #'+id)).catch(()=>toast('复制失败',true));
}

async function delItem(id){
  if(!confirm('确定删除 ID #'+id+' 这条授权记录吗？\n（仅删除后台记录，已下发的授权码仍可用）')) return;
  try{
    const r = await fetch('api/delete.php?id='+id, {method:'DELETE'});
    const j = await r.json();
    if(!j.ok){ toast(j.msg||'删除失败', true); return; }
    toast('已删除');
    loadList(currentPage);
  }catch(err){ toast('请求失败：'+err.message, true); }
}

// 回车搜索
document.getElementById('kw').addEventListener('keydown', e => {
  if(e.key === 'Enter') loadList(1);
});

// ---- 修改密码 ----
async function changePassword(){
  const oldP = prompt('请输入【原密码】');
  if(!oldP) return;
  const newP = prompt('请输入【新密码】（至少 6 位）');
  if(!newP) return;
  if(newP.length < 6){ toast('新密码至少 6 位', true); return; }
  const newP2 = prompt('请再次输入新密码确认');
  if(newP !== newP2){ toast('两次输入的新密码不一致', true); return; }
  try{
    const body = new URLSearchParams({old:oldP, new:newP});
    const r = await fetch('api/change_password.php', {method:'POST', body});
    const j = await r.json();
    if(!j.ok){ toast(j.msg||'修改失败', true); return; }
    toast('密码已更新');
  }catch(err){ toast('请求失败：'+err.message, true); }
}

// 首次加载
loadList(1);
</script>
</body>
</html>
