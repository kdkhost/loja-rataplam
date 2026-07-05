@extends('master.back')

@section('styles')
<style>
    .cron-command-box {
        display: block;
        padding: 12px 14px;
        border-radius: 6px;
        background: #f5f7fb;
        border: 1px solid #e6eaf0;
        color: #1f2937;
        white-space: normal;
        word-break: break-word;
    }
    .cron-table td {
        vertical-align: top;
    }
    .cron-schedule-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        gap: 10px;
    }
    @media (max-width: 1199px) {
        .cron-schedule-grid {
            grid-template-columns: repeat(2, minmax(120px, 1fr));
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
@endphp

<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-1 bc-title"><b>Central Interna de Cron</b></h3>
            <p class="mb-0 text-muted">Cadastre no cPanel apenas o cron geral abaixo. As rotinas internas ficam centralizadas aqui com agendamento no mesmo formato do WHM/cPanel.</p>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="card mb-4">
        <div class="card-body">
            <h5><b>Cron geral para cadastrar na hospedagem</b></h5>
            <code class="cron-command-box">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
            <small class="d-block mt-2 text-muted">No cPanel: Cron Jobs &gt; Add New Cron Job &gt; Common Settings: Once Per Minute.</small>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Nova tarefa interna</div>
        </div>
        <div class="card-body">
            <form action="{{ route('back.platform.cron.store') }}" method="POST" data-cron-form>
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tarefa do sistema</label>
                            <select class="form-control" data-command-select>
                                <option value="">Selecione uma tarefa pronta</option>
                                @foreach ($commandOptions as $command => $label)
                                    <option value="{{ $command }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Comando Artisan</label>
                            <input type="text" name="command" class="form-control" placeholder="queue:work --stop-when-empty" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descricao operacional</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Explique quando e por que esta rotina deve rodar."></textarea>
                </div>

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

                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <label class="switch-primary mb-3">
                        <input type="checkbox" class="switch switch-bootstrap status" name="is_active" value="1" checked>
                        <span class="switch-body"></span>
                        <span class="switch-text">{{ __('Enable') }}</span>
                    </label>
                    <button class="btn btn-secondary mb-3" type="submit">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Tarefas registradas</div>
        </div>
        <div class="card-body gd-responsive-table">
            <table class="table table-bordered cron-table">
                <thead>
                    <tr>
                        <th>Rotina</th>
                        <th>Agendamento cPanel</th>
                        <th>Execucao</th>
                        <th>Status</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td style="min-width: 260px;">
                                <form id="cron-update-{{ $task->id }}" action="{{ route('back.platform.cron.update', $task->id) }}" method="POST" data-cron-form>
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" name="name" class="form-control" value="{{ $task->name }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Comando Artisan</label>
                                        <input type="text" name="command" class="form-control" value="{{ $task->command }}" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Descricao</label>
                                        <textarea name="description" class="form-control" rows="2">{{ $task->description }}</textarea>
                                    </div>
                                    @if ($task->is_system)
                                        <span class="badge badge-info mt-2">Sistema</span>
                                    @endif
                                </form>
                            </td>
                            <td style="min-width: 420px;">
                                <div class="form-group">
                                    <label>Configuracoes comuns</label>
                                    <select class="form-control" form="cron-update-{{ $task->id }}" data-common-cron>
                                        <option value="">Personalizado</option>
                                        @foreach ($commonSettings as $expression => $label)
                                            <option value="{{ $expression }}" {{ $task->cronExpression() === $expression ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="cron-schedule-grid">
                                    <div class="form-group">
                                        <label>Minuto</label>
                                        {!! $applySelect('minute', $task->minute, 'form-control cpanel-field', 'cron-update-'.$task->id) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Hora</label>
                                        {!! $applySelect('hour', $task->hour, 'form-control cpanel-field', 'cron-update-'.$task->id) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Dia</label>
                                        {!! $applySelect('day', $task->day, 'form-control cpanel-field', 'cron-update-'.$task->id) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Mes</label>
                                        {!! $applySelect('month', $task->month, 'form-control cpanel-field', 'cron-update-'.$task->id) !!}
                                    </div>
                                    <div class="form-group">
                                        <label>Semana</label>
                                        {!! $applySelect('weekday', $task->weekday, 'form-control cpanel-field', 'cron-update-'.$task->id) !!}
                                    </div>
                                </div>
                                <code>{{ $task->cronExpression() }}</code>
                            </td>
                            <td style="min-width: 200px;">
                                <p class="mb-1"><b>Ultima:</b> {{ $task->last_run_at ? $task->last_run_at->format('d/m/Y H:i') : '-' }}</p>
                                <p class="mb-1"><b>Proxima:</b> {{ $task->next_run_at ? $task->next_run_at->format('d/m/Y H:i') : '-' }}</p>
                                <label class="switch-primary mt-2">
                                    <input type="checkbox" class="switch switch-bootstrap status" name="is_active" value="1" form="cron-update-{{ $task->id }}" {{ $task->is_active ? 'checked' : '' }}>
                                    <span class="switch-body"></span>
                                    <span class="switch-text">{{ __('Enable') }}</span>
                                </label>
                            </td>
                            <td style="min-width: 200px;">
                                <span class="badge badge-{{ $task->last_status === 'success' ? 'success' : ($task->last_status === 'error' ? 'danger' : 'secondary') }}">
                                    {{ $task->last_status ?: '-' }}
                                </span>
                                @if ($task->last_output)
                                    <pre class="mt-2 mb-0 small">{{ \Illuminate\Support\Str::limit($task->last_output, 500) }}</pre>
                                @endif
                            </td>
                            <td style="min-width: 140px;">
                                <button class="btn btn-success btn-sm mb-1" type="submit" form="cron-update-{{ $task->id }}" data-toggle="tooltip" title="Salvar">
                                    <i class="fas fa-save"></i>
                                </button>
                                <a class="btn btn-primary btn-sm mb-1" href="{{ route('back.platform.cron.run', $task->id) }}" data-toggle="tooltip" title="Executar agora">
                                    <i class="fas fa-play"></i>
                                </a>
                                <form action="{{ route('back.platform.cron.destroy', $task->id) }}" method="POST" class="d-inline" data-confirm-submit data-confirm-title="Excluir tarefa?" data-confirm-text="Esta tarefa sera removida da central interna de cron." data-confirm-button="Excluir">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm mb-1" type="submit" data-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhuma tarefa registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        function applyExpression(select) {
            if (!select.value) return;
            var form = select.closest('[data-cron-form]');
            if (!form && select.getAttribute('form')) {
                form = document.getElementById(select.getAttribute('form'));
            }
            form = form || document;
            var parts = select.value.split(' ');
            ['minute', 'hour', 'day', 'month', 'weekday'].forEach(function (field, index) {
                var input = form.querySelector('[data-cron-field="' + field + '"]');
                if (!input && form.id) {
                    input = document.querySelector('[form="' + form.id + '"][data-cron-field="' + field + '"]');
                }
                if (input) input.value = parts[index] || '*';
            });
        }

        document.querySelectorAll('[data-common-cron]').forEach(function (select) {
            select.addEventListener('change', function () {
                applyExpression(select);
            });
        });

        document.querySelectorAll('[data-command-select]').forEach(function (select) {
            select.addEventListener('change', function () {
                var form = select.closest('form');
                var command = form ? form.querySelector('[name="command"]') : null;
                if (command && select.value) command.value = select.value;
            });
        });
    })();
</script>
@endsection
