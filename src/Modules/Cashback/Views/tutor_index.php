<?php
/** @var array $tutor */
/** @var float $balance */
/** @var array $stats */
/** @var array $chart_data */
/** @var array $history */
/** @var array $withdrawals */
/** @var array $pix */
/** @var string $SITE_URL */

$totalEarned = (float)($stats['total_earned'] ?? 0);
$totalUsed   = (float)($stats['total_used'] ?? 0);
$creditLimit = (float)($tutor['credit_limit'] ?? 0);

// Prepare Chart Data
$chartLabels = [];
$chartEarned = [];
$chartUsed   = [];
foreach ($chart_data as $data) {
    $chartLabels[] = $data['month'];
    $chartEarned[] = (float)$data['earned'];
    $chartUsed[]   = (float)$data['used'];
}
if (empty($chartLabels)) { $chartLabels = ['Sem dados']; $chartEarned = [0]; $chartUsed = [0]; }
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Header Module -->
<div class="cashback-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="fw-800">ClubePet+ <span class="text-primary">Cashback</span></h2>
            <p class="text-muted mb-0">Gerencie seus benefícios e realize saques do seu saldo acumulado.</p>
        </div>
        <a href="<?php echo $SITE_URL; ?>/<?php echo $_SESSION['company_slug']; ?>/clube-pet" class="btn-primary px-4 py-3">
            <i data-lucide="shopping-cart"></i> IR PARA A LOJA
        </a>
    </div>
</div>

<!-- Global Stats: Fixed at the top for both tabs -->
<div class="row g-4 mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
    <!-- Saldo Disponível -->
    <div style="min-width: 0;">
        <div class="card shadow-sm border-0 p-4 h-100" style="border-radius: 20px; background: var(--bg-card); min-height: 160px;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-1">
                    <span class="text-muted small fw-800 text-uppercase tracking-wider" style="font-size: 10px; opacity: 0.7; display: block;">Saldo Disponível</span>
                    <strong class="d-block mt-1" style="font-size: 1.8rem; letter-spacing: -1px; color: var(--text-main);">R$ <?php echo number_format($balance, 2, ',', '.'); ?></strong>
                </div>
                <div class="icon-box-lite ms-3 shadow-premium" style="background: var(--primary); color: white; width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
            <div class="progress-premium mt-3" style="height: 6px; background: rgba(var(--primary-rgb), 0.1); border-radius: 10px; overflow: hidden;">
                <div class="progress-bar-premium" style="width: <?php echo min(100, ($balance / 50) * 100); ?>%; background: var(--primary); height: 100%; border-radius: 10px;"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-2">
                <span style="font-size: 0.65rem; font-weight: 700;">Progresso para Saque (Mín. R$ 50)</span>
            </div>
        </div>
    </div>

    <!-- Total Recebido -->
    <div style="min-width: 0;">
        <div class="card shadow-sm border-0 p-4 h-100" style="border-radius: 20px; background: var(--bg-card); min-height: 160px;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-1">
                    <span class="text-muted small fw-800 text-uppercase tracking-wider" style="font-size: 10px; opacity: 0.7; display: block;">Total Recebido</span>
                    <strong class="d-block mt-1 text-success" style="font-size: 1.8rem; letter-spacing: -1px;">R$ <?php echo number_format($totalEarned, 2, ',', '.'); ?></strong>
                </div>
                <div class="icon-box-lite ms-3" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="trending-up" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
            <div class="mt-3 small text-muted fw-600" style="font-size: 11px;">Histórico total de ganhos no clube.</div>
        </div>
    </div>

    <!-- Limite de Crédito -->
    <div style="min-width: 0;">
        <div class="card shadow-sm border-0 p-4 h-100" style="border-radius: 20px; background: var(--bg-card); min-height: 160px;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-1">
                    <span class="text-muted small fw-800 text-uppercase tracking-wider" style="font-size: 10px; opacity: 0.7; display: block;">Limite de Crédito</span>
                    <strong class="d-block mt-1 text-info" style="font-size: 1.8rem; letter-spacing: -1px;">R$ <?php echo number_format($creditLimit - $pendingLoans, 2, ',', '.'); ?></strong>
                </div>
                <div class="icon-box-lite ms-3" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="hand-coins" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                    <span class="text-muted fw-600">Total do Plano:</span>
                    <span class="fw-800">R$ <?php echo number_format($creditLimit, 2, ',', '.'); ?></span>
                </div>
                <?php if ($pendingLoans > 0): ?>
                    <div class="d-flex justify-content-between" style="font-size: 11px;">
                        <span class="text-warning fw-600">Aguardando Aprovação:</span>
                        <span class="text-warning fw-800">- R$ <?php echo number_format($pendingLoans, 2, ',', '.'); ?></span>
                    </div>
                <?php else: ?>
                    <div class="small text-muted fw-600" style="font-size: 11px;">Disponível para empréstimo imediato.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Module Tabs -->
