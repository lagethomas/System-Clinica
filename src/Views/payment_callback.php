<?php
/** @var string $status */
/** @var string $invoice_id */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento da Assinatura - VetManager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        :root {
            --primary: #d4af37;
            --primary-rgb: 212, 175, 55;
            --success: #10b981;
            --danger: #ef4444;
            --pending: #f59e0b;
            --bg-body: #0a0a0b;
            --card-bg: #141417;
            --text-main: #ffffff;
            --text-muted: #8e8e93;
            --border: rgba(255,255,255,0.08);
        }

        body { 
            background: var(--bg-body); 
            color: var(--text-main);
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            overflow: hidden;
        }

        /* Background Effects */
        .bg-glow {
            position: fixed;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(var(--primary-rgb), 0.15) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            z-index: -1;
            filter: blur(40px);
        }

        .result-card { 
            max-width: 480px; 
            width: 100%; 
            border-radius: 32px; 
            border: 1px solid var(--border);
            padding: 50px 40px; 
            text-align: center; 
            background: var(--card-bg); 
            box-shadow: 0 25px 80px rgba(0,0,0,0.5);
            backdrop-filter: blur(20px);
            animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Icon Container */
        .status-icon-box {
            width: 100px;
            height: 100px;
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            position: relative;
        }
        
        .status-icon-box::after {
            content: '';
            position: absolute;
            inset: -10px;
            border-radius: 40px;
            opacity: 0.1;
            background: currentColor;
        }

        .status-icon { width: 48px; height: 48px; stroke-width: 2.5px; }

        /* Icon Colors */
        .color-success { color: var(--success); background: rgba(16, 185, 129, 0.1); }
        .color-pending { color: var(--pending); background: rgba(244, 158, 11, 0.1); }
        .color-error { color: var(--danger); background: rgba(239, 68, 68, 0.1); }

        h2 { font-weight: 800; letter-spacing: -0.5px; margin-bottom: 12px; }
        .subtitle { color: var(--text-muted); font-size: 14px; line-height: 1.6; padding: 0 10px; }

        .info-pill {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-top: 20px;
        }

        .divider { height: 1px; background: var(--border); margin: 40px 0; }

        /* Buttons */
        .btn-premium {
            background: var(--primary);
            color: #000;
            font-weight: 800;
            padding: 14px 24px;
            border-radius: 16px;
            text-decoration: none;
            display: block;
            width: 100%;
            transition: all 0.3s;
            border: none;
            font-size: 15px;
        }
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.4);
            color: #000;
        }

        .btn-outline-custom {
            border: 1px solid var(--border);
            color: var(--text-main);
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 16px;
            text-decoration: none;
            display: block;
            width: 100%;
            font-size: 14px;
            margin-top: 15px;
            transition: 0.3s;
        }
        .btn-outline-custom:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-main);
            border-color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="bg-glow" style="top: -100px; right: -100px;"></div>
    <div class="bg-glow" style="bottom: -100px; left: -100px;"></div>

    <div class="result-card">
        <?php if ($status === 'success'): ?>
            <div class="status-icon-box color-success">
                <i data-lucide="check-circle" class="status-icon"></i>
            </div>
            <h2>Pagamento Aprovado!</h2>
            <p class="subtitle">Sua transação foi processada com sucesso. O acesso ao sistema está renovado e sua conta atualizada.</p>
            <div class="info-pill">
                <i data-lucide="hash" style="width: 14px;"></i> Fatura #<?php echo htmlspecialchars((string)$invoice_id); ?>
            </div>

        <?php elseif ($status === 'pending'): ?>
            <div class="status-icon-box color-pending">
                <i data-lucide="clock" class="status-icon"></i>
            </div>
            <h2>Pagamento em Análise</h2>
            <p class="subtitle">Aguardando a confirmação do Mercado Pago. Sua assinatura será renovada assim que o pagamento for aceito.</p>
            <div class="info-pill">
                <i data-lucide="hash" style="width: 14px;"></i> Identificador: <?php echo htmlspecialchars((string)$invoice_id); ?>
            </div>

        <?php else: ?>
            <div class="status-icon-box color-error">
                <i data-lucide="x-circle" class="status-icon"></i>
            </div>
            <h2>Erro no Pagamento</h2>
            <p class="subtitle">Não conseguimos validar o pagamento para a fatura #<?php echo htmlspecialchars((string)$invoice_id); ?>. Por favor, tente novamente.</p>
            
            <a href="/checkout/<?php echo htmlspecialchars((string)$invoice_id); ?>" class="btn-premium mt-4">Tentar Novamente</a>
        <?php endif; ?>

        <div class="divider"></div>

        <a href="/dashboard" class="btn-premium">Acessar Painel Principal</a>
        <a href="/app/subscriptions" class="btn-outline-custom">Ver Histórico de Faturas</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
