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

    #announcement-container.announcement-with-content {
        display: flex;
        width: 100%;
        min-height: 420px;
        max-height: calc(100vh - 56px);
        background: #fff;
        overflow: hidden;
        border-radius: 8px;
    }

    #announcement-container .left-area {
        position: relative;
        flex: 0 0 42%;
        min-height: 420px;
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
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 14px;
        padding: clamp(28px, 4vw, 56px);
        overflow: auto;
        background: linear-gradient(180deg, #fffaf0 0%, #fff8ea 100%);
    }

    #announcement-overlay .announcement-title {
        margin: 0;
        color: #111827;
        font-size: clamp(26px, 3vw, 40px);
        line-height: 1.1;
        font-weight: 800;
    }

    #announcement-overlay .announcement-text {
        margin: 0;
        color: #4b5563;
        font-size: 16px;
        line-height: 1.7;
        max-width: 42ch;
    }

    #announcement-overlay .announcement-dynamic-content {
        margin-top: 8px;
    }

    .announcement-btn {
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
        max-width: 420px;
    }

    #announcement-overlay .form-control {
        min-height: 44px;
    }

    @media (max-width: 991px) {
        .white-popup {
            width: min(960px, calc(100vw - 24px));
        }

        #announcement-container.announcement-with-content {
            flex-direction: column;
            min-height: auto;
            max-height: calc(100vh - 32px);
        }

        #announcement-container .left-area {
            flex: 0 0 auto;
            width: 100%;
            min-height: 260px;
        }

        #announcement-overlay.right-area {
            padding: 26px 22px 28px;
        }

        #announcement-overlay .announcement-text {
            max-width: none;
        }
    }

    @media (max-width: 575px) {
        .white-popup {
            width: calc(100vw - 16px);
            margin: 8px auto;
        }

        #announcement-container .left-area {
            min-height: 220px;
        }

        #announcement-overlay.right-area {
            padding: 22px 18px 24px;
        }

        #announcement-overlay .announcement-title {
            font-size: 24px;
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
