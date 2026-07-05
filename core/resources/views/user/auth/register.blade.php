@extends('master.front')

@section('title')
    Criar conta
@endsection

@section('style')
    @include('user.auth.partials.premium-style')
@endsection

@section('content')
    <section class="customer-auth-area" aria-label="Cadastro do cliente">
        <div class="customer-auth-shell is-wide">
            <div class="customer-auth-showcase">
                <a class="customer-auth-logo" href="{{ route('front.index') }}" aria-label="{{ $setting->title }}">
                    <img src="{{ url('/core/public/storage/images/' . $setting->logo) }}" alt="{{ $setting->title }}">
                </a>

                <div class="customer-auth-copy">
                    <span class="customer-auth-kicker">Cadastro Rataplam</span>
                    <h1>Crie sua conta para comprar com mais praticidade.</h1>
                    <p>Salve seus dados, acompanhe pedidos e mantenha seus favoritos sempre por perto.</p>
                </div>

                <div class="customer-auth-benefits is-stacked" aria-label="Vantagens da conta">
                    <div class="customer-auth-benefit">
                        <i class="icon-user-check"></i>
                        <strong>Cadastro rápido</strong>
                        <span>Informações essenciais para uma compra mais fluida.</span>
                    </div>
                    <div class="customer-auth-benefit">
                        <i class="icon-shopping-bag"></i>
                        <strong>Pedidos organizados</strong>
                        <span>Histórico e acompanhamento em uma área segura.</span>
                    </div>
                </div>
            </div>

            <div class="customer-auth-card is-wide">
                <form class="customer-auth-form" action="{{ route('user.register.submit') }}" method="POST">
                    @csrf

                    <h2>Criar conta</h2>
                    <p class="customer-auth-subtitle">Preencha seus dados para acessar a loja com segurança.</p>

                    <div class="customer-auth-grid">
                        <div class="customer-auth-field">
                            <label for="reg-fn">Nome*</label>
                            <div class="customer-auth-input">
                                <i class="icon-user"></i>
                                <input class="form-control" type="text" name="first_name" id="reg-fn" value="{{ old('first_name') }}" autocomplete="given-name" required>
                            </div>
                            @error('first_name')
                                <p class="customer-auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="customer-auth-field">
                            <label for="reg-ln">Sobrenome*</label>
                            <div class="customer-auth-input">
                                <i class="icon-user"></i>
                                <input class="form-control" type="text" name="last_name" id="reg-ln" value="{{ old('last_name') }}" autocomplete="family-name" required>
                            </div>
                            @error('last_name')
                                <p class="customer-auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="customer-auth-field">
                            <label for="reg-email">E-mail*</label>
                            <div class="customer-auth-input">
                                <i class="icon-mail"></i>
                                <input class="form-control" type="email" name="email" id="reg-email" value="{{ old('email') }}" autocomplete="email" required>
                            </div>
                            @error('email')
                                <p class="customer-auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="customer-auth-field">
                            <label for="reg-phone">Telefone*</label>
                            <div class="customer-auth-input">
                                <i class="icon-phone"></i>
                                <input class="form-control" name="phone" type="tel" id="reg-phone" value="{{ old('phone') }}" inputmode="tel" autocomplete="tel" data-phone-mask required>
                            </div>
                            @error('phone')
                                <p class="customer-auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="customer-auth-field">
                            <label for="reg-pass">Senha*</label>
                            <div class="customer-auth-input">
                                <i class="icon-lock"></i>
                                <input class="form-control" type="password" name="password" id="reg-pass" autocomplete="new-password" required>
                                <button class="customer-auth-toggle" type="button" data-toggle-password="reg-pass" aria-label="Mostrar senha">
                                    <i class="icon-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="customer-auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="customer-auth-field">
                            <label for="reg-pass-confirm">Confirmar senha*</label>
                            <div class="customer-auth-input">
                                <i class="icon-lock"></i>
                                <input class="form-control" type="password" name="password_confirmation" id="reg-pass-confirm" autocomplete="new-password" required>
                                <button class="customer-auth-toggle" type="button" data-toggle-password="reg-pass-confirm" aria-label="Mostrar senha">
                                    <i class="icon-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="text" name="honeypot" id="honeypot" value="" style="display:none;">

                    @if ($setting->recaptcha == 1)
                        <div class="customer-auth-recaptcha">
                            {!! NoCaptcha::renderJs() !!}
                            {!! NoCaptcha::display() !!}
                            @if ($errors->has('g-recaptcha-response'))
                                @php
                                    $errmsg = $errors->first('g-recaptcha-response');
                                @endphp
                                <p class="customer-auth-error">{{ __("$errmsg") }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="customer-auth-actions">
                        <a class="customer-auth-secondary" href="{{ route('user.login') }}">Já tenho conta</a>
                        <button class="customer-auth-submit" type="submit">
                            <span>Criar conta</span>
                            <i class="icon-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.getAttribute('data-toggle-password'));
                var icon = button.querySelector('i');

                if (!input) return;

                var isHidden = input.getAttribute('type') === 'password';
                input.setAttribute('type', isHidden ? 'text' : 'password');
                button.setAttribute('aria-label', isHidden ? 'Ocultar senha' : 'Mostrar senha');

                if (icon) {
                    icon.classList.toggle('icon-eye', !isHidden);
                    icon.classList.toggle('icon-eye-off', isHidden);
                }
            });
        });

        document.querySelectorAll('[data-phone-mask]').forEach(function (input) {
            input.addEventListener('input', function () {
                var digits = input.value.replace(/\D/g, '').slice(0, 11);
                var formatted = digits;

                if (digits.length > 10) {
                    formatted = digits.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
                } else if (digits.length > 6) {
                    formatted = digits.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                } else if (digits.length > 2) {
                    formatted = digits.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
                } else if (digits.length > 0) {
                    formatted = digits.replace(/^(\d{0,2}).*/, '($1');
                }

                input.value = formatted;
            });

            input.dispatchEvent(new Event('input'));
        });
    </script>
@endsection
