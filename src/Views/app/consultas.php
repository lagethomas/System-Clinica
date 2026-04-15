<?php
/** @var array $consultas */
/** @var array $tutores */
/** @var array $pets */
/** @var string $search */
/** @var string $nonce_save */
/** @var string $nonce_delete */
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Agenda de Atendimentos</h2>
        <p style="color: var(--text-muted);">Acompanhamento de atendimentos agendados e concluídos.</p>
    </div>
    <button class="btn-primary" onclick="openConsultaModal()">
        <i data-lucide="calendar-plus" class="icon-lucide"></i> Agendar Novo Horário
    </button>
</div>

<!-- FullCalendar CDN -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<div class="card p-4">
    <div id='calendar-container' style="min-height: 600px;">
        <div id='calendar'></div>
    </div>
</div>

<style>
    :root {
        --fc-border-color: var(--border);
        --fc-button-bg-color: var(--bg-card);
        --fc-button-border-color: var(--border);
        --fc-button-text-color: var(--text-main);
        --fc-button-hover-bg-color: var(--primary);
        --fc-button-hover-border-color: var(--primary);
        --fc-button-active-bg-color: var(--primary);
        --fc-event-bg-color: var(--primary);
        --fc-event-border-color: var(--primary);
        --fc-page-bg-color: transparent;
    }
    .fc { font-family: 'Outfit', sans-serif; }
    .fc-header-toolbar { margin-bottom: 25px !important; }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700; color: var(--primary); }
    .fc-button { border-radius: 10px !important; font-weight: 600 !important; text-transform: capitalize !important; }
    .fc-event { cursor: pointer; padding: 2px 5px; border-radius: 6px; font-size: 11px; font-weight: 600; border: none !important; }
    .fc-daygrid-day-number { color: var(--text-muted); font-size: 13px; text-decoration: none !important; font-weight: 500; }
    .fc-daygrid-day.fc-day-today { background: rgba(var(--primary-rgb), 0.05) !important; }
    .fc-col-header-cell-cushion { color: var(--text-muted); font-weight: 600; font-size: 12px; text-transform: uppercase; text-decoration: none !important; }
    .fc-theme-standard td, .fc-theme-standard th { border: 1px solid var(--border); }
</style>

<script>
// Global tutores and pets list
const tutoresList = <?php echo json_encode($tutores); ?>;
const petsList = <?php echo json_encode($pets ?? []); ?>;

