<?php
/** @var array $tutores */
/** @var string $search */
/** @var string $nonce_save */
/** @var string $nonce_delete */
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Clientes (Tutores)</h2>
        <p style="color: var(--text-muted);">Gerenciamento de proprietários de animais.</p>
    </div>
    <button class="btn-primary" onclick="openTutorModal()">
        <i data-lucide="plus" class="icon-lucide"></i> Novo Cliente
    </button>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Nome / Localização</th>
                    <th>CPF / Telefone</th>
                    <th>E-mail</th>
                    <th class="text-right" style="width: 150px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tutores)): ?>
                    <tr><td colspan="4" class="text-center p-5 text-muted">Nenhum cliente cadastrado. Clique em "Novo Cliente" para começar.</td></tr>
                <?php else: ?>
                    <?php foreach ($tutores as $t): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 15px;"><?php echo htmlspecialchars($t['nome']); ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($t['city'] ?? 'Endereço não informado'); ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 13px; color: var(--primary);">
                                    <?php echo htmlspecialchars($t['cpf'] ?: '-'); ?>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($t['telefone'] ?: '-'); ?>
                                </div>
                            </td>
                            <td class="text-muted"><?php echo htmlspecialchars($t['email'] ?: '-'); ?></td>
                            <td class="text-right">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?php echo SITE_URL; ?>/app/tutores/perfil/<?php echo $t['id']; ?>" class="btn-user-action" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2); display: flex; align-items: center; justify-content: center; text-decoration: none;" title="Ver Detalhes">
                                        <i data-lucide="eye" class="icon-lucide icon-sm"></i>
                                    </a>
                                    <button onclick='openTutorModal(<?php echo json_encode($t); ?>)' class="btn-user-action btn-user-edit" title="Editar">
                                        <i data-lucide="edit-3" class="icon-lucide icon-sm"></i>
                                    </button>
                                    <button onclick="deleteTutor(<?php echo $t['id']; ?>)" class="btn-user-action btn-user-delete" title="Excluir">
                                        <i data-lucide="trash-2" class="icon-lucide icon-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function viewTutorDetails(id) {
    const res = await fetch('<?php echo SITE_URL; ?>/api/tutores/details?id=' + id).then(r => r.json());
    if (!res || !res.success) {
        UI.showToast('Erro ao carregar detalhes', 'error');
        return;
    }

    const { tutor, pets } = res;
    
    const html = `
        <div class="modal-body-scroll">
            <div class="tutor-header mb-4 d-flex align-items-center gap-3">
                <div style="width: 60px; height: 60px; background: rgba(var(--primary-rgb), 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <i data-lucide="user" class="icon-lucide" style="width: 30px; height: 30px;"></i>
                </div>
                <div>
                    <h3 class="mb-0" style="color: var(--primary);">${tutor.nome}</h3>
                    <span class="text-muted small">ID Cliente: #${tutor.id}</span>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="text-muted small text-uppercase fw-bold">CPF</label>
                    <div class="fw-600" style="font-weight:600;">${tutor.cpf || 'Não informado'}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Telefone</label>
                    <div class="fw-600" style="font-weight:600;">${tutor.telefone || 'Não informado'}</div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="text-muted small text-uppercase fw-bold">Endereço Completo</label>
                    <div class="fw-600" style="font-weight:600;">
                        ${tutor.street ? `${tutor.street}, nº ${tutor.address_number || 'S/N'}` : 'Não informado'} <br>
                        ${tutor.neighborhood ? `${tutor.neighborhood} - ` : ''} ${tutor.city || ''} / ${tutor.state || ''} <br>
                        ${tutor.zip_code ? `CEP: ${tutor.zip_code}` : ''}
                    </div>
                </div>
            </div>

            <div class="pets-section pt-3" style="border-top: 1px solid var(--border);">
                <h4 class="mb-3 d-flex align-items-center gap-2">
                    <i data-lucide="dog" class="icon-lucide icon-sm"></i> Pets Associados
                </h4>
                
                ${pets.length === 0 ? `
                    <div class="p-3 text-center text-muted small bg-light rounded-3" style="background: rgba(255,255,255,0.02) !important;">
                        Nenhum pet vinculado a este cliente.
                    </div>
                ` : `
                    <div class="d-flex flex-column gap-2">
                        ${pets.map(p => `
                            <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.1);">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 40px; height: 40px; background: rgba(var(--primary-rgb), 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                        <i data-lucide="dog" class="icon-lucide icon-sm"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 14px; font-weight:700;">${p.nome}</div>
                                        <div class="text-muted small">${p.especie || ''} | ${p.raca || 'Sem raça'}</div>
                                    </div>
                                </div>
                                <a href="<?php echo SITE_URL; ?>/app/pets/perfil/${p.id}" class="btn-primary py-1 px-3" style="font-size: 11px; border-radius:6px; text-decoration:none;">Ver Perfil</a>
                            </div>
                        `).join('')}
                    </div>
                `}
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="UI.closeModal()">Fechar</button>
            <button class="btn-primary" onclick='UI.closeModal(); openTutorModal(${JSON.stringify(tutor)})'>Editar Cliente</button>
        </div>
    `;

    UI.showModal('Ficha do Cliente', html);
    lucide.createIcons();
}

