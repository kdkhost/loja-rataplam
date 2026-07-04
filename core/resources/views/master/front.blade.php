@php
    $websiteLanguage = Session::has('language')
        ? DB::table('languages')->where('status', 1)->where('id', Session::get('language'))->first()
        : DB::table('languages')->where('type', 'Website')->where('status', 1)->where('is_default', 1)->first();
    $websiteLanguage = $websiteLanguage ?: DB::table('languages')->where('type', 'Website')->where('status', 1)->first();
    $websiteLocale = $websiteLanguage ? ($websiteLanguage->name ?: pathinfo($websiteLanguage->file, PATHINFO_FILENAME)) : app()->getLocale();
    $activeWebsiteLanguages = DB::table('languages')->where('type', 'Website')->where('status', 1)->orderByDesc('is_default')->orderBy('language')->get();
    $activeCurrencies = DB::table('currencies')->where('status', 1)->whereIn('name', ['BRL', 'USD'])->orderByDesc('is_default')->orderBy('name')->get();
    $showLanguageSwitcher = $activeWebsiteLanguages->count() > 1;
    $showCurrencySwitcher = $showLanguageSwitcher && $activeCurrencies->count() > 1;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $websiteLocale) }}">

<head>
    <meta charset="UTF-8">
    @if (url()->current() == route('front.index'))
        <title>@yield('hometitle')</title>
    @else
        <title>{{ $setting->title }} -@yield('title')</title>
    @endif

    <!-- SEO Meta Tags-->
    @if (url()->current() == route('front.index'))
        <meta name="author" content="GeniusDevs">
        <meta name="distribution" content="web">
        <meta name="description" content="{{ $setting->meta_description }}">
        <meta name="keywords" content="{{ $setting->meta_keywords }}">
        <meta name="image" content="{{ url('/core/public/storage/images/' . $setting->meta_image) }}">
        <meta property="og:title" content="{{ $setting->title }}">
        <meta property="og:description" content="{{ $setting->meta_description }}">
        <meta property="og:image" content="{{ url('/core/public/storage/images/' . $setting->meta_image) }}">
        <meta property="og:image:secure_url"
            content="{{ url('/core/public/storage/images/' . $setting->meta_image) }}" />
        <meta property="og:image:type" content="image/jpeg" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="627" />
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ $setting->title }}">
        <meta property="og:type" content="website">
    @else
        @yield('meta')
    @endif

    <!-- Mobile Specific Meta Tag-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Favicon Icons-->
    <link rel="icon" type="image/png" href="{{ url('/core/public/storage/images/' . $setting->favicon) }}">
    <link rel="apple-touch-icon" href="{{ url('/core/public/storage/images/' . $setting->favicon) }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ url('/core/public/storage/images/' . $setting->favicon) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/core/public/storage/images/' . $setting->favicon) }}">
    <link rel="apple-touch-icon" sizes="167x167" href="{{ url('/core/public/storage/images/' . $setting->favicon) }}">
    @if ($setting->is_pwa)
        <link rel="manifest" href="{{ route('front.pwa.manifest') }}">
        <meta name="theme-color" content="{{ $setting->pwa_theme_color ?: $setting->primary_color }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="{{ $setting->pwa_short_name ?: $setting->title }}">
    @endif

    <!-- Vendor Styles including: Bootstrap, Font Icons, Plugins, etc.-->
    <link rel="stylesheet" media="screen" href="{{ asset('assets/front/css/plugins.min.css') }}">

    @yield('styleplugins')

    <link id="mainStyles" rel="stylesheet" media="screen" href="{{ asset('assets/front/css/styles.min.css') }}">

    <link id="mainStyles" rel="stylesheet" media="screen" href="{{ asset('assets/front/css/responsive.css') }}">
    <!-- Color css -->
    <link
        href="{{ asset('assets/front/css/color.php?primary_color=') . str_replace('#', '', $setting->primary_color) }}"
        rel="stylesheet">

    <!-- Modernizr-->
    <script src="{{ asset('assets/front/js/modernizr.min.js') }}"></script>

    @if ($websiteLanguage && $websiteLanguage->rtl == 1)
        <link rel="stylesheet" href="{{ asset('assets/front/css/rtl.css') }}">
    @endif
    <style>
        {{ $setting->custom_css }}
    </style>
    {{-- Google AdSense Start --}}
    @if ($setting->is_google_adsense == '1')
        {!! $setting->google_adsense !!}
    @endif
    {{-- Google AdSense End --}}

    {{-- Google AnalyTics Start --}}
    @if ($setting->is_google_analytics == '1')
        {!! $setting->google_analytics !!}
    @endif
    {{-- Google AnalyTics End --}}

    {{-- Facebook pixel  Start --}}
    @if ($setting->is_facebook_pixel == '1')
        {!! $setting->facebook_pixel !!}
    @endif
    {{-- Facebook pixel End --}}

</head>
<!-- Body-->

<body
    class="
