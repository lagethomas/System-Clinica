<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;

class TutorDashboardController extends Controller {

    public function index(): void {
        Auth::requireRole('tutor');
        $company_id = Auth::companyId();
        $tutor_id = Auth::tutorId();

        $tutor = Database::fetch("SELECT * FROM cp_tutores WHERE id = :id AND company_id = :cid", ['id' => $tutor_id, 'cid' => $company_id]);
        $pets = Database::fetchAll("SELECT * FROM cp_pets WHERE tutor_id = :tid AND company_id = :cid", ['tid' => $tutor_id, 'cid' => $company_id]);
        
        // Next appointments
        $next_consultas = Database::fetchAll("
            SELECT c.*, p.nome as pet_nome 
            FROM cp_consultas c
            JOIN cp_pets p ON c.pet_id = p.id
            WHERE p.tutor_id = :tid 
            AND c.data_consulta >= CURDATE()
            AND c.company_id = :cid
            ORDER BY c.data_consulta ASC
            LIMIT 5
        ", ['tid' => $tutor_id, 'cid' => $company_id]);

        $this->render('tutor/dashboard', [
            'title'             => 'Minha Área - ' . $tutor['nome'],
            'tutor'             => $tutor,
            'pets'              => $pets,
            'next_appointments' => $next_consultas,
            'pending_orders'    => Database::fetchAll(
                "SELECT * FROM cp_pedidos_loja 
                 WHERE tutor_id = :tid AND company_id = :cid 
                   AND payment_mode = 'online' AND payment_status = 'pending' AND status = 'pendente'
                 ORDER BY created_at DESC",
                ['tid' => $tutor_id, 'cid' => $company_id]
            )
        ]);
    }

    public function minhasCompras(): void {
        Auth::requireRole('tutor');
        $company_id = Auth::companyId();
        $tutor_id   = Auth::tutorId();

        $pedidos = Database::fetchAll(
            "SELECT * FROM cp_pedidos_loja
             WHERE tutor_id = :tid AND company_id = :cid
             ORDER BY created_at DESC",
            ['tid' => $tutor_id, 'cid' => $company_id]
        );

        $this->render('tutor/minhas_compras', [
            'title'   => 'Minhas Compras',
            'pedidos' => $pedidos,
        ]);
    }

    public function petPerfil($id): void {
        Auth::requireRole('tutor');
        $company_id = Auth::companyId();
        $tutor_id = Auth::tutorId();

        // Security check: Ensure the pet belongs to the logged-in tutor
        $pet = Database::fetch("
            SELECT p.*, pl.numero_carteirinha, pl.status as plano_status 
            FROM cp_pets p 
            LEFT JOIN cp_planos_pet pl ON p.id = pl.pet_id 
            WHERE p.id = :id AND p.tutor_id = :tid AND p.company_id = :cid
        ", ['id' => $id, 'tid' => $tutor_id, 'cid' => $company_id]);

        if (!$pet) {
            $this->redirect('/app/tutor/dashboard');
            return;
        }

        $consultas = Database::fetchAll("
            SELECT * FROM cp_consultas 
            WHERE pet_id = :pid AND company_id = :cid 
            ORDER BY data_consulta DESC
        ", ['pid' => $id, 'cid' => $company_id]);

        $this->render('tutor/pet_perfil', [
            'title' => 'Prontuário: ' . $pet['nome'],
            'pet' => $pet,
            'consultas' => $consultas
        ]);
    }
}
