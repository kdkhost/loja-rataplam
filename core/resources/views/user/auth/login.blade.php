@extends('master.front')

@section('title')
    Entrar
@endsection

@section('style')
    <style>
        :root {
            --customer-auth-ink: #162031;
            --customer-auth-muted: #667085;
            --customer-auth-line: #d9e1ea;
            --customer-auth-blue: #0f5f83;
            --customer-auth-green: #2f7f68;
            --customer-auth-gold: #ffaa20;
            --customer-auth-soft: #eef4f7;
            --customer-auth-card: #ffffff;
        }

        .customer-auth-area {
            padding: 34px 0 54px;
            background:
                linear-gradient(135deg, rgba(15, 95, 131, .08), rgba(255, 170, 32, .1)),
                #f2f6f8;
        }

        .customer-auth-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, .92fr) minmax(360px, .72fr);
            gap: 22px;
            align-items: stretch;
        }

        .customer-auth-showcase,
        .customer-auth-card {
            border: 1px solid rgba(15, 95, 131, .12);
            border-radius: 8px;
            background: var(--customer-auth-card);
            box-shadow: 0 22px 54px rgba(22, 32, 49, .1);
        }

        .customer-auth-showcase {
            position: relative;
            min-height: 560px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 34px;
            overflow: hidden;
            padding: 38px;
            background:
                linear-gradient(135deg, rgba(15, 95, 131, .94), rgba(47, 127, 104, .88)),
                #0f5f83;
            color: #fff;
        }

        .customer-auth-showcase::before {
            content: "";
            position: absolute;
            inset: auto -10% -28% 28%;
            height: 56%;
            border-radius: 999px 0 0 0;
            background: rgba(255, 255, 255, .12);
            transform: rotate(-6deg);
        }

        .customer-auth-showcase::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .05), rgba(255, 255, 255, 0)),
                repeating-linear-gradient(135deg, rgba(255, 255, 255, .12) 0 1px, transparent 1px 18px);
            opacity: .55;
            pointer-events: none;
        }

        .customer-auth-showcase > * {
            position: relative;
            z-index: 1;
        }

        .customer-auth-logo {
            width: 184px;
            max-width: 70%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .95);
            box-shadow: 0 18px 40px rgba(0, 0, 0, .16);
        }

        .customer-auth-logo img {
            max-width: 100%;
            max-height: 76px;
            object-fit: contain;
        }

        .customer-auth-copy {
            max-width: 560px;
        }

        .customer-auth-kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
            color: rgba(255, 255, 255, .9);
            font-size: 13px;
            font-weight: 800;
            line-height: 1.3;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .customer-auth-kicker::before {
            content: "";
            width: 30px;
            height: 3px;
            border-radius: 999px;
            background: var(--customer-auth-gold);
        }

        .customer-auth-copy h1 {
            margin: 0;
            color: #fff;
            font-size: 52px;
            line-height: 1.02;
            font-weight: 800;
            letter-spacing: 0;
        }

        .customer-auth-copy p {
            max-width: 470px;
            margin: 18px 0 0;
            color: rgba(255, 255, 255, .84);
            font-size: 16px;
            line-height: 1.65;
        }

        .customer-auth-benefits {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .customer-auth-benefit {
            min-height: 118px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, .17);
            border-radius: 8px;
            background: rgba(255, 255, 255, .12);
            backdrop-filter: blur(10px);
        }

        .customer-auth-benefit i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            margin-bottom: 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            font-size: 18px;
        }

        .customer-auth-benefit strong {
            display: block;
            color: #fff;
            font-size: 15px;
            line-height: 1.35;
            font-weight: 800;
        }

        .customer-auth-benefit span {
            display: block;
            margin-top: 6px;
            color: rgba(255, 255, 255, .78);
            font-size: 13px;
            line-height: 1.45;
        }

        .customer-auth-card {
            display: flex;
            align-items: center;
            padding: 42px;
        }

        .customer-auth-form {
            width: 100%;
        }

        .customer-auth-form h2 {
            margin: 0;
            color: var(--customer-auth-ink);
            font-size: 31px;
            line-height: 1.14;
            font-weight: 800;
            letter-spacing: 0;
        }

        .customer-auth-form .customer-auth-subtitle {
            margin: 10px 0 28px;
            color: var(--customer-auth-muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .customer-auth-field {
            margin-bottom: 18px;
        }

        .customer-auth-field label {
            display: block;
            margin-bottom: 8px;
            color: #344054;
            font-size: 13px;
            font-weight: 800;
        }

        .customer-auth-input {
            position: relative;
        }

        .customer-auth-input > i {
            position: absolute;
            left: 16px;
            top: 50%;
            color: #7b8da2;
            font-size: 17px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .customer-auth-input .form-control {
            width: 100%;
            min-height: 56px;
            padding: 15px 48px 15px 48px;
            border: 1px solid var(--customer-auth-line);
            border-radius: 8px;
            background: #fbfcfd;
            color: var(--customer-auth-ink);
            font-size: 15px;
            box-shadow: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .customer-auth-input .form-control:focus {
            border-color: var(--customer-auth-blue);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(15, 95, 131, .12);
        }

        .customer-auth-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #667085;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .customer-auth-toggle:hover,
        .customer-auth-toggle:focus {
            background: #edf4f7;
            color: var(--customer-auth-blue);
            outline: none;
        }

        .customer-auth-error {
            margin: 8px 0 0;
            color: #c0392b;
            font-size: 13px;
            line-height: 1.4;
        }

        .customer-auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 2px 0 24px;
        }

        .customer-auth-options a,
        .customer-auth-register a {
            color: var(--customer-auth-blue);
            font-weight: 800;
            text-decoration: none;
        }

        .customer-auth-options a:hover,
        .customer-auth-register a:hover {
            color: #0b526f;
            text-decoration: underline;
        }

        .customer-auth-submit {
            width: 100%;
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 8px;
            background: var(--customer-auth-blue);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            box-shadow: 0 16px 30px rgba(15, 95, 131, .24);
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .customer-auth-submit:hover,
        .customer-auth-submit:focus {
            background: #0b526f;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 19px 36px rgba(15, 95, 131, .3);
        }

        .customer-auth-register {
            margin: 20px 0 0;
            color: var(--customer-auth-muted);
            font-size: 14px;
            line-height: 1.5;
            text-align: center;
        }

        .customer-auth-social {
            margin-top: 24px;
            padding-top: 22px;
            border-top: 1px solid #e6edf3;
        }

        .customer-auth-social-title {
            margin: 0 0 14px;
            color: #667085;
            font-size: 13px;
            line-height: 1.4;
            text-align: center;
        }

        .customer-auth-social-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .customer-auth-social-link {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            border: 1px solid #dbe4ec;
            border-radius: 8px;
            background: #fff;
            color: #344054;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .customer-auth-social-link:hover,
        .customer-auth-social-link:focus {
            border-color: var(--customer-auth-blue);
            color: var(--customer-auth-blue);
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(22, 32, 49, .09);
        }

        @media (max-width: 991px) {
            .customer-auth-area {
                padding: 24px 0 42px;
            }

            .customer-auth-shell {
                grid-template-columns: 1fr;
            }

            .customer-auth-showcase {
                min-height: auto;
                padding: 32px;
            }

            .customer-auth-copy {
                max-width: 680px;
            }

            .customer-auth-copy h1 {
                font-size: 34px;
            }
        }

        @media (max-width: 767px) {
            .customer-auth-shell {
                width: min(100% - 22px, 560px);
                gap: 14px;
            }

            .customer-auth-showcase,
            .customer-auth-card {
                border-radius: 8px;
            }

            .customer-auth-benefits {
                grid-template-columns: 1fr;
            }

            .customer-auth-benefit {
                min-height: 92px;
            }

            .customer-auth-card {
                padding: 28px 22px;
            }
        }

        @media (max-width: 480px) {
            .customer-auth-area {
                padding: 12px 0 28px;
            }

            .customer-auth-shell {
                width: 100%;
            }

            .customer-auth-showcase,
            .customer-auth-card {
                border-left: 0;
                border-right: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .customer-auth-showcase {
                padding: 26px 18px;
            }

            .customer-auth-logo {
                width: 156px;
            }

            .customer-auth-copy h1 {
                font-size: 29px;
            }

            .customer-auth-copy p {
                font-size: 14px;
            }

            .customer-auth-card {
                padding: 28px 18px 34px;
            }

            .customer-auth-form h2 {
                font-size: 26px;
            }

            .customer-auth-options {
                align-items: flex-start;
                flex-direction: column;
            }

            .customer-auth-social-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <section class="customer-auth-area" aria-label="Login do cliente">
        <div class="customer-auth-shell">
            <div class="customer-auth-showcase">
                <a class="customer-auth-logo" href="{{ route('front.index') }}" aria-label="{{ $setting->title }}">
                    <img src="{{ url('/core/public/storage/images/' . $setting->logo) }}" alt="{{ $setting->title }}">
                </a>

                <div class="customer-auth-copy">
                    <span class="customer-auth-kicker">Conta do cliente</span>
                    <h1>Entre para acompanhar seus pedidos Rataplam.</h1>
                    <p>Acesse sua conta para ver compras, favoritos e continuar escolhendo peças infantis com mais praticidade.</p>
                </div>

                <div class="customer-auth-benefits" aria-label="Recursos da conta">
                    <div class="customer-auth-benefit">
                        <i class="icon-package"></i>
                        <strong>Pedidos</strong>
                        <span>Acompanhe suas compras em poucos toques.</span>
                    </div>
                    <div class="customer-auth-benefit">
                        <i class="icon-heart"></i>
                        <strong>Favoritos</strong>
                        <span>Guarde peças para comprar depois.</span>
                    </div>
                    <div class="customer-auth-benefit">
                        <i class="icon-headphones"></i>
                        <strong>Atendimento</strong>
                        <span>Tenha suporte com dados da conta.</span>
                    </div>
                </div>
            </div>

            <div class="customer-auth-card">
                <form class="customer-auth-form" method="post" action="{{ route('user.login.submit') }}">
                    @csrf

                    <h2>Entrar na sua conta</h2>
                    <p class="customer-auth-subtitle">Informe seu e-mail e senha para continuar.</p>

                    <div class="customer-auth-field">
                        <label for="customer-login-email">E-mail</label>
                        <div class="customer-auth-input">
                            <i class="icon-mail"></i>
                            <input id="customer-login-email" class="form-control" type="email" name="login_email" value="{{ old('login_email') }}" autocomplete="email" required>
                        </div>
                        @error('login_email')
                            <p class="customer-auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="customer-auth-field">
                        <label for="customer-login-password">Senha</label>
                        <div class="customer-auth-input">
                            <i class="icon-lock"></i>
                            <input id="customer-login-password" class="form-control" type="password" name="login_password" autocomplete="current-password" required>
                            <button class="customer-auth-toggle" type="button" data-toggle-password="customer-login-password" aria-label="Mostrar senha">
                                <i class="icon-eye"></i>
                            </button>
                        </div>
                        @error('login_password')
                            <p class="customer-auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="customer-auth-options">
                        <span>Acesso seguro</span>
                        <a href="{{ route('user.forgot') }}">Esqueci minha senha</a>
                    </div>

                    <button class="customer-auth-submit" type="submit">
                        <span>Entrar</span>
                        <i class="icon-arrow-right"></i>
                    </button>

                    <p class="customer-auth-register">
                        Ainda não tem conta?
                        <a href="{{ route('user.register') }}">Cadastre-se agora</a>
                    </p>

                    @if($setting->facebook_check == 1 || $setting->google_check == 1)
                        <div class="customer-auth-social">
                            <p class="customer-auth-social-title">Ou continue com</p>
                            <div class="customer-auth-social-actions">
                                @if($setting->facebook_check == 1)
                                    <a class="customer-auth-social-link" href="{{ route('social.provider', 'facebook') }}">
                                        <i class="icon-facebook"></i>
                                        Facebook
                                    </a>
                                @endif
                                @if($setting->google_check == 1)
                                    <a class="customer-auth-social-link" href="{{ route('social.provider', 'google') }}">
                                        <i class="icon-chrome"></i>
                                        Google
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
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
    </script>
@endsection
