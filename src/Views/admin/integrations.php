<?php
/** @var array $settings */
/** @var string $active_tab */
/** @var string $msg */
?>


<div class="settings-tab-nav">
    <a href="?tab=email" class="nav-link-tab <?php echo $active_tab === 'email' ? 'active' : ''; ?>">
        <i data-lucide="mail" class="icon-lucide"></i> E-mail (SMTP)
    </a>
    <a href="?tab=mercadopago" class="nav-link-tab <?php echo $active_tab === 'mercadopago' ? 'active' : ''; ?>">
        <i data-lucide="credit-card" class="icon-lucide"></i> Mercado Pago
    </a>
</div>

<?php if ($msg): ?>
    <div class="alert-success-custom">
        <i data-lucide="check-circle" class="icon-lucide"></i> <?php echo $msg; ?>
    </div>
<?php endif; ?>

<div class="integration-card">
    <?php if ($active_tab === 'email'): ?>
        <form method="POST">
            <div class="integration-header">
                <i data-lucide="mail" class="icon-lucide"></i>
                <h5>Configurações de E-mail (SMTP)</h5>
            </div>
            <p class="integration-subtitle">Configure o servidor SMTP para o envio de notificações e e-mails do sistema.</p>
            
            <div class="form-grid-3 mb-4">
                <div class="form-group">
                    <label class="form-label">Host SMTP</label>
                    <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" class="form-control w-100" placeholder="ex: smtp.gmail.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Porta SMTP</label>
                    <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>" class="form-control w-100" placeholder="ex: 587">
                </div>
                <div class="form-group">
                    <label class="form-label">Segurança</label>
                    <select name="smtp_secure" class="form-control w-100">
                        <option value="" <?php echo ($settings['smtp_secure'] ?? '') === '' ? 'selected' : ''; ?>>Nenhum</option>
                        <option value="ssl" <?php echo ($settings['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL (Porta 465)</option>
                        <option value="tls" <?php echo ($settings['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS / STARTTLS (Porta 587)</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2 mb-4">
                <div class="form-group">
                    <label class="form-label">Usuário SMTP</label>
                    <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>" class="form-control w-100">
                </div>
                <div class="form-group">
                    <label class="form-label">Senha SMTP</label>
                    <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>" class="form-control w-100">
                </div>
            </div>

            <div class="form-grid-2 mb-4">
                <div class="form-group">
                    <label class="form-label">E-mail de Envio (From Email)</label>
                    <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>" class="form-control w-100" placeholder="ex: no-reply@seusistema.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Nome de Exibição (From Name)</label>
                    <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>" class="form-control w-100" placeholder="ex: SaaSFlow Core">
                </div>
            </div>

            <div class="integration-footer">
                <button type="submit" name="save_email" class="btn-primary btn-integration-save">
                    <i data-lucide="save" class="icon-lucide"></i> Salvar Integração de E-mail
                </button>
                <button type="button" onclick="sendTestEmail()" class="btn-secondary btn-integration-test">
                    <i data-lucide="paper-plane" class="icon-lucide"></i> Enviar E-mail Teste
                </button>
            </div>
        </form>
    <?php elseif ($active_tab === 'mercadopago'): ?>
        <form method="POST">
            <div class="integration-header">
                <i data-lucide="credit-card" class="icon-lucide"></i>
                <h5>Mercado Pago (SaaS)</h5>
            </div>
            <p class="integration-subtitle">Configure as credenciais de produção para receber mensalidades dos seus clientes.</p>

            <div class="card p-4 mb-4" style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid var(--border); border-radius: 15px;">
                <div class="form-group mb-4">
                    <label class="form-label">Access Token</label>
                    <input type="password" name="mp_access_token" value="<?php echo htmlspecialchars($settings['mp_access_token'] ?? ''); ?>" class="form-control w-100" placeholder="APP_USR-...">
                </div>
                <div class="form-group">
                    <label class="form-label">Public Key</label>
                    <input type="text" name="mp_public_key" value="<?php echo htmlspecialchars($settings['mp_public_key'] ?? ''); ?>" class="form-control w-100" placeholder="APP_USR-...">
                </div>
            </div>

            <div class="alert-info-custom mb-4" style="padding: 15px; border-radius: 10px; background: rgba(var(--primary-rgb), 0.1); color: var(--text-main); font-size: 13px; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="link" class="icon-lucide"></i> <span>URL de Webhook: <code><?php echo SITE_URL; ?>/api/webhook/mercadopago</code></span>
            </div>

            <div class="integration-footer">
                <button type="submit" name="save_mercadopago" class="btn-primary">
                    <i data-lucide="save" class="icon-lucide"></i> Salvar Credenciais Mercado Pago
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
async function sendTestEmail() {
    const email = prompt('Para qual e-mail deseja enviar o teste?');
    if (!email) return;

    const formData = new FormData();
    formData.append('email', email);

    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/admin/test_email', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        alert(data.message);
    } catch (e) {
        alert('Erro ao enviar teste.');
    }
}
</script>


