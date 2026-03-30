<?php
/** @var array $pet */
/** @var array $consultas */
?>

<div class="page-header d-flex justify-content-between align-items-center mb-5">
    <div class="d-flex align-items-center gap-3">
        <a href="<?php echo SITE_URL; ?>/app/pets" class="btn-secondary back-button-circular">
            <i data-lucide="arrow-left" class="icon-lucide"></i>
        </a>
        <div>
            <h1 class="page-main-title">Ficha do Paciente</h1>
            <p class="text-muted small mb-0">Gestão completa do histórico clínico e dados do pet.</p>
        </div>
    </div>
    <div class="d-flex gap-3">
        <button class="btn-secondary px-4 py-2 rounded-12 fw-700 d-flex align-items-center gap-2" onclick="editPet(<?php echo htmlspecialchars(json_encode($pet)); ?>)">
            <i data-lucide="edit-3" class="icon-lucide icon-sm"></i> Editar Dados
        </button>
        <button class="btn-primary px-4 py-2 shadow-primary rounded-12 fw-800 d-flex align-items-center gap-2" onclick="openConsultaModal(<?php echo $pet['id']; ?>)">
            <i data-lucide="plus-circle" class="icon-lucide icon-sm"></i> Novo Registro
        </button>
    </div>
</div>

