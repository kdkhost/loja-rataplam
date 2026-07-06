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
    $announcementButtonText = trim((string) ($setting->announcement_button_text ?? ''));
    $announcementButtonText = $announcementButtonText !== '' ? $announcementButtonText : ($setting->announcement_type === 'newletter' ? __('Subscribe') : __('View more'));
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
        background: #f5efe3 center center / cover no-repeat;
        overflow: hidden;
        border-radius: 10px;
    }

    #announcement-container .left-area {
        display: none;
    }

    #announcement-overlay.right-area {
        position: absolute;
        inset: 0 auto 0 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        gap: 12px;
        width: min(44%, 420px);
        padding: clamp(24px, 3.2vw, 40px);
        overflow: auto;
        background: linear-gradient(90deg, rgba(255, 249, 235, 0.94) 0%, rgba(255, 249, 235, 0.84) 68%, rgba(255, 249, 235, 0.62) 100%);
    }

    #announcement-overlay .announcement-title {
        margin: 0;
        color: #111827;
        font-size: clamp(24px, 2.4vw, 34px);
        line-height: 1.1;
        font-weight: 800;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.25);
    }

    #announcement-overlay .announcement-text {
        margin: 0;
        color: #374151;
        font-size: 16px;
        line-height: 1.6;
        max-width: 26ch;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.2);
    }

    #announcement-overlay .announcement-dynamic-content {
        margin-top: 8px;
    }

    .announcement-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        position: relative;
        color: #fff !important;
        width: fit-content;
    }

    .announcement-btn::before {
        z-index: 0;
    }

    .announcement-btn > span {
        position: relative;
        z-index: 2;
        color: #fff !important;
    }

    .announcement-btn:hover,
    .announcement-btn:focus {
        color: #fff !important;
        filter: brightness(.98);
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

        #announcement-overlay.right-area {
            width: 58%;
            padding: 22px 18px 22px 24px;
            background: linear-gradient(90deg, rgba(255, 249, 235, 0.95) 0%, rgba(255, 249, 235, 0.84) 72%, rgba(255, 249, 235, 0.58) 100%);
        }

        #announcement-overlay .announcement-text {
            max-width: 28ch;
        }
    }

    @media (max-width: 575px) {
        .white-popup {
            width: min(360px, calc(100vw - 14px));
            margin: 8px auto;
        }

        #announcement-container.announcement-with-content {
            min-height: 380px;
            background-position: center right;
        }

        #announcement-overlay.right-area {
            width: 74%;
            padding: 18px 12px 18px 18px;
        }

        #announcement-overlay .announcement-title {
            font-size: 20px;
        }

        #announcement-overlay .announcement-text {
            font-size: 15px;
            max-width: none;
        }
    }
</style>

<div id="announcement-container" class="announcement-with-content" @if ($announcementImage) style="background-image:url('{{ $announcementImage }}');" @endif>
    <div class="left-area" aria-hidden="true"></div>

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
                <button class="btn btn-primary mt-2 announcement-btn" type="submit">
                    <span>{{ $announcementButtonText }}</span>
                </button>
            </form>
        @elseif ($announcementLink)
            <a href="{{ $announcementLink }}" target="_blank" rel="noopener" class="btn btn-primary announcement-btn">
                <span>{{ $announcementButtonText }}</span>
            </a>
        @endif
    </div>
</div>
