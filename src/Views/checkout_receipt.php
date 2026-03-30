<?php
/** @var array $invoice */
$invoice_id = $invoice['id'];
$amount = number_format((float)$invoice['amount'], 2, ',', '.');
$due_date = date('d/m/Y', strtotime($invoice['due_date']));
$paid_at = date('d/m/Y H:i', strtotime($invoice['paid_at']));
$description = $invoice['description'] ?: 'Mensalidade SaaS';
$company_name = $invoice['company_name'];
$document = $invoice['document'] ?: 'N/A';
$plan_name = $invoice['plan_name'] ?: 'Plano Personalizado';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?php echo $invoice_id; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #1a1a1a; line-height: 1.4; margin: 0; padding: 20px; background: #e0e0e0; display: flex; justify-content: center; }
        
        /* Receipt Style Container */
        .receipt-slip { 
            max-width: 400px; 
            width: 100%; 
            background: #fff; 
            padding: 30px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            border-top: 5px solid #d4af37;
        }

        /* Serrated bottom effect */
        .receipt-slip::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 100%;
            height: 10px;
            background: linear-gradient(-135deg, transparent 5px, #fff 0) 0 5px,
                        linear-gradient(135deg, transparent 5px, #fff 0) 0 5px;
            background-size: 10px 10px;
            background-repeat: repeat-x;
        }

        .header { text-align: center; border-bottom: 2px dashed #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: 800; color: #333; margin-bottom: 5px; text-transform: uppercase; }
        .receipt-num { font-size: 11px; color: #777; letter-spacing: 2px; }
        
        .section { margin-bottom: 20px; font-size: 13px; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 11px; color: #999; margin-bottom: 8px; border-bottom: 1px solid #f0f0f0; }
        
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .info-label { color: #666; }
        .info-value { font-weight: 600; text-align: right; }

        .items-area { margin: 25px 0; border-top: 2px dashed #eee; border-bottom: 2px dashed #eee; padding: 15px 0; }
        .item-desc { font-weight: bold; display: block; }
        .item-sub { font-size: 11px; color: #777; font-style: italic; }

        .total-section { text-align: center; margin-top: 25px; }
        .total-label { font-size: 12px; text-transform: uppercase; color: #666; }
        .total-amount { font-size: 28px; font-weight: 800; color: #000; margin: 5px 0; }

        .paid-badge { 
            background: #10b981; 
            color: #fff; 
            padding: 4px 12px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: bold; 
            display: inline-block;
            margin-bottom: 15px;
        }

        .footer { text-align: center; margin-top: 30px; color: #999; font-size: 10px; }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #d4af37;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 100;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt-slip { box-shadow: none; border-top: none; padding: 10px; }
            .receipt-slip::after { display: none; }
            .no-print { display: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">IMPRIMIR RECIBO</button>

    <div class="receipt-slip">
        <div class="header">
            <div class="logo">VetManager SaaS</div>
            <div class="receipt-num">COMPROVANTE #<?php echo $invoice_id; ?></div>
        </div>

        <div style="text-align: center;">
            <div class="paid-badge">PAGO COM SUCESSO</div>
        </div>

        <div class="section">
            <div class="section-title">Dados do Cliente</div>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value"><?php echo htmlspecialchars($company_name); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Doc:</span>
                <span class="info-value"><?php echo htmlspecialchars($document); ?></span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Pagamento</div>
            <div class="info-row">
                <span class="info-label">Vencimento:</span>
                <span class="info-value"><?php echo $due_date; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Pago em:</span>
                <span class="info-value"><?php echo $paid_at; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Método:</span>
                <span class="info-value">Mercado Pago</span>
            </div>
        </div>

        <div class="items-area">
            <span class="item-desc"><?php echo htmlspecialchars($plan_name); ?></span>
            <span class="item-sub"><?php echo htmlspecialchars($description); ?></span>
            <div style="text-align: right; margin-top: 5px; font-weight: bold;">R$ <?php echo $amount; ?></div>
        </div>

        <div class="total-section">
            <div class="total-label">Valor Total</div>
            <div class="total-amount">R$ <?php echo $amount; ?></div>
        </div>

        <div class="footer">
            Gerado em <?php echo date('d/m/Y H:i'); ?><br>
            Obrigado por utilizar nossos serviços!<br>
            VetManager SaaS - Gestão Veterinária
        </div>
    </div>

</body>
</html>
