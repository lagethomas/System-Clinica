<?php
/** @var array $invoices */
?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Faturas e Assinaturas</h2>
        <p style="color: var(--text-muted);">Acompanhe o status dos pagamentos das empresas no SaaS.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-primary" onclick="UI.showModal('Gerar Fatura Manual', document.getElementById('manualInvoiceFormTemplate').innerHTML)">
            <i data-lucide="plus-circle" class="icon-lucide"></i> Gerar Fatura Manual
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Fatura</th>
                    <th>Empresa</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="6" class="text-center" style="padding: 40px; color: var(--text-muted);">Nenhuma fatura encontrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td class="text-muted"><code style="background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; font-size: 11px;">#INV-<?php echo str_pad((string)$inv['id'], 5, '0', STR_PAD_LEFT); ?></code></td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($inv['company_name']); ?></div>
                            <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($inv['email']); ?></div>
                        </td>
                        <td style="color: var(--primary); font-weight: 700;">R$ <?php echo number_format((float)$inv['amount'], 2, ',', '.'); ?></td>
                        <td>
                            <i data-lucide="calendar" class="icon-lucide"></i> 
                            <?php echo date('d/m/Y', strtotime($inv['due_date'])); ?>
                        </td>
                        <td>
                            <?php if ($inv['status'] === 'paid'): ?>
                                <span class="badge status-active" style="padding: 4px 10px; font-size: 11px;"><i data-lucide="check" class="icon-lucide"></i> Paga</span>
                            <?php elseif ($inv['status'] === 'pending' && strtotime($inv['due_date']) < time()): ?>
                                <span class="badge status-danger" style="padding: 4px 10px; font-size: 11px;"><i data-lucide="alert-triangle" class="icon-lucide"></i> Atrasada</span>
                            <?php elseif ($inv['status'] === 'cancelled'): ?>
                                <span class="badge status-secondary" style="padding: 4px 10px; font-size: 11px;"><i data-lucide="ban" class="icon-lucide"></i> Cancelada</span>
                            <?php else: ?>
                                <span class="badge status-warning" style="padding: 4px 10px; font-size: 11px;"><i data-lucide="clock" class="icon-lucide"></i> Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($inv['status'] !== 'paid'): ?>
                                <a href="<?php echo SITE_URL; ?>/checkout/<?php echo $inv['id']; ?>" class="btn-primary" style="padding: 8px 12px; font-size: 12px; text-decoration: none;" target="_blank" title="Pagar Fatura">
                                    <i data-lucide="credit-card" class="icon-lucide"></i> Pagar
                                </a>
                            <?php else: ?>
                                <div style="color: #00ff7f; font-size: 12px; font-weight: 600;">
                                    <i data-lucide="check-circle" class="icon-lucide"></i> Pago em <?php echo date('d/m/y', strtotime($inv['paid_at'] ?? 'now')); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Template Fatura Manual -->
<template id="manualInvoiceFormTemplate">
    <form id="manualInvoiceForm" onsubmit="saveManualInvoice(event)">
        <input type="hidden" name="nonce" value="<?php echo $nonces['generate']; ?>">
        
        <div class="form-group mb-3">
            <label class="form-label">Empresa Cliente</label>
            <select name="company_id" class="form-control w-100" required>
                <option value="">-- Selecione a Empresa --</option>
                <?php foreach ($companies as $comp): ?>
                    <option value="<?php echo $comp['id']; ?>"><?php echo htmlspecialchars($comp['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-grid-2 mb-3">
            <div class="form-group">
                <label class="form-label">Valor (R$)</label>
                <input type="text" name="amount" class="form-control w-100 decimal-mask" placeholder="0,00" required>
            </div>
            <div class="form-group">
                <label class="form-label">Vencimento</label>
                <input type="date" name="due_date" class="form-control w-100" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>

        <div class="form-group mb-4">
            <label class="form-label">Descrição / Motivo</label>
            <input type="text" name="description" class="form-control w-100" placeholder="ex: Upgrade de plano, Taxa extra, etc." required>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
            <button type="submit" class="btn-primary" id="btnSaveInvoice">Gerar Fatura agora</button>
        </div>
    </form>
</template>

<script>
async function saveManualInvoice(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveInvoice');
    const oldText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="icon-lucide"></i> Gerando...';
        
        const formData = new FormData(e.target);
        const res = await fetch('<?php echo SITE_URL; ?>/api/admin/subscriptions/generate', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const data = await res.json();
        UI.showToast(data.message, data.success ? 'success' : 'error');
        
        if (data.success) {
            UI.closeModal('manualInvoiceModal');
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (err) {
        UI.showToast('Erro ao processar requisição', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = oldText;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    UI.initMasks();
});
</script>
