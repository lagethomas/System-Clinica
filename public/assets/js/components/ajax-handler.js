/**
 * AJAX Handler Component
 * Handles CSRF auto-injection and UI.request helper
 */
(function () {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (!metaToken) return;
    const csrfToken = metaToken.getAttribute('content');
    if (!csrfToken) return;

    const origFetch = window.fetch;
    window.fetch = function (input, init) {
        init = init || {};
        const method = (init.method || 'GET').toUpperCase();
        
        // Only inject for relative URLs or internal domain
        const url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
        const isInternal = !url.startsWith('http') || url.startsWith(window.location.origin);
        
        if (isInternal) {
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                init.headers.set('X-CSRF-Token', csrfToken);
            } else {
                init.headers['X-CSRF-Token'] = csrfToken;
            }
            
            // For POST/PUT with FormData, append token field
            if (['POST', 'PUT'].includes(method)) {
                if (init.body instanceof FormData && !init.body.has('csrf_token')) {
                    init.body.append('csrf_token', csrfToken);
                }
            }
        }
        return origFetch.call(this, input, init);
    };
})();

if (typeof UI !== 'undefined') {
    UI.request = async function (url, data = null) {
        try {
            let body = data;
            let method = data ? 'POST' : 'GET';
            const headers = { 'X-Requested-With': 'XMLHttpRequest' };

            // Compatibility: if data is an options object { method, body }
            if (data && typeof data === 'object' && data.method && data.body) {
                method = data.method.toUpperCase();
                body = data.body;
            }

            if (body && !(body instanceof FormData)) {
                const newBody = new FormData();
                Object.keys(body).forEach(key => {
                    const val = body[key];
                    if (val !== null && val !== undefined) {
                        newBody.append(key, val);
                    }
                });
                body = newBody;
            }

            const options = {
                method: method,
                body: method !== 'GET' ? body : null,
                headers: headers
            };

            const response = await fetch(url, options);
            const contentType = response.headers.get("content-type");

            if (contentType && contentType.indexOf("application/json") !== -1) {
                const result = await response.json();
                if (!result.success) {
                    this.showToast(result.message || 'Erro ao processar requisição', result.type || 'error');
                }
                return result;
            } else {
                const text = await response.text();
                console.error('Non-JSON Response:', text);
                if (text.includes('Fatal error') || text.includes('Parse error')) {
                    this.showToast('❌ Erro crítico no motor do sistema.', 'error');
                } else {
                    this.showToast('📡 Falha técnica na comunicação com o servidor.', 'error');
                }
                return null;
            }
        } catch (error) {
            console.error('Request Error:', error);
            this.showToast('Erro de conexão com o servidor', 'error');
            return null;
        }
    };
}

// Global Form Interceptor for .ajax-form
document.addEventListener('submit', async (e) => {
    if (e.target.classList.contains('ajax-form')) {
        e.preventDefault();
        const form = e.target;
        const action = form.getAttribute('action') || window.location.href;
        const formData = new FormData(form);

        const btn = e.submitter || form.querySelector('button[type="submit"]');
        const originalText = btn ? btn.innerHTML : '';

        if (btn && btn.name) {
            formData.append(btn.name, btn.value || '1');
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" class="icon-lucide animate-spin spinner-lucide"></i> Processando...';
            if(window.lucide) lucide.createIcons({ root: btn });
        }

        const result = await UI.request(action, formData);

        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }

        if (result && result.success) {
            UI.showToast(result.message || 'Operação realizada com sucesso', result.type || 'success');
            form.dispatchEvent(new CustomEvent('ajaxSuccess', { detail: result }));
            if (result.noClose || (result.data && result.data.noClose)) return;
            UI.closeModal();

            if (result.redirect) {
                setTimeout(() => window.location.href = result.redirect, 1000);
            } else if (!result.noReload) {
                setTimeout(() => window.location.reload(), 1200);
            }
        }
    }
});
