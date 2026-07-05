<style>
    .product-seo-card .card-header {
        background: #fff;
        border-bottom: 1px solid #edf0f4;
    }
    .seo-score-panel {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        border: 1px solid #edf0f4;
        border-radius: 8px;
        background: #fbfcff;
    }
    .seo-score-ring {
        width: 74px;
        height: 74px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        flex: 0 0 74px;
        background: conic-gradient(#f3545d 0deg, #edf0f4 0deg);
        position: relative;
        font-size: 22px;
        font-weight: 700;
        color: #152238;
    }
    .seo-score-ring::before {
        content: "";
        position: absolute;
        inset: 8px;
        border-radius: 50%;
        background: #fff;
    }
    .seo-score-ring span {
        position: relative;
        z-index: 1;
    }
    .seo-preview-box {
        border: 1px solid #edf0f4;
        border-radius: 8px;
        padding: 14px;
        background: #fff;
    }
    .seo-preview-label {
        display: block;
        margin-bottom: 8px;
        color: #6f7a8a;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-weight: 700;
    }
    .seo-preview-box h5 {
        color: #1a0dab;
        font-size: 18px;
        line-height: 1.3;
        margin-bottom: 5px;
    }
    .seo-preview-box p {
        margin-bottom: 6px;
        color: #545454;
        line-height: 1.45;
    }
    #seo-preview-url {
        color: #188038;
        font-size: 13px;
        overflow-wrap: anywhere;
    }
    .seo-social-image {
        height: 130px;
        border-radius: 7px;
        background: #eef2f7 center/cover no-repeat;
        margin-bottom: 12px;
    }
    .seo-checklist {
        display: grid;
        gap: 8px;
    }
    .seo-check {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        font-size: 13px;
        color: #6f7a8a;
    }
    .seo-check.is-ok i {
        color: #31ce36;
    }
    .seo-check.is-warning i {
        color: #ffad46;
    }
</style>
