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
        width: min(900px, calc(100vw - 24px));
        max-width: 900px;
        margin: 12px auto;
    }

    #announcement-container.announcement-with-content {
        position: relative;
        width: 100%;
        aspect-ratio: 1.92 / 1;
        min-height: 0;
        max-height: calc(100vh - 24px);
        background: #f5efe3;
        overflow: hidden;
        border-radius: 10px;
    }

    #announcement-container .left-area {
        position: absolute;
        inset: 0;
        background: #f5efe3 center center / cover no-repeat;
        overflow: hidden;
    }

    #announcement-container .left-area img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    #announcement-overlay.right-area {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        gap: 12px;
        width: 54%;
        padding: clamp(24px, 3.2vw, 42px);
        overflow: auto;
        background: linear-gradient(90deg, rgba(255, 249, 235, 0.92) 0%, rgba(255, 249, 235, 0.72) 58%, rgba(255, 249, 235, 0) 100%);
    }

    #announcement-overlay .announcement-title {
        margin: 0;
        color: #111827;
        font-size: clamp(26px, 2.6vw, 38px);
        line-height: 1.1;
        font-weight: 800;
        max-width: 9ch;
    }

    #announcement-overlay .announcement-text {
        margin: 0;
        color: #4b5563;
        font-size: 17px;
        line-height: 1.55;
        max-width: 22ch;
    }

    #announcement-overlay .announcement-dynamic-content {
        margin-top: 8px;
    }

    .announcement-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        min-height: 40px;
        padding: 0 16px;
        border-radius: 8px;
        background: #f59e0b;
        color: #fff;
        font-weight: 700;
        border: 0;
        transition: filter .18s ease, transform .18s ease;
    }

    .announcement-btn:hover,
    .announcement-btn:focus {
        color: #fff;
        filter: brightness(.98);
        transform: translateY(-1px);
    }

    #announcement-overlay .input-group {
        width: 100%;
        max-width: 320px;
    }

    #announcement-overlay .form-control {
        min-height: 40px;
    }

    @media (max-width: 991px) {
        .white-popup {
            width: min(760px, calc(100vw - 20px));
        }

        #announcement-container.announcement-with-content {
            aspect-ratio: auto;
            min-height: 380px;
            max-height: calc(100vh - 24px);
        }

        #announcement-container .left-area {
            position: absolute;
            inset: 0;
        }

        #announcement-overlay.right-area {
            width: 62%;
            padding: 22px 18px 22px 24px;
            background: linear-gradient(90deg, rgba(255, 249, 235, 0.94) 0%, rgba(255, 249, 235, 0.76) 64%, rgba(255, 249, 235, 0) 100%);
        }

        #announcement-overlay .announcement-text {
            max-width: 26ch;
        }
    }

    @media (max-width: 575px) {
        .white-popup {
            width: calc(100vw - 12px);
            margin: 8px auto;
        }

        #announcement-overlay.right-area {
            width: 72%;
            padding: 18px 14px 18px 18px;
        }

        #announcement-overlay .announcement-title {
            font-size: 22px;
            max-width: none;
        }

        #announcement-overlay .announcement-text {
            font-size: 15px;
            max-width: none;
        }
    }
</style>

<div id="announcement-container" class="announcement-with-content">
    <div class="left-area" @if ($announcementImage) style="background-image:url('{{ $announcementImage }}');" @endif>
        @if ($announcementImage)
            <img src="{{ $announcementImage }}" alt="{{ $announcementTitle ?: __('Announcement') }}">
        @endif
    </div>

    <div id="announcement-overlay" class="right-area">
        @if ($announcementTitle)
            <h3 class="announcement-title">{{ $announcementTitle }}</h3>
        @endif

        @if ($announcementDetails)
            <p class="announcement-text">{{ $announcementDetails }}</p>
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
                <button class="btn btn-primary btn-block mt-2 announcement-btn" type="submit">
                    <span>{{ __('Subscribe') }}</span>
                </button>
            </form>
        @elseif ($announcementLink)
            <a href="{{ $announcementLink }}" target="_blank" rel="noopener" class="announcement-btn">
                {{ __('View more') }}
            </a>
        @endif
    </div>
</div>
