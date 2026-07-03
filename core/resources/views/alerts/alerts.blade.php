@if (Session::has('success'))
    <span class="admin-notify-message d-none" data-type="success" data-title="{{ __('Success') }}"
        data-message="{{ e(Session::get('success')) }}"></span>
@endif

@if (Session::has('error'))
    <span class="admin-notify-message d-none" data-type="danger" data-title="{{ __('Error') }}"
        data-message="{{ e(Session::get('error')) }}"></span>
@endif

@if ($errors->count() > 0)
    @foreach ($errors->all() as $error)
        <span class="admin-notify-message d-none" data-type="danger" data-title="{{ __('Error') }}"
            data-message="{{ e($error) }}"></span>
    @endforeach
@endif
