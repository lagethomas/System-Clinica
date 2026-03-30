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
