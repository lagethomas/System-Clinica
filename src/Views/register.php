<?php
/** @var array $plans */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crie sua Conta SaaS | System Clinica</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        body { background: #f4f6f9; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .register-card { max-width: 500px; width: 100%; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .register-card h2 { font-weight: 700; margin-bottom: 20px; color: #333; }
        .form-control { border-radius: 8px; padding: 12px 15px; }
        .btn-primary { border-radius: 8px; padding: 12px; font-weight: 600; font-size: 16px; background: #007bff; border: none; }
        .btn-primary:hover { background: #0056b3; }
        .input-group-text { background: transparent; border-right: none; color: #888; }
        .form-control.slug-input { border-left: none; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <h2><i data-lucide="user-round" class="icon-lucide"></i> System Clinica</h2>
            <p class="text-muted">Digitalize sua clínica em minutos.</p>
        </div>
        
        <form id="register-form" method="POST" action="/register">
            <div class="mb-3">
                <label class="form-label">Nome da Clínica</label>
                <input type="text" name="restaurant_name" id="restaurant_name" class="form-control" placeholder="Sua Clínica ou Consultório" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Link (Slug)</label>
                <div class="input-group">
                    <span class="input-group-text">seusite.com/</span>
                    <input type="text" name="slug" id="slug" class="form-control slug-input" placeholder="sua-clinica" required>
                </div>
                <small class="text-muted">O endereço onde seus clientes acessarão seu sistema.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Seu Nome (Proprietário)</label>
                <input type="text" name="owner_name" class="form-control" placeholder="João Silva" required>
            </div>

            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="joao@email.com" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Escolha um Plano</label>
                <select name="plan_id" class="form-select" required>
                    <option value="" disabled selected>Selecione seu pacote</option>
                    <?php foreach($plans as $p): ?>
                        <option value="<?php echo $p['id']; ?>">
                            <?php echo htmlspecialchars($p['name']); ?> - R$ <?php echo number_format((float)$p['base_price'], 2, ',', '.'); ?>/mês
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted d-block mt-1">Todos os planos incluem período de teste gratuito.</small>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="btn-submit"><i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> Criar Minha Conta</button>
            <div class="text-center mt-3">
                <a href="/login" class="text-decoration-none text-muted">Já tem uma conta? Entrar</a>
            </div>
            <div id="register-message" class="mt-3 text-center" style="display: none;"></div>
        </form>
    </div>

    <script>
        const nameInput = document.getElementById('restaurant_name');
        const slugInput = document.getElementById('slug');

        nameInput.addEventListener('input', function() {
            let slug = this.value.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        });

        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit');
            const msgBox = document.getElementById('register-message');
            
            btn.innerHTML = '<i data-lucide="loader" class="icon-lucide"></i> Criando...';
            btn.disabled = true;
            msgBox.style.display = 'none';

            try {
                const formData = new FormData(this);
                const response = await fetch('/register', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                msgBox.style.display = 'block';
                msgBox.innerHTML = result.message;
                
                if (result.success) {
                    msgBox.className = 'mt-3 text-center text-success fw-bold';
                    setTimeout(() => window.location.href = '/login', 2000);
                } else {
                    msgBox.className = 'mt-3 text-center text-danger';
                    btn.innerHTML = 'Criar Minha Conta';
                    btn.disabled = false;
                }
            } catch (error) {
                msgBox.style.display = 'block';
                msgBox.className = 'mt-3 text-center text-danger';
                msgBox.innerHTML = 'Erro de conexão. Tente novamente.';
                btn.innerHTML = 'Criar Minha Conta';
                btn.disabled = false;
            }
        });
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
