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
    .backup-file,
    .backup-tab-title {
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

    .backup-stat__icon,
    .backup-tab-title__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #177dff;
    }

    .backup-stat__icon {
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        font-size: 18px;
    }

    .backup-tab-title__icon {
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 10px;
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

    .backup-tabs {
        gap: 8px;
        border-bottom: 1px solid var(--backup-border);
        margin-bottom: 22px;
    }

    .backup-tabs .nav-link {
        border: 0;
        border-bottom: 3px solid transparent;
        color: var(--backup-muted);
        font-weight: 700;
        padding: 12px 16px;
    }

    .backup-tabs .nav-link.active {
        color: #177dff;
        background: transparent;
        border-bottom-color: #177dff;
    }

    .backup-help,
    .backup-system-box {
        padding: 16px 18px;
        border-radius: 8px;
        line-height: 1.6;
    }

    .backup-help {
        border: 1px solid #d9e8ff;
        background: #f2f7ff;
        color: #345071;
    }

    .backup-help strong {
        color: #173f73;
    }

    .backup-system-box {
        border: 1px dashed var(--backup-border);
        background: var(--backup-soft);
        color: var(--backup-muted);
    }

    .backup-system-box b {
        color: var(--backup-strong);
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

    html.admin-theme-dark .backup-tabs .nav-link.active {
        color: #74b4ff;
        border-bottom-color: #74b4ff;
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

        .backup-tabs {
            display: grid;
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@php
    $isSystemTab = ($activeTab ?? 'database') === 'system';
@endphp

<div class="container-fluid backup-page">
    <div class="card mb-4">
        <div class="card-body">
            <div class="backup-titlebar">
                <div>
                    <h3 class="mb-1 bc-title"><b>Backups do sistema</b></h3>
                    <span class="text-muted">Tudo em uma unica pagina, separado por guias.</span>
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
                <div class="backup-stat__label">Backups SQL</div>
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

    <div class="card shadow mb-4">
        <div class="card-body">
            <ul class="nav nav-tabs backup-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !$isSystemTab ? 'active' : '' }}" data-toggle="tab" href="#database-backup" role="tab">
                        <i class="fas fa-database"></i> Banco de dados
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isSystemTab ? 'active' : '' }}" data-toggle="tab" href="#system-backup" role="tab">
                        <i class="fas fa-code-branch"></i> Sistema
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade {{ !$isSystemTab ? 'show active' : '' }}" id="database-backup" role="tabpanel">
                    <div class="d-sm-flex align-items-start justify-content-between mb-4">
                        <div class="backup-tab-title mb-3 mb-sm-0">
                            <span class="backup-tab-title__icon"><i class="fas fa-database"></i></span>
                            <div>
                                <h4 class="mb-1"><b>Backup do banco de dados</b></h4>
                                <span class="text-muted">Arquivos SQL gerados e mantidos dentro do sistema.</span>
                            </div>
                        </div>
                        <div class="backup-actions">
                            <form action="{{ route('back.backup.database.store') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus-circle"></i> Gerar backup SQL
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="backup-help mb-4">
                        <strong>Como funciona:</strong> ao clicar em <b>Gerar backup SQL</b>, o sistema cria um arquivo
                        em <b>{{ $backupPathLabel }}</b>. O arquivo fica listado abaixo para download ou exclusao pelo administrador.
                    </div>

                    <div class="d-sm-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-2 mb-sm-0"><b>Historico de backups SQL</b></h4>
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
                            Nenhum backup SQL foi gerado ainda.
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade {{ $isSystemTab ? 'show active' : '' }}" id="system-backup" role="tabpanel">
                    <div class="backup-tab-title mb-4">
                        <span class="backup-tab-title__icon" style="background:#ff9f1a;"><i class="fas fa-code-branch"></i></span>
                        <div>
                            <h4 class="mb-1"><b>Backup do sistema</b></h4>
                            <span class="text-muted">Controle do codigo-fonte e arquivos do projeto.</span>
                        </div>
                    </div>

                    <div class="backup-system-box mb-4">
                        <p class="mb-2">
                            <b>Backup por ZIP permanece desativado.</b> O projeto deve ser preservado pelo repositorio Git,
                            porque o backup em ZIP no painel pode gerar arquivos grandes, lentos e duplicados no servidor.
                        </p>
                        <p class="mb-0">
                            Use esta mesma pagina para o banco de dados e o Git para o codigo-fonte. As rotas antigas de
                            backup de sistema apenas abrem esta guia, sem criar arquivo separado.
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="backup-stat h-100">
                                <span class="backup-stat__icon"><i class="fab fa-git-alt"></i></span>
                                <div>
                                    <div class="backup-stat__label">Codigo-fonte</div>
                                    <p class="backup-stat__value">Controlado pelo Git</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="backup-stat h-100">
                                <span class="backup-stat__icon" style="background:#f3545d;"><i class="fas fa-ban"></i></span>
                                <div>
                                    <div class="backup-stat__label">ZIP automatico</div>
                                    <p class="backup-stat__value">Desativado</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="backup-stat h-100">
                                <span class="backup-stat__icon" style="background:#31ce36;"><i class="fas fa-database"></i></span>
                                <div>
                                    <div class="backup-stat__label">Banco</div>
                                    <p class="backup-stat__value">Guia Banco de dados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
