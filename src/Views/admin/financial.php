<?php
/** @var array $monthly_revenue */
/** @var array $top_companies */
/** @var array $recent_sales */
/** @var float $total_paid */
/** @var float $total_pending */
?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Financeiro do SaaS</h2>
        <p style="color: var(--text-muted);">Acompanhe a saúde financeira, faturamento e inadimplência global.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo SITE_URL; ?>/admin/subscriptions" class="btn-primary">
            <i data-lucide="receipt" class="icon-lucide"></i> Monitorar Faturas
        </a>
    </div>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <!-- Receita Total -->
    <div class="card stat-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(var(--black-rgb), 0.5) 100%);">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
            <i data-lucide="trending-up" class="icon-lucide"></i>
        </div>
        <div class="stat-info">
            <h3>Receita Total (Paga)</h3>
            <div class="value" style="color: #10b981;">R$ <?php echo number_format($total_paid, 2, ',', '.'); ?></div>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Soma de faturas liquidadas no sistema.</p>
        </div>
    </div>

    <!-- Pendente Global -->
    <div class="card stat-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(var(--black-rgb), 0.5) 100%);">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
            <i data-lucide="clock" class="icon-lucide"></i>
        </div>
        <div class="stat-info">
            <h3>Pendente de Recebimento</h3>
            <div class="value" style="color: #f59e0b;">R$ <?php echo number_format($total_pending, 2, ',', '.'); ?></div>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Faturas em aberto aguardando pagamento.</p>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 40px;">
    <!-- Gráfico de Faturamento -->
    <div class="card p-4" style="background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border);">
        <h3 style="font-size: 18px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="bar-chart-3" class="icon-lucide"></i> Evolução de Faturamento Mensal
        </h3>
        <div style="height: 350px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Maiores Pagadores -->
    <div class="card p-4" style="background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border);">
        <h3 style="font-size: 16px; margin-bottom: 20px;">Top 10 Empresas (Receita)</h3>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($top_companies as $idx => $comp): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--text-muted);">#<?php echo $idx + 1; ?></span>
                        <div>
                            <div style="font-weight: 700; font-size: 14px; color: var(--text-main);"><?php echo htmlspecialchars($comp['name']); ?></div>
                            <div style="font-size: 11px; color: var(--text-muted);"><?php echo $comp['invoice_count']; ?> faturas pagas</div>
                        </div>
                    </div>
                    <div style="font-weight: 800; color: var(--primary); font-size: 14px;">R$ <?php echo number_format((float)$comp['total_paid'], 2, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Tabela de Vendas Recentes -->
<div class="card p-4" style="background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border);">
    <h3 style="font-size: 18px; margin-bottom: 25px;">Últimos Recebimentos de Assinatura</h3>
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Valor</th>
                    <th>Data de Pagamento</th>
                    <th>Tipo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_sales as $sale): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($sale['company_name']); ?></div>
                            <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($sale['description']); ?></div>
                        </td>
                        <td style="font-weight: 800; color: #10b981;">R$ <?php echo number_format((float)$sale['amount'], 2, ',', '.'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($sale['paid_at'])); ?></td>
                        <td><span class="badge" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);"><?php echo strtoupper($sale['type'] ?: 'mensal'); ?></span></td>
                        <td><span class="badge status-active">RECEBIDO</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Labels e Dados do PHP
    const labels = <?php echo json_encode(array_column($monthly_revenue, 'month')); ?>;
    const data = <?php echo json_encode(array_column($monthly_revenue, 'total')); ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Receita Líquida (R$)',
                data: data,
                borderColor: '#e6c152',
                backgroundColor: 'rgba(230, 193, 82, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#e6c152',
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#94a3b8', font: { family: 'Outfit', size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { family: 'Outfit', size: 10 } }
                }
            }
        }
    });
});
</script>
