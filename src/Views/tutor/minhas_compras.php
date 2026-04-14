<?php
/** @var array $pedidos */
/** @var string $SITE_URL */
?>
<div class="page-header">
    <div class="page-header-info">
        <h2>Minhas Compras</h2>
        <p>Acompanhe todos os seus pedidos e pagamentos.</p>
    </div>
</div>

<style>
.orders-filter-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 7px 18px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-tab:hover,
.filter-tab.active {
    background: var(--primary);
    color: var(--text-on-primary, #000);
    border-color: var(--primary);
}

.order-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 16px;
    transition: border-color 0.2s ease;
}

.order-card:hover {
    border-color: var(--primary);
}

.order-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 10px;
}

.order-id-date {
    display: flex;
    align-items: center;
    gap: 12px;
}

.order-num {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-main);
}

.order-date {
    font-size: 12px;
    color: var(--text-muted);
}

.order-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 14px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-status-badge.pendente {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.order-status-badge.pago {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.order-status-badge.cancelado {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.order-status-badge.processando {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.25);
}

.order-items-list {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 14px;
    line-height: 1.6;
}

.order-total-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid var(--border);
}

.order-total-label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
}

.order-total-value {
    font-size: 20px;
    font-weight: 800;
    color: var(--primary);
}

.btn-pay-now {
    padding: 8px 20px;
    border-radius: 100px;
    background: #f59e0b;
    color: #000;
    font-weight: 800;
    font-size: 12px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-pay-now:hover {
    filter: brightness(1.1);
    color: #000;
}

.empty-orders {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-muted);
}

.orders-section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin: 24px 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.payment-type-tag {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    padding: 3px 10px;
    border-radius: 100px;
}
</style>

<?php
// Group orders by status
$pendentes  = array_filter($pedidos, fn($p) => in_array($p['payment_status'], ['pending']) || ($p['status'] === 'pendente' && $p['payment_mode'] === 'online'));
$pagos      = array_filter($pedidos, fn($p) => $p['payment_status'] === 'approved' || ($p['payment_mode'] === 'delivery' && !in_array($p['status'], ['cancelado'])));
$cancelados = array_filter($pedidos, fn($p) => $p['status'] === 'cancelado');

$statusLabels = [
    'pendente'     => ['Pendente',    'pendente'],
    'confirmado'   => ['Confirmado',  'processando'],
    'em_preparo'   => ['Em Preparo',  'processando'],
    'a_caminho'    => ['A Caminho',   'processando'],
    'entregue'     => ['Entregue',    'pago'],
    'cancelado'    => ['Cancelado',   'cancelado'],
];
?>

<!-- Filter Tabs -->
<div class="orders-filter-tabs">
    <button class="filter-tab active" onclick="filterOrders('all', this)">Todos (<?php echo count($pedidos); ?>)</button>
    <button class="filter-tab" onclick="filterOrders('pendente', this)">
        Pagamentos Pendentes (<?php echo count($pendentes); ?>)
    </button>
    <button class="filter-tab" onclick="filterOrders('pago', this)">Concluídos (<?php echo count($pagos); ?>)</button>
    <button class="filter-tab" onclick="filterOrders('cancelado', this)">Cancelados (<?php echo count($cancelados); ?>)</button>
</div>

