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
    $announcementLabel = $setting->announcement_type === 'newletter' ? __('Newsletter Popup') : __('Announcement');
    $hasAnnouncementImage = (bool) $announcementImage;
@endphp

<style>
    .white-popup {
        width: min(1120px, calc(100vw - 32px));
        max-width: 1120px;
        margin: 18px auto;
    }
    .announcement-with-content {
        display: grid;
        grid-template-columns: minmax(300px, 1fr) minmax(360px, 1.05fr);
        min-height: min(76vh, 560px);
        max-height: calc(100vh - 56px);
        overflow: hidden;
        background: #fff;
        border-radius: 8px;
    }
    .announcement-with-content--no-image {
        grid-template-columns: 1fr;
    }
    .announcement-with-content__copy {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 14px;
        padding: clamp(28px, 4vw, 56px);
        background: linear-gradient(180deg, #fffaf0 0%, #fff8ea 100%);
    }
    .announcement-with-content__label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #b26a00;
    }
    .announcement-with-content__title {
        margin: 0;
        color: #111827;
        font-size: clamp(26px, 3vw, 40px);
        line-height: 1.1;
        font-weight: 800;
        word-break: break-word;
    }
    .announcement-with-content__text {
        margin: 0;
        color: #4b5563;
        font-size: 16px;
        line-height: 1.7;
        max-width: 42ch;
        word-break: break-word;
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
    .announcement-with-content__form {
        display: grid;
        gap: 12px;
        max-width: 100%;
    }
    .announcement-with-content__field .input-group {
        width: 100%;
    }
    .announcement-with-content__field .form-control {
        min-height: 44px;
    }
    .announcement-with-content__visual {
        position: relative;
        min-height: 100%;
        background: #f4f1ea center center / cover no-repeat;
    }
    .announcement-with-content__visual--fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #fff5db 0%, #fbeed0 55%, #f5e3bc 100%);
    }
    .announcement-with-content__visual::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(255,255,255,.12) 0%, rgba(255,255,255,0) 20%);
    }
    .announcement-with-content__image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .announcement-with-content__link {
        position: absolute;
        inset: 0;
        display: block;
        z-index: 2;
    }
    .announcement-with-content__fallback {
        position: relative;
        z-index: 1;
        max-width: 68%;
        text-align: center;
        color: #6b4e00;
        font-weight: 700;
        line-height: 1.55;
        font-size: 18px;
        padding: 28px;
        background: rgba(255,255,255,.42);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 12px;
        backdrop-filter: blur(4px);
    }
    html.admin-theme-dark .announcement-with-content__copy {
        background: linear-gradient(180deg, #fffaf0 0%, #fff3da 100%);
    }
    @media (max-width: 991px) {
        .white-popup {
            width: min(960px, calc(100vw - 24px));
        }
        .announcement-with-content {
            grid-template-columns: 1fr;
            min-height: auto;
            max-height: calc(100vh - 32px);
        }
        .announcement-with-content__copy {
            order: 2;
            padding: 26px 22px 28px;
        }
        .announcement-with-content__visual {
            order: 1;
            min-height: 260px;
        }
        .announcement-with-content__text {
            max-width: none;
        }
    }
    @media (max-width: 575px) {
        .white-popup {
            width: calc(100vw - 16px);
            margin: 8px auto;
        }
        .announcement-with-content__visual {
            min-height: 220px;
        }
        .announcement-with-content__copy {
            padding: 22px 18px 24px;
        }
        .announcement-with-content__title {
            font-size: 24px;
        }
    }
</style>

<div class="announcement-with-content">
    <div class="announcement-with-content__copy">
        <div class="announcement-with-content__label">{{ $announcementLabel }}</div>
        @if ($announcementTitle)
            <h3 class="announcement-with-content__title">{{ $announcementTitle }}</h3>
        @endif
        @if ($announcementDetails)
            <p class="announcement-with-content__text">{{ $announcementDetails }}</p>
        @endif
        @if ($setting->announcement_type === 'newletter')
            <form class="announcement-with-content__form subscriber-form" action="{{ route('front.subscriber.submit') }}" method="post">
                @csrf
                <div class="announcement-with-content__field">
                    <div class="input-group">
                        <input class="form-control" type="email" name="email" placeholder="{{ __('Your e-mail') }}">
                        <span class="input-group-addon"><i class="icon-mail"></i></span>
                    </div>
                    <div aria-hidden="true">
                        <input type="hidden" name="b_c7103e2c981361a6639545bd5_1194bb7544" tabindex="-1">
                    </div>
                </div>
                <button class="announcement-with-content__action btn btn-primary btn-block" type="submit">
                    <span>{{ __('Subscribe') }}</span>
                </button>
            </form>
        @elseif ($announcementLink)
            <a href="{{ $announcementLink }}" target="_blank" rel="noopener" class="announcement-with-content__action">
                {{ __('View more') }}
            </a>
        @endif
    </div>

    <div class="announcement-with-content__visual {{ $hasAnnouncementImage ? '' : 'announcement-with-content__visual--fallback' }}" @if ($announcementImage) style="background-image: url('{{ $announcementImage }}');" @endif>
        @if ($announcementImage)
            <img class="announcement-with-content__image" src="{{ $announcementImage }}" alt="{{ $announcementTitle ?: $announcementLabel }}">
        @else
            <div class="announcement-with-content__fallback">
                {{ $announcementTitle ?: $announcementLabel }}<br>
                <small>{{ $announcementDetails ?: __('View more') }}</small>
            </div>
        @endif
        @if ($announcementLink && $setting->announcement_type !== 'newletter')
            <a class="announcement-with-content__link" href="{{ $announcementLink }}" target="_blank" rel="noopener" aria-label="{{ $announcementTitle ?: $announcementLabel }}"></a>
        @endif
    </div>
</div>
