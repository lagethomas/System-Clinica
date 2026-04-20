<?php
/** @var array $withdrawals */
/** @var string $SITE_URL */
?>

<div class="cashback-admin-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-800">Solicitações de <span class="text-primary">Saque</span></h2>
            <p class="text-muted mb-0">Gerencie os pedidos de resgate de cashback dos seus clientes.</p>
        </div>
    </div>
</div>

<div class="module-tabs mb-4">
    <div class="tab-link active" onclick="switchTab(this, 'withdrawals')">
        <i data-lucide="banknote" class="icon-sm"></i> Resgates Pix
    </div>
    <div class="tab-link" onclick="switchTab(this, 'loans')">
        <i data-lucide="landmark" class="icon-sm"></i> Empréstimos ClubePet+
    </div>
</div>

<div id="tab-withdrawals" class="tab-content active">
    <div class="card border-0 shadow-sm">
        <div class="card-header-premium p-4 border-bottom d-flex justify-content-between align-items-center">
            <h3 class="m-0">Pedidos de Saque (Pix)</h3>
            <span class="badge bg-primary px-3 py-2" style="border-radius: 20px;">
                <?php echo count(array_filter($withdrawals, fn($w) => $w['status'] === 'pending')); ?> Pendentes
            </span>
        </div>
    <div class="table-responsive">
        <table class="premium-table w-100">
            <thead>
                <tr>
                    <th class="px-4">Data</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Tipo Pix</th>
                    <th>Chave Pix</th>
                    <th>Status</th>
                    <th class="text-center px-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($withdrawals)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="opacity-25 mb-3"><i data-lucide="inbox" style="width: 48px; height: 48px;"></i></div>
                            <h6 class="text-muted">Nenhuma solicitação registrada.</h6>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($withdrawals as $w): ?>
                        <tr id="withdraw-row-<?php echo $w['id']; ?>">
                            <td class="px-4 small"><?php echo date('d/m/Y H:i', strtotime($w['created_at'])); ?></td>
                            <td>
                                <div class="fw-800"><?php echo htmlspecialchars($w['tutor_nome']); ?></div>
                                <div class="text-muted small">ID Tutor: #<?php echo $w['tutor_id']; ?></div>
                            </td>
                            <td class="fw-800 text-primary" style="font-size: 16px;">R$ <?php echo number_format((float)$w['amount'], 2, ',', '.'); ?></td>
                            <td><span class="badge bg-light text-dark border small"><?php echo strtoupper($w['pix_type']); ?></span></td>
                            <td class="small"><code><?php echo htmlspecialchars($w['pix_key']); ?></code></td>
                            <td>
                                <?php if ($w['status'] === 'pending'): ?>
                                    <span class="status-badge status-info">Pendente</span>
                                <?php elseif ($w['status'] === 'paid'): ?>
                                    <span class="status-badge status-success">Pago em <?php echo date('d/m/Y', strtotime($w['paid_at'])); ?></span>
                                <?php else: ?>
                                    <span class="status-badge status-danger">Cancelado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center px-4">
                                <?php if ($w['status'] === 'pending'): ?>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button class="btn-icon btn-outline-primary" title="Gerar QR Code Pix" onclick="showPixQRCode('<?php echo $w['pix_key']; ?>', '<?php echo $w['pix_type']; ?>', '<?php echo $w['amount']; ?>', '<?php echo $w['tutor_nome']; ?>')">
                                            <i data-lucide="qr-code"></i>
                                        </button>
                                        <button class="btn-icon btn-outline-success" title="Confirmar Pagamento" onclick="confirmPayment(<?php echo $w['id']; ?>)">
                                            <i data-lucide="check-circle"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">---</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div> <!-- End of tab-withdrawals -->