<div class="module-tabs mb-4">
    <div class="tab-link active" onclick="switchTab(this, 'geral')">
        <i data-lucide="bar-chart-3" class="icon-sm"></i> Visão Geral
    </div>
    <div class="tab-link" onclick="switchTab(this, 'saque')">
        <i data-lucide="banknote" class="icon-sm"></i> Solicitar Saque
    </div>
    <div class="tab-link" onclick="switchTab(this, 'emprestimo')">
        <i data-lucide="landmark" class="icon-sm"></i> Empréstimo
    </div>
</div>

<!-- TAB: GERAL -->
<div id="tab-geral" class="tab-content active">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header-premium p-4 border-bottom">
                    <h3 class="m-0 fw-800" style="font-size: 16px;">Desempenho de Cashback</h3>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="cashbackChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header-premium p-4 border-bottom">
                    <h3 class="m-0 fw-800" style="font-size: 16px;">Últimas Movimentações</h3>
                </div>
                <div class="table-responsive">
                    <table class="premium-table">
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td class="text-center py-5 text-muted opacity-50"><i data-lucide="history" class="mb-2"></i><br>Nenhum registro.</td></tr>
                            <?php else: ?>
                                <?php foreach (array_slice($history, 0, 10) as $log): ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-800 small text-main"><?php echo htmlspecialchars($log['description']); ?></div>
                                                <span class="status-badge status-<?php 
                                                    if ($log['item_status'] === 'pending') echo 'info';
                                                    elseif ($log['item_status'] === 'approved' || $log['item_status'] === 'completed') echo 'success';
                                                    elseif ($log['item_status'] === 'rejected') echo 'danger';
                                                    else echo 'info';
                                                ?>" style="font-size: 9px; padding: 2px 8px;">
                                                    <?php 
                                                    if ($log['item_status'] === 'completed') echo 'Concluído';
                                                    elseif ($log['item_status'] === 'pending') echo 'Aguardando';
                                                    elseif ($log['item_status'] === 'approved') echo 'Aprovado';
                                                    elseif ($log['item_status'] === 'rejected') echo 'Recusado';
                                                    else echo ucfirst($log['item_status']);
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                                                <span><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></span>
                                                <span class="opacity-50">Fonte: <?php echo $log['source'] === 'loan' ? 'Empréstimo' : 'ClubePet+'; ?></span>
                                            </div>
                                            <div class="mt-1 text-end fw-800 <?php echo $log['type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $log['type'] === 'credit' ? '+' : '-'; ?> R$ <?php echo number_format((float)$log['amount'], 2, ',', '.'); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB: SAQUE -->
