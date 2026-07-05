<style>
    :root {
        --customer-auth-ink: #162031;
        --customer-auth-muted: #667085;
        --customer-auth-line: #d9e1ea;
        --customer-auth-blue: #0f5f83;
        --customer-auth-green: #2f7f68;
        --customer-auth-gold: #ffaa20;
        --customer-auth-card: #ffffff;
    }

    .customer-auth-area {
        padding: 34px 0 54px;
        background:
            linear-gradient(135deg, rgba(15, 95, 131, .08), rgba(255, 170, 32, .1)),
            #f2f6f8;
    }

    .customer-auth-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, .92fr) minmax(360px, .72fr);
        gap: 22px;
        align-items: stretch;
    }

    .customer-auth-shell.is-wide {
        grid-template-columns: minmax(320px, .78fr) minmax(560px, 1.1fr);
    }

    .customer-auth-showcase,
    .customer-auth-card {
        border: 1px solid rgba(15, 95, 131, .12);
        border-radius: 8px;
        background: var(--customer-auth-card);
        box-shadow: 0 22px 54px rgba(22, 32, 49, .1);
    }

    .customer-auth-showcase {
        position: relative;
        min-height: 560px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 34px;
        overflow: hidden;
        padding: 38px;
        background:
            linear-gradient(135deg, rgba(15, 95, 131, .94), rgba(47, 127, 104, .88)),
            #0f5f83;
        color: #fff;
    }

    .customer-auth-showcase::before {
        content: "";
        position: absolute;
        inset: auto -10% -28% 28%;
        height: 56%;
        border-radius: 999px 0 0 0;
        background: rgba(255, 255, 255, .12);
        transform: rotate(-6deg);
    }

    .customer-auth-showcase::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .05), rgba(255, 255, 255, 0)),
            repeating-linear-gradient(135deg, rgba(255, 255, 255, .12) 0 1px, transparent 1px 18px);
        opacity: .55;
        pointer-events: none;
    }

    .customer-auth-showcase > * {
        position: relative;
        z-index: 1;
    }

    .customer-auth-logo {
        width: 184px;
        max-width: 70%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 16px;
        border-radius: 8px;
        background: rgba(255, 255, 255, .95);
        box-shadow: 0 18px 40px rgba(0, 0, 0, .16);
    }

    .customer-auth-logo img {
        max-width: 100%;
        max-height: 76px;
        object-fit: contain;
    }

    .customer-auth-copy {
        max-width: 560px;
    }

    .customer-auth-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 18px;
        color: rgba(255, 255, 255, .9);
        font-size: 13px;
        font-weight: 800;
        line-height: 1.3;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .customer-auth-kicker::before {
        content: "";
        width: 30px;
        height: 3px;
        border-radius: 999px;
        background: var(--customer-auth-gold);
    }

    .customer-auth-copy h1 {
        margin: 0;
        color: #fff;
        font-size: 48px;
        line-height: 1.04;
        font-weight: 800;
        letter-spacing: 0;
    }

    .customer-auth-copy p {
        max-width: 470px;
        margin: 18px 0 0;
        color: rgba(255, 255, 255, .84);
        font-size: 16px;
        line-height: 1.65;
    }

    .customer-auth-benefits {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .customer-auth-benefits.is-stacked {
        grid-template-columns: 1fr;
    }

    .customer-auth-benefit {
        min-height: 118px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 18px;
        border: 1px solid rgba(255, 255, 255, .17);
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
        backdrop-filter: blur(10px);
    }

    .customer-auth-benefit i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        margin-bottom: 12px;
        border-radius: 8px;
        background: rgba(255, 255, 255, .15);
        color: #fff;
        font-size: 18px;
    }

    .customer-auth-benefit strong {
        display: block;
        color: #fff;
        font-size: 15px;
        line-height: 1.35;
        font-weight: 800;
    }

    .customer-auth-benefit span {
        display: block;
        margin-top: 6px;
        color: rgba(255, 255, 255, .78);
        font-size: 13px;
        line-height: 1.45;
    }

    .customer-auth-card {
        display: flex;
        align-items: center;
        padding: 42px;
    }

    .customer-auth-card.is-wide {
        align-items: flex-start;
    }

    .customer-auth-form {
        width: 100%;
    }

    .customer-auth-form h2 {
        margin: 0;
        color: var(--customer-auth-ink);
        font-size: 31px;
        line-height: 1.14;
        font-weight: 800;
        letter-spacing: 0;
    }

    .customer-auth-subtitle {
        margin: 10px 0 28px;
        color: var(--customer-auth-muted);
        font-size: 15px;
        line-height: 1.55;
    }

    .customer-auth-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .customer-auth-field {
        margin-bottom: 18px;
    }

    .customer-auth-grid .customer-auth-field {
        margin-bottom: 0;
    }

    .customer-auth-field.is-full {
        grid-column: 1 / -1;
    }

    .customer-auth-field label {
        display: block;
        margin-bottom: 8px;
        color: #344054;
        font-size: 13px;
        font-weight: 800;
    }

    .customer-auth-input {
        position: relative;
    }

    .customer-auth-input > i {
        position: absolute;
        left: 16px;
        top: 50%;
        color: #7b8da2;
        font-size: 17px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .customer-auth-input .form-control {
        width: 100%;
        min-height: 56px;
        padding: 15px 48px 15px 48px;
        border: 1px solid var(--customer-auth-line);
        border-radius: 8px;
        background: #fbfcfd;
        color: var(--customer-auth-ink);
        font-size: 15px;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .customer-auth-input .form-control:focus {
        border-color: var(--customer-auth-blue);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(15, 95, 131, .12);
    }

    .customer-auth-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #667085;
        transform: translateY(-50%);
        cursor: pointer;
    }

    .customer-auth-toggle:hover,
    .customer-auth-toggle:focus {
        background: #edf4f7;
        color: var(--customer-auth-blue);
        outline: none;
    }

    .customer-auth-error {
        margin: 8px 0 0;
        color: #c0392b;
        font-size: 13px;
        line-height: 1.4;
    }

    .customer-auth-note {
        display: block;
        margin-top: 8px;
        color: #667085;
        font-size: 13px;
        line-height: 1.45;
    }

    .customer-auth-recaptcha {
        margin-top: 18px;
    }

    .customer-auth-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 24px;
    }

    .customer-auth-submit,
    .customer-auth-secondary {
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

    .customer-auth-submit {
        min-width: 180px;
        border: 0;
        padding: 14px 22px;
        background: var(--customer-auth-blue);
        color: #fff;
        box-shadow: 0 16px 30px rgba(15, 95, 131, .24);
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .customer-auth-submit:hover,
    .customer-auth-submit:focus {
        background: #0b526f;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 19px 36px rgba(15, 95, 131, .3);
    }

    .customer-auth-secondary {
        color: var(--customer-auth-blue);
    }

    .customer-auth-secondary:hover,
    .customer-auth-secondary:focus {
        color: #0b526f;
        text-decoration: underline;
    }

    .customer-auth-register {
        margin: 20px 0 0;
        color: var(--customer-auth-muted);
        font-size: 14px;
        line-height: 1.5;
        text-align: center;
    }

    .customer-auth-register a {
        color: var(--customer-auth-blue);
        font-weight: 800;
        text-decoration: none;
    }

    .customer-auth-register a:hover {
        color: #0b526f;
        text-decoration: underline;
    }

    @media (max-width: 991px) {
        .customer-auth-area {
            padding: 24px 0 42px;
        }

        .customer-auth-shell,
        .customer-auth-shell.is-wide {
            grid-template-columns: 1fr;
        }

        .customer-auth-showcase {
            min-height: auto;
            padding: 32px;
        }

        .customer-auth-copy {
            max-width: 680px;
        }

        .customer-auth-copy h1 {
            font-size: 34px;
        }
    }

    @media (max-width: 767px) {
        .customer-auth-shell,
        .customer-auth-shell.is-wide {
            width: min(100% - 22px, 560px);
            gap: 14px;
        }

        .customer-auth-benefits,
        .customer-auth-benefits.is-stacked,
        .customer-auth-grid {
            grid-template-columns: 1fr;
        }

        .customer-auth-benefit {
            min-height: 92px;
        }

        .customer-auth-card {
            padding: 28px 22px;
        }
    }

    @media (max-width: 480px) {
        .customer-auth-area {
            padding: 12px 0 28px;
        }

        .customer-auth-shell,
        .customer-auth-shell.is-wide {
            width: 100%;
        }

        .customer-auth-showcase,
        .customer-auth-card {
            border-left: 0;
            border-right: 0;
            border-radius: 0;
            box-shadow: none;
        }

        .customer-auth-showcase {
            padding: 26px 18px;
        }

        .customer-auth-logo {
            width: 156px;
        }

        .customer-auth-copy h1 {
            font-size: 29px;
        }

        .customer-auth-copy p {
            font-size: 14px;
        }

        .customer-auth-card {
            padding: 28px 18px 34px;
        }

        .customer-auth-form h2 {
            font-size: 26px;
        }

        .customer-auth-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .customer-auth-submit,
        .customer-auth-secondary {
            width: 100%;
        }
    }
</style>
