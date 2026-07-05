@extends('master.back-login')

@section('style')
    @include('back.auth.partials.premium-style')
@endsection

@section('content')
    <main class="admin-auth-page">
        <section class="admin-auth-shell" aria-label="Definir nova senha administrativa">
            <div class="admin-auth-brand">
                <div>
                    <a class="admin-auth-logo-card" href="{{ route('front.index') }}" aria-label="{{ $setting->title }}">
                        <img src="{{ url('/core/public/storage/images/' . $setting->logo) }}" alt="{{ $setting->title }}">
                    </a>
                </div>

                <div class="admin-auth-brand-copy">
                    <span class="admin-auth-kicker">Nova senha</span>
                    <h1>Defina uma nova senha para o painel.</h1>
                    <p>Crie uma senha segura para manter a administração da loja protegida.</p>
                </div>

                <div class="admin-auth-status" aria-label="Boas práticas de senha">
                    <div class="admin-auth-status-item">
                        <strong>Senha segura</strong>
                        <span>Evite repetir senhas usadas em outros serviços.</span>
                    </div>
                    <div class="admin-auth-status-item">
                        <strong>Confirmação</strong>
                        <span>Digite a mesma senha nos dois campos.</span>
                    </div>
                </div>
            </div>

            <div class="admin-auth-panel">
                <div class="admin-auth-card">
                    <h2>Definir nova senha</h2>
                    <p class="auth-subtitle">Informe e confirme a nova senha administrativa.</p>

                    <form action="{{ route('back.change.password') }}" method="POST">
                        @csrf

                        @include('alerts.alerts')

                        <div class="admin-auth-field">
                            <label for="admin-new-password">Nova senha</label>
                            <div class="admin-auth-input-wrap">
                                <i class="fas fa-lock"></i>
                                <input id="admin-new-password" name="new_password" type="password" class="form-control" autocomplete="new-password" required>
                                <button class="admin-auth-password-toggle" type="button" data-toggle-password="admin-new-password" aria-label="Mostrar senha">
                                    <span class="fas fa-eye"></span>
                                </button>
                            </div>
                        </div>

                        <div class="admin-auth-field">
                            <label for="admin-renew-password">Confirmar nova senha</label>
                            <div class="admin-auth-input-wrap">
                                <i class="fas fa-lock"></i>
                                <input id="admin-renew-password" name="renew_password" type="password" class="form-control" autocomplete="new-password" required>
                                <button class="admin-auth-password-toggle" type="button" data-toggle-password="admin-renew-password" aria-label="Mostrar senha">
                                    <span class="fas fa-eye"></span>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="file_token" value="{{ $token }}">

                        <div class="admin-auth-actions">
                            <a href="{{ route('back.login') }}" class="admin-auth-secondary">Voltar ao login</a>
                            <button type="submit" class="admin-auth-submit">
                                <span class="fas fa-check"></span>
                                Salvar senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
@endsection

@section('script')
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.getAttribute('data-toggle-password'));
                var icon = button.querySelector('span');

                if (!input) return;

                var isHidden = input.getAttribute('type') === 'password';
                input.setAttribute('type', isHidden ? 'text' : 'password');
                button.setAttribute('aria-label', isHidden ? 'Ocultar senha' : 'Mostrar senha');

                if (icon) {
                    icon.classList.toggle('fa-eye', !isHidden);
                    icon.classList.toggle('fa-eye-slash', isHidden);
                }
            });
        });
    </script>
@endsection