function openTutorModal(data = null) {
    const isEdit = data !== null;
    const html = `
        <form class="ajax-form" id="form-tutor" action="<?php echo SITE_URL; ?>/api/tutores/save">
            <div class="modal-body-scroll">
                <input type="hidden" name="id" value="${isEdit ? data.id : ''}">
                <input type="hidden" name="nonce" value="<?php echo $nonce_save; ?>">
                
                <h6 class="mb-3 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="user" class="icon-lucide icon-xs"></i> Identificação do Cliente
                </h6>

                <div class="form-group mb-3">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" value="${isEdit ? data.nome : ''}" required placeholder="Nome do proprietário" oninput="suggestUsername()">
                </div>

                <div class="form-grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">CPF/CNPJ</label>
                        <input type="text" name="cpf" class="form-control mask-document" value="${isEdit ? (data.cpf || '') : ''}" placeholder="000.000.000-00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="${isEdit ? (data.email || '') : ''}" placeholder="email@exemplo.com" oninput="suggestUsername()">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">WhatsApp *</label>
                    <input type="text" name="telefone" class="form-control mask-phone" value="${isEdit ? (data.telefone || '') : ''}" required placeholder="(00) 00000-0000">
                </div>

                <h6 class="mb-3 mt-4 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="key" class="icon-lucide icon-xs"></i> Acesso ao Portal do Cliente (Tutor)
                </h6>

                <div class="form-grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">Nome de Usuário (Login)</label>
                        <input type="text" name="username" id="tutor-username" class="form-control" value="${isEdit ? (data.username || '') : ''}" required placeholder="Ex: joaosilva" onfocus="if(!this.value) suggestUsername()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Senha de Acesso</label>
                        <input type="password" name="password" class="form-control" placeholder="${isEdit ? 'Deixe em branco p/ manter' : 'Senha inicial'}" ${isEdit ? '' : 'required'}>
                    </div>
                </div>

                <div class="form-group mb-3 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="send_email" id="send_email" value="1" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
                        <label for="send_email" class="form-label mb-0" style="cursor: pointer; font-size: 13px;">Enviar credenciais de acesso para o e-mail do cliente</label>
                    </div>
                </div>

                <h6 class="mb-3 mt-4 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="map-pin" class="icon-lucide icon-xs"></i> Localização / Endereço
                </h6>

                <div class="form-grid-3 mb-3">
                    <div class="form-group">
                        <label class="form-label">CEP</label>
                        <input type="text" name="zip_code" class="form-control mask-zip" value="${isEdit ? (data.zip_code || '') : ''}" onblur="UI.lookupZip(this.value, 'tutor-city', 'tutor-state', 'tutor-street', 'tutor-neighborhood')">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Rua / Avenida</label>
                        <input type="text" name="street" id="tutor-street" class="form-control" value="${isEdit ? (data.street || '') : ''}">
                    </div>
                </div>

                <div class="form-grid-3 mb-3">
                    <div class="form-group">
                        <label class="form-label">Número</label>
                        <input type="text" name="address_number" class="form-control" value="${isEdit ? (data.address_number || '') : ''}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" id="tutor-neighborhood" class="form-control" value="${isEdit ? (data.neighborhood || '') : ''}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cidade / UF</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="city" id="tutor-city" class="form-control" value="${isEdit ? (data.city || '') : ''}" placeholder="Cidade">
                            <input type="text" name="state" id="tutor-state" class="form-control text-center" value="${isEdit ? (data.state || '') : ''}" maxlength="2" style="width: 60px;" placeholder="UF">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer mt-4">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">${isEdit ? 'Atualizar Cliente' : 'Cadastrar Cliente'}</button>
            </div>
        </form>
    `;

    UI.showModal(isEdit ? 'Editar Cliente' : 'Novo Cliente', html);
}

function suggestUsername() {
    const nome = document.querySelector('input[name="nome"]').value;
    const email = document.querySelector('input[name="email"]').value;
    const usernameInput = document.getElementById('tutor-username');
    
    // Se o usuário já começou a digitar manualmente no campo username, não sobrescrevemos
    if (usernameInput.dataset.manual === 'true') return;

    let suggestion = '';
    if (email) {
        suggestion = email.split('@')[0].toLowerCase().replace(/[^a-z0-9]/g, '');
    } else if (nome) {
        // Pega o primeiro e segundo nome se existir
        const parts = nome.trim().split(' ');
        suggestion = parts[0].toLowerCase();
        if (parts.length > 1) suggestion += parts[1].charAt(0).toLowerCase();
        suggestion = suggestion.replace(/[^a-z0-9]/g, '');
    }
    
    usernameInput.value = suggestion;
}

// Marcar como manual se o usuário editar o campo
document.addEventListener('input', (e) => {
    if (e.target.id === 'tutor-username') {
        e.target.dataset.manual = 'true';
    }
});
</script>
