<?php
/** @var array $tutor */
/** @var array $pets */
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?php echo SITE_URL; ?>/app/tutores" class="btn-secondary tp-back-btn">
            <i data-lucide="arrow-left" class="icon-lucide"></i>
        </a>
        <div>
            <h1 class="tp-page-title">Ficha do Cliente</h1>
            <p class="text-muted small mb-0">Gestão completa dos dados do tutor e pacientes vinculados.</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn-primary px-4 py-2 shadow-primary rounded-12 fw-800 d-flex align-items-center gap-2" 
                onclick='openTutorUploadModal(<?php echo json_encode($tutor, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
            <i data-lucide="file-up" class="icon-lucide icon-xs"></i> Anexar Documento
        </button>
        <button class="btn-primary px-4 py-2 shadow-primary rounded-12 fw-800 d-flex align-items-center gap-2" 
                onclick='openTutorEditModal(<?php echo json_encode($tutor, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
            <i data-lucide="edit-3" class="icon-lucide icon-xs"></i> Editar Cadastro
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- MAIN GRID: SIDEBAR + CONTENT                   -->
<!-- ═══════════════════════════════════════════════ -->
<div class="tp-grid">
    <!-- LEFT SIDEBAR -->
    <div class="tp-sidebar">
        <div class="tp-sidebar-card shadow-sm">
            <!-- Avatar Header -->
            <div class="tp-sidebar-header">
                <div class="tp-avatar">
                    <i data-lucide="user-2" class="tp-avatar-icon"></i>
                </div>
                <h2 class="tp-name"><?php echo htmlspecialchars($tutor['nome']); ?></h2>
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <?php if (($tutor['user_active'] ?? 1) == 1): ?>
                        <span class="tp-badge-active cursor-pointer" onclick="toggleTutorStatus(<?php echo $tutor['id']; ?>, 0)">
                             <i data-lucide="check-circle" style="width: 10px; height: 10px;"></i> ATIVO
                        </span>
                    <?php else: ?>
                        <span class="tp-badge-inactive cursor-pointer" onclick="toggleTutorStatus(<?php echo $tutor['id']; ?>, 1)" style="background: rgba(220, 38, 38, 0.1); color: #dc2626; font-size: 9px; padding: 2px 8px; border-radius: 6px; font-weight: 800;">
                            <i data-lucide="x-circle" style="width: 10px; height: 10px;"></i> INATIVO
                        </span>
                    <?php endif; ?>
                    <span class="text-muted small fw-700">#<?php echo $tutor['id']; ?></span>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="tp-sidebar-body">
                <div class="tp-stat-box">
                    <span class="tp-stat-label">Pets Associados</span>
                    <span class="tp-stat-value"><?php echo count($pets); ?></span>
                </div>
                
                <div class="tp-sidebar-meta">
                    <div class="d-flex align-items-center gap-3">
                        <div class="tp-meta-icon">
                            <i data-lucide="calendar" class="icon-lucide icon-xs"></i>
                        </div>
                        <div>
                            <span class="tp-meta-label">Cadastro em</span>
                            <span class="tp-meta-value"><?php echo date('d/m/Y', strtotime($tutor['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($tutor['contrato_url'])): ?>
                <div class="tp-sidebar-meta mt-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="tp-meta-icon" style="background: rgba(var(--primary-rgb), 0.1);">
                            <i data-lucide="file-check-2" class="icon-lucide icon-xs"></i>
                        </div>
                        <div>
                            <span class="tp-meta-label">Documentação</span>
                            <a href="<?php echo SITE_URL . $tutor['contrato_url']; ?>" target="_blank" class="tp-meta-value text-primary" style="text-decoration: underline;">BAIXAR PDF</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT -->
    <div class="tp-content p-0">
        <div class="row g-4 d-flex align-items-stretch">
            <!-- LEFT COLUMN: CANAIS DE COMUNICAÇÃO -->
            <div class="col-lg-6">
                <div class="tp-premium-card h-100 shadow-sm border">
                    <div class="tp-card-header mb-4">
                        <div class="tp-card-icon-box">
                            <i data-lucide="message-square" class="icon-sm"></i>
                        </div>
                        <div>
                            <h4 class="tp-card-title">Canais de Comunicação</h4>
                            <p class="tp-card-subtitle">E-mail, telefone e notificações.</p>
                        </div>
                    </div>
                    
                    <div class="info-stack">
                        <div class="tp-data-row">
                            <div class="tp-data-icon tp-phone-bg">
                                <i data-lucide="phone" class="icon-xs"></i>
                            </div>
                            <div class="flex-1">
                                <span class="tp-data-label">WhatsApp / Celular</span>
                                <span class="tp-data-value-main"><?php echo htmlspecialchars($tutor['telefone'] ?: 'Não informado'); ?></span>
                            </div>
                        </div>
                        
                        <div class="tp-data-row mt-3">
                            <div class="tp-data-icon tp-email-bg">
                                <i data-lucide="mail" class="icon-xs"></i>
                            </div>
                            <div class="flex-1">
                                <span class="tp-data-label">Correio Eletrônico</span>
                                <span class="tp-data-value-main text-truncate"><?php echo htmlspecialchars($tutor['email'] ?: 'Não informado'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: IDENTIFICAÇÃO E LOCALIZAÇÃO -->
            <div class="col-lg-6">
                <div class="tp-premium-card h-100 shadow-sm border">
                    <div class="tp-card-header mb-4">
                        <div class="tp-card-icon-box">
                            <i data-lucide="map-pin" class="icon-sm"></i>
                        </div>
                        <div>
                            <h4 class="tp-card-title">Dados e Localização</h4>
                            <p class="tp-card-subtitle">Documentos e endereço físico.</p>
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="tp-data-box-simple">
                                <span class="tp-data-label">Documento</span>
                                <span class="tp-data-value-alt"><?php echo htmlspecialchars($tutor['cpf'] ?: 'Não cadastrado'); ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="tp-data-box-simple">
                                <span class="tp-data-label">Cidade / UF</span>
                                <span class="tp-data-value-alt text-truncate"><?php echo htmlspecialchars($tutor['city'] ?: '---'); ?> / <?php echo htmlspecialchars($tutor['state'] ?: '---'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tp-address-premium-box mt-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="tp-address-indicator">
                                <i data-lucide="map" class="icon-xs"></i>
                            </div>
                            <div class="flex-1">
                                <span class="tp-data-label">Endereço de Entrega/Visita</span>
                                <div class="tp-address-content mt-1">
                                    <?php 
                                    if (!empty($tutor['street'])) {
                                        echo "<span class='font-weight-bold d-block'>" . htmlspecialchars($tutor['street']) . ", " . ($tutor['address_number'] ?: 'S/N') . "</span>";
                                        if ($tutor['neighborhood']) echo "<span class='text-muted small'>" . htmlspecialchars($tutor['neighborhood']) . "</span>";
                                        echo "<div class='mt-2'><span class='tp-badge-zip-outline'>CEP: " . ($tutor['zip_code'] ?: '---') . "</span></div>";
                                    } else {
                                        echo "<span class='text-muted small italic opacity-50'>Sem endereço cadastrado.</span>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botão de Contrato Integrado -->
                    <?php if (!empty($tutor['contrato_url'])): ?>
                    <div class="mt-4 pt-3 border-top-dashed">
                        <a href="<?php echo SITE_URL . $tutor['contrato_url']; ?>" target="_blank" class="tp-contract-btn-premium">
                            <div class="tp-contract-badge">
                                <i data-lucide="file-check" class="icon-xs"></i>
                            </div>
                            <div class="flex-1">
                                <span class="d-block fw-800 text-uppercase tracking-1" style="font-size: 10px;">CONTRATO DIGITAL</span>
                                <span class="text-primary small fw-600">Visualizar Termo de Serviço</span>
                            </div>
                            <i data-lucide="external-link" class="icon-xs opacity-50"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- ASSOCIATED PETS                                 -->
<!-- ═══════════════════════════════════════════════ -->
<div class="tp-pets-section shadow-sm">
    <div class="tp-pets-header">
        <div>
            <h3 class="tp-pets-title">
                <i data-lucide="dog" class="icon-lucide icon-xs text-primary"></i> 
                Pacientes Associados
            </h3>
            <p class="text-muted small mt-1 mb-0">Pacientes vinculados à responsabilidade deste cliente.</p>
        </div>
        <button class="btn-primary px-4 py-2 rounded-12 fw-700 d-flex align-items-center gap-2" onclick="window.location.href='<?php echo SITE_URL; ?>/app/pets'">
            <i data-lucide="plus-circle" class="icon-lucide icon-xs"></i> Adicionar
        </button>
    </div>

    <?php if (empty($pets)): ?>
        <div class="tp-empty-state">
            <i data-lucide="heart-off" class="tp-empty-icon"></i>
            <h4 class="fw-800 text-main-color mt-3">Nenhum Paciente</h4>
            <p class="small text-muted">Este tutor ainda não possui pets cadastrados.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($pets as $pet): ?>
                <div class="col-md-6 col-xl-4">
                    <a href="<?php echo SITE_URL; ?>/app/pets/perfil/<?php echo $pet['id']; ?>" class="tp-pet-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tp-pet-avatar">
                                <?php if (!empty($pet['foto_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($pet['foto_url']); ?>" alt="Pet">
                                <?php else: ?>
                                    <i data-lucide="dog" class="icon-lucide text-primary" style="opacity: 0.3; width: 28px; height: 28px;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <h4 class="tp-pet-name"><?php echo htmlspecialchars($pet['nome']); ?></h4>
                                <div class="text-muted small text-truncate">
                                    <?php echo htmlspecialchars($pet['especie']); ?> — <?php echo htmlspecialchars($pet['raca'] ?: 'S.R.D'); ?>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <span class="tp-pet-badge"><?php echo ($pet['sexo'] == 'M' ? 'MACHO' : 'FÊMEA'); ?></span>
                                    <span class="tp-pet-badge tp-pet-badge--success">ATIVO</span>
                                </div>
                            </div>
                        </div>
                        <div class="tp-pet-footer">
                            <span class="small text-muted fw-700">Ficha Médica</span>
                            <i data-lucide="chevron-right" class="icon-lucide icon-xs text-primary"></i>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- EDIT MODAL SCRIPT                               -->
<!-- ═══════════════════════════════════════════════ -->
<script>
function openTutorEditModal(data) {
    if(!data) return;
    
    const html = `
        <form class="ajax-form" id="form-tutor-edit" action="<?php echo SITE_URL; ?>/api/tutores/save" enctype="multipart/form-data">
            <div class="modal-body-scroll">
                <input type="hidden" name="id" value="${data.id}">
                <input type="hidden" name="nonce" value="<?php echo \Nonce::create('tutor_save'); ?>">
                
                <h6 class="mb-3 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="user" class="icon-lucide icon-xs"></i> Identificação do Cliente
                </h6>

                <div class="form-group mb-3">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome" class="form-control" value="${data.nome || ''}" required placeholder="Nome do proprietário">
                </div>

                <div class="form-grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">CPF/CNPJ</label>
                        <input type="text" name="cpf" class="form-control mask-document" value="${data.cpf || ''}" placeholder="000.000.000-00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="${data.email || ''}" placeholder="email@exemplo.com">
                    </div>
                </div>

                <div class="form-grid-2 mb-4">
                    <div class="form-group">
                        <label class="form-label">WhatsApp *</label>
                        <input type="text" name="telefone" class="form-control mask-phone" value="${data.telefone || ''}" required placeholder="(00) 00000-0000">
                    </div>
                    <div class="form-group">
                        <label class="form-label text-primary fw-bold">Limite de Crédito (R$)</label>
                        <input type="text" name="credit_limit" class="form-control mask-money" value="${UI.formatMoney(data.credit_limit || 0)}">
                    </div>
                </div>

                <h6 class="mb-3 mt-4 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="key" class="icon-lucide icon-xs"></i> Acesso ao Portal do Cliente (Tutor)
                </h6>

                <div class="form-grid-2 mb-3">
                    <div class="form-group">
                        <label class="form-label">Nome de Usuário (Login)</label>
                        <input type="text" name="username" class="form-control" value="${data.username || ''}" readonly style="background: rgba(var(--primary-rgb), 0.05); cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Senha de Acesso</label>
                        <input type="password" name="password" class="form-control" placeholder="Deixe em branco p/ manter">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Contrato / Termo (PDF)</label>
                    <div class="modern-upload" style="border: 1px dashed var(--border); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; background: rgba(var(--primary-rgb), 0.02); position: relative;">
                        <label for="contrato-upload" style="cursor: pointer; display: block; margin: 0;">
                            <div id="contrato-preview-container" class="mb-2" style="display: none;">
                                <i data-lucide="file-check" class="text-success" style="width: 32px; height: 32px;"></i>
                                <div class="small fw-800 text-success mt-1">Arquivo PDF Selecionado</div>
                            </div>
                            <div id="contrato-placeholder">
                                <i data-lucide="file-up" class="icon-lucide mb-2 text-primary" style="width: 32px; height: 32px;"></i>
                                <div class="small fw-800 text-main-color">Clique ou arraste o PDF do contrato</div>
                                <div class="text-muted" style="font-size: 11px;">Apenas arquivos .pdf são aceitos</div>
                            </div>
                        </label>
                        <input type="file" id="contrato-upload" name="contrato" accept="application/pdf" style="display: none;" onchange="handleContratoChange(this)">
                    </div>
                </div>

                <h6 class="mb-3 mt-4 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="map-pin" class="icon-lucide icon-xs"></i> Localização / Endereço
                </h6>

                <div class="form-grid-3 mb-3">
                    <div class="form-group">
                        <label class="form-label">CEP</label>
                        <input type="text" name="zip_code" class="form-control mask-zip" value="${data.zip_code || ''}" onblur="UI.lookupZip(this.value, 'tutor-city', 'tutor-state', 'tutor-street', 'tutor-neighborhood')">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Rua / Avenida</label>
                        <input type="text" name="street" id="tutor-street" class="form-control" value="${data.street || ''}">
                    </div>
                </div>

                <div class="form-grid-3 mb-3">
                    <div class="form-group">
                        <label class="form-label">Número</label>
                        <input type="text" name="address_number" class="form-control" value="${data.address_number || ''}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="neighborhood" id="tutor-neighborhood" class="form-control" value="${data.neighborhood || ''}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="city" id="tutor-city" class="form-control" value="${data.city || ''}">
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label">Estado (UF)</label>
                    <input type="text" name="state" id="tutor-state" class="form-control text-center" value="${data.state || ''}" maxlength="2" style="width: 60px;">
                </div>
            </div>

            <div class="modal-footer mt-4">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Atualizar Cadastro</button>
            </div>
        </form>
    `;

    UI.showModal('Editar Dados do Cliente', html);
    if(window.lucide) lucide.createIcons();
    if(window.UI && UI.initMasks) UI.initMasks();
}

/**
 * Handle Contrato Upload Preview
 */
function handleContratoChange(input) {
    const preview = document.getElementById('contrato-preview-container');
    const placeholder = document.getElementById('contrato-placeholder');
    if (input.files && input.files[0]) {
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        UI.showToast('Contrato selecionado: ' + input.files[0].name, 'info');
    }
}

function openTutorUploadModal(data) {
    if(!data) return;
    
    const html = `
        <form class="ajax-form" id="form-tutor-upload" action="<?php echo SITE_URL; ?>/api/tutores/upload-contract" enctype="multipart/form-data">
            <input type="hidden" name="id" value="${data.id}">
            <input type="hidden" name="nonce" value="<?php echo \Nonce::create('tutor_save'); ?>">
            
            <div class="p-3">
                <div class="modern-upload" style="border: 2px dashed var(--primary); border-radius: 16px; padding: 40px 20px; text-align: center; cursor: pointer; background: rgba(var(--primary-rgb), 0.05); position: relative;">
                    <label for="contrato-upload-single" style="cursor: pointer; display: block; margin: 0;">
                        <div id="contrato-preview-single" class="mb-3" style="display: none;">
                            <i data-lucide="file-check" class="text-success" style="width: 48px; height: 48px;"></i>
                            <div class="fw-800 text-success mt-2">PDF Selecionado com Sucesso</div>
                        </div>
                        <div id="contrato-placeholder-single">
                            <i data-lucide="cloud-upload" class="mb-3 text-primary" style="width: 48px; height: 48px;"></i>
                            <div class="fw-800 text-main-color" style="font-size: 15px;">Clique para anexar o Contrato (PDF)</div>
                            <p class="text-muted small mt-1">O arquivo será vinculado à ficha do cliente: <b>${data.nome}</b></p>
                        </div>
                    </label>
                    <input type="file" id="contrato-upload-single" name="contrato" accept="application/pdf" style="display: none;" 
                           onchange="document.getElementById('contrato-preview-single').style.display='block'; 
                                     document.getElementById('contrato-placeholder-single').style.display='none'; 
                                     lucide.createIcons();">
                </div>
                
                <div class="mt-4 d-grid">
                    <button type="submit" class="btn-primary py-3 fw-800 shadow-primary">
                        <i data-lucide="save" class="icon-xs"></i> SALVAR DOCUMENTO AGORA
                    </button>
                    <button type="button" class="btn-link mt-2 text-muted small" onclick="UI.closeModal()">Cancelar e Voltar</button>
                </div>
            </div>
        </form>
    `;

    UI.showModal('Anexar Contrato Digital', html);
    if(window.lucide) lucide.createIcons();
}

function toggleTutorStatus(id, newStatus) {
    const action = newStatus === 1 ? 'ativar' : 'inativar';
    UI.confirm(`Deseja realmente <b>${action}</b> este cliente?`, {
        title: 'Alteração de Status',
        confirmText: `Sim, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
        cancelText: 'Cancelar',
        type: newStatus === 1 ? 'success' : 'danger',
        icon: newStatus === 1 ? 'check' : 'alert-triangle'
    }).then((confirmed) => {
        if (confirmed) {
            UI.request('<?php echo SITE_URL; ?>/api/tutores/toggle-status', { 
                id: id, 
                status: newStatus 
            }).then((res) => {
                if (res && res.success) {
                    UI.toast(res.message);
                    setTimeout(() => window.location.reload(), 800);
                } else if (res) {
                    UI.toast(res.message || 'Erro ao processar solicitação', 'error');
                }
            });
        }
    });
}
</script>

<!-- ═══════════════════════════════════════════════ -->
<!-- SCOPED STYLES                                   -->
<!-- ═══════════════════════════════════════════════ -->
<style>
/* ── Layout Grid ── */
.tp-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 24px;
    margin-bottom: 24px;
}
@media (max-width: 992px) {
    .tp-grid { grid-template-columns: 1fr; }
}

/* ── Page Header ── */
.tp-page-title { color: var(--text-main); margin: 0; font-size: 20px; font-weight: 850; }
.tp-back-btn { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 0; }

/* ── Sidebar ── */
.tp-sidebar-card {
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    overflow: hidden;
}

.tp-sidebar-header {
    padding: 2.5rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.06) 0%, rgba(var(--primary-rgb), 0.12) 100%);
    border-bottom: 1px solid var(--border);
}

.tp-avatar {
    width: 80px; height: 80px;
    border-radius: 24px;
    background: rgba(255,255,255,0.1);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    margin: 0 auto;
    border: 2px solid rgba(var(--primary-rgb), 0.2);
}
.tp-avatar-icon { width: 36px; height: 36px; }

.tp-name {
    font-weight: 850; font-size: 18px;
    color: var(--text-main);
    margin: 1rem 0 0;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    padding: 0 10px;
}

.tp-badge-active {
    background: #10b981; color: #fff;
    font-size: 9px; font-weight: 800;
    padding: 4px 12px; border-radius: 50px;
    letter-spacing: 0.5px;
}

.tp-sidebar-body { padding: 1.25rem; }

.tp-stat-box {
    padding: 1rem;
    border-radius: 14px;
    text-align: center;
    background: rgba(var(--primary-rgb), 0.04);
    border: 1px solid var(--border);
}
.tp-stat-label { display: block; font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 800; color: var(--text-muted); margin-bottom: 4px; }
.tp-stat-value { font-size: 28px; font-weight: 850; color: var(--text-main); line-height: 1; }

.tp-sidebar-meta {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px dashed var(--border);
}
.tp-meta-icon { width: 30px; height: 30px; border-radius: 8px; background: rgba(var(--primary-rgb), 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary); }
.tp-meta-label { display: block; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 800; color: var(--text-muted); }
.tp-meta-value { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); }

/* ── Content Card ── */
.tp-content-card {
    border-radius: 28px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    padding: 2.25rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

/* ── Content Card ── */
.tp-premium-card {
    border-radius: 24px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    padding: 2rem;
}

.tp-card-header { display: flex; align-items: center; gap: 15px; border-bottom: 1px dashed var(--border); padding-bottom: 1.5rem; }
.tp-card-icon-box { width: 42px; height: 42px; border-radius: 12px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; }
.tp-card-title { font-size: 16px; font-weight: 850; color: var(--text-main); margin: 0; }
.tp-card-subtitle { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--text-muted); margin: 2px 0 0; }

/* Info Data Rows */
.tp-data-row { background: rgba(var(--primary-rgb), 0.02); border: 1px solid var(--border); border-radius: 16px; padding: 1.15rem; display: flex; align-items: center; gap: 15px; }
.tp-data-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.tp-phone-bg { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.tp-email-bg { background: rgba(var(--primary-rgb), 0.1); color: var(--primary); }

.tp-data-label { display: block; font-size: 9px; text-transform: uppercase; font-weight: 850; color: var(--text-muted); margin-bottom: 3px; }
.tp-data-value-main { display: block; font-size: 15px; font-weight: 850; color: var(--text-main); }

/* Data Boxes Simple */
.tp-data-box-simple { padding: 1rem; border-radius: 14px; background: rgba(var(--primary-rgb), 0.02); border: 1px solid var(--border); }
.tp-data-value-alt { display: block; font-size: 14px; font-weight: 800; color: var(--text-main); }

/* Address Premium Box */
.tp-address-premium-box { background: rgba(var(--primary-rgb), 0.01); border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem; }
.tp-address-indicator { width: 34px; height: 34px; border-radius: 9px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.tp-address-content { font-size: 13px; line-height: 1.5; color: var(--text-main); }
.tp-badge-zip-outline { display: inline-block; font-size: 9px; font-weight: 850; border: 1px solid var(--primary); color: var(--primary); padding: 2px 8px; border-radius: 5px; }

/* Contract Button Premium */
.tp-contract-btn-premium { display: flex; align-items: center; gap: 15px; padding: 1rem; border-radius: 16px; background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.08) 0%, rgba(var(--primary-rgb), 0.03) 100%); border: 1px solid rgba(var(--primary-rgb), 0.15); text-decoration: none !important; transition: all 0.3s ease; }
.tp-contract-btn-premium:hover { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.1); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.1); }
.tp-contract-badge { width: 36px; height: 36px; border-radius: 10px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; }
.border-top-dashed { border-top: 1px dashed var(--border); }

.tp-info-section-header {
    display: flex;
    align-items: center;
    gap: 18px;
    padding-bottom: 20px;
    border-bottom: 1px dashed rgba(var(--primary-rgb), 0.1);
}

.tp-info-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.15) 0%, rgba(var(--primary-rgb), 0.05) 100%);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.1);
}

.tp-info-section-title {
    font-size: 16px;
    font-weight: 850;
    color: var(--text-main);
    margin: 0;
    letter-spacing: -0.2px;
}

.tp-info-section-subtitle {
    font-size: 10px;
    color: var(--text-muted);
    margin: 4px 0 0;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 800;
    opacity: 0.8;
}

/* Premium Item Cards */
.tp-premium-item-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 18px;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.tp-premium-item-card:hover { 
    border-color: var(--primary); 
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    background: rgba(var(--primary-rgb), 0.01);
}

.tp-premium-item-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.tp-icon-phone { background: rgba(16, 185, 129, 0.08); color: #10b981; }
.tp-icon-email { background: rgba(var(--primary-rgb), 0.08); color: var(--primary); }

.tp-premium-item-label {
    display: block;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    font-weight: 850;
    color: var(--text-muted);
    margin-bottom: 5px;
    opacity: 0.7;
}

.tp-premium-item-value {
    display: block;
    font-size: 17px;
    font-weight: 850;
    color: var(--text-main);
    letter-spacing: -0.1px;
}

/* Minimal Data Boxes */
.tp-minimal-data-box {
    padding: 1.25rem;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px;
    transition: var(--transition-smooth);
}
.tp-minimal-data-box:hover { border-color: rgba(var(--primary-rgb), 0.3); }

.tp-minimal-data-label {
    display: block;
    font-size: 9px;
    text-transform: uppercase;
    font-weight: 850;
    color: var(--text-muted);
    margin-bottom: 6px;
    letter-spacing: 1px;
    opacity: 0.7;
}

.tp-minimal-data-value {
    display: block;
    font-size: 15px;
    font-weight: 850;
    color: var(--text-main);
}

/* Address Premium */
.tp-premium-address-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 1.75rem;
    transition: var(--transition-smooth);
}
.tp-premium-address-card:hover { border-color: rgba(var(--primary-rgb), 0.3); }

.tp-address-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(var(--primary-rgb), 0.06);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tp-address-primary {
    font-size: 15px;
    font-weight: 850;
    color: var(--text-main);
}

.tp-address-secondary {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 3px;
    font-weight: 600;
}

.tp-badge-zip {
    font-size: 9px;
    font-weight: 850;
    background: var(--primary);
    color: #fff;
    padding: 4px 12px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tp-address-empty {
    font-size: 14px;
    color: var(--text-muted);
    font-style: italic;
    opacity: 0.5;
}

.tp-divider { border: none; border-top: 1px solid var(--border); margin: 1.5rem 0; }

/* Info Cards (Contact) */
.tp-info-card {
    background: rgba(var(--primary-rgb), 0.02);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1rem 1.25rem;
    transition: var(--transition-smooth);
}
.tp-info-card:hover { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.05); }

.tp-info-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 800; color: var(--text-muted); display: block; }
.tp-info-value { font-size: 15px; font-weight: 800; color: var(--text-main); }

.tp-info-icon {
    width: 32px; height: 32px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.tp-info-icon--success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.tp-info-icon--primary { background: rgba(var(--primary-rgb), 0.1); color: var(--primary); }

/* Data Blocks (Document/City) */
.tp-data-block {
    background: rgba(var(--primary-rgb), 0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1rem 1.25rem;
}
.tp-data-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 4px; }
.tp-data-value { font-size: 14px; font-weight: 700; color: var(--text-main); display: block; }

/* Address */
.tp-address-box {
    background: rgba(var(--primary-rgb), 0.02);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.25rem;
}
.tp-address-text { font-size: 13px; font-weight: 600; color: var(--text-main); line-height: 1.6; opacity: 0.85; }
.tp-cep-badge { display: inline-block; margin-top: 8px; font-size: 10px; font-weight: 800; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); padding: 3px 10px; border-radius: 6px; }

/* ── Pets Section ── */
.tp-pets-section {
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    padding: 2rem;
    margin-top: 24px;
}
.tp-pets-header {
    display: flex; justify-content: space-between; align-items: center;
    padding-bottom: 1.25rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid var(--border);
}
.tp-pets-title { font-size: 16px; font-weight: 850; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px; }

/* Pet Cards */
.tp-pet-card {
    display: flex; flex-direction: column;
    height: 100%;
    padding: 1.25rem;
    background: rgba(var(--primary-rgb), 0.02);
    border: 1px solid var(--border);
    border-radius: 16px;
    text-decoration: none; color: inherit;
    transition: var(--transition-smooth);
}
.tp-pet-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); background: rgba(var(--primary-rgb), 0.05); }

.tp-pet-avatar {
    width: 60px; height: 60px;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(var(--primary-rgb), 0.15) 100%);
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.tp-pet-avatar img { width: 100%; height: 100%; object-fit: cover; }

.tp-pet-name { margin: 0 0 2px; font-size: 15px; font-weight: 850; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.tp-pet-badge {
    font-size: 9px; font-weight: 800;
    background: rgba(var(--primary-rgb), 0.1); color: var(--primary);
    padding: 2px 8px; border-radius: 6px;
}
.tp-pet-badge--success { background: rgba(16, 185, 129, 0.1); color: #10b981; }

.tp-pet-footer {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 1rem;
}

/* Empty State */
.tp-empty-state {
    padding: 3rem;
    text-align: center;
    background: rgba(var(--primary-rgb), 0.02);
    border-radius: 16px;
    border: 2px dashed var(--border);
}
.tp-empty-icon { width: 50px; height: 50px; opacity: 0.15; color: var(--text-muted); display: block; margin: 0 auto; }

/* Utility overrides */
.rounded-12 { border-radius: 12px; }
.text-main-color { color: var(--text-main); }
.label-tiny-caps { font-size: 9px; letter-spacing: 0.8px; text-transform: uppercase; font-weight: 800; color: var(--text-muted); display: block; margin-bottom: 0.35rem; }
.col-span-2 { grid-column: span 2; }
</style>
