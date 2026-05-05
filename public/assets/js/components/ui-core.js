/**
 * UI Core Component
 * Handles Toasts, Modals and základ UI interactions
 */
const UI = {
    modal: document.getElementById('global-modal'),
    modalTitle: document.getElementById('modal-title'),
    modalBody: document.getElementById('modal-body'),
    modalFooter: document.getElementById('modal-footer'),
    toastContainer: document.getElementById('toast-container'),

    showModal(title, html) {
        if (!this.modal) return;
        this.modalTitle.textContent = title;

        const temp = document.createElement('div');
        temp.innerHTML = html;

        const footer = temp.querySelector('.modal-footer');
        if (footer && this.modalFooter) {
            this.modalFooter.innerHTML = footer.innerHTML;
            this.modalFooter.style.display = 'flex';
            footer.remove();

            const form = temp.querySelector('form');
            if (form) {
                let formId = form.getAttribute('id');
                if (!formId) {
                    formId = 'modal-dynamic-form-' + Date.now();
                    form.setAttribute('id', formId);
                }
                this.modalFooter.querySelectorAll('button[type="submit"]').forEach(btn => {
                    btn.setAttribute('form', formId);
                    btn.onclick = (e) => {
                        e.preventDefault();
                        const realForm = document.getElementById(formId);
                        if (realForm) {
                            if (realForm.reportValidity && !realForm.reportValidity()) return;
                            if (realForm.requestSubmit) {
                                realForm.requestSubmit(btn);
                            } else {
                                realForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                            }
                        }
                    };
                });
            }
        } else if (this.modalFooter) {
            this.modalFooter.innerHTML = '';
            this.modalFooter.style.display = 'none';
        }

        this.modalBody.innerHTML = temp.innerHTML;
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        if (typeof this.initAutocomplete === 'function') this.initAutocomplete();
        if (typeof this.initMasks === 'function') this.initMasks();
        if (typeof this.initPasswordToggles === 'function') this.initPasswordToggles();
        
        if (window.lucide) {
            lucide.createIcons();
        }
    },

    closeModal() {
        const modal = document.querySelector('.modal-overlay.active') || this.modal;
        if (!modal) return;
        modal.classList.remove('active');
        if (modal === this.modal) {
            this.modalBody.innerHTML = '';
            if (this.modalFooter) {
                this.modalFooter.innerHTML = '';
                this.modalFooter.style.display = 'none';
            }
        }
        document.body.style.overflow = '';
    },

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        let icon = 'check-circle';
        if (type === 'error') icon = 'x-circle';
        if (type === 'warning') icon = 'alert-triangle';
        if (type === 'info') icon = 'info';

        toast.innerHTML = `
            <i data-lucide="${icon}" class="icon-lucide" style="margin-right:8px;"></i> 
            <span class="toast-message">${message}</span>
            <button class="toast-close" title="Fechar">&times;</button>
        `;

        if (!this.toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
            this.toastContainer = container;
        }

        this.toastContainer.appendChild(toast);
        if(window.lucide) {
            lucide.createIcons({
                nameAttr: 'data-lucide',
                root: toast
            });
        }

        const removeToast = () => {
            if (!toast.parentElement) return;
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 300);
        };

        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) closeBtn.onclick = (e) => { e.stopPropagation(); removeToast(); };
        setTimeout(removeToast, 4000);
    },

    confirm(message, options = {}) {
        const defaults = {
            title: 'Confirmar Exclusão',
            confirmText: 'Sim, Excluir',
            cancelText: 'Cancelar',
            type: 'danger',
            icon: 'alert-triangle'
        };
        const config = { ...defaults, ...options };
        const colors = {
            danger: { bg: 'rgba(255, 77, 77, 0.1)', color: '#ff4d4d' },
            success: { bg: 'rgba(16, 185, 129, 0.1)', color: '#10b981' },
            info: { bg: 'rgba(14, 165, 233, 0.1)', color: '#0ea5e9' },
            primary: { bg: 'rgba(var(--primary-rgb), 0.1)', color: 'var(--primary)' }
        };
        const activeColor = colors[config.type] || colors.danger;

        return new Promise((resolve) => {
            const html = `
                <div class="confirm-container" style="padding: 10px 0; text-align: center;">
                    <div class="confirm-icon-circle" style="background: ${activeColor.bg}; color: ${activeColor.color}; width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 28px; box-shadow: 0 10px 20px -5px rgba(0,0,0,0.3);">
                        <i data-lucide="${config.icon.replace('fa-', '')}" class="icon-lucide"></i>
                    </div>
                    <p class="confirm-message" style="font-size: 16px; font-weight: 500; line-height: 1.6; margin-bottom: 35px; color: var(--text-main); padding: 0 10px;">${message}</p>
                    <div class="confirm-buttons" style="display: flex; gap: 15px; justify-content: center;">
                        <button class="btn-secondary" id="confirm-cancel" style="padding: 12px 30px; border-radius: 30px; font-weight: 600; font-size: 14px; min-width: 130px; transition: 0.3s; color: var(--text-muted);">${config.cancelText}</button>
                        <button class="btn-primary" id="confirm-ok" style="background: ${activeColor.color}; border: 1px solid ${activeColor.color}; color: var(--text-on-primary); padding: 12px 30px; border-radius: 30px; font-weight: 800; font-size: 14px; min-width: 130px; transition: 0.3s; box-shadow: 0 4px 15px ${activeColor.bg};">${config.confirmText}</button>
                    </div>
                </div>
            `;
            this.showModal(config.title, html);
            if(window.lucide) lucide.createIcons();
            const okBtn = document.getElementById('confirm-ok');
            const cancelBtn = document.getElementById('confirm-cancel');
            okBtn.onclick = () => { this.closeModal(); resolve(true); };
            cancelBtn.onclick = () => { this.closeModal(); resolve(false); };
        });
    },

    confirmAction(title, message, options = {}) {
        return this.confirm(message, { title, ...options });
    },

    openDropdown(e, html) {
        e.stopPropagation();
        this.closeDropdown();

        const dropdown = document.createElement('div');
        dropdown.className = 'premium-dropdown';
        dropdown.innerHTML = html;
        document.body.appendChild(dropdown);

        const rect = e.currentTarget.getBoundingClientRect();
        const dropdownWidth = 180;
        
        dropdown.style.position = 'absolute';
        dropdown.style.zIndex = '9999';
        dropdown.style.top = (rect.bottom + window.scrollY + 5) + 'px';
        dropdown.style.left = (rect.right - dropdownWidth + window.scrollX) + 'px';
        dropdown.style.width = dropdownWidth + 'px';

        setTimeout(() => dropdown.classList.add('active'), 10);

        const closeDropdown = (event) => {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
                setTimeout(() => dropdown.remove(), 200);
                document.removeEventListener('click', closeDropdown);
            }
        };

        document.addEventListener('click', closeDropdown);
        if (window.lucide) lucide.createIcons({ root: dropdown });
    },

    closeDropdown() {
        const existing = document.querySelector('.premium-dropdown');
        if (existing) existing.remove();
    },

    prompt(message, options = {}) {
        const defaults = { title: 'Entrada de Dados', confirmText: 'Confirmar', cancelText: 'Cancelar', placeholder: '', defaultValue: '' };
        const config = { ...defaults, ...options };

        return new Promise((resolve) => {
            const html = `
                <div class="prompt-container" style="padding: 5px 0;">
                    <p class="prompt-message" style="margin-bottom: 20px; font-size: 14px; color: var(--text-muted); line-height: 1.5;">${message}</p>
                    <input type="text" id="prompt-input" class="form-control" style="margin-bottom: 25px; height: 50px; font-size: 15px;" placeholder="${config.placeholder}" value="${config.defaultValue}">
                    <div class="prompt-buttons" style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button class="btn-secondary" id="prompt-cancel" style="padding: 10px 25px; border-radius: 30px; font-weight: 600;">${config.cancelText}</button>
                        <button class="btn-primary" id="prompt-ok" style="padding: 10px 30px; border-radius: 30px; font-weight: 800; color: var(--text-on-primary);">${config.confirmText}</button>
                    </div>
                </div>
            `;
            this.showModal(config.title, html);
            const input = document.getElementById('prompt-input');
            const okBtn = document.getElementById('prompt-ok');
            const cancelBtn = document.getElementById('prompt-cancel');
            setTimeout(() => input.focus(), 100);
            okBtn.onclick = () => { const val = input.value; this.closeModal(); resolve(val); };
            cancelBtn.onclick = () => { this.closeModal(); resolve(null); };
            input.onkeyup = (e) => { if (e.key === 'Enter') okBtn.click(); };
        });
    },

    handleUrlMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            const messages = {
                'success': 'Operação realizada com sucesso!',
                'saved': 'Alterações salvas com sucesso!',
                'deleted': 'Item removido com sucesso!',
                'error': 'Ocorreu um erro ao processar a solicitação.',
                'invoice_created': 'Fatura gerada com sucesso!',
                'updated': 'Configurações atualizadas!'
            };
            const toastMsg = messages[msg] || decodeURIComponent(msg.replace(/_/g, ' '));
            const type = msg.includes('error') ? 'error' : 'success';
            setTimeout(() => this.showToast(toastMsg, type), 500);
            const newUrl = window.location.pathname + window.location.search.replace(/([&?]msg=[^&]*)/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }
    },

    initAutocomplete() {
        document.querySelectorAll('select.tom-select').forEach(el => {
            if (el.tomselect) return;
            if (typeof TomSelect !== 'undefined') {
                new TomSelect(el, {
                    plugins: ['remove_button'],
                    persist: false,
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 50,
                    sortField: { field: "text", direction: "asc" }
                });
            }
        });
    },
    
    togglePassword(btn, targetId) {
        const input = document.getElementById(targetId);
        if (!input) return;
        
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        
        btn.innerHTML = `<i data-lucide="${isPassword ? 'eye-off' : 'eye'}" class="icon-lucide"></i>`;
        if(window.lucide) {
            lucide.createIcons({
                attrs: { class: 'icon-lucide' },
                root: btn
            });
        }
    },

    generatePassword(targetId) {
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let retVal = "";
        for (let i = 0, n = charset.length; i < 12; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * n));
        }
        const input = document.getElementById(targetId);
        if (input) {
            input.value = retVal;
            input.type = 'text'; // Show it so they can see it
            this.showToast('Senha gerada e aplicada!');
            
            // Try to find the toggle button to update icon to eye-off
            const wrapper = input.parentElement;
            const toggleBtn = wrapper.querySelector('.password-toggle-btn');
            if (toggleBtn) {
                toggleBtn.innerHTML = '<i data-lucide="eye-off" class="icon-lucide"></i>';
                if(window.lucide) lucide.createIcons({ root: toggleBtn });
            }
        }
    },

    initPasswordToggles() {
        document.querySelectorAll('input[type="password"]').forEach(input => {
            if (input.dataset.toggleInit) return;
            input.dataset.toggleInit = 'true';
            
            // Create wrapper if not already wrapped
            let wrapper = input.parentElement;
            if (!wrapper.classList.contains('password-toggle-wrapper')) {
                wrapper = document.createElement('div');
                wrapper.className = 'password-toggle-wrapper relative';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
            }
            
            // Create toggle button
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'password-toggle-btn';
            btn.title = 'Mostrar/Ocultar Senha';
            btn.innerHTML = '<i data-lucide="eye" class="icon-lucide"></i>';
            
            if (!input.id) input.id = 'pwd-' + Math.random().toString(36).substr(2, 9);
            
            btn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.togglePassword(btn, input.id);
            };
            
            wrapper.appendChild(btn);
            
            // Initial Lucide render for the new toggle button
            if(window.lucide) lucide.createIcons({ root: btn });
            
            // Also render existing shuffle icon if present
            const genBtn = wrapper.querySelector('.btn-generate-password');
            if (genBtn) {
                btn.style.right = '45px';
                if(window.lucide) lucide.createIcons({ root: genBtn });
            }
        });
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showToast('Link copiado para a área de transferência!');
        }).catch(err => {
            console.error('Erro ao copiar: ', err);
            this.showToast('Erro ao copiar link', 'error');
        });
    },

    showQRModal(url, title = 'QRCode de Acesso') {
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
        const html = `
            <div style="text-align: center; padding: 20px;">
                <div style="background: #fff; padding: 20px; border-radius: 15px; display: inline-block; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    <img src="${qrUrl}" alt="QRCode" style="width: 200px; height: 200px; display: block;">
                </div>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 25px;">Aponte a câmera do celular para acessar o cardápio.</p>
                <div class="form-group mb-4">
                    <input type="text" class="form-control" value="${url}" readonly style="text-align: center; background: rgba(255,255,255,0.03);">
                </div>
                <button class="btn-primary" onclick="UI.copyToClipboard('${url}')" style="width: 100%;">
                    <i data-lucide="copy" class="icon-lucide icon-sm"></i> COPIAR LINK
                </button>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" style="width: 100%;" onclick="UI.closeModal()">FECHAR</button>
            </div>
        `;
        this.showModal(title, html);
    }
};

window.UI = UI;
