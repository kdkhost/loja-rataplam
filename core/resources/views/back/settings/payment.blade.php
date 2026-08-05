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
                                                                            <span class="switch-text">Exibir Mercado Pago</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="image-show {{ $mercadopago->status == 1 ? '' : 'd-none' }}">

                                                                        <div class="form-group col-xl-12">
                                                                            <label for="name">{{ __('Current Image') }}</label>
                                                                            <div class="col-lg-12 pb-1">
                                                                                <img class="admin-setting-img"
                                                                                    src="{{ $mercadopago->photo ? url('/core/public/storage/images/' . $mercadopago->photo) : url('/core/public/storage/images/placeholder.png') }}"
                                                                                    alt="No Image Found">
                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group position-relative col-xl-12">
                                                                            <label class="file">
                                                                                <input type="file" class="upload-photo"
                                                                                    name="photo" id="file"
                                                                                    aria-label="File browser example">
                                                                                <span class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="mercadopago_name">Nome exibido no checkout *</label>
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="mercadopago_name"
                                                                                value="{{ $mercadopago->name ?: 'Mercado Pago' }}">
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="text">Texto exibido no checkout *</label>
                                                                            <textarea name="text" id="text" class="form-control" rows="3"
                                                                                placeholder="Informe a mensagem exibida ao cliente no checkout">{{ $mercadopago->text }}</textarea>
                                                                        </div>

                                                                        <h5 class="mt-4 mb-3">Configuração Técnica</h5>

                                                                        <div class="form-group">
                                                                            <label for="mode">Ambiente</label>
                                                                            <select class="form-control" id="mode" name="mode">
                                                                                <option value="sandbox" {{ ($mercadopagoAdminConfig['mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Teste)</option>
                                                                                <option value="production" {{ ($mercadopagoAdminConfig['mode'] ?? 'sandbox') === 'production' ? 'selected' : '' }}>Produção</option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="border rounded p-3 mb-3">
                                                                            <h6 class="mb-3">Credenciais Sandbox</h6>

                                                                            <div class="form-group">
                                                                                <label for="sandbox_public_key">Public Key Sandbox</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="sandbox_public_key"
                                                                                    name="sandbox_public_key"
                                                                                    placeholder="TEST-..."
                                                                                    value="{{ $mercadopagoAdminConfig['sandbox_public_key'] ?? '' }}">
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label for="sandbox_access_token">Access Token Sandbox</label>
                                                                                <input type="password" class="form-control"
                                                                                    id="sandbox_access_token"
                                                                                    name="sandbox_access_token"
                                                                                    placeholder="Cole o token aqui"
                                                                                    value=""
                                                                                    autocomplete="new-password">
                                                                            </div>

                                                                            <div class="small text-muted mb-2">
                                                                                Status: {{ $mercadopagoAdminConfig['sandbox_token_configured'] ?? false ? 'Credencial configurada' : 'Credencial não configurada' }}
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label for="sandbox_webhook_secret">Webhook Secret Sandbox</label>
                                                                                <input type="password" class="form-control"
                                                                                    id="sandbox_webhook_secret"
                                                                                    name="sandbox_webhook_secret"
                                                                                    placeholder="Cole o segredo aqui"
                                                                                    value=""
                                                                                    autocomplete="new-password">
                                                                            </div>

                                                                            <div class="small text-muted mb-2">
                                                                                Status: {{ $mercadopagoAdminConfig['sandbox_secret_configured'] ?? false ? 'Segredo configurado' : 'Segredo não configurado' }}
                                                                            </div>

                                                                            <div class="form-check">
                                                                                <input type="checkbox" class="form-check-input"
                                                                                    id="remove_sandbox_token"
                                                                                    name="remove_sandbox_token"
                                                                                    value="1">
                                                                                <label class="form-check-label" for="remove_sandbox_token">
                                                                                    Remover Access Token Sandbox
                                                                                </label>
                                                                            </div>

                                                                            <div class="form-check">
                                                                                <input type="checkbox" class="form-check-input"
                                                                                    id="remove_sandbox_secret"
                                                                                    name="remove_sandbox_secret"
                                                                                    value="1">
                                                                                <label class="form-check-label" for="remove_sandbox_secret">
                                                                                    Remover Webhook Secret Sandbox
                                                                                </label>
                                                                            </div>
                                                                        </div>

                                                                        <div class="border rounded p-3 mb-3">
                                                                            <h6 class="mb-3">Credenciais Produção</h6>

                                                                            <div class="form-group">
                                                                                <label for="production_public_key">Public Key Produção</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="production_public_key"
                                                                                    name="production_public_key"
                                                                                    placeholder="APP_USR-..."
                                                                                    value="{{ $mercadopagoAdminConfig['production_public_key'] ?? '' }}">
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label for="production_access_token">Access Token Produção</label>
                                                                                <input type="password" class="form-control"
                                                                                    id="production_access_token"
                                                                                    name="production_access_token"
                                                                                    placeholder="Cole o token aqui"
                                                                                    value=""
                                                                                    autocomplete="new-password">
                                                                            </div>

                                                                            <div class="small text-muted mb-2">
                                                                                Status: {{ $mercadopagoAdminConfig['production_token_configured'] ?? false ? 'Credencial configurada' : 'Credencial não configurada' }}
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label for="production_webhook_secret">Webhook Secret Produção</label>
                                                                                <input type="password" class="form-control"
                                                                                    id="production_webhook_secret"
                                                                                    name="production_webhook_secret"
                                                                                    placeholder="Cole o segredo aqui"
                                                                                    value=""
                                                                                    autocomplete="new-password">
                                                                            </div>

                                                                            <div class="small text-muted mb-2">
                                                                                Status: {{ $mercadopagoAdminConfig['production_secret_configured'] ?? false ? 'Segredo configurado' : 'Segredo não configurado' }}
                                                                            </div>

                                                                            <div class="form-check">
                                                                                <input type="checkbox" class="form-check-input"
                                                                                    id="remove_production_token"
                                                                                    name="remove_production_token"
                                                                                    value="1">
                                                                                <label class="form-check-label" for="remove_production_token">
                                                                                    Remover Access Token Produção
                                                                                </label>
                                                                            </div>

                                                                            <div class="form-check">
                                                                                <input type="checkbox" class="form-check-input"
                                                                                    id="remove_production_secret"
                                                                                    name="remove_production_secret"
                                                                                    value="1">
                                                                                <label class="form-check-label" for="remove_production_secret">
                                                                                    Remover Webhook Secret Produção
                                                                                </label>
                                                                            </div>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="webhook_validation_mode">Modo de validação do Webhook</label>
                                                                            <select class="form-control" id="webhook_validation_mode" name="webhook_validation_mode">
                                                                                <option value="api_lookup" {{ ($mercadopagoAdminConfig['webhook_validation_mode'] ?? 'api_lookup') === 'api_lookup' ? 'selected' : '' }}>API lookup</option>
                                                                                <option value="signed" {{ ($mercadopagoAdminConfig['webhook_validation_mode'] ?? 'api_lookup') === 'signed' ? 'selected' : '' }}>Assinatura + API lookup</option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="alert alert-info small">
                                                                            <strong>API lookup:</strong> A notificação é confirmada consultando diretamente a API do Mercado Pago. Não há validação criptográfica sem um segredo configurado.
                                                                        </div>

                                                                        <div class="alert alert-info small">
                                                                            <strong>Assinatura + API lookup:</strong> Exige o segredo fornecido na configuração de Webhooks da aplicação Mercado Pago. A URL dinâmica não gera esse segredo automaticamente.
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="webhook_url">URL do Webhook (somente leitura)</label>
                                                                            <input type="text" class="form-control"
                                                                                id="webhook_url"
                                                                                value="{{ route('front.mercadopago.webhook') }}"
                                                                                readonly>
                                                                        </div>

                                                                        <h5 class="mt-4 mb-3">Métodos de Pagamento</h5>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="pix_enabled"
                                                                                name="pix_enabled"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['pix_enabled'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="pix_enabled">
                                                                                Aceitar Pix
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="credit_card_enabled"
                                                                                name="credit_card_enabled"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['credit_card_enabled'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="credit_card_enabled">
                                                                                Aceitar Cartão de Crédito
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group mt-3">
                                                                            <label for="pix_expiration_minutes">Expiração do Pix em minutos</label>
                                                                            <input type="number" min="5" max="4320"
                                                                                class="form-control"
                                                                                id="pix_expiration_minutes"
                                                                                name="pix_expiration_minutes"
                                                                                value="{{ $mercadopagoAdminConfig['pix_expiration_minutes'] ?? 30 }}">
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="max_installments">Parcelas máximas no crédito</label>
                                                                            <select class="form-control"
                                                                                id="max_installments"
                                                                                name="max_installments">
                                                                                @for ($installment = 1; $installment <= 12; $installment++)
                                                                                    <option value="{{ $installment }}"
                                                                                        {{ (int) ($mercadopagoAdminConfig['max_installments'] ?? 1) === $installment ? 'selected' : '' }}>
                                                                                        {{ $installment }}x
                                                                                    </option>
                                                                                @endfor
                                                                            </select>
                                                                        </div>

                                                                        <h5 class="mt-4 mb-3">Taxas</h5>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="fee_pass_to_customer"
                                                                                name="fee_pass_to_customer"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['fee_pass_to_customer'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="fee_pass_to_customer">
                                                                                Repassar taxa do Mercado Pago ao cliente
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="fee_calculation_mode">Modo de cálculo da taxa</label>
                                                                            <select class="form-control" id="fee_calculation_mode" name="fee_calculation_mode">
                                                                                <option value="additive" {{ ($mercadopagoAdminConfig['fee_calculation_mode'] ?? 'additive') === 'additive' ? 'selected' : '' }}>Aditivo</option>
                                                                                <option value="gross_up" {{ ($mercadopagoAdminConfig['fee_calculation_mode'] ?? 'additive') === 'gross_up' ? 'selected' : '' }}>Gross Up</option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="form-group col-xl-6">
                                                                                <label for="pix_fee_percent">Taxa percentual Pix (%)</label>
                                                                                <input type="text" inputmode="decimal"
                                                                                    class="form-control"
                                                                                    id="pix_fee_percent"
                                                                                    name="pix_fee_percent"
                                                                                    value="{{ str_replace('.', ',', $mercadopagoAdminConfig['pix_fee_percent'] ?? 0) }}">
                                                                            </div>

                                                                            <div class="form-group col-xl-6">
                                                                                <label for="pix_fee_fixed">Taxa fixa Pix (R$)</label>
                                                                                <input type="text" inputmode="decimal"
                                                                                    class="form-control"
                                                                                    id="pix_fee_fixed"
                                                                                    name="pix_fee_fixed"
                                                                                    value="{{ str_replace('.', ',', $mercadopagoAdminConfig['pix_fee_fixed'] ?? 0) }}">
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="form-group col-xl-6">
                                                                                <label for="credit_fee_percent">Taxa percentual Crédito (%)</label>
                                                                                <input type="text" inputmode="decimal"
                                                                                    class="form-control"
                                                                                    id="credit_fee_percent"
                                                                                    name="credit_fee_percent"
                                                                                    value="{{ str_replace('.', ',', $mercadopagoAdminConfig['credit_fee_percent'] ?? 0) }}">
                                                                            </div>

                                                                            <div class="form-group col-xl-6">
                                                                                <label for="credit_fee_fixed">Taxa fixa Crédito (R$)</label>
                                                                                <input type="text" inputmode="decimal"
                                                                                    class="form-control"
                                                                                    id="credit_fee_fixed"
                                                                                    name="credit_fee_fixed"
                                                                                    value="{{ str_replace('.', ',', $mercadopagoAdminConfig['credit_fee_fixed'] ?? 0) }}">
                                                                            </div>
                                                                        </div>

                                                                        <h5 class="mt-4 mb-3">Operações Avançadas</h5>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="refund_enabled"
                                                                                name="refund_enabled"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['refund_enabled'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="refund_enabled">
                                                                                Permitir reembolso
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="partial_refund_enabled"
                                                                                name="partial_refund_enabled"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['partial_refund_enabled'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="partial_refund_enabled">
                                                                                Permitir reembolso parcial
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="cancellation_enabled"
                                                                                name="cancellation_enabled"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['cancellation_enabled'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="cancellation_enabled">
                                                                                Permitir cancelamento
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="reconciliation_enabled"
                                                                                name="reconciliation_enabled"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['reconciliation_enabled'] ?? true) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="reconciliation_enabled">
                                                                                Habilitar reconciliação
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-check">
                                                                            <input type="checkbox" class="form-check-input"
                                                                                id="binary_mode"
                                                                                name="binary_mode"
                                                                                value="1"
                                                                                {{ ($mercadopagoAdminConfig['binary_mode'] ?? true) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="binary_mode">
                                                                                Modo binário (rejeitar pagamentos pendentes)
                                                                            </label>
                                                                        </div>

                                                                        <div class="form-group mt-3">
                                                                            <label for="statement_descriptor">Descritor na fatura (máx 22 caracteres)</label>
                                                                            <input type="text" class="form-control"
                                                                                id="statement_descriptor"
                                                                                name="statement_descriptor"
                                                                                placeholder="Nome da loja"
                                                                                value="{{ $mercadopagoAdminConfig['statement_descriptor'] ?? '' }}"
                                                                                maxlength="22">
                                                                        </div>

                                                                    </div>

                                                                    <div class="form-group d-flex justify-content-center">
                                                                        <button type="submit"
                                                                            class="btn btn-secondary btn-block w-50">Salvar Mercado Pago</button>
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
