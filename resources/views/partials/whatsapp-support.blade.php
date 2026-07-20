@if (config('dev.whatsapp_support'))
@php
    $whatsappNumber = config('dev.whatsapp_support');
    $whatsappMessage = rawurlencode('Hello, I need help with '.config('app.name').'.');
@endphp
<style>
    .whatsapp-support-wrap {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.75rem;
    }
    .whatsapp-support-tip {
        position: relative;
        width: min(18rem, calc(100vw - 2.5rem));
        background: #fff;
        color: #0f172a;
        border-radius: 1rem;
        padding: 1rem 2.25rem 1rem 1rem;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.18);
        border: 1px solid rgba(15, 23, 42, 0.06);
        opacity: 0;
        transform: translateY(8px) scale(0.96);
        pointer-events: none;
        transition: opacity 0.35s ease, transform 0.35s ease;
    }
    .whatsapp-support-tip.is-visible {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }
    .whatsapp-support-tip.is-hidden {
        opacity: 0;
        transform: translateY(8px) scale(0.96);
        pointer-events: none;
    }
    .whatsapp-support-tip::after {
        content: '';
        position: absolute;
        bottom: -7px;
        right: 1.35rem;
        width: 14px;
        height: 14px;
        background: #fff;
        border-right: 1px solid rgba(15, 23, 42, 0.06);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        transform: rotate(45deg);
    }
    .whatsapp-support-tip__title {
        font-weight: 700;
        font-size: 0.95rem;
        margin: 0 0 0.35rem;
        line-height: 1.3;
    }
    .whatsapp-support-tip__text {
        margin: 0;
        font-size: 0.84rem;
        line-height: 1.45;
        color: #475569;
    }
    .whatsapp-support-tip__close {
        position: absolute;
        top: 0.55rem;
        right: 0.55rem;
        width: 1.65rem;
        height: 1.65rem;
        border: 0;
        border-radius: 50%;
        background: #f1f5f9;
        color: #64748b;
        font-size: 1.1rem;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .whatsapp-support-tip__close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .whatsapp-support {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #25d366;
        color: #fff !important;
        text-decoration: none !important;
        padding: 0.75rem 1rem;
        border-radius: 999px;
        box-shadow: 0 4px 14px rgba(37, 211, 102, 0.45);
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .whatsapp-support:hover {
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(37, 211, 102, 0.55);
    }
    .whatsapp-support svg {
        width: 1.35rem;
        height: 1.35rem;
        flex-shrink: 0;
        fill: currentColor;
    }
    @media (max-width: 575.98px) {
        .whatsapp-support span {
            display: none;
        }
        .whatsapp-support {
            padding: 0.85rem;
            border-radius: 50%;
        }
        .whatsapp-support-tip {
            width: min(16.5rem, calc(100vw - 2.5rem));
        }
    }
</style>
<div class="whatsapp-support-wrap" id="whatsapp-support-wrap">
    <div class="whatsapp-support-tip" id="whatsapp-support-tip" role="status" aria-live="polite">
        <button type="button" class="whatsapp-support-tip__close" id="whatsapp-support-tip-close" aria-label="Dismiss">&times;</button>
        <p class="whatsapp-support-tip__title">Need a hand?</p>
        <p class="whatsapp-support-tip__text">Run into any issues? Tap below to chat with us on WhatsApp — we're happy to help.</p>
    </div>
    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
       class="whatsapp-support"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Chat with us on WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        <span>Chat support</span>
    </a>
</div>
<script>
(function () {
    var tip = document.getElementById('whatsapp-support-tip');
    var closeBtn = document.getElementById('whatsapp-support-tip-close');
    if (!tip || !closeBtn) return;

    var hideTimer;

    function hideTip() {
        tip.classList.remove('is-visible');
        tip.classList.add('is-hidden');
        if (hideTimer) clearTimeout(hideTimer);
    }

    function showTip() {
        tip.classList.remove('is-hidden');
        requestAnimationFrame(function () {
            tip.classList.add('is-visible');
        });
        hideTimer = setTimeout(hideTip, 10000);
    }

    closeBtn.addEventListener('click', hideTip);
    showTip();
})();
</script>
@endif
