<?php
declare(strict_types=1);

namespace App\Modules\Produtos\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Modules\Produtos\Models\Produto;
use App\Helpers\Logger;
use Auth;
use Nonce;

class ProdutosController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        $produtos = Produto::allByCompany($company_id);
        $categorias = \App\Modules\Produtos\Models\Categoria::allByCompany($company_id);

        $this->render('Modules/Produtos/Views/index', [
            'title' => 'Gerenciamento de Produtos',
            'produtos' => $produtos,
            'categorias' => $categorias,
            'nonce_save' => Nonce::create('produto_save'),
            'nonce_delete' => Nonce::create('produto_delete')
        ]);
    }

    public function save(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        if (!Nonce::verify($_POST['nonce'] ?? '', 'produto_save')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $data = [
            'company_id' => $company_id,
            'nome' => $_POST['nome'] ?? '',
            'categoria_id' => !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null,
            'descricao' => $_POST['descricao'] ?? null,
            'preco' => str_replace(',', '.', $_POST['preco'] ?? '0'),
            'preco_promocional' => !empty($_POST['preco_promocional']) ? str_replace(',', '.', $_POST['preco_promocional']) : null,
            'em_promocao' => isset($_POST['em_promocao']) ? 1 : 0,
            'status' => isset($_POST['status']) ? 1 : 0
        ];

        if (empty($data['nome'])) {
            $this->jsonResponse(['success' => false, 'message' => 'O nome do produto é obrigatório'], 400);
            return;
        }

        // Handle Cover Image Upload
        if (isset($_FILES['capa']) && $_FILES['capa']['error'] === 0) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($_FILES['capa']['type'], $allowed)) {
                $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
                $filename = 'prod_' . md5(uniqid()) . '.' . $ext;
                $upload_dir = '/uploads/produtos/';
                $full_dir = dirname(__DIR__, 4) . '/public' . $upload_dir;

                if (!is_dir($full_dir)) {
                    mkdir($full_dir, 0777, true);
                }

                if (move_uploaded_file($_FILES['capa']['tmp_name'], $full_dir . $filename)) {
                    $data['capa'] = $upload_dir . $filename;
                }
            }
        }

        if ($id) {
            Produto::update($id, $data);
            Logger::log('produto_update', "Atualizou produto ID #$id: " . $data['nome']);
            $msg = 'Produto atualizado com sucesso!';
        } else {
            $id = (int)Produto::create($data);
            Logger::log('produto_create', "Cadastrou novo produto: " . $data['nome']);
            $msg = 'Produto cadastrado com sucesso!';
        }

        $this->jsonResponse(['success' => true, 'message' => $msg, 'id' => $id]);
    }

    public function delete(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        if (!Nonce::verify($_POST['nonce'] ?? '', 'produto_delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            // Check ownership
            $prod = Produto::find((int)$id);
            if ($prod && $prod['company_id'] == $company_id) {
                Produto::delete((int)$id);
                Logger::log('produto_delete', "Removeu produto ID #$id");
                $this->jsonResponse(['success' => true]);
                return;
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Produto não encontrado ou sem permissão'], 404);
    }

    public function details(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }

        $prod = Produto::find((int)$id);
        if (!$prod || $prod['company_id'] != $company_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Produto não encontrado'], 404);
            return;
        }

        $this->jsonResponse(['success' => true, 'produto' => $prod]);
    }

    /**
     * Public Store View
     */
    public function loja(string $slug): void {
        // Find company by slug
        $company = Database::fetch("SELECT * FROM cp_companies WHERE slug = ? AND active = 1", [$slug]);
        if (!$company) {
            header("Location: " . SITE_URL);
            exit;
        }

        $produtos = Produto::allPublicByCompany((int)$company['id']);
        $categorias = \App\Modules\Produtos\Models\Categoria::allByCompany((int)$company['id']);

        // Group products by category
        $produtos_por_categoria = [];
        $sem_categoria = [];

        foreach ($produtos as $p) {
            if ($p['categoria_id']) {
                $produtos_por_categoria[$p['categoria_id']][] = $p;
            } else {
                $sem_categoria[] = $p;
            }
        }

        // Client Data (Tutor)
        $client_data = null;
        if (Auth::isLoggedIn() && Auth::tutorId()) {
            $client_data = Database::fetch("SELECT * FROM cp_tutores WHERE id = ?", [Auth::tutorId()]);
        }

        $this->render('Modules/Produtos/Views/loja', [
            'title'   => 'ClubePet+ - ' . $company['name'],
            'company' => $company,
            'produtos' => $produtos,
            'categorias' => $categorias,
            'produtos_por_categoria' => $produtos_por_categoria,
            'sem_categoria' => $sem_categoria,
            'client_data' => $client_data
        ], false);
    }

    /**
     * Public Product Single Page
     */
    public function single(string $slug, string $id): void {
        $company = Database::fetch("SELECT * FROM cp_companies WHERE slug = ? AND active = 1", [$slug]);
        if (!$company) {
            header("Location: " . SITE_URL);
            exit;
        }

        $produto = Produto::find((int)$id);
        if (!$produto || $produto['company_id'] != $company['id'] || !$produto['status']) {
            header("Location: " . SITE_URL . '/' . $slug . '/clube-pet');
            exit;
        }

        // Client Data (Tutor)
        $client_data = null;
        if (Auth::isLoggedIn() && Auth::tutorId()) {
            $client_data = Database::fetch("SELECT * FROM cp_tutores WHERE id = ?", [Auth::tutorId()]);
        }

        $this->render('Modules/Produtos/Views/produto_single', [
            'title'   => $produto['nome'] . ' | ' . $company['name'],
            'company' => $company,
            'produto' => $produto,
            'client_data' => $client_data
        ], false);
    }

    /**
     * API: Public Client Search (by phone)
     */
    public function publicSearchClient(): void {
        $phone = $_GET['phone'] ?? '';
        $company_id = $_GET['company_id'] ?? null;

        if (!$phone || !$company_id) {
            $this->jsonResponse(['success' => false], 400);
            return;
        }

        // Clean phone for searching
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        $client = Database::fetch("SELECT * FROM cp_tutores WHERE company_id = ? AND (REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), ' ', '') LIKE ? OR cpf = ?)", [
            $company_id,
            "%$cleanPhone%",
            $cleanPhone
        ]);

        if ($client) {
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'name' => $client['nome'],
                    'phone' => $client['telefone'],
                    'zip_code' => $client['cep'] ?? '',
                    'neighborhood' => $client['bairro'] ?? '',
                    'address' => $client['endereco'] ?? '',
                    'city' => $client['cidade'] ?? '',
                    'state' => $client['estado'] ?? '',
                    'number' => $client['numero'] ?? '',
                    'complement' => $client['complemento'] ?? ''
                ]
            ]);
        } else {
            $this->jsonResponse(['success' => false]);
        }
    }

    /**
     * API: Public Create Order (from cart)
     */
    public function publicCreateOrder(): void {
        // Force authentication for checkout
        if (!Auth::isLoggedIn() || !Auth::tutorId()) {
            $this->jsonResponse(['success' => false, 'message' => 'Autenticação necessária para finalizar a compra.'], 401);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['itens'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Dados do pedido inválidos'], 400);
            return;
        }

        $company_id = (int)$data['company_id'];
        $company = Database::fetch("SELECT * FROM cp_companies WHERE id = ?", [$company_id]);
        if (!$company) {
            $this->jsonResponse(['success' => false, 'message' => 'Empresa não encontrada'], 404);
            return;
        }

        $total = 0;
        foreach ($data['itens'] as $it) {
            $total += (float)$it['preco'] * (int)$it['quantidade'];
        }

        // Add delivery fee if applicable
        $frete = 0;
        if (($data['tipo'] ?? 'pickup') === 'delivery') {
            $frete = (float)($company['taxa_entrega'] ?? 0);
            $total += $frete;
        }

        $cashback_used = (float)($data['cashback_used'] ?? 0);
        
        // 1. Validate Cashback
        if ($cashback_used > 0) {
            $tutor = Database::fetch("SELECT cashback_balance FROM cp_tutores WHERE id = ?", [Auth::tutorId()]);
            if ($cashback_used > (float)$tutor['cashback_balance']) {
                $this->jsonResponse(['success' => false, 'message' => 'Saldo cashback insuficiente.'], 400);
                return;
            }
            if ($cashback_used > $total) $cashback_used = $total;
            $total -= $cashback_used;
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $orderId = Database::insert('cp_pedidos_loja', [
                'company_id'       => $company_id,
                'tutor_id'         => Auth::tutorId(),
                'cliente_nome'     => $data['cliente_nome'],
                'cliente_telefone' => $data['cliente_telefone'],
                'zip_code'         => $data['zip_code'] ?? null,
                'neighborhood'     => $data['neighborhood'] ?? null,
                'address'          => $data['address'] ?? null,
                'city'             => $data['city'] ?? null,
                'state'            => $data['state'] ?? null,
                'number'           => $data['number'] ?? null,
                'complement'       => $data['complement'] ?? null,
                'tipo'             => $data['tipo'] ?? 'pickup',
                'payment_mode'     => $data['payment_mode'] ?? 'delivery',
                'observacoes'      => $data['observacoes'] ?? null,
                'total'            => $total,
                'frete'            => $frete,
                'cashback_used'    => $cashback_used,
                'itens_json'       => json_encode($data['itens'], JSON_UNESCAPED_UNICODE),
                'status'           => 'pendente',
                'payment_status'   => 'pending'
            ]);

            if ($cashback_used > 0) {
                Database::query("UPDATE cp_tutores SET cashback_balance = cashback_balance - ? WHERE id = ?", [$cashback_used, Auth::tutorId()]);
                Database::insert('cp_cashback_logs', [
                    'company_id'  => $company_id,
                    'tutor_id'    => Auth::tutorId(),
                    'amount'      => $cashback_used,
                    'type'        => 'debit',
                    'source'      => 'order',
                    'description' => "Uso de saldo no pedido #$orderId"
                ]);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao processar pedido: ' . $e->getMessage()], 500);
            return;
        }

        if (!$orderId) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao salvar pedido no banco'], 500);
            return;
        }

        // 🔔 Notify all company staff about the new order
        $payLabel = ($data['payment_mode'] ?? 'delivery') === 'online' ? 'Online (MP)' : 'Na entrega';
        \App\Helpers\Notification::forCompany(
            $company_id,
            '🛒 Novo Pedido #' . $orderId,
            'De: ' . ($data['cliente_nome'] ?? 'Cliente') . ' — R$ ' . number_format($total, 2, ',', '.') . ' — ' . $payLabel,
            '/app/clube-pet/pedidos',
            'info'
        );

        // 🕐 Auto-cancel online orders pending payment for more than 4 hours
        Database::query(
            "UPDATE cp_pedidos_loja 
             SET status = 'cancelado' 
             WHERE company_id = ? 
               AND payment_mode = 'online' 
               AND payment_status = 'pending' 
               AND status = 'pendente' 
               AND created_at < DATE_SUB(NOW(), INTERVAL 4 HOUR)",
            [$company_id]
        );

        $response = [
            'success'  => true,
            'order_id' => $orderId,
            'tracking_url' => SITE_URL . '/' . $company['slug'] . '/clube-pet/confirmacao/' . $orderId
        ];

        // Mercado Pago Integration
        if (($data['payment_mode'] ?? '') === 'online' && ($company['mp_enabled'] ?? 0) == 1) {
            $mp_token = $company['mp_access_token'];
            if (!$mp_token) {
                $this->jsonResponse(['success' => false, 'message' => 'Configuração de pagamento online incompleta'], 500);
                return;
            }

            $preferenceData = [
                "items" => [
                    [
                        "title" => "Pedido #" . $orderId . " - " . $company['name'],
                        "quantity" => 1,
                        "unit_price" => (float)$total,
                        "currency_id" => "BRL"
                    ]
                ],
                "payer" => [
                    "name" => $data['cliente_nome'],
                    "phone" => ["number" => preg_replace('/\D/', '', $data['cliente_telefone'])]
                ],
                "external_reference" => "PEDLOJA-" . $orderId,
                "back_urls" => [
                    "success" => SITE_URL . '/' . $company['slug'] . '/clube-pet/checkout/success?order_id=' . $orderId,
                    "failure" => SITE_URL . '/' . $company['slug'] . '/clube-pet/checkout/failure?order_id=' . $orderId,
                    "pending" => SITE_URL . '/' . $company['slug'] . '/clube-pet/checkout/pending?order_id=' . $orderId
                ],
                "auto_return" => "approved",
                "notification_url" => SITE_URL . "/api/webhook/mercadopago?cid=" . $company_id
            ];

            $ch = curl_init("https://api.mercadopago.com/checkout/preferences");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preferenceData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $mp_token,
                "Content-Type: application/json"
            ]);

            $mp_res = curl_exec($ch);
            // curl_close is redundant in PHP 8+
            $preference = json_decode($mp_res, true);

            if (isset($preference['id'])) {
                $response['payment_url'] = $preference['init_point'];
            } else {
                // Log priority: database might have saved but MP failed
                Logger::log('mp_error', "Error generating preference for Order #$orderId: " . $mp_res);
            }
        }

        $this->jsonResponse($response);
    }

    /**
     * Admin: Store Orders Management
     */
    public function adminPedidos(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        // Fetch orders by status
        $sql = "SELECT p.*, t.nome as tutor_nome 
                FROM cp_pedidos_loja p 
                LEFT JOIN cp_tutores t ON t.id = p.tutor_id 
                WHERE p.company_id = ? 
                ORDER BY p.created_at DESC";
        $all_orders = Database::fetchAll($sql, [$company_id]);

        $pedidos = [
            'novos'      => array_filter($all_orders, fn($o) => $o['status'] === 'pendente'),
            'concluidos' => array_filter($all_orders, fn($o) => $o['status'] === 'entregue' || $o['status'] === 'confirmado'),
            'cancelados' => array_filter($all_orders, fn($o) => $o['status'] === 'cancelado')
        ];

        $this->render('Modules/Produtos/Views/admin/pedidos', [
            'title' => 'Gestão de Pedidos',
            'pedidos' => $pedidos
        ]);
    }

    /**
     * API: Update Order Status
     */
    public function updateOrderStatus(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();
        $user_id = (int)($_SESSION['user_id'] ?? 0);

        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$id || !$status) {
            $this->jsonResponse(['success' => false, 'message' => 'Campos obrigatórios ausentes'], 400);
            return;
        }

        $allowed = ['pendente', 'confirmado', 'cancelado', 'entregue'];
        if (!in_array($status, $allowed)) {
            $this->jsonResponse(['success' => false, 'message' => 'Status inválido'], 400);
            return;
        }

        // Fetch order details first
        $order = Database::fetch("SELECT * FROM cp_pedidos_loja WHERE id = ? AND company_id = ?", [$id, $company_id]);
        if (!$order) {
            $this->jsonResponse(['success' => false, 'message' => 'Pedido não encontrado'], 404);
            return;
        }

        $updated = Database::query("UPDATE cp_pedidos_loja SET status = ? WHERE id = ? AND company_id = ?", [
            $status, $id, $company_id
        ]);

        if ($updated) {
            // Se o status for 'entregue', adiciona ao financeiro (se ainda não existir)
            if ($status === 'entregue') {
                $descFin = "Pedido ClubePet+ #$id - " . $order['cliente_nome'];
                
                // Check if already in financial to prevent duplicates
                $exists = Database::fetch("SELECT id FROM cp_financeiro WHERE company_id = ? AND descricao = ?", [$company_id, $descFin]);
                
                if (!$exists) {
                    Database::insert('cp_financeiro', [
                        'company_id'       => $company_id,
                        'user_id'          => $user_id,
                        'tutor_id'         => $order['tutor_id'] ?: null,
                        'descricao'        => $descFin,
                        'valor'            => (float)$order['total'],
                        'tipo'             => 'entrada',
                        'categoria'        => 'Venda ClubePet+',
                        'metodo_pagamento' => ($order['payment_mode'] === 'online') ? 'Online' : 'Dinheiro/Cartão'
                    ]);
                    
                    Logger::log('store_financial_entry', "Lançamento automático de R$ " . number_format((float)$order['total'], 2, ',', '.') . " referente ao pedido #$id");

                    // Apply Cashback Logic
                    try {
                        require_once __DIR__ . '/../../Cashback/Helpers/CashbackHelper.php';
                        \App\Modules\Cashback\Helpers\CashbackHelper::applyForOrder((int)$id);
                    } catch (\Exception $e) {}
                }
            }

            Logger::log('store_order_status', "Status do pedido #$id alterado para $status");
            $this->jsonResponse(['success' => true, 'message' => 'Status atualizado']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao atualizar status'], 500);
        }
    }
}
