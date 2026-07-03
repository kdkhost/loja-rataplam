@php
    $dashboardLanguage = DB::table('languages')->where('type', 'Dashboard')->where('status', 1)->where('is_default', 1)->first()
        ?: DB::table('languages')->where('type', 'Dashboard')->where('status', 1)->first();
    $dashboardLocale = $dashboardLanguage ? ($dashboardLanguage->name ?: pathinfo($dashboardLanguage->file, PATHINFO_FILENAME)) : app()->getLocale();
    $databaseConnection = config('database.default');
    $databaseDriver = config("database.connections.{$databaseConnection}.driver", $databaseConnection);
    $serverArchitecture = php_uname('s') . ' ' . php_uname('m');
    try {
        $databaseServerVersion = DB::selectOne('select version() as version')->version ?? null;
    } catch (Throwable $exception) {
        $databaseServerVersion = null;
    }
    $formatWhatsAppNumber = function ($number) {
        $digits = preg_replace('/\D+/', '', (string) $number);
        return strlen($digits) >= 10 ? $digits : '';
    };
    $adminSupportEnabled = (bool) $setting->admin_whatsapp_enabled;
    $adminSupportTitle = $setting->admin_whatsapp_title ?: 'Suporte e desenvolvimento';
    $adminSupportContacts = json_decode($setting->admin_whatsapp_contacts ?? '[]', true) ?: [];
    if (!count($adminSupportContacts)) {
        if ($setting->admin_whatsapp_phone) {
            $adminSupportContacts[] = [
                'name' => $setting->admin_whatsapp_primary_name ?: 'Marcelo Brad - RJ',
                'phone' => $setting->admin_whatsapp_phone,
                'label' => $setting->admin_whatsapp_primary_label ?: 'Suporte e desenvolvimento',
                'message' => $setting->admin_whatsapp_message ?: 'Olá, preciso de suporte no painel admin.',
            ];
        }
        if ($setting->admin_whatsapp_secondary_enabled && $setting->admin_whatsapp_secondary_phone) {
            $adminSupportContacts[] = [
                'name' => $setting->admin_whatsapp_secondary_name ?: 'Monique',
                'phone' => $setting->admin_whatsapp_secondary_phone,
                'label' => $setting->admin_whatsapp_secondary_label ?: 'Desenvolvimento e marketing',
                'message' => $setting->admin_whatsapp_secondary_message ?: 'Olá, preciso de suporte no painel admin.',
            ];
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $dashboardLocale) }}">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>{{ $setting->title }}</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <link rel="icon" type="image/x-icon" href="{{ url('/core/public/storage/images/' . $setting->favicon) }}" />
    <script>
        if (localStorage.getItem('admin-theme') === 'dark') {
            document.documentElement.classList.add('admin-theme-dark');
        }
    </script>

    <!-- Fonts and icons -->
    <script src="{{ asset('assets/back/js/plugin/webfont/webfont.min.js') }}"></script>
    <script id="setFont" data-src="{{ asset('assets/back/css/fonts.css') }}"
        src="{{ asset('assets/back/js/plugin/webfont/setfont.js') }}"></script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/back/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/azzara.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/tagify.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/bootstrap-iconpicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/back/css/magnific-popup.css') }}">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/back/css/custom.css') }}">


    @if ($dashboardLanguage && $dashboardLanguage->rtl == 1)
        <link rel="stylesheet" href="{{ asset('assets/back/css/rtl.css') }}">
    @endif

    @yield('styles')

</head>

