<?php
/** @var string $user_name */
/** @var array $summary_stats */
/** @var array $admin_stats */
/** @var array $vet_stats */
?>

<div class="welcome-container mb-5">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="welcome-title mb-2">Bem-vindo de volta, <span class="text-primary"><?php echo htmlspecialchars($user_name); ?></span>!</h1>
            <p class="welcome-sub text-muted">
                <?php echo Auth::isAdmin() ? 'Visão global e gerencial do ecossistema SaaS.' : 'Aqui está o resumo operacional da sua clínica para hoje.'; ?>
            </p>
        </div>
        <div class="col-md-4 text-md-right">
            <div class="date-badge">
                <i data-lucide="calendar" class="icon-lucide icon-sm mr-2"></i>
                <?php echo date('d \d\e F, Y'); ?>
            </div>
        </div>
    </div>
</div>

<!-- STATS ROW (DYNAMISM) -->
<div class="stats-grid mb-5">
    <?php foreach ($summary_stats as $stat): ?>
        <a href="<?php echo SITE_URL . $stat['link']; ?>" class="stat-card-premium">
            <div class="stat-icon-box <?php echo $stat['color']; ?>">
                <i data-lucide="<?php echo $stat['icon']; ?>" class="icon-lucide"></i>
            </div>
            <div class="stat-info-premium">
                <span class="stat-label-premium"><?php echo $stat['label']; ?></span>
                <h3 class="stat-value-premium"><?php echo $stat['value']; ?></h3>
            </div>
            <div class="stat-action-hint">
                <i data-lucide="arrow-up-right" class="icon-lucide icon-sm"></i>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div class="dashboard-grid mb-5">
    <!-- ATALHOS RÁPIDOS (SINGLE INSTANCE) -->
    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 fw-800" style="font-size: 17px; color: var(--text-main);">Atalhos Rápidos</h3>
            <span class="badge badge-primary-lite">Ações</span>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
            <?php if (Auth::isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>/admin/companies" class="card-link-premium p-3">
                    <div class="icon-box-lite mb-2"><i data-lucide="building" class="icon-sm"></i></div>
                    <span class="small fw-700">Empresas</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/plans" class="card-link-premium p-3">
                    <div class="icon-box-lite mb-2"><i data-lucide="package" class="icon-sm"></i></div>
                    <span class="small fw-700">Planos</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/integrations" class="card-link-premium p-3">
                    <div class="icon-box-lite mb-2"><i data-lucide="settings" class="icon-sm"></i></div>
                    <span class="small fw-700">Integrações</span>
                </a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/app/consultas" class="card-link-premium p-3">
                    <div class="icon-box-lite mb-2"><i data-lucide="calendar" class="icon-sm"></i></div>
                    <span class="small fw-700">Agenda</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/app/pets" class="card-link-premium p-3">
                    <div class="icon-box-lite mb-2"><i data-lucide="dog" class="icon-sm"></i></div>
                    <span class="small fw-700">Pets</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/app/tutores" class="card-link-premium p-3">
                    <div class="icon-box-lite mb-2"><i data-lucide="users" class="icon-sm"></i></div>
                    <span class="small fw-700">Tutores</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- CONTEXT CARD -->
    <?php if (Auth::isAdmin()): ?>
        <div class="dashboard-card card-admin-special">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="icon-box bg-primary-glass">
                    <i data-lucide="shield-check" class="icon-lucide"></i>
                </div>
                <div>
                    <h3 class="m-0 fw-800" style="font-size: 17px; color: var(--text-main);">Gestão SaaS</h3>
                    <p class="small text-muted mb-0">Monitoramento Multi-Tenant</p>
                </div>
            </div>
            <div class="finance-mini-report">
                <div class="d-flex justify-content-between py-2 border-bottom-muted">
                    <span class="small text-muted">Receita Pendente (Mês)</span>
                    <span class="small fw-700 text-warning">R$ <?php echo number_format((float)($admin_stats['pending_revenue'] ?? 0), 2, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="small text-muted">Total de Contratos</span>
                    <span class="small fw-700"><?php echo $admin_stats['total_companies'] ?? 0; ?></span>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- CLINIC INFO HEADER -->
        <div class="dashboard-card shadow-sm border-left-primary-thick">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="company-logo-dashboard">
                    <i data-lucide="building-2" class="icon-lucide text-primary" style="width: 32px; height: 32px;"></i>
                </div>
                <div class="flex-1">
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="m-0 fw-850 font-20 text-main-color"><?php echo htmlspecialchars($vet_stats['company']['name'] ?? 'Minha Clínica'); ?></h2>
                        <span class="badge badge-success-lite px-2 py-1 font-8 fw-800 rounded-pill">ATIVO</span>
                    </div>
                    <div class="d-flex flex-wrap gap-4 mt-1">
                        <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 11px;">
                            <i data-lucide="hash" class="icon-lucide icon-xs text-primary"></i>
                            <span class="fw-700"><?php echo ($vet_stats['company']['cnpj'] ?? ($vet_stats['company']['document'] ?? '---')) ?: '---'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="ml-auto">
                    <a href="<?php echo SITE_URL; ?>/app/company-settings" class="btn-primary-glass px-3 py-2 rounded-10 fw-800 d-flex align-items-center gap-2" style="font-size: 12px;">
                        Perfil <i data-lucide="arrow-right" class="icon-lucide icon-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- BOTTOM GRID -->
<div class="dashboard-grid">
    <?php if (!Auth::isAdmin()): ?>
        <!-- VET ONLY -->
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom-muted">
                <h3 class="m-0 fw-800" style="font-size: 16px; color: var(--text-main);">Atendimentos Recentes</h3>
                <a href="<?php echo SITE_URL; ?>/app/consultas" class="small fw-700 text-primary">Ver Tudo</a>
            </div>
            <div class="list-container">
                <?php if (empty($vet_stats['recent_appointments'])): ?>
                    <div class="text-center p-4 text-muted small">Nenhum atendimento registrado.</div>
                <?php else: ?>
                    <?php foreach ($vet_stats['recent_appointments'] as $app): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom-muted">
                            <div>
                                <div class="fw-700 small" style="color: var(--text-main);"><?php echo htmlspecialchars($app['pet_nome']); ?></div>
                                <div class="text-muted small" style="font-size: 11px;"><?php echo htmlspecialchars($app['motivo']); ?></div>
                            </div>
                            <span class="badge badge-outline-primary" style="font-size: 10px;"><?php echo date('d/m', strtotime($app['data_consulta'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom-muted">
                <h3 class="m-0 fw-800" style="font-size: 16px; color: var(--text-main);">Distribuição por Espécie</h3>
            </div>
            <div class="list-container">
                <?php if (empty($vet_stats['species_distribution'])): ?>
                    <div class="text-center p-4 text-muted small">Nenhum pet cadastrado.</div>
                <?php else: ?>
                    <?php foreach ($vet_stats['species_distribution'] as $spec): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-700"><?php echo $spec['especie']; ?></span>
                                <span class="text-muted"><?php echo $spec['total']; ?></span>
                            </div>
                            <div class="progress-premium">
                                <div class="progress-bar-premium" style="width: <?php echo min(100, ($spec['total'] / ($vet_stats['total_pets'] ?: 1)) * 100); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- ADMIN ONLY -->
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="m-0 fw-800" style="font-size: 17px; color: var(--text-main);">Empresas Recém Cadastradas</h3>
                <a href="<?php echo SITE_URL; ?>/admin/companies" class="small fw-700 text-primary">Ver Todas</a>
            </div>
            <div class="table-responsive">
                <table class="table-borderless w-100" style="font-size: 14px;">
                    <tbody>
                        <?php if (empty($admin_stats['recent_companies'])): ?>
                            <tr><td class="text-center p-4 text-muted small">Nenhuma empresa cadastrada recentemente.</td></tr>
                        <?php else: ?>
                            <?php foreach ($admin_stats['recent_companies'] as $comp): ?>
                                <tr class="search-result-row">
                                    <td class="py-3">
                                        <div class="fw-700" style="color: var(--text-main);"><?php echo htmlspecialchars($comp['name']); ?></div>
                                        <div class="text-muted small">Slug: <?php echo htmlspecialchars($comp['slug']); ?></div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge badge-primary-lite" style="font-size: 10px;">
                                            <?php echo date('d/m/Y', strtotime($comp['created_at'])); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <a href="<?php echo SITE_URL; ?>/admin/companies/details?id=<?php echo $comp['id']; ?>" class="btn-secondary btn-sm px-3" style="font-size: 11px; border-radius: 8px;">Detalhes</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card">
            <h3 class="mb-4 fw-800" style="font-size: 17px; color: var(--text-main);">Planos & Faturamento</h3>
            <div class="p-4 rounded-4 text-center bg-primary-ultra-lite border-dashed" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                <i data-lucide="shield-check" class="icon-lucide mb-3 text-primary" style="width: 42px; height: 42px; margin: 0 auto;"></i>
                <h5 class="fw-800" style="color: var(--text-main);">Gestão de Cobrança</h5>
                <p class="text-muted small px-3">Monitore a saúde financeira do SaaS e gerencie inadimplências.</p>
                <div class="mt-4">
                    <a href="<?php echo SITE_URL; ?>/admin/subscriptions" class="btn-premium py-3 w-100">Gerenciar Assinaturas</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* .stat-card styles moved to app-premium.css */
.border-top-muted { border-top: 1px solid var(--border); }
.border-bottom-muted { border-bottom: 1px solid var(--border); }
.bg-primary-ultra-lite { background: rgba(var(--primary-rgb), 0.05); }
.border-dashed { border: 1px dashed var(--border); }

.card-link-premium {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-main);
    transition: var(--transition-smooth);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.card-link-premium:hover {
    transform: translateY(-3px);
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.02);
}
.icon-box-lite {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
}
.progress-premium {
    height: 6px;
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    overflow: hidden;
}
.progress-bar-premium {
    height: 100%;
    background: var(--primary);
    border-radius: 10px;
}
.search-result-row { border-bottom: 1px solid var(--border); transition: 0.2s; }
.search-result-row:hover { background: rgba(255,255,255,0.02); }
.search-result-row:last-child { border-bottom: none; }

.btn-premium {
    background: var(--primary);
    color: #000;
    font-weight: 800;
    padding: 12px 20px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-block;
    border: none;
    transition: 0.3s;
}
.btn-premium:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3); color: #000; }
</style>
