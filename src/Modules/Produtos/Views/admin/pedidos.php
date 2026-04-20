<?php
/** @var array $pedidos */

// Flatten all orders into a JS-accessible map
$allOrders = array_merge(
    array_values($pedidos['novos']),
    array_values($pedidos['concluidos']),
    array_values($pedidos['cancelados'])
);
$ordersMap = [];
foreach ($allOrders as $o) {
    $ordersMap[$o['id']] = $o;
}
?>

<style>
.ped-tab-nav { display:flex; border-bottom:1px solid var(--border); padding:0 20px; }
.ped-tab-btn {
    padding:14px 20px; border:none; background:none; cursor:pointer; font-weight:700;
    font-size:13px; color:var(--text-muted); position:relative; transition:.2s;
}
.ped-tab-btn.active { color:var(--primary); }
.ped-tab-btn.active::after {
    content:''; position:absolute; bottom:0; left:15%; right:15%; height:3px;
    background:var(--primary); border-radius:4px 4px 0 0;
}
.ped-tab-count { background:rgba(var(--primary-rgb),.12); color:var(--primary); font-size:10px; padding:2px 7px; border-radius:20px; margin-left:5px; }
.ped-panel { display:none; }
.ped-panel.active { display:block; }

/* Table */
.ped-tbl { width:100%; border-collapse:collapse; }
.ped-tbl th { padding:10px 14px; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); border-bottom:1px solid var(--border); text-align:left; white-space:nowrap; background:rgba(var(--primary-rgb),.025); }
.ped-tbl td { padding:13px 14px; border-bottom:1px solid var(--border); vertical-align:middle; font-size:13px; }
.ped-tbl tbody tr:last-child td { border-bottom:none; }
.ped-tbl tbody tr:hover td { background:rgba(var(--primary-rgb),.02); }

