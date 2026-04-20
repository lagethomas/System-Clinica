<div class="page-header">
    <div class="page-header-info">
        <h2>Olá, <?php echo !empty($tutor['nome']) ? explode(' ', $tutor['nome'])[0] : 'Tutor'; ?>! 👋</h2>
        <p>Bem-vindo à sua área exclusiva na <?php echo htmlspecialchars($system_name ?? 'nossa clínica'); ?>.</p>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Meus Dados -->
    <div class="dashboard-card">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="icon-circle-box">
                <i data-lucide="user" class="icon-lucide"></i>
            </div>
            <h3 class="margin-0">Meus Dados</h3>
        </div>
        <div class="info-list">
            <div class="mb-3">
                <label class="label-caps-header">E-mail</label>
                <div class="text-main"><?php echo htmlspecialchars($tutor['email'] ?? 'Não informado'); ?></div>
            </div>
            <div class="mb-3">
                <label class="label-caps-header">Telefone</label>
                <div class="text-main"><?php echo htmlspecialchars($tutor['telefone'] ?? 'Não informado'); ?></div>
            </div>
            <div class="mb-3">
                <label class="label-caps-header">CPF</label>
                <div class="text-main"><?php echo htmlspecialchars($tutor['cpf'] ?? 'Não informado'); ?></div>
            </div>
        </div>
        <a href="<?php echo SITE_URL; ?>/profile" class="btn-primary-glass mt-auto">
            <i data-lucide="edit-3" class="icon-lucide icon-sm"></i> ATUALIZAR PERFIL
        </a>
    </div>

    <!-- Próximos Agendamentos -->
    <div class="dashboard-card">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="icon-circle-box" style="background: rgba(var(--primary-rgb), 0.1);">
                <i data-lucide="calendar" class="icon-lucide"></i>
            </div>
            <h3 class="margin-0">Próximos Agendamentos</h3>
        </div>
        
        <?php if (empty($next_appointments)): ?>
            <div class="text-center py-4">
                <p class="text-muted small">Você não possui agendamentos futuros.</p>
                <i data-lucide="calendar-off" class="icon-lucide text-muted mt-2" style="opacity: 0.3; width: 40px; height: 40px;"></i>
            </div>
        <?php else: ?>
            <div class="appointment-list">
                <?php foreach ($next_appointments as $app): ?>
                    <div class="info-block mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?php echo htmlspecialchars($app['pet_nome']); ?></strong>
                                <div class="text-muted small"><?php echo htmlspecialchars($app['servico'] ?? 'Consulta'); ?></div>
                            </div>
                            <div class="text-right">
                                <div class="text-primary font-weight-bold"><?php echo date('d/m', strtotime($app['data_consulta'])); ?></div>
                                <div class="text-muted small"><?php echo (!empty($app['hora_consulta']) && $app['hora_consulta'] !== '00:00:00') ? date('H:i', strtotime($app['hora_consulta'])) : date('H:i', strtotime($app['data_consulta'])); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Meus Documentos -->
    <div class="dashboard-card">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="icon-circle-box" style="background: rgba(var(--primary-rgb), 0.1);">
                <i data-lucide="file-text" class="icon-lucide"></i>
            </div>
            <h3 class="margin-0">Meus Documentos</h3>
        </div>
        
        <div class="info-list">
            <?php if (!empty($tutor['contrato_url'])): ?>
                <div class="info-block d-flex align-items-center justify-content-between p-3 bg-muted-light rounded-12 shadow-sm border" style="background: rgba(var(--primary-rgb), 0.02);">
                    <div class="d-flex align-items-center gap-3">
                        <i data-lucide="file-check" class="text-primary" style="width: 24px; height: 24px;"></i>
                        <div>
                            <span class="text-main d-block font-weight-bold">Contrato de Serviço</span>
                            <span class="text-muted small">Arquivo PDF Disponível</span>
                        </div>
                    </div>
                    <a href="<?php echo SITE_URL . $tutor['contrato_url']; ?>" target="_blank" class="btn-primary" style="padding: 8px 15px; font-size: 11px;">
                        <i data-lucide="download" class="icon-xs mr-1"></i> BAIXAR
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted small">Nenhum documento disponível para download.</p>
                    <i data-lucide="file-question" class="icon-lucide text-muted opacity-2" style="width: 32px; height: 32px;"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($pending_orders)): ?>
<div class="dashboard-card mt-4" style="border-left:4px solid #f59e0b;background:rgba(245,158,11,.05);">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="icon-circle-box" style="background:rgba(245,158,11,.15);">
            <i data-lucide="credit-card" class="icon-lucide" style="color:#f59e0b;"></i>
        </div>
        <div>
            <h3 class="margin-0">Pagamentos Pendentes</h3>
            <p class="text-muted small mb-0">Complete o pagamento antes que seu pedido seja cancelado.</p>
        </div>
    </div>
    <?php foreach ($pending_orders as $po):
        $expires   = strtotime($po['created_at']) + (4 * 3600);
        $remaining = $expires - time();
        $timeStr   = $remaining > 0 ? gmdate('H\h i\m', $remaining) : 'Expirando...';
    ?>
    <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-12" style="border:1px solid rgba(245,158,11,.3);background:rgba(245,158,11,.03);">
        <div>
            <div class="fw-700">Pedido #<?php echo $po['id']; ?> — R$ <?php echo number_format((float)$po['total'], 2, ',', '.'); ?></div>
            <div class="small" style="color:#f59e0b;font-weight:700;">⏳ Expira em: <?php echo $timeStr; ?></div>
        </div>
        <a href="<?php echo SITE_URL . '/' . ($_SESSION['company_slug'] ?? '') . '/clube-pet'; ?>" class="btn-primary" style="background:#f59e0b;border-color:#f59e0b;color:#000;white-space:nowrap;font-size:12px;">
            Pagar Agora
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<h3 class="mt-5 mb-4 d-flex align-items-center gap-2">
    <i data-lucide="dog" class="icon-lucide text-primary"></i> Meus Pets
</h3>

<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
    <?php foreach ($pets as $pet): ?>
        <a href="<?php echo SITE_URL; ?>/app/tutor/pet/<?php echo $pet['id']; ?>" class="item-grid-card">
            <div class="d-flex align-items-center gap-3">
                <div class="table-thumb" style="width: 60px; height: 60px; border-radius: 15px;">
                    <?php if (!empty($pet['foto_url'])): ?>
                        <img src="<?php echo SITE_URL . $pet['foto_url']; ?>" alt="<?php echo $pet['nome']; ?>">
                    <?php else: ?>
                        <i data-lucide="dog" class="icon-lucide" style="opacity: 0.2;"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <h4 class="margin-0 text-main"><?php echo htmlspecialchars($pet['nome']); ?></h4>
                    <span class="text-primary small font-weight-bold text-uppercase tracking-1">
                        <?php echo htmlspecialchars($pet['especie'] ?? 'Pet'); ?> • <?php echo htmlspecialchars($pet['raca'] ?? 'SDR'); ?>
                    </span>
                </div>
                <i data-lucide="chevron-right" class="icon-lucide text-muted"></i>
            </div>
        </a>
    <?php endforeach; ?>
    
    <?php if (empty($pets)): ?>
        <div class="dashboard-card text-center py-5" style="grid-column: 1 / -1;">
            <p class="text-muted">Nenhum pet cadastrado no seu perfil.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.info-list label { margin-bottom: 2px; }
.text-main { font-weight: 500; font-size: 15px; color: var(--text-main); }
.appointment-list .info-block { border-left: 4px solid var(--primary); }
</style>
