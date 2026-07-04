// CheckPraia - Shared UI Interactions

document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initHeaderScrollEffect();
    initPageTransitions();
    initMobilePullToRefresh();
});

function initScrollAnimations() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                const delay = entry.target.dataset.staggerDelay
                    ? parseFloat(entry.target.dataset.staggerDelay)
                    : Math.min(index * 0.05, 0.5);
                entry.target.style.animationDelay = `${delay}s`;
                entry.target.classList.add('animate-fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    document.querySelectorAll('[data-animate]').forEach(el => {
        observer.observe(el);
    });

    document.querySelectorAll('[data-animate-stagger]').forEach((container) => {
        const children = container.children;
        Array.from(children).forEach((child, i) => {
            const delay = parseFloat(container.dataset.animateStagger) || 0.05;
            child.dataset.staggerDelay = String(i * delay);
        });
        Array.from(children).forEach(child => observer.observe(child));
    });
}

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

function initPageTransitions() {
    const main = document.querySelector('main');
    if (main) {
        main.classList.add('page-enter');
    }
}

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

    const createIndicator = () => {
        indicator = document.createElement('div');
        indicator.className = 'pull-indicator';
        indicator.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-theme-secondary font-medium">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Atualizar...</span>
            </div>
        `;
        document.body.prepend(indicator);
    };

    main.addEventListener('touchstart', (e) => {
        if (window.scrollY === 0) {
            startY = e.touches[0].clientY;
            pulling = true;
        }
    }, { passive: true });

    main.addEventListener('touchmove', (e) => {
        if (!pulling) return;
        pullDist = e.touches[0].clientY - startY;
        if (pullDist > 0 && pullDist < threshold * 1.5) {
            if (!indicator) createIndicator();
            indicator.style.transform = `translateY(${Math.min(pullDist * 0.5, threshold)}px)`;
            indicator.classList.toggle('visible', pullDist > 20);
        }
    }, { passive: true });

    main.addEventListener('touchend', () => {
        if (!pulling) return;
        if (pullDist >= threshold) {
            window.location.reload();
        }
        if (indicator) {
            indicator.classList.remove('visible');
            indicator.style.transform = '';
            setTimeout(() => {
                if (indicator && indicator.parentNode) indicator.parentNode.removeChild(indicator);
                indicator = null;
            }, 300);
        }
        pulling = false;
        pullDist = 0;
    }, { passive: true });
}
