<?php
/** @var array $categorias */
/** @var string $nonce_save */
/** @var string $nonce_delete */
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Categorias de Produtos</h2>
        <p class="text-muted">Organize seus produtos em categorias para facilitar a navegação.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo SITE_URL; ?>/app/produtos" class="btn-secondary">
            <i data-lucide="arrow-left" class="icon-lucide"></i> Voltar para Produtos
        </a>
        <button class="btn-primary" onclick="openCategoriaModal()">
            <i data-lucide="plus" class="icon-lucide"></i> Nova Categoria
        </button>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Nome da Categoria</th>
                    <th>Sub-Produtos (Qtd.)</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i data-lucide="tag" class="icon-lucide icon-xl mb-2 opacity-50"></i>
                            <p>Nenhuma categoria cadastrada ainda.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categorias as $c): ?>
                        <?php 
                        // Simplified count, usually I'd do this in the controller/model with a JOIN or separate query
                        $count = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_produtos WHERE categoria_id = ?", [$c['id']])['total'] ?? 0;
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($c['nome']); ?></div>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary"><?php echo $count; ?> produtos</span>
                            </td>
                            <td class="text-right">
                                <button onclick='editCategoria(<?php echo json_encode($c); ?>)' class="btn-user-action" title="Editar">
                                    <i data-lucide="edit-3" class="icon-lucide"></i>
                                </button>
                                <button onclick="deleteCategoria(<?php echo $c['id']; ?>)" class="btn-user-action btn-user-delete" title="Excluir">
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
function openCategoriaModal(data = null) {
    const isEdit = data !== null;
    
    const html = `
        <form class="ajax-form" id="form-categoria" action="<?php echo SITE_URL; ?>/api/produtos/categorias/save">
            <div class="modal-body">
                <input type="hidden" name="id" value="${isEdit ? data.id : ''}">
                <input type="hidden" name="nonce" value="<?php echo $nonce_save; ?>">

                <div class="floating-group mb-3">
                    <input type="text" name="nome" class="form-control" value="${isEdit ? data.nome : ''}" required placeholder=" " id="cat_nome">
                    <label for="cat_nome">Nome da Categoria *</label>
                </div>
            </div>

            <div class="modal-footer mt-4">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">${isEdit ? 'Atualizar Categoria' : 'Adicionar Categoria'}</button>
            </div>
        </form>
    `;

    UI.showModal(isEdit ? 'Editar Categoria' : 'Nova Categoria', html);
    lucide.createIcons();
}

function editCategoria(data) {
    openCategoriaModal(data);
}

async function deleteCategoria(id) {
    if (await UI.confirmAction('Excluir Categoria', 'Deseja realmente remover esta categoria? Os produtos associados ficarão sem categoria.')) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/produtos/categorias/delete', {
            id: id,
            nonce: '<?php echo $nonce_delete; ?>'
        });
        if (res && res.success) {
            location.reload();
        }
    }
}
</script>
