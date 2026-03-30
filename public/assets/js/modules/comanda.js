/**
 * Comanda Module - comanda.js
 * 
 * Padrão do sistema: utiliza UI.showModal(), UI.showToast(), UI.confirm()
 */

window.Comanda = {

    config: {
        produtos: [], 
        filtroProduto: '' 
    },

    mesas: [],
    mesaAtual: null,
    itensAtual: [],
    configEmpresa: { couvert: 0, taxa_valor: 0, taxa_tipo: 'porcentagem' },
    taxasRemovidas: { couvert: false, taxa: false },

    formatarMoeda(val) {
        return parseFloat(val || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    },

    async init() {
        console.log('Comanda Module: Iniciando módulo...');
        await this.carregarProdutos();
        await this.carregarMesas();
    },

    async carregarProdutos() {
        const res = await this.apiGet('/api/produtos/list');
        if (res && res.success) {
            this.config.produtos = res.data;
        }
    },

    async apiGet(path) {
        try {
            const url = `${window.SITE_URL}${path}`;
            const res = await fetch(url);
            if (!res.ok) throw new Error('Status ' + res.status);
            return await res.json();
        } catch (e) {
            console.error('Comanda API Error:', e);
            return null;
        }
    },

    async apiPost(path, body = null, nonce = null) {
        try {
            const url = `${window.SITE_URL}${path}`;
            const opts = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            };
            const payload = body || {};
            if (nonce) payload.nonce = nonce;
            opts.body = JSON.stringify(payload);
            
            const res = await fetch(url, opts);
            if (!res.ok) throw new Error('Status ' + res.status);
            return await res.json();
        } catch (e) {
            console.error('Comanda POST Error:', e);
            return null;
        }
    },

    async carregarMesas() {
        const data = await this.apiGet('/api/comanda/mesas');
        if (data && data.success) {
            this.mesas = data.data;
        }
        this.renderMesas();
    },

    renderMesas() {
        const grid = document.getElementById('mesas-grid');
        if (!grid) return;

        if (!this.mesas.length) {
            grid.innerHTML = `
                <div class="comanda-empty-state">
                    <i data-lucide="armchair" class="icon-lucide comanda-empty-icon"></i>
                    <p class="comanda-empty-text">Nenhuma mesa criada</p>
                </div>
            `;
            return;
        }

        grid.style.opacity = '0';
        grid.innerHTML = this.mesas.map(m => `
            <div class="mesa-card ${m.status}" onclick="Comanda.abrirComanda(${m.id})">
                <button class="btn-qr-mesa" onclick="event.stopPropagation(); Comanda.showQrCode(${m.numero})" title="Gerar QR Code da Mesa">
                    <i data-lucide="qr-code" class="icon-lucide icon-sm"></i>
                </button>
                ${window.IS_ADMIN ? `<button class="btn-remove-mesa" onclick="event.stopPropagation(); Comanda.removerMesa(${m.id}, ${m.numero})" title="Remover Mesa">&times;</button>` : ''}
                
                <i data-lucide="${m.status === 'ocupada' ? (m.is_reservada == 1 ? 'calendar-check' : 'users') : 'armchair'}" class="icon-lucide mesa-icon"></i>
                <div class="mesa-numero">MESA ${m.numero}</div>
                
                ${m.cliente_nome ? `<div class="mesa-cliente-badge">${m.cliente_nome}</div>` : ''}
                
                <div class="mesa-status-badge">
                    ${m.status === 'ocupada' ? (m.is_reservada == 1 ? 'RESERVADO' : 'Ocupada') : 'Livre'}
                </div>

                <div class="mesa-footer ${m.status === 'livre' ? 'mesa-footer-hidden' : ''}">
                    <span class="mesa-total">R$ ${parseFloat(m.total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                    <span class="mesa-itens">${m.itens_count || 0} itens lançados</span>
                </div>
            </div>
        `).join('');
        setTimeout(() => {
            grid.style.opacity = '1';
            if (window.lucide) lucide.createIcons();
        }, 50);
    },

    async removerMesa(id, numero) {
        if (await UI.confirm(`Remover permanentemente a <strong>Mesa ${numero}</strong>?`)) {
            const nonce = window.COM_NONCES?.remover_mesa;
            const res = await fetch(`${window.SITE_URL}/api/comanda/mesa/${id}?nonce=${nonce}`, { method: 'DELETE' });
            const data = await res.json();
            if (data && data.success) {
                UI.showToast(`Mesa ${numero} removida.`);
                this.carregarMesas();
            } else {
                UI.showToast(data.message || 'Erro', 'error');
            }
        }
    },

    async addMesa() {
        const html = `
            <div class="form-group mb-4 comanda-form-group-left">
                <label class="form-label">Número da Nova Mesa</label>
                <input type="number" id="new_mesa_num" class="form-control comanda-input-large" placeholder="Digite o número (Ex: 6)">
                <div id="mesa_error_msg" class="comanda-error-msg" style="display: none;">O número desta mesa já está em uso!</div>
            </div>
            <div class="comanda-modal-footer-flex">
                <button class="btn-secondary comanda-btn-flex-1" onclick="UI.closeModal()">Cancelar</button>
                <button class="btn-primary comanda-btn-flex-2" id="btn_confirm_add_mesa" disabled>CRIAR MESA</button>
            </div>
        `;

        UI.showModal('Adicionar Nova Mesa', html);
        
        const input = document.getElementById('new_mesa_num');
        const btn = document.getElementById('btn_confirm_add_mesa');
        const error = document.getElementById('mesa_error_msg');

        input.oninput = () => {
            const val = parseInt(input.value);
            const exists = this.mesas.find(m => m.numero === val);
            
            if (val > 0 && !exists) {
                btn.disabled = false;
                error.style.display = 'none';
                input.style.borderColor = 'var(--primary)';
            } else if (exists) {
                btn.disabled = true;
                error.style.display = 'block';
                input.style.borderColor = '#ef4444';
            } else {
                btn.disabled = true;
                error.style.display = 'none';
                input.style.borderColor = 'var(--border)';
            }
        };

        btn.onclick = async () => {
            const val = parseInt(input.value);
            const data = await this.apiPost('/api/comanda/mesas/add', { numero: val }, window.COM_NONCES?.add_mesa);
            if (data && data.success) {
                UI.showToast(`Mesa ${val} criada com sucesso!`);
                UI.closeModal();
                this.carregarMesas();
            } else if (data && data.message) {
                UI.showToast(data.message, 'error');
            }
        };

        setTimeout(() => input.focus(), 100);
    },

    async abrirComanda(id) {
        this.mesaAtual = this.mesas.find(m => m.id === id);
        if (!this.mesaAtual) return;

        const data = await this.apiGet(`/api/comanda/mesa/${id}`);
        this.itensAtual = (data && data.success) ? (data.itens || []) : [];
        
        // Reset removed fees for THIS specific open session 
        // Or should I keep them? Usually, if you re-open, they should re-appear unless we persist the removal.
        // For simplicity, let's reset them every time the comanda modal is opened anew.
        this.taxasRemovidas = { 
            couvert: data.config ? !data.config.couvert_ativo : false, 
            taxa: data.config ? !data.config.taxa_ativa : false 
        }; 
        if (data && data.config) {
            this.configEmpresa = data.config;
        }

        this.config.filtroProduto = ''; // Reseta filtro ao abrir
        this.renderModal();
    },

    // Filtro de produtos dinâmico
    filtrarProdutos(termo) {
        this.config.filtroProduto = termo.toLowerCase();
        this.renderQuickAdd();
    },

    renderQuickAdd() {
        const container = document.getElementById('quick-add-container');
        if (!container) return;

        const termo = this.config.filtroProduto;
        const filtrados = this.config.produtos.filter(p => 
            p.nome.toLowerCase().includes(termo) || 
            (p.codigo && p.codigo.toLowerCase().includes(termo)) ||
            (p.categoria && p.categoria.toLowerCase().includes(termo))
        );

        if (filtrados.length === 0) {
            container.innerHTML = `<p class="comanda-quick-add-empty">Nenhum produto encontrado.</p>`;
            return;
        }

        container.innerHTML = filtrados.map(p => `
            <button class="btn-produto-rapido" onclick="Comanda.lancarItem('${p.nome.replace(/'/g, "\\'")}', ${p.preco})">
                <div class="comanda-btn-produto-content">
                    <div class="comanda-btn-produto-header">
                        <span class="comanda-btn-produto-nome">${p.nome}</span>
                        ${p.codigo ? `<span class="comanda-btn-produto-codigo">${p.codigo}</span>` : ''}
                    </div>
                    <small class="comanda-btn-produto-cat">${p.categoria || 'Geral'}</small>
                </div>
                <span class="btn-produto-preco">${this.formatarMoeda(p.preco)}</span>
            </button>
        `).join('');
    },

    renderModal() {
        const m = this.mesaAtual;
        const itens = this.itensAtual;
        const total = itens.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);

        let itensHtml = (itens.length === 0) 
            ? `<div class="empty-comanda"><i data-lucide="receipt" class="icon-lucide"></i><p>A comanda está vazia.</p></div>`
            : itens.map(it => `
                <div class="comanda-item-row">
                    <div class="comanda-item-info">
                        <span class="comanda-item-nome">${it.produto_nome}</span>
                        <span class="comanda-item-qtd">Quantidade: ${it.quantidade || 1}</span>
                    </div>
                    <span class="comanda-item-preco comanda-item-preco-large">${this.formatarMoeda(parseFloat(it.preco) * (it.quantidade || 1))}</span>
                    <button class="btn-remove-item" onclick="Comanda.removerItem(${it.id})" title="Remover item">
                        <i data-lucide="trash-2" class="icon-lucide"></i>
                    </button>
                </div>
            `).join('');

        // VIRTUAL ITEMS (FEES)
        if (m.status === 'ocupada' && itens.length > 0) {
            const subtotal = itens.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
            
            // Couvert
            if (this.configEmpresa.couvert > 0 && !this.taxasRemovidas.couvert) {
                itensHtml += `
                    <div class="comanda-item-row virtual-item comanda-virtual-item-couvert">
                        <div class="comanda-item-info">
                            <span class="comanda-item-nome">Couvert Artístico</span>
                            <span class="comanda-item-qtd">(Configuração)</span>
                        </div>
                        <span class="comanda-item-preco comanda-item-preco-large">${this.formatarMoeda(this.configEmpresa.couvert)}</span>
                        <button class="btn-remove-item" onclick="Comanda.toggleTaxa('couvert')" title="Remover do cliente">
                            <i data-lucide="x-circle" class="icon-lucide comanda-btn-remover-taxa-icon"></i>
                        </button>
                    </div>
                `;
            }

            // Taxa
            if (this.configEmpresa.taxa_valor > 0 && !this.taxasRemovidas.taxa) {
                const valorTaxa = (this.configEmpresa.taxa_tipo === 'porcentagem') 
                    ? (subtotal * (this.configEmpresa.taxa_valor / 100)) 
                    : this.configEmpresa.taxa_valor;

                itensHtml += `
                    <div class="comanda-item-row virtual-item comanda-virtual-item-taxa">
                        <div class="comanda-item-info">
                            <span class="comanda-item-nome">Taxa de Serviço (${this.configEmpresa.taxa_valor}${this.configEmpresa.taxa_tipo === 'porcentagem' ? '%' : ' R$'})</span>
                            <span class="comanda-item-qtd">(Configuração)</span>
                        </div>
                        <span class="comanda-item-preco comanda-item-preco-large">${this.formatarMoeda(valorTaxa)}</span>
                        <button class="btn-remove-item" onclick="Comanda.toggleTaxa('taxa')" title="Remover do cliente">
                            <i data-lucide="x-circle" class="icon-lucide comanda-btn-remover-taxa-icon"></i>
                        </button>
                    </div>
                `;
            }
        }

        const btnAcao = m.status === 'livre'
            ? `
                <div class="form-group mb-3 comanda-form-group-left">
                    <label class="form-label comanda-client-label">Nome do Cliente (Opcional)</label>
                    <input type="text" id="input_cliente_nome" class="form-control comanda-client-input" placeholder="Identificar mesa/pessoa...">
                </div>
                
                <div class="comanda-reserve-wrapper">
                    <div class="comanda-reserve-info">
                        <i data-lucide="calendar" class="icon-lucide comanda-reserve-icon"></i>
                        <div>
                            <span class="comanda-reserve-title">Reservar Mesa?</span>
                            <small class="comanda-reserve-hint">Marcar como reserva no mapa</small>
                        </div>
                    </div>
                    <label class="switch switch-sm">
                        <input type="checkbox" id="check_reservada">
                        <span class="slider"></span>
                    </label>
                </div>

                <button class="btn-primary comanda-btn-action-full" onclick="Comanda.abrirMesa(${m.id})"><i data-lucide="play" class="icon-lucide icon-sm"></i> INICIAR ATENDIMENTO</button>
            `
            : `<button class="btn-danger mt-2 comanda-btn-action-full" onclick="Comanda.fecharMesa(${m.id})"><i data-lucide="check-circle" class="icon-lucide icon-sm"></i> FECHAR CONTA</button>`;

        const html = `
            <div class="comanda-modal-body-scroll">
                <div class="comanda-modal-layout">
                    <!-- COLUNA ESQUERDA: EXTRATO -->
                    <div class="comanda-extrato-flex">
                        <div class="comanda-itens-list">
                            <div class="comanda-extrato-header">
                                <h4 class="comanda-extrato-label">Extrato da Mesa</h4>
                                <span class="comanda-extrato-title">Mesa ${m.numero}</span>
                            </div>
                            <div class="comanda-items-container">
                                ${itensHtml}
                            </div>
                        </div>
                        <div>
                            <div class="comanda-total-container">
                                <span class="comanda-total-hint">VALOR TOTAL</span>
                                <span id="comanda-total-val" class="comanda-total-value-huge">${this.formatarMoeda(this.getCalculatedTotal())}</span>
                            </div>
                            ${btnAcao}
                        </div>
                    </div>

                    <!-- COLUNA DIREITA: LANÇAMENTO RÁPIDO -->
                    <div class="comanda-actions-panel comanda-actions-panel-border">
                        <h4 class="comanda-actions-label comanda-actions-header">
                            <i data-lucide="zap" class="icon-lucide icon-sm comanda-icon-bolt"></i> Lançamento Rápido
                        </h4>
                        
                        <div class="comanda-search-wrapper">
                            <i data-lucide="search" class="icon-lucide comanda-search-icon"></i>
                            <input type="text" 
                                id="search-produto" 
                                class="form-control comanda-search-input" 
                                placeholder="Buscar produto..." 
                                onkeyup="Comanda.filtrarProdutos(this.value)">
                        </div>

                        <div id="quick-add-container" class="quick-add-scroll comanda-quick-add-container">
                            <!-- Carregado via JS filtrarProdutos -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="comanda-modal-footer-padded">
                <button class="btn-secondary comanda-btn-radius-10" onclick="UI.closeModal()"><i data-lucide="x" class="icon-lucide icon-sm"></i> Fechar Janela</button>
            </div>
        `;

        UI.showModal(`Gerenciar Mesa ${m.numero} ${m.cliente_nome ? ' — ' + m.cliente_nome : ''}`, html);
        this.renderQuickAdd();
    },

    async abrirMesa(id) {
        const m = this.mesas.find(x => x.id === id);
        if (!m) return;

        const nome = document.getElementById('input_cliente_nome')?.value || '';
        const isReservada = document.getElementById('check_reservada')?.checked ? 1 : 0;
        const nonce = window.COM_NONCES?.abrir_mesa;
        const res = await this.apiPost(`/api/comanda/mesa/abrir/${id}`, { 
            cliente_nome: nome,
            is_reservada: isReservada
        }, nonce);
        
        if (res && res.success) {
            m.status = 'ocupada';
            m.cliente_nome = nome;
            m.is_reservada = isReservada;
            m.total = 0;
            m.itens_count = 0;
            this.renderModal();
            this.renderMesas();
        }
    },

    async lancarItem(nome, preco) {
        if (!await UI.confirm(`Deseja realmente adicionar <strong>${nome}</strong> à comanda?`, { 
            title: 'Confirmar Lançamento', 
            confirmText: 'Sim, Adicionar', 
            type: 'primary',
            icon: 'plus-circle'
        })) {
            return;
        }
        if (this.mesaAtual.status === 'livre') {
            await this.apiPost(`/api/comanda/mesa/abrir/${this.mesaAtual.id}`, {}, window.COM_NONCES?.abrir_mesa);
            const m = this.mesas.find(x => x.id === this.mesaAtual.id);
            if (m) m.status = 'ocupada';
        }

        const nonce = window.COM_NONCES?.lancar_item;
        await this.apiPost(`/api/comanda/mesa/item/${this.mesaAtual.id}`, { nome, preco, quantidade: 1 }, nonce);
        const res = await this.apiGet(`/api/comanda/mesa/${this.mesaAtual.id}`);
        this.itensAtual = (res && res.success) ? (res.itens || []) : [...this.itensAtual, { id: Date.now(), produto_nome: nome, preco, quantidade: 1 }];

        const m = this.mesas.find(x => x.id === this.mesaAtual.id);
        if (m) {
            m.total = this.itensAtual.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
            m.itens_count = this.itensAtual.length;
        }

        this.renderModal();
        this.renderMesas();
    },

    async removerItem(itemId) {
        if (await UI.confirm('Remover este item?', { icon: 'trash' })) {
            const nonce = window.COM_NONCES?.remover_item;
            await fetch(`${window.SITE_URL}/api/comanda/item/${itemId}?nonce=${nonce}`, { method: 'DELETE' });
            this.itensAtual = this.itensAtual.filter(i => i.id !== itemId);
            const m = this.mesas.find(x => x.id === this.mesaAtual.id);
            if (m) {
                m.total = this.itensAtual.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
                m.itens_count = this.itensAtual.length;
            }
            this.renderModal();
            this.renderMesas();
        }
    },

    togglePaymentFields(method) {
        const df = document.getElementById('dinheiro-fields-mesa');
        if (df) df.style.display = method === 'Dinheiro' ? 'block' : 'none';
        if (method !== 'Dinheiro') {
            const vr = document.getElementById('valor_recebido_mesa');
            if (vr) vr.value = '';
            const td = document.getElementById('troco-display-mesa');
            if (td) td.style.display = 'none';
        }
    },

    async toggleTaxa(tipo) {
        const res = await this.apiPost(`/api/comanda/mesa/toggle-taxa/${this.mesaAtual.id}`, { tipo });
        if (res && res.success) {
            this.taxasRemovidas[tipo] = !this.taxasRemovidas[tipo];
            this.renderModal();
        } else {
            UI.showToast('Erro ao alterar taxa', 'error');
        }
    },

    getCalculatedTotal() {
        const subtotal = this.itensAtual.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
        if (subtotal === 0) return 0;

        let total = subtotal;
        
        // Couvert
        if (this.configEmpresa.couvert > 0 && !this.taxasRemovidas.couvert) {
            total += this.configEmpresa.couvert;
        }

        // Taxa
        if (this.configEmpresa.taxa_valor > 0 && !this.taxasRemovidas.taxa) {
            const valorTaxa = (this.configEmpresa.taxa_tipo === 'porcentagem') 
                ? (subtotal * (this.configEmpresa.taxa_valor / 100)) 
                : this.configEmpresa.taxa_valor;
            total += valorTaxa;
        }

        return total;
    },

    calcTroco(total) {
        const vr = document.getElementById('valor_recebido_mesa');
        const td = document.getElementById('troco-display-mesa');
        const tv = document.getElementById('troco-value-mesa');
        if (!vr || !td || !tv) return;

        const recebido = parseFloat(vr.value.replace(',', '.')) || 0;
        const troco = recebido - total;
        if (troco > 0) {
            td.style.display = 'block';
            tv.innerText = this.formatarMoeda(troco);
        } else {
            td.style.display = 'none';
        }
    },

    async fecharMesa(id) {
        const finalTotal = this.getCalculatedTotal();
        const subtotal = this.itensAtual.reduce((s, i) => s + (parseFloat(i.preco) * (i.quantidade || 1)), 0);
        
        let taxasInfo = '';
        if (finalTotal > subtotal) {
            taxasInfo = `
                <div class="comanda-payment-info">
                    <div class="comanda-payment-row">
                        <span>Subtotal:</span>
                        <span>${this.formatarMoeda(subtotal)}</span>
                    </div>
                    ${(this.configEmpresa.couvert > 0 && !this.taxasRemovidas.couvert) ? `
                        <div class="comanda-payment-row">
                            <span>Couvert:</span>
                            <span>${this.formatarMoeda(this.configEmpresa.couvert)}</span>
                        </div>
                    ` : ''}
                    ${(this.configEmpresa.taxa_valor > 0 && !this.taxasRemovidas.taxa) ? `
                        <div class="comanda-payment-row-last">
                            <span>Taxa de Serviço:</span>
                            <span>${this.formatarMoeda((this.configEmpresa.taxa_tipo === 'porcentagem') ? (subtotal * (this.configEmpresa.taxa_valor / 100)) : this.configEmpresa.taxa_valor)}</span>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        const html = `
            <div class="modal-body-scroll comanda-payment-body-scroll">
                <div class="payment-modal-content">
                    <div class="comanda-payment-header-center">
                        <span class="comanda-payment-total-label">Total a Pagar</span>
                        <h2 id="mesa-final-total" class="comanda-payment-total-value">${this.formatarMoeda(finalTotal)}</h2>
                    </div>

                    ${taxasInfo}

                    <div class="form-group mb-4">
                        <label class="form-label comanda-payment-method-label">Forma de Pagamento</label>
                        <div class="payment-methods-grid">
                            <label class="pay-method-opt">
                                <input type="radio" name="pay_method_mesa" value="Dinheiro" checked onchange="Comanda.togglePaymentFields('Dinheiro')">
                                <span><i data-lucide="banknote" class="icon-lucide"></i> Dinheiro</span>
                            </label>
                            <label class="pay-method-opt">
                                <input type="radio" name="pay_method_mesa" value="PIX" onchange="Comanda.togglePaymentFields('PIX')">
                                <span><i data-lucide="qr-code" class="icon-lucide"></i> PIX</span>
                            </label>
                            <label class="pay-method-opt">
                                <input type="radio" name="pay_method_mesa" value="Cartão" onchange="Comanda.togglePaymentFields('Cartao')">
                                <span><i data-lucide="credit-card" class="icon-lucide"></i> Cartão</span>
                            </label>
                        </div>
                    </div>

                    <div id="dinheiro-fields-mesa">
                        <div class="form-group">
                            <label class="form-label">Valor Recebido (Gaveta)</label>
                            <input type="text" id="valor_recebido_mesa" class="form-control mask-money comanda-payment-input-huge" placeholder="0,00" onkeyup="Comanda.calcTroco()">
                        </div>
                        <div id="troco-display-mesa" class="comanda-payment-troco-container" style="display:none;">
                            <span class="comanda-payment-troco-label">TROCO PARA O CLIENTE</span>
                            <span id="troco-value-mesa" class="comanda-payment-troco-value">R$ 0,00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="comanda-payment-footer">
                <button class="btn-secondary comanda-payment-btn-cancel" onclick="UI.closeModal()">Cancelar</button>
                <button class="btn-primary comanda-payment-btn-finish" onclick="Comanda.confirmarFechamento(${id}, ${subtotal})">
                    <i data-lucide="check-circle" class="icon-lucide icon-sm"></i> FINALIZAR CONTA
                </button>
            </div>
        `;

        UI.showModal(`Finalizar Atendimento — Mesa ${this.mesaAtual.numero}`, html);
        this.recalcFechamento(subtotal);
    },

    recalcFechamento(subtotal) {
        const finalTotal = this.getCalculatedTotal();
        document.getElementById('mesa-final-total').innerText = this.formatarMoeda(finalTotal);
        this._currentFinalTotal = finalTotal;
        this.calcTroco();
    },

    calcTroco() {
        const vr = document.getElementById('valor_recebido_mesa');
        const td = document.getElementById('troco-display-mesa');
        const tv = document.getElementById('troco-value-mesa');
        if (!vr || !td || !tv) return;

        const total = this._currentFinalTotal || 0;
        const recebido = parseFloat(vr.value.replace('.', '').replace(',', '.')) || 0;
        const troco = recebido - total;
        if (troco > 0) {
            td.style.display = 'block';
            tv.innerText = this.formatarMoeda(troco);
        } else {
            td.style.display = 'none';
        }
    },

    async confirmarFechamento(id, subtotal) {
        const metodo = document.querySelector('input[name="pay_method_mesa"]:checked').value;
        
        let cover = (this.configEmpresa.couvert > 0 && !this.taxasRemovidas.couvert) ? this.configEmpresa.couvert : 0;
        let taxaFinal = 0;
        if (this.configEmpresa.taxa_valor > 0 && !this.taxasRemovidas.taxa) {
            taxaFinal = (this.configEmpresa.taxa_tipo === 'porcentagem') 
                ? (subtotal * (this.configEmpresa.taxa_valor / 100)) 
                : this.configEmpresa.taxa_valor;
        }

        const nonce = window.COM_NONCES?.fechar_mesa;
        
        const res = await this.apiPost(`/api/comanda/mesa/fechar/${id}`, { 
            metodo, 
            taxa_servico: taxaFinal,
            cover: cover
        }, nonce);

        if (res && res.success) {
            UI.showToast('Mesa finalizada com sucesso!');
            const m = this.mesas.find(x => x.id === id);
            if (m) { m.status = 'livre'; m.total = 0; m.itens_count = 0; }
            this.itensAtual = [];
            UI.closeModal();
            this.renderMesas();
        } else {
            UI.showToast('Erro ao fechar mesa', 'error');
        }
    },

    showQrCode(numero) {
        const url = `${window.SITE_URL}/${window.COMPANY_SLUG}/mesa/${numero}`;
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
        
        const html = `
            <div class="qr-code-body">
                <div class="qr-code-img-wrapper">
                    <img src="${qrUrl}" class="qr-code-img">
                </div>
                <h3 class="qr-code-title">QR Code da Mesa ${numero}</h3>
                <p class="qr-code-hint">O cliente poderá escanear este código para acompanhar o consumo em tempo real.</p>
                
                <div class="form-group mb-4">
                    <label class="form-label qr-code-input-label">Link da Comanda:</label>
                    <input type="text" class="form-control qr-code-input" value="${url}" readonly>
                </div>

                <div class="qr-code-actions">
                    <a href="${qrUrl}&format=png" download="Mesa_${numero}_QR.png" class="btn-primary qr-code-btn-link">
                        <i data-lucide="download" class="icon-lucide icon-sm"></i> BAIXAR QR
                    </a>
                    <button class="btn-secondary qr-code-btn-test" onclick="window.open('${url}', '_blank')">
                        <i data-lucide="external-link" class="icon-lucide icon-sm"></i> TESTAR LINK
                    </button>
                </div>
            </div>
        `;

        UI.showModal(`Imprimir QR Code - Mesa ${numero}`, html);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Comanda.init());
} else {
    Comanda.init();
}
