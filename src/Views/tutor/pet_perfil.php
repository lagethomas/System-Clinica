<div class="page-header d-flex align-items-center justify-content-between mb-0">
    <div class="page-header-info">
        <div class="d-flex align-items-center gap-3">
            <a href="<?php echo SITE_URL; ?>/app/tutor/dashboard" class="btn-secondary-icon" title="Voltar ao Painel">
                <i data-lucide="arrow-left" class="icon-lucide"></i>
            </a>
            <div class="table-thumb" style="width: 80px; height: 80px; border-radius: 20px;">
                <?php if (!empty($pet['foto_url'])): ?>
                    <img src="<?php echo SITE_URL . $pet['foto_url']; ?>" alt="<?php echo $pet['nome']; ?>">
                <?php else: ?>
                    <i data-lucide="dog" class="icon-lucide" style="opacity: 0.2;"></i>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="margin-0"><?php echo htmlspecialchars($pet['nome']); ?></h1>
                <p class="small text-primary font-weight-bold text-uppercase tracking-1 mb-0">
                    <?php echo htmlspecialchars($pet['especie'] ?? 'Pet'); ?> • <?php echo htmlspecialchars($pet['raca'] ?? 'SDR'); ?>
                </p>
                <div class="badge-status <?php echo ($pet['plano_status'] == 'active') ? 'active' : 'inactive'; ?> mt-2">
                    <span class="badge-dot" style="background: <?php echo ($pet['plano_status'] == 'active') ? 'var(--success)' : 'var(--danger)'; ?>;"></span>
                    <?php echo ($pet['plano_status'] == 'active') ? 'Plano Ativo' : 'Sem Plano Ativo'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid mt-4" style="grid-template-columns: repeat(12, 1fr); gap: 24px;">
    <!-- Prontuário Médico -->
    <div class="dashboard-card" style="grid-column: span 8; height: fit-content;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-circle-box" style="background: rgba(var(--primary-rgb), 0.1);">
                    <i data-lucide="clipboard-list" class="icon-lucide"></i>
                </div>
                <h3 class="margin-0">Histórico de Atendimentos</h3>
            </div>
        </div>

        <div class="timeline-container">
            <?php if (empty($consultas)): ?>
                <div class="text-center py-5">
                    <p class="text-muted">Nenhum histórico médico disponível para este pet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($consultas as $con): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker <?php echo ($con['status'] == 'Concluída') ? 'success' : 'primary'; ?>"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h4 class="margin-0 text-main"><?php echo htmlspecialchars($con['servico'] ?? 'Atendimento'); ?></h4>
                                    <div class="text-muted small">
                                        <?php echo date('d/m/Y', strtotime($con['data_consulta'])); ?> • 
                                        <?php echo (!empty($con['hora_consulta']) && $con['hora_consulta'] !== '00:00:00') ? date('H:i', strtotime($con['hora_consulta'])) : date('H:i', strtotime($con['data_consulta'])); ?>
                                    </div>
                                </div>
                                <span class="badge-pill <?php echo ($con['status'] == 'Concluída') ? 'success' : 'primary'; ?>"><?php echo htmlspecialchars($con['status']); ?></span>
                            </div>
                            
                            <div class="p-4 bg-muted-light border-radius-sm mb-3">
                                <label class="label-caps-header">Relato Médico / Observações</label>
                                <p class="text-main mb-0" style="line-height: 1.6; font-size: 14.5px;">
                                    <?php echo !empty($con['descricao']) ? nl2br(htmlspecialchars($con['descricao'])) : 'Sem descrição detalhada.'; ?>
                                </p>
                            </div>

                                <?php if (!empty($con['diagnostico'])): ?>
                                <div>
                                    <label class="label-caps-header">Diagnóstico Principal</label>
                                    <span class="text-main font-weight-bold"><?php echo htmlspecialchars($con['diagnostico']); ?></span>
                                </div>
                                <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detalhes do Pet -->
    <div class="dashboard-card" style="grid-column: span 4; height: fit-content;">
        <h3 class="margin-0 mb-4">Detalhes Técnicos</h3>
        
        <div class="info-list">
            <div class="info-block mb-3 p-3 bg-muted-light border-radius-sm d-flex align-items-center gap-3">
                <i data-lucide="scale" class="icon-lucide text-primary"></i>
                <div class="flex-1">
                    <label class="label-caps-header">Peso Atual</label>
                    <span class="text-main"><?php echo number_format((float)($pet['peso'] ?? 0), 2, ',', '.'); ?> kg</span>
                </div>
            </div>
            <div class="info-block mb-3 p-3 bg-muted-light border-radius-sm d-flex align-items-center gap-3">
                <i data-lucide="calendar" class="icon-lucide text-primary"></i>
                <div class="flex-1">
                    <label class="label-caps-header">Idade</label>
                    <span class="text-main"><?php echo htmlspecialchars($pet['idade'] ?? 'N/A'); ?></span>
                </div>
            </div>
            <div class="info-block mb-3 p-3 bg-muted-light border-radius-sm d-flex align-items-center gap-3">
                <i data-lucide="hash" class="icon-lucide text-primary"></i>
                <div class="flex-1">
                    <label class="label-caps-header">Microchip</label>
                    <span class="text-main"><?php echo htmlspecialchars($pet['microchip'] ?? 'Não registrado'); ?></span>
                </div>
            </div>
            <div class="info-block p-3 bg-muted-light border-radius-sm d-flex align-items-center gap-3">
                <i data-lucide="credit-card" class="icon-lucide text-primary"></i>
                <div class="flex-1">
                    <label class="label-caps-header">Nº Carteirinha</label>
                    <span class="text-main"><?php echo htmlspecialchars($pet['numero_carteirinha'] ?? 'Individual'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-muted-light { background: rgba(var(--primary-rgb), 0.03); border: 1px solid rgba(var(--primary-rgb), 0.05); border-radius: 12px; }
.timeline-container { position: relative; padding-left: 20px; }
.timeline-container::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: var(--border); border-radius: 2px; }
.timeline-item { position: relative; padding-bottom: 40px; padding-left: 20px; }
.timeline-marker { position: absolute; left: -25px; top: 0; width: 12px; height: 12px; border-radius: 50%; border: 3px solid var(--bg-surface); background: var(--border); transition: 0.3s; }
.timeline-marker.success { background: var(--success); }
.timeline-marker.primary { background: var(--primary); }
.badge-pill { padding: 4px 12px; font-size: 11px; font-weight: 800; border-radius: 20px; text-transform: uppercase; }
.badge-pill.success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.badge-pill.primary { background: rgba(var(--primary-rgb), 0.1); color: var(--primary); }
</style>
