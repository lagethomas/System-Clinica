<?php
/** @var array $company */
/** @var array $produtos */

// Primary color
$primaryColor = $company['theme_color'] ?? '#2563eb';
$hex = str_replace('#', '', $primaryColor);
if (strlen($hex) == 3) {
    $r = hexdec(substr($hex,0,1).substr($hex,0,1));
    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
} else {
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
}
$primaryRGB = "$r, $g, $b";
$SITE_URL = SITE_URL;
$systemLogo = !empty($company['logo']) ? $SITE_URL . '/' . ltrim($company['logo'], '/') : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company['name']); ?> | Loja Online</title>
    <meta name="description" content="Confira todos os produtos disponíveis em <?php echo htmlspecialchars($company['name']); ?>.">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="<?php echo $SITE_URL; ?>/assets/css/modules/loja.css">

    <style>
        :root {
            --primary: <?php echo $primaryColor; ?>;
            --primary-rgb: <?php echo $primaryRGB; ?>;
        }

        /* ==================
           CLUBE DE BENEFÍCIOS BANNER
        ================== */
        .clube-banner {
            background: linear-gradient(135deg,
                rgba(var(--primary-rgb), 0.18) 0%,
                rgba(var(--primary-rgb), 0.06) 40%,
                rgba(0,0,0,0.3) 100%);
            border: 1px solid rgba(var(--primary-rgb), 0.25);
            border-radius: 28px;
            padding: 50px 40px;
            margin-bottom: 60px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .clube-banner::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(var(--primary-rgb), 0.15), transparent 70%);
            pointer-events: none;
        }

        .clube-banner::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(var(--primary-rgb), 0.08), transparent 70%);
            pointer-events: none;
        }

        .clube-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(var(--primary-rgb), 0.15);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            color: var(--primary);
            padding: 6px 18px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .clube-title {
            font-size: clamp(28px, 5vw, 46px);
            font-weight: 800;
            color: #fff;
            letter-spacing: -1px;
            margin-bottom: 14px;
            line-height: 1.15;
        }

        .clube-title span {
            color: var(--primary);
        }

        .clube-subtitle {
            font-size: 17px;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.7;
        }

        .clube-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            position: relative;
            z-index: 1;
        }

        .clube-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(var(--primary-rgb), 0.15);
            border-radius: 20px;
            padding: 24px 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .clube-card:hover {
            background: rgba(var(--primary-rgb), 0.1);
            border-color: rgba(var(--primary-rgb), 0.4);
            transform: translateY(-4px);
        }

        .clube-card-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: rgba(var(--primary-rgb), 0.15);
            border: 1px solid rgba(var(--primary-rgb), 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--primary);
        }

        .clube-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .clube-card-desc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* ==================
           STORE HEADER
        ================== */
        .store-header {
            padding: 60px 0 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .store-logo-wrap {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            padding: 8px;
            background: rgba(var(--primary-rgb), 0.08);
            border: 2px solid rgba(var(--primary-rgb), 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }

        .store-logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .store-header h1 {
            font-size: clamp(26px, 5vw, 38px);
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .store-header p {
            font-size: 16px;
            color: var(--text-muted);
        }

        /* ==================
           PRODUCT CARDS
        ================== */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
            padding-bottom: 120px;
        }

        .product-card {
            background: rgba(22, 25, 30, 0.75);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border);
            border-radius: 22px;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            cursor: pointer;
            text-decoration: none;
        }

        .product-card:hover {
            transform: translateY(-8px);
            border-color: rgba(var(--primary-rgb), 0.4);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: rgba(255,255,255,0.03);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.08);
        }

        .product-image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            font-size: 12px;
        }

        .promo-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #ef4444;
            color: #fff;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
            z-index: 2;
            animation: promoPulse 2s infinite ease-in-out;
        }

        @keyframes promoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.06); }
        }

        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .product-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 16px;
            flex: 1;
        }

        .price-section {
            margin-bottom: 16px;
        }

        .price-original {
            font-size: 12px;
            color: var(--text-muted);
            text-decoration: line-through;
            display: block;
            margin-bottom: 2px;
        }

        .price-current {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
        }

        .price-current.promo {
            color: #ef4444;
        }

        .btn-ver-produto {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(var(--primary-rgb), 0.12);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            color: var(--primary);
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-ver-produto:hover {
            background: var(--primary);
            color: #000;
            border-color: var(--primary);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 100px 0;
            color: var(--text-muted);
        }

        /* Floating Cart */
        .admin-link-top {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            background: rgba(255,255,255,0.05);
            padding: 8px 16px;
            border-radius: 100px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }
        .admin-link-top:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .clube-banner { padding: 32px 20px; }
            .clube-cards-grid { grid-template-columns: 1fr 1fr; }
            .products-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .clube-cards-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Admin Link -->
    <a href="<?php echo !empty($company['slug']) ? $SITE_URL . '/' . $company['slug'] . '/login' : $SITE_URL . '/login'; ?>" class="admin-link-top">
        <i data-lucide="shield" style="width:14px;height:14px;"></i> Painel Admin
    </a>

    <div class="container" style="padding-top:30px;">

        <!-- Store Header -->
        <div class="store-header">
            <div class="store-logo-wrap">
                <?php if ($systemLogo): ?>
                    <img src="<?php echo $systemLogo; ?>" alt="Logo <?php echo htmlspecialchars($company['name']); ?>">
                <?php else: ?>
                    <i data-lucide="package" style="width:48px;height:48px;color:var(--primary);"></i>
                <?php endif; ?>
            </div>
            <h1><?php echo htmlspecialchars($company['name']); ?></h1>
            <p><?php echo htmlspecialchars(($company['city'] ?? '') . ((!empty($company['city']) && !empty($company['state'])) ? ' - ' : '') . ($company['state'] ?? '')); ?></p>
        </div>

        <!-- ========== CLUBE DE BENEFÍCIOS BANNER ========== -->
        <div class="clube-banner">
            <div class="clube-badge">
                <i data-lucide="star" style="width:12px;height:12px;"></i>
                Exclusivo para você
            </div>
            <h2 class="clube-title">Clube de <span>Benefícios</span></h2>
            <p class="clube-subtitle">
                Faça parte do nosso clube e aproveite vantagens exclusivas, descontos especiais e muito mais para você e seu pet.
            </p>

            <div class="clube-cards-grid">
                <div class="clube-card">
                    <div class="clube-card-icon">
                        <i data-lucide="percent" style="width:24px;height:24px;"></i>
                    </div>
                    <div class="clube-card-title">Descontos Exclusivos</div>
                    <div class="clube-card-desc">Acesso a preços especiais e promoções disponíveis apenas para membros.</div>
                </div>

                <div class="clube-card">
                    <div class="clube-card-icon">
                        <i data-lucide="gift" style="width:24px;height:24px;"></i>
                    </div>
                    <div class="clube-card-title">Brindes & Surpresas</div>
                    <div class="clube-card-desc">Ganhe brindes e presentes surpresa nas suas compras e consultas.</div>
                </div>

                <div class="clube-card">
                    <div class="clube-card-icon">
                        <i data-lucide="shield-check" style="width:24px;height:24px;"></i>
                    </div>
                    <div class="clube-card-title">Cobertura Veterinária</div>
                    <div class="clube-card-desc">Consultas e exames com preços diferenciados para membros do clube.</div>
                </div>

                <div class="clube-card">
                    <div class="clube-card-icon">
                        <i data-lucide="bell" style="width:24px;height:24px;"></i>
                    </div>
                    <div class="clube-card-title">Alertas de Novidades</div>
                    <div class="clube-card-desc">Seja o primeiro a saber sobre novos produtos e serviços disponíveis.</div>
                </div>

                <div class="clube-card">
                    <div class="clube-card-icon">
                        <i data-lucide="heart" style="width:24px;height:24px;"></i>
                    </div>
                    <div class="clube-card-title">Cuidado Preferencial</div>
                    <div class="clube-card-desc">Atendimento prioritário e cuidado personalizado para seus pets.</div>
                </div>

                <div class="clube-card">
                    <div class="clube-card-icon">
                        <i data-lucide="trending-up" style="width:24px;height:24px;"></i>
                    </div>
                    <div class="clube-card-title">Acúmulo de Pontos</div>
                    <div class="clube-card-desc">Cada compra gera pontos que podem ser trocados por benefícios.</div>
                </div>
            </div>
        </div>
        <!-- ========== /CLUBE DE BENEFÍCIOS ========== -->

        <!-- Products Section -->
        <?php if (empty($produtos)): ?>
            <div class="empty-state">
                <i data-lucide="shopping-bag" style="width:64px;height:64px;margin-bottom:20px;opacity:0.2;"></i>
                <p style="font-size:18px;font-weight:600;margin-bottom:8px;">Nenhum produto disponível</p>
                <p>Volte em breve para conferir nossas novidades!</p>
            </div>
        <?php else: ?>
            <div style="margin-bottom:30px;">
                <h2 style="font-size:22px;font-weight:800;color:#fff;margin-bottom:4px;">Nossos Produtos</h2>
                <p style="font-size:14px;color:var(--text-muted);"><?php echo count($produtos); ?> produto<?php echo count($produtos) !== 1 ? 's' : ''; ?> disponíve<?php echo count($produtos) !== 1 ? 'is' : 'l'; ?></p>
            </div>

            <div class="products-grid" id="products-grid">
                <?php foreach ($produtos as $p): ?>
                    <?php
                        $preco = (float)$p['preco'];
                        $precoPromo = !empty($p['preco_promocional']) ? (float)$p['preco_promocional'] : null;
                        $emPromocao = (bool)$p['em_promocao'];
                        $precoFinal = ($emPromocao && $precoPromo) ? $precoPromo : $preco;
                        $singleUrl = $SITE_URL . '/' . $company['slug'] . '/loja/' . $p['id'];
                    ?>
                    <a href="<?php echo $singleUrl; ?>" class="product-card">
                        <?php if ($emPromocao): ?>
                            <div class="promo-badge">Clube</div>
                        <?php endif; ?>

                        <div class="product-image">
                            <?php if (!empty($p['capa'])): ?>
                                <img src="<?php echo $SITE_URL . '/' . ltrim($p['capa'], '/'); ?>"
                                     alt="<?php echo htmlspecialchars($p['nome']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="product-image-placeholder">
                                    <i data-lucide="image" style="width:40px;height:40px;opacity:0.2;"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($p['nome']); ?></h3>
                            <p class="product-desc">
                                <?php echo htmlspecialchars($p['descricao'] ?: 'Explore este produto incrível disponível para você.'); ?>
                            </p>

                            <div class="price-section">
                                <?php if ($emPromocao && $precoPromo): ?>
                                    <span class="price-original"><span style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-right:4px;">Preço normal</span>R$ <?php echo number_format($preco, 2, ',', '.'); ?></span>
                                    <span class="price-current promo"><span style="font-size:10px;font-weight:700;background:rgba(239,68,68,0.15);color:#ef4444;padding:2px 6px;border-radius:4px;margin-right:6px;text-transform:uppercase;letter-spacing:0.5px;">Cliente clube</span>R$ <?php echo number_format($precoPromo, 2, ',', '.'); ?></span>
                                <?php else: ?>
                                    <span class="price-current">R$ <?php echo number_format($preco, 2, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="btn-ver-produto">
                                <i data-lucide="shopping-cart" style="width:16px;height:16px;"></i>
                                Ver Produto
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div><!-- /container -->

    <!-- Floating Cart Button -->
    <div class="cart-floating" id="cart-btn" onclick="toggleCart()">
        <i data-lucide="shopping-bag" style="width:26px;height:26px;"></i>
        <div class="cart-badge" id="cart-count">0</div>
    </div>

    <!-- Side Cart Panel -->
    <div id="cart-panel">
        <div class="cart-header">
            <h2 style="font-size:20px;font-weight:800;color:#fff;">Meu Carrinho</h2>
            <div class="m-close-inline" onclick="toggleCart()">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </div>
        </div>

        <div id="cart-items-container" class="cart-items">
            <div class="empty-state-cart">
                <i data-lucide="shopping-cart" style="width:48px;height:48px;opacity:0.2;"></i>
                <p>O seu carrinho está vazio.</p>
            </div>
        </div>

        <!-- Checkout Form -->
        <div id="checkout-form-container" class="checkout-form hidden">
            <h3 style="font-size:18px;font-weight:800;color:#fff;margin-bottom:20px;">Dados para Entrega</h3>
            <div class="form-group-digital">
                <label class="checkout-label">Seu Telefone</label>
                <input type="text" id="order_phone" class="form-control-digital" placeholder="(00) 00000-0000" onblur="checkClientByPhone(this.value)" value="<?php echo htmlspecialchars($client_data['telefone'] ?? ''); ?>">
            </div>
            <div class="form-group-digital">
                <label class="checkout-label">Seu Nome</label>
                <input type="text" id="order_name" class="form-control-digital" placeholder="Seu Nome Completo" value="<?php echo htmlspecialchars($client_data['nome'] ?? ''); ?>">
            </div>

            <!-- Delivery Method Selector -->
            <div class="form-group-digital-lg">
                <label class="checkout-label-bold">Método de Entrega</label>
                <div class="delivery-method-grid">
                    <div class="method-option active" id="method-delivery" onclick="setDeliveryMethod('delivery')">
                        <i data-lucide="truck" style="width:18px;height:18px;"></i>
                        <span>Entrega</span>
                    </div>
                    <div class="method-option" id="method-pickup" onclick="setDeliveryMethod('pickup')">
                        <i data-lucide="store" style="width:18px;height:18px;"></i>
                        <span>Retirar na Loja</span>
                    </div>
                </div>
            </div>

            <!-- Address Fields Wrapper -->
            <div id="address-fields-wrapper">
                <div class="address-grid-2-1">
                    <div>
                        <label class="checkout-label">CEP</label>
                        <input type="text" id="order_zip" class="form-control-digital" placeholder="00000-000" onblur="lookupCEP(this.value)" value="<?php echo htmlspecialchars($client_data['zip_code'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="checkout-label">Nº</label>
                        <input type="text" id="order_number" class="form-control-digital" placeholder="123" value="<?php echo htmlspecialchars($client_data['address_number'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group-digital">
                    <label class="checkout-label">Bairro</label>
                    <input type="text" id="order_neighborhood" class="form-control-digital" placeholder="Seu Bairro" value="<?php echo htmlspecialchars($client_data['neighborhood'] ?? ''); ?>">
                </div>
                <div class="form-group-digital">
                    <label class="checkout-label">Endereço Completo</label>
                    <input type="text" id="order_address" class="form-control-digital" placeholder="Rua, Av, Travessa..." value="<?php echo htmlspecialchars($client_data['street'] ?? ''); ?>">
                </div>
                <div class="address-grid-3-1">
                    <div>
                        <label class="checkout-label">Cidade</label>
                        <input type="text" id="order_city" class="form-control-digital" placeholder="Sua Cidade" value="<?php echo htmlspecialchars($client_data['city'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="checkout-label">UF</label>
                        <input type="text" id="order_state" class="form-control-digital" placeholder="UF" maxlength="2" value="<?php echo htmlspecialchars($client_data['state'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group-digital">
                    <label class="checkout-label">Complemento / Referência</label>
                    <input type="text" id="order_complement" class="form-control-digital" placeholder="Apto, Casa 2, Perto de..." value="<?php echo htmlspecialchars($client_data['address_complement'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group-digital">
                <label class="checkout-label">Observações sobre o pedido</label>
                <textarea id="order_notes" class="form-control-digital" style="height:80px;resize:none;" placeholder="Ex: Deixar na portaria, campanhia com defeito..."></textarea>
            </div>
        </div>

        <div class="cart-footer hidden" id="cart-footer">
            <div class="cart-total-row delivery-fee-row" id="delivery-fee-row">
                <span style="font-size:14px;color:var(--text-muted);">Frete</span>
                <span id="cart-delivery" style="font-size:14px;color:var(--text-muted);">+ R$ 0,00</span>
            </div>
            <div class="cart-total-row">
                <span>Total</span>
                <span id="cart-total">R$ 0,00</span>
            </div>

            <div id="footer-actions">
                <?php if ($client_data): ?>
                    <button class="add-to-cart-btn" id="btn-finalize" onclick="showCheckoutForm()" style="background:var(--primary);color:#000;">
                        FINALIZAR PEDIDO <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
                    </button>

                    <div id="checkout-actions" class="hidden" style="display:flex;flex-direction:column;gap:10px;">
                        <?php if (($company['mp_enabled'] ?? '0') == '1'): ?>
                            <!-- Main Action: Online Payment -->
                            <button class="btn-pay-online" id="btn-pay-online" onclick="submitOrder('online')">
                                <i data-lucide="credit-card" style="width:20px;height:20px;"></i> 
                                PAGAR ONLINE (PIX/CARTÃO)
                            </button>
                            
                            <!-- Secondary Action: Pay on Delivery -->
                            <button class="btn-secondary-custom" id="btn-submit-delivery" onclick="submitOrder('delivery')" style="padding:14px;border-radius:50px;width:100%;">
                                <i data-lucide="truck" style="width:16px;height:16px;"></i>
                                PAGAR NA ENTREGA
                            </button>
                        <?php else: ?>
                            <!-- Fallback: Only WhatsApp -->
                            <button class="add-to-cart-btn" id="btn-submit" onclick="submitOrder('delivery')" style="background:var(--primary);color:#000;">
                                <i data-lucide="send" style="width:18px;height:18px;"></i>
                                ENVIAR PEDIDO
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn-text-only" id="btn-back-to-cart" onclick="hideCheckoutForm()">
                            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
                            Voltar ao carrinho
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Not Logged In -->
                    <a href="<?php echo SITE_URL; ?>/<?php echo $company['slug']; ?>/login" class="add-to-cart-btn" style="background:var(--primary);color:#000;text-decoration:none;display:flex;align-items:center;justify-content:center;">
                        FAZER LOGIN PARA COMPRAR <i data-lucide="log-in" style="width:18px;height:18px;margin-left:10px;"></i>
                    </a>
                <?php endif; ?>
                
                <button class="btn-text-only" id="btn-clear-cart" onclick="clearCart()" style="margin-top:10px;">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                    Limpar carrinho
                </button>
            </div>
        </div>
    </div>

    <script>
    // System Constants
    const SITE_URL = '<?php echo $SITE_URL; ?>';
    const COMPANY_ID = '<?php echo $company['id']; ?>';
    const COMPANY_SLUG = '<?php echo $company['slug']; ?>';
    const CART_KEY = 'cart_loja_' + COMPANY_ID;
    const DELIVERY_FEE = parseFloat('<?php echo $company['taxa_entrega'] ?? 0; ?>');

    // UI Helpers
    const UI = {
        showToast(message, type = 'success') {
            console.log(`[TOAST ${type}] ${message}`);
            // Simple alert if no premium toast system is available
            alert(message);
        }
    };

    let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
    let deliveryMethod = 'delivery';

    function removeItemFromCart(key) {
        cart = cart.filter(it => it.uniqueKey !== key);
        saveCart();
        renderCart();
    }

    function clearCart() {
        if (!confirm('Limpar todo o carrinho?')) return;
        cart = [];
        saveCart();
        renderCart();
        hideCheckoutForm();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const countEl   = document.getElementById('cart-count');
        const totalEl   = document.getElementById('cart-total');
        const footer    = document.getElementById('cart-footer');
        const devFeeRow = document.getElementById('delivery-fee-row');
        const devFeeVal = document.getElementById('cart-delivery');

        const totalQty = cart.reduce((sum, it) => sum + it.quantidade, 0);
        countEl.innerText = totalQty > 0 ? totalQty : '0';

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="empty-state-cart">
                    <i data-lucide="shopping-cart" style="width:48px;height:48px;opacity:0.2;"></i>
                    <p>O seu carrinho está vazio.</p>
                </div>`;
            footer.classList.add('hidden');
            if (window.lucide) lucide.createIcons();
            return;
        }

        footer.classList.remove('hidden');
        let subtotal = 0;

        container.innerHTML = cart.map(item => {
            const itemSubtotal = item.preco * item.quantidade;
            subtotal += itemSubtotal;
            return `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-title">${item.quantidade}x ${item.nome}</div>
                        ${item.observacoes ? `<div class="cart-item-extras" style="font-style:italic;color:var(--primary);">Obs: ${item.observacoes}</div>` : ''}
                        <div class="cart-item-price">R$ ${itemSubtotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>
                    </div>
                    <button onclick="removeItemFromCart(${item.uniqueKey})" class="btn-remove-item">
                        <i data-lucide="x" style="width:14px;height:14px;"></i>
                    </button>
                </div>`;
        }).join('');

        let total = subtotal;
        if (deliveryMethod === 'delivery') {
            total += DELIVERY_FEE;
            devFeeRow.classList.remove('hidden');
            devFeeVal.innerText = '+ R$ ' + DELIVERY_FEE.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        } else {
            devFeeRow.classList.add('hidden');
        }

        totalEl.innerText = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

        if (window.lucide) lucide.createIcons();
    }

    function saveCart() {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    }

    function toggleCart(forceOpen = null) {
        const panel = document.getElementById('cart-panel');
        if (forceOpen === true) panel.classList.add('active');
        else if (forceOpen === false) panel.classList.remove('active');
        else panel.classList.toggle('active');
    }

    function showCheckoutForm() {
        if (cart.length === 0) return;
        document.getElementById('cart-items-container').classList.add('hidden');
        document.getElementById('checkout-form-container').classList.remove('hidden');
        document.getElementById('btn-finalize').classList.add('hidden');
        document.getElementById('btn-clear-cart').classList.add('hidden');
        document.getElementById('checkout-actions').classList.remove('hidden');
    }

    function hideCheckoutForm() {
        document.getElementById('cart-items-container').classList.remove('hidden');
        document.getElementById('checkout-form-container').classList.add('hidden');
        document.getElementById('btn-finalize').classList.remove('hidden');
        document.getElementById('btn-clear-cart').classList.remove('hidden');
        document.getElementById('checkout-actions').classList.add('hidden');
    }

    function setDeliveryMethod(method) {
        deliveryMethod = method;
        document.querySelectorAll('.method-option').forEach(el => el.classList.remove('active'));
        document.getElementById('method-' + method).classList.add('active');
        
        const wrapper = document.getElementById('address-fields-wrapper');
        if (method === 'pickup') wrapper.classList.add('hidden');
        else wrapper.classList.remove('hidden');
        
        renderCart();
    }

    async function checkClientByPhone(phone) {
        if (!phone || phone.length < 10) return;
        try {
            const res = await fetch(`${SITE_URL}/api/public-search-client?phone=${encodeURIComponent(phone)}&company_id=${COMPANY_ID}`);
            const result = await res.json();
            if (result.success && result.data) {
                const c = result.data;
                document.getElementById('order_name').value = c.name || '';
                document.getElementById('order_zip').value = c.zip_code || '';
                document.getElementById('order_neighborhood').value = c.neighborhood || '';
                document.getElementById('order_address').value = c.address || '';
                document.getElementById('order_city').value = c.city || '';
                document.getElementById('order_state').value = c.state || '';
                document.getElementById('order_number').value = c.number || '';
                document.getElementById('order_complement').value = c.complement || '';
            }
        } catch (e) { console.error(e); }
    }

    async function lookupCEP(cep) {
        const cleanCep = cep.replace(/\D/g, '');
        if (cleanCep.length !== 8) return;
        try {
            const res = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`);
            const data = await res.json();
            if (!data.erro) {
                document.getElementById('order_address').value = data.logradouro || '';
                document.getElementById('order_neighborhood').value = data.bairro || '';
                document.getElementById('order_city').value = data.localidade || '';
                document.getElementById('order_state').value = data.uf || '';
                document.getElementById('order_number').focus();
            }
        } catch (e) { console.error(e); }
    }

    async function submitOrder(mode = 'delivery') {
        const phone = document.getElementById('order_phone').value;
        const name = document.getElementById('order_name').value;
        const address = document.getElementById('order_address').value;

        if (!phone || !name) {
            UI.showToast('Por favor, preencha nome e telefone.', 'warning');
            return;
        }

        if (deliveryMethod === 'delivery' && !address) {
            UI.showToast('Por favor, preencha o seu endereço para entrega.', 'warning');
            return;
        }

        const orderData = {
            company_id: COMPANY_ID,
            cliente_nome: name,
            cliente_telefone: phone,
            zip_code: document.getElementById('order_zip').value,
            neighborhood: document.getElementById('order_neighborhood').value,
            address: address,
            city: document.getElementById('order_city').value,
            state: document.getElementById('order_state').value,
            number: document.getElementById('order_number').value,
            complement: document.getElementById('order_complement').value,
            tipo: deliveryMethod, 
            payment_mode: mode,
            observacoes: document.getElementById('order_notes').value,
            itens: cart.map(it => ({
                id: it.id,
                nome: it.nome,
                preco: it.preco,
                quantidade: it.quantidade
            }))
        };

        // If simple delivery (WhatsApp), we can still create the record then send to WA
        try {
            const res = await fetch(`${SITE_URL}/api/pedidos/public-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });
            const result = await res.json();
            
            if (result.success) {
                localStorage.removeItem(CART_KEY);
                
                if (mode === 'online' && result.payment_url) {
                    window.location.href = result.payment_url;
                    return;
                }

                UI.showToast('Pedido realizado com sucesso!');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                UI.showToast(result.message || 'Erro ao processar pedido', 'error');
            }
        } catch (e) {
            console.error(e);
            UI.showToast('Erro de conexão ao enviar pedido.', 'error');
        }
    }

    function sendWhatsapp(data, orderId) {
        let msg = `🛒 *Novo Pedido - #${orderId}*\n\n`;
        msg += `👤 *Cliente:* ${data.cliente_nome}\n`;
        msg += `📞 *Telefone:* ${data.cliente_telefone}\n`;
        msg += `📍 *Tipo:* ${data.tipo === 'delivery' ? 'Entrega' : 'Retirar na Loja'}\n`;
        
        if (data.tipo === 'delivery') {
            msg += `🏠 *Endereço:* ${data.address}, ${data.number}\n`;
            msg += `🏙️ *Bairro:* ${data.neighborhood} - ${data.city}/${data.state}\n`;
            if (data.complement) msg += `🏢 *Compl:* ${data.complement}\n`;
        }
        
        msg += `\n📦 *Itens:*\n`;
        let subtotal = 0;
        cart.forEach(it => {
            const itemTotal = it.preco * it.quantidade;
            subtotal += itemTotal;
            msg += `• ${it.quantidade}x ${it.nome} - R$ ${itemTotal.toLocaleString('pt-BR', {minimumFractionDigits:2})}\n`;
        });
        
        let total = subtotal;
        if (data.tipo === 'delivery') {
            msg += `🚚 *Frete:* R$ ${DELIVERY_FEE.toLocaleString('pt-BR', {minimumFractionDigits:2})}\n`;
            total += DELIVERY_FEE;
        }
        
        msg += `\n*💰 TOTAL: R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits:2})}*\n`;
        msg += `💳 *Pagamento:* ${data.payment_mode === 'online' ? 'Online (Pago)' : 'Pagar na Entrega'}\n`;
        if (data.observacoes) msg += `\n📝 *Obs:* ${data.observacoes}`;

        const waPhone = '<?php echo preg_replace('/\D/','',$company['phone']??''); ?>';
        const fullWa = waPhone.length <= 11 ? '55' + waPhone : waPhone;
        const url = `https://wa.me/${fullWa}?text=${encodeURIComponent(msg)}`;
        window.open(url, '_blank');
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderCart();
        if (window.lucide) lucide.createIcons();
    });
    </script>

</body>
</html>