<div id="tab-saque" class="tab-content">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header-premium p-4 border-bottom">
                    <h3 class="m-0 fw-800" style="font-size: 16px;">Configurar Resgate Pix</h3>
                </div>
                <div class="card-body p-4">
                    <form id="withdrawal-form" onsubmit="requestWithdrawal(event)">
                        
                        <div class="alert alert-premium-info mb-4">
                            <div class="d-flex gap-3">
                                <i data-lucide="info" class="text-primary mt-1"></i>
                                <div>
                                    <strong class="d-block mb-1">Regras de Saque</strong>
                                    <p class="m-0 small text-muted">O valor mínimo para resgate é de <b>R$ 50,00</b>. As solicitações são processadas em até 2 dias úteis.</p>
                                </div>
                            </div>
                        </div>

                        <div class="floating-group mb-4">
                            <input type="text" name="amount" id="withdrawal_amount" class="form-control" value="<?php echo number_format($balance, 2, ',', '.'); ?>" required placeholder=" ">
                            <label for="withdrawal_amount">Quanto deseja sacar?</label>
                            <div class="input-info-badge">Disponível: R$ <?php echo number_format($balance, 2, ',', '.'); ?></div>
                        </div>

                        <div class="pix-config-box p-4 bg-light border rounded-4 mb-4">
                            <h6 class="fw-800 mb-3 small text-uppercase opacity-75">Destino do Pagamento (Pix)</h6>
                            <div class="row g-3">
                                <div class="col-12"><div class="floating-group"><select name="pix_type" id="pix_type" class="form-control" required><option value="cpf" <?php echo ($pix['pix_type'] ?? '') === 'cpf' ? 'selected' : ''; ?>>CPF</option><option value="cnpj" <?php echo ($pix['pix_type'] ?? '') === 'cnpj' ? 'selected' : ''; ?>>CNPJ</option><option value="phone" <?php echo ($pix['pix_type'] ?? '') === 'phone' ? 'selected' : ''; ?>>Telefone Celular</option><option value="email" <?php echo ($pix['pix_type'] ?? '') === 'email' ? 'selected' : ''; ?>>E-mail</option><option value="random" <?php echo ($pix['pix_type'] ?? '') === 'random' ? 'selected' : ''; ?>>Chave Aleatória</option></select><label for="pix_type">Tipo de Chave</label></div></div>
                                <div class="col-12"><div class="floating-group"><input type="text" name="pix_key" id="pix_key" class="form-control" value="<?php echo htmlspecialchars($pix['pix_key'] ?? ''); ?>" placeholder=" " required><label for="pix_key">Sua Chave Pix</label></div></div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary w-100 py-3 shadow-premium">
                            <span class="btn-text fw-800"><i data-lucide="arrow-up-right"></i> CONFIRMAR SOLICITAÇÃO</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header-premium p-4 border-bottom"><h3 class="m-0 fw-800" style="font-size: 16px;">Minhas Solicitações</h3></div>
                <div class="table-responsive">
                    <table class="premium-table">
                        <thead><tr><th class="px-4">Data/Hora</th><th>Valor</th><th>Status</th><th class="px-4 text-end">Destino</th></tr></thead>
                        <tbody>
                            <?php if (empty($withdrawals)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted opacity-50"><i data-lucide="inbox" class="mb-2"></i><br>Nenhuma solicitação encontrada.</td></tr>
                            <?php else: ?>
                                <?php foreach ($withdrawals as $w): ?>
                                    <tr><td class="px-4"><div class="small fw-700"><?php echo date('d/m/Y', strtotime($w['created_at'])); ?></div></td><td><strong class="text-main">R$ <?php echo number_format((float)$w['amount'], 2, ',', '.'); ?></strong></td><td><span class="status-badge status-<?php echo $w['status'] === 'paid' ? 'success' : ($w['status'] === 'pending' ? 'info' : 'danger'); ?>"><?php echo ucfirst($w['status']); ?></span></td><td class="px-4 text-end small text-muted"><?php echo htmlspecialchars($w['pix_key']); ?></td></tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB: EMPRÉSTIMO -->
<div id="tab-emprestimo" class="tab-content">
    <div class="row g-4 justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-5 text-center" style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(255,255,255,1) 100%); border-radius: 30px;">
                <div class="icon-box-lite mx-auto mb-4" style="width: 80px; height: 80px; background: var(--primary); color: white;">
                    <i data-lucide="landmark" style="width: 40px; height: 40px;"></i>
                </div>
                <h3 class="fw-800 mb-2" style="font-size: 24px;">Crédito Pré-Aprovado</h3>
                <p class="text-muted mb-4">Você possui um limite exclusivo para empréstimos rápidos com taxas reduzidas.</p>
                
                <div class="available-credit-display mb-4">
                    <span class="d-block text-muted small fw-bold text-uppercase mb-1">Limite Disponível</span>
                    <strong class="text-primary" style="font-size: 3rem;">R$ <?php echo number_format($creditLimit, 2, ',', '.'); ?></strong>
                </div>

                <button class="btn-primary w-100 py-3 shadow-premium rounded-pill" onclick="openLoanCalculator()">
                    <i data-lucide="calculator"></i> SIMULAR EMPRÉSTIMO
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Calculadora de Empréstimo -->
<div id="loan-modal" class="modal-premium" style="display: none;">
    <div class="modal-premium-content p-4" style="max-width: 500px;">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h5 class="m-0 fw-800"><i data-lucide="calculator" class="text-primary"></i> Simulador de Crédito</h5>
            <button onclick="closeLoanModal()" class="btn-close-modal"><i data-lucide="x"></i></button>
        </div>
        
        <div class="modal-body p-0">
            <div class="floating-group mb-4">
                <input type="text" id="loan_amount" class="form-control" value="<?php echo number_format($creditLimit, 2, ',', '.'); ?>" oninput="calculateLoan()">
                <label for="loan_amount">Valor do Empréstimo (R$)</label>
                <div class="input-info-badge">Máximo: R$ <?php echo number_format($creditLimit, 2, ',', '.'); ?></div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label fw-800 small text-uppercase opacity-75">Parcelamento (Máx. 10x)</label>
                <input type="range" class="form-range w-100" min="1" max="10" step="1" id="loan_installments" value="1" oninput="calculateLoan()">
                <div class="d-flex justify-content-between small fw-bold text-primary mt-1">
                    <span>1x</span>
                    <span id="display_installments">1x</span>
                    <span>10x</span>
                </div>
            </div>

            <div class="loan-result-box p-4 bg-light border rounded-4 mb-4" style="background: rgba(var(--primary-rgb), 0.03) !important;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Juros (1% ao mês):</span>
                    <span class="fw-800 text-danger" id="result_interest">+ R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span class="text-muted">Total a Pagar:</span>
                    <span class="fw-800" id="result_total">R$ 0,00</span>
                </div>
                <div class="text-center">
                    <div class="small text-muted mb-1">Valor por Parcela:</div>
                    <strong class="text-primary" style="font-size: 24px;" id="result_installment">R$ 0,00</strong>
                </div>
            </div>

            <button class="btn-primary w-100 py-3 rounded-pill" onclick="requestLoan()" id="btn-loan-submit">
                <i data-lucide="check-circle"></i> CONTRATAR AGORA
            </button>
        </div>
    </div>
