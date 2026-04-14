<?php
/** @var array $produtos */
/** @var string $nonce_save */
/** @var string $nonce_delete */
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Gerenciamento de Produtos</h2>
        <p class="text-muted">Cadastre e configure os produtos da sua loja online.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo SITE_URL; ?>/app/loja/pedidos" class="btn-primary-glass">
            <i data-lucide="shopping-bag" class="icon-lucide"></i> Gerenciar Pedidos
        </a>
        <a href="<?php echo SITE_URL; ?>/<?php echo $_SESSION['company_slug'] ?? 'loja'; ?>/loja" target="_blank" class="btn-secondary">
            <i data-lucide="external-link" class="icon-lucide"></i> Ver Loja
        </a>
        <button class="btn-primary" onclick="openProdutoModal()">
            <i data-lucide="plus" class="icon-lucide"></i> Novo Produto
        </button>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Capa</th>
                    <th>Nome do Produto</th>
                    <th>Preço</th>
                    <th>Status</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i data-lucide="package-search" class="icon-lucide icon-xl mb-2 opacity-50"></i>
                            <p>Nenhum produto cadastrado ainda.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td>
                                <div class="product-capa-thumb" style="width: 50px; height: 50px; border-radius: 8px; background: #f0f0f0; overflow: hidden; border: 1px solid var(--border);">
                                    <?php if ($p['capa']): ?>
                                        <img src="<?php echo SITE_URL . $p['capa']; ?>" alt="Capa" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                            <i data-lucide="package" class="icon-lucide icon-sm"></i>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($p['em_promocao']): ?>
                                        <span class="badge-promo-mini">PROMO</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($p['nome']); ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars(mb_strimwidth($p['descricao'] ?: '', 0, 50, '...')); ?></div>
                            </td>
                            <td>
                                <?php if ($p['em_promocao']): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted" style="text-decoration: line-through; font-size: 11px;">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></span>
                                        <span style="color: #e11d48; font-weight: 700; font-size: 14px;">R$ <?php echo number_format((float)$p['preco_promocional'], 2, ',', '.'); ?></span>
                                    </div>
                                    <span class="badge" style="background: #e11d48; color: #fff; font-size: 9px; padding: 2px 6px; width: fit-content;">PROMOÇÃO</span>
                                <?php else: ?>
                                    <div style="font-weight: 700; font-size: 14px; color: var(--primary);">
                                        R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status']): ?>
                                    <span class="badge status-active">Ativo</span>
                                <?php else: ?>
                                    <span class="badge status-danger">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <button onclick='editProduto(<?php echo json_encode($p); ?>)' class="btn-user-action" title="Editar">
                                    <i data-lucide="edit-3" class="icon-lucide"></i>
                                </button>
                                <button onclick="deleteProduto(<?php echo $p['id']; ?>)" class="btn-user-action btn-user-delete" title="Excluir">
                                    <i data-lucide="trash-2" class="icon-lucide"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function openProdutoModal(data = null) {
    const isEdit = data !== null;
    
    const html = `
        <form class="ajax-form" id="form-produto" action="<?php echo SITE_URL; ?>/api/produtos/save" enctype="multipart/form-data">
            <div class="modal-body-scroll">
                <input type="hidden" name="id" value="${isEdit ? data.id : ''}">
                <input type="hidden" name="nonce" value="<?php echo $nonce_save; ?>">

                <!-- Foto de Capa (Padrão Moderno 100%) -->
                <div class="form-group mb-4">
                    <label class="form-label">Foto de Capa do Produto</label>
                    <div class="modern-upload">
                        <input type="file" name="capa" id="prod-img-input" accept="image/*" onchange="previewProdutoImage(this)">
                        <label for="prod-img-input">
                            <div id="img-preview-container" class="img-preview-container" style="display: ${isEdit && data.capa ? 'block' : 'none'}">
                                <img id="img-preview" src="${isEdit && data.capa ? '<?php echo SITE_URL; ?>' + data.capa : ''}">
                            </div>
                            <div id="upload-placeholder" class="upload-placeholder" style="display: ${isEdit && data.capa ? 'none' : 'flex'}">
                                <i data-lucide="upload-cloud" class="icon-lucide icon-xl"></i>
                                <span id="img-name-preview" class="mt-2 text-uppercase">Selecionar Foto</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Informações Básicas (Duas Colunas) -->
                <div class="form-grid-2 mb-3">
                    <div class="floating-group">
                        <input type="text" name="nome" class="form-control" value="${isEdit ? data.nome : ''}" required placeholder=" " id="prod_nome">
                        <label for="prod_nome">Nome do Produto *</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="preco" class="form-control mask-money" value="${isEdit ? (data.preco || '0,00').replace('.', ',') : '0,00'}" required placeholder=" " id="prod_preco">
                        <label for="prod_preco">Preço Original (R$)</label>
                    </div>
                </div>

                <div class="form-grid-2 mb-4">
                    <div class="d-flex flex-column justify-content-center">
                         <div class="selectable-card ${isEdit && data.em_promocao == 1 ? 'active' : ''}" onclick="togglePromoField(this)">
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="tag" class="icon-lucide icon-xs"></i>
                                <span style="font-size: 13px; font-weight: 600;">Produto em Promoção?</span>
                                <input type="checkbox" name="em_promocao" value="1" ${isEdit && data.em_promocao == 1 ? 'checked' : ''} style="display:none;">
                            </div>
                        </div>
                    </div>

                    <div id="promo-price-group" style="display: ${isEdit && data.em_promocao == 1 ? 'block' : 'none'}">
                        <div class="floating-group">
                            <input type="text" name="preco_promocional" class="form-control mask-money" value="${isEdit ? (data.preco_promocional || '').replace('.', ',') : ''}" placeholder=" " id="prod_preco_promo">
                            <label for="prod_preco_promo">Preço Promocional (R$)</label>
                        </div>
                    </div>
                </div>

                <div class="floating-group mb-4">
                    <textarea name="descricao" class="form-control" placeholder=" " id="prod_desc" style="height: 80px;">${isEdit ? (data.descricao || '') : ''}</textarea>
                    <label for="prod_desc">Descrição do Produto</label>
                </div>

                <div class="form-group mb-3 px-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size: 14px; font-weight: 600; color: var(--text-main);">Produto Ativo/Visível na Loja</span>
                        <label class="switch">
                            <input type="checkbox" name="status" value="1" ${!isEdit || data.status == 1 ? 'checked' : ''}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer mt-4">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">${isEdit ? 'Atualizar Produto' : 'Adicionar Produto'}</button>
            </div>
        </form>
    `;

    UI.showModal(isEdit ? 'Editar Produto' : 'Novo Produto', html);
    UI.initMasks();
    lucide.createIcons();
}

function previewProdutoImage(input) {
    const preview = document.getElementById('img-preview');
    const container = document.getElementById('img-preview-container');
    const placeholder = document.getElementById('upload-placeholder');
    const nameLabel = document.getElementById('img-name-preview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
            placeholder.style.display = 'none';
            nameLabel.innerText = 'Trocar Foto';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePromoField(el) {
    const checkbox = el.querySelector('input[type="checkbox"]');
    const promoGroup = document.getElementById('promo-price-group');
    
    checkbox.checked = !checkbox.checked;
    el.classList.toggle('active', checkbox.checked);
    
    if (checkbox.checked) {
        promoGroup.style.display = 'block';
        promoGroup.querySelector('input').focus();
    } else {
        promoGroup.style.display = 'none';
    }
}

function editProduto(data) {
    openProdutoModal(data);
}

async function deleteProduto(id) {
    if (await UI.confirmAction('Excluir Produto', 'Deseja realmente remover este produto? Esta ação não pode ser desfeita.')) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/produtos/delete', {
            id: id,
            nonce: '<?php echo $nonce_delete; ?>'
        });
        if (res && res.success) {
            location.reload();
        }
    }
}
</script>
