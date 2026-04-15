<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;

class CheckoutController extends Controller {
    
    public function index($invoice_id): void {
        global $pdo, $platform_settings;
        require_once __DIR__ . '/../../includes/repositories/CompanyRepository.php';

        $stmt = $pdo->prepare("SELECT i.*, c.name as company_name, c.email, c.document, c.id as company_id 
                               FROM cp_invoices i 
                               JOIN cp_companies c ON i.company_id = c.id 
                               WHERE i.id = ?");
        $stmt->execute([(int)$invoice_id]);
        $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$invoice || $invoice['status'] === 'paid') {
            echo "Fatura não encontrada ou já paga.";
            return;
        }

        // Recuperar credenciais do Master Admin ou Plataforma (configuração de MercadoPago)
        $mp_access_token = $platform_settings['mp_access_token'] ?? '';
        
        // Fallback: Se não estiver no global, tenta carregar direto do banco (prevenção contra problemas de escopo/cache)
        if (empty($mp_access_token)) {
            $stmt_sets = $pdo->query("SELECT setting_value FROM cp_settings WHERE setting_key = 'mp_access_token'");
            $mp_access_token = $stmt_sets->fetchColumn() ?: '';
            
            if (!empty($mp_access_token)) {
                $this->logMercadoPagoError("Info: Token recuperado via fallback do banco (global estava vazio).", ["invoice_id" => $invoice_id]);
            }
        }

        if (empty($mp_access_token)) {
            $this->logMercadoPagoError("Token de acesso ausente nas configurações da plataforma.", [
                "invoice_id" => $invoice_id,
                "debug_global_keys" => array_keys($platform_settings ?? [])
            ]);
            echo "Configuração do MercadoPago ausente.";
            return;
        }

        $base_url = SITE_URL;
        $preferenceData = [
            "items" => [
                [
                    "title" => $invoice['description'] ?: 'Mensalidade SaaS',
                    "quantity" => 1,
                    "unit_price" => (float)$invoice['amount'],
                    "currency_id" => "BRL"
                ]
            ],
            "payer" => [
                "email" => $invoice['email'] ?: 'cliente@email.com'
            ],
            "external_reference" => "INV-" . $invoice['id'],
            "back_urls" => [
                "success" => $base_url . "/payment/callback?status=success&invoice_id=" . $invoice['id'],
                "failure" => $base_url . "/payment/callback?status=failure&invoice_id=" . $invoice['id'],
                "pending" => $base_url . "/payment/callback?status=pending&invoice_id=" . $invoice['id']
            ],
            "auto_return" => "approved",
            "notification_url" => $base_url . "/api/webhook/mercadopago" // Webhook address
        ];

        $ch = curl_init("https://api.mercadopago.com/checkout/preferences");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preferenceData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $mp_access_token,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $this->logMercadoPagoError("Erro de cURL na requisição ao Mercado Pago.", ["curl_error" => curl_error($ch)]);
        }
        curl_close($ch);
        
        $preference = json_decode($response, true);

