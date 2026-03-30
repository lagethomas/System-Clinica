<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;

class PetController extends Controller {

    public function index(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        $search = $_GET['search'] ?? '';
        $where = "WHERE p.company_id = :cid";
        $params = ['cid' => $company_id];

        if (!empty($search)) {
            $where .= " AND (p.nome LIKE :s1 OR t.nome LIKE :s2 OR pl.numero_carteirinha LIKE :s3)";
            $params['s1'] = "%$search%";
            $params['s2'] = "%$search%";
            $params['s3'] = "%$search%";
        }

        $sql = "SELECT p.*, t.nome as tutor_nome, pl.numero_carteirinha, pl.status as plano_status 
                FROM cp_pets p 
                JOIN cp_tutores t ON p.tutor_id = t.id 
                LEFT JOIN cp_planos_pet pl ON p.id = pl.pet_id 
                $where 
                ORDER BY p.nome ASC";

        $pets = Database::fetchAll($sql, $params);
        $tutores = Database::fetchAll("SELECT id, nome FROM cp_tutores WHERE company_id = :cid ORDER BY nome ASC", ['cid' => $company_id]);

        $this->render('app/pets', [
            'title' => 'Gestão de Pacientes (Pets)',
            'pets' => $pets,
            'tutores' => $tutores,
            'search' => $search,
            'nonce_save' => \Nonce::create('pet_save'),
            'nonce_delete' => \Nonce::create('pet_delete')
        ]);
    }

    public function perfil($id): void {
        $id = (int)$id;
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        $pet = Database::fetch("SELECT p.*, t.nome as tutor_nome, t.telefone as tutor_telefone, t.email as tutor_email, pl.numero_carteirinha, pl.status as plano_status 
                                FROM cp_pets p 
                                JOIN cp_tutores t ON p.tutor_id = t.id 
                                LEFT JOIN cp_planos_pet pl ON p.id = pl.pet_id 
                                WHERE p.id = :id AND p.company_id = :cid", ['id' => $id, 'cid' => $company_id]);

        if (!$pet) {
            $this->redirect('/app/pets');
            return;
        }

        $consultas = Database::fetchAll("SELECT * FROM cp_consultas WHERE pet_id = :pid AND company_id = :cid ORDER BY data_consulta DESC", 
                                        ['pid' => $id, 'cid' => $company_id]);

        $this->render('app/pet_perfil', [
            'title' => 'Perfil do Pet: ' . $pet['nome'],
            'pet' => $pet,
            'consultas' => $consultas,
            'nonce_save' => \Nonce::create('pet_detail_save')
        ]);
    }

    public function save(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        if (!\Nonce::verify($_POST['nonce'] ?? '', 'pet_save')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        $tutor_id = (int)$_POST['tutor_id'];
        
        $data = [
            'company_id' => $company_id,
            'tutor_id' => $tutor_id,
            'nome' => $_POST['nome'] ?? '',
            'especie' => $_POST['especie'] ?? null,
            'raca' => $_POST['raca'] ?? null,
            'sexo' => $_POST['sexo'] ?? null,
            'idade' => $_POST['idade'] ?? null,
            'peso' => !empty($_POST['peso']) ? (float)str_replace(',', '.', $_POST['peso']) : null,
            'cor' => $_POST['cor'] ?? null,
            'microchip' => $_POST['microchip'] ?? null
        ];

        if (empty($data['nome']) || empty($data['tutor_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome e Tutor são obrigatórios'], 400);
            return;
        }

        // Handle Image Upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('pet_') . '.' . $ext;
            $upload_dir = __DIR__ . '/../../public/uploads/pets/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $filename)) {
                $data['foto_url'] = '/uploads/pets/' . $filename;
            }
        }

        if ($id) {
            Database::update('cp_pets', $data, "id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            $pet_id = (int)$id;
            Logger::log('pet_update', "Atualizou pet ID #$id: " . $data['nome']);
        } else {
            Database::insert('cp_pets', $data);
            $pet_id = (int)Database::getInstance()->lastInsertId();
            Logger::log('pet_create', "Cadastrou novo pet: " . $data['nome']);
        }

        // Handle Plan Card Number
        $carteirinha = $_POST['numero_carteirinha'] ?? '';
        if (!empty($carteirinha)) {
            $check = Database::fetch("SELECT id FROM cp_planos_pet WHERE pet_id = :pid AND company_id = :cid", ['pid' => $pet_id, 'cid' => $company_id]);
            if ($check) {
                Database::update('cp_planos_pet', ['numero_carteirinha' => $carteirinha], "id = :id AND company_id = :cid", ['id' => $check['id'], 'cid' => $company_id]);
            } else {
                Database::insert('cp_planos_pet', [
                    'company_id' => $company_id,
                    'pet_id' => $pet_id,
                    'numero_carteirinha' => $carteirinha
                ]);
            }
        }

        $this->jsonResponse(['success' => true]);
    }

    public function delete(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        if (!\Nonce::verify($_POST['nonce'] ?? '', 'pet_delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            Database::query("DELETE FROM cp_pets WHERE id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            Logger::log('pet_delete', "Removeu pet ID #$id");
            $this->jsonResponse(['success' => true]);
        }
    }
}