@if ($setting->theme == 'theme1') body_theme1
@elseif($setting->theme == 'theme2')
body_theme2
@elseif($setting->theme == 'theme3')
body_theme3
@elseif($setting->theme == 'theme4')
body_theme4 @endif
">
    @if ($setting->is_loader == 1)
        <!-- Preloader Start -->
        @if ($setting->is_loader == 1)
            <div id="preloader">
                <img src="{{ url('/core/public/storage/images/' . $setting->loader) }}" alt="{{ __('Loading...') }}">
            </div>
        @endif

        <!-- Preloader endif -->
    @endif

    <!-- Header-->

    <header class="site-header navbar-sticky">
        <div class="menu-top-area">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <div class="t-m-s-a">
                            <a class="track-order-link" href="{{ route('front.order.track') }}"><i
                                    class="icon-map-pin"></i>{{ __('Track Order') }}</a>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="right-area">

                            <a class="track-order-link wishlist-mobile d-inline-block d-lg-none"
                                href="{{ route('user.wishlist.index') }}"><i
                                    class="icon-heart"></i>{{ __('Wishlist') }}</a>

                            @if ($showLanguageSwitcher)
                            <div class="t-h-dropdown ">
                                <a class="main-link" href="#">{{ __('Language') }}<i
                                        class="icon-chevron-down"></i></a>
                                <div class="t-h-dropdown-menu">
                                    @foreach ($activeWebsiteLanguages as $language)
                                        <a class="{{ Session::get('language') == $language->id ? 'active' : ($language->is_default == 1 && !Session::has('language') ? 'active' : '') }}"
                                            href="{{ route('front.language.setup', $language->id) }}"><i
                                                class="icon-chevron-right pr-2"></i>{{ $language->language }}</a>
                                    @endforeach
                                </div>
                            </div>
                            @endif


                            @if ($showCurrencySwitcher)
                            <div class="t-h-dropdown ">
                                <a class="main-link" href="#">{{ __('Currency') }}<i
                                        class="icon-chevron-down"></i></a>
                                <div class="t-h-dropdown-menu">
                                    @foreach ($activeCurrencies as $currency)
                                        <a class="{{ Session::get('currency') == $currency->id ? 'active' : ($currency->is_default == 1 && !Session::has('currency') ? 'active' : '') }}"
                                            href="{{ route('front.currency.setup', $currency->id) }}"><i
                                                class="icon-chevron-right pr-2"></i>{{ $currency->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="login-register ">
                                @if (!Auth::user())
                                    <a class="track-order-link mr-0" href="{{ route('user.login') }}">
                                        {{ __('Login') }}
                                    </a>
                                @else
                                    <div class="t-h-dropdown">
                                        <div class="main-link">
                                            <i class="icon-user pr-2"></i> <span
                                                class="text-label">{{ Auth::user()->first_name }}</span>
                                        </div>
                                        <div class="t-h-dropdown-menu">
                                            <a href="{{ route('user.dashboard') }}"><i
                                                    class="icon-chevron-right pr-2"></i>{{ __('Dashboard') }}</a>
                                            <a href="{{ route('user.logout') }}"><i
                                                    class="icon-chevron-right pr-2"></i>{{ __('Logout') }}</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Topbar-->
        <div class="topbar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex justify-content-between">
                            <!-- Logo-->
                            <div class="site-branding"><a class="site-logo align-self-center"
                                    href="{{ route('front.index') }}"><img
                                        src="{{ url('/core/public/storage/images/' . $setting->logo) }}"
                                        alt="{{ $setting->title }}"></a></div>
                            <!-- Search / Categories-->
                            <div class="search-box-wrap d-none d-lg-block d-flex">
                                <div class="search-box-inner align-self-center">
                                    <div class="search-box d-flex">
                                        <select name="category" id="category_select" class="categoris">
                                            <option value="">{{ __('All') }}</option>
                                            @foreach (DB::table('categories')->whereStatus(1)->get() as $category)
                                                <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <form class="input-group" id="header_search_form"
                                            action="{{ route('front.catalog') }}" method="get">
                                            <input type="hidden" name="category" value=""
                                                id="search__category">
                                            <span class="input-group-btn">
                                                <button type="submit"><i class="icon-search"></i></button>
                                            </span>
                                            <input class="form-control" type="text"
                                                data-target="{{ route('front.search.suggest') }}"
                                                id="__product__search" name="search"
                                                placeholder="{{ __('Search by product name') }}">
                                            <div class="serch-result d-none">
                                                {{-- search result --}}
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <span class="d-block d-lg-none close-m-serch"><i class="icon-x"></i></span>
                            </div>
                            <!-- Toolbar-->
                            <div class="toolbar d-flex">

                                <div class="toolbar-item close-m-serch visible-on-mobile"><a href="#">
                                        <div>
                                            <i class="icon-search"></i>
                                        </div>
                                    </a>
                                </div>
                                <div class="toolbar-item visible-on-mobile mobile-menu-toggle"><a href="#">
                                        <div><i class="icon-menu"></i><span
                                                class="text-label">{{ __('Menu') }}</span></div>
                                    </a>
                                </div>

                                <div class="toolbar-item hidden-on-mobile"><a
                                        href="{{ route('fornt.compare.index') }}">
                                        <div><span class="compare-icon"><i class="icon-repeat"></i><span
                                                    class="count-label compare_count">{{ Session::has('compare') ? count(Session::get('compare')) : '0' }}</span></span><span
                                                class="text-label">{{ __('Compare') }}</span></div>
                                    </a>
                                </div>
                                @if (Auth::check())
                                    <div class="toolbar-item hidden-on-mobile"><a
                                            href="{{ route('user.wishlist.index') }}">
                                            <div><span class="compare-icon"><i class="icon-heart"></i><span
                                                        class="count-label wishlist_count">{{ Auth::user()->wishlists->count() }}</span></span><span
                                                    class="text-label">{{ __('Wishlist') }}</span></div>
                                        </a>
                                    </div>
                                @else
                                    <div class="toolbar-item hidden-on-mobile"><a
                                            href="{{ route('user.wishlist.index') }}">
                                            <div><span class="compare-icon"><i class="icon-heart"></i></span><span
                                                    class="text-label">{{ __('Wishlist') }}</span></div>
                                        </a>
                                    </div>
                                @endif
                                <div class="toolbar-item"><a href="{{ route('front.cart') }}">
                                        <div><span class="cart-icon"><i class="icon-shopping-cart"></i><span
                                                    class="count-label cart_count">{{ Session::has('cart') ? count(Session::get('cart')) : '0' }}
                                                </span></span><span class="text-label">{{ __('Cart') }}</span>
                                        </div>
                                    </a>
                                    <div class="toolbar-dropdown cart-dropdown widget-cart  cart_view_header"
                                        id="header_cart_load" data-target="{{ route('front.header.cart') }}">
                                        @include('includes.header_cart')
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Menu-->
                            <div class="mobile-menu-backdrop" data-mobile-menu-close></div>
                            <div class="mobile-menu" id="mobile-menu" aria-hidden="true">
                                <!-- Slideable (Mobile) Menu-->
                                <div class="mm-heading-area">
                                    <h4>{{ __('Navigation') }}</h4>
                                    <div class="toolbar-item visible-on-mobile mobile-menu-toggle mm-t-two" data-mobile-menu-close>
                                        <a href="#">
                                            <div> <i class="icon-x"></i></div>
                                        </a>
                                    </div>
                                </div>
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item" role="presentation99">
                                        <span class="active" id="mmenu-tab" data-bs-toggle="tab"
                                            data-bs-target="#mmenu" role="tab" aria-controls="mmenu"
                                            aria-selected="true">{{ __('Menu') }}</span>
                                    </li>
                                    <li class="nav-item" role="presentation99">
                                        <span class="" id="mcat-tab" data-bs-toggle="tab"
                                            data-bs-target="#mcat" role="tab" aria-controls="mcat"
                                            aria-selected="false">{{ __('Category') }}</span>
                                    </li>

                                </ul>
                                <div class="tab-content p-0">
                                    <div class="tab-pane fade show active" id="mmenu" role="tabpanel"
                                        aria-labelledby="mmenu-tab">
                                        <nav class="slideable-menu">
                                            <ul>
                                                <li class="{{ request()->routeIs('front.index') ? 'active' : '' }}"><a
                                                        href="{{ route('front.index') }}"><i
                                                            class="icon-chevron-right"></i>{{ __('Home') }}</a>
                                                </li>
                                                @if ($setting->is_shop == 1)
                                                    <li
                                                        class="{{ request()->routeIs('front.catalog*') ? 'active' : '' }}">
                                                        <a href="{{ route('front.catalog') }}"><i
                                                                class="icon-chevron-right"></i>{{ __('Shop') }}</a>
                                                    </li>
                                                @endif
                                                @if ($setting->is_campaign == 1)
                                                    <li
                                                        class="{{ request()->routeIs('front.campaign') ? 'active' : '' }}">
                                                        <a href="{{ route('front.campaign') }}"><i
                                                                class="icon-chevron-right"></i>{{ __('Campaign') }}</a>
                                                    </li>
                                                @endif
                                                @if ($setting->is_brands == 1)
                                                    <li
                                                        class="{{ request()->routeIs('front.brand') ? 'active' : '' }}">
                                                        <a href="{{ route('front.brand') }}"><i
                                                                class="icon-chevron-right"></i>{{ __('Brand') }}</a>
                                                    </li>
                                                @endif

                                                @if ($setting->is_blog == 1)
                                                    <li
                                                        class="{{ request()->routeIs('front.blog*') ? 'active' : '' }}">
                                                        <a href="{{ route('front.blog') }}"><i
                                                                class="icon-chevron-right"></i>{{ __('Blog') }}</a>
                                                    </li>
                                                @endif
                                                <li class="t-h-dropdown">
                                                    <a class="" href="#"><i
                                                            class="icon-chevron-right"></i>{{ __('Pages') }} <i
                                                            class="icon-chevron-down"></i></a>
                                                    <div class="t-h-dropdown-menu">
                                                        @if ($setting->is_faq == 1)
                                                            <a class="{{ request()->routeIs('front.faq*') ? 'active' : '' }}"
                                                                href="{{ route('front.faq') }}"><i
                                                                    class="icon-chevron-right pr-2"></i>{{ __('Faq') }}</a>
                                                        @endif
                                                        @foreach (DB::table('pages')->wherePos(0)->orwhere('pos', 2)->get() as $page)
                                                            <a class="{{ request()->url() == route('front.page', $page->slug) ? 'active' : '' }} "
                                                                href="{{ route('front.page', $page->slug) }}"><i
                                                                    class="icon-chevron-right pr-2"></i>{{ $page->title }}</a>
                                                        @endforeach
                                                    </div>
                                                </li>

                                                @if ($setting->is_contact == 1)
                                                    <li
                                                        class="{{ request()->routeIs('front.contact') ? 'active' : '' }}">
                                                        <a href="{{ route('front.contact') }}"><i
                                                                class="icon-chevron-right"></i>{{ __('Contact') }}</a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </nav>
                                    </div>
                                    <div class="tab-pane fade" id="mcat" role="tabpanel"
                                        aria-labelledby="mcat-tab">
                                        <nav class="slideable-menu">
                                            @include('includes.mobile-category')

                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Navbar-->
        <div class="navbar">
            <div class="container">
                <div class="row g-3 w-100">
                    @if ($setting->is_show_category == 1)
                        <div class="col-lg-3">
                            @include('includes.categories')
                        </div>
                    @endif
                    <div class="col-lg-9 d-flex justify-content-between">
                        <div class="nav-inner">
                            @include('master.inc.site-menu')
                        </div>
                        @php
                            $free_shipping = DB::table('shipping_services')
                                ->whereStatus(1)
                                ->whereIsCondition(1)
                                ->first();
                        @endphp

                    </div>
                </div>
            </div>
        </div>

    </header>
    <!-- Page Content-->
    @yield('content')

    <!--    announcement banner section start   -->
    <a class="announcement-banner" href="#announcement-modal"></a>
    <div id="announcement-modal" class="mfp-hide white-popup">
        @if ($setting->announcement_type == 'newletter')
            <div class="announcement-with-content">
                <div class="left-area">
                    <img src="{{ url('/core/public/storage/images/' . $setting->announcement) }}" alt="">
                </div>
                <div class="right-area">
                    <h3 class="">{{ $setting->announcement_title }}</h3>
                    <p>{{ $setting->announcement_details }}</p>
                    <form class="subscriber-form" action="{{ route('front.subscriber.submit') }}" method="post">
                        @csrf
                        <div class="input-group">
                            <input class="form-control" type="email" name="email"
                                placeholder="{{ __('Your e-mail') }}">
                            <span class="input-group-addon"><i class="icon-mail"></i></span>
                        </div>
                        <div aria-hidden="true">
                            <input type="hidden" name="b_c7103e2c981361a6639545bd5_1194bb7544" tabindex="-1">
                        </div>

                        <button class="btn btn-primary btn-block mt-2" type="submit">
                            <span>{{ __('Subscribe') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <a href="{{ $setting->announcement_link }}">
                <img src="{{ url('/core/public/storage/images/' . $setting->announcement) }}" alt="">
            </a>
        @endif


    </div>
    <!--    announcement banner section end   -->

    <!-- Site Footer-->
    @php
        $formatBusinessTime = function ($time) {
            if (!$time) {
                return '';
            }

            try {
                return \Carbon\Carbon::parse($time)->format('H:i');
            } catch (\Throwable $e) {
                return trim($time);
            }
        };
        $footerWorkingDays = trim(rtrim($setting->working_days_from_to, ':'));
    @endphp
    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <!-- Contact Info-->
                    <section class="widget widget-light-skin">
                        <h3 class="widget-title">{{ __('Get In Touch') }}</h3>
                        <p class="mb-1"><strong>{{ __('Address') }}: </strong> {{ $setting->footer_address }}</p>
                        <p class="mb-1"><strong>{{ __('Phone') }}: </strong> {{ $setting->footer_phone }}</p>
                        <p class="mb-1"><strong>{{ __('Email') }}: </strong> {{ $setting->footer_email }}</p>
                        <ul class="list-unstyled text-sm">
                            <li><span class=""><strong>{{ $footerWorkingDays }}: </strong></span>{{ $formatBusinessTime($setting->friday_start) }} - {{ $formatBusinessTime($setting->friday_end) }}</li>
                        </ul>
                        @php
                            $links = json_decode($setting->social_link, true)['links'];
                            $icons = json_decode($setting->social_link, true)['icons'];

                        @endphp
                        <div class="footer-social-links">
                            @foreach ($links as $link_key => $link)
                                <a href="{{ $link }}"><span><i
                                            class="{{ $icons[$link_key] }}"></i></span></a>
                            @endforeach
                        </div>
                    </section>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <!-- Customer Info-->
                    <div class="widget widget-links widget-light-skin">
                        <h3 class="widget-title">{{ __('Usefull Links') }}</h3>
                        <ul>
                            @if ($setting->is_faq == 1)
                                <li>
                                    <a class="" href="{{ route('front.faq') }}">{{ __('Faq') }}</a>
                                </li>
                            @endif
                            @foreach (DB::table('pages')->wherePos(2)->orwhere('pos', 1)->get() as $page)
                                <li><a href="{{ route('front.page', $page->slug) }}">{{ $page->title }}</a></li>
                            @endforeach

                        </ul>
                    </div>
                </div>
                <div class="col-lg-4">
                    <!-- Subscription-->
                    <section class="widget">
                        <h3 class="widget-title">{{ __('Newsletter') }}</h3>
                        <form class="row subscriber-form" action="{{ route('front.subscriber.submit') }}"
                            method="post">
                            @csrf
                            <div class="col-sm-12">
                                <div class="input-group">
                                    <input class="form-control" type="email" name="email"
                                        placeholder="{{ __('Your e-mail') }}">
                                    <span class="input-group-addon"><i class="icon-mail"></i></span>
                                </div>
                                <div aria-hidden="true">
                                    <input type="hidden" name="b_c7103e2c981361a6639545bd5_1194bb7544"
                                        tabindex="-1">
                                </div>

                            </div>
                            <div class="col-sm-12">
                                <button class="btn btn-primary btn-block mt-2" type="submit">
                                    <span>{{ __('Subscribe') }}</span>
                                </button>
                            </div>
                            <div class="col-lg-12">
                                <p class="text-sm opacity-80 pt-2">
                                    {{ __('Subscribe to our Newsletter to receive early discount offers, latest news, sales and promo information.') }}
                                </p>
                            </div>
                        </form>
                        <div class="pt-3"><img class="d-block gateway_image"
                                src="{{ $setting->footer_gateway_img ? url('/core/public/storage/images/' . $setting->footer_gateway_img) : asset('system/resources/assets/images/placeholder.png') }}">
                        </div>
                    </section>
                </div>
            </div>
            <!-- Copyright-->
            <p class="footer-copyright"> {{ $setting->copy_right }}</p>
        </div>
    </footer>

    <!-- Back To Top Button-->
    <a class="scroll-to-top-btn" href="#">
        <i class="icon-chevron-up"></i>
    </a>
    <!-- Backdrop-->
    <div class="site-backdrop"></div>

    <!-- Cookie alert dialog  -->
    @if ($setting->is_cookie == 1)
        @include('cookie-consent::index')
    @endif
    <!-- Cookie alert dialog  -->

    @php
        $siteWhatsAppDigits = preg_replace('/\D+/', '', (string) $setting->site_whatsapp_phone);
        $siteWhatsAppMessage = $setting->site_whatsapp_message ?: 'Olá, vim pelo site e preciso de atendimento.';
        $siteWhatsAppAttendantName = $setting->site_whatsapp_attendant_name ?: 'Atendimento';
        $siteWhatsAppSupportMessage = $setting->site_whatsapp_support_message ?: 'Como podemos ajudar hoje?';
        $siteWhatsAppOfflineMessage = $setting->site_whatsapp_offline_message ?: 'Estamos fora do horário de atendimento. Sua mensagem será respondida assim que possível no próximo dia útil.';
        $siteWhatsAppWorkingDays = json_decode($setting->site_whatsapp_working_days ?? 'null', true);
        $siteWhatsAppWorkingDays = is_array($siteWhatsAppWorkingDays) ? $siteWhatsAppWorkingDays : [1, 2, 3, 4, 5];
        $siteWhatsAppNow = \Carbon\Carbon::now(config('app.timezone'));
        $siteWhatsAppStart = $setting->site_whatsapp_working_start ?: '08:00';
        $siteWhatsAppEnd = $setting->site_whatsapp_working_end ?: '18:00';
        $siteWhatsAppCurrentTime = $siteWhatsAppNow->format('H:i');
        $siteWhatsAppIsOpen = in_array($siteWhatsAppNow->dayOfWeek, $siteWhatsAppWorkingDays)
            && $siteWhatsAppCurrentTime >= $siteWhatsAppStart
            && $siteWhatsAppCurrentTime <= $siteWhatsAppEnd;
        $siteWhatsAppGreeting = $siteWhatsAppNow->hour < 12 ? 'Bom dia' : ($siteWhatsAppNow->hour < 18 ? 'Boa tarde' : 'Boa noite');
    @endphp

    @if ($setting->site_whatsapp_enabled && strlen($siteWhatsAppDigits) >= 10)
        <div class="site-whatsapp-widget site-whatsapp-widget--{{ $setting->site_whatsapp_position == 'left' ? 'left' : 'right' }}" id="site-whatsapp-widget">
            <div class="site-whatsapp-box" id="site-whatsapp-box" hidden>
                <button type="button" class="site-whatsapp-box__close" id="site-whatsapp-close" aria-label="Fechar">&times;</button>
                <div class="site-whatsapp-box__header">
                    <img src="{{ $setting->site_whatsapp_attendant_photo ? url('/core/public/storage/images/'.$setting->site_whatsapp_attendant_photo) : url('/core/public/storage/images/placeholder.png') }}" alt="{{ $siteWhatsAppAttendantName }}">
                    <div>
                        <strong>{{ $siteWhatsAppAttendantName }}</strong>
                        <span>{{ $siteWhatsAppIsOpen ? 'Online agora' : 'Fora do horário' }}</span>
                    </div>
                </div>
                <div class="site-whatsapp-box__body">
                    <p><b>{{ $siteWhatsAppGreeting }}!</b></p>
                    <p>{{ $siteWhatsAppIsOpen ? $siteWhatsAppSupportMessage : $siteWhatsAppOfflineMessage }}</p>
                </div>
                <a class="site-whatsapp-box__action" href="https://wa.me/{{ $siteWhatsAppDigits }}?text={{ urlencode($siteWhatsAppMessage) }}" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i> Iniciar conversa
                </a>
            </div>
            <button type="button" class="site-whatsapp-float" id="site-whatsapp-toggle" aria-label="WhatsApp">
            <i class="fab fa-whatsapp"></i>
            </button>
        </div>
        <style>
            .site-whatsapp-widget{position:fixed;bottom:24px;z-index:1030}.site-whatsapp-widget--right{right:24px}.site-whatsapp-widget--left{left:24px}.site-whatsapp-float{border:0;width:56px;height:56px;border-radius:50%;background:#25d366;color:#fff;display:flex;align-items:center;justify-content:center;font-size:30px;box-shadow:0 14px 30px rgba(37,211,102,.35);cursor:pointer}.site-whatsapp-float:hover{filter:brightness(.96)}
            .site-whatsapp-box{position:absolute;bottom:72px;width:320px;background:#fff;border-radius:8px;box-shadow:0 24px 70px rgba(17,24,39,.22);overflow:hidden;text-align:left}.site-whatsapp-widget--right .site-whatsapp-box{right:0}.site-whatsapp-widget--left .site-whatsapp-box{left:0}.site-whatsapp-box__close{position:absolute;right:8px;top:8px;border:0;background:rgba(255,255,255,.92);color:#111827;width:28px;height:28px;border-radius:50%;font-size:20px;line-height:1;cursor:pointer}.site-whatsapp-box__header{display:flex;align-items:center;gap:12px;background:#25d366;color:#fff;padding:18px}.site-whatsapp-box__header img{width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.85)}.site-whatsapp-box__header strong{display:block;font-size:16px}.site-whatsapp-box__header span{font-size:12px;opacity:.9}.site-whatsapp-box__body{padding:18px;color:#374151}.site-whatsapp-box__body p{margin:0 0 8px}.site-whatsapp-box__action{display:flex;align-items:center;justify-content:center;gap:8px;margin:0 18px 18px;padding:12px;border-radius:6px;background:#25d366;color:#fff;font-weight:700}.site-whatsapp-box__action:hover{color:#fff;text-decoration:none;filter:brightness(.96)}@media(max-width:420px){.site-whatsapp-widget{left:16px;right:16px}.site-whatsapp-widget--right,.site-whatsapp-widget--left{left:16px;right:16px}.site-whatsapp-box{width:100%;right:0;left:0}.site-whatsapp-float{margin-left:auto}}
        </style>
    @endif

    @if ($setting->is_pwa && $setting->pwa_install_popup_enabled)
        <div class="pwa-install-backdrop" id="pwa-install-backdrop" hidden></div>
        <div class="pwa-install-popup" id="pwa-install-popup" hidden>
            <button type="button" class="pwa-install-popup__close" id="pwa-install-close">&times;</button>
            <div class="pwa-install-popup__media">
                <img src="{{ $setting->pwa_install_popup_image ? url('/core/public/storage/images/'.$setting->pwa_install_popup_image) : ($setting->pwa_icon_512 ? url('/core/public/storage/images/'.$setting->pwa_icon_512) : ($setting->pwa_icon ? url('/core/public/storage/images/'.$setting->pwa_icon) : url('/core/public/storage/images/'.$setting->favicon))) }}" alt="{{ $setting->pwa_name ?: $setting->title }}">
            </div>
            <div class="pwa-install-popup__body">
                <h3>{{ $setting->pwa_install_popup_title ?: 'Instale nosso aplicativo' }}</h3>
                <p>{{ $setting->pwa_install_popup_text ?: 'Acesse mais rápido e use o site como aplicativo no seu celular.' }}</p>
                <div class="pwa-install-popup__actions">
                    <button type="button" class="btn btn-primary" id="pwa-install-confirm">{{ $setting->pwa_install_popup_button_text ?: 'Instalar agora' }}</button>
                    <button type="button" class="btn btn-link" id="pwa-install-later">{{ $setting->pwa_install_popup_later_text ?: 'Agora não' }}</button>
                </div>
            </div>
        </div>
        <style>
            .pwa-install-backdrop{position:fixed;inset:0;background:rgba(17,24,39,.48);z-index:1042}.pwa-install-popup{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:1043;width:min(520px,calc(100% - 28px));background:#fff;border-radius:8px;box-shadow:0 26px 70px rgba(17,24,39,.26);display:grid;grid-template-columns:128px 1fr;overflow:hidden}.pwa-install-popup__media{display:flex;align-items:center;justify-content:center;background:#f3f7ff;padding:22px}.pwa-install-popup__media img{width:88px;height:88px;object-fit:contain;border-radius:18px}.pwa-install-popup__body{padding:24px 26px 22px}.pwa-install-popup__body h3{margin:0 0 8px;font-size:22px}.pwa-install-popup__body p{color:#6b7280;margin-bottom:16px}.pwa-install-popup__actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.pwa-install-popup__close{position:absolute;right:10px;top:10px;border:0;background:#fff;color:#111827;width:34px;height:34px;border-radius:50%;font-size:24px;line-height:1;box-shadow:0 6px 18px rgba(17,24,39,.16)}@media(max-width:575px){.pwa-install-popup{grid-template-columns:1fr}.pwa-install-popup__media{padding:18px}.pwa-install-popup__body{text-align:center}.pwa-install-popup__actions{justify-content:center}}
        </style>
    @endif

    @php
        $promoPopupProduct = null;
        $promoPopupActive = (bool) $setting->promo_popup_enabled;
        if ($promoPopupActive && $setting->promo_popup_starts_at && now()->lt(\Carbon\Carbon::parse($setting->promo_popup_starts_at))) {
            $promoPopupActive = false;
        }
        if ($promoPopupActive && $setting->promo_popup_ends_at && now()->gt(\Carbon\Carbon::parse($setting->promo_popup_ends_at))) {
            $promoPopupActive = false;
        }
        if ($promoPopupActive && $setting->promo_popup_mode === 'product' && $setting->promo_popup_item_id) {
            $promoPopupProduct = \App\Models\Item::whereStatus(1)->find($setting->promo_popup_item_id);
            $promoPopupActive = (bool) $promoPopupProduct;
        }
        $promoPopupImage = $promoPopupProduct ? $promoPopupProduct->photo : $setting->promo_popup_image;
        $promoPopupLink = $promoPopupProduct ? route('front.product', $promoPopupProduct->slug) : $setting->promo_popup_link;
        $promoPopupTitle = $setting->promo_popup_title ?: ($promoPopupProduct ? $promoPopupProduct->name : 'Promoção especial');
        $promoPopupText = $setting->promo_popup_text ?: ($promoPopupProduct ? $promoPopupProduct->sort_details : 'Confira nossas ofertas ativas.');
    @endphp

    @if ($promoPopupActive || $setting->exit_popup_enabled)
        <div class="commerce-popup-backdrop" id="commerce-popup-backdrop" hidden></div>
        @if ($promoPopupActive)
            <div class="commerce-popup" id="promo-popup" hidden>
                <button type="button" class="commerce-popup__close" data-popup-close>&times;</button>
                @if ($promoPopupImage)
                    <img src="{{ url('/core/public/storage/images/'.$promoPopupImage) }}" alt="{{ $promoPopupTitle }}">
                @endif
                <div class="commerce-popup__body">
                    <div class="commerce-popup__badge">{{ $setting->promo_popup_badge ?: ($setting->promo_popup_campaign_type == 'blackfriday' ? 'BLACK FRIDAY' : 'OFERTA RELAMPAGO') }}</div>
                    <h3>{{ $promoPopupTitle }}</h3>
                    <p>{{ $promoPopupText }}</p>
                    @if ($promoPopupProduct)
                        <div class="commerce-popup__price">
                            @if ($promoPopupProduct->previous_price)
                                <span>{{ \App\Helpers\PriceHelper::setPrice($promoPopupProduct->previous_price) }}</span>
                            @endif
                            <strong>{{ \App\Helpers\PriceHelper::setPrice($promoPopupProduct->discount_price) }}</strong>
                        </div>
                    @endif
                    @if ($setting->promo_popup_ends_at)
                        <div class="commerce-popup-countdown" data-promo-end="{{ \Carbon\Carbon::parse($setting->promo_popup_ends_at)->toIso8601String() }}">
                            <div><strong data-promo-days>0</strong><small>Dias</small></div>
                            <div><strong data-promo-hours>0</strong><small>Horas</small></div>
                            <div><strong data-promo-minutes>0</strong><small>Min</small></div>
                            <div><strong data-promo-seconds>0</strong><small>Seg</small></div>
                        </div>
                    @endif
                    @if ($promoPopupLink)
                        <a class="btn btn-primary" href="{{ $promoPopupLink }}">{{ $setting->promo_popup_button_text ?: 'Ver oferta' }}</a>
                    @endif
                </div>
            </div>
        @endif
        @if ($setting->exit_popup_enabled)
            <div class="commerce-popup" id="exit-popup" hidden>
                <button type="button" class="commerce-popup__close" data-popup-close>&times;</button>
                <div class="commerce-popup__body">
                    <h3>{{ $setting->exit_popup_title ?: 'Antes de sair' }}</h3>
                    <p>{{ $setting->exit_popup_text ?: 'Aproveite um desconto especial antes de finalizar sua visita.' }}</p>
                    @if ($setting->exit_popup_coupon)
                        <div class="commerce-popup__coupon">{{ $setting->exit_popup_coupon }}</div>
                    @endif
                    @if ($setting->exit_popup_link)
                        <a class="btn btn-primary" href="{{ $setting->exit_popup_link }}">{{ $setting->exit_popup_button_text ?: 'Usar desconto' }}</a>
                    @endif
                </div>
            </div>
        @endif
        <style>
            .commerce-popup-backdrop{position:fixed;inset:0;background:rgba(17,24,39,.52);z-index:1060}.commerce-popup{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:1061;width:min(520px,calc(100% - 32px));background:#fff;border-radius:8px;box-shadow:0 28px 80px rgba(17,24,39,.28);overflow:hidden}
            .commerce-popup img{width:100%;max-height:260px;object-fit:cover}.commerce-popup__body{padding:28px;text-align:center}.commerce-popup__body h3{margin:0 0 12px}.commerce-popup__body p{color:#6b7280}.commerce-popup__close{position:absolute;right:10px;top:10px;border:0;background:#fff;color:#111827;width:34px;height:34px;border-radius:50%;font-size:24px;line-height:1;box-shadow:0 6px 18px rgba(17,24,39,.16)}.commerce-popup__coupon{display:inline-block;margin:8px 0 18px;padding:10px 18px;border:1px dashed #177dff;border-radius:6px;color:#177dff;font-weight:700;letter-spacing:.08em}.commerce-popup__badge{display:inline-block;margin-bottom:12px;padding:7px 12px;border-radius:4px;background:#111827;color:#fff;font-size:12px;font-weight:700;letter-spacing:.08em}.commerce-popup__price{display:flex;align-items:center;justify-content:center;gap:10px;margin:12px 0 18px}.commerce-popup__price span{text-decoration:line-through;color:#9ca3af}.commerce-popup__price strong{font-size:24px;color:#177dff}.commerce-popup-countdown{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:16px 0}.commerce-popup-countdown div{border:1px solid #e5e7eb;border-radius:6px;padding:8px 4px}.commerce-popup-countdown strong{display:block;color:#111827;font-size:20px}.commerce-popup-countdown small{color:#6b7280;text-transform:uppercase;font-size:10px}
        </style>
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
        var decimal_separator = '{!! $setting->decimal_separator !!}';
        var thousand_separator = '{!! $setting->thousand_separator !!}';
        window.omnimartLocale = '{{ str_replace('_', '-', $websiteLocale) }}';
    </script>

    <script>
        let language = {
            Days: '{{ __('Days') }}',
            Hrs: '{{ __('Hrs') }}',
            Min: '{{ __('Min') }}',
            Sec: '{{ __('Sec') }}',
        }
    </script>



    <!-- JavaScript (jQuery) libraries, plugins and custom scripts-->
    <script type="text/javascript" src="{{ asset('assets/front/js/plugins.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/back/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}">
    </script>
    <script type="text/javascript" src="{{ asset('assets/front/js/scripts.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/front/js/lazy.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/front/js/lazy.plugin.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/back/js/br-localization.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/front/js/myscript.js') }}"></script>
    @yield('script')

    @if ($setting->is_facebook_messenger == '1')
        <!-- Messenger Chat Plugin Code -->
        <div id="fb-root"></div>

        <!-- Your Chat Plugin code -->
        <div id="fb-customer-chat" class="fb-customerchat">
        </div>

        <script>
            var chatbox = document.getElementById('fb-customer-chat');
            chatbox.setAttribute("page_id", "{{ $setting->facebook_messenger }}");
            chatbox.setAttribute("attribution", "biz_inbox");
            window.fbAsyncInit = function() {
                FB.init({
                    xfbml: true,
                    version: 'v11.0'
                });
            };

            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
    @endif



    <script type="text/javascript">
        let mainurl = '{{ route('front.index') }}';

        let view_extra_index = 0;
        // Notifications
        function SuccessNotification(title) {
            $.notify({
                title: ` <strong>${title}</strong>`,
                message: '',
                icon: 'fas fa-check-circle'
            }, {
                element: 'body',
                position: null,
                type: "success",
                allow_dismiss: true,
                newest_on_top: false,
                showProgressbar: false,
                placement: {
                    from: "top",
                    align: "right"
                },
                offset: 20,
                spacing: 10,
                z_index: 1031,
                delay: 5000,
                timer: 1000,
                url_target: '_blank',
                mouse_over: null,
                animate: {
                    enter: 'animated fadeInDown',
                    exit: 'animated fadeOutUp'
                },
                onShow: null,
                onShown: null,
                onClose: null,
                onClosed: null,
                icon_type: 'class'
            });
        }

        function DangerNotification(title) {
            $.notify({
                // options
                title: ` <strong>${title}</strong>`,
                message: '',
                icon: 'fas fa-exclamation-triangle'
            }, {
                // settings
                element: 'body',
                position: null,
                type: "danger",
                allow_dismiss: true,
                newest_on_top: false,
                showProgressbar: false,
                placement: {
                    from: "top",
                    align: "right"
                },
                offset: 20,
                spacing: 10,
                z_index: 1031,
                delay: 5000,
                timer: 1000,
                url_target: '_blank',
                mouse_over: null,
                animate: {
                    enter: 'animated fadeInDown',
                    exit: 'animated fadeOutUp'
                },
                onShow: null,
                onShown: null,
                onClose: null,
                onClosed: null,
                icon_type: 'class'
            });
        }
        // Notifications Ends
    </script>

    @if (Session::has('error'))
        <script>
            $(document).ready(function() {
                DangerNotification('{{ Session::get('error') }}')
            })
        </script>
    @endif
    @if (Session::has('success'))
        <script>
            $(document).ready(function() {
                SuccessNotification('{{ Session::get('success') }}');
            })
        </script>
    @endif

    @if ($setting->is_pwa)
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('{{ route('front.pwa.sw') }}');
            }
        </script>
    @endif

    @if ($setting->is_pwa && $setting->pwa_install_popup_enabled)
        <script>
            (function () {
                var deferredPrompt = null;
                var popup = document.getElementById('pwa-install-popup');
                var backdrop = document.getElementById('pwa-install-backdrop');
                var confirmButton = document.getElementById('pwa-install-confirm');
                var laterButton = document.getElementById('pwa-install-later');
                var closeButton = document.getElementById('pwa-install-close');

                function isInstalled() {
                    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
                }

                function openInstallPopup() {
                    if (!popup || !backdrop || isInstalled() || sessionStorage.getItem('pwa_install_later')) return;
                    backdrop.hidden = false;
                    popup.hidden = false;
                }

                function closeInstallPopup(skipSession) {
                    if (backdrop) backdrop.hidden = true;
                    if (popup) popup.hidden = true;
                    if (skipSession) sessionStorage.setItem('pwa_install_later', '1');
                }

                window.addEventListener('beforeinstallprompt', function (event) {
                    event.preventDefault();
                    deferredPrompt = event;
                    setTimeout(openInstallPopup, {{ (int) ($setting->pwa_install_popup_delay ?: 3) * 1000 }});
                });

                if (confirmButton) {
                    confirmButton.addEventListener('click', function () {
                        if (!deferredPrompt) {
                            closeInstallPopup(true);
                            return;
                        }
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.finally(function () {
                            deferredPrompt = null;
                            closeInstallPopup(true);
                        });
                    });
                }

                [laterButton, closeButton, backdrop].forEach(function (element) {
                    if (!element) return;
                    element.addEventListener('click', function () {
                        closeInstallPopup(true);
                    });
                });
            })();
        </script>
    @endif

    @if ($setting->site_whatsapp_enabled && strlen($siteWhatsAppDigits) >= 10)
        <script>
            (function () {
                var toggle = document.getElementById('site-whatsapp-toggle');
                var box = document.getElementById('site-whatsapp-box');
                var close = document.getElementById('site-whatsapp-close');

                if (!toggle || !box) return;

                toggle.addEventListener('click', function () {
                    box.hidden = !box.hidden;
                });

                if (close) {
                    close.addEventListener('click', function () {
                        box.hidden = true;
                    });
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        box.hidden = true;
                    }
                });
            })();
        </script>
    @endif

    @if ($promoPopupActive || $setting->exit_popup_enabled)
        <script>
            (function () {
                var backdrop = document.getElementById('commerce-popup-backdrop');
                var storage = null;
                try {
                    storage = window.sessionStorage;
                } catch (e) {
                    storage = null;
                }

                function hasSeen(key) {
                    return storage ? storage.getItem(key) : false;
                }

                function setSeen(key) {
                    if (storage) storage.setItem(key, '1');
                }

                function hasOpenPopup() {
                    return !!document.querySelector('.commerce-popup:not([hidden])');
                }

                function openPopup(id) {
                    var popup = document.getElementById(id);
                    if (!popup || !backdrop) return false;
                    if (window.jQuery && window.jQuery.magnificPopup) {
                        window.jQuery.magnificPopup.close();
                    }
                    document.querySelectorAll('.commerce-popup').forEach(function (currentPopup) {
                        currentPopup.hidden = true;
                    });
                    backdrop.hidden = false;
                    popup.hidden = false;
                    return true;
                }
                function closePopups() {
                    if (backdrop) backdrop.hidden = true;
                    document.querySelectorAll('.commerce-popup').forEach(function (popup) {
                        popup.hidden = true;
                    });
                }
                document.querySelectorAll('[data-popup-close]').forEach(function (button) {
                    button.addEventListener('click', closePopups);
                });
                if (backdrop) backdrop.addEventListener('click', closePopups);
                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') closePopups();
                });

                @if ($promoPopupActive)
                    var countdown = document.querySelector('.commerce-popup-countdown');
                    if (countdown) {
                        var promoEnd = new Date(countdown.dataset.promoEnd).getTime();
                        var tickPromo = function () {
                            var diff = Math.max(0, promoEnd - Date.now());
                            var days = Math.floor(diff / 86400000);
                            var hours = Math.floor((diff % 86400000) / 3600000);
                            var minutes = Math.floor((diff % 3600000) / 60000);
                            var seconds = Math.floor((diff % 60000) / 1000);
                            countdown.querySelector('[data-promo-days]').textContent = days;
                            countdown.querySelector('[data-promo-hours]').textContent = hours;
                            countdown.querySelector('[data-promo-minutes]').textContent = minutes;
                            countdown.querySelector('[data-promo-seconds]').textContent = seconds;
                            if (diff <= 0) {
                                closePopups();
                            }
                        };
                        tickPromo();
                        setInterval(tickPromo, 1000);
                    }

                    if (!hasSeen('promo_popup_seen')) {
                        setTimeout(function () {
                            if (openPopup('promo-popup')) {
                                setSeen('promo_popup_seen');
                            }
                        }, {{ (int) ($setting->promo_popup_delay ?: 3) * 1000 }});
                    }
                @endif

                @if ($setting->exit_popup_enabled)
                    var exitPopupArmed = false;
                    var exitTouchScroll = 0;
                    var lastScrollY = window.scrollY || window.pageYOffset || 0;

                    setTimeout(function () {
                        exitPopupArmed = true;
                    }, 900);

                    function showExitPopup() {
                        if (!exitPopupArmed || hasSeen('exit_popup_seen') || hasOpenPopup()) return;
                        if (openPopup('exit-popup')) {
                            setSeen('exit_popup_seen');
                        }
                    }

                    document.addEventListener('mouseout', function (event) {
                        var leavingWindow = !event.relatedTarget && !event.toElement;
                        if (leavingWindow && event.clientY <= 8) {
                            showExitPopup();
                        }
                    });

                    document.documentElement.addEventListener('mouseleave', function (event) {
                        if (event.clientY <= 8) {
                            showExitPopup();
                        }
                    });

                    window.addEventListener('scroll', function () {
                        var currentScrollY = window.scrollY || window.pageYOffset || 0;
                        if (currentScrollY > 360) {
                            exitTouchScroll = 1;
                        }
                        if (exitTouchScroll && currentScrollY < 120 && lastScrollY - currentScrollY > 70) {
                            showExitPopup();
                        }
                        lastScrollY = currentScrollY;
                    }, { passive: true });
                @endif
            })();
        </script>
    @endif

</body>

</html>
