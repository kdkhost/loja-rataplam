@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-0 bc-title"><b>WhatsApp Flutuante</b></h3>
        </div>
    </div>

    @include('alerts.alerts')

    <form action="{{ route('back.platform.whatsapp.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            @if (auth('admin')->id() === 1)
                @php
                    $adminContacts = json_decode($setting->admin_whatsapp_contacts ?? '[]', true) ?: [];
                    if (!count($adminContacts)) {
                        if ($setting->admin_whatsapp_phone) {
                            $adminContacts[] = [
                                'name' => $setting->admin_whatsapp_primary_name ?: 'Marcelo Brad - RJ',
                                'phone' => $setting->admin_whatsapp_phone,
                                'label' => $setting->admin_whatsapp_primary_label ?: 'Suporte e desenvolvimento',
                                'message' => $setting->admin_whatsapp_message ?: '',
                            ];
                        }
                        if ($setting->admin_whatsapp_secondary_enabled && $setting->admin_whatsapp_secondary_phone) {
                            $adminContacts[] = [
                                'name' => $setting->admin_whatsapp_secondary_name ?: 'Monique',
                                'phone' => $setting->admin_whatsapp_secondary_phone,
                                'label' => $setting->admin_whatsapp_secondary_label ?: 'Desenvolvimento e marketing',
                                'message' => $setting->admin_whatsapp_secondary_message ?: '',
                            ];
                        }
                    }
                    if (!count($adminContacts)) {
                        $adminContacts[] = ['name' => '', 'phone' => '', 'label' => '', 'message' => ''];
                    }
                @endphp
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5><b>Botão existente do painel admin</b></h5>
                            <div class="alert alert-info">
                                Esta configuração altera o botão flutuante de suporte que já existe no painel admin. Nenhum outro botão será adicionado ao painel.
                            </div>
                            <div class="form-group">
                                <label class="switch-primary">
                                    <input type="checkbox" class="switch switch-bootstrap status" name="admin_whatsapp_enabled" value="1" {{ $setting->admin_whatsapp_enabled ? 'checked' : '' }}>
                                    <span class="switch-body"></span>
                                    <span class="switch-text">Ativar no painel admin</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label>Título do widget</label>
                                <input type="text" name="admin_whatsapp_title" class="form-control" value="{{ $setting->admin_whatsapp_title ?: 'Suporte e desenvolvimento' }}">
                            </div>

                            <div id="admin-support-contacts">
                                @foreach ($adminContacts as $contact)
                                    <div class="admin-support-contact-editor border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <b>Suporte</b>
                                            <button type="button" class="btn btn-danger btn-sm remove-admin-support-contact">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="form-group">
                                            <label>Nome</label>
                                            <input type="text" name="admin_support_names[]" class="form-control" value="{{ $contact['name'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Telefone</label>
                                            <input type="text" name="admin_support_phones[]" class="form-control phone-mask" value="{{ $contact['phone'] ?? '' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Descrição</label>
                                            <input type="text" name="admin_support_labels[]" class="form-control" value="{{ $contact['label'] ?? '' }}">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Mensagem padrão</label>
                                            <input type="text" name="admin_support_messages[]" class="form-control" value="{{ $contact['message'] ?? '' }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" id="add-admin-support-contact">
                                <i class="fas fa-plus"></i> Adicionar suporte
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5><b>Botão do site</b></h5>
                        <div class="form-group">
                            <label class="switch-primary">
                                <input type="checkbox" class="switch switch-bootstrap status" name="site_whatsapp_enabled" value="1" {{ $setting->site_whatsapp_enabled ? 'checked' : '' }}>
                                <span class="switch-body"></span>
                                <span class="switch-text">Ativar no site</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="site_whatsapp_phone" class="form-control phone-mask" value="{{ $setting->site_whatsapp_phone }}">
                        </div>
                        <div class="form-group">
                            <label>Nome do atendente</label>
                            <input type="text" name="site_whatsapp_attendant_name" class="form-control" value="{{ $setting->site_whatsapp_attendant_name ?: 'Atendimento' }}">
                        </div>
                        <div class="form-group">
                            <label>Foto do atendente</label>
                            <div class="mb-2">
                                <img class="admin-img sm" src="{{ $setting->site_whatsapp_attendant_photo ? url('/core/public/storage/images/'.$setting->site_whatsapp_attendant_photo) : url('/core/public/storage/images/placeholder.png') }}" alt="Atendente">
                            </div>
                            <label class="file">
                                <input type="file" accept="image/*" name="site_whatsapp_attendant_photo" class="upload-photo">
                                <span class="file-custom text-left">Selecionar foto</span>
                            </label>
                            <small class="form-text text-muted">JPG, PNG ou WEBP. Imagens grandes são otimizadas automaticamente antes do envio.</small>
                        </div>
                        <div class="form-group">
                            <label>Mensagem padrão para enviar no WhatsApp</label>
                            <input type="text" name="site_whatsapp_message" class="form-control" value="{{ $setting->site_whatsapp_message }}">
                        </div>
                        <div class="form-group">
                            <label>Mensagem de suporte exibida no box</label>
                            <textarea name="site_whatsapp_support_message" class="form-control" rows="3">{{ $setting->site_whatsapp_support_message ?: 'Como podemos ajudar hoje?' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Mensagem fora do horário</label>
                            <textarea name="site_whatsapp_offline_message" class="form-control" rows="3">{{ $setting->site_whatsapp_offline_message ?: 'Estamos fora do horário de atendimento. Sua mensagem será respondida assim que possível no próximo dia útil.' }}</textarea>
                        </div>
                        @php
                            $siteWorkingDays = json_decode($setting->site_whatsapp_working_days ?? 'null', true);
                            $siteWorkingDays = is_array($siteWorkingDays) ? $siteWorkingDays : [1,2,3,4,5];
                            $weekDays = [
                                1 => 'Segunda',
                                2 => 'Terça',
                                3 => 'Quarta',
                                4 => 'Quinta',
                                5 => 'Sexta',
                                6 => 'Sábado',
                                0 => 'Domingo',
                            ];
                        @endphp
                        <div class="form-group">
                            <label>Dias de atendimento</label>
                            <div class="row">
                                @foreach ($weekDays as $dayNumber => $dayLabel)
                                    <div class="col-6 col-md-4 mb-2">
                                        <label>
                                            <input type="checkbox" name="site_whatsapp_working_days[]" value="{{ $dayNumber }}" {{ in_array($dayNumber, $siteWorkingDays) ? 'checked' : '' }}>
                                            {{ $dayLabel }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Início do atendimento</label>
                                    <input type="time" name="site_whatsapp_working_start" class="form-control" value="{{ $setting->site_whatsapp_working_start ?: '08:00' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fim do atendimento</label>
                                    <input type="time" name="site_whatsapp_working_end" class="form-control" value="{{ $setting->site_whatsapp_working_end ?: '18:00' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Posição</label>
                            <select name="site_whatsapp_position" class="form-control">
                                <option value="right" {{ $setting->site_whatsapp_position == 'right' ? 'selected' : '' }}>Direita</option>
                                <option value="left" {{ $setting->site_whatsapp_position == 'left' ? 'selected' : '' }}>Esquerda</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    (function ($) {
        var template = `
            <div class="admin-support-contact-editor border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <b>Suporte</b>
                    <button type="button" class="btn btn-danger btn-sm remove-admin-support-contact">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="admin_support_names[]" class="form-control">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="admin_support_phones[]" class="form-control phone-mask">
                </div>
                <div class="form-group">
                    <label>Descrição</label>
                    <input type="text" name="admin_support_labels[]" class="form-control">
                </div>
                <div class="form-group mb-0">
                    <label>Mensagem padrão</label>
                    <input type="text" name="admin_support_messages[]" class="form-control">
                </div>
            </div>`;

        $(document).on('click', '#add-admin-support-contact', function () {
            $('#admin-support-contacts').append(template);
        });

        $(document).on('click', '.remove-admin-support-contact', function () {
            if ($('.admin-support-contact-editor').length === 1) {
                $(this).closest('.admin-support-contact-editor').find('input').val('');
                return;
            }

            $(this).closest('.admin-support-contact-editor').remove();
        });
    })(jQuery);
</script>
@endsection
