<style>
    :root {
        --auth-ink: #162031;
        --auth-muted: #667085;
        --auth-line: #d9e1ea;
        --auth-blue: #0f5f83;
        --auth-green: #2f7f68;
        --auth-gold: #ffaa20;
        --auth-surface: #ffffff;
    }

    body.login {
        min-height: 100vh;
        margin: 0;
        background: #eef4f7;
        color: var(--auth-ink);
        font-family: "Inter", "Segoe UI", Arial, sans-serif;
    }

    .admin-auth-page {
        min-height: 100vh;
        display: flex;
        align-items: stretch;
        justify-content: center;
        padding: 28px;
        background:
            linear-gradient(135deg, rgba(15, 95, 131, .12), rgba(255, 170, 32, .12)),
            #eef4f7;
    }

    .admin-auth-shell {
        width: min(1120px, 100%);
        min-height: min(720px, calc(100vh - 56px));
        display: grid;
        grid-template-columns: minmax(360px, .92fr) minmax(420px, 1.08fr);
        overflow: hidden;
        border: 1px solid rgba(15, 95, 131, .14);
        border-radius: 8px;
        background: var(--auth-surface);
        box-shadow: 0 24px 70px rgba(22, 32, 49, .16);
    }

    .admin-auth-brand {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 28px;
        padding: 38px;
        background:
            linear-gradient(150deg, rgba(15, 95, 131, .96), rgba(47, 127, 104, .92)),
            #0f5f83;
        color: #fff;
    }

    .admin-auth-brand::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 42%;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, .08)),
            repeating-linear-gradient(135deg, rgba(255, 255, 255, .12) 0 1px, transparent 1px 18px);
        pointer-events: none;
    }

    .admin-auth-brand > * {
        position: relative;
        z-index: 1;
    }

    .admin-auth-logo-card {
        width: 180px;
        max-width: 70%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 16px;
        border-radius: 8px;
        background: rgba(255, 255, 255, .94);
        box-shadow: 0 18px 40px rgba(0, 0, 0, .14);
    }

    .admin-auth-logo-card img {
        max-width: 100%;
        max-height: 72px;
        object-fit: contain;
    }

    .admin-auth-brand-copy {
        max-width: 440px;
    }

    .admin-auth-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        color: rgba(255, 255, 255, .88);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .admin-auth-kicker::before {
        content: "";
        width: 28px;
        height: 3px;
        border-radius: 999px;
        background: var(--auth-gold);
    }

    .admin-auth-brand h1 {
        margin: 0;
        color: #fff;
        font-size: 46px;
        line-height: 1.04;
        font-weight: 800;
        letter-spacing: 0;
    }

    .admin-auth-brand p {
        max-width: 380px;
        margin: 18px 0 0;
        color: rgba(255, 255, 255, .84);
        font-size: 16px;
        line-height: 1.65;
    }

    .admin-auth-status {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .admin-auth-status-item {
        min-height: 96px;
        padding: 16px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 8px;
        background: rgba(255, 255, 255, .11);
        backdrop-filter: blur(10px);
    }

    .admin-auth-status-item strong {
        display: block;
        color: #fff;
        font-size: 15px;
        font-weight: 800;
    }

    .admin-auth-status-item span {
        display: block;
        margin-top: 7px;
        color: rgba(255, 255, 255, .78);
        font-size: 13px;
        line-height: 1.45;
    }

    .admin-auth-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 42px;
        background: #fff;
    }

    .admin-auth-card {
        width: min(430px, 100%);
    }

    .admin-auth-card h2 {
        margin: 0;
        color: var(--auth-ink);
        font-size: 30px;
        line-height: 1.12;
        font-weight: 800;
        letter-spacing: 0;
    }

    .admin-auth-card .auth-subtitle {
        margin: 10px 0 28px;
        color: var(--auth-muted);
        font-size: 15px;
        line-height: 1.55;
    }

    .admin-auth-field {
        margin-bottom: 18px;
    }

    .admin-auth-field label {
        display: block;
        margin-bottom: 8px;
        color: #344054;
        font-size: 13px;
        font-weight: 700;
    }

    .admin-auth-input-wrap {
        position: relative;
    }

    .admin-auth-input-wrap > i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #7b8da2;
        font-size: 15px;
        pointer-events: none;
    }

    .admin-auth-input-wrap .form-control {
        width: 100%;
        min-height: 54px;
        padding: 14px 46px 14px 46px;
        border: 1px solid var(--auth-line);
        border-radius: 8px;
        background: #fbfcfd;
        color: var(--auth-ink);
        font-size: 15px;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .admin-auth-input-wrap .form-control:focus {
        border-color: var(--auth-blue);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(15, 95, 131, .12);
    }

    .admin-auth-password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #667085;
        transform: translateY(-50%);
        cursor: pointer;
    }

    .admin-auth-password-toggle:hover,
    .admin-auth-password-toggle:focus {
        background: #edf4f7;
        color: var(--auth-blue);
        outline: none;
    }

    .admin-auth-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 24px;
    }

    .admin-auth-submit,
    .admin-auth-secondary {
        min-height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
    }

    .admin-auth-submit {
        min-width: 170px;
        border: 0;
        padding: 14px 22px;
        background: #0f5f83;
        color: #fff;
        box-shadow: 0 14px 28px rgba(15, 95, 131, .24);
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .admin-auth-submit:hover,
    .admin-auth-submit:focus {
        background: #0b526f;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 18px 34px rgba(15, 95, 131, .3);
    }

    .admin-auth-secondary {
        color: var(--auth-blue);
    }

    .admin-auth-secondary:hover,
    .admin-auth-secondary:focus {
        color: #0b526f;
        text-decoration: underline;
    }

    .admin-auth-footnote {
        margin-top: 20px;
        color: #7a8797;
        font-size: 13px;
        line-height: 1.45;
        text-align: center;
    }

    @media (max-width: 980px) {
        .admin-auth-page {
            padding: 18px;
        }

        .admin-auth-shell {
            min-height: auto;
            grid-template-columns: 1fr;
        }

        .admin-auth-brand {
            min-height: 320px;
            padding: 30px;
        }

        .admin-auth-brand h1 {
            font-size: 34px;
        }

        .admin-auth-status {
            max-width: 520px;
        }
    }

    @media (max-width: 575px) {
        .admin-auth-page {
            min-height: 100svh;
            padding: 0;
        }

        .admin-auth-shell {
            min-height: 100svh;
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        .admin-auth-brand {
            min-height: auto;
            padding: 24px 20px;
        }

        .admin-auth-brand h1 {
            font-size: 28px;
        }

        .admin-auth-brand p {
            font-size: 14px;
        }

        .admin-auth-status {
            grid-template-columns: 1fr;
        }

        .admin-auth-panel {
            padding: 28px 18px 34px;
        }

        .admin-auth-card h2 {
            font-size: 26px;
        }

        .admin-auth-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .admin-auth-submit,
        .admin-auth-secondary {
            width: 100%;
        }
    }
</style>
