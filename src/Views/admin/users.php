<?php
/** @var array $all_users */
?>


<div class="users-header">
    <h2>Gerenciamento de Usuários</h2>
    <p>Controle quem tem acesso ao sistema e seus níveis de permissão.</p>
</div>

<div class="user-list-card">
    <div class="settings-tab-nav mb-4">
        <button class="nav-link-tab active" onclick="filterByRole('all', this)" style="background:transparent; border-top:none; border-left:none; border-right:none; font-family:inherit; cursor:pointer;">
            <i data-lucide="users" class="icon-lucide"></i> Todos
        </button>
        <button class="nav-link-tab" onclick="filterByRole('proprietario', this)" style="background:transparent; border-top:none; border-left:none; border-right:none; font-family:inherit; cursor:pointer;">
            <i data-lucide="building" class="icon-lucide"></i> Proprietários
        </button>
        <?php if (Auth::isAdmin()): ?>
        <button class="nav-link-tab" onclick="filterByRole('administrador', this)" style="background:transparent; border-top:none; border-left:none; border-right:none; font-family:inherit; cursor:pointer;">
            <i data-lucide="shield-check" class="icon-lucide"></i> Administradores
        </button>
        <?php endif; ?>
    </div>

    <div class="user-list-header mt-4">
        <h3 id="current-tab-title">Lista de Usuários</h3>
        <button class="btn-primary" onclick="openUserModal()">
            <i data-lucide="user-plus" class="icon-lucide"></i> Novo Usuário
        </button>
    </div>

    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Empresa</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <?php foreach ($all_users as $user): ?>
                <tr class="user-row" data-role="<?php echo strtolower($user['role'] ?? 'usuario'); ?>">
                    <td>
                        <div class="user-cell" style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar" style="width: 35px; height: 35px; font-size: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border-radius: 50%; font-weight: 700;">
                                <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div class="user-info" style="display: flex; flex-direction: column; line-height: 1.2;">
                                <span class="user-name-cell" style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
                                <span class="user-sub" style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($user['username'] ?? ''); ?></span>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                    <td><span class="badge status-secondary"><?php echo ucfirst($user['role'] ?? ''); ?></span></td>
                    <td>
                        <?php if (!empty($user['company_id'])): ?>
                            <span class="badge status-active"><?php echo htmlspecialchars($user['company_name'] ?? 'Empresa #'.$user['company_id']); ?></span>
                        <?php else: ?>
                            <span class="badge status-secondary">Master Admin</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn-user-action" onclick="sendUserCredentials(<?php echo $user['id']; ?>)" title="Enviar Dados de Acesso"><i data-lucide="send" class="icon-lucide"></i></button>
                        <button class="btn-user-action" onclick="openUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="Editar Usuário"><i data-lucide="edit" class="icon-lucide"></i></button>
                        <?php if (strtolower($user['role'] ?? '') !== 'administrador'): ?>
                            <button class="btn-user-action danger" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Excluir Usuário"><i data-lucide="trash" class="icon-lucide"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterByRole(role, btn) {
    // UI tabs update
    document.querySelectorAll('.nav-link-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Title update
    const titles = {
        'all': 'Lista de Usuários',
        'proprietario': 'Proprietários de Empresa',
        'administrador': 'Administradores Globais'
    };
    document.getElementById('current-tab-title').innerText = titles[role] || 'Usuários';

    // Rows filtering
    const rows = document.querySelectorAll('.user-row');
    rows.forEach(row => {
        if (role === 'all' || row.dataset.role === role) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

const USER_NONCES = <?php echo json_encode($nonces); ?>;
const IS_ADMIN = <?php echo Auth::isAdmin() ? 'true' : 'false'; ?>;

function openUserModal(data = null) {
    const html = `
        <form action="<?php echo SITE_URL; ?>/api/admin/users/save" class="ajax-form">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            <input type="hidden" name="nonce" value="${USER_NONCES.save}">
            
            <div class="form-group mb-3">
                <label class="form-label">Nome Completo</label>
                <input type="text" name="name" id="user_name" class="form-control w-100" value="${data ? data.name : ''}" required onkeyup="${!data ? 'suggestUsername(this.value)' : ''}">
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="user_username" class="form-control w-100" value="${data ? data.username : ''}" ${data ? 'readonly' : 'required'}>
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" id="user_email" class="form-control w-100" value="${data ? data.email : ''}" required>
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="phone" class="form-control mask-phone w-100" value="${data ? data.phone || '' : ''}" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Papel</label>
                    <select name="role" id="user_role" class="form-control w-100" onchange="toggleCompanyField()" required>
                        <option value="proprietario" ${data && data.role === 'proprietario' ? 'selected' : (!data ? 'selected' : '')}>Proprietário (Empresa)</option>
                        ${IS_ADMIN ? `
                        <option value="administrador" ${data && data.role === 'administrador' ? 'selected' : ''}>Administrador Global</option>
                        ` : ''}
                    </select>
                </div>
            </div>

            <div id="company_field" class="form-group mb-3" style="display:none;">
                <label class="form-label">Empresa</label>
                <select name="company_id" id="user_company_id" class="form-control w-100">
                    <option value="">-- Selecione a Empresa --</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?php echo $comp['id']; ?>"><?php echo htmlspecialchars($comp['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Senha ${data ? '(opcional)' : ''}</label>
                <div class="password-toggle-wrapper relative">
                    <input type="password" name="password" id="modal-password" class="form-control w-100 pr-80" ${data ? '' : 'required'}>
                    <button type="button" onclick="UI.generatePassword ? UI.generatePassword('modal-password') : null" class="btn-generate-password" title="Gerar Senha">
                        <i data-lucide="shuffle" class="icon-lucide"></i>
                    </button>
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">CEP</label>
                    <input type="text" name="zip_code" class="form-control mask-zip w-100" value="${data ? data.zip_code || '' : ''}" onblur="UI.lookupZip(this.value, 'user-city', 'user-state', 'user-street', 'user-neighborhood')">
                </div>
                <div class="form-group">
                    <label class="form-label">Rua / Logradouro</label>
                    <input type="text" name="street" id="user-street" class="form-control w-100" value="${data ? data.street || '' : ''}">
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Bairro</label>
                    <input type="text" name="neighborhood" id="user-neighborhood" class="form-control w-100" value="${data ? data.neighborhood || '' : ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="city" id="user-city" class="form-control w-100" value="${data ? data.city || '' : ''}">
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">UF</label>
                    <input type="text" name="state" id="user-state" class="form-control w-100" value="${data ? data.state || '' : ''}" maxlength="2">
                </div>
                <div class="form-group">
                    <label class="form-label">Número</label>
                    <input type="text" name="address_number" class="form-control w-100" value="${data ? data.address_number || '' : ''}">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Criar Usuário'}
                </button>
            </div>
        </form>
    `;
    UI.showModal(data ? 'Editar Usuário' : 'Novo Usuário', html);
    
    // Initialize masks after injection
    UI.initMasks();
    // Set company field visibility after modal content is loaded
    if (data) {
        document.getElementById('user_company_id').value = data.company_id || '';
    }
    toggleCompanyField();
}

function toggleCompanyField() {
    const role = document.getElementById('user_role').value;
    const companyField = document.getElementById('company_field');
    if (role === 'proprietario') {
        companyField.style.display = 'block';
    } else {
        companyField.style.display = 'none';
    }
}

function suggestUsername(name) {
    const input = document.getElementById('user_username');
    if (!input || input.readOnly) return;
    input.value = name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, '.').replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
}

async function deleteUser(id) {
    if (await UI.confirm('Deseja realmente remover este usuário?')) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/admin/users/delete', { id, nonce: USER_NONCES.delete });
        if (res && res.success) {
            UI.showToast(res.message || 'Usuário removido', 'success');
            setTimeout(() => window.location.reload(), 1500);
        }
    }
}

async function sendUserCredentials(id) {
    if (await UI.confirm('Deseja gerar uma nova senha e enviar os dados de acesso para este usuário por e-mail?', {
        title: 'Enviar Acesso',
        confirmText: 'Sim, Gerar e Enviar',
        type: 'primary',
        icon: 'send'
    })) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/admin/users/send_credentials', { id });
        if (res && res.success) {
            UI.showToast(res.message || 'Dados enviados', 'success');
        } else {
            UI.showToast(res.message || 'Falha ao enviar', 'error');
        }
    }
}
</script>


