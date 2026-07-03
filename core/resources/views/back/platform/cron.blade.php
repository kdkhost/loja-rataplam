@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-0 bc-title"><b>Central Interna de Cron</b></h3>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="card mb-4">
        <div class="card-body">
            <h5><b>Cron geral para cadastrar na hospedagem</b></h5>
            <code>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('back.platform.cron.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Comando Artisan</label>
                            <input type="text" name="command" class="form-control" placeholder="queue:work --stop-when-empty" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Frequencia</label>
                            <select name="frequency" class="form-control">
                                @foreach ($frequencies as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group pt-4">
                            <label class="switch-primary">
                                <input type="checkbox" class="switch switch-bootstrap status" name="is_active" value="1" checked>
                                <span class="switch-body"></span>
                                <span class="switch-text">{{ __('Enable') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-secondary" type="submit">{{ __('Submit') }}</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body gd-responsive-table">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Comando</th>
                        <th>Frequencia</th>
                        <th>Ultima execucao</th>
                        <th>Proxima</th>
                        <th>Status</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        <tr>
                            <td>{{ $task->name }}</td>
                            <td><code>{{ $task->command }}</code></td>
                            <td>{{ $frequencies[$task->frequency] ?? $task->frequency }}</td>
                            <td>{{ $task->last_run_at ? $task->last_run_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $task->next_run_at ? $task->next_run_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $task->last_status ?: '-' }}</td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="{{ route('back.platform.cron.run', $task->id) }}" data-toggle="tooltip" title="Executar agora">
                                    <i class="fas fa-play"></i>
                                </a>
                                <form action="{{ route('back.platform.cron.destroy', $task->id) }}" method="POST" class="d-inline" data-confirm-submit data-confirm-title="Excluir tarefa?" data-confirm-text="Esta tarefa sera removida da central interna de cron." data-confirm-button="Excluir">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit" data-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
