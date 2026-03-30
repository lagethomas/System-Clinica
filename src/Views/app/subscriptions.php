<?php
/** @var array $company */
/** @var string $active_tab */
/** @var array $invoices */
/** @var array $all_plans */
/** @var int $active_users */
/** @var int $extra_users */
/** @var float $extra_cost */

$v = time();
$current_theme = $company['theme'] ?? 'gold-black';
?>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/invoices.css?v=<?php echo $v; ?>">

<div class="settings-tab-nav mb-4">
    <a href="?tab=invoices" class="nav-link-tab <?php echo $active_tab === 'invoices' ? 'active' : ''; ?>">
        <i data-lucide="receipt" class="icon-lucide"></i> Faturas e Cobranças
    </a>
    <a href="?tab=plans" class="nav-link-tab <?php echo $active_tab === 'plans' ? 'active' : ''; ?>">
        <i data-lucide="crown" class="icon-lucide"></i> Detalhes do Plano
    </a>
</div>

<div class="card subscriptions-main-card">    <!-- ABA FATURAS -->
    <?php if ($active_tab === 'invoices'): ?>
        <div class="settings-header-box" style="padding: 30px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5><i data-lucide="receipt" class="icon-lucide"></i> Faturas e Cobranças</h5>
                    <p>Gerencie seus pagamentos e acompanhe o histórico de faturamento.</p>
                </div>
            </div>
        </div>

        <div class="invoices-flex-container">
            <!-- COLUNA DA TABELA (MAIOR) -->
            <div class="invoices-main-col">
                <div class="table-responsive">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Fatura</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr><td colspan="5" class="text-center py-5">Nenhuma fatura encontrada.</td></tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td><span class="invoice-id">#<?php echo str_pad((string)$inv['id'], 5, '0', STR_PAD_LEFT); ?></span></td>
                                        <td class="fw-bold">R$ <?php echo number_format($inv['amount'], 2, ',', '.'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($inv['due_date'])); ?></td>
                                        <td>
                                            <span class="status-pill <?php echo $inv['status']; ?>">
                                                <i data-lucide="<?php echo $inv['status'] === 'paid' ? 'check' : 'clock'; ?>" class="icon-lucide"></i>
                                                <?php echo $inv['status'] === 'paid' ? 'Paga' : 'Pendente'; ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <?php if ($inv['status'] === 'pending'): ?>
                                                <button class="btn-primary-glass" onclick="payInvoice(<?php echo $inv['id']; ?>)">Pagar</button>
                                            <?php else: ?>
                                                <a href="<?php echo SITE_URL; ?>/checkout/receipt/<?php echo $inv['id']; ?>" target="_blank" class="btn-action-glass" style="padding: 6px 15px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i data-lucide="file-text" class="icon-lucide icon-xs"></i> PDF
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- COLUNA DOS CARDS (MENOR) -->
            <div class="invoices-side-col">
                <!-- PRÓXIMA FATURA -->
                <div class="side-card-glass">
                    <label>Próximo Vencimento</label>
                    <?php 
                        $next_due = !empty($invoices) ? $invoices[0]['due_date'] : null;
                        if ($next_due):
                            $days = floor((strtotime($next_due) - time()) / 86400);
                    ?>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="mb-0"><?php echo date('d/m/Y', strtotime($next_due)); ?></h3>
                            <span class="badge-status <?php echo $days < 5 ? 'danger' : 'info'; ?>">
                                <?php echo $days > 0 ? "$days dias" : ($days == 0 ? "Hoje" : "Atrasada"); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <h3 class="mb-0">Ciclo Inicial</h3>
                    <?php endif; ?>
                </div>

                <!-- USO DE USUARIOS -->
                <div class="side-card-glass highlight-secondary">
                    <div class="d-flex justify-content-between">
                        <label><i data-lucide="users" class="icon-lucide"></i> Uso de Usuários</label>
                        <span class="usage-badge"><?php echo $active_users; ?> / <?php echo $company['included_users']; ?></span>
                    </div>
                    
                    <div class="usage-progress-container mt-2">
                        <?php 
                            $pct = ($company['included_users'] > 0) ? min(100, ($active_users / $company['included_users']) * 100) : 100;
                        ?>
                        <div class="usage-progress-bar" style="width: <?php echo $pct; ?>%; background: <?php echo $pct > 90 ? 'var(--danger)' : 'var(--primary)'; ?>;"></div>
                    </div>

                    <div class="usage-footer-info mt-3">
                        <?php if ($extra_users > 0): ?>
                            <div class="extra-alert">
                                <i data-lucide="alert-triangle" class="icon-lucide"></i>
                                <span><strong><?php echo $extra_users; ?> excedentes:</strong> R$ <?php echo number_format($extra_cost, 2, ',', '.'); ?>/mês</span>
                            </div>
                        <?php else: ?>
                            <div class="usage-ok-info">
                                <i data-lucide="check-circle" class="icon-lucide"></i> <span>Dentro do limite do plano</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- MÉTODO DE PAGAMENTO -->
                <div class="side-card-glass">
                    <label>Método de Pagamento</label>
                    <div class="payment-method-box">
                        <?php if (empty($company['mp_subscription_id'])): ?>
                            <div class="payment-type-pill manual"><i data-lucide="tag" class="icon-lucide"></i> Pagamento Manual</div>
                        <?php else: ?>
                            <div class="payment-type-pill active"><i data-lucide="credit-card" class="icon-lucide"></i> Cartão de Crédito</div>
                        <?php endif; ?>
                        <button class="btn-action-glass mt-3 w-100" onclick="UI.showToast('Recorrência em implantação...', 'info')">
                            Alterar para Recorrência <i data-lucide="arrow-right" class="icon-lucide"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- ABA PLANOS -->
    <?php elseif ($active_tab === 'plans'): ?>
        <div class="settings-header-box" style="padding: 30px;">
            <h5><i data-lucide="diamond" class="icon-lucide"></i> Gestão de Assinatura</h5>
            <p>Confira os detalhes do seu plano e otimize seus custos de operação.</p>
        </div>

        <div class="plan-details-grid-full" style="padding: 0 30px 30px 30px;">
            <div class="card active-plan-detail-card mb-4" style="border-radius: 12px; border: 1px solid var(--border); background: var(--card-bg);">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="padding: 30px;">
                    <div class="d-flex align-items-center mb-0">
                        <div class="plan-icon-large" style="width: 50px; height: 50px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i data-lucide="diamond" class="icon-lucide"></i>
                        </div>
                        <div class="ml-3">
                            <span class="text-muted small">Plano Contratado</span>
                            <h2 class="mb-0" style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($company['plan_name'] ?? 'N/A'); ?></h2>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-4">
                        <div class="info-item text-center pr-4" style="border-right: 1px solid var(--border);">
                            <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Mensalidade Base</label>
                            <div class="value primary" style="font-size: 24px; font-weight: 700; color: var(--primary);">R$ <?php echo number_format($company['base_price'] ?? 0, 2, ',', '.'); ?></div>
                        </div>
                        <div class="info-item text-center pr-4" style="border-right: 1px solid var(--border);">
                            <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Usuários Incluídos</label>
                            <div class="value" style="font-size: 24px; font-weight: 700; color: var(--text-main);"><?php echo $company['included_users'] ?? 0; ?></div>
                        </div>
                        <div class="info-item p-2 text-center" style="background: rgba(var(--primary-rgb), 0.05); border-radius: 8px;">
                            <label style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;"><i data-lucide="user-plus" class="icon-lucide"></i> Adicional</label>
                            <div class="value" style="font-size: 20px; font-weight: 700; color: var(--text-main);">R$ <?php echo number_format($company['extra_user_price'] ?? 0, 2, ',', '.'); ?>/cada</div>
                            <small class="text-muted" style="font-size: 11px;">Base: <?php echo $extra_users; ?> excedentes.</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($company['pending_plan_id'])): ?>
                <div class="plan-change-scheduled-card-premium mt-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="schedule-icon"><i data-lucide="clock" class="icon-lucide"></i></div>
                            <div class="ml-3">
                                <h4 class="mb-0">Trocar Plano para: <?php echo htmlspecialchars($company['pending_plan_name']); ?></h4>
                                <p class="mb-0 opacity-70">Agendado para o próximo fechamento.</p>
                            </div>
                        </div>
                        <button class="btn-cancel-glass" onclick="handleSubscription('cancel_plan_change')">Cancelar</button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="available-plans-grid-section mt-5">
                <div class="section-title-premium mb-4">
                    <h5>Upgrade de Plano</h5>
                    <div class="title-line"></div>
                </div>

                <div class="plans-row-5">
                    <?php foreach ($all_plans as $p): 
                        $isCurrent = ($p['id'] == ($company['plan_id'] ?? null));
                        $isPending = ($p['id'] == ($company['pending_plan_id'] ?? null));
                    ?>
                        <div class="plan-card-mini <?php echo $isCurrent ? 'active' : ($isPending ? 'pending' : ''); ?>">
                            <div class="tag-plan"><?php echo htmlspecialchars($p['name']); ?></div>
                            <div class="mini-price">
                                <span class="prefix">R$</span>
                                <span class="amt"><?php echo number_format($p['base_price'], 0, ',', '.'); ?></span>
                            </div>
                            
                            <div class="mini-features">
                                <div class="f-item"><i data-lucide="users" class="icon-lucide"></i> <?php echo $p['included_users']; ?> usuários</div>
                                <div class="f-item highlight"><i data-lucide="plus" class="icon-lucide"></i> R$ <?php echo number_format($p['extra_user_price'], 0, ',', '.'); ?>/extra</div>
                            </div>

                            <div class="mini-footer">
                                <?php if (!$isCurrent && !$isPending): ?>
                                    <button class="btn-upgrade-sm" onclick="handleSubscription('schedule_plan', <?php echo $p['id']; ?>)">Migrar</button>
                                <?php elseif ($isCurrent): ?>
                                    <span class="label-current">Atual</span>
                                <?php else: ?>
                                    <span class="label-pending">Agendado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
