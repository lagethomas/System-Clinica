<?php
/** @var array $pets */
/** @var array $tutores */
/** @var string $search */
/** @var string $nonce_save */
/** @var string $nonce_delete */
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Pacientes (Pets)</h2>
        <p style="color: var(--text-muted);">Controle clínico e cadastral de animais.</p>
    </div>
    <button class="btn-primary" onclick="openPetModal()">
        <i data-lucide="plus" class="icon-lucide"></i> Novo Pet
    </button>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Nome do Paciente</th>
                    <th>Tutor Responsável</th>
                    <th>Raça / Espécie</th>
                    <th>Carteirinha</th>
                    <th class="text-right" style="width: 150px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pets)): ?>
                    <tr><td colspan="5" class="text-center p-5 text-muted">Nenhum pet cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($pets as $p): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="pet-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: rgba(var(--primary-rgb), 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                        <?php if (!empty($p['foto_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($p['foto_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <i data-lucide="dog" class="icon-lucide icon-sm"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 15px;"><?php echo htmlspecialchars($p['nome']); ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($p['especie'] ?: 'Espécie não inf.'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">
                                    <?php echo htmlspecialchars($p['tutor_nome']); ?>
                                </div>
                            </td>
                            <td class="text-muted">
                                <div style="font-size: 13px;"><?php echo htmlspecialchars($p['raca'] ?: 'Sem Raça'); ?></div>
                            </td>
                            <td>
                                <?php if (!empty($p['numero_carteirinha'])): ?>
                                    <span class="badge" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); font-weight: 700;">#<?php echo htmlspecialchars($p['numero_carteirinha']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?php echo SITE_URL; ?>/app/pets/perfil/<?php echo $p['id']; ?>" class="btn-user-action" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary); border: 1px solid rgba(var(--primary-rgb), 0.2);" title="Ver Perfil">
                                        <i data-lucide="eye" class="icon-lucide icon-sm"></i>
                                    </a>
                                    <button onclick='openPetModal(<?php echo json_encode($p); ?>)' class="btn-user-action btn-user-edit" title="Editar">
                                        <i data-lucide="edit-3" class="icon-lucide icon-sm"></i>
                                    </button>
                                    <button onclick="deletePet(<?php echo $p['id']; ?>)" class="btn-user-action btn-user-delete" title="Excluir">
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
    <?php echo \App\Core\Pagination::render($pagination['page'], $pagination['totalPages'], SITE_URL . '/app/pets'); ?>
</div>

