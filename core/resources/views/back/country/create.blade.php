@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0"><b>Cadastrar país</b></h3>
                <a class="btn btn-primary btn-sm" href="{{ route('back.country.index') }}">
                    <i class="fas fa-chevron-left"></i> {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card o-hidden border-0 shadow-lg">
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <form class="admin-form" action="{{ route('back.country.store') }}" method="POST">
                                @csrf
                                @include('alerts.alerts')

                                <div class="form-group">
                                    <label for="country-name">Nome do país *</label>
                                    <input type="text" name="name" class="form-control" id="country-name" placeholder="Ex.: Brasil" value="{{ old('name') }}">
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="country-status" name="status" value="1" checked>
                                        <label class="custom-control-label" for="country-status">Liberar venda para este país</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
