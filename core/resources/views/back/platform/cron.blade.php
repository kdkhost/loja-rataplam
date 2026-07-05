@extends('master.back')

@section('styles')
<style>
    .cron-page {
        --cron-border: #e8edf5;
        --cron-muted: #6f7a8a;
        --cron-soft: #f7f9fd;
        --cron-strong: #1f2937;
    }
    .cron-page .card {
        border: 1px solid var(--cron-border);
        box-shadow: 0 8px 24px rgba(23, 125, 255, .04);
    }
    .cron-titlebar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .cron-titlebar__meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .cron-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 30px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #edf5ff;
        color: #177dff;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
    }
    .cron-stat {
        min-height: 112px;
    }
    .cron-stat .card-body {
        display: flex;
        align-items: center;
        gap: 14px;
        height: 100%;
    }
    .cron-stat__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        color: #fff;
        font-size: 18px;
    }
    .cron-stat__label {
        color: var(--cron-muted);
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .cron-stat__value {
        color: var(--cron-strong);
        font-size: 24px;
        font-weight: 700;
        line-height: 1;
        margin: 0;
    }
    .cron-command-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
    }
    .cron-command-box {
        display: block;
        width: 100%;
        min-height: 44px;
        padding: 13px 14px;
        border-radius: 8px;
        background: #f5f7fb;
        border: 1px solid var(--cron-border);
        color: var(--cron-strong);
        white-space: normal;
        word-break: break-word;
    }
    .cron-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: var(--cron-strong);
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 16px;
    }
    .cron-section-title i {
        color: #177dff;
    }
    .cron-schedule-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        gap: 10px;
    }
    .cron-expression-preview {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        max-width: 100%;
        padding: 8px 10px;
        border-radius: 8px;
        background: #fff7e8;
        border: 1px solid #ffe0a8;
        color: #9a6400;
        font-size: 12px;
        font-weight: 700;
        white-space: normal;
        word-break: break-word;
    }
    .cron-task-list {
        display: grid;
        gap: 14px;
    }
    .cron-task-card {
        border: 1px solid var(--cron-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }
    .cron-task-card.is-due {
        border-left: 4px solid #177dff;
    }
    .cron-task-card.has-error {
        border-left: 4px solid #f3545d;
    }
    .cron-task-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 18px;
        background: #fbfcff;
        border-bottom: 1px solid var(--cron-border);
    }
    .cron-task-title {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .cron-task-title h5 {
        margin: 0;
        color: var(--cron-strong);
        font-size: 15px;
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }
    .cron-task-title i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #edf5ff;
        color: #177dff;
    }
    .cron-task-badges {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 6px;
    }
    .cron-task-body {
        display: grid;
        grid-template-columns: minmax(250px, .95fr) minmax(360px, 1.4fr) minmax(230px, .8fr);
        gap: 18px;
        padding: 18px;
    }
    .cron-task-section {
        min-width: 0;
    }
    .cron-runtime-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--cron-border);
    }
    .cron-runtime-row:first-child {
        padding-top: 0;
    }
    .cron-runtime-row b {
        color: var(--cron-strong);
        font-weight: 700;
    }
    .cron-output {
        max-height: 116px;
        overflow: auto;
        padding: 10px;
        border-radius: 8px;
        background: #f6f8fb;
        border: 1px solid var(--cron-border);
        color: #394150;
        white-space: pre-wrap;
    }
    .cron-task-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-top: 1px solid var(--cron-border);
        background: #fff;
    }
    .cron-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }
    .cron-empty {
        padding: 42px 20px;
        text-align: center;
        color: var(--cron-muted);
    }
    .cron-empty i {
        display: block;
        margin-bottom: 10px;
        color: #b7c1d1;
        font-size: 28px;
    }
    .cron-page .form-control {
        min-height: 42px;
    }
    .cron-page textarea.form-control {
        min-height: auto;
    }
    html.admin-theme-dark .cron-page {
        --cron-border: #2b3446;
        --cron-muted: #a8b3c6;
        --cron-soft: #182033;
        --cron-strong: #f1f5fb;
    }
    html.admin-theme-dark .cron-task-card,
    html.admin-theme-dark .cron-task-footer {
        background: #141b2d;
    }
    html.admin-theme-dark .cron-task-header,
    html.admin-theme-dark .cron-command-box,
    html.admin-theme-dark .cron-output {
        background: #182033;
    }
    html.admin-theme-dark .cron-command-box,
    html.admin-theme-dark .cron-output {
        color: #e5ebf5;
    }
    html.admin-theme-dark .cron-expression-preview {
        background: rgba(255, 165, 52, .12);
        border-color: rgba(255, 165, 52, .28);
        color: #ffc36b;
    }
    @media (max-width: 1399px) {
        .cron-task-body {
            grid-template-columns: 1fr 1fr;
        }
        .cron-task-section--runtime {
            grid-column: 1 / -1;
        }
    }
    @media (max-width: 1199px) {
        .cron-schedule-grid {
            grid-template-columns: repeat(2, minmax(120px, 1fr));
        }
    }
    @media (max-width: 767px) {
        .cron-titlebar,
        .cron-command-panel,
        .cron-task-header,
        .cron-task-footer {
            align-items: stretch;
            flex-direction: column;
        }
        .cron-command-panel,
        .cron-task-body {
            grid-template-columns: 1fr;
        }
        .cron-task-badges,
        .cron-actions {
            justify-content: flex-start;
        }
    }
    @media (max-width: 575px) {
        .cron-schedule-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@php
    $applySelect = function ($name, $selected, $class = 'form-control cpanel-field', $form = null) use ($selectOptions) {
        $formAttr = $form ? ' form="'.$form.'"' : '';
        $html = '<select name="'.$name.'" class="'.$class.'" data-cron-field="'.$name.'"'.$formAttr.'>';
        foreach ($selectOptions[$name] as $value => $label) {
            $html .= '<option value="'.$value.'" '.((string) $selected === (string) $value ? 'selected' : '').'>'.$label.'</option>';
        }
        return $html.'</select>';
    };

    $statusLabels = [
        'success' => 'Sucesso',
        'error' => 'Erro',
    ];
    $statusBadges = [
        'success' => 'success',
        'error' => 'danger',
    ];
    $totalTasks = $tasks->count();
    $activeTasks = $tasks->filter(function ($task) {
        return (bool) $task->is_active;
    })->count();
    $dueTasks = $tasks->filter->isDue()->count();
    $errorTasks = $tasks->where('last_status', 'error')->count();
    $nextTask = $tasks->filter(function ($task) {
        return $task->is_active && $task->next_run_at;
    })->sortBy('next_run_at')->first();
@endphp

<div class="container-fluid cron-page">
    <div class="card mb-4">
        <div class="card-body">
            <div class="cron-titlebar">
                <div>
                    <h3 class="mb-1 bc-title"><b>Central Interna de Cron</b></h3>
                    <p class="mb-0 text-muted">Rotinas operacionais do sistema com agendamento no padrao WHM/cPanel.</p>
                </div>
                <div class="cron-titlebar__meta">
                    <span class="cron-pill"><i class="fas fa-clock"></i>{{ config('app.timezone') }}</span>
                    @if ($nextTask)
                        <span class="cron-pill"><i class="fas fa-forward"></i>{{ $nextTask->next_run_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card cron-stat">
                <div class="card-body">
                    <span class="cron-stat__icon bg-primary"><i class="fas fa-tasks"></i></span>
                    <div>
                        <p class="cron-stat__label">Tarefas registradas</p>
                        <h4 class="cron-stat__value">{{ number_format($totalTasks, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card cron-stat">
                <div class="card-body">
                    <span class="cron-stat__icon bg-success"><i class="fas fa-toggle-on"></i></span>
                    <div>
                        <p class="cron-stat__label">Tarefas ativas</p>
                        <h4 class="cron-stat__value">{{ number_format($activeTasks, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card cron-stat">
                <div class="card-body">
                    <span class="cron-stat__icon bg-info"><i class="fas fa-hourglass-half"></i></span>
                    <div>
                        <p class="cron-stat__label">Prontas para rodar</p>
                        <h4 class="cron-stat__value">{{ number_format($dueTasks, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card cron-stat">
                <div class="card-body">
                    <span class="cron-stat__icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                    <div>
                        <p class="cron-stat__label">Com erro</p>
                        <h4 class="cron-stat__value">{{ number_format($errorTasks, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="cron-section-title"><i class="fas fa-server"></i>Cron geral da hospedagem</div>
            <div class="cron-command-panel">
                <code class="cron-command-box" id="hosting-cron-command">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
                <button type="button" class="btn btn-primary" data-copy-cron="#hosting-cron-command">
                    <i class="fas fa-copy mr-1"></i> Copiar
                </button>
            </div>
            <small class="d-block mt-2 text-muted">No cPanel, use Common Settings: Once Per Minute.</small>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-plus-circle text-primary mr-2"></i>Nova tarefa interna</div>
        </div>
        <div class="card-body">
            <form id="cron-create-form" action="{{ route('back.platform.cron.store') }}" method="POST" data-cron-form>
                @csrf
                <div class="row">
                    <div class="col-lg-5">
                        <div class="cron-section-title"><i class="fas fa-clipboard-list"></i>Dados da rotina</div>
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tarefa do sistema</label>
                            <select class="form-control" data-command-select>
                                <option value="">Selecione uma tarefa pronta</option>
                                @foreach ($commandOptions as $command => $label)
                                    <option value="{{ $command }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Comando Artisan</label>
                            <input type="text" name="command" class="form-control" placeholder="queue:work --stop-when-empty" required>
                        </div>
                        <div class="form-group mb-lg-0">
                            <label>Descricao operacional</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Explique quando e por que esta rotina deve rodar."></textarea>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="cron-section-title"><i class="fas fa-calendar-alt"></i>Agendamento cPanel</div>
                        <div class="form-group">
                            <label>Configuracoes comuns</label>
                            <select class="form-control" data-common-cron>
                                <option value="">Personalizado</option>
                                @foreach ($commonSettings as $expression => $label)
                                    <option value="{{ $expression }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cron-schedule-grid">
                            <div class="form-group">
                                <label>Minuto</label>
                                {!! $applySelect('minute', '*') !!}
                            </div>
                            <div class="form-group">
                                <label>Hora</label>
                                {!! $applySelect('hour', '*') !!}
                            </div>
                            <div class="form-group">
                                <label>Dia</label>
                                {!! $applySelect('day', '*') !!}
                            </div>
                            <div class="form-group">
                                <label>Mes</label>
                                {!! $applySelect('month', '*') !!}
                            </div>
                            <div class="form-group">
                                <label>Dia da semana</label>
                                {!! $applySelect('weekday', '*') !!}
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                            <code class="cron-expression-preview mb-3" data-cron-preview="cron-create-form">* * * * *</code>
                            <label class="switch-primary mb-3">
                                <input type="checkbox" class="switch switch-bootstrap status" name="is_active" value="1" checked>
                                <span class="switch-body"></span>
                                <span class="switch-text">Ativar</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="text-right mt-3">
                    <button class="btn btn-secondary" type="submit">
                        <i class="fas fa-save mr-1"></i> Salvar tarefa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-stream text-primary mr-2"></i>Tarefas registradas</div>
        </div>
        <div class="card-body">
            <div class="cron-task-list">
                @forelse ($tasks as $task)
                    @php
                        $formId = 'cron-update-'.$task->id;
                        $statusKey = $task->last_status ?: 'none';
                        $statusLabel = $statusLabels[$statusKey] ?? 'Sem execucao';
                        $statusBadge = $statusBadges[$statusKey] ?? 'secondary';
                        $taskClasses = trim(($task->isDue() ? 'is-due ' : '').($task->last_status === 'error' ? 'has-error' : ''));
                    @endphp
                    <div class="cron-task-card {{ $taskClasses }}">
                        <form id="{{ $formId }}" action="{{ route('back.platform.cron.update', $task->id) }}" method="POST" data-cron-form>
                            @csrf
                            @method('PUT')
                        </form>

                        <div class="cron-task-header">
                            <div class="cron-task-title">
                                <i class="fas fa-cog"></i>
                                <div>
                                    <h5>{{ $task->name }}</h5>
                                    <small class="text-muted">{{ $task->command }}</small>
                                </div>
                            </div>
                            <div class="cron-task-badges">
                                <span class="badge badge-{{ $task->is_active ? 'success' : 'secondary' }}">{{ $task->is_active ? 'Ativa' : 'Inativa' }}</span>
                                <span class="badge badge-{{ $statusBadge }}">{{ $statusLabel }}</span>
                                @if ($task->is_system)
                                    <span class="badge badge-info">Sistema</span>
                                @else
                                    <span class="badge badge-light">Personalizada</span>
                                @endif
                            </div>
                        </div>

                        <div class="cron-task-body">
                            <div class="cron-task-section">
                                <div class="cron-section-title"><i class="fas fa-pen"></i>Rotina</div>
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input type="text" name="name" class="form-control" value="{{ $task->name }}" form="{{ $formId }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Comando Artisan</label>
                                    <input type="text" name="command" class="form-control" value="{{ $task->command }}" form="{{ $formId }}" required>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Descricao</label>
                                    <textarea name="description" class="form-control" rows="3" form="{{ $formId }}">{{ $task->description }}</textarea>
                                </div>
                            </div>

                            <div class="cron-task-section">
                                <div class="cron-section-title"><i class="fas fa-calendar-check"></i>Agendamento</div>
                                <div class="form-group">
                                    <label>Configuracoes comuns</label>
                                    <select class="form-control" form="{{ $formId }}" data-common-cron>
                                        <option value="">Personalizado</option>
                                        @foreach ($commonSettings as $expression => $label)
                                            <option value="{{ $expression }}" {{ $task->cronExpression() === $expression ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="cron-schedule-grid">
                                    <div class="form-group">
                                        <label>Minuto</label>
                                        {!! $applySelect('minute', $task->minute, 'form-control cpanel-field', $formId) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Hora</label>
                                        {!! $applySelect('hour', $task->hour, 'form-control cpanel-field', $formId) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Dia</label>
                                        {!! $applySelect('day', $task->day, 'form-control cpanel-field', $formId) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Mes</label>
                                        {!! $applySelect('month', $task->month, 'form-control cpanel-field', $formId) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Semana</label>
                                        {!! $applySelect('weekday', $task->weekday, 'form-control cpanel-field', $formId) !!}
                                    </div>
                                </div>
                                <code class="cron-expression-preview" data-cron-preview="{{ $formId }}">{{ $task->cronExpression() }}</code>
                            </div>

                            <div class="cron-task-section cron-task-section--runtime">
                                <div class="cron-section-title"><i class="fas fa-history"></i>Execucao</div>
                                <div class="cron-runtime-row">
                                    <span class="text-muted">Ultima</span>
                                    <b>{{ $task->last_run_at ? $task->last_run_at->format('d/m/Y H:i') : '-' }}</b>
                                </div>
                                <div class="cron-runtime-row">
                                    <span class="text-muted">Proxima</span>
                                    <b>{{ $task->next_run_at ? $task->next_run_at->format('d/m/Y H:i') : '-' }}</b>
                                </div>
                                <div class="cron-runtime-row">
                                    <span class="text-muted">Expressao</span>
                                    <b>{{ $task->cronExpression() }}</b>
                                </div>
                                @if ($task->last_output)
                                    <pre class="cron-output mt-3 mb-0 small">{{ \Illuminate\Support\Str::limit($task->last_output, 500) }}</pre>
                                @endif
                            </div>
                        </div>

                        <div class="cron-task-footer">
                            <label class="switch-primary mb-0">
                                <input type="checkbox" class="switch switch-bootstrap status" name="is_active" value="1" form="{{ $formId }}" {{ $task->is_active ? 'checked' : '' }}>
                                <span class="switch-body"></span>
                                <span class="switch-text">Ativar</span>
                            </label>
                            <div class="cron-actions">
                                <button class="btn btn-success btn-sm" type="submit" form="{{ $formId }}" data-toggle="tooltip" title="Salvar">
                                    <i class="fas fa-save mr-1"></i> Salvar
                                </button>
                                <a class="btn btn-primary btn-sm" href="{{ route('back.platform.cron.run', $task->id) }}" data-toggle="tooltip" title="Executar agora">
                                    <i class="fas fa-play mr-1"></i> Executar
                                </a>
                                <form action="{{ route('back.platform.cron.destroy', $task->id) }}" method="POST" class="d-inline" data-confirm-submit data-confirm-title="Excluir tarefa?" data-confirm-text="Esta tarefa sera removida da central interna de cron." data-confirm-button="Excluir">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit" data-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="cron-empty">
                        <i class="fas fa-calendar-times"></i>
                        Nenhuma tarefa registrada.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var fields = ['minute', 'hour', 'day', 'month', 'weekday'];

        function getFormId(element) {
            if (element.getAttribute('form')) {
                return element.getAttribute('form');
            }

            var form = element.closest('[data-cron-form]');
            return form ? form.id : null;
        }

        function findField(formId, field) {
            var form = document.getElementById(formId);
            var input = form ? form.querySelector('[data-cron-field="' + field + '"]') : null;

            if (!input) {
                input = document.querySelector('[form="' + formId + '"][data-cron-field="' + field + '"]');
            }

            return input;
        }

        function expressionFor(formId) {
            return fields.map(function (field) {
                var input = findField(formId, field);
                return input && input.value ? input.value : '*';
            }).join(' ');
        }

        function updatePreview(formId) {
            if (!formId) return;
            document.querySelectorAll('[data-cron-preview="' + formId + '"]').forEach(function (preview) {
                preview.textContent = expressionFor(formId);
            });
        }

        function applyExpression(select) {
            if (!select.value) return;
            var formId = getFormId(select);
            var parts = select.value.split(' ');

            fields.forEach(function (field, index) {
                var input = findField(formId, field);
                if (input) input.value = parts[index] || '*';
            });

            updatePreview(formId);
        }

        document.querySelectorAll('[data-common-cron]').forEach(function (select) {
            select.addEventListener('change', function () {
                applyExpression(select);
            });
        });

        document.querySelectorAll('[data-cron-field]').forEach(function (input) {
            input.addEventListener('change', function () {
                updatePreview(getFormId(input));
            });
        });

        document.querySelectorAll('[data-command-select]').forEach(function (select) {
            select.addEventListener('change', function () {
                var form = select.closest('form');
                var command = form ? form.querySelector('[name="command"]') : null;
                var name = form ? form.querySelector('[name="name"]') : null;
                var label = select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : '';

                if (command && select.value) command.value = select.value;
                if (name && select.value && !name.value) name.value = label;
            });
        });

        document.querySelectorAll('[data-copy-cron]').forEach(function (button) {
            button.addEventListener('click', function () {
                var target = document.querySelector(button.getAttribute('data-copy-cron'));
                if (!target) return;

                var text = target.textContent.trim();
                var original = button.innerHTML;
                var setCopied = function () {
                    button.innerHTML = '<i class="fas fa-check mr-1"></i> Copiado';
                    setTimeout(function () {
                        button.innerHTML = original;
                    }, 1800);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(setCopied);
                    return;
                }

                var textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', 'readonly');
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                setCopied();
            });
        });

        document.querySelectorAll('[data-cron-form]').forEach(function (form) {
            updatePreview(form.id);
        });
    })();
</script>
@endsection
