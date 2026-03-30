/**
 * Input Masks Component
 */
if (typeof UI !== 'undefined') {
    UI.maskPhone = function (el) {
        el.addEventListener('input', (e) => {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    };

    UI.maskCpfCnpj = function (el) {
        el.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 14) value = value.slice(0, 14);

            if (value.length <= 11) {
                let x = value.match(/(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2})/);
                e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' + x[3] : '') + (x[4] ? '-' + x[4] : '');
            } else {
                let x = value.match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
                e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' + x[3] : '') + (x[4] ? '/' + x[4] : '') + (x[5] ? '-' + x[5] : '');
            }
        });
    };

    UI.maskZip = function (el) {
        el.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.slice(0, 8);
            let x = value.match(/(\d{0,5})(\d{0,3})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
        });
    };

    UI.maskNumber = function (el) {
        el.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    };

    UI.maskWeight = function (el) {
        el.addEventListener('input', (e) => {
            // Permite apenas números e um ponto/vírgula
            let v = e.target.value.replace(/[^\d.,]/g, '');
            v = v.replace(',', '.');
            // Garante apenas um ponto decimal
            const parts = v.split('.');
            if (parts.length > 2) v = parts[0] + '.' + parts.slice(1).join('');
            e.target.value = v;
        });
    };

    UI.maskMoney = function (el) {
        if (el.dataset.maskInit) return;
        el.dataset.maskInit = 'true';

        const format = (v) => {
            let val = v.replace(/\D/g, '');
            if (val === "") return "";
            return new Intl.NumberFormat('pt-BR', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            }).format(parseFloat(val) / 100);
        };

        // Format initial value
        if (el.value) el.value = format(el.value);

        el.addEventListener('input', (e) => {
            e.target.value = format(e.target.value);
        });
    };

    UI.initMasks = function (container = document) {
        container.querySelectorAll('.mask-phone').forEach(el => this.maskPhone(el));
        container.querySelectorAll('.mask-document, .mask-doc').forEach(el => this.maskCpfCnpj(el));
        container.querySelectorAll('.mask-zip').forEach(el => this.maskZip(el));
        container.querySelectorAll('.mask-money').forEach(el => this.maskMoney(el));
        container.querySelectorAll('.mask-number').forEach(el => this.maskNumber(el));
        container.querySelectorAll('.mask-weight').forEach(el => this.maskWeight(el));
    };
}