<div class="profile-main-grid">
    <!-- LEFT COLUMN: PHOTO & STATUS -->
    <div class="tutor-sidebar">
        <div class="profile-sidebar-card">
            <div class="pet-photo-container image-header-320">
                <?php if (!empty($pet['foto_url'])): ?>
                    <img src="<?php echo htmlspecialchars($pet['foto_url']); ?>" alt="Pet Photo" class="img-fluid-cover">
                <?php else: ?>
                    <div class="placeholder-avatar-gradient">
                        <i data-lucide="dog" class="placeholder-icon-large"></i>
                        <span class="placeholder-text-muted">Sem Foto</span>
                    </div>
                <?php endif; ?>
                
                <div class="badge-overlay">
                    <span class="status-badge-premium" style="background: <?php echo ($pet['plano_status'] == 'ativo') ? '#10b981' : '#ef4444'; ?>;">
                        <?php echo strtoupper($pet['plano_status'] ?? 'N/A'); ?>
                    </span>
                </div>
            </div>
            
            <div class="p-4">
                <div class="mini-info-card-highlight">
                    <label class="label-tiny-caps">Responsável (Tutor)</label>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fw-800 text-main-color"><?php echo htmlspecialchars($pet['tutor_nome']); ?></span>
                        <a href="<?php echo SITE_URL; ?>/app/tutores/perfil/<?php echo $pet['tutor_id']; ?>" class="text-primary small fw-700 action-link-icon" title="Ver Perfil"><i data-lucide="external-link" class="icon-lucide icon-xs"></i></a>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 border-top-dashed">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">ID do Paciente:</span>
                        <span class="fw-700 small">#<?php echo $pet['id']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Plano:</span>
                        <span class="fw-700 small"><?php echo htmlspecialchars($pet['numero_carteirinha'] ?: 'Particular'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: MAIN INFO -->
    <div class="pet-main-info">
        <div class="profile-main-card">
            <div class="mb-5">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <h1 class="giant-title"><?php echo htmlspecialchars($pet['nome']); ?></h1>
                    <i data-lucide="<?php echo ($pet['sexo'] == 'M') ? 'mars' : 'venus'; ?>" class="icon-lucide gender-icon-large <?php echo ($pet['sexo'] == 'M') ? 'male' : 'female'; ?>"></i>
                </div>
                <p class="subtitle-highlight text-muted">
                    <?php echo htmlspecialchars($pet['especie']); ?> 
                    <span class="mx-2 text-border">|</span> 
                    <?php echo htmlspecialchars($pet['raca'] ?: 'Sem Raça Definida'); ?>
                </p>
            </div>

            <div class="info-blocks-grid-2">
                <div class="info-block">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle-box">
                            <i data-lucide="scale" class="icon-lucide icon-sm"></i>
                        </div>
                        <div>
                            <label class="label-tiny-caps">Peso Registrado</label>
                            <div class="fw-800 value-display-18">
                                <?php echo $pet['peso'] ? number_format((float)$pet['peso'], 1, ',', '.') . ' kg' : 'Não Inf.'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-block">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle-box">
                            <i data-lucide="calendar" class="icon-lucide icon-sm"></i>
                        </div>
                        <div>
                            <label class="label-tiny-caps">Idade Aproximada</label>
                            <div class="fw-800 value-display-18"><?php echo htmlspecialchars($pet['idade'] ?: 'Não Inf.'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="info-block">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle-box">
                            <i data-lucide="info" class="icon-lucide icon-sm"></i>
                        </div>
                        <div>
                            <label class="label-tiny-caps">Microchip / Ident.</label>
                            <div class="fw-800 value-display-18"><?php echo htmlspecialchars($pet['microchip'] ?: 'Ausente'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="info-block">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle-box">
                            <i data-lucide="palette" class="icon-lucide icon-sm"></i>
                        </div>
                        <div>
                            <label class="label-tiny-caps">Pelagem / Cor</label>
                            <div class="fw-800 value-display-18"><?php echo htmlspecialchars($pet['cor'] ?: 'Não Inf.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <div class="next-event-card-premium shadow-sm mt-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box-bell">
                                <i data-lucide="bell" class="icon-lucide text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-850 font-16 text-main-color">Próxima Vacinação</h6>
                                <p class="small text-muted mb-0">Nenhum lembrete configurado para este paciente.</p>
                            </div>
                        </div>
                        <button class="btn-primary py-2 px-4 rounded-12 font-13 fw-800 d-flex align-items-center gap-2" onclick="UI.showToast('Módulo de vacinas em breve', 'info')">
                            Configurar <i data-lucide="settings" class="icon-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CLINICAL RECORDS SECTION -->
<div class="section-full-width">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-4 border-bottom-muted">
        <div>
            <h3 class="giant-heading">
                <i data-lucide="clipboard-list" class="icon-lucide mr-3 text-primary icon-heading"></i> 
                Prontuário Clínico & Histórico
            </h3>
            <p class="text-muted small mt-2 mb-0">Linha do tempo cronológica de todos os atendimentos, diagnósticos e medicações.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn-secondary px-4 py-2 rounded-10 fw-700"><i data-lucide="download" class="icon-lucide icon-xs mr-2"></i> Exportar PDF</button>
        </div>
    </div>

    <div class="clinical-timeline">
        <?php if (empty($consultas)): ?>
            <div class="empty-state-clinical">
                <i data-lucide="stethoscope" class="empty-state-icon"></i>
                <h4 class="mb-2 fw-800 text-main-color">Nenhum Registro</h4>
                <p class="small">Inicie o prontuário deste paciente clicando no botão "Novo Registro".</p>
                <button class="btn-primary mt-3 py-2 px-4" onclick="openConsultaModal(<?php echo $pet['id']; ?>)">Começar Agora</button>
            </div>
        <?php else: ?>
            <div class="timeline-container">
                <div class="timeline-line"></div>
                
                <?php foreach ($consultas as $c): ?>
                    <div class="timeline-item position-relative mb-5 animate-slide-in">
                        <div class="timeline-marker" style="background: <?php echo ($c['status'] == 'concluida') ? '#10b981' : (($c['status'] == 'agendada') ? '#f59e0b' : '#ef4444'); ?>;"></div>
                        
                        <div class="timeline-item-card">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i data-lucide="calendar" class="icon-lucide text-primary icon-tiny"></i>
                                        <span class="small fw-800 text-uppercase tracking-1 text-primary"><?php echo date('d/m/Y \à\s H:i', strtotime($c['data_consulta'])); ?></span>
                                    </div>
                                    <h4 class="m-0 font-18 fw-800 text-main-color"><?php echo htmlspecialchars($c['motivo'] ?: 'Consulta Preventiva'); ?></h4>
                                </div>
                                <div class="badge-status-clinical" style="background: <?php echo ($c['status'] == 'concluida') ? 'rgba(16, 185, 129, 0.1)' : (($c['status'] == 'agendada') ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)'); ?>; color: <?php echo ($c['status'] == 'concluida') ? '#10b981' : (($c['status'] == 'agendada') ? '#f59e0b' : '#ef4444'); ?>;">
                                    <?php echo strtoupper($c['status']); ?>
                                </div>
                            </div>

                            <div class="row g-4">
                                <?php if (!empty($c['diagnostico'])): ?>
                                    <div class="col-md-6">
                                        <div class="p-4 rounded-4 h-100 bg-black-20 border-muted">
                                            <label class="label-tiny-caps text-primary-important mb-3">
                                                <i data-lucide="activity" class="icon-lucide icon-xs mr-1"></i> Evolução Clínica / Sinais
                                            </label>
                                            <div class="text-main font-14 line-height-1.6"><?php echo nl2br(htmlspecialchars($c['diagnostico'])); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($c['prescricao'])): ?>
                                    <div class="col-md-6">
                                        <div class="p-4 rounded-4 h-100 bg-success-ultra-lite border-success-lite">
                                            <label class="label-tiny-caps text-success-important mb-3">
                                                <i data-lucide="pill" class="icon-lucide icon-xs mr-1"></i> Prescrição & Tratamento
                                            </label>
                                            <div class="text-main font-14 line-height-1.6 font-italic"><?php echo nl2br(htmlspecialchars($c['prescricao'])); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($c['observacoes'])): ?>
                                <div class="mt-4 p-3 rounded-3 bg-white-01 border-left-primary">
                                    <p class="small text-muted mb-0"><i data-lucide="info" class="icon-lucide icon-xs mr-1"></i> <?php echo htmlspecialchars($c['observacoes']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Logic remains identical, but I will adjust the UI class usage if needed in future turns
function editPet(data) {
    const html = `
        <form class="ajax-form" id="form-pet-edit" action="<?php echo SITE_URL; ?>/api/pets/save" enctype="multipart/form-data">
            <input type="hidden" name="id" value="${data.id}">
            <input type="hidden" name="tutor_id" value="${data.tutor_id}">
            <input type="hidden" name="nonce" value="<?php echo \Nonce::create('pet_save'); ?>">
            
            <div class="modal-body-scroll">
                <div class="form-group mb-4">
                    <label class="form-label">Foto do Paciente</label>
                    <div class="modern-upload" style="border: 1px dashed var(--border); border-radius: 12px; padding: 25px; text-align: center; cursor: pointer; background: rgba(var(--primary-rgb), 0.02); position: relative;">
                        <label for="pet-photo-upload" style="cursor: pointer; display: block; margin: 0;">
                            <div id="photo-preview-container" class="mb-2" style="${data.foto_url ? '' : 'display: none;'}">
                                <img id="photo-preview" src="${data.foto_url ? '<?php echo SITE_URL; ?>' + data.foto_url : ''}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 20px; border: 2px solid var(--primary); box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                            </div>
                            <i data-lucide="camera" class="icon-lucide mb-2 text-primary" style="width: 32px; height: 32px; ${data.foto_url ? 'display: none;' : ''}"></i>
                            <div class="small fw-800 photo-upload-label text-main-color mt-2">${data.foto_url ? 'Alterar foto do pet' : 'Clique para enviar foto...'}</div>
                            <div class="text-muted" style="font-size: 11px;">Recomendado: Quadrada (Ex: 500x500)</div>
                        </label>
                        <input type="file" id="pet-photo-upload" name="foto" accept="image/*" onchange="handlePhotoChange(this)" style="display: none;">
                    </div>
                </div>

                <div class="form-grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">Nome do Paciente *</label>
                        <input type="text" name="nome" class="form-control" value="${data.nome}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Chip / Carteirinha</label>
                        <input type="text" name="numero_carteirinha" class="form-control" value="${data.numero_carteirinha || ''}">
                    </div>
                </div>

                <div class="form-grid-3 mb-3">
                    <div class="form-group">
                        <label class="form-label">Espécie</label>
                        <select name="especie" class="form-control">
                            <option value="Cachorro" ${data.especie == 'Cachorro' ? 'selected' : ''}>Cachorro</option>
                            <option value="Gato" ${data.especie == 'Gato' ? 'selected' : ''}>Gato</option>
                            <option value="Peixe" ${data.especie == 'Peixe' ? 'selected' : ''}>Peixe</option>
                            <option value="Ave" ${data.especie == 'Ave' ? 'selected' : ''}>Ave</option>
                            <option value="Outros" ${data.especie == 'Outros' ? 'selected' : ''}>Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Raça</label>
                        <input type="text" name="raca" class="form-control" value="${data.raca || ''}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sexo</label>
                        <select name="sexo" class="form-control">
                            <option value="M" ${data.sexo == 'M' ? 'selected' : ''}>Macho</option>
                            <option value="F" ${data.sexo == 'F' ? 'selected' : ''}>Fêmea</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-3 mb-3">
                    <div class="form-group">
                        <label class="form-label">Peso (kg)</label>
                        <input type="text" name="peso" class="form-control mask-weight" value="${data.peso || ''}" placeholder="0.0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Idade / Nasc.</label>
                        <input type="text" name="idade" class="form-control mask-number" value="${data.idade || ''}" placeholder="Ex: 5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cor</label>
                        <input type="text" name="cor" class="form-control" value="${data.cor || ''}">
                    </div>
                </div>
            </div>

            <div class="modal-footer mt-4">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Atualizar Cadastro</button>
            </div>
        </form>
    `;
    UI.showModal('Editar Dados do Paciente', html);
    lucide.createIcons();
    if (UI.initMasks) UI.initMasks(document.getElementById('form-pet-edit'));
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

async function openConsultaModal(pet_id) {
    const html = `
        <form class="ajax-form" id="form-consulta" action="<?php echo SITE_URL; ?>/api/consultas/save">
            <div class="modal-body-scroll">
                <input type="hidden" name="pet_id" value="${pet_id}">
                <input type="hidden" name="nonce" value="<?php echo \Nonce::create('consulta_save'); ?>">
                
                <div class="form-grid-2">
                    <div class="form-group mb-4">
                        <label class="form-label">Data e Hora *</label>
                        <input type="datetime-local" name="data_consulta" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="concluida" selected>Concluída</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Motivo da Visita *</label>
                    <input type="text" name="motivo" class="form-control" required placeholder="Ex: Vacinas, Check-up...">
                </div>

                <div class="form-grid-1 mb-4">
                    <div class="form-group">
                        <label class="form-label">Sintomas / Observações Clínicas</label>
                        <textarea name="diagnostico" class="form-control" rows="4" placeholder="Relato clínico..."></textarea>
                    </div>
                </div>

                <div class="form-grid-1 mb-4">
                    <div class="form-group">
                        <label class="form-label">Prescrição & Tratamento</label>
                        <textarea name="prescricao" class="form-control" rows="4" placeholder="Receituário detalhado..."></textarea>
                    </div>
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group mb-3">
                        <label class="form-label">Valor (R$)</label>
                        <input type="text" name="valor" class="form-control mask-money" placeholder="0,00">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Obs. Adicionais</label>
                        <input type="text" name="observacoes" class="form-control" placeholder="Anotações internas...">
                    </div>
                </div>
            </div>

            <div class="modal-footer mt-4">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar no Prontuário</button>
            </div>
        </form>
    `;

    UI.showModal('Novo Registro Clínico', html);
    lucide.createIcons();
    if (UI.initMasks) UI.initMasks(document.getElementById('form-consulta'));
}
</script>

<style>
/* Page specific minor overrides or animations */
.page-main-title { color: var(--text-main); margin:0; font-size: 24px; font-weight: 850; }
.back-button-circular { width: 45px; height: 45px; border-radius: 14px; display: flex; align-items: center; justify-content: center; padding: 0; }
.rounded-12 { border-radius: 12px; }
.image-header-320 { height: 320px; position: relative; }
.img-fluid-cover { width: 100%; height: 100%; object-fit: cover; }
.placeholder-avatar-gradient { height: 100%; background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(var(--primary-rgb), 0.1) 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; }
.placeholder-icon-large { width: 80px; height: 80px; color: var(--primary); opacity: 0.2; }
.placeholder-text-muted { color: var(--text-muted); font-size: 13px; font-weight: 800; text-transform: uppercase; margin-top: 15px; letter-spacing: 1px; }
.mini-info-card-highlight { background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border); padding: 1rem; border-radius: 1rem; }
.label-tiny-caps { font-size: 9px; letter-spacing: 0.5px; text-transform: uppercase; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 0.25rem; }
.text-main-color { color: var(--text-main); }
.action-link-icon { color: var(--primary); font-size: 11px; font-weight: 700; transition: var(--transition-smooth); }
.action-link-icon:hover { opacity: 0.8; }
.border-top-dashed { border-top: 1px dashed var(--border); }
.giant-title { font-size: 42px; font-weight: 850; margin: 0; color: var(--text-main); }
.gender-icon-large { width: 28px; height: 28px; }
.gender-icon-large.male { color: #3b82f6; }
.gender-icon-large.female { color: #ec4899; }
.subtitle-highlight { font-size: 18px; font-weight: 600; margin-bottom: 25px; }
.info-blocks-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; margin-bottom: 32px; }
.value-display-18 { font-size: 18px; color: var(--text-main); font-weight: 700; }
.bg-black-border { background: #000; border: 1px solid var(--border); }
.rounded-10 { border-radius: 10px; }
.font-12 { font-size: 12px; }
.giant-heading { font-size: 24px; font-weight: 850; color: var(--text-main); margin: 0; }
.icon-heading { width: 28px; height: 28px; vertical-align: middle; }
.border-bottom-muted { border-bottom: 1px solid var(--border); }
.empty-state-clinical { padding: 3rem; text-align: center; color: var(--text-muted); background: rgba(var(--primary-rgb), 0.02); border-radius: 20px; border: 2px dashed var(--border); }
.empty-state-icon { width: 60px; height: 60px; opacity: 0.1; }
.icon-tiny { width: 14px; height: 14px; }
.tracking-1 { letter-spacing: 1px; }
.font-18 { font-size: 18px; }
.badge-status-clinical { font-size: 10px; font-weight: 800; border: 1px solid currentColor; padding: 5px 12px; border-radius: 8px; }
.bg-black-20 { background: rgba(0,0,0,0.2); }
.border-muted { border: 1px solid var(--border); }
.font-14 { font-size: 14px; }
.text-primary-important { color: var(--primary) !important; }
.bg-success-ultra-lite { background: rgba(16, 185, 129, 0.03); }
.border-success-lite { border: 1px solid rgba(16, 185, 129, 0.1); }
.text-success-important { color: #10b981 !important; }
.bg-white-01 { background: rgba(255,255,255,0.01); }
.border-left-primary { border-left: 3px solid var(--primary); }

.animate-slide-in {
    animation: slideIn 0.5s ease-out forwards;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>
