@extends('master.back')

@section('styles')
<style>
    .backup-page {
        --backup-border: #e7ebf3;
        --backup-soft: #f7f9fd;
        --backup-muted: #687386;
        --backup-strong: #172033;
    }

    .backup-page .card {
        border: 1px solid var(--backup-border);
        box-shadow: 0 8px 24px rgba(23, 125, 255, .04);
    }

    .backup-titlebar,
    .backup-actions,
    .backup-file {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .backup-titlebar {
        justify-content: space-between;
    }

    .backup-actions {
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .backup-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .backup-stat {
        min-height: 108px;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid var(--backup-border);
        background: #fff;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .backup-stat__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        color: #fff;
        background: #177dff;
        font-size: 18px;
    }

    .backup-stat__label {
        margin-bottom: 5px;
        color: var(--backup-muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .backup-stat__value {
        margin: 0;
        color: var(--backup-strong);
        font-size: 20px;
        font-weight: 800;
        line-height: 1.2;
        word-break: break-word;
    }

    .backup-help {
        padding: 16px 18px;
        border-radius: 8px;
        border: 1px solid #d9e8ff;
        background: #f2f7ff;
        color: #345071;
        line-height: 1.6;
    }

    .backup-help strong {
        color: #173f73;
    }

    .backup-file__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #fff7e8;
        color: #ff9f1a;
    }

    .backup-file__name {
        display: block;
        color: var(--backup-strong);
        font-weight: 700;
        word-break: break-word;
    }

    .backup-file__path {
        color: var(--backup-muted);
        font-size: 12px;
    }

    .backup-empty {
        padding: 44px 18px;
        text-align: center;
        color: var(--backup-muted);
    }

    .backup-empty i {
        display: block;
        margin-bottom: 12px;
        color: #ff9f1a;
        font-size: 34px;
    }

    html.admin-theme-dark .backup-page {
        --backup-border: #2d3a4e;
        --backup-soft: #111b2b;
        --backup-muted: #a9b4c5;
        --backup-strong: #eef4ff;
    }

    html.admin-theme-dark .backup-page .card,
    html.admin-theme-dark .backup-stat {
        background: #162235;
        border-color: var(--backup-border);
    }

    html.admin-theme-dark .backup-help {
        background: #13243c;
        border-color: #28456f;
        color: #c8d6ea;
    }

    html.admin-theme-dark .backup-help strong {
        color: #ffffff;
    }

    html.admin-theme-dark .backup-file__icon {
        background: #2b2418;
    }

    @media (max-width: 991px) {
        .backup-titlebar {
            align-items: flex-start;
            flex-direction: column;
        }

        .backup-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .backup-summary {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575px) {
        .backup-actions .btn,
        .backup-table-actions .btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 8px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid backup-page">
    <div class="card mb-4">
        <div class="card-body">
            <div class="backup-titlebar">
                <div>
                    <h3 class="mb-1 bc-title"><b>Backups do sistema</b></h3>
                    <span class="text-muted">Gere, acompanhe e baixe os backups salvos no servidor.</span>
                </div>
                <div class="backup-actions">
                    <form action="{{ route('back.backup.database.store') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-database"></i> Gerar backup do banco
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="backup-summary">
        <div class="backup-stat">
            <span class="backup-stat__icon"><i class="fas fa-server"></i></span>
            <div>
                <div class="backup-stat__label">Armazenamento</div>
                <p class="backup-stat__value">{{ $backupPathLabel }}</p>
            </div>
        </div>
        <div class="backup-stat">
            <span class="backup-stat__icon" style="background:#31ce36;"><i class="fas fa-copy"></i></span>
            <div>
                <div class="backup-stat__label">Backups disponiveis</div>
                <p class="backup-stat__value">{{ count($databaseBackups) }}</p>
            </div>
        </div>
        <div class="backup-stat">
            <span class="backup-stat__icon" style="background:#ff9f1a;"><i class="fas fa-shield-alt"></i></span>
            <div>
                <div class="backup-stat__label">Seguranca</div>
                <p class="backup-stat__value">Download autenticado</p>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="backup-help">
                <strong>Como funciona:</strong> o menu abre esta central sem baixar nada automaticamente. Ao clicar em
                <b>Gerar backup do banco</b>, o sistema cria um arquivo SQL dentro do servidor. Depois disso, o administrador
                pode baixar ou excluir cada arquivo pela lista abaixo.
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-2 mb-sm-0"><b>Historico de backups</b></h4>
                <span class="text-muted">Ordenado do mais recente para o mais antigo</span>
            </div>

            @if (count($databaseBackups))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="admin-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Gerado em</th>
                                <th>Tamanho</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($databaseBackups as $backup)
                                <tr>
                                    <td>
                                        <div class="backup-file">
                                            <span class="backup-file__icon"><i class="fas fa-file-code"></i></span>
                                            <span>
                                                <span class="backup-file__name">{{ $backup['name'] }}</span>
                                                <span class="backup-file__path">{{ $backupPathLabel }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ $backup['created_at'] }}</td>
                                    <td>{{ $backup['size'] }}</td>
                                    <td class="backup-table-actions">
                                        <a href="{{ route('back.backup.download', $backup['name']) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-download"></i> Baixar
                                        </a>
                                        <a class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete" href="javascript:;"
                                            data-href="{{ route('back.backup.destroy', $backup['name']) }}">
                                            <i class="fas fa-trash-alt"></i> Excluir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="backup-empty">
                    <i class="fas fa-database"></i>
                    Nenhum backup foi gerado ainda.
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="confirm-deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirm-deleteModalLabel">Excluir backup?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Esta acao remove o arquivo de backup salvo no servidor. Deseja continuar?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="" class="d-inline btn-ok" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
