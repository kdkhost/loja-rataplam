@extends('master.front')

@section('title')
    Nova senha
@endsection

@section('style')
    @include('user.auth.partials.premium-style')
@endsection

@section('content')
    <section class="customer-auth-area" aria-label="Definir nova senha">
        <div class="customer-auth-shell">
            <div class="customer-auth-showcase">
                <a class="customer-auth-logo" href="{{ route('front.index') }}" aria-label="{{ $setting->title }}">
                    <img src="{{ url('/core/public/storage/images/' . $setting->logo) }}" alt="{{ $setting->title }}">
                </a>

                <div class="customer-auth-copy">
                    <span class="customer-auth-kicker">Nova senha</span>
                    <h1>Crie uma senha segura para sua conta.</h1>
                    <p>Escolha uma senha nova e confirme para recuperar seu acesso à área do cliente.</p>
                </div>

                <div class="customer-auth-benefits is-stacked" aria-label="Boas práticas de senha">
                    <div class="customer-auth-benefit">
                        <i class="icon-lock"></i>
                        <strong>Senha protegida</strong>
                        <span>Use uma combinação que você ainda não usa em outros sites.</span>
                    </div>
                    <div class="customer-auth-benefit">
                        <i class="icon-check-circle"></i>
                        <strong>Confirme com atenção</strong>
                        <span>As duas senhas precisam ser iguais para concluir.</span>
                    </div>
                </div>
            </div>

            <div class="customer-auth-card">
                <form class="customer-auth-form" action="{{ route('user.change.password') }}" method="POST">
                    @csrf

                    @include('alerts.alerts')

                    <h2>Definir nova senha</h2>
                    <p class="customer-auth-subtitle">Informe e confirme a nova senha da sua conta.</p>

                    <div class="customer-auth-field">
                        <label for="new_password">Nova senha</label>
                        <div class="customer-auth-input">
                            <i class="icon-lock"></i>
                            <input id="new_password" name="new_password" type="password" class="form-control" autocomplete="new-password" required>
                            <button class="customer-auth-toggle" type="button" data-toggle-password="new_password" aria-label="Mostrar senha">
                                <i class="icon-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="customer-auth-field">
                        <label for="renew_password">Confirmar nova senha</label>
                        <div class="customer-auth-input">
                            <i class="icon-lock"></i>
                            <input id="renew_password" name="renew_password" type="password" class="form-control" autocomplete="new-password" required>
                            <button class="customer-auth-toggle" type="button" data-toggle-password="renew_password" aria-label="Mostrar senha">
                                <i class="icon-eye"></i>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="file_token" value="{{ $token }}">

                    <div class="customer-auth-actions">
                        <a class="customer-auth-secondary" href="{{ route('user.login') }}">Voltar ao login</a>
                        <button type="submit" class="customer-auth-submit">
                            <span>Salvar senha</span>
                            <i class="icon-check"></i>
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
    </script>
@endsection