</div>

<style>
/* Refined Styles */
/* Estilos Scoped para os Novos Cards Premium */
.row.g-4 .card {
    transition: all 0.3s ease;
    border: 1px solid rgba(var(--primary-rgb), 0.1) !important;
}
.row.g-4 .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    border-color: var(--primary) !important;
}
.icon-box-lite.shadow-premium {
    box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.3);
}
.flex-1 { flex: 1; }
.module-tabs { display: flex; gap: 8px; background: rgba(255, 255, 255, 0.03); padding: 6px; border-radius: 14px; border: 1px solid var(--border); width: fit-content; }
.tab-link { padding: 10px 20px; border-radius: 10px; color: var(--text-muted); font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.25s; display: flex; align-items: center; gap: 8px; }
.tab-link:hover { background: rgba(var(--primary-rgb), 0.05); color: var(--primary); }
.tab-link.active { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3); }
.tab-content { display: none; animation: fadeIn 0.3s ease; }
.tab-content.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.modal-premium { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(5px); }
.modal-premium-content { background: var(--bg-surface, #ffffff); border: 1px solid var(--border); border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); width: 90%; }
.btn-close-modal { background: transparent; border: none; color: var(--text-muted); cursor: pointer; }

.form-range::-webkit-slider-thumb { background: var(--primary); }
.form-range::-moz-range-thumb { background: var(--primary); }

.alert-premium-info { background: rgba(var(--primary-rgb), 0.06); border: 1px solid rgba(var(--primary-rgb), 0.15); border-radius: 16px; padding: 16px; }
.input-info-badge { 
    position: absolute; 
    right: 12px; 
    top: -10px; 
    background: var(--primary); 
    padding: 2px 10px; 
    font-size: 10px; 
    font-weight: 800; 
    color: #ffffff; 
    border-radius: 6px; 
    z-index: 5; 
    box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.3);
}
.shadow-premium { box-shadow: 0 8px 20px -6px rgba(var(--primary-rgb), 0.4); }
</style>

<script>
function switchTab(el, tabId) {
    document.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

function openLoanCalculator() {
    document.getElementById('loan-modal').style.display = 'flex';
    calculateLoan();
}

function closeLoanModal() {
    document.getElementById('loan-modal').style.display = 'none';
}

function calculateLoan() {
    const amountInput = document.getElementById('loan_amount');
    const installmentsInput = document.getElementById('loan_installments');
    const maxCredit = <?php echo (float)$creditLimit; ?>;
    
    let amount = parseFloat(amountInput.value.replace(/\./g, '').replace(',', '.'));
    if (isNaN(amount)) amount = 0;
    
    // Clamp to max
    if (amount > maxCredit) {
        amount = maxCredit;
        amountInput.value = amount.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }

    const n = parseInt(installmentsInput.value);
    document.getElementById('display_installments').textContent = n + 'x';

    // Interest Calculation: 1% per month per installment
    // Simple logic: Total = Amount * (1 + 0.01 * n)
    const interestTotal = amount * (0.01 * n);
    const totalToPay = amount + interestTotal;
    const installmentValue = totalToPay / n;

    document.getElementById('result_interest').textContent = '+ R$ ' + interestTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('result_total').textContent = 'R$ ' + totalToPay.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('result_installment').textContent = 'R$ ' + installmentValue.toLocaleString('pt-BR', {minimumFractionDigits: 2});
}

// Reuse existing money mask and other scripts...
document.addEventListener('DOMContentLoaded', function() {
    // Chart, Masks... (omitted for brevity in this replace but should be kept)
    // Money Mask for loan
    const loanInput = document.getElementById('loan_amount');
    if (loanInput) {
        loanInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v === '') return;
            v = (parseInt(v) / 100).toFixed(2) + '';
            v = v.replace(".", ",");
            v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
            e.target.value = v;
            calculateLoan();
        });
    }

    const instInput = document.getElementById('loan_installments');
    if (instInput) {
        instInput.addEventListener('input', calculateLoan);
    }
    // Repopulate other masks if needed
    if (window.lucide) lucide.createIcons();
});

