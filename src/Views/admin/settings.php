<?php
/** @var array $settings */
/** @var string $active_tab */
?>


<div class="settings-tab-nav">
    <a href="?tab=general" class="nav-link-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <i data-lucide="settings" class="icon-lucide"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i data-lucide="palette" class="icon-lucide"></i> Temas
    </a>
    <a href="?tab=security" class="nav-link-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
        <i data-lucide="shield" class="icon-lucide"></i> Segurança
    </a>
    <a href="?tab=billing" class="nav-link-tab <?php echo $active_tab === 'billing' ? 'active' : ''; ?>">
        <i data-lucide="receipt" class="icon-lucide"></i> Faturamento
    </a>
</div>

<div class="card settings-main-card">
    <?php if ($active_tab === 'general'): ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="nonce" value="<?php echo $nonces['general']; ?>">
            <div class="settings-header-box">
                <h5><i data-lucide="settings" class="icon-lucide"></i> Configurações Gerais</h5>
                <p>Configurações básicas de identidade e comportamento do sistema.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group mb-4">
                    <label class="form-label">Nome do Sistema</label>
                    <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" class="form-control w-100">
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Logo do Sistema (WebP Recomendado)</label>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <?php if (!empty($settings['system_logo'])): ?>
                            <img src="<?php echo SITE_URL . $settings['system_logo']; ?>" style="height: 40px; border-radius: 4px; border: 1px solid var(--border);">
                        <?php endif; ?>
                        <div class="modern-upload" style="flex: 1;">
                            <input type="file" name="system_logo" id="logo-input" accept="image/*" onchange="document.getElementById('logo-preview-text').innerText = this.files[0].name">
                            <label for="logo-input">
                                <i data-lucide="image" class="icon-lucide"></i>
                                <span id="logo-preview-text">Selecionar Logo</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group mb-4 mt-2">
                <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <h6 class="mb-0">Ativar Logs do Sistema</h6>
                        <small class="text-muted">Registrar erros e atividades no diretório /logs</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_system_logs" value="1" <?php echo ($settings['enable_system_logs'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <button type="submit" name="save_general" class="btn-primary">
                <i data-lucide="save" class="icon-lucide"></i> Salvar Alterações
            </button>
        </form>

    <?php elseif ($active_tab === 'themes'): ?>
        <form method="POST">
            <input type="hidden" name="nonce" value="<?php echo $nonces['theme']; ?>">
            <div class="settings-header-box">
                <h5><i data-lucide="palette" class="icon-lucide"></i> Personalização de Tema</h5>
                <p>Selecione a identidade visual que será aplicada a todos os usuários do sistema.</p>
            </div>

            <div class="theme-grid">
                <?php 
                $themes = ThemeHelper::getAvailableThemes();
                $current_theme = $settings['system_theme'] ?? 'gold-black';
                
                foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_theme);
                ?>
                    <label class="theme-card-label">
                        <input type="radio" name="system_theme" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> style="display: none;">
                        <div class="theme-card-ui">
                            <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>;">
                                <div class="theme-card-accent" style="background: <?php echo $theme['color']; ?>; box-shadow: 0 0 15px <?php echo $theme['color']; ?>88;"></div>
                                <div class="theme-card-subaccent" style="background: <?php echo ($theme['bg'] == '#ffffff' || $theme['bg'] == 'white') ? '#eee' : 'rgba(255,255,255,0.1)'; ?>;"></div>
                            </div>
                            <div class="text-center">
                                <span class="theme-card-name"><?php echo $theme['name']; ?></span>
                            </div>
                            <div class="theme-check-icon" style="display: <?php echo $isSelected ? 'flex' : 'none'; ?>;">
                                <i data-lucide="check" class="icon-lucide"></i>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_theme" class="btn-primary">
                <i data-lucide="save" class="icon-lucide"></i> Aplicar Tema Selecionado
            </button>
        </form>

    <?php elseif ($active_tab === 'security'): ?>
        <form method="POST">
            <input type="hidden" name="nonce" value="<?php echo $nonces['security']; ?>">
            <div class="settings-header-box">
                <h5><i data-lucide="shield" class="icon-lucide"></i> Segurança e Proteção</h5>
                <p>Configure políticas de retenção de dados e proteção contra acessos indevidos.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <!-- Retenção de Logs -->
                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="history" class="icon-lucide"></i> Retenção de Histórico
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Manter Logs por (Dias)</label>
                        <input type="number" name="security_log_days" value="<?php echo htmlspecialchars($settings['security_log_days'] ?? '30'); ?>" class="form-control" placeholder="Ex: 30">
                        <small class="text-muted" style="font-size: 11px;">Logs mais antigos que este período serão excluídos automaticamente.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size:13px;">Limite de Registros (Quantidade)</label>
                        <input type="number" name="security_log_limit" value="<?php echo htmlspecialchars($settings['security_log_limit'] ?? '10000'); ?>" class="form-control" placeholder="Ex: 5000">
                        <small class="text-muted" style="font-size: 11px;">Cap máximo de logs no banco de dados.</small>
                    </div>
                </div>

                <!-- Proteção de Login -->
                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="shield" class="icon-lucide"></i> Proteção de Acesso
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Máximo de Tentativas Erradas</label>
                        <input type="number" name="security_max_attempts" value="<?php echo htmlspecialchars($settings['security_max_attempts'] ?? '5'); ?>" class="form-control">
                        <small class="text-muted" style="font-size: 11px;">Bloqueia o usuário após atingir este limite.</small>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Tempo de Bloqueio (Minutos)</label>
                        <input type="number" name="security_lockout_time" value="<?php echo htmlspecialchars($settings['security_lockout_time'] ?? '15'); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <h6 class="mb-0" style="font-size:13px;">Exigir Senha Forte</h6>
                                <small class="text-muted" style="font-size: 11px;">Mínimo 8 caracteres, números e letras.</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="security_strong_password" value="1" <?php echo ($settings['security_strong_password'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                </div>

                <!-- Sessão e Sessões Ativas -->
                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="clock" class="icon-lucide"></i> Gerenciamento de Sessão
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Tempo de Inatividade (Minutos)</label>
                        <input type="number" name="security_session_timeout" value="<?php echo htmlspecialchars($settings['security_session_timeout'] ?? '60'); ?>" class="form-control">
                        <small class="text-muted" style="font-size: 11px;">Desloga o usuário automaticamente após este tempo.</small>
                    </div>
                    <div class="form-group mb-4">
                        <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <h6 class="mb-0" style="font-size:13px;">Sessão Única por Usuário</h6>
                                <small class="text-muted" style="font-size: 11px;">Derruba logins anteriores ao entrar.</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="security_single_session" value="1" <?php echo ($settings['security_single_session'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <h6 class="mb-0" style="font-size:13px;">Bloqueio de IP por Erros</h6>
                                <small class="text-muted" style="font-size: 11px;">Bloqueia o IP globalmente no servidor.</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="security_ip_lockout" value="1" <?php echo ($settings['security_ip_lockout'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_security" class="btn-primary">
                <i data-lucide="save" class="icon-lucide"></i> Salvar Configurações de Segurança
            </button>
        </form>

    <?php elseif ($active_tab === 'billing'): ?>
        <form method="POST">
            <input type="hidden" name="nonce" value="<?php echo $nonces['billing']; ?>">
            <div class="settings-header-box">
                <h5><i data-lucide="receipt" class="icon-lucide"></i> Regras de Faturamento e Cobrança</h5>
                <p>Gerencie como o sistema lida com pagamentos, vencimentos e suspensão de acesso.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="clock" class="icon-lucide"></i> Suspensão e Carência
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Prazo de Carência (Dias)</label>
                        <input type="number" name="grace_period" value="<?php echo htmlspecialchars($settings['grace_period'] ?? '2'); ?>" class="form-control" required>
                        <small class="text-muted" style="font-size: 11px;">Dias extras de uso após o vencimento da fatura antes do bloqueio total.</small>
                    </div>
                </div>

                <div class="card p-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); border-radius: 15px;">
                    <h6 style="font-weight: 700; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="bell" class="icon-lucide"></i> Alertas e Notificações
                    </h6>
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-size:13px;">Antecedência de Alerta (Dias)</label>
                        <input type="number" name="days_before_notify" value="<?php echo htmlspecialchars($settings['days_before_notify'] ?? '5'); ?>" class="form-control" required>
                        <small class="text-muted" style="font-size: 11px;">Quantos dias antes do vencimento o sistema deve começar a notificar no sino.</small>
                    </div>
                </div>
            </div>

            <button type="submit" name="save_billing" class="btn-primary">
                <i data-lucide="save" class="icon-lucide"></i> Salvar Configurações de Faturamento
            </button>
        </form>
    <?php endif; ?>
</div>