/* Gear button */
.gear-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 12px; background:rgba(var(--primary-rgb),.07); border:1px solid var(--border); border-radius:10px; cursor:pointer; font-size:12px; font-weight:700; color:var(--text-main); transition:.2s; }
.gear-btn:hover { background:var(--primary); color:#000; border-color:var(--primary); }

/* Floating dropdown */
#pdd { position:fixed; z-index:999999; background:var(--bg-card); border:1px solid var(--border); border-radius:14px; box-shadow:0 16px 48px rgba(0,0,0,.4); min-width:185px; display:none; overflow:hidden; }
#pdd.show { display:block; }
#pdd a { display:flex; align-items:center; gap:9px; padding:11px 15px; text-decoration:none; color:var(--text-main); font-size:13px; font-weight:600; border-bottom:1px solid var(--border); transition:.15s; }
#pdd a:last-child { border-bottom:none; }
#pdd a:hover { background:rgba(var(--primary-rgb),.06); color:var(--primary); }
#pdd a.red:hover { background:rgba(239,68,68,.07); color:#ef4444; }

/* Modal */
#pmd-bg { display:none; position:fixed; inset:0; z-index:99998; background:rgba(0,0,0,.6); backdrop-filter:blur(5px); align-items:center; justify-content:center; }
#pmd-bg.show { display:flex; }
#pmd { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; width:100%; max-width:470px; max-height:88vh; overflow-y:auto; box-shadow:0 20px 64px rgba(0,0,0,.45); animation:pmup .22s ease; }
@keyframes pmup { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.pmd-hd { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid var(--border); position:sticky; top:0; background:var(--bg-card); border-radius:20px 20px 0 0; }
.pmd-hd h5 { margin:0; font-weight:800; font-size:15px; }
.pmd-cls { width:30px; height:30px; border-radius:50%; border:none; cursor:pointer; background:rgba(var(--primary-rgb),.08); color:var(--text-main); font-size:18px; display:flex; align-items:center; justify-content:center; transition:.2s; line-height:1; }
.pmd-cls:hover { background:var(--primary); color:#000; }
.pmd-bd { padding:22px; }
.pmd-item { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border); }
.pmd-item:last-child { border-bottom:none; }
.pmd-obs { background:rgba(var(--primary-rgb),.05); border-radius:12px; padding:13px; margin-top:14px; }

/* Empty */
.ped-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:56px 20px; color:var(--text-muted); gap:12px; }
</style>

<!-- Header -->
<div class="welcome-container mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="welcome-title mb-1">Gestão de Pedidos</h1>
            <p class="welcome-sub text-muted">Acompanhe e gerencie as vendas do seu ClubePet+.</p>
        </div>
        <div class="col-md-4 text-md-right">
            <a href="<?php echo SITE_URL; ?>/app/produtos" class="btn-secondary">← Voltar</a>
        </div>
    </div>
</div>

<!-- Main card: NO overflow:hidden here -->
<div class="dashboard-card p-0" style="border-radius:16px;">

    <!-- Tabs -->
    <div class="ped-tab-nav" style="border-radius:16px 16px 0 0; background:rgba(var(--primary-rgb),.02);">
        <button class="ped-tab-btn active" onclick="pedTab('novos',this)">
            Novos <span class="ped-tab-count"><?php echo count($pedidos['novos']); ?></span>
        </button>
        <button class="ped-tab-btn" onclick="pedTab('concluidos',this)">
            Concluídos <span class="ped-tab-count"><?php echo count($pedidos['concluidos']); ?></span>
        </button>
        <button class="ped-tab-btn" onclick="pedTab('cancelados',this)">
            Cancelados <span class="ped-tab-count"><?php echo count($pedidos['cancelados']); ?></span>
        </button>
    </div>

    <!-- Panels -->
    <div style="padding:20px;">
        <?php foreach (['novos','concluidos','cancelados'] as $tab): ?>
        <div id="pt-<?php echo $tab; ?>" class="ped-panel <?php echo $tab === 'novos' ? 'active' : ''; ?>">
            <?php if (empty($pedidos[$tab])): ?>
                <div class="ped-empty">
                    <i data-lucide="package" style="width:42px;height:42px;opacity:.3;"></i>
                    <p>Nenhum pedido nesta categoria.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="ped-tbl">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Tipo / Endereço</th>
                            <th>Pagamento</th>
                            <th>Valor</th>
                            <th style="text-align:right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pedidos[$tab] as $p): ?>
                        <tr>
                            <td>
                                <div class="fw-800">#<?php echo $p['id']; ?></div>
                                <div class="small text-muted"><?php echo date('d/m H:i', strtotime($p['created_at'])); ?></div>
                            </td>
                            <td>
                                <div class="fw-700"><?php echo htmlspecialchars($p['cliente_nome']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($p['cliente_telefone'] ?? ''); ?></div>
                            </td>
                            <td>
                                <?php if ($p['tipo'] === 'delivery'): ?>
                                    <span class="badge badge-primary-lite">Entrega</span>
                                    <div style="font-size:11px;margin-top:4px;"><?php echo htmlspecialchars(trim(($p['address'] ?? '') . ', ' . ($p['number'] ?? ''), ', ')); ?></div>
                                    <div style="font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars(trim(($p['neighborhood'] ?? '') . ' — ' . ($p['city'] ?? ''), ' —')); ?></div>
                                <?php else: ?>
                                    <span class="badge badge-secondary-lite">Retirada no ClubePet+</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['payment_mode'] === 'online'): ?>
                                    <span class="badge badge-success-lite">Online</span>
                                    <div style="font-size:11px;margin-top:3px;font-weight:700;color:<?php echo $p['payment_status'] === 'paid' ? 'var(--success,#22c55e)' : '#f59e0b'; ?>;">
                                        <?php echo $p['payment_status'] === 'paid' ? '✓ Pago' : '⏳ Aguardando'; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-outline-primary">Na entrega</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-800">R$ <?php echo number_format((float)$p['total'], 2, ',', '.'); ?></div>
                                <?php if ((float)($p['frete'] ?? 0) > 0): ?>
                                    <div style="font-size:11px;color:var(--text-muted);">+ R$ <?php echo number_format((float)$p['frete'], 2, ',', '.'); ?> frete</div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <button class="gear-btn" onclick="openPedDrop(event, <?php echo $p['id']; ?>)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                    Ações
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Floating dropdown (portal) -->
<div id="pdd"></div>

<!-- Modal -->
<div id="pmd-bg" onclick="if(event.target===this)closePmd()">
    <div id="pmd">
        <div class="pmd-hd">
            <h5>Itens do Pedido <span id="pmd-num" style="color:var(--primary);"></span></h5>
            <button class="pmd-cls" onclick="closePmd()">×</button>
        </div>
        <div class="pmd-bd">
            <div id="pmd-items"></div>
            <div style="border-top:1px solid var(--border);margin-top:10px;padding-top:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px;"><span class="text-muted">Subtotal</span><span id="pmd-sub" class="fw-700"></span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px;"><span class="text-muted">Frete</span><span id="pmd-frete" class="fw-700"></span></div>
                <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1px solid var(--border);font-weight:800;font-size:18px;color:var(--primary);"><span>TOTAL</span><span id="pmd-total"></span></div>
            </div>
            <div class="pmd-obs">
                <div class="fw-700 small mb-1">Observações:</div>
                <p class="small mb-0" id="pmd-obs"></p>
            </div>
        </div>
    </div>
</div>

<!-- All orders data -->
<script>
const ORDERS = <?php echo json_encode($ordersMap, JSON_UNESCAPED_UNICODE); ?>;
const BASE_URL = '<?php echo SITE_URL; ?>';

/* ── TABS ──────────────────────────────── */
function pedTab(id, btn) {
    document.querySelectorAll('.ped-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.ped-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('pt-' + id).classList.add('active');
    closePdd();
}

/* ── DROPDOWN ──────────────────────────── */
const pdd = document.getElementById('pdd');
let pddOrderId = null;

function openPedDrop(e, orderId) {
    e.stopPropagation();
    pddOrderId = orderId;
    const p = ORDERS[orderId];
    if (!p) return;

    const ico_ok  = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
    const ico_x   = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;
    const ico_lst = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>`;

    let html = '';
    if (p.status === 'pendente') {
        html += `<a href="#" onclick="pedStatus(${orderId},'entregue');return false;">${ico_ok} Concluir Pedido</a>`;
        html += `<a href="#" class="red" onclick="pedStatus(${orderId},'cancelado');return false;">${ico_x} Cancelar Pedido</a>`;
    } else if (p.status === 'confirmado') {
        html += `<a href="#" onclick="pedStatus(${orderId},'entregue');return false;">${ico_ok} Marcar Entregue</a>`;
    }
    html += `<a href="#" onclick="openPmd(${orderId});return false;">${ico_lst} Ver Itens</a>`;
    pdd.innerHTML = html;

    const r = e.currentTarget.getBoundingClientRect();
    pdd.style.top  = (r.bottom + window.scrollY + 6) + 'px';
    pdd.style.left = Math.max(8, r.right + window.scrollX - 190) + 'px';
    pdd.classList.add('show');
}

function closePdd() { pdd.classList.remove('show'); }
document.addEventListener('click', e => { if (!e.target.closest('#pdd') && !e.target.closest('.gear-btn')) closePdd(); });

/* ── STATUS UPDATE ─────────────────────── */
function pedStatus(id, status) {
    const labels = { entregue: 'Entregue/Concluído', cancelado: 'Cancelado' };
    if (!confirm(`Alterar pedido #${id} para "${labels[status]}"?`)) return;
    closePdd();
    fetch(`${BASE_URL}/api/pedidos/update-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&status=${status}`
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message || 'Erro ao atualizar');
    }).catch(() => alert('Erro de conexão'));
}

