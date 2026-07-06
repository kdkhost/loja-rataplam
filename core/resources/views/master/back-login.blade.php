
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title>{{ $setting->title }}</title>
	<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
	<link rel="icon" href="{{ url('/core/public/storage/images/'.$setting->favicon) }}" type="image/x-icon"/>

	<!-- Fonts and icons -->
	<script src="{{ asset('assets/back/js/plugin/webfont/webfont.min.js') }}"></script>
	<script id="setFont" data-src="{{ asset("assets/back/css/fonts.css") }}" src="{{ asset('assets/back/js/plugin/webfont/setfont.js') }}"></script>


	<!-- CSS Files -->
	<link rel="stylesheet" href="{{ asset('assets/back/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/back/css/azzara.min.css') }}">

	@if(optional(DB::table('languages')->where('type', 'Dashboard')->where('status', 1)->where('is_default',1)->first())->rtl == 1)
    <link rel="stylesheet" href="{{ asset('assets/back/css/rtl.css') }}">
    @endif
    @yield('style')
</head>

<body class="login">

        @yield('content')

    @php
        $mainbs = [];
        $mainbs['is_announcement'] = $setting->is_announcement;
        $mainbs['announcement_delay'] = $setting->announcement_delay;
        $mainbs['overlay'] = $setting->overlay;
        $mainbs = json_encode($mainbs);
    @endphp

	<script src="{{ asset('assets/back/js/core/jquery.3.6.0.min.js') }}"></script>
	<script src="{{ asset('assets/back/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
	<script src="{{ asset('assets/back/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('assets/back/js/ready.min.js') }}"></script>
    <script>
        $(function () {
            $('.admin-notify-message').each(function () {
                var type = $(this).data('type') === 'success' ? 'success' : 'danger';
                $.notify({
                    icon: type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle',
                    title: ' <strong>' + ($(this).data('title') || '') + '</strong>',
                    message: $(this).data('message') || ''
                }, {
                    element: 'body',
                    type: type,
                    allow_dismiss: true,
                    newest_on_top: true,
                    placement: { from: 'top', align: 'right' },
                    offset: 20,
                    spacing: 10,
                    z_index: 1031,
                    delay: 5000,
                    timer: 1000,
                    animate: {
                        enter: 'animated fadeInDown',
                        exit: 'animated fadeOutUp'
                    },
                    icon_type: 'class'
                });
            });
        });
    </script>
    @yield('script')
</body>
</html>
