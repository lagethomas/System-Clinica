<?php
/** @var array $company */
/** @var string $nonce */
/** @var string $active_tab */
/** @var array $themes */

$v = time();
$current_theme = $company['theme'] ?? 'gold-black';
?>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/settings.css?v=<?php echo $v; ?>">

<div class="settings-tab-nav">
    <a href="?tab=general" class="nav-link-tab <?php echo ($active_tab === 'general' || empty($active_tab)) ? 'active' : ''; ?>">
        <i data-lucide="settings" class="icon-lucide"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i data-lucide="palette" class="icon-lucide"></i> Temas
    </a>
    <a href="?tab=mercadopago" class="nav-link-tab <?php echo $active_tab === 'mercadopago' ? 'active' : ''; ?>">
        <i data-lucide="credit-card" class="icon-lucide"></i> Mercado Pago
    </a>
</div>

<div class="card settings-main-card">
    <form id="company-settings-form" enctype="multipart/form-data" onsubmit="saveCompanySettings(event)">
        <input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce); ?>">
        
        <?php if ($active_tab === 'general' || empty($active_tab)): ?>
            <div class="settings-header-box" style="padding: 30px 30px 0 30px; border-bottom: none;">
                <h5><i data-lucide="settings" class="icon-lucide"></i> Configurações Gerais</h5>
                <p>Gerencie a identidade básica e funcionamento da sua unidade.</p>
            </div>
            
            <div class="form-grid-4 mb-4" style="padding: 0 30px;">
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="info" class="icon-lucide"></i> Nome</label>
                    <div class="form-group mt-2">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>" class="form-control" placeholder="ex: Clínica Vet Saúde" required>
                    </div>
                </div>
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="contact" class="icon-lucide"></i> Documento (CNPJ/CPF)</label>
                    <div class="form-group mt-2">
                        <input type="text" name="document" value="<?php echo htmlspecialchars($company['document'] ?? ''); ?>" class="form-control mask-cnpj" placeholder="00.000.000/0000-00">
                    </div>
                </div>
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="mail" class="icon-lucide"></i> E-mail</label>
                    <div class="form-group mt-2">
                        <input type="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" class="form-control" placeholder="contato@clinica.com">
                    </div>
                </div>
                <div class="upload-box-wrapper p-3">
                    <label class="upload-label"><i data-lucide="phone" class="icon-lucide"></i> WhatsApp</label>
                    <div class="form-group mt-2">
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>" class="form-control mask-phone" placeholder="(00) 00000-0000">
                    </div>
                </div>
    </div>

    <div class="form-grid-1 mb-4" style="padding: 0 30px;">
        <div class="upload-box-wrapper p-3">
            <label class="upload-label"><i data-lucide="globe" class="icon-lucide"></i> Domínio Customizado (Ex: www.suaclinica.com.br)</label>
            <div class="form-group mt-2">
                <input type="text" name="custom_domain" value="<?php echo htmlspecialchars($company['custom_domain'] ?? ''); ?>" class="form-control" placeholder="ex: www.spivet.app">
                <small class="text-muted" style="display: block; margin-top: 5px;">Para usar seu próprio domínio, crie um apontamento <b>CNAME</b> no seu provedor de DNS para: <b style="color: var(--primary);"><?php echo parse_url(SITE_URL, PHP_URL_HOST); ?></b></small>
            </div>
        </div>
    </div>

            <div class="form-grid-5 mb-4" style="padding: 0 30px;">
                <div class="upload-box-wrapper p-3" style="grid-column: span 2;">
                    <label class="upload-label"><i data-lucide="image" class="icon-lucide"></i> Logotipo</label>
                    <div class="upload-flex-container">
                        <div id="preview-logo" class="upload-preview-box">
                            <?php if (!empty($company['logo'])): ?>
                                <img src="<?php echo SITE_URL . $company['logo']; ?>" alt="Logo" class="logo-img">
                            <?php else: ?>
                                <i data-lucide="dog" class="icon-lucide"></i>
                            <?php endif; ?>
                        </div>
                        <div class="upload-actions-flex">
                            <label for="logo-upload" class="btn-primary" style="padding: 8px 15px; font-size: 12px; border-radius: 8px; cursor: pointer;">
                                <i data-lucide="upload" class="icon-lucide"></i> Escolher Logo
                                <input type="file" id="logo-upload" name="logo" onchange="previewImage(this, 'preview-logo', 'logo-img')" style="display: none;" accept="image/*">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="upload-box-wrapper p-3" style="grid-column: span 3;">
                    <label class="upload-label"><i data-lucide="monitor" class="icon-lucide"></i> Background Login</label>
                    <div class="upload-flex-container">
                        <div id="preview-bg" class="upload-preview-box">
                            <?php if (!empty($company['background_image'])): ?>
                                <img src="<?php echo SITE_URL . $company['background_image']; ?>" alt="BG" class="bg-img">
                            <?php else: ?>
                                <i data-lucide="image" class="icon-lucide"></i>
                            <?php endif; ?>
                        </div>
                        <div class="upload-actions-flex">
                            <label for="bg-upload" class="btn-primary" style="padding: 8px 15px; font-size: 12px; border-radius: 8px; cursor: pointer;">
                                <i data-lucide="upload" class="icon-lucide"></i> Escolher Fundo
                                <input type="file" id="bg-upload" name="background_image" onchange="previewImage(this, 'preview-bg', 'bg-img')" style="display: none;" accept="image/*">
                            </label>
                        </div>
                    </div>
                </div>
            </div>


            <div class="settings-footer-section" style="padding: 30px;">
                <button type="submit" class="btn-primary settings-save-btn">
                    <span class="btn-text"><i data-lucide="save" class="icon-lucide"></i> Salvar Agora</span>
                    <span class="btn-loader" style="display: none;"><i data-lucide="loader" class="icon-lucide"></i> Salvando...</span>
                </button>
            </div>

        <?php elseif ($active_tab === 'themes'): ?>
            <div class="settings-header-box" style="padding: 30px;">
                <h5><i data-lucide="palette" class="icon-lucide"></i> Personalização de Tema</h5>
                <p>Selecione a identidade visual que será aplicada ao seu painel administrativo.</p>
            </div>
            <div class="theme-grid" style="padding: 0 30px;">
                <?php foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_theme);
                ?>
                    <label class="theme-card-label">
                        <input type="radio" name="theme" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> style="display: none;">
                        <div class="theme-card-ui">
                            <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>;">
                                <div class="theme-card-accent" style="background: <?php echo $theme['color']; ?>; box-shadow: 0 0 15px <?php echo $theme['color']; ?>88;"></div>
                            </div>
                            <div class="text-center">
                                <span class="theme-card-name"><?php echo $theme['name']; ?></span>
                            </div>
                            <div class="theme-check-icon"><i data-lucide="check" class="icon-lucide"></i></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="settings-footer-section" style="padding: 30px;">
                <button type="submit" class="btn-primary settings-save-btn"><i data-lucide="save" class="icon-lucide"></i> Aplicar Tema e Cor</button>
            </div>
        <?php elseif ($active_tab === 'mercadopago'): ?>

            <div class="settings-header-box" style="padding: 25px 30px 5px 30px; border-bottom: none;">
                <h5><i data-lucide="credit-card" class="icon-lucide" style="width:16px;height:16px;"></i> Mercado Pago</h5>
                <p>Configure suas credenciais para aceitar pagamentos online via Pix e cartão na loja da sua clínica.</p>
            </div>

            <!-- Delivery Fee & Status -->
            <div style="padding: 20px 30px 0; display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                <div class="floating-group" style="width: 200px;">
                    <input type="text" name="taxa_entrega" id="taxa_entrega" class="form-control"
                           value="<?php echo number_format((float)($company['taxa_entrega'] ?? 0), 2, ',', '.'); ?>"
                           placeholder=" ">
                    <label for="taxa_entrega">Taxa de Entrega (R$)</label>
                </div>

                <label class="selectable-card" style="flex-direction: row; gap: 12px; width: auto; padding: 14px 20px; cursor: pointer; margin-bottom: 0;">
                    <input type="checkbox" name="mp_enabled" id="mp_enabled_toggle"
                           <?php echo ($company['mp_enabled'] ?? 0) == 1 ? 'checked' : ''; ?>
                           style="display:none;" onchange="toggleMpStatus(this)">
                    <span id="mp-toggle-icon" style="display:flex;align-items:center;">
                        <i data-lucide="<?php echo ($company['mp_enabled'] ?? 0) == 1 ? 'toggle-right' : 'toggle-left'; ?>"
                           class="icon-lucide"
                           style="width:24px;height:24px;color:<?php echo ($company['mp_enabled'] ?? 0) == 1 ? 'var(--primary)' : 'var(--text-muted)'; ?>;"></i>
                    </span>
                    <span style="font-size:14px;font-weight:600;">
                        Pagamento Online via Mercado Pago:
                        <strong id="mp-status-label" style="color:<?php echo ($company['mp_enabled'] ?? 0) == 1 ? 'var(--primary)' : 'var(--text-muted)'; ?>;">
                            <?php echo ($company['mp_enabled'] ?? 0) == 1 ? 'ATIVADO' : 'DESATIVADO'; ?>
                        </strong>
                    </span>
                </label>
            </div>

            <div class="form-grid-4" style="padding: 20px 30px 10px;">
                <div class="form-group" style="grid-column: span 2;">
                    <div class="floating-group">
                        <input type="text" name="mp_public_key" class="form-control" id="mp_public_key"
                               value="<?php echo htmlspecialchars($company['mp_public_key'] ?? ''); ?>"
                               placeholder=" ">
                        <label for="mp_public_key">Public Key</label>
                    </div>
                    <small class="text-muted" style="display:block;margin-top:4px;">Começa com <code>APP_USR-</code>. Encontrada em: Mercado Pago → Suas Integrações → Credenciais.</small>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <div class="floating-group password-toggle-wrapper">
                        <input type="password" name="mp_access_token" class="form-control" id="mp_access_token"
                               value="<?php echo htmlspecialchars($company['mp_access_token'] ?? ''); ?>"
                               placeholder=" ">
                        <label for="mp_access_token">Access Token (Produção)</label>
                        <button type="button" class="password-toggle-btn" onclick="UI.togglePassword(this, 'mp_access_token')" tabindex="-1">
                            <i data-lucide="eye" class="icon-lucide"></i>
                        </button>
                    </div>
                    <small class="text-muted" style="display:block;margin-top:4px;">⚠️ Use o token de <strong>Produção</strong>, não de teste. Este campo é sensível — nunca compartilhe.</small>
                </div>
            </div>

            <!-- Help Guide -->
            <div style="padding: 0 30px 20px;">
                <div style="background: rgba(var(--primary-rgb), 0.06); border: 1px solid rgba(var(--primary-rgb), 0.15); border-radius: 14px; padding: 18px 22px;">
                    <p style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:10px;">
                        <i data-lucide="info" class="icon-lucide" style="width:14px;height:14px;"></i>
                        Como obter suas credenciais
                    </p>
                    <ol style="font-size:13px;color:var(--text-muted);line-height:2;padding-left:18px;">
                        <li>Acesse <a href="https://www.mercadopago.com.br/developers/panel" target="_blank" style="color:var(--primary);">mercadopago.com.br/developers/panel</a></li>
                        <li>Vá em <strong>Suas Integrações</strong> → selecione ou crie uma aplicação</li>
                        <li>Clique em <strong>Credenciais de Produção</strong></li>
                        <li>Copie a <strong>Public Key</strong> e o <strong>Access Token</strong> e cole acima</li>
                        <li>Ative o toggle e salve as configurações</li>
                    </ol>
                </div>
            </div>

            <div class="settings-footer-section" style="padding: 20px 30px; border-top: 1px solid var(--border);">
                <button type="submit" class="btn-primary settings-save-btn">
                    <span class="btn-text"><i data-lucide="save" class="icon-lucide"></i> Salvar Integração</span>
                    <span class="btn-loader" style="display:none;"><i data-lucide="loader" class="icon-lucide"></i> Salvando...</span>
                </button>
            </div>

        <?php endif; ?>
    </form>
