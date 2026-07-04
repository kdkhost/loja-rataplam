@extends('master.front')

@section('title')
    Pedido realizado
@endsection

@section('content')
    @php
        $paymentDetails = json_decode($order->payment_details ?? '', true) ?: [];
        $mercadoPagoPix = data_get($paymentDetails, 'mercadopago.payment_type') === 'pix'
            ? data_get($paymentDetails, 'mercadopago')
            : null;
    @endphp
    <!-- Page Title-->
    <div class="page-title">
        <div class="container">
            <div class="column">
                <ul class="breadcrumbs">
                    <li><a href="{{ route('front.index') }}">Início</a> </li>
                    <li class="separator"></li>
                    <li>Pedido realizado</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Content-->
    <div class="container padding-bottom-3x mb-1">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="card-title text-success">
                    {{ $mercadoPagoPix ? 'Pedido recebido. Agora falta pagar o Pix.' : 'Pedido realizado com sucesso!' }}
                </h3>
                <p class="card-text">
                    {{ $mercadoPagoPix ? 'Assim que o pagamento for confirmado, o status do pedido será atualizado.' : 'Seu pedido foi recebido e será processado o mais breve possível.' }}
                </p>
                <p class="card-text">Anote o número do pedido: <span
                        class="text-medium">{{ $order->transaction_number }}</span></p>

                @if ($mercadoPagoPix)
                    <div class="row justify-content-center mt-4">
                        <div class="col-lg-7">
                            @if (!empty($mercadoPagoPix['qr_code_base64']))
                                <img class="img-fluid mb-3" style="max-width: 240px;"
                                    src="data:image/png;base64,{{ $mercadoPagoPix['qr_code_base64'] }}"
                                    alt="QR Code Pix">
                            @endif

                            @if (!empty($mercadoPagoPix['qr_code']))
                                <label class="d-block text-left font-weight-bold" for="mercadopago-pix-code">
                                    Pix copia e cola
                                </label>
                                <textarea id="mercadopago-pix-code" class="form-control mb-3" rows="4" readonly>{{ $mercadoPagoPix['qr_code'] }}</textarea>
                                <button type="button" class="btn btn-primary btn-sm mb-3" id="copy-mercadopago-pix">
                                    Copiar código Pix
                                </button>
                            @endif

                            @if (!empty($mercadoPagoPix['ticket_url']))
                                <a class="btn btn-outline-primary btn-sm mb-3" href="{{ $mercadoPagoPix['ticket_url'] }}" target="_blank" rel="noopener">
                                    Abrir pagamento no Mercado Pago
                                </a>
                            @endif

                            @if (!empty($mercadoPagoPix['expires_at']))
                                <p class="text-muted mb-0">
                                    Este Pix expira em {{ \Carbon\Carbon::parse($mercadoPagoPix['expires_at'])->format('d/m/Y H:i') }}.
                                </p>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="card-text">Você receberá em breve um e-mail com a confirmação do pedido.</p>
                @endif
                <div class="padding-top-1x padding-bottom-1x">

                    <a class="btn btn-primary m-4" href="{{ route('front.catalog') }}"><span><i
                                class="icon-package pr-2"></i> Ver produtos novamente</span></a>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if ($mercadoPagoPix && !empty($mercadoPagoPix['qr_code']))
        <script>
            (function () {
                var button = document.getElementById('copy-mercadopago-pix');
                var code = document.getElementById('mercadopago-pix-code');

                if (!button || !code) {
                    return;
                }

                button.addEventListener('click', function () {
                    code.select();
                    code.setSelectionRange(0, code.value.length);

                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(code.value);
                    } else {
                        document.execCommand('copy');
                    }

                    button.textContent = 'Código copiado';
                });
            })();
        </script>
    @endif
@endsection