<script>
function openPetModal(data = null) {
    const isEdit = data !== null;
    const tutores = <?php echo json_encode($tutores); ?>;
    
    const html = `
        <form class="ajax-form" id="form-pet" action="<?php echo SITE_URL; ?>/api/pets/save" enctype="multipart/form-data">
            <div class="modal-body-scroll">
                <input type="hidden" name="id" value="${isEdit ? data.id : ''}">
                <input type="hidden" name="nonce" value="<?php echo $nonce_save; ?>">
                
                <div class="form-group mb-4">
                    <label class="form-label">Foto do Paciente</label>
                    <div class="modern-upload" style="border: 1px dashed var(--border); border-radius: 12px; padding: 25px; text-align: center; cursor: pointer; background: rgba(var(--primary-rgb), 0.02); position: relative;">
                        <label for="pet-foto" style="cursor: pointer; display: block; margin: 0;">
                            <div id="photo-preview-container" class="mb-2" style="${isEdit && data.foto_url ? '' : 'display: none;'}">
                                <img id="photo-preview" src="${isEdit && data.foto_url ? '<?php echo SITE_URL; ?>' + data.foto_url : ''}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 20px; border: 2px solid var(--primary); box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                            </div>
                            <i data-lucide="camera" class="icon-lucide mb-2 text-primary" style="width: 32px; height: 32px; ${isEdit && data.foto_url ? 'display: none;' : ''}"></i>
                            <div class="small fw-800 photo-upload-label text-main-color mt-2">${isEdit && data.foto_url ? 'Alterar foto do pet' : 'Clique para enviar foto...'}</div>
                        </label>
                        <input type="file" id="pet-foto" name="foto" accept="image/*" onchange="handlePhotoChange(this)" style="display: none;">
                    </div>
                </div>

                <h6 class="mb-3 d-flex align-items-center gap-2 label-caps-header">
                    <i data-lucide="user" class="icon-lucide icon-xs"></i> Responsável e Nome
                </h6>

                <div class="form-group mb-3">
                    <label class="form-label">Tutor Responsável *</label>
                    <div class="search-input-box">
                        <i data-lucide="user" class="icon-lucide icon-sm" style="color: var(--primary);"></i>
                        <input type="text" id="tutor-search" class="form-control" placeholder="Procure pelo nome do tutor..." value="${isEdit ? data.tutor_nome : ''}" required autocomplete="off">
                        <input type="hidden" id="tutor_id" name="tutor_id" value="${isEdit ? data.tutor_id : ''}">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Nome do Paciente *</label>
                    <div class="input-with-icon">
                        <i data-lucide="dog" class="icon-lucide"></i>
                        <input type="text" name="nome" class="form-control" value="${isEdit ? data.nome : ''}" required placeholder="Ex: Thor, Mel...">
                    </div>
                </div>

                <div class="form-grid-2 mb-4">
                    <div class="form-group">
                        <label class="form-label">Identificador / Chip</label>
                        <input type="text" name="numero_carteirinha" class="form-control" value="${isEdit ? (data.numero_carteirinha || '') : ''}" placeholder="Opcional">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Espécie</label>
                        <select name="especie" class="form-control">
                            <option value="Cachorro" ${isEdit && data.especie == 'Cachorro' ? 'selected' : ''}>Cachorro</option>
                            <option value="Gato" ${isEdit && data.especie == 'Gato' ? 'selected' : ''}>Gato</option>
                            <option value="Peixe" ${isEdit && data.especie == 'Peixe' ? 'selected' : ''}>Peixe</option>
                            <option value="Ave" ${isEdit && data.especie == 'Ave' ? 'selected' : ''}>Ave</option>
                            <option value="Outros" ${isEdit && data.especie == 'Outros' ? 'selected' : ''}>Outros</option>
                        </select>
                    </div>
                </div>

                <h6 class="mb-3 mt-4 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="activity" class="icon-lucide icon-xs"></i> Características Clínicas
                </h6>
                
                <div class="form-grid-2 mb-4">
                    <div class="form-group">
                        <label class="form-label">Raça</label>
                        <input type="text" name="raca" class="form-control" value="${isEdit ? (data.raca || '') : ''}" placeholder="Ex: Poodle, Persa...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sexo</label>
                        <select name="sexo" class="form-control">
                            <option value="M" ${isEdit && data.sexo == 'M' ? 'selected' : ''}>Macho</option>
                            <option value="F" ${isEdit && data.sexo == 'F' ? 'selected' : ''}>Fêmea</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-3 mb-3">
                    <div class="form-group">
                        <label class="form-label">Peso (kg)</label>
                        <input type="text" name="peso" class="form-control mask-weight" value="${isEdit ? (data.peso || '') : ''}" placeholder="0.0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nascimento</label>
                        <input type="date" name="nascimento" class="form-control" value="${isEdit ? (data.data_nascimento || '') : ''}" onchange="updateAge(this.value, this.closest('.form-grid-3').querySelector('[name=idade]'))">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Idade</label>
                        <input type="text" name="idade" class="form-control" value="${isEdit ? (data.idade || '') : ''}" placeholder="Calculado/Manual">
                    </div>
                </div>

                <div class="form-grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">Cor / Pelagem</label>
                        <input type="text" name="cor" class="form-control" value="${isEdit ? (data.cor || '') : ''}" placeholder="Ex: Branco e Preto">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nº Microchip</label>
                        <input type="text" name="microchip" class="form-control" value="${isEdit ? (data.microchip || '') : ''}" placeholder="Chip UID">
                    </div>
                </div>

            </div>

            <div class="modal-footer mt-4">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">${isEdit ? 'Atualizar Pet' : 'Cadastrar Pet'}</button>
            </div>
        </form>
    `;

    UI.showModal(isEdit ? 'Editar Dados do Pet' : 'Novo Cadastro de Pet', html);
    lucide.createIcons();
    if (UI.initMasks) UI.initMasks(document.getElementById('form-pet'));

    // ── Tutor Autocomplete (fixed-position dropdown, escapes modal overflow) ──
    setupFloatingAutocomplete({
        inputId: 'tutor-search',
        hiddenId: 'tutor_id',
        data: tutores,
        searchKey: 'nome',
        displayKey: 'nome',
        subKey: null,
        icon: 'user'
    });
}

function updateAge(dateString, targetInput) {
    if (!dateString) return;
    const birthDate = new Date(dateString + 'T00:00:00');
    const today = new Date();
    let years = today.getFullYear() - birthDate.getFullYear();
    let months = today.getMonth() - birthDate.getMonth();
    let days = today.getDate() - birthDate.getDate();

    if (days < 0) {
        months--;
        const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
        days += lastMonth.getDate();
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    let ageText = "";
    if (years > 0) {
        ageText = years + (years === 1 ? " ano" : " anos");
        if (months > 0) ageText += " e " + months + (months === 1 ? " mês" : " meses");
    } else if (months > 0) {
        ageText = months + (months === 1 ? " mês" : " meses");
        if (days > 0) ageText += " e " + days + (days === 1 ? " dia" : " dias");
    } else {
        ageText = days + (days === 1 ? " dia" : " dias");
    }
    targetInput.value = ageText;
}

/**
 * Handle Photo Preview
 */
window.handlePhotoChange = function(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const container = document.getElementById('photo-preview-container');
        const preview = document.getElementById('photo-preview');
        const label = container.parentElement.querySelector('.photo-upload-label');
        const icon = container.parentElement.querySelector('i[data-lucide="camera"]');

        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
            if (icon) icon.style.display = 'none';
            label.innerText = 'Foto selecionada para envio';
        };
        reader.readAsDataURL(input.files[0]);
    }
};

async function deletePet(id) {
    if (await UI.confirm('Remover este pet? O histórico de consultas não poderá ser recuperado.')) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/pets/delete', { id, nonce: '<?php echo $nonce_delete; ?>' });
        if (res && res.success) window.location.reload();
    }
}
</script>