</div>

<script>
function previewImage(input, previewId, imgClass) {
    const preview = document.getElementById(previewId);
    if (!preview) return;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = preview.querySelector('img');
            if (!img) {
                preview.innerHTML = '';
                img = document.createElement('img');
                img.className = imgClass;
                preview.appendChild(img);
            }
            img.src = e.target.result;
            preview.classList.add('pulse-preview');
            setTimeout(() => preview.classList.remove('pulse-preview'), 500);
            UI.showToast('Imagem selecionada!', 'info');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleMpStatus(checkbox) {
    const isOn = checkbox.checked;
    const icon = document.getElementById('mp-toggle-icon');
    const label = document.getElementById('mp-status-label');
    if (icon) {
        icon.innerHTML = `<i data-lucide="${isOn ? 'toggle-right' : 'toggle-left'}" class="icon-lucide" style="width:24px;height:24px;color:${isOn ? 'var(--primary)' : 'var(--text-muted)'};"></i>`;
        if (window.lucide) lucide.createIcons({ nodes: [icon] });
    }
    if (label) {
        label.textContent = isOn ? 'ATIVADO' : 'DESATIVADO';
        label.style.color = isOn ? 'var(--primary)' : 'var(--text-muted)';
    }
}

async function saveCompanySettings(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const text = btn.querySelector('.btn-text');
    const loader = btn.querySelector('.btn-loader');
    
    if (btn.disabled) return;
    btn.disabled = true;
    if(text) text.style.display = 'none';
    if(loader) loader.style.display = 'inline-block';

    try {
        const formData = new FormData(form);
        const res = await fetch('<?php echo SITE_URL; ?>/api/company-settings/save', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            UI.showToast('Configurações salvas!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            UI.showToast(data.message || 'Erro ao salvar.', 'error');
            btn.disabled = false;
            if(text) text.style.display = 'inline-block';
            if(loader) loader.style.display = 'none';
        }
    } catch (err) {
        UI.showToast('Erro de conexão.', 'error');
        btn.disabled = false;
        if(text) text.style.display = 'inline-block';
        if(loader) loader.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const docInput = document.querySelector('.mask-cnpj');
    if(docInput) {
        docInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 14) v = v.substring(0, 14);
            if (v.length <= 11) {
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                v = v.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = v;
        });
    }

    const phoneInput = document.querySelector('.mask-phone');
    if(phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 11) v = v.substring(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = v;
        });
    }

    const moneyInputs = document.querySelectorAll('.mask-money');
    moneyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v === '') return;
            v = (parseInt(v) / 100).toFixed(2) + '';
            v = v.replace(".", ",");
            v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
            e.target.value = v;
        });
    });
});
</script>