async function handleSubscription(action, planId = 0) {
    if (action === 'schedule_plan' && !await UI.confirm('Confirmar agendamento de troca de plano? A alteração será aplicada no próximo ciclo.', {
        title: 'Troca de Plano',
        confirmText: 'Agendar Troca',
        type: 'info',
        icon: 'calendar'
    })) return;

    if (action === 'cancel_plan_change' && !await UI.confirm('Deseja cancelar a troca agendada?', {
        title: 'Cancelar Agendamento',
        confirmText: 'Sim, Cancelar',
        type: 'info',
        icon: 'x-circle'
    })) return;

    try {
        const formData = new FormData();
        formData.append('action', action);
        if (planId) formData.append('plan_id', planId);

        const res = await fetch('<?php echo SITE_URL; ?>/api/company-subscription-action', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            UI.showToast(data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            UI.showToast(data.message || 'Erro ao processar.', 'error');
        }
    } catch (err) {
        UI.showToast('Erro de conexão.', 'error');
    }
}

function payInvoice(id) {
    UI.showToast('Redirecionando para o Mercado Pago...', 'info');
    setTimeout(() => {
        window.location.href = '<?php echo SITE_URL; ?>/checkout/' + id;
    }, 800);
}
</script>

<style>
.subscriptions-main-card {
    border-radius: 12px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    overflow: hidden;
}

/* Glassmorphism & Dashboard Layout */
.glass-table-wrapper {
    background: rgba(var(--text-main-rgb), 0.02);
    border-radius: 16px;
    border: 1px solid var(--border);
    padding: 10px;
}

.invoice-id { font-family: monospace; color: var(--primary); font-weight: 700; }

.status-pill {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.status-pill.paid { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
.status-pill.pending { background: rgba(230, 126, 34, 0.1); color: #e67e22; }

.btn-primary-glass {
    background: var(--primary);
    color: #000;
    border: none;
    padding: 6px 15px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    transition: 0.3s;
}
.btn-primary-glass:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3); }

.side-card-glass {
    background: rgba(var(--text-main-rgb), 0.03);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    backdrop-filter: blur(5px);
}

.side-card-glass label {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 10px;
}

.badge-status { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
.badge-status.info { background: rgba(52, 152, 219, 0.1); color: #3498db; }
.badge-status.danger { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

.usage-badge { font-size: 12px; font-weight: 700; color: var(--text-main); }
.usage-progress-container { width: 100%; height: 6px; background: rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
.usage-progress-bar { height: 100%; border-radius: 10px; transition: width 1s ease; }

.extra-alert { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--danger); }
.usage-ok-info { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #2ecc71; }

.payment-type-pill { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
.payment-type-pill.manual { background: rgba(var(--text-main-rgb), 0.05); color: var(--text-muted); }
.btn-action-glass { background: rgba(var(--primary-rgb), 0.08); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2); padding: 10px; border-radius: 10px; font-weight: 700; cursor: pointer; }
.btn-action-glass:hover { background: var(--primary); color: #000; }

/* Plan Details Premium */
.active-plan-detail-card-glass {
    position: relative;
    border: 1px solid rgba(var(--primary-rgb), 0.2);
    border-radius: 24px;
    padding: 35px;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1), rgba(0,0,0,0));
    backdrop-filter: blur(10px);
}
.plan-icon-premium { width: 60px; height: 60px; background: var(--primary); color: #000; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.metric-value { font-size: 28px; font-weight: 800; }
.metric-value span { font-size: 12px; color: var(--text-muted); font-weight: 400; }
.metric-separator { width: 1px; height: 40px; background: var(--border); }
.highlight-extra { background: rgba(var(--primary-rgb), 0.05); border-radius: 15px; padding: 15px 20px; border: 1px dashed rgba(var(--primary-rgb), 0.3); }

/* Grid 5 Colunas */
.plans-row-5 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 15px;
}

@media (max-width: 1200px) { .plans-row-5 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .plans-row-5 { grid-template-columns: 1fr; } }

.plan-card-mini {
    background: rgba(var(--text-main-rgb), 0.02);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    transition: 0.3s;
}
.plan-card-mini:hover { border-color: var(--primary); transform: translateY(-5px); }
.plan-card-mini.active { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.05); }

.tag-plan { font-size: 11px; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 10px; }
.mini-price .amt { font-size: 24px; font-weight: 800; }
.mini-price .prefix { font-size: 14px; color: var(--text-muted); }

.mini-features { margin: 15px 0; font-size: 12px; color: var(--text-muted); }
.mini-features .f-item { margin-bottom: 5px; }
.mini-features .f-item.highlight { color: var(--text-main); font-weight: 600; }

.btn-upgrade-sm { background: var(--primary); color: #000; border: none; padding: 8px; width: 100%; border-radius: 8px; font-weight: 700; cursor: pointer; }
.label-current { font-size: 11px; font-weight: 700; color: var(--primary); text-transform: uppercase; }

.plan-change-scheduled-card-premium { background: rgba(230, 126, 34, 0.1); border: 1px solid rgba(230, 126, 34, 0.3); padding: 15px 25px; border-radius: 16px; }
.btn-cancel-glass { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.2); border-radius: 8px; padding: 5px 15px; font-size: 11px; cursor: pointer; }

/* Invoices Flex Layout */
.invoices-flex-container {
    display: flex;
    gap: 30px;
    padding: 0 30px 30px 30px;
}
.invoices-main-col {
    flex: 1;
    min-width: 0;
}
.invoices-side-col {
    flex: 0 0 320px;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
@media (max-width: 991px) {
    .invoices-flex-container {
        flex-direction: column;
    }
    .invoices-side-col {
        flex: 1;
        width: 100%;
    }
}

</style>
