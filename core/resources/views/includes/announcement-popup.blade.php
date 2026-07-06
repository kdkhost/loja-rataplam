@php
    $announcementImageValue = trim((string) ($setting->announcement ?? ''));
    $announcementImageValue = preg_replace('#^https?://[^/]+/#i', '', $announcementImageValue);
    $announcementImageValue = ltrim($announcementImageValue, '/');
    $announcementImagePath = $announcementImageValue && str_contains($announcementImageValue, 'storage/images/')
        ? $announcementImageValue
        : ($announcementImageValue ? 'images/' . $announcementImageValue : null);
    $announcementImage = $announcementImagePath ? url('/core/public/storage/' . ltrim($announcementImagePath, '/')) : null;
    $announcementTitle = trim((string) ($setting->announcement_title ?? ''));
    $announcementDetails = trim((string) ($setting->announcement_details ?? ''));
    $announcementLink = trim((string) ($setting->announcement_link ?? ''));
@endphp

<style>
    .white-popup {
        width: min(1080px, calc(100vw - 32px));
        max-width: 1080px;
        margin: 20px auto;
    }

    .announcement-with-content {
        display: flex;
        width: 100%;
        min-height: 420px;
        max-height: calc(100vh - 56px);
        background: #fff;
        overflow: hidden;
        border-radius: 8px;
    }

    .announcement-with-content .left-area {
        position: relative;
        flex: 0 0 42%;
        min-height: 420px;
        background: #f5efe3 center center / cover no-repeat;
    }

    .announcement-with-content .left-area img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .announcement-with-content .right-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 14px;
        padding: clamp(28px, 4vw, 56px);
        overflow: auto;
        background: linear-gradient(180deg, #fffaf0 0%, #fff8ea 100%);
    }

    .announcement-with-content .right-area h3 {
        margin: 0;
        color: #111827;
        font-size: clamp(26px, 3vw, 40px);
        line-height: 1.1;
        font-weight: 800;
    }

    .announcement-with-content .right-area p {
        margin: 0;
        color: #4b5563;
        font-size: 16px;
        line-height: 1.7;
        max-width: 42ch;
    }

    .announcement-with-content .right-area form {
        margin-top: 8px;
        max-width: 420px;
    }

    .announcement-with-content__action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 8px;
        background: #f59e0b;
        color: #fff;
        font-weight: 700;
        transition: filter .18s ease, transform .18s ease;
    }

    .announcement-with-content__action:hover,
    .announcement-with-content__action:focus {
        color: #fff;
        filter: brightness(.98);
        transform: translateY(-1px);
    }

    @media (max-width: 991px) {
        .white-popup {
            width: min(960px, calc(100vw - 24px));
        }

        .announcement-with-content {
            flex-direction: column;
            min-height: auto;
            max-height: calc(100vh - 32px);
        }

        .announcement-with-content .left-area {
            flex: 0 0 auto;
            width: 100%;
            min-height: 260px;
        }

        .announcement-with-content .right-area {
            padding: 26px 22px 28px;
        }

        .announcement-with-content .right-area p {
            max-width: none;
        }
    }

    @media (max-width: 575px) {
        .white-popup {
            width: calc(100vw - 16px);
            margin: 8px auto;
        }

        .announcement-with-content .left-area {
            min-height: 220px;
        }

        .announcement-with-content .right-area {
            padding: 22px 18px 24px;
        }

        .announcement-with-content .right-area h3 {
            font-size: 24px;
        }
    }
</style>

<div class="announcement-with-content">
    <div class="left-area" @if ($announcementImage) style="background-image:url('{{ $announcementImage }}');" @endif>
        @if ($announcementImage)
            <img src="{{ $announcementImage }}" alt="{{ $announcementTitle ?: __('Announcement') }}">
        @endif
    </div>

    <div class="right-area">
        @if ($announcementTitle)
            <h3>{{ $announcementTitle }}</h3>
        @endif

        @if ($announcementDetails)
            <p>{{ $announcementDetails }}</p>
        @endif

        @if ($setting->announcement_type === 'newletter')
            <form class="subscriber-form" action="{{ route('front.subscriber.submit') }}" method="post">
                @csrf
                <div class="input-group">
                    <input class="form-control" type="email" name="email" placeholder="{{ __('Your e-mail') }}">
                    <span class="input-group-addon"><i class="icon-mail"></i></span>
                </div>
                <div aria-hidden="true">
                    <input type="hidden" name="b_c7103e2c981361a6639545bd5_1194bb7544" tabindex="-1">
                </div>
                <button class="btn btn-primary btn-block mt-2" type="submit">
                    <span>{{ __('Subscribe') }}</span>
                </button>
            </form>
        @elseif ($announcementLink)
            <a href="{{ $announcementLink }}" target="_blank" rel="noopener" class="announcement-with-content__action">
                {{ __('View more') }}
            </a>
        @endif
    </div>
</div>