// Adding back the Chart and other Logic from previous version
// Premium Line Chart
const ctx = document.getElementById('cashbackChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [
            { 
                label: 'Ganhos', 
                data: <?php echo json_encode($chartEarned); ?>, 
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#22c55e'
            },
            { 
                label: 'Resgates', 
                data: <?php echo json_encode($chartUsed); ?>, 
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#ef4444'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: { 
            legend: { 
                position: 'bottom', 
                labels: { color: 'rgba(var(--text-main-rgb), 0.5)', font: { size: 11, weight: '700' }, padding: 20, usePointStyle: true } 
            } 
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }, 
                ticks: { color: 'rgba(var(--text-main-rgb), 0.4)', font: { size: 10 } } 
            },
            x: { 
                grid: { display: false }, 
                ticks: { color: 'rgba(var(--text-main-rgb), 0.4)', font: { size: 10 } } 
            }
        }
    }
});

async function requestWithdrawal(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    if (btn.disabled) return;
    const amountInput = document.getElementById('withdrawal_amount');
    const rawAmount = parseFloat(amountInput.value.replace(/\./g, '').replace(',', '.'));
    const availableBalance = <?php echo (float)$balance; ?>;
    if (availableBalance < 50) { UI.showToast('Saldo insuficiente para o mínimo de R$ 50.', 'warning'); return; }
    if (rawAmount > availableBalance) { UI.showToast('Saldo insuficiente!', 'error'); return; }
    if (!confirm('Confirmar saque?')) return;
    btn.disabled = true;
    try {
        const formData = new FormData(form);
        const res = await fetch('<?php echo SITE_URL; ?>/api/cashback/withdrawal/request', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) { UI.showToast(data.message, 'success'); setTimeout(() => window.location.reload(), 1500); }
        else { UI.showToast(data.message, 'error'); btn.disabled = false; }
    } catch (err) { UI.showToast('Erro na comunicação.', 'error'); btn.disabled = false; }
}

const moneyInput = document.getElementById('withdrawal_amount');
if (moneyInput) {
    moneyInput.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        if (v === '') return;
        v = (parseInt(v) / 100).toFixed(2) + '';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
        e.target.value = v;
    });
}
async function requestLoan() {
    const btn = document.getElementById('btn-loan-submit');
    if (btn.disabled) return;
    
    const amountStr = document.getElementById('loan_amount').value;
    const installments = document.getElementById('loan_installments').value;
    const totalToPayStr = document.getElementById('result_total').textContent.replace('R$ ', '');
    
    if (!amountStr || amountStr === '0,00') { UI.showToast('Informe um valor válido.', 'warning'); return; }
    if (!confirm('Deseja confirmar a solicitação de crédito?')) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> PROCESSANDO...';

    try {
        const formData = new FormData();
        formData.append('amount', amountStr);
        formData.append('installments', installments);
        formData.append('total_to_pay', totalToPayStr);

        const res = await fetch('<?php echo SITE_URL; ?>/api/cashback/loan/request', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            UI.showToast(data.message, 'success');
            closeLoanModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            UI.showToast(data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle"></i> CONTRATAR AGORA';
            if (window.lucide) lucide.createIcons();
        }
    } catch (err) {
        UI.showToast('Erro ao processar solicitação.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="check-circle"></i> CONTRATAR AGORA';
        if (window.lucide) lucide.createIcons();
    }
}
</script>