        if (isset($preference['id'])) {
            // Redirect to MercadoPago
            header("Location: " . $preference['init_point']);
            exit;
        } else {
            $this->logMercadoPagoError("Erro ao gerar preferência de pagamento.", [
                "invoice_id" => $invoice_id,
                "api_response" => $preference,
                "request_data" => $preferenceData
            ]);
            echo "Erro ao gerar pagamento: <pre>" . print_r($preference, true) . "</pre>";
        }
    }

    public function callback(): void {
        global $pdo;
        $status = $_GET['status'] ?? 'failure';
        $invoice_id = $_GET['invoice_id'] ?? null;

        // Se o status no GET não for sucesso, verifica no banco de dados se o webhook já marcou como pago
        if ($status !== 'success' && $invoice_id) {
            $stmt = $pdo->prepare("SELECT status FROM cp_invoices WHERE id = ?");
            $stmt->execute([(int)$invoice_id]);
            $db_status = $stmt->fetchColumn();
            
            if ($db_status === 'paid') {
                $status = 'success';
            }
        }
        
        // Exibir página amigável (sem layout global)
        $this->render('payment_callback', [
            'status' => $status,
            'invoice_id' => $invoice_id
        ], false);
    }

    public function receipt($id): void {
        Auth::requireLogin();
        global $pdo;
        
        $stmt = $pdo->prepare("
            SELECT i.*, c.name as company_name, c.document, c.email, p.name as plan_name
            FROM cp_invoices i
            JOIN cp_companies c ON i.company_id = c.id
            LEFT JOIN cp_plans p ON c.plan_id = p.id
            WHERE i.id = ? AND i.status = 'paid'
        ");
        $stmt->execute([(int)$id]);
        $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$invoice) {
            echo "Recibo não disponível ou fatura não paga.";
            return;
        }

        $this->render('checkout_receipt', [
            'invoice' => $invoice
        ], false);
    }

    public function webhook(): void {
        global $pdo, $platform_settings;
        require_once __DIR__ . '/../../includes/repositories/CompanyRepository.php';
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $cid = (int)($_GET['cid'] ?? 0);

        if (isset($data['type']) && $data['type'] === 'payment') {
            $payment_id = $data['data']['id'];
            
            // Determine Token (Platform vs Specific Company)
            $mp_token = $platform_settings['mp_access_token'] ?? '';
            if ($cid > 0) {
                $stmt_c = $pdo->prepare("SELECT mp_access_token FROM cp_companies WHERE id = ?");
                $stmt_c->execute([$cid]);
                $c_token = $stmt_c->fetchColumn();
                if ($c_token) $mp_token = $c_token;
            }

            if (empty($mp_token)) {
                $stmt_sets = $pdo->query("SELECT setting_value FROM cp_settings WHERE setting_key = 'mp_access_token'");
                $mp_token = $stmt_sets->fetchColumn() ?: '';
            }

            if (!$mp_token) {
                $this->logMercadoPagoError("Webhook: Token MP ausente ao tentar processar pagamento.", ["payment_id" => $payment_id, "cid" => $cid]);
                http_response_code(500);
                exit;
            }

            // Consultar o pagamento
            $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $payment_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $mp_token
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $payment_info = json_decode($response, true);

            if (!isset($payment_info['status'])) {
                $this->logMercadoPagoError("Webhook: Erro ao consultar pagamento no MP.", [
                    "payment_id" => $payment_id,
                    "api_response" => $payment_info
                ]);
            }

            if (isset($payment_info['status']) && $payment_info['status'] === 'approved') {
                $ref = $payment_info['external_reference'] ?? '';
                
                // 1. SaaS Invoice
                if (strpos($ref, 'INV-') === 0) {
                    $inv_id = (int)str_replace('INV-', '', $ref);
                    
                    // Marcar fatura como paga
                    $stmt = $pdo->prepare("UPDATE cp_invoices SET status = 'paid', paid_at = NOW() WHERE id = ? AND status != 'paid'");
                    $stmt->execute([$inv_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        // Sincronizar vencimento da empresa
                        $stmt2 = $pdo->prepare("SELECT company_id FROM cp_invoices WHERE id = ?");
                        $stmt2->execute([$inv_id]);
                        $company_id = $stmt2->fetchColumn();

                        if ($company_id) {
                            $companyRepo = new \CompanyRepository($pdo);
                            $companyRepo->synchronizeExpiration($company_id);
                        }
                    }
                }
                    // 2. Store Order
                else if (strpos($ref, 'PEDLOJA-') === 0) {
                    $order_id = (int)str_replace('PEDLOJA-', '', $ref);
                    
                    // Mark order payment as paid
                    $stmt = $pdo->prepare("UPDATE cp_pedidos_loja SET payment_status = 'paid', payment_id = ? WHERE id = ? AND payment_status != 'paid'");
                    $stmt->execute([$payment_id, $order_id]);

                    if ($stmt->rowCount() > 0) {
                        // Add to financial module
                        $order = \App\Core\Database::fetch("SELECT * FROM cp_pedidos_loja WHERE id = ?", [$order_id]);
                        if ($order) {
                            $company_id = (int)$order['company_id'];
                            $descFin = "Pedido Loja #$order_id - " . $order['cliente_nome'];
                            
                            // Check for duplicates
                            $exists = \App\Core\Database::fetch("SELECT id FROM cp_financeiro WHERE company_id = ? AND descricao = ?", [$company_id, $descFin]);
                            
                            if (!$exists) {
                                \App\Core\Database::insert('cp_financeiro', [
                                    'company_id'       => $company_id,
                                    'user_id'          => 0, // System automated
                                    'tutor_id'         => $order['tutor_id'] ?: null,
                                    'descricao'        => $descFin,
                                    'valor'            => (float)$order['total'],
                                    'tipo'             => 'entrada',
                                    'categoria'        => 'Venda Loja',
                                    'metodo_pagamento' => 'Mercado Pago'
                                ]);
                                
                                \App\Helpers\Logger::log('store_financial_entry', "Lançamento automático via Webhook (MP) referente ao pedido #$order_id");
                            }
                        }
                    }
                }
            }
        }
        
        http_response_code(200);
        echo "OK";
    }

    /**
     * Auxiliar para logar erros específicos do Mercado Pago
     */
    private function logMercadoPagoError(string $message, array $context = []): void {
        $logFile = __DIR__ . '/../../logs/mercadopago_error.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextJson = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $logEntry = "[{$timestamp}] ERROR: {$message}\nContexto: {$contextJson}\n" . str_repeat('-', 50) . "\n";
        
        // Garante que o diretório de logs existe
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
