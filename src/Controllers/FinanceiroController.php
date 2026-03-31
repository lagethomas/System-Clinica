<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Pagination;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;
use PDO;

class FinanceiroController extends Controller {
    
    public function index(): void {
        Auth::requirePermission('financeiro');
        $company_id = Auth::companyId();
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $where = " WHERE DATE(data_movimentacao) BETWEEN :start AND :end ";
        $params = ['start' => $start_date, 'end' => $end_date];

        if ($company_id) {
            $where .= " AND company_id = :cid ";
            $params['cid'] = $company_id;
        }

        // Count total for pagination
        $totalItems = (int)Database::fetch("SELECT COUNT(*) as total FROM cp_financeiro $where", $params)['total'];
        $pagination = Pagination::getParams($totalItems, 25);

        $sql = "SELECT * FROM cp_financeiro $where ORDER BY data_movimentacao DESC LIMIT :limit OFFSET :offset";
        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular resumo (com base no filtro)
        $res_entradas = Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro $where AND tipo = 'entrada'", $params);
        $totalEntradas = (float)($res_entradas['total'] ?? 0);
        
        $res_saidas = Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro $where AND tipo = 'saida'", $params);
        $totalSaidas = (float)($res_saidas['total'] ?? 0);
        
        $saldo = $totalEntradas - $totalSaidas;

        // Dados para o gráfico (últimos 7 dias)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $offset = '-' . (string)$i . ' days';
            $date = date('Y-m-d', (int)strtotime($offset));
            $label = date('d/m', strtotime($date));
            
            $c_where = "WHERE DATE(data_movimentacao) = :d";
            $c_params = ['d' => $date];
            if ($company_id) {
                $c_where .= " AND company_id = :cid";
                $c_params['cid'] = $company_id;
            }

            $entrada = (float)(Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro $c_where AND tipo = 'entrada'", $c_params)['total'] ?? 0);
            $saida = (float)(Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro $c_where AND tipo = 'saida'", $c_params)['total'] ?? 0);
            
            $chartData[] = [
                'label' => $label,
                'entrada' => (float)$entrada,
                'saida' => (float)$saida
            ];
        }

        $this->render('app/financeiro', [
            'title' => 'Módulo Financeiro',
            'movimentacoes' => $movimentacoes,
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ],
            'resumo' => [
                'entradas' => (float)$totalEntradas,
                'saidas' => (float)$totalSaidas,
                'saldo' => (float)$saldo
            ],
            'chartData' => $chartData,
            'pagination' => $pagination,
            'tutores' => Database::fetchAll("SELECT id, nome FROM cp_tutores WHERE company_id = :cid ORDER BY nome ASC", ['cid' => $company_id]),
            'nonce_add' => \Nonce::create('financeiro_add'),
            'nonce_delete' => \Nonce::create('financeiro_delete'),
        ]);
    }

    public function addMovimentacao(): void {
        Auth::requirePermission('financeiro');
        $company_id = Auth::companyId();
        $user_id = (int)($_SESSION['user_id'] ?? 0);

        // Rule 6: Validate Nonce
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'financeiro_add')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $descricao = $_POST['descricao'] ?? '';
        $valor = str_replace(',', '.', $_POST['valor'] ?? '0');
        $tipo = $_POST['tipo'] ?? 'entrada';
        $categoria = $_POST['categoria'] ?? 'Geral';
        $metodo = $_POST['metodo_pagamento'] ?? 'Outros';

        if (empty($descricao) || empty($valor)) {
            $this->jsonResponse(['success' => false, 'message' => 'Campos obrigatórios'], 400);
            return;
        }

        $tutor_id = !empty($_POST['tutor_id']) ? (int)$_POST['tutor_id'] : null;

        Database::insert('cp_financeiro', [
            'company_id' => $company_id,
            'user_id' => $user_id,
            'tutor_id' => $tutor_id,
            'descricao' => $descricao,
            'valor' => (float)$valor,
            'tipo' => $tipo,
            'categoria' => $categoria,
            'metodo_pagamento' => $metodo
        ]);

        $msg = "Lançamento manual de R$ " . number_format((float)$valor, 2, ',', '.') . " ($tipo) [$categoria] via $metodo — $descricao";
        Logger::log('financeiro_manual', $msg);
        \App\Helpers\Notification::forCompany((int)$company_id, 'Novo Lançamento Financeiro', $msg, "/app/financeiro", (($tipo === 'entrada') ? 'success' : 'warning'), $user_id);

        $this->jsonResponse(['success' => true]);
    }

    public function delete(): void {
        Auth::requirePermission('financeiro');
        $company_id = Auth::companyId();
        
        // Rule 6: Validate Nonce
        if (!\Nonce::verify($_POST['nonce'] ?? '', 'financeiro_delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            $m_where = "id = :id";
            $m_params = ['id' => $id];
            if ($company_id) {
                $m_where .= " AND company_id = :cid";
                $m_params['cid'] = $company_id;
            }

            // Simple delete for generic SaaS
            Database::query("DELETE FROM cp_financeiro WHERE $m_where", $m_params);

            $user_id = (int)$_SESSION['user_id'];
            $msg = "Removeu lançamento financeiro ID #" . (string)$id;
            Logger::log('financeiro_deletado', $msg);
            
            $this->jsonResponse(['success' => true]);
        }
    }
}
