(function loadHeadScript() {
    if (!window.__HEAD_SCRIPT__) return;
    if (window.__HEAD_SCRIPT_LOADED__) return;

    window.__HEAD_SCRIPT_LOADED__ = true;

    const script = document.createElement("script");
    script.type = "text/javascript";
    script.text = window.__HEAD_SCRIPT__;

    document.head.appendChild(script);
})();


document.addEventListener("DOMContentLoaded", () => {
    const bootstrapScript = document.createElement("script");
    bootstrapScript.src =
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js";
    document.body.appendChild(bootstrapScript);

    const ScrollTrigger = document.createElement("script");
    ScrollTrigger.src = "assets/js/ScrollTrigger.min.js";
    document.body.appendChild(ScrollTrigger);

    // ---> Add Footer Script
    if (!window.__FOOTER_SCRIPT__) return;

    const script = document.createElement("script");
    script.type = "text/javascript";
    script.text = window.__FOOTER_SCRIPT__;

    document.body.appendChild(script);
});


document.addEventListener("livewire:navigating", () => {
    document
        .querySelectorAll("link[data-page-style]")
        .forEach((el) => el.remove());
});

document.addEventListener("click", function (e) {
    const el = e.target.closest("[data-analytics]");
    if (!el) return;

    const payload = {
        event: el.dataset.event,
        entity_type: el.dataset.entity,
        entity_id: el.dataset.id,
        source: el.dataset.source,
        page: window.location.pathname,
    };

    // ✅ الطريقة المثالية للتتبع
    if (navigator.sendBeacon) {
        const blob = new Blob([JSON.stringify(payload)], {
            type: "application/json",
        });
        navigator.sendBeacon("/analytics/track", blob);
    } else {
        // fallback
        fetch("/analytics/track", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
            body: JSON.stringify(payload),
            keepalive: true,
        });
    }
});