function openConsultaModal(data = null) {
    const isEdit = data !== null && data.id;
    
    // Fallback in case lists are somehow not ready
    const localTutores = typeof tutoresList !== 'undefined' ? tutoresList : [];
    
    const html = `
        <form class="ajax-form" id="form-consulta" action="<?php echo SITE_URL; ?>/api/consultas/save">
            <div class="modal-body-scroll">
                <input type="hidden" name="id" value="${isEdit ? data.id : ''}">
                <input type="hidden" name="nonce" value="<?php echo $nonce_save; ?>">
                
                <h6 class="mb-3 d-flex align-items-center gap-2" style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;">
                    <i data-lucide="calendar" class="icon-lucide icon-xs"></i> Dados do Agendamento
                </h6>
                
                <div class="form-grid-2 mb-4">
                    <div class="form-group">
                        <label class="form-label">Paciente (Tutor)</label>
                        <div class="search-input-box">
                            <i data-lucide="user" class="icon-lucide icon-sm" style="color: var(--primary);"></i>
                            <input type="text" id="tutor-search" class="form-control" placeholder="Busque por Nome ou CPF..." value="${isEdit ? (data.tutor_nome || '') : ''}" required autocomplete="off">
                            <input type="hidden" id="tutor_id" name="tutor_id" value="${isEdit ? data.tutor_id : ''}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pet</label>
                        <select name="pet_id" id="pet_id_select" class="form-control">
                            <option value="">Selecione um tutor...</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2 mb-4">
                    <div class="form-group">
                        <label class="form-label">Data e Hora</label>
                        <input type="datetime-local" name="data_consulta" class="form-control"
                               value="${(data && data.data_consulta) ? data.data_consulta.replace(' ', 'T').substring(0, 16) : '<?php echo date('Y-m-d\TH:i'); ?>'}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="agendada" ${isEdit && data.status == 'agendada' ? 'selected' : ''}>Agendada</option>
                            <option value="concluida" ${isEdit && data.status == 'concluida' ? 'selected' : ''}>Concluída</option>
                            <option value="cancelada" ${isEdit && data.status == 'cancelada' ? 'selected' : ''}>Cancelada</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2 mb-4">
                    <div class="form-group">
                        <label class="form-label">Motivo da Visita</label>
                        <input type="text" name="motivo" class="form-control" value="${isEdit ? (data.motivo || '') : ''}" required placeholder="Ex: Vacinas, Check-up, Emergência...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor cobrado (R$)</label>
                        <input type="text" name="valor" class="form-control mask-money" value="${isEdit ? (data.valor ? data.valor.toString().replace('.', ',') : '') : ''}" placeholder="0,00">
                    </div>
                </div>

            </div>

            <div class="modal-footer mt-4 d-flex justify-content-between">
                <div>
                    ${isEdit ? `<button type="button" class="btn-danger" onclick="deleteConsulta(${data.id})">Excluir Agendamento</button>` : ''}
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">${isEdit ? 'Atualizar Agendamento' : 'Criar agendamento'}</button>
                </div>
            </div>
        </form>
    `;

    UI.showModal(isEdit ? 'Remarcar / Editar Consulta' : 'Novo Agendamento Clínico', html);
    lucide.createIcons();
    
    // Iniciar Máscaras para os campos do modal
    if (UI.initMasks) UI.initMasks(document.getElementById('form-consulta'));

    // ── Tutor Autocomplete ──
    setupFloatingAutocomplete({
        inputId: 'tutor-search',
        hiddenId: 'tutor_id',
        data: localTutores,
        searchKey: 'search_string',
        displayKey: 'nome',
        subKey: 'cpf',
        icon: 'user'
    });

    // ── Pet Selection Logic ──
    const tutorIdInput = document.getElementById('tutor_id');
    const petSelect = document.getElementById('pet_id_select');

    const updatePetSelect = (tutorId, selectedPetId = null) => {
        petSelect.innerHTML = '<option value="">-- Selecione o Pet --</option>';
        if (!tutorId) return;

        const filtered = petsList.filter(p => p.tutor_id == tutorId);
        if (filtered.length === 0) {
            petSelect.innerHTML = '<option value="">Nenhum pet encontrado</option>';
            return;
        }

        filtered.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.nome;
            if (selectedPetId && p.id == selectedPetId) opt.selected = true;
            petSelect.appendChild(opt);
        });
    };

    tutorIdInput.addEventListener('change', function() {
        updatePetSelect(this.value);
    });

    // Trigger update if hidden ID is already set (e.g. edit mode)
    if (tutorIdInput.value) {
        updatePetSelect(tutorIdInput.value, isEdit ? data.pet_id : null);
    }
}

// ── Calendar Operations ──
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const consultas = <?php echo json_encode($consultas); ?>;
    
    const statusColors = {
        'agendada': '#f59e0b',
        'concluida': '#10b981',
        'cancelada': '#ef4444'
    };

    const events = consultas.map(c => ({
        id: c.id,
        title: `${c.tutor_nome}${c.pet_nome ? ' (' + c.pet_nome + ')' : ''}`,
        start: c.data_consulta.replace(' ', 'T'),
        backgroundColor: statusColors[c.status] || '#6366f1',
        extendedProps: c
    }));

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        events: events,
        eventClick: function(info) {
            openConsultaModal(info.event.extendedProps);
        },
        dateClick: function(info) {
            openConsultaModal({ data_consulta: info.dateStr + ' 09:00:00' });
        },
        height: 'auto',
        nowIndicator: true,
        editable: false
    });

    calendar.render();
});

async function deleteConsulta(id) {
    if (await UI.confirm('Remover este agendamento?')) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/consultas/delete', { id, nonce: '<?php echo $nonce_delete; ?>' });
        if (res && res.success) window.location.reload();
    }
}
</script>
