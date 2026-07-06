<style>
    .cookie-consent {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1041;
        padding: 0 14px 14px;
        pointer-events: none;
    }

    .cookie-consent__panel {
        width: min(1120px, 100%);
        margin: 0 auto;
        padding: 16px 18px;
        border-radius: 8px;
        background: rgba(17, 24, 39, .94);
        box-shadow: 0 18px 54px rgba(17, 24, 39, .28);
        pointer-events: auto;
    }

    .cookie-consent__content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .cookie-consent__message {
        margin: 0;
        color: #fff;
        font-size: 14px;
        line-height: 1.55;
    }

    .cookie-consent__actions {
        flex: 0 0 auto;
    }

    .cookie-consent__agree {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 9px 22px;
        border: 0;
        border-radius: 20px;
        background: #ffa000;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .cookie-consent__agree:hover,
    .cookie-consent__agree:focus {
        color: #fff;
        filter: brightness(.96);
        outline: none;
    }

    @media (max-width: 575px) {
        .cookie-consent {
            padding: 0 12px max(12px, env(safe-area-inset-bottom));
        }

        .cookie-consent__panel {
            width: min(360px, 100%);
            max-height: calc(100vh - 24px);
            overflow: auto;
            padding: 18px 16px;
        }

        .cookie-consent__content {
            display: block;
        }

        .cookie-consent__message {
            font-size: 14px;
            line-height: 1.58;
        }

        .cookie-consent__actions {
            margin-top: 12px;
        }

        .cookie-consent__agree {
            min-width: 148px;
            min-height: 36px;
            padding: 8px 18px;
        }
    }
</style>

<div class="js-cookie-consent cookie-consent">
    <div class="cookie-consent__panel">
        <div class="cookie-consent__content">
            <p class="cookie-consent__message">
                {{ $setting->cookie_text }}
            </p>
            <div class="cookie-consent__actions">
                <button class="js-cookie-consent-agree cookie-consent__agree" type="button">
                    {{ __('Allow Cookies') }}
                </button>
            </div>
        </div>
    </div>
</div>
