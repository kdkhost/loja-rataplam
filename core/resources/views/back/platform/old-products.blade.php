@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-0 bc-title"><b>Importar Produtos do Site Antigo</b></h3>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('back.platform.old-products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Arquivo CSV</label>
                            <label class="file">
                                <input type="file" name="csv" accept=".csv,text/csv,text/plain">
                                <span class="file-custom text-left">Selecionar CSV...</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>URL CSV do site antigo</label>
                            <input type="url" name="csv_url" class="form-control" placeholder="https://site-antigo.com/produtos.csv">
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    Campos aceitos: nome/name/title, preco/price/valor, descricao/description, categoria/category, marca/brand, sku/codigo, estoque/stock e imagem/image/photo.
                </div>
                <button type="submit" class="btn btn-secondary">Importar produtos</button>
            </form>
        </div>
    </div>
</div>
@endsection
