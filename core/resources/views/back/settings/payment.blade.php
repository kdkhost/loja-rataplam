@extends('master.back')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-sm-flex align-items-center justify-content-between">
                    <h3 class=" mb-0"><b>{{ __('Payment') }}</b></h3>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12">

                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body ">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="nav flex-column m-3 nav-pills nav-secondary" id="v-pills-tab" role="tablist"
                                    aria-orientation="vertical">

                                    <a class="nav-link active" data-toggle="pill"
                                        href="#cod">{{ __('Cash On Delivery') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#stripe">{{ __('Stripe') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#paypal">{{ __('Paypal') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#molly">{{ __('Mollie') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#paytm">{{ __('Paytm') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#razorpay">{{ __('Razorpay') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#sslcommerz">{{ __('SSL commerz') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#mercadopago">Mercado Pago</a>
                                    <a class="nav-link" data-toggle="pill" href="#authorize">{{ __('Authorize.Net') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#paystack">{{ __('Paystack') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#flutterwave">{{ __('Flutterwave') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#paytabs">{{ __('Paytabs') }}</a>
                                    <a class="nav-link" data-toggle="pill" href="#bank">{{ __('Bank Transfer') }}</a>

                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="p-5">
                                    <div class="admin-form">

                                        @include('alerts.alerts')

                                        <div class="container pl-0 pr-0 ml-0 mr-0 w-100 mw-100">
                                            <div id="tabs">
                                                <!-- Tab panes -->
                                                <div class="tab-content">
                                                    <div id="cod" class="container tab-pane active"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf
                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $cod->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Cash On Delivery') }}</span>
                                                                        </label>
                                                                    </div>

                                                                    <div
                                                                        class="image-show {{ $cod->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $cod->name }}">
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $cod->photo ? url('/core/public/storage/images/' . $cod->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    alt="No Image Found">
                                                                            </div>
</div>

                                                                        <div class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" accept="image/*"
                                                                                    class="upload-photo" name="photo"
                                                                                    id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5" placeholder="{{ __('Enter Text') }}">{{ $cod->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="cod">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary ">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="stripe" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf



                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $stripe->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Stripe') }}</span>
                                                                        </label>
                                                                    </div>


                                                                    <div
                                                                        class="image-show {{ $stripe->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $stripe->photo ? url('/core/public/storage/images/' . $stripe->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" accept="image/*"
                                                                                    class="upload-photo" name="photo"
                                                                                    id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $stripe->name }}">
                                                                        </div>
                                                                        @foreach ($stripeData as $pkey => $pdata)
                                                                            <div class="form-group">
                                                                                <label
                                                                                    for="inp-{{ __($pkey) }}">{{ __($stripe->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="inp-{{ __($pkey) }}"
                                                                                    name="pkey[{{ __($pkey) }}]"
                                                                                    placeholder="{{ __($stripe->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                    value="{{ $pdata }}">
                                                                            </div>
                                                                        @endforeach

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5" placeholder="{{ __('Enter Text') }}">{{ $stripe->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="stripe">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary ">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="paypal" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $paypal->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Paypal') }}</span>
                                                                        </label>
                                                                    </div>


                                                                    <div
                                                                        class="image-show {{ $paypal->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $paypal->photo ? url('/core/public/storage/images/' . $paypal->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    alt="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" accept="image/*"
                                                                                    class="upload-photo" name="photo"
                                                                                    id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>


                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $paypal->name }}">
                                                                        </div>

                                                                        @foreach ($paypalData as $pkey => $pdata)
                                                                            @if ($pkey == 'check_sandbox')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pkey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ $pdata == 1 ? 'checked' : '' }}
                                                                                            id="{{ $pkey }}">
                                                                                        <label class="custom-control-label"
                                                                                            for="{{ $pkey }}">
                                                                                            {{ __($paypal->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group">
                                                                                    <label
                                                                                        for="inp-{{ __($pkey) }}">{{ __($paypal->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pkey) }}"
                                                                                        name="pkey[{{ __($pkey) }}]"
                                                                                        placeholder="{{ __($paypal->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                        value="{{ $pdata }}">
                                                                                </div>
                                                                            @endif
                                                                        @endforeach

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5" placeholder="{{ __('Enter Text') }}">{{ $paypal->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="paypal">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary ">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>
                                                    <div id="molly" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $molly->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Mollie') }}</span>
                                                                        </label>
                                                                    </div>



                                                                    <div
                                                                        class="image-show {{ $molly->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $molly->photo ? url('/core/public/storage/images/' . $molly->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    alt="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" accept="image/*"
                                                                                    class="upload-photo" name="photo"
                                                                                    id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $molly->name }}">
                                                                        </div>

                                                                        @foreach ($mollyData as $pkey => $pdata)
                                                                            <div class="form-group">
                                                                                <label
                                                                                    for="inp-{{ __($pkey) }}">{{ __($molly->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="inp-{{ __($pkey) }}"
                                                                                    name="pkey[{{ __($pkey) }}]"
                                                                                    placeholder="{{ __($molly->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                    value="{{ $pdata }}">
                                                                            </div>
                                                                        @endforeach

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="mollie">

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5" placeholder="{{ __('Enter Text') }}">{{ $molly->text }}</textarea>
                                                                        </div>

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary ">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="paytm" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $paytm->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Paytm') }}</span>
                                                                        </label>
                                                                    </div>



                                                                    <div
                                                                        class="image-show {{ $paytm->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $paytm->photo ? url('/core/public/storage/images/' . $paytm->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $paytm->name }}">
                                                                        </div>

                                                                        @foreach ($paytmData as $pakey => $paytmDat)
                                                                            @if ($pakey == 'paytm_mode')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pakey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ $paytmDat == 1 ? 'checked' : '' }}
                                                                                            id="{{ $pakey }}"
                                                                                            value="1">
                                                                                        <label class="custom-control-label"
                                                                                            for="{{ $pakey }}">
                                                                                            {{ __(ucwords(str_replace('_', ' ', $pakey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group">
                                                                                    <label
                                                                                        for="inp-{{ __($pakey) }}">{{ __($paytm->name . ' ' . ucwords(str_replace('_', ' ', $pakey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pakey) }}"
                                                                                        name="pkey[{{ __($pakey) }}]"
                                                                                        placeholder="{{ __($paytm->name . ' ' . ucwords(str_replace('_', ' ', $pakey))) }}"
                                                                                        value="{{ $paytmDat }}">
                                                                                </div>
                                                                            @endif
                                                                        @endforeach

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $paytm->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="paytm">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="sslcommerz" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $sslcommerz->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display sslcommerz') }}</span>
                                                                        </label>
                                                                    </div>


                                                                    <div
                                                                        class="image-show {{ $sslcommerz->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $sslcommerz->photo ? url('/core/public/storage/images/' . $sslcommerz->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>


                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $sslcommerz->name }}">
                                                                        </div>

                                                                        @foreach ($sslcommerzData as $pkey => $sslcommerzData)
                                                                            @if ($pkey == 'check_sandbox')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pkey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ $sslcommerzData == 1 ? 'checked' : '' }}
                                                                                            id="ssl{{ $pkey }}">
                                                                                        <label class="custom-control-label"
                                                                                            for="ssl{{ $pkey }}">
                                                                                            {{ __($sslcommerz->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group col-xl-12">
                                                                                    <label
                                                                                        for="inp-{{ __($pkey) }}">{{ __($sslcommerz->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pkey) }}"
                                                                                        name="pkey[{{ __($pkey) }}]"
                                                                                        placeholder="{{ __($sslcommerz->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                        value="{{ $sslcommerzData }}"
                                                                                        required>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $sslcommerz->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="sslcommerz">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="mercadopago" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.mercadopago.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $mercadopago->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">Exibir Mercado Pago</span>
                                                                        </label>
                                                                    </div>



                                                                    <div
                                                                        class="image-show {{ $mercadopago->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $mercadopago->photo ? url('/core/public/storage/images/' . $mercadopago->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        @php
                                                                            $mercadoPagoDefaults = [
                                                                                'public_key' => '',
                                                                                'token' => '',
                                                                                'check_sandbox' => 1,
                                                                                'pix_enabled' => 1,
                                                                                'credit_card_enabled' => 1,
                                                                                'debit_card_enabled' => 0,
                                                                                'pix_expiration_minutes' => 30,
                                                                                'fee_pass_to_customer' => 0,
                                                                                'fee_percent' => 0,
                                                                                'fee_fixed' => 0,
                                                                                'max_installments' => 1,
                                                                            ];
                                                                            $mercadoPagoConfig = array_merge($mercadoPagoDefaults, $mercadopagoData ?: []);
                                                                        @endphp

                                                                        <div class="form-group">
                                                                            <label for="mercadopago_name">Nome exibido no checkout *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="mercadopago_name"
                                                                                value="{{ $mercadopago->name ?: 'Mercado Pago' }}">
                                                                        </div>

                                                                        <div class="form-group col-xl-12">
                                                                            <label for="mercadopago_public_key">Chave pública *</label>
                                                                            <input type="text" class="form-control"
                                                                                id="mercadopago_public_key"
                                                                                name="pkey[public_key]"
                                                                                placeholder="APP_USR ou TEST-..."
                                                                                value="{{ $mercadoPagoConfig['public_key'] }}"
                                                                                required>
                                                                        </div>

                                                                        <div class="form-group col-xl-12">
                                                                            <label for="mercadopago_token">Access token *</label>
                                                                            <input type="text" class="form-control"
                                                                                id="mercadopago_token"
                                                                                name="pkey[token]"
                                                                                placeholder="APP_USR ou TEST-..."
                                                                                value="{{ $mercadoPagoConfig['token'] }}"
                                                                                required>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="form-group col-xl-6 col-md-6">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox"
                                                                                        name="pkey[check_sandbox]"
                                                                                        class="custom-control-input"
                                                                                        {{ $mercadoPagoConfig['check_sandbox'] == 1 ? 'checked' : '' }}
                                                                                        id="mercadopago_check_sandbox">
                                                                                    <label class="custom-control-label"
                                                                                        for="mercadopago_check_sandbox">
                                                                                        Usar ambiente de teste
                                                                                    </label>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group col-xl-6 col-md-6">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox"
                                                                                        name="pkey[pix_enabled]"
                                                                                        class="custom-control-input"
                                                                                        {{ $mercadoPagoConfig['pix_enabled'] == 1 ? 'checked' : '' }}
                                                                                        id="mercadopago_pix_enabled">
                                                                                    <label class="custom-control-label"
                                                                                        for="mercadopago_pix_enabled">
                                                                                        Aceitar Pix
                                                                                    </label>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group col-xl-6 col-md-6">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox"
                                                                                        name="pkey[credit_card_enabled]"
                                                                                        class="custom-control-input"
                                                                                        {{ $mercadoPagoConfig['credit_card_enabled'] == 1 ? 'checked' : '' }}
                                                                                        id="mercadopago_credit_card_enabled">
                                                                                    <label class="custom-control-label"
                                                                                        for="mercadopago_credit_card_enabled">
                                                                                        Aceitar cartão de crédito
                                                                                    </label>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group col-xl-6 col-md-6">
                                                                                <div class="custom-control custom-checkbox">
                                                                                    <input type="checkbox"
                                                                                        class="custom-control-input"
                                                                                        id="mercadopago_debit_card_enabled"
                                                                                        disabled>
                                                                                    <label class="custom-control-label"
                                                                                        for="mercadopago_debit_card_enabled">
                                                                                        Cartão de débito bloqueado
                                                                                    </label>
                                                                                </div>
                                                                                <input type="hidden" name="pkey[debit_card_enabled]" value="0">
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="form-group col-xl-6">
                                                                                <label for="mercadopago_pix_expiration_minutes">Expiração do Pix em minutos</label>
                                                                                <input type="number" min="5" max="4320"
                                                                                    class="form-control"
                                                                                    id="mercadopago_pix_expiration_minutes"
                                                                                    name="pkey[pix_expiration_minutes]"
                                                                                    value="{{ $mercadoPagoConfig['pix_expiration_minutes'] }}">
                                                                            </div>

                                                                            <div class="form-group col-xl-6">
                                                                                <label for="mercadopago_max_installments">Parcelas máximas no crédito</label>
                                                                                <select class="form-control"
                                                                                    id="mercadopago_max_installments"
                                                                                    name="pkey[max_installments]">
                                                                                    @for ($installment = 1; $installment <= 12; $installment++)
                                                                                        <option value="{{ $installment }}"
                                                                                            {{ (int) $mercadoPagoConfig['max_installments'] === $installment ? 'selected' : '' }}>
                                                                                            {{ $installment }}x
                                                                                        </option>
                                                                                    @endfor
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="border rounded p-3 mb-3">
                                                                            <div class="custom-control custom-checkbox mb-3">
                                                                                <input type="checkbox"
                                                                                    name="pkey[fee_pass_to_customer]"
                                                                                    class="custom-control-input"
                                                                                    {{ $mercadoPagoConfig['fee_pass_to_customer'] == 1 ? 'checked' : '' }}
                                                                                    id="mercadopago_fee_pass_to_customer">
                                                                                <label class="custom-control-label"
                                                                                    for="mercadopago_fee_pass_to_customer">
                                                                                    Repassar taxa do Mercado Pago ao cliente
                                                                                </label>
                                                                            </div>

                                                                            <div class="row">
                                                                                <div class="form-group col-xl-6 mb-xl-0">
                                                                                    <label for="mercadopago_fee_percent">Taxa percentual (%)</label>
                                                                                    <input type="text" inputmode="decimal"
                                                                                        class="form-control"
                                                                                        id="mercadopago_fee_percent"
                                                                                        name="pkey[fee_percent]"
                                                                                        value="{{ str_replace('.', ',', $mercadoPagoConfig['fee_percent']) }}">
                                                                                </div>

                                                                                <div class="form-group col-xl-6 mb-0">
                                                                                    <label for="mercadopago_fee_fixed">Taxa fixa em reais (R$)</label>
                                                                                    <input type="text" inputmode="decimal"
                                                                                        class="form-control"
                                                                                        id="mercadopago_fee_fixed"
                                                                                        name="pkey[fee_fixed]"
                                                                                        value="{{ str_replace('.', ',', $mercadoPagoConfig['fee_fixed']) }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="text">Texto exibido no checkout *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="Informe a mensagem exibida ao cliente no checkout">{{ $mercadopago->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="mercadopago">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">Salvar Mercado Pago</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="authorize" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $authorize->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Authorize.Net') }}</span>
                                                                        </label>
                                                                    </div>


                                                                    <div
                                                                        class="image-show {{ $authorize->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $authorize->photo ? url('/core/public/storage/images/' . $authorize->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $authorize->name }}">
                                                                        </div>

                                                                        @foreach ($authorizeData as $pkey => $authorizeData)
                                                                            @if ($pkey == 'check_sandbox')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pkey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ $authorizeData == 1 ? 'checked' : '' }}
                                                                                            id="mer{{ $pkey }}">
                                                                                        <label class="custom-control-label"
                                                                                            for="mer{{ $pkey }}">
                                                                                            {{ __($authorize->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group col-xl-12">
                                                                                    <label
                                                                                        for="inp-{{ __($pkey) }}">{{ __($authorize->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pkey) }}"
                                                                                        name="pkey[{{ __($pkey) }}]"
                                                                                        placeholder="{{ __($authorize->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                        value="{{ $authorizeData }}"
                                                                                        required>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $authorize->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="authorize">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="paystack" class="container tab-pane"><br>

                                                        <div class="row justify-content-center">

                                                            <div class="col-lg-8">

                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $paystack->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Paystack') }}</span>
                                                                        </label>
                                                                    </div>



                                                                    <div
                                                                        class="image-show {{ $paystack->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $paystack->photo ? url('/core/public/storage/images/' . $paystack->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $paystack->name }}">
                                                                        </div>

                                                                        @foreach ($paystackData as $pkey => $paystackData)
                                                                            @if ($pkey == 'check_sandbox')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pkey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ $paystackData->status == 1 ? 'checked' : '' }}
                                                                                            id="mer{{ $pkey }}">
                                                                                        <label class="custom-control-label"
                                                                                            for="mer{{ $pkey }}">
                                                                                            {{ __($paystack->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group col-xl-12">
                                                                                    <label
                                                                                        for="inp-{{ __($pkey) }}">{{ __($paystack->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pkey) }}"
                                                                                        name="pkey[{{ __($pkey) }}]"
                                                                                        placeholder="{{ __($paystack->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                        value="{{ $paystackData }}"
                                                                                        required>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $paystack->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="paystack">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="bank" class="container tab-pane"><br>
                                                        <div class="row justify-content-center">
                                                            <div class="col-lg-8">
                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">
                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $bank->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Bank Transfer') }}</span>
                                                                        </label>
                                                                    </div>
                                                                    <div
                                                                        class="image-show {{ $bank->status == 1 ? '' : 'd-none' }}">
                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $bank->photo ? url('/core/public/storage/images/' . $bank->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $bank->name }}">
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control text-editor" rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $bank->text }}</textarea>
                                                                        </div>

                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="bank">

                                                                    </div>

                                                                    <div>

                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>

                                                                    </div>

                                                                </form>

                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div id="razorpay" class="container tab-pane"><br>
                                                        <div class="row justify-content-center">
                                                            <div class="col-lg-8">
                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $razorpay->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Razorpay') }}</span>
                                                                        </label>
                                                                    </div>

                                                                    <div
                                                                        class="image-show {{ $razorpay->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $razorpay->photo ? url('/core/public/storage/images/' . $razorpay->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $razorpay->name }}">
                                                                        </div>

                                                                        @foreach ($razorpayData as $pkey => $razorpayData)
                                                                            @if ($pkey == 'check_sandbox')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pkey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ $razorpayData->status == 1 ? 'checked' : '' }}
                                                                                            id="mer{{ $pkey }}">
                                                                                        <label class="custom-control-label"
                                                                                            for="mer{{ $pkey }}">
                                                                                            {{ __($razorpay->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group col-xl-12">
                                                                                    <label
                                                                                        for="inp-{{ __($pkey) }}">{{ __($razorpay->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pkey) }}"
                                                                                        name="pkey[{{ __($pkey) }}]"
                                                                                        placeholder="{{ __($razorpay->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                        value="{{ $razorpayData }}"
                                                                                        required>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $razorpay->text }}</textarea>
                                                                        </div>
                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="razorpay">
                                                                    </div>
                                                                    <div>
                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div id="flutterwave" class="container tab-pane"><br>
                                                        <div class="row justify-content-center">
                                                            <div class="col-lg-8">
                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $flutterwave->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Flutterwave') }}</span>
                                                                        </label>
                                                                    </div>

                                                                    <div
                                                                        class="image-show {{ $flutterwave->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $flutterwave->photo ? url('/core/public/storage/images/' . $flutterwave->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $flutterwave->name }}">
                                                                        </div>

                                                                        @foreach ($flutterwaveData as $pkey => $flutterwaveData)
                                                                            @if ($pkey == 'check_sandbox')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pkey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ $flutterwaveData->status == 1 ? 'checked' : '' }}
                                                                                            id="mer{{ $pkey }}">
                                                                                        <label class="custom-control-label"
                                                                                            for="mer{{ $pkey }}">
                                                                                            {{ __($flutterwave->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group col-xl-12">
                                                                                    <label
                                                                                        for="inp-{{ __($pkey) }}">{{ __($flutterwave->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pkey) }}"
                                                                                        name="pkey[{{ __($pkey) }}]"
                                                                                        placeholder="{{ __($flutterwave->name . ' ' . ucwords(str_replace('_', ' ', $pkey))) }}"
                                                                                        value="{{ $flutterwaveData }}"
                                                                                        required>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $flutterwave->text }}</textarea>
                                                                        </div>
                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="flutterwave">
                                                                    </div>
                                                                    <div>
                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <div id="paytabs" class="container tab-pane"><br>
                                                        <div class="row justify-content-center">
                                                            <div class="col-lg-8">
                                                                <form action="{{ route('back.setting.payment.update') }}"
                                                                    method="POST" enctype="multipart/form-data">

                                                                    @csrf

                                                                    <div class="form-group">
                                                                        <label class="switch-primary">
                                                                            <input type="checkbox"
                                                                                class="switch switch-bootstrap "
                                                                                name="status" value="1"
                                                                                {{ $paytabs->status == 1 ? 'checked' : '' }}>
                                                                            <span class="switch-body"></span>
                                                                            <span
                                                                                class="switch-text">{{ __('Display Paytabs') }}</span>
                                                                        </label>
                                                                    </div>

                                                                    <div
                                                                        class="image-show {{ $paytabs->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label
                                                                                for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $paytabs->photo ? url('/core/public/storage/images/' . $flutterwave->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    stripe="No Image Found">
                                                                            </div>
</div>

                                                                        <div
                                                                            class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span
                                                                                    class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="name">{{ __('Enter Name') }}
                                                                                *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                value="{{ $paytabs->name }}">
                                                                        </div>

                                                                        @foreach ($paytabsData as $pakey => $paytabsData)
                                                                            @if ($pakey == 'check_sandbox')
                                                                                <div class="form-group  col-xl-4 col-md-6">
                                                                                    <div
                                                                                        class="custom-control custom-checkbox">
                                                                                        <input type="checkbox"
                                                                                            name="pkey[{{ __($pakey) }}]"
                                                                                            class="custom-control-input"
                                                                                            {{ @$paytabsData == 1 ? 'checked' : '' }}
                                                                                            id="pay{{ $pakey }}">
                                                                                        <label class="custom-control-label"
                                                                                            for="pay{{ $pakey }}">
                                                                                            {{ __($paytabs->name . ' ' . ucwords(str_replace('_', ' ', $pakey))) }}
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="form-group col-xl-12">
                                                                                    <label
                                                                                        for="inp-{{ __($pakey) }}">{{ __($paytabs->name . ' ' . ucwords(str_replace('_', ' ', $pakey))) }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        id="inp-{{ __($pakey) }}"
                                                                                        name="pkey[{{ __($pakey) }}]"
                                                                                        placeholder="{{ __($paytabs->name . ' ' . ucwords(str_replace('_', ' ', $pakey))) }}"
                                                                                        value="{{ $paytabsData }}"
                                                                                        required>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                        <div class="form-group">
                                                                            <label for="text">{{ __('Enter Text') }}
                                                                                *</label>
                                                                            <textarea name="text" id="text" class="form-control " rows="5"
                                                                                placeholder="{{ __('Enter Text') }}">{{ $paytabs->text }}</textarea>
                                                                        </div>
                                                                        <input type="hidden" name="unique_keyword"
                                                                            value="paytabs">
                                                                    </div>
                                                                    <div>
                                                                        <div
                                                                            class="form-group d-flex justify-content-center">
                                                                            <button type="submit"
                                                                                class="btn btn-secondary btn-block w-50">{{ __('Submit') }}</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>



                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    @endsection
