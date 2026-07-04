@php
    $mercadoPagoPayment = App\Models\PaymentSetting::where('unique_keyword', 'mercadopago')->first();
    $mercadoPagoConfig = array_merge([
        'public_key' => '',
        'token' => '',
        'pix_enabled' => 1,
        'credit_card_enabled' => 1,
        'debit_card_enabled' => 0,
        'pix_expiration_minutes' => 30,
        'fee_pass_to_customer' => 0,
        'fee_percent' => 0,
        'fee_fixed' => 0,
        'max_installments' => 1,
    ], $mercadoPagoPayment ? ($mercadoPagoPayment->convertJsonData() ?: []) : []);
    $mercadoPagoPixEnabled = (int) $mercadoPagoConfig['pix_enabled'] === 1;
    $mercadoPagoCreditEnabled = (int) $mercadoPagoConfig['credit_card_enabled'] === 1;
    $mercadoPagoDefaultType = $mercadoPagoPixEnabled ? 'pix' : 'credit_card';
@endphp

@if ($mercadoPagoPayment && $mercadoPagoPayment->status == 1 && ($mercadoPagoPixEnabled || $mercadoPagoCreditEnabled))
    <div class="modal fade" id="mercadopago" tabindex="-1" aria-hidden="true">
        <form class="interactive-credit-card row" id="mercadopagofrom"
            action="{{ route('front.mercadopago.submit') }}" method="POST">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Transações via Mercado Pago</h6>
                        <button class="close" type="button" data-bs-dismiss="modal" aria-label="Fechar"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="card-body">
                            @if ($mercadoPagoPixEnabled && $mercadoPagoCreditEnabled)
                                <div class="form-group">
                                    <label class="d-block mb-2">Forma de pagamento</label>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="mercadopago_payment_pix" name="mercadopago_payment_type"
                                            class="custom-control-input mercadopago-payment-type" value="pix" checked>
                                        <label class="custom-control-label" for="mercadopago_payment_pix">Pix</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="mercadopago_payment_credit" name="mercadopago_payment_type"
                                            class="custom-control-input mercadopago-payment-type" value="credit_card">
                                        <label class="custom-control-label" for="mercadopago_payment_credit">Cartão de crédito</label>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="mercadopago_payment_type" value="{{ $mercadoPagoDefaultType }}">
                            @endif

                            @if ($mercadoPagoPixEnabled)
                                <div class="alert alert-info mercadopago-pix-info" role="alert">
                                    O Pix será gerado com QR Code e código copia e cola. A expiração configurada é de
                                    {{ (int) $mercadoPagoConfig['pix_expiration_minutes'] }} minutos.
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-lg-5 form-group">
                                    <label for="docType">Tipo de documento</label>
                                    <select id="docType" class="form-control" name="docType" data-checkout="docType" required>
                                        <option value="CPF" selected>CPF</option>
                                        <option value="CNPJ">CNPJ</option>
                                    </select>
                                </div>
                                <div class="col-lg-7 form-group">
                                    <label for="docNumber">Número do documento</label>
                                    <input class="form-control" type="text" id="docNumber" name="docNumber"
                                        data-checkout="docNumber" placeholder="000.000.000-00" autocomplete="off" required>
                                </div>
                            </div>

                            @if ($mercadoPagoCreditEnabled)
                                <div id="mercadopago-card-fields">
                                    <div class="col-lg-12 form-group px-0">
                                        <div id="cardNumber"></div>
                                    </div>
                                    <div class="col-lg-12 form-group px-0">
                                        <div id="securityCode"></div>
                                    </div>
                                    <div class="col-lg-12 form-group px-0">
                                        <div id="expirationDate"></div>
                                    </div>
                                    <div class="col-lg-12 form-group px-0">
                                        <input class="form-control" type="text" id="cardholderName"
                                            data-checkout="cardholderName" placeholder="Nome impresso no cartão"
                                            autocomplete="cc-name">
                                    </div>
                                    <small class="d-block text-muted mb-3">
                                        Cartão de débito não é aceito nesta operação.
                                    </small>
                                </div>
                            @endif

                            @if ((int) $mercadoPagoConfig['fee_pass_to_customer'] === 1)
                                <small class="d-block text-muted mb-3">
                                    Taxas do Mercado Pago podem ser somadas ao valor final conforme configuração da loja.
                                </small>
                            @endif

                            <p>{{ PriceHelper::GatewayText('mercadopago') }}</p>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" value="Mercado Pago">
                    <input type="hidden" name="shipping_id" value="" class="shipping_id_setup">
                    <input type="hidden" name="state_id"
                        value="{{ auth()->check() && auth()->user()->state_id ? auth()->user()->state_id : '' }}"
                        class="state_id_setup">
                    <input type="hidden" name="installments" value="1">
                    <input type="hidden" name="amount" id="transactionAmount">
                    <input type="hidden" name="description">
                    <input type="hidden" name="paymentMethodId" id="mercadopagoPaymentMethodId">
                    <input type="hidden" name="paymentTypeId" id="mercadopagoPaymentTypeId">
                    <div class="modal-footer">
                        <button class="btn btn-primary btn-sm" type="button"
                            data-bs-dismiss="modal"><span>Cancelar</span></button>
                        <button class="btn btn-primary btn-sm" id="mercadopagoSubmitButton"
                            type="submit"><span id="mercadopagoSubmitText">Gerar Pix</span></button>
                    </div>
                </div>
            </div>
        </form>

        @if ($mercadoPagoCreditEnabled)
            <script src="https://sdk.mercadopago.com/js/v2"></script>
        @endif
        <script>
            (function () {
                var form = document.getElementById('mercadopagofrom');
                if (!form) {
                    return;
                }

                var creditEnabled = @json($mercadoPagoCreditEnabled);
                var publicKey = @json($mercadoPagoConfig['public_key']);
                var cardFields = document.getElementById('mercadopago-card-fields');
                var cardholderName = document.getElementById('cardholderName');
                var submitButton = document.getElementById('mercadopagoSubmitButton');
                var submitText = document.getElementById('mercadopagoSubmitText');
                var paymentMethodInput = document.getElementById('mercadopagoPaymentMethodId');
                var paymentTypeInput = document.getElementById('mercadopagoPaymentTypeId');
                var docType = document.getElementById('docType');
                var docNumber = document.getElementById('docNumber');
                var cardNumberElement = null;
                var doSubmit = false;
                var debitBlocked = false;
                var mp = null;

                function selectedType() {
                    var checked = form.querySelector('input[name="mercadopago_payment_type"]:checked');
                    var hidden = form.querySelector('input[type="hidden"][name="mercadopago_payment_type"]');
                    return checked ? checked.value : (hidden ? hidden.value : 'pix');
                }

                function notify(message) {
                    if (window.Swal) {
                        window.Swal.fire('Atenção', message, 'warning');
                        return;
                    }

                    alert(message);
                }

                function onlyDigits(value) {
                    return (value || '').replace(/\D+/g, '');
                }

                function maskDocument() {
                    var digits = onlyDigits(docNumber.value);

                    if (docType.value === 'CNPJ') {
                        digits = digits.substring(0, 14);
                        docNumber.value = digits
                            .replace(/^(\d{2})(\d)/, '$1.$2')
                            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                            .replace(/\.(\d{3})(\d)/, '.$1/$2')
                            .replace(/(\d{4})(\d)/, '$1-$2');
                        docNumber.placeholder = '00.000.000/0000-00';
                        return;
                    }

                    digits = digits.substring(0, 11);
                    docNumber.value = digits
                        .replace(/^(\d{3})(\d)/, '$1.$2')
                        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                        .replace(/\.(\d{3})(\d)/, '.$1-$2');
                    docNumber.placeholder = '000.000.000-00';
                }

                function setMode() {
                    var type = selectedType();
                    var isCard = type === 'credit_card';

                    if (cardFields) {
                        cardFields.style.display = isCard ? '' : 'none';
                    }

                    if (cardholderName) {
                        cardholderName.required = isCard;
                    }

                    if (submitText) {
                        submitText.textContent = isCard ? 'Finalizar com cartão de crédito' : 'Gerar Pix';
                    }

                    if (!isCard) {
                        debitBlocked = false;
                        paymentMethodInput.value = '';
                        paymentTypeInput.value = '';
                    }

                    submitButton.disabled = isCard && debitBlocked;
                }

                docType.addEventListener('change', maskDocument);
                docNumber.addEventListener('input', maskDocument);
                form.querySelectorAll('.mercadopago-payment-type').forEach(function (input) {
                    input.addEventListener('change', setMode);
                });
                maskDocument();
                setMode();

                if (creditEnabled && publicKey && window.MercadoPago) {
                    mp = new MercadoPago(publicKey, {
                        locale: 'pt-BR'
                    });

                    cardNumberElement = mp.fields.create('cardNumber', {
                        placeholder: 'Número do cartão'
                    }).mount('cardNumber');

                    mp.fields.create('expirationDate', {
                        placeholder: 'MM/AA'
                    }).mount('expirationDate');

                    mp.fields.create('securityCode', {
                        placeholder: 'Código de segurança'
                    }).mount('securityCode');

                    cardNumberElement.on('binChange', async function (data) {
                        if (!data.bin || data.bin.length < 6) {
                            return;
                        }

                        try {
                            var response = await mp.getPaymentMethods({
                                bin: data.bin
                            });
                            var method = response.results && response.results.length ? response.results[0] : null;

                            if (!method) {
                                return;
                            }

                            paymentMethodInput.value = method.id || '';
                            paymentTypeInput.value = method.payment_type_id || '';
                            debitBlocked = method.payment_type_id === 'debit_card' || (method.id || '').indexOf('deb') === 0;

                            if (debitBlocked) {
                                submitButton.disabled = true;
                                notify('Cartão de débito não é aceito nesta operação. Use Pix ou cartão de crédito.');
                                return;
                            }

                            submitButton.disabled = false;
                        } catch (error) {
                            console.error('Erro ao identificar bandeira Mercado Pago:', error);
                        }
                    });
                }

                form.addEventListener('submit', async function (event) {
                    if (selectedType() !== 'credit_card') {
                        docNumber.value = onlyDigits(docNumber.value);
                        return;
                    }

                    event.preventDefault();

                    if (!mp) {
                        notify('Chave pública do Mercado Pago não configurada.');
                        return;
                    }

                    if (debitBlocked || paymentTypeInput.value === 'debit_card') {
                        notify('Cartão de débito não é aceito nesta operação.');
                        return;
                    }

                    if (doSubmit) {
                        return;
                    }

                    try {
                        submitButton.disabled = true;
                        var token = await mp.fields.createCardToken({
                            cardholderName: cardholderName.value,
                            identificationType: docType.value,
                            identificationNumber: onlyDigits(docNumber.value)
                        });

                        if (!token || !token.id) {
                            submitButton.disabled = false;
                            notify('Não foi possível validar os dados do cartão.');
                            return;
                        }

                        if (!paymentMethodInput.value && (token.paymentMethodId || token.payment_method_id)) {
                            paymentMethodInput.value = token.paymentMethodId || token.payment_method_id;
                        }

                        docNumber.value = onlyDigits(docNumber.value);

                        var card = document.createElement('input');
                        card.setAttribute('name', 'token');
                        card.setAttribute('type', 'hidden');
                        card.setAttribute('value', token.id);
                        form.appendChild(card);

                        doSubmit = true;
                        form.submit();
                    } catch (error) {
                        submitButton.disabled = false;
                        notify('Confira os dados do cartão e tente novamente.');
                        console.error('Erro ao criar token Mercado Pago:', error);
                    }
                });
            })();
        </script>
    </div>
@endif
