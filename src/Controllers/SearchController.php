<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;

class SearchController extends Controller {

    public function search(): void {
        Auth::requireLogin();
        $query = trim($_GET['q'] ?? '');
        $results = [];

        if (strlen($query) < 2) {
            $this->jsonResponse(['results' => []]);
            return;
        }

        try {
            $term = "%$query%";
            $company_id = Auth::companyId();
            $isAdmin = Auth::isAdmin();

            // 1. Search Pets (Only if company_id is present)
            if ($company_id) {
                try {
                    $pets = Database::fetchAll("
                        SELECT id, nome as name, raca as sub, 'pet' as type 
                        FROM cp_pets 
                        WHERE company_id = :cid AND (nome LIKE :q1 OR raca LIKE :q2 OR microchip LIKE :q3)
                        LIMIT 5
                    ", ['cid' => $company_id, 'q1' => $term, 'q2' => $term, 'q3' => $term]);
                    
                    foreach ($pets as $p) {
                        $p['url'] = SITE_URL . "/app/pets/perfil/" . $p['id'];
                        $results[] = $p;
                    }
                } catch (\Exception $e) {
                    // Fallback search if microchip doesn't exist or other SQL error
                    $pets = Database::fetchAll("
                        SELECT id, nome as name, raca as sub, 'pet' as type 
                        FROM cp_pets 
                        WHERE company_id = :cid AND (nome LIKE :q1 OR raca LIKE :q2)
                        LIMIT 5
                    ", ['cid' => $company_id, 'q1' => $term, 'q2' => $term]);
                    foreach ($pets as $p) {
                        $p['url'] = SITE_URL . "/app/pets/perfil/" . $p['id'];
                        $results[] = $p;
                    }
                }

                // 2. Search Tutores
                try {
                    $tutores = Database::fetchAll("
                        SELECT id, nome as name, email as sub, 'tutor' as type 
                        FROM cp_tutores 
                        WHERE company_id = :cid AND (nome LIKE :q1 OR email LIKE :q2 OR cpf LIKE :q3)
                        LIMIT 5
                    ", ['cid' => $company_id, 'q1' => $term, 'q2' => $term, 'q3' => $term]);

                    foreach ($tutores as $t) {
                        $t['url'] = SITE_URL . "/app/tutores/perfil/" . $t['id'];
                        $results[] = $t;
                    }
                } catch (\Exception $e) {
                    $tutores = Database::fetchAll("
                        SELECT id, nome as name, email as sub, 'tutor' as type 
                        FROM cp_tutores 
                        WHERE company_id = :cid AND (nome LIKE :q1 OR email LIKE :q2)
                        LIMIT 5
                    ", ['cid' => $company_id, 'q1' => $term, 'q2' => $term]);
                    foreach ($tutores as $t) {
                        $t['url'] = SITE_URL . "/app/tutores/perfil/" . $t['id'];
                        $results[] = $t;
                    }
                }
            }

            // 3. Search Companies (Admin Only)
            if ($isAdmin) {
                $companies = Database::fetchAll("
                    SELECT id, name, slug as sub, 'company' as type 
                    FROM cp_companies 
                    WHERE (name LIKE :q1 OR slug LIKE :q2) 
                    AND trashed_at IS NULL
                    LIMIT 5
                ", ['q1' => $term, 'q2' => $term]);

                foreach ($companies as $c) {
                    $c['url'] = SITE_URL . "/admin/companies/details?id=" . $c['id'];
                    $results[] = $c;
                }

                // 4. Search Users
                $users = Database::fetchAll("
                    SELECT id, name, email as sub, 'user' as type 
                    FROM cp_users 
                    WHERE (name LIKE :q1 OR email LIKE :q2)
                    LIMIT 5
                ", ['q1' => $term, 'q2' => $term]);

                foreach ($users as $u) {
                    $u['url'] = SITE_URL . "/users";
                    $results[] = $u;
                }
            }

            $this->jsonResponse(['results' => $results]);
        } catch (\Exception $e) {
            $this->jsonResponse(['results' => [], 'message' => 'Search error', 'debug' => $e->getMessage()], 500);
        }
    }
}
