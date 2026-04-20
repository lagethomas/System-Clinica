<?php
declare(strict_types=1);

/** @var array $company */
/** @var array $produto */

// Primary color
$primaryColor = $company['theme_color'] ?? '#2563eb';
function hexToRgbSingle(string $hex): string {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return "$r, $g, $b";
}
$primaryRGB = hexToRgbSingle($primaryColor);
$SITE_URL = SITE_URL;
$systemLogo = !empty($company['logo']) ? $SITE_URL . '/' . ltrim($company['logo'], '/') : null;
$lojaUrl = $SITE_URL . '/' . $company['slug'] . '/clube-pet';

// Price calculation
$preco = (float)$produto['preco'];
$precoPromo = !empty($produto['preco_promocional']) ? (float)$produto['preco_promocional'] : null;
$emPromocao = (bool)$produto['em_promocao'];
$precoFinal = ($emPromocao && $precoPromo) ? $precoPromo : $preco;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produto['nome']); ?> | <?php echo htmlspecialchars($company['name']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(mb_strimwidth($produto['descricao'] ?? '', 0, 160, '...')); ?>">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="<?php echo $SITE_URL; ?>/assets/css/modules/clube-pet.css">

    <style>
        :root {
            --primary: <?php echo $primaryColor; ?>;
            --primary-rgb: <?php echo $primaryRGB; ?>;
        }

        /* Single Product Layout */
        .single-page {
            min-height: 100vh;
            padding-bottom: 120px;
        }

        .single-topbar {
            position: sticky;
            top: 0;
            z-index: 500;
            background: rgba(10, 12, 16, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .single-topbar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .topbar-logo-img {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: contain;
            border: 1px solid rgba(var(--primary-rgb), 0.4);
            background: rgba(var(--primary-rgb), 0.08);
            padding: 4px;
        }

        .topbar-company-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
        }

        .btn-back-loja {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-back-loja:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .single-content {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: start;
        }

        /* Product Image Side */
        .single-image-wrap {
            position: sticky;
            top: 90px;
        }

        .single-image-container {
            background: rgba(22, 25, 30, 0.75);
            border: 1px solid var(--border);
            border-radius: 28px;
            overflow: hidden;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .single-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .single-image-container:hover img {
            transform: scale(1.05);
        }

        .single-image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .promo-badge-single {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #ef4444;
            color: #fff;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 20px rgba(239, 68, 68, 0.4);
            animation: promoPulse 2s infinite ease-in-out;
        }

        /* Product Info Side */
        .single-info {
            padding-top: 10px;
        }

        .single-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            padding: 5px 14px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .single-title {
            font-size: clamp(26px, 5vw, 40px);
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            letter-spacing: -0.5px;
            margin-bottom: 20px;
        }

        .single-description {
            font-size: 16px;
            color: var(--text-muted);
            line-height: 1.75;
            margin-bottom: 30px;
            white-space: pre-line;
        }

        .single-price-box {
            background: rgba(22, 25, 30, 0.75);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 30px;
        }

        .single-price-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .single-price-original {
            font-size: 15px;
            text-decoration: line-through;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .single-price-current {
            font-size: 42px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .single-price-current.is-promo {
            color: #ef4444;
        }

        .single-price-discount {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 8px;
        }

        /* Quantity + Add to Cart */
        .single-actions {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .single-qty-row {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .single-qty-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
            white-space: nowrap;
        }

        .single-subtotal {
            flex: 1;
            text-align: right;
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }

        .btn-add-to-cart-single {
            background: var(--primary);
            color: #000;
            border: none;
            padding: 18px 28px;
            border-radius: 100px;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(var(--primary-rgb), 0.35);
        }

        .btn-add-to-cart-single:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(var(--primary-rgb), 0.5);
            filter: brightness(1.1);
        }

        .btn-add-to-cart-single:active {
            transform: scale(0.98);
        }

        .btn-whatsapp-single {
            background: #25d366;
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.25);
        }

        .btn-whatsapp-single:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        /* Notes */
        .single-notes-box {
            margin-top: 20px;
        }

        .single-notes-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .single-notes-input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px 18px;
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 90px;
            transition: border-color 0.2s;
        }

        .single-notes-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255,255,255,0.06);
        }

        .single-notes-input::placeholder {
            color: var(--text-muted);
        }

        /* Cart Overlay (full panel from loja.css is already loaded) */
        .cart-header h2 {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .single-content {
                grid-template-columns: 1fr;
                gap: 30px;
                margin: 24px auto;
            }

            .single-image-wrap {
                position: static;
            }

            .single-topbar {
                padding: 12px 16px;
            }

            .topbar-company-name {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Sticky Topbar -->
    <div class="single-topbar">
        <a href="<?php echo $lojaUrl; ?>" class="single-topbar-logo">
            <?php if ($systemLogo): ?>
                <img src="<?php echo $systemLogo; ?>" alt="Logo" class="topbar-logo-img">
            <?php else: ?>
                <div style="width:40px;height:40px;background:rgba(var(--primary-rgb),0.15);border:1px solid var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="package" style="width:20px;height:20px;color:var(--primary);"></i>
                </div>
            <?php endif; ?>
            <span class="topbar-company-name"><?php echo htmlspecialchars($company['name']); ?></span>
        </a>
        <a href="<?php echo $lojaUrl; ?>" class="btn-back-loja">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Voltar ao ClubePet+
        </a>
    </div>

    <!-- Single Content -->
    <div class="single-page">
        <div class="single-content">

            <!-- Image -->
            <div class="single-image-wrap">
                <div class="single-image-container">
                    <?php if ($emPromocao): ?>
                        <div class="promo-badge-single">Clube</div>
                    <?php endif; ?>

                    <?php if (!empty($produto['capa'])): ?>
                        <img src="<?php echo $SITE_URL . '/' . ltrim($produto['capa'], '/'); ?>"
                             alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                    <?php else: ?>
                        <div class="single-image-placeholder">
                            <i data-lucide="image" style="width:64px;height:64px;opacity:0.2;"></i>
                            <span>Sem imagem disponível</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info -->
            <div class="single-info">

                <div class="single-badge">
                    <i data-lucide="tag" style="width:12px;height:12px;"></i>
                    Produto
                </div>

                <h1 class="single-title"><?php echo htmlspecialchars($produto['nome']); ?></h1>

                <?php if (!empty($produto['descricao'])): ?>
                    <p class="single-description"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                <?php endif; ?>

                <!-- Price Box -->
                <div class="single-price-box">
                    <?php if ($emPromocao && $precoPromo): ?>
                        <div class="single-price-label" style="color:var(--text-muted);">Preço normal</div>
                        <div class="single-price-original">
                            R$ <?php echo number_format($preco, 2, ',', '.'); ?>
                        </div>
                        <div class="single-price-label" style="color:#ef4444;margin-top:12px;">Cliente clube</div>
                        <div class="single-price-current is-promo">
                            R$ <?php echo number_format($precoPromo, 2, ',', '.'); ?>
                        </div>
                        <?php
                            $discount = round((($preco - $precoPromo) / $preco) * 100);
                        ?>
                        <div class="single-price-discount">
                            <i data-lucide="trending-down" style="width:12px;height:12px;"></i>
                            <?php echo $discount; ?>% de desconto
                        </div>
                    <?php else: ?>
                        <div class="single-price-label">Preço</div>
                        <div class="single-price-current">
                            R$ <?php echo number_format($preco, 2, ',', '.'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quantity + Actions -->
                <div class="single-actions">

                    <div class="single-qty-row">
                        <span class="single-qty-label">Quantidade</span>
                        <div class="quantity-control">
                            <button class="qty-btn" onclick="changeQtySingle(-1)" id="btn-minus">
                                <i data-lucide="minus" style="width:14px;height:14px;"></i>
                            </button>
                            <span id="single-qty" class="qty-number">1</span>
                            <button class="qty-btn" onclick="changeQtySingle(1)" id="btn-plus">
                                <i data-lucide="plus" style="width:14px;height:14px;"></i>
                            </button>
                        </div>
                        <div class="single-subtotal" id="single-subtotal">
                            R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="single-notes-box">
                        <span class="single-notes-label">Observações (opcional)</span>
                        <textarea id="single-notes"
                                  class="single-notes-input"
                                  placeholder="Ex: Sem determinado ingrediente, preferências especiais..."></textarea>
                    </div>

                    <button class="btn-add-to-cart-single" id="btn-add-cart" onclick="addToCartSingle()">
                        <i data-lucide="shopping-bag" style="width:20px;height:20px;"></i>
                        Adicionar ao Carrinho
                    </button>

                    <?php
                        $whatsapp_number = preg_replace('/\D/', '', $company['phone'] ?? '');
                        if (strlen($whatsapp_number) === 10 || strlen($whatsapp_number) === 11) {
                            $whatsapp_number = '55' . $whatsapp_number;
                        }
                        $wa_text = "Olá! Tenho interesse no produto: " . $produto['nome'] . " (R$ " . number_format($precoFinal, 2, ',', '.') . ")";
                        $wa_link = !empty($whatsapp_number) ? "https://wa.me/{$whatsapp_number}?text=" . urlencode($wa_text) : null;
                    ?>
                    <?php if ($wa_link): ?>
                        <a href="<?php echo $wa_link; ?>" target="_blank" class="btn-whatsapp-single">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .018 5.394 0 12.03c0 2.122.54 4.197 1.566 6.073L0 24l6.11-1.603a11.78 11.78 0 005.938 1.603h.005c6.637 0 12.032-5.395 12.035-12.032a11.761 11.761 0 00-3.528-8.503z"/>
                            </svg>
                            Perguntar pelo WhatsApp
                        </a>
                    <?php endif; ?>

                </div><!-- /single-actions -->
            </div><!-- /single-info -->

        </div><!-- /single-content -->
    </div><!-- /single-page -->

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
                        <span>Retirar no ClubePet+</span>
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
                <a href="<?php echo $lojaUrl; ?>" class="btn-text-only" style="text-align:center;text-decoration:none;">
                    <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
                    Continuar comprando
                </a>
            </div>
        </div>
    </div>

    <script>
    // System Constants
    const SITE_URL = '<?php echo $SITE_URL; ?>';
    const COMPANY_ID = '<?php echo $company['id']; ?>';
    const COMPANY_SLUG = '<?php echo $company['slug']; ?>';
    const CART_KEY = 'cart_clube_pet_' + COMPANY_ID;
    const DELIVERY_FEE = parseFloat('<?php echo $company['taxa_entrega'] ?? 0; ?>');

    const PRODUCT = <?php echo json_encode([
        'id'              => (int)$produto['id'],
        'nome'            => $produto['nome'],
        'descricao'       => $produto['descricao'] ?? '',
        'preco'           => (float)$produto['preco'],
        'preco_promo'     => ($emPromocao && $precoPromo) ? $precoPromo : null,
        'em_promocao'     => $emPromocao,
        'preco_final'     => $precoFinal,
        'capa'            => $produto['capa'] ?? null,
    ], JSON_UNESCAPED_UNICODE); ?>;

    // UI Helpers
    const UI = {
        showToast(message, type = 'success') {
            console.log(`[TOAST ${type}] ${message}`);
            alert(message);
        }
    };

    let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
    let deliveryMethod = 'delivery';
    let currentQty = 1;

    // --- Single Product Actions ---
    function changeQtySingle(delta) {
        currentQty = Math.max(1, currentQty + delta);
        document.getElementById('single-qty').innerText = currentQty;
        updateSubtotal();
    }

    function updateSubtotal() {
        const total = PRODUCT.preco_final * currentQty;
        document.getElementById('single-subtotal').innerText =
            'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    }

    function addToCartSingle() {
        const notes = document.getElementById('single-notes').value.trim();
        const hashKey = 'p' + PRODUCT.id + (notes ? '-n' + notes.slice(0, 20) : '');

        const existingItem = cart.find(it => it.id === PRODUCT.id && it.extrasHash === hashKey);

        if (existingItem) {
            existingItem.quantidade += currentQty;
        } else {
            cart.push({
                id: PRODUCT.id,
                nome: PRODUCT.nome,
                preco: PRODUCT.preco_final,
                quantidade: currentQty,
                extras: [],
                extrasHash: hashKey,
                uniqueKey: Date.now(),
                observacoes: notes
            });
        }

        saveCart();
        renderCart();

        // Fly Animation
        const btn = document.getElementById('btn-add-cart');
        if (btn) {
            const rect = btn.getBoundingClientRect();
            flyToCart(rect.left + rect.width / 2, rect.top + rect.height / 2);
        }

        currentQty = 1;
        document.getElementById('single-qty').innerText = '1';
        document.getElementById('single-notes').value = '';
        updateSubtotal();

        setTimeout(() => toggleCart(true), 400);
    }

    function flyToCart(startX, startY) {
        const cartBtn = document.getElementById('cart-btn');
        if (!cartBtn) return;
        const cartRect = cartBtn.getBoundingClientRect();
        const endX = cartRect.left + cartRect.width / 2;
        const endY = cartRect.top + cartRect.height / 2;

        const flyer = document.createElement('div');
        flyer.className = 'cart-flyer';
        flyer.style.left = startX + 'px'; flyer.style.top = startY + 'px';
        flyer.style.background = 'var(--primary)';
        document.body.appendChild(flyer);

        setTimeout(() => {
            flyer.style.left = endX + 'px';
            flyer.style.top = endY + 'px';
            flyer.style.transform = 'scale(0.1)';
            flyer.style.opacity = '0';
        }, 50);

        setTimeout(() => {
            flyer.remove();
            cartBtn.classList.remove('pulse');
            void cartBtn.offsetWidth;
            cartBtn.classList.add('pulse');
        }, 600);
    }

    // --- Standard Cart/Checkout Logic ---
    function removeItemFromCart(key) {
        cart = cart.filter(it => it.uniqueKey !== key);
        saveCart();
        renderCart();
    }

    function clearCart() {
        if (!confirm('Limpar todo o carrinho?')) return;
        cart = []; saveCart(); renderCart(); hideCheckoutForm();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const countEl   = document.getElementById('cart-count');
        const totalEl   = document.getElementById('cart-total');
        const footer    = document.getElementById('cart-footer');
        const devFeeRow = document.getElementById('delivery-fee-row');
        const devFeeVal = document.getElementById('cart-delivery');

        const totalQty = cart.reduce((sum, it) => sum + it.quantidade, 0);
        countEl.innerText = totalQty;

        if (cart.length === 0) {
            container.innerHTML = `<div class="empty-state-cart"><i data-lucide="shopping-cart"></i><p>Seu carrinho está vazio.</p></div>`;
            footer.classList.add('hidden');
            if (window.lucide) lucide.createIcons();
            return;
        }

        footer.classList.remove('hidden');
        let subtotal = 0;
        container.innerHTML = cart.map(item => {
            const itemSubtotal = item.preco * item.quantidade;
            subtotal += itemSubtotal;
            return `<div class="cart-item"><div class="cart-item-info"><div class="cart-item-title">${item.quantidade}x ${item.nome}</div>${item.observacoes ? `<div class="cart-item-extras">Obs: ${item.observacoes}</div>` : ''}<div class="cart-item-price">R$ ${itemSubtotal.toLocaleString('pt-BR', {minimumFractionDigits:2})}</div></div><button onclick="removeItemFromCart(${item.uniqueKey})" class="btn-remove-item"><i data-lucide="x"></i></button></div>`;
        }).join('');

        let total = subtotal;
        if (deliveryMethod === 'delivery') {
            total += DELIVERY_FEE;
            devFeeRow.classList.remove('hidden');
            devFeeVal.innerText = '+ R$ ' + DELIVERY_FEE.toLocaleString('pt-BR', {minimumFractionDigits:2});
        } else {
            devFeeRow.classList.add('hidden');
        }
        totalEl.innerText = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2});
        if (window.lucide) lucide.createIcons();
    }

    function saveCart() { localStorage.setItem(CART_KEY, JSON.stringify(cart)); }
    function toggleCart(open = null) {
        const p = document.getElementById('cart-panel');
        if (p) {
            if (open === true) p.classList.add('active'); else if (open === false) p.classList.remove('active'); else p.classList.toggle('active');
        }
    }

    function showCheckoutForm() {
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
        document.getElementById('address-fields-wrapper').classList.toggle('hidden', method === 'pickup');
        renderCart();
    }

    async function checkClientByPhone(phone) {
        if (!phone || phone.length < 10) return;
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
    }

    async function lookupCEP(cep) {
        const clean = cep.replace(/\D/g, ''); if (clean.length !== 8) return;
        const res = await fetch(`https://viacep.com.br/ws/${clean}/json/`);
        const data = await res.json();
        if (!data.erro) {
            document.getElementById('order_address').value = data.logradouro || '';
            document.getElementById('order_neighborhood').value = data.bairro || '';
            document.getElementById('order_city').value = data.localidade || '';
            document.getElementById('order_state').value = data.uf || '';
            document.getElementById('order_number').focus();
        }
    }

    async function submitOrder(mode = 'delivery') {
        const name = document.getElementById('order_name').value;
        const phone = document.getElementById('order_phone').value;
        if (!name || !phone) { UI.showToast('Preencha nome e telefone', 'warning'); return; }

        const orderData = {
            company_id: COMPANY_ID, cliente_nome: name, cliente_telefone: phone,
            zip_code: document.getElementById('order_zip').value,
            neighborhood: document.getElementById('order_neighborhood').value,
            address: document.getElementById('order_address').value,
            city: document.getElementById('order_city').value,
            state: document.getElementById('order_state').value,
            number: document.getElementById('order_number').value,
            complement: document.getElementById('order_complement').value,
            tipo: deliveryMethod, payment_mode: mode,
            observacoes: document.getElementById('order_notes').value,
            itens: cart.map(it => ({ id: it.id, nome: it.nome, preco: it.preco, quantidade: it.quantidade }))
        };

        try {
            const res = await fetch(`${SITE_URL}/api/pedidos/public-create`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(orderData)
            });
            const result = await res.json();
            if (result.success) {
                localStorage.removeItem(CART_KEY);
                if (mode === 'online' && result.payment_url) { window.location.href = result.payment_url; return; }
                UI.showToast('Pedido realizado!');
                setTimeout(() => window.location.reload(), 2000);
            } else { UI.showToast(result.message || 'Erro ao processar', 'error'); }
        } catch (e) { UI.showToast('Erro de conexão', 'error'); }
    }

    function sendWhatsapp(data, orderId) {
        let msg = `🛒 *Novo Pedido - #${orderId}*\n\n`;
        msg += `👤 *Cliente:* ${data.cliente_nome}\n📍 *Tipo:* ${data.tipo === 'delivery' ? 'Entrega' : 'Retirada'}\n`;
        if (data.tipo === 'delivery') msg += `🏠 *Endereço:* ${data.address}, ${data.number}\n`;
        msg += `\n📦 *Itens:*\n`;
        let total = 0;
        cart.forEach(it => { total += (it.preco * it.quantidade); msg += `• ${it.quantidade}x ${it.nome} - R$ ${(it.preco * it.quantidade).toLocaleString('pt-BR')}\n`; });
        if (data.tipo === 'delivery') total += DELIVERY_FEE;
        msg += `\n*💰 TOTAL: R$ ${total.toLocaleString('pt-BR')}*\n`;
        const waPhone = '<?php echo preg_replace('/\D/','',$company['phone']??''); ?>';
        window.open(`https://wa.me/${waPhone.length <= 11 ? '55' + waPhone : waPhone}?text=${encodeURIComponent(msg)}`, '_blank');
    }

    document.addEventListener('DOMContentLoaded', () => { renderCart(); updateSubtotal(); if (window.lucide) lucide.createIcons(); });
    </script>

</body>
</html>
