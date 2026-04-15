<?php
declare(strict_types=1);

namespace App\Modules\Produtos\Controllers;

use App\Core\Controller;
use App\Modules\Produtos\Models\Categoria;
use App\Helpers\Logger;
use Auth;
use Nonce;

class CategoriasController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        $categorias = Categoria::allByCompany($company_id);

        $this->render('Modules/Produtos/Views/categorias/index', [
            'title' => 'Gerenciamento de Categorias',
            'categorias' => $categorias,
            'nonce_save' => Nonce::create('categoria_save'),
            'nonce_delete' => Nonce::create('categoria_delete')
        ]);
    }

    public function save(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        if (!Nonce::verify($_POST['nonce'] ?? '', 'categoria_save')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $data = [
            'company_id' => $company_id,
            'nome' => $_POST['nome'] ?? ''
        ];

        if (empty($data['nome'])) {
            $this->jsonResponse(['success' => false, 'message' => 'O nome da categoria é obrigatório'], 400);
            return;
        }

        if ($id) {
            Categoria::update($id, $data);
            Logger::log('categoria_update', "Atualizou categoria ID #$id: " . $data['nome']);
            $msg = 'Categoria atualizada com sucesso!';
        } else {
            $id = (int)Categoria::create($data);
            Logger::log('categoria_create', "Cadastrou nova categoria: " . $data['nome']);
            $msg = 'Categoria cadastrada com sucesso!';
        }

        $this->jsonResponse(['success' => true, 'message' => $msg, 'id' => $id]);
    }

    public function delete(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        if (!Nonce::verify($_POST['nonce'] ?? '', 'categoria_delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            $cat = Categoria::find((int)$id);
            if ($cat && $cat['company_id'] == $company_id) {
                Categoria::delete((int)$id);
                Logger::log('categoria_delete', "Removeu categoria ID #$id");
                $this->jsonResponse(['success' => true]);
                return;
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Categoria não encontrada ou sem permissão'], 404);
    }
}