<div id="tab-loans" class="tab-content">
    <div class="card border-0 shadow-sm">
        <div class="card-header-premium p-4 border-bottom d-flex justify-content-between align-items-center">
            <h3 class="m-0">Solicitações de Empréstimo</h3>
            <span class="badge bg-primary px-3 py-2" style="border-radius: 20px;">
                <?php echo count(array_filter($loans, fn($l) => $l['status'] === 'pending')); ?> Pendentes
            </span>
        </div>
        <div class="table-responsive">
            <table class="premium-table w-100">
                <thead>
                    <tr>
                        <th class="px-4">Data</th>
                        <th>Cliente</th>
                        <th>Valor Solicitado</th>
                        <th>Parcelas</th>
                        <th>Total a Pagar</th>
                        <th>Status</th>
                        <th class="text-center px-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($loans)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted opacity-50">
                                <div class="opacity-25 mb-3"><i data-lucide="landmark" style="width: 48px; height: 48px;"></i></div>
                                <h6 class="text-muted">Nenhuma solicitação de crédito registrada.</h6>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($loans as $l): ?>
                            <tr id="loan-row-<?php echo $l['id']; ?>">
                                <td class="px-4 small"><?php echo date('d/m/Y', strtotime($l['created_at'])); ?></td>
                                <td>
                                    <div class="fw-800"><?php echo htmlspecialchars($l['tutor_nome']); ?></div>
                                </td>
                                <td><strong class="text-main">R$ <?php echo number_format($l['amount'], 2, ',', '.'); ?></strong></td>
                                <td><?php echo $l['installments']; ?>x</td>
                                <td><span class="text-muted small">R$ <?php echo number_format($l['total_to_pay'], 2, ',', '.'); ?></span></td>
                                <td>
                                    <span class="status-badge status-<?php echo $l['status'] === 'approved' ? 'success' : ($l['status'] === 'pending' ? 'info' : 'danger'); ?>">
                                        <?php echo ucfirst($l['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center px-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn-icon btn-outline-primary" onclick='viewLoanDetails(<?php echo json_encode($l); ?>)' title="Ver Detalhes">
                                            <i data-lucide="eye"></i>
                                        </button>
                                        <?php if ($l['status'] === 'pending'): ?>
                                            <button class="btn-icon btn-outline-success" onclick="processLoan(<?php echo $l['id']; ?>, 'approve')" title="Aprovar">
                                                <i data-lucide="check"></i>
                                            </button>
                                            <button class="btn-icon btn-outline-danger" onclick="processLoan(<?php echo $l['id']; ?>, 'reject')" title="Recusar">
                                                <i data-lucide="x"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Modal Detalhes Empréstimo -->
<div id="loan-detail-modal" class="modal-premium" style="display: none;">
    <div class="modal-premium-content p-4" style="max-width: 450px;">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h5 class="m-0 fw-800">Detalhes do Crédito</h5>
            <button onclick="closeLoanDetailModal()" class="btn-close-modal"><i data-lucide="x"></i></button>
        </div>
        <div class="loan-info-list" style="font-size: 14px;">
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Cliente:</span>
                <strong id="det-client"></strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Valor Principal:</span>
                <strong class="text-primary" id="det-amount" style="font-size: 18px;"></strong>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Parcelamento:</span>
                <strong id="det-installments"></strong>
            </div>
            <div class="d-flex justify-content-between mb-3 border-top pt-3">
                <span class="text-muted">Total c/ Juros (1% a.m.):</span>
                <strong id="det-total"></strong>
            </div>
            <div class="d-flex justify-content-between mt-3 text-muted small">
                <span>Data da Solicitação:</span>
                <span id="det-date"></span>
            </div>
        </div>
        <button onclick="closeLoanDetailModal()" class="btn-primary w-100 mt-4 py-3">FECHAR</button>
    </div>
</div>

<!-- Modal QR Code -->
<div id="pix-modal" class="modal-premium" style="display: none;">
    <div class="modal-premium-content p-4 text-center" style="max-width: 400px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0 fw-800">Pagamento Pix</h5>
            <button onclick="closePixModal()" class="btn-close-modal"><i data-lucide="x"></i></button>
        </div>
        
        <div class="qr-box p-3 bg-white mb-3" style="border-radius: 20px;">
            <img id="qr-image" src="" alt="QR Code Pix" style="width: 200px; height: 200px;">
        </div>
        
        <div class="text-start mb-4">
            <div class="small text-muted mb-1">Pagar para:</div>
            <div class="fw-800 mb-2" id="modal-client-name">---</div>
            <div class="small text-muted mb-1">Valor:</div>
            <div class="fw-800 text-primary" style="font-size: 20px;" id="modal-amount">R$ 0,00</div>
        </div>

        <div class="alert alert-warning small p-2 text-start mb-4" style="font-size: 11px;">
            <i data-lucide="alert-triangle" class="icon-sm"></i> Escaneie o código com seu app bancário para realizar a transferência manual.
        </div>

        <button onclick="closePixModal()" class="btn-primary w-100">FECHAR</button>
    </div>
</div>

<style>
.module-tabs { display: flex; gap: 8px; background: rgba(0, 0, 0, 0.03); padding: 6px; border-radius: 14px; border: 1px solid var(--border); width: fit-content; }
.tab-link { padding: 10px 20px; border-radius: 10px; color: var(--text-muted); font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.25s; display: flex; align-items: center; gap: 8px; }
.tab-link:hover { background: rgba(var(--primary-rgb), 0.05); color: var(--primary); }
.tab-link.active { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3); }
.tab-content { display: none; animation: fadeIn 0.3s ease; }
.tab-content.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
    background: transparent;
}
.btn-outline-primary { border-color: var(--primary); color: var(--primary); }
.btn-outline-primary:hover { background: var(--primary); color: white; }
.btn-outline-success { border-color: #22c55e; color: #22c55e; }
.btn-outline-success:hover { background: #22c55e; color: white; }
.btn-outline-danger { border-color: #ef4444; color: #ef4444; }
.btn-outline-danger:hover { background: #ef4444; color: white; }

.modal-premium {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}
.modal-premium-content {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border);
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
    width: 90%;
}
.btn-close-modal {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
}
</style>

<script>
function showPixQRCode(key, type, amount, name) {
    const formattedAmount = parseFloat(amount).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    document.getElementById('modal-client-name').textContent = name;
    document.getElementById('modal-amount').textContent = formattedAmount;
    
    // Using Google Charts API for simple QR Code generation
    // Ideally this would be a real Pix Payload, but as a shortcut we show the key
    const qrData = encodeURIComponent(`PIX:${key}|VALOR:${amount}`);
    document.getElementById('qr-image').src = `https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=${qrData}`;
    
    document.getElementById('pix-modal').style.display = 'flex';
}

function closePixModal() {
    document.getElementById('pix-modal').style.display = 'none';
}

async function confirmPayment(id) {
    if (!confirm('Deseja marcar esta solicitação como PAGA? Certifique-se de que a transferência foi concluída.')) return;

    try {
        const formData = new FormData();
        formData.append('id', id);

        const res = await fetch('<?php echo SITE_URL; ?>/api/cashback/withdrawal/mark-paid', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            UI.showToast('Saque marcado como pago!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            UI.showToast(data.message, 'error');
        }
    } catch (err) {
        UI.showToast('Erro ao processar.', 'error');
    }
}
function switchTab(el, tabId) {
    document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById(`tab-${tabId}`).classList.add('active');
}

function viewLoanDetails(loan) {
    document.getElementById('det-client').textContent = loan.tutor_nome;
    document.getElementById('det-amount').textContent = 'R$ ' + parseFloat(loan.amount).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('det-installments').textContent = loan.installments + 'x';
    document.getElementById('det-total').textContent = 'R$ ' + parseFloat(loan.total_to_pay).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('det-date').textContent = new Date(loan.created_at).toLocaleString('pt-BR');
    document.getElementById('loan-detail-modal').style.display = 'flex';
}

function closeLoanDetailModal() {
    document.getElementById('loan-detail-modal').style.display = 'none';
}

async function processLoan(id, action) {
    const msg = action === 'approve' ? 'Deseja APROVAR este crédito? O valor será adicionado ao saldo do cliente imediatamente.' : 'Deseja REJEITAR esta solicitação?';
    if (!confirm(msg)) return;

    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);

        const res = await fetch('<?php echo SITE_URL; ?>/api/cashback/loan/process', {
            method: 'POST',
            body: formData
        });
        
        const text = await res.text();
        console.log('Loan Process Response:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch(e) {
            console.error('Invalid JSON response:', text);
            UI.showToast('Erro na resposta do servidor.', 'error');
            return;
        }
        
        if (data.success) {
            UI.showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            console.error('Server error:', data);
            UI.showToast(data.message || 'Erro interno.', 'error');
        }
    } catch (err) {
        console.error('Process Loan AJAX error:', err);
        UI.showToast('Erro ao processar.', 'error');
    }
}
</script>
