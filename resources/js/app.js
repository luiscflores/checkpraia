import '../css/app.css';

// CheckPraia - Shared UI Interactions
// Performance-optimised: all init deferred to avoid blocking LCP

// ── Permission helpers (exposed on window for Alpine) ───────────────────────

window.CheckPraiaPermissions = {
    /**
     * Request geolocation with an explanation prompt before the browser dialog.
     * @param {string} purposeKey — translation key for the explanation (e.g. "beach.gps_report_purpose")
     * @returns {Promise<GeolocationPosition>}
     */
    requestLocation(purposeKey) {
        return new Promise((resolve, reject) => {
            if (!('geolocation' in navigator)) {
                reject(new Error('GPS_NOT_SUPPORTED'));
                return;
            }

            if (navigator.permissions && navigator.permissions.query) {
                navigator.permissions.query({ name: 'geolocation' }).then((status) => {
                    if (status.state === 'denied') {
                        reject(new Error('GPS_DENIED'));
                        return;
                    }
                    this._doGetCurrentPosition(resolve, reject);
                }).catch(() => {
                    this._doGetCurrentPosition(resolve, reject);
                });
            } else {
                this._doGetCurrentPosition(resolve, reject);
            }
        });
    },

    _doGetCurrentPosition(resolve, reject) {
        const MAX_ATTEMPTS = 3;
        let attempt = 0;

        const attemptLocation = () => {
            attempt++;
            if (attempt > MAX_ATTEMPTS) {
                reject(new Error('GPS_UNAVAILABLE'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    if (position.coords.accuracy > 150 && attempt < MAX_ATTEMPTS) {
                        attemptLocation();
                        return;
                    }
                    resolve(position);
                },
                (error) => {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            reject(new Error('GPS_DENIED'));
                            break;
                        case error.TIMEOUT:
                            if (attempt < MAX_ATTEMPTS) {
                                attemptLocation();
                            } else {
                                reject(new Error('GPS_TIMEOUT'));
                            }
                            break;
                        default:
                            if (attempt < MAX_ATTEMPTS) {
                                attemptLocation();
                            } else {
                                reject(new Error('GPS_ERROR'));
                            }
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        };

        attemptLocation();
    },
};

// ── Critical path: run on DOMContentLoaded ──────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initHeaderScrollEffect();
});

// ── Non-critical: defer to idle time ────────────────────────────────────────
const runWhenIdle = (fn) => {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(fn, { timeout: 1500 });
    } else {
        setTimeout(fn, 200);
    }
};

runWhenIdle(() => {
    initScrollAnimations();
    initMobilePullToRefresh();
    initCardTouchFeedback();
});

// ── Scroll Animations (IntersectionObserver — non-blocking) ──────────────────
function initScrollAnimations() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                const delay = entry.target.dataset.staggerDelay
                    ? parseFloat(entry.target.dataset.staggerDelay)
                    : Math.min(index * 0.04, 0.4);
                entry.target.style.animationDelay = `${delay}s`;
                entry.target.classList.add('animate-fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.05,
        rootMargin: '0px 0px -30px 0px'
    });

    document.querySelectorAll('[data-animate]').forEach(el => {
        observer.observe(el);
    });

    document.querySelectorAll('[data-animate-stagger]').forEach((container) => {
        const children = container.children;
        const delay = parseFloat(container.dataset.animateStagger) || 0.04;
        Array.from(children).forEach((child, i) => {
            child.dataset.staggerDelay = String(i * delay);
            observer.observe(child);
        });
    });
}

// ── Header scroll shadow (passive listener, rAF throttled) ───────────────────
function initHeaderScrollEffect() {
    const header = document.querySelector('header');
    if (!header) return;

    let ticking = false;
    const onScroll = () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const scrolled = window.scrollY > 10;
                header.classList.toggle('shadow-lg', scrolled);
                header.classList.toggle('shadow-blue-500/5', scrolled);
                ticking = false;
            });
            ticking = true;
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

// ── Mobile touch card feedback (replaces CSS :active which lags on iOS) ──────
function initCardTouchFeedback() {
    // Only on touch devices
    if (!('ontouchstart' in window)) return;

    document.addEventListener('touchstart', (e) => {
        const card = e.target.closest('.glass-card');
        if (card) card.classList.add('opacity-90');
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        const card = e.target.closest('.glass-card');
        if (card) {
            requestAnimationFrame(() => card.classList.remove('opacity-90'));
        }
    }, { passive: true });
}

// ── Mobile Pull-to-Refresh ────────────────────────────────────────────────────
function initMobilePullToRefresh() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (window.matchMedia('(min-width: 768px)').matches) return;

    const main = document.querySelector('main');
    if (!main) return;

    let startY = 0;
    let pulling = false;
    let pullDist = 0;
    const threshold = 80;
    let indicator = null;

    const getOrCreateIndicator = () => {
        if (indicator) return indicator;
        indicator = document.createElement('div');
        indicator.className = 'pull-indicator';
        indicator.innerHTML = `
            <div class="flex items-center gap-2 text-sm font-medium" style="color:var(--text-secondary)">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Atualizar...</span>
            </div>
        `;
        document.body.prepend(indicator);
        return indicator;
    };

    const isInsideMap = (el) => {
        while (el && el !== main) {
            if (el.classList && (el.classList.contains('leaflet-container') || el.id === 'map-continente' || el.id === 'map-acores' || el.id === 'map-madeira')) {
                return true;
            }
            el = el.parentElement;
        }
        return false;
    };

    main.addEventListener('touchstart', (e) => {
        if (isInsideMap(e.target)) return;
        if (window.scrollY === 0) {
            startY = e.touches[0].clientY;
            pulling = true;
        }
    }, { passive: true });

    main.addEventListener('touchmove', (e) => {
        if (!pulling) return;
        pullDist = e.touches[0].clientY - startY;
        if (pullDist > 0 && pullDist < threshold * 1.5) {
            const ind = getOrCreateIndicator();
            const progress = Math.min(pullDist * 0.5, threshold);
            ind.style.transform = `translateX(-50%) translateY(${progress - 60}px)`;
            ind.style.opacity = pullDist > 20 ? Math.min((pullDist - 20) / 40, 1) : 0;
        }
    }, { passive: true });

    main.addEventListener('touchend', () => {
        if (!pulling) return;
        if (pullDist >= threshold) {
            window.location.reload();
        }
        if (indicator) {
            indicator.style.opacity = '0';
            indicator.style.transform = 'translateX(-50%) translateY(-60px)';
            setTimeout(() => {
                if (indicator?.parentNode) indicator.parentNode.removeChild(indicator);
                indicator = null;
            }, 250);
        }
        pulling = false;
        pullDist = 0;
    }, { passive: true });
}
