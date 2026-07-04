@foreach($datas as $data)
    <tr>
        <td>{{ $data->name }}</td>
        <td>
            <div class="dropdown">
                <button class="btn btn-{{ $data->status ? 'success' : 'danger' }} btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ $data->status ? 'Liberado para venda' : 'Bloqueado' }}
                </button>
                <div class="dropdown-menu animated--fade-in">
                    <a class="dropdown-item" href="{{ route('back.country.status', [$data->id, 1]) }}">Liberar venda</a>
                    <a class="dropdown-item" href="{{ route('back.country.status', [$data->id, 0]) }}">Bloquear venda</a>
                </div>
            </div>
        </td>
        <td>
            <div class="action-list">
                <a class="btn btn-secondary btn-sm" href="{{ route('back.country.edit', [$data->id]) }}">
                    <i class="fas fa-edit"></i>
                </a>
                <a class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete" href="javascript:;" data-href="{{ route('back.country.destroy', [$data->id]) }}">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </div>
        </td>
    </tr>
@endforeach