/* ── MODAL ─────────────────────────────── */
function openPmd(orderId) {
    const p = ORDERS[orderId];
    if (!p) return;
    closePdd();

    document.getElementById('pmd-num').innerText = '#' + p.id;
    const total = parseFloat(p.total || 0);
    const frete = parseFloat(p.frete || 0);
    document.getElementById('pmd-total').innerText = 'R$ ' + total.toLocaleString('pt-BR',{minimumFractionDigits:2});
    document.getElementById('pmd-frete').innerText = frete > 0 ? '+ R$ '+frete.toLocaleString('pt-BR',{minimumFractionDigits:2}) : 'Grátis';
    document.getElementById('pmd-sub').innerText   = 'R$ ' + (total-frete).toLocaleString('pt-BR',{minimumFractionDigits:2});
    document.getElementById('pmd-obs').innerText   = p.observacoes || 'Sem observações.';

    const items = typeof p.itens_json === 'string' ? JSON.parse(p.itens_json) : (p.itens_json || []);
    document.getElementById('pmd-items').innerHTML = items.map(it => {
        const line = (parseFloat(it.preco) * parseInt(it.quantidade)).toLocaleString('pt-BR',{minimumFractionDigits:2});
        const unit = parseFloat(it.preco).toLocaleString('pt-BR',{minimumFractionDigits:2});
        return `<div class="pmd-item">
            <div><div class="fw-700 small">${it.quantidade}× ${it.nome}</div><div class="text-muted" style="font-size:11px;">R$ ${unit} un.</div></div>
            <div class="fw-800 small">R$ ${line}</div>
        </div>`;
    }).join('');

    document.getElementById('pmd-bg').classList.add('show');
}
function closePmd() { document.getElementById('pmd-bg').classList.remove('show'); }

document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });
</script>