<?php if (empty($pedidos)): ?>
    <div class="empty-orders">
        <i data-lucide="shopping-bag" class="icon-lucide" style="width:56px;height:56px;opacity:0.15;margin-bottom:16px;"></i>
        <p style="font-size:17px;font-weight:600;margin-bottom:6px;">Nenhuma compra encontrada</p>
        <p style="font-size:14px;">Suas compras na loja aparecerão aqui.</p>
        <?php if (!empty($_SESSION['company_slug'])): ?>
            <a href="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($_SESSION['company_slug']); ?>/loja" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;margin-top:20px;padding:10px 24px;">
                <i data-lucide="shopping-bag" class="icon-lucide icon-sm"></i> Ir para a Loja
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div id="orders-list">
        <?php foreach ($pedidos as $pedido): 
            $payStatus  = $pedido['payment_status'] ?? 'pending'; // approved, pending, cancelled
            $orderStatus = $pedido['status'] ?? 'pendente';
            $isPending  = ($payStatus === 'pending' && $pedido['payment_mode'] === 'online');
            $isCanceled = ($orderStatus === 'cancelado');
            $isPaid     = ($payStatus === 'approved' || $pedido['payment_mode'] === 'delivery') && !$isCanceled;

            // Determine display badge
            if ($isCanceled) {
                $badgeClass = 'cancelado';
                $badgeText  = 'Cancelado';
                $badgeIcon  = 'x-circle';
                $filterKey  = 'cancelado';
            } elseif ($isPending) {
                $badgeClass = 'pendente';
                $badgeText  = 'Pagamento Pendente';
                $badgeIcon  = 'clock';
                $filterKey  = 'pendente';
            } elseif ($payStatus === 'approved') {
                $badgeClass = 'pago';
                $badgeText  = 'Pago';
                $badgeIcon  = 'check-circle';
                $filterKey  = 'pago';
            } else {
                // delivery payment - show order status
                $statusInfo = $statusLabels[$orderStatus] ?? ['Em andamento', 'processando'];
                $badgeClass = $statusInfo[1];
                $badgeText  = $statusInfo[0];
                $badgeIcon  = 'package';
                $filterKey  = 'pago'; // delivery orders go to "concluídos" filter
            }

            // Decode itens JSON if stored as JSON
            $itens = [];
            if (!empty($pedido['itens'])) {
                $itens = json_decode($pedido['itens'], true) ?: [];
            }

            // Expiry for pending
            $expiresAt = '';
            if ($isPending) {
                $expires   = strtotime($pedido['created_at']) + (4 * 3600);
                $remaining = $expires - time();
                $expiresAt = $remaining > 0 ? gmdate('H\h i\m', $remaining) : 'Expirado';
            }
        ?>
        <div class="order-card" data-filter="<?php echo $filterKey; ?>">
            <div class="order-card-header">
                <div class="order-id-date">
                    <span class="order-num">Pedido #<?php echo $pedido['id']; ?></span>
                    <span class="order-date"><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></span>
                    <span class="payment-type-tag">
                        <?php echo $pedido['payment_mode'] === 'online' ? 'Online (PIX/Cartão)' : 'Pagar na Entrega'; ?>
                    </span>
                </div>
                <span class="order-status-badge <?php echo $badgeClass; ?>">
                    <i data-lucide="<?php echo $badgeIcon; ?>" style="width:11px;height:11px;"></i>
                    <?php echo $badgeText; ?>
                </span>
            </div>

            <?php if ($isPending && $expiresAt): ?>
                <div style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:13px;color:#f59e0b;font-weight:600;">
                    <i data-lucide="alert-triangle" style="width:14px;height:14px;margin-right:4px;"></i>
                    Pagamento expira em: <?php echo $expiresAt; ?> — Finalize para não perder seu pedido.
                </div>
            <?php endif; ?>

            <?php if (!empty($itens)): ?>
                <div class="order-items-list">
                    <?php foreach ($itens as $item): ?>
                        <span><?php echo (int)($item['quantidade'] ?? 1); ?>x <?php echo htmlspecialchars($item['nome'] ?? ''); ?></span><?php echo (array_key_last($itens) !== array_search($item, $itens)) ? ' · ' : ''; ?>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($pedido['observacoes'])): ?>
                <div class="order-items-list"><?php echo htmlspecialchars($pedido['observacoes']); ?></div>
            <?php endif; ?>

            <div class="order-total-row">
                <div>
                    <div class="order-total-label">Total do Pedido</div>
                    <div class="order-total-value">R$ <?php echo number_format((float)$pedido['total'], 2, ',', '.'); ?></div>
                </div>
                <?php if ($isPending && !empty($pedido['payment_url'])): ?>
                    <a href="<?php echo htmlspecialchars($pedido['payment_url']); ?>" class="btn-pay-now">
                        <i data-lucide="credit-card" style="width:14px;height:14px;"></i>
                        Pagar Agora
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div id="empty-filtered" style="display:none;" class="empty-orders">
        <i data-lucide="inbox" class="icon-lucide" style="width:48px;height:48px;opacity:0.15;margin-bottom:12px;"></i>
        <p>Nenhum pedido nesta categoria.</p>
    </div>
<?php endif; ?>

<script>
function filterOrders(status, btn) {
    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const cards = document.querySelectorAll('.order-card');
    const emptyMsg = document.getElementById('empty-filtered');
    let visible = 0;

    cards.forEach(card => {
        if (status === 'all' || card.dataset.filter === status) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    if (emptyMsg) emptyMsg.style.display = (visible === 0 && cards.length > 0) ? 'block' : 'none';
}
</script>