<body>
    <div class="wrapper">
        <div class="main-header ">
            <!-- Logo Header -->
            <div class="logo-header">

                <a href="{{ route('back.dashboard') }}" class="logo">
                    <img src="{{ $setting->logo ? url('/core/public/storage/images/' . $setting->logo) : url('/core/public/storage/images/placeholder.png') }}"
                        alt="navbar brand" class="navbar-brand">
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="fa fa-bars"></i>
                    </span>
                </button>
                <button class="topbar-toggler more"><i class="fa fa-ellipsis-v"></i></button>
                <div class="navbar-minimize">
                    <button class="btn btn-minimize ">
                        <i class="fa fa-bars"></i>
                    </button>
                </div>
            </div>
            <!-- End Logo Header -->

            <!-- Navbar Header -->
            <nav class="navbar navbar-header navbar-expand-lg">
                <div class="container-fluid">
                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        <li class="nav-item mr-4">
                            <a class="btn btn-sm btn-primary py-1 text-white" title="website"
                                href="{{ route('front.index') }}" target="_blank">
                                <b> {{ __('View Website') }}</b>
                            </a>
                        </li>
                        <li class="nav-item mr-3">
                            <button type="button" class="btn btn-sm admin-theme-toggle" id="admin-theme-toggle"
                                title="{{ __('Dark / Light') }}" aria-label="{{ __('Dark / Light') }}">
                                <i class="fas fa-moon"></i>
                            </button>
                        </li>
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span
                                    class="badge badge-danger badge-counter" id="notification-count">{{ App\Models\Notification::unreadCount() }}</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown" id="display-notf"
                                data-href={{ route('back.notifications') }}>
                                @include('back.notification.index')
                            </div>
                        </li>

                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown"
                                href="{{ route('back.dashboard') }}" aria-expanded="false">
                                <div class="avatar-sm avatar avatar-sm">
                                    <img src="{{ Auth::guard('admin')->user()->photo ? url('/core/public/storage/images/' . Auth::guard('admin')->user()->photo) : url('/core/public/storage/images/noimage.png') }}"
                                        alt="..." class="avatar-img rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <li>
                                    <div class="user-box">
                                        <div class="avatar-lg"><img
                                                src="{{ Auth::guard('admin')->user()->photo ? url('/core/public/storage/images/' . Auth::guard('admin')->user()->photo) : url('/core/public/storage/images/noimage.png') }}"
                                                alt="image profile" class="avatar-img rounded"></div>

                                        <div class="u-text">
                                            <h4>{{ Auth::guard('admin')->user()->name }}</h4>
                                            <p class="text-muted">{{ Auth::guard('admin')->user()->email }}</p><a
                                                href="{{ route('back.profile') }}"
                                                class="btn  btn-secondary btn-sm">{{ __('Update Profile') }}</a>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                        href="{{ route('back.profile') }}">{{ __('Update Profile') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                        href="{{ route('back.password') }}">{{ __('Change Password') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('back.logout') }}">{{ __('Logout') }}</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- End Navbar -->
        </div>

        <!-- Sidebar -->
        <div class="sidebar">

            <div class="sidebar-background"></div>
            <div class="sidebar-wrapper scrollbar-inner">
                <div class="sidebar-content">
                    <div class="user">
                        <div class="avatar-sm float-left mr-2">
                            <img src="{{ Auth::guard('admin')->user()->photo ? url('/core/public/storage/images/' . Auth::guard('admin')->user()->photo) : url('/core/public/storage/images/noimage.png') }}"
                                alt="..." class="avatar-img rounded-circle">
                        </div>
                        <div class="info">
                            <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                                <span>
                                    {{ Auth::guard('admin')->user()->name }}
                                    <span class="user-level">{{ __('Administrator') }}</span>
                                </span>
                            </a>
                        </div>
                    </div>

                    @if (Auth::guard('admin')->user()->id == 1)
                        @include('master.inc.super')
                    @else
                        @include('master.inc.normal')
                    @endif
                    <div class="sidebar-footer text-primary d-block text-center pt-3">
                        <span class="d-inline-block"><b>{{ __('Version') }} {{ $setting->version }}</b></span>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Sidebar -->
        <button type="button" class="admin-sidebar-backdrop" id="admin-sidebar-backdrop"
            aria-label="Fechar menu lateral"></button>

        <div class="main-panel">
            <div class="content">
                <div class="page-inner">
                    @yield('content')
                </div>
            </div>
            <footer class="admin-system-footer">
                <div class="admin-system-footer__meta">
                    <span><b>{{ __('Version') }}:</b> {{ $setting->version }}</span>
                    <span>Laravel {{ app()->version() }} - {{ ucfirst($databaseDriver) }} {{ $databaseServerVersion ?: 'N/A' }} - PHP {{ PHP_VERSION }}</span>
                </div>
                <div class="admin-system-footer__credit">
                    Desenvolvido por <b>Marcelo Brad - RJ</b> e <b>Eth Estratégias</b>
                </div>
            </footer>
        </div>

    </div>

    @if ($adminSupportEnabled)
    <div class="admin-support-widget" id="admin-support-widget">
        <button type="button" class="admin-support-widget__button" id="admin-support-toggle"
            aria-expanded="false" aria-controls="admin-support-panel" title="{{ $adminSupportTitle }}">
            <i class="fab fa-whatsapp"></i>
        </button>
        <div class="admin-support-widget__panel" id="admin-support-panel" aria-hidden="true">
            <div class="admin-support-widget__header">
                <span>{{ $adminSupportTitle }}</span>
                <button type="button" class="admin-support-widget__close" id="admin-support-close"
                    aria-label="Fechar"><i class="fas fa-times"></i></button>
            </div>
            <div class="admin-support-widget__body">
                @forelse ($adminSupportContacts as $contact)
                    @php
                        $supportPhone = $formatWhatsAppNumber($contact['phone'] ?? '');
                        $supportMessage = $contact['message'] ?? 'Olá, preciso de suporte no painel admin.';
                    @endphp
                    @if ($supportPhone)
                        <a href="https://wa.me/{{ $supportPhone }}?text={{ urlencode($supportMessage) }}" target="_blank" rel="noopener"
                            class="admin-support-contact">
                            <span class="admin-support-contact__icon"><i class="fab fa-whatsapp"></i></span>
                            <span>
                                <b>{{ $contact['name'] ?? 'Suporte' }}</b>
                                <small>{{ $contact['label'] ?? 'Atendimento' }}</small>
                            </span>
                        </a>
                    @endif
                @empty
                    <span class="admin-support-contact admin-support-contact--disabled">
                        <span class="admin-support-contact__icon"><i class="fab fa-whatsapp"></i></span>
                        <span>
                            <b>Suporte</b>
                            <small>Configure ao menos um telefone no painel</small>
                        </span>
                    </span>
                @endforelse
            </div>
        </div>
    </div>
    @endif
    @php
        $mainbs = [];
        $mainbs['is_announcement'] = $setting->is_announcement;
        $mainbs['announcement_delay'] = $setting->announcement_delay;
        $mainbs['overlay'] = $setting->overlay;
        $mainbs = json_encode($mainbs);

    @endphp

    <script>
        var mainbs = {!! $mainbs !!};
        var summernot_upload_url = '{{ route('back.summernote.image.upload') }}';
        window.omnimartLocale = '{{ str_replace('_', '-', $dashboardLocale) }}';
    </script>
    <!--   Core JS Files   -->
    <script src="{{ asset('assets/back/js/core/jquery.3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/core/bootstrap.min.js') }}"></script>

    <!-- jQuery UI -->
    <script src="{{ asset('assets/back/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js') }}"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('assets/back/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

    <!-- Moment JS -->
    <script src="{{ asset('assets/back/js/plugin/moment/moment.min.js') }}"></script>

    <!-- Datatables -->
    <script src="{{ asset('assets/back/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/plugin/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Bootstrap Notify -->
    <script src="{{ asset('assets/back/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <!-- sweetalert2 -->
    <script src="{{ asset('assets/back/js/plugin/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- Menu Builder -->
    <script src="{{ asset('assets/back/js/plugin/jquery-menu-editor.js') }}"></script>

    <!-- Chartjs -->
    <script src="{{ asset('assets/back/js/plugin/chart.min.js') }}"></script>

    <!-- Editor -->
    <script src="{{ asset('assets/back/js/plugin/editor.js') }}"></script>
    <script src="{{ asset('assets/back/js/plugin/datepicker/bootstrap-datetimepicker.min.js') }}"></script>

    <!-- Tagify -->
    <script src="{{ asset('assets/back/js/tagify.js') }}"></script>

    <!-- JS Color -->
    <script src="{{ asset('assets/back/js/jscolor.js') }}"></script>

    <!-- Magnific Popup -->
    <script src="{{ asset('assets/back/js/jquery.magnific-popup.min.js') }}"></script>

    <!-- Sortable -->
    <script src="{{ asset('assets/back/js/sortable.js') }}"></script>

    <!-- Icon Picker -->
    <script src="{{ asset('assets/back/js/bootstrap-iconpicker.bundle.min.js') }}"></script>

    <!-- Azzara JS -->
    <script src="{{ asset('assets/back/js/ready.min.js') }}"></script>

    <!-- Custom JS -->

    @yield('scripts')
    <script src="{{ asset('assets/back/js/br-localization.js') }}"></script>
    <script src="{{ asset('assets/back/js/custom.js') }}"></script>

</body>

</html>
