@extends('master.back')

@section('content')

<div class="container-fluid">

   	<!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class=" mb-0 bc-title"> <b>{{ __('Announcement') }}</b> </h3>
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
						<div class="col-lg-12">
							<div class="p-5">
								<div class="admin-form">

									@include('alerts.alerts')

                                    <div class="row justify-content-center">

                                        <div class="col-lg-8">

                                            <form action="{{ route('back.setting.update') }}" method="POST"
                                            enctype="multipart/form-data">

                                            @csrf


                                                <div class="form-group">
                                                    <label class="switch-primary">
                                                      <input type="checkbox" class="switch switch-bootstrap status radio-check" name="is_announcement" value="1" {{ $setting->is_announcement == 1 ? 'checked' : '' }}>
                                                      <span class="switch-body"></span>
                                                      <span class="switch-text">{{ __('Announcement Banner') }}</span>
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label for="announcement_type">{{ __('Select Type') }} *</label>
                                                    <select name="announcement_type" id="announcement_type" class="form-control" >
                                                        <option value="banner" {{$setting->announcement_type =='banner' ? 'selected' : ''}} >{{__('Announcement')}}</option>
                                                        <option value="newletter" {{$setting->announcement_type =='newletter' ? 'selected' : ''}}>{{__('Newsletter Popup')}}</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="announcement_mode">{{ __('Content Mode') }} *</label>
                                                    <select name="announcement_mode" id="announcement_mode" class="form-control" >
                                                        <option value="manual" {{$setting->announcement_mode =='manual' ? 'selected' : ''}}>{{__('Manual')}}</option>
                                                        <option value="coupon" {{$setting->announcement_mode =='coupon' ? 'selected' : ''}}>{{__('Coupon')}}</option>
                                                        <option value="product" {{$setting->announcement_mode =='product' ? 'selected' : ''}}>{{__('Product')}}</option>
                                                    </select>
                                                </div>

                                                <div class="image-show {{ $setting->is_announcement == 1 ? '' : 'd-none' }}">

                                                    <div class="form-group">
                                                        <label for="name">{{ __('Image') }}</label>
                                                        <div class="col-lg-12 pb-1">
                                                            <img class="admin-img lg"
                                                                src="{{ $setting->announcement ? url('/core/public/storage/images/'.$setting->announcement) : url('/core/public/storage/images/placeholder.png') }}"
                                                                alt="No Image Found">
                                                        </div>
</div>

                                                    <div class="form-group position-relative ">
                                                        <label class="file">
                                                            <input type="file"  accept="image/*"  class="upload-photo" name="announcement" id="file" aria-label="File browser example">
                                                            <span class="file-custom text-left">{{ __('Upload Image...') }}</span>
                                                        </label>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="announcement_delay">{{ __('Announcement Delay (seconds)') }} *</label>
                                                        <input type="number" name="announcement_delay" class="form-control" id="announcement_delay"
                                                            placeholder="{{ __('Announcement Delay') }}" value="{{ $setting->announcement_delay ?: 30 }}" min="0" step="1">
                                                        <small class="text-muted">Tempo em segundos antes de exibir o popup (recomendado: 30-60 segundos)</small>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="announcement_starts_at">{{ __('Start Date/Time') }}</label>
                                                                <input type="datetime-local" name="announcement_starts_at" class="form-control" id="announcement_starts_at"
                                                                    value="{{ $setting->announcement_starts_at ? \Carbon\Carbon::parse($setting->announcement_starts_at)->format('Y-m-d\TH:i') : '' }}">
                                                                <small class="text-muted">Data/hora para começar a exibir (opcional)</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="announcement_ends_at">{{ __('End Date/Time') }}</label>
                                                                <input type="datetime-local" name="announcement_ends_at" class="form-control" id="announcement_ends_at"
                                                                    value="{{ $setting->announcement_ends_at ? \Carbon\Carbon::parse($setting->announcement_ends_at)->format('Y-m-d\TH:i') : '' }}">
                                                                <small class="text-muted">Data/hora para parar de exibir (opcional)</small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="announcement_title">{{ __('Newsletter Title') }} *</label>
                                                        <input type="text" name="announcement_title" class="form-control" id="announcement_title"
                                                            placeholder="{{ __('Popup Title') }}" value="{{ $setting->announcement_title }}" >
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="announcement_details">{{ __('Newsletter Text') }} *</label>
                                                        <textarea name="announcement_details" class="form-control" id="announcement_details" >{{ $setting->announcement_details }}</textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="announcement_link">{{ __('Announcement Link') }} *</label>
                                                        <input type="text" name="announcement_link" class="form-control" id="announcement_link"
                                                            placeholder="{{ __('Link') }}" value="{{ $setting->announcement_link }}" >
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="announcement_button_text">{{ __('Button Text') }} *</label>
                                                        <input type="text" name="announcement_button_text" class="form-control" id="announcement_button_text"
                                                            placeholder="{{ __('View more') }}" value="{{ data_get($setting, 'announcement_button_text', __('View more')) }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Cupons disponíveis (modo cupom)</label>
                                                        <select name="announcement_coupon_ids[]" class="form-control select2" multiple="multiple" id="announcement-coupon-ids">
                                                            @if(isset($promoCodes))
                                                                @foreach ($promoCodes as $promoCode)
                                                                    <option value="{{ $promoCode->id }}" data-coupon-code="{{ $promoCode->code_name }}" {{ in_array($promoCode->id, json_decode($setting->announcement_coupon_ids ?? '[]', true) ?: []) ? 'selected' : '' }}>
                                                                        {{ $promoCode->title }} - {{ $promoCode->code_name }} ({{ $promoCode->discount }}%)
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <small class="text-muted">Selecione múltiplos cupons para exibir aleatoriamente</small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Produtos em promoção (modo produto)</label>
                                                        <select name="announcement_product_ids[]" class="form-control select2" multiple="multiple" id="announcement-product-ids">
                                                            @if(isset($items))
                                                                @foreach ($items as $item)
                                                                    <option value="{{ $item->id }}" {{ in_array($item->id, json_decode($setting->announcement_product_ids ?? '[]', true) ?: []) ? 'selected' : '' }}>
                                                                        {{ $item->name }} - {{ \App\Helpers\PriceHelper::setPrice($item->discount_price) }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <small class="text-muted">Selecione múltiplos produtos para exibir aleatoriamente</small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="switch-primary">
                                                          <input type="checkbox" class="switch switch-bootstrap" name="announcement_show_random" value="1" {{ $setting->announcement_show_random == 1 ? 'checked' : '' }}>
                                                          <span class="switch-body"></span>
                                                          <span class="switch-text">{{ __('Show Randomly') }}</span>
                                                        </label>
                                                        <small class="text-muted">Quando ativado, exibe um cupom ou produto aleatório da lista selecionada</small>
                                                    </div>

                                                </div>



                                                <div>

                                                    <div class="form-group d-flex justify-content-center">
                                                        <button type="submit" class="btn btn-secondary ">{{ __('Submit') }}</button>
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
@endsection
