@php
    $notifications = App\Models\Notification::where('is_read', 0)->orderby('id','desc')->get();
@endphp

@if($notifications->count() > 0)
    <h6 class="dropdown-header">
        {{ __('Notifications') }}
        <a class="text-dark float-right clear-notf" id="clear-notf" data-href="{{ route('back.notifications.clear') }}" href="javascript:;">
            <small>{{ __('Clear All') }}</small>
        </a>
    </h6>

    @foreach($notifications as $notf)
        @if($notf->user_id != null)
            <a class="dropdown-item d-flex align-items-center notification-read-link"
                data-href="{{ route('back.notification.read',$notf->id) }}"
                href="{{ route('back.notification.read',$notf->id) }}">
            <div class="mr-3">
                <div class="icon-circle bg-primary">
                <i class="fas fa-user text-white"></i>
                </div>
            </div>
            <div>
                <div class="small text-gray-500">{{ $notf->created_at->diffForHumans() }}</div>
                {{ __('A new user has registered.') }}
            </div>
            </a>
        @endif
        @if($notf->order_id != null)
            <a class="dropdown-item d-flex align-items-center notification-read-link"
                data-href="{{ route('back.notification.read',$notf->id) }}"
                href="{{ route('back.notification.read',$notf->id) }}">
            <div class="mr-3">
                <div class="icon-circle bg-success">
                <i class="fas fa-donate text-white"></i>
                </div>
            </div>
            <div>
                <div class="small text-gray-500">{{ $notf->created_at->diffForHumans() }}</div>
                {{ __('You have recieved a new order.') }}
            </div>
            </a>
        @endif
    @endforeach
    @if($notifications->count() > 0)

        <a class="dropdown-header mt-1 d-block text-center"  href="{{route('back.view.notification')}}"> {{__('View All')}} </a>

    @endif
@else


<h6 class="dropdown-header">
    {{ __('Notifications') }}
</h6>
<a class="dropdown-item d-flex align-items-center" href="javascript:;">
    <div>
        {{ __('No Notifications') }}
    </div>
</a>
@endif
