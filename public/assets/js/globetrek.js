/**
 * GlobeTrek front-end behaviour.
 *
 * Replaces the template's demo main.js. Vanilla ES2020, no jQuery — the only
 * dependencies are Bootstrap (modals/tabs/collapse), Swiper, noUiSlider,
 * Fancybox and WOW, all loaded before this file.
 */
(function () {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const money = (n) => '$' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });

    /* ------------------------------------------------------------------ chrome */

    function preloader() {
        const el = $('.preload-container');
        if (!el) return;
        const hide = () => el.classList.add('is-hidden');
        window.addEventListener('load', () => setTimeout(hide, 200));
        // Never let a slow asset trap the page behind the spinner.
        setTimeout(hide, 2500);
    }

    function headerScroll() {
        const header = $('#header');
        if (!header) return;
        const apply = () => header.classList.toggle('is-scrolled', window.scrollY > 80);
        apply();
        window.addEventListener('scroll', apply, { passive: true });
    }

    function backToTop() {
        const btn = $('#backtotop');
        if (!btn) return;
        const progress = $('.border-progress', btn);

        const update = () => {
            const max = document.body.scrollHeight - window.innerHeight;
            const pct = max > 0 ? (window.scrollY / max) * 100 : 0;
            btn.classList.toggle('is-visible', window.scrollY > 400);
            if (progress) progress.style.background =
                `conic-gradient(var(--primary, #3c6e57) ${pct * 3.6}deg, rgba(0,0,0,.08) 0deg)`;
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    function mobileMenu() {
        const toggle = $('.toggle-mobile');
        const menu = $('.mobile-menu');
        const overlay = $('.overlay');
        const close = $('.close-btn');
        if (!toggle || !menu) return;

        const setOpen = (open) => {
            menu.classList.toggle('is-open', open);
            overlay?.classList.toggle('is-open', open);
            close?.classList.toggle('is-open', open);
            document.body.classList.toggle('gt-no-scroll', open);
        };

        toggle.addEventListener('click', () => setOpen(!menu.classList.contains('is-open')));
        overlay?.addEventListener('click', () => setOpen(false));
        close?.addEventListener('click', () => setOpen(false));
        document.addEventListener('keydown', (e) => e.key === 'Escape' && setOpen(false));

        // Collapsible sub-menus inside the drawer.
        $$('.mobile-dropdown', menu).forEach((link) => {
            const sub = link.nextElementSibling;
            if (!sub || !sub.classList.contains('mb-sub-menu')) return;
            link.addEventListener('click', (e) => {
                e.preventDefault();
                link.classList.toggle('is-open');
                sub.classList.toggle('is-open');
            });
        });
    }

    /* ----------------------------------------------------------------- sliders */

    const SWIPER_PRESETS = {
        hero: { effect: 'fade', fadeEffect: { crossFade: true }, navigation: { nextEl: '.flex-next', prevEl: '.flex-prev' } },
        types: { pagination: '.sw-pagination-types' },
        benefit: { pagination: '.sw-pagination-device' },
        tour: { pagination: '.sw-pagination-tour' },
        testimonial: {
            pagination: '.sw-pagination-tes',
            navigation: { nextEl: '.nav-next-location', prevEl: '.nav-prev-location' },
        },
        gallery: { pagination: '.sw-pagination-gallery' },
        related: { pagination: '.sw-pagination-related' },
    };

    function initSwipers() {
        if (typeof Swiper === 'undefined') return;

        $$('[data-gt-swiper]').forEach((el) => {
            const d = el.dataset;
            const preset = SWIPER_PRESETS[d.gtSwiper] || {};

            const perView = (v, fallback) => Number(v || fallback);
            const space = Number(d.space || 30);

            const options = {
                slidesPerView: perView(d.mobile, 1),
                spaceBetween: space,
                loop: d.loop === 'true',
                speed: Number(d.speed || 700),
                watchOverflow: true,
                breakpoints: {
                    576: { slidesPerView: perView(d.mobileSm || d.mobile, 1) },
                    768: { slidesPerView: perView(d.tablet || d.preview, 2) },
                    1200: { slidesPerView: perView(d.preview, 3) },
                },
                ...preset,
            };

            if (d.autoplay === 'true') options.autoplay = { delay: 5500, disableOnInteraction: false };
            if (preset.pagination) options.pagination = { el: preset.pagination, clickable: true };
            if (preset.navigation) {
                options.navigation = {
                    nextEl: el.querySelector(preset.navigation.nextEl) || preset.navigation.nextEl,
                    prevEl: el.querySelector(preset.navigation.prevEl) || preset.navigation.prevEl,
                };
            }

            new Swiper(el, options);
        });
    }

    /* ---------------------------------------------------------------- wishlist */

    const Wishlist = {
        key: 'gt_wishlist',

        read() {
            try {
                return JSON.parse(localStorage.getItem(this.key) || '[]').map(Number);
            } catch {
                return [];
            }
        },

        write(ids) {
            localStorage.setItem(this.key, JSON.stringify(ids));
            this.paint();
            this.sync(ids);
        },

        toggle(id) {
            const ids = this.read();
            const at = ids.indexOf(id);
            const added = at === -1;
            added ? ids.push(id) : ids.splice(at, 1);
            this.write(ids);
            return added;
        },

        /** Mirrors into a cookie so the wishlist page can render server-side. */
        sync(ids) {
            const token = $('meta[name="csrf-token"]')?.content;
            if (!token) return;
            fetch('/wishlist/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ ids }),
            }).catch(() => {});
        },

        paint() {
            const ids = this.read();
            $$('.gt-wishlist-toggle').forEach((btn) => {
                btn.classList.toggle('is-active', ids.includes(Number(btn.dataset.tourId)));
            });
            $$('[data-wishlist-count]').forEach((el) => {
                el.textContent = ids.length;
                el.hidden = ids.length === 0;
            });
        },
    };

    function initWishlist() {
        Wishlist.paint();
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.gt-wishlist-toggle');
            if (!btn) return;
            e.preventDefault();
            const added = Wishlist.toggle(Number(btn.dataset.tourId));
            toast(added ? 'Saved to your wishlist.' : 'Removed from your wishlist.', 'success');
        });
    }

    /* ------------------------------------------------------------------- toast */

    function toast(message, tone = 'info') {
        const stack = $('#gt-toast-stack');
        if (!stack) return;
        const el = document.createElement('div');
        el.className = `gt-toast gt-toast--${tone}`;
        el.innerHTML = `<span class="gt-toast__text"></span>
            <button type="button" class="gt-toast__close" aria-label="Dismiss">&times;</button>`;
        $('.gt-toast__text', el).textContent = message;
        stack.appendChild(el);
        setTimeout(() => el.classList.add('is-leaving'), 4000);
        setTimeout(() => el.remove(), 4400);
    }

    function initToasts() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.gt-toast__close')) e.target.closest('.gt-toast').remove();
        });
        $$('#gt-toast-stack .gt-toast').forEach((el) => {
            setTimeout(() => el.classList.add('is-leaving'), 5000);
            setTimeout(() => el.remove(), 5400);
        });
    }

    /* ------------------------------------------------------------- tour filters */

    function initPriceSlider() {
        const el = $('#gt-price-slider');
        if (!el || typeof noUiSlider === 'undefined') return;

        const form = el.closest('form');
        const minInput = $('#gt-price-min');
        const maxInput = $('#gt-price-max');
        const label = $('#gt-price-label');
        const floor = Number(el.dataset.floor || 0);
        const ceiling = Number(el.dataset.ceiling || 5000);

        noUiSlider.create(el, {
            start: [Number(minInput.value || floor), Number(maxInput.value || ceiling)],
            connect: true,
            step: 25,
            range: { min: floor, max: ceiling },
        });

        el.noUiSlider.on('update', (values) => {
            const [lo, hi] = values.map((v) => Math.round(Number(v)));
            minInput.value = lo;
            maxInput.value = hi;
            if (label) label.textContent = `${money(lo)} – ${money(hi)}${hi >= ceiling ? '+' : ''}`;
        });

        el.noUiSlider.on('change', () => form?.requestSubmit());
    }

    function initFilterForm() {
        const form = $('#gt-filter-form');
        if (!form) return;

        // Checkboxes and selects apply immediately; the text search waits for a pause.
        form.addEventListener('change', (e) => {
            if (e.target.matches('input[type="checkbox"], select, input[type="date"], input[type="number"]')) {
                form.requestSubmit();
            }
        });

        const search = $('#gt-filter-search', form);
        if (search) {
            let timer;
            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => form.requestSubmit(), 500);
            });
        }

        // Layout switch (list / grid) submits through hidden input.
        $$('[data-layout-switch]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const input = $('#gt-filter-layout', form);
                if (input) input.value = btn.dataset.layoutSwitch;
                form.requestSubmit();
            });
        });

        // Sort select lives outside the sidebar but belongs to the same form.
        $('#gt-sort-select')?.addEventListener('change', function () {
            const input = $('#gt-filter-sort', form);
            if (input) input.value = this.value;
            form.requestSubmit();
        });
    }

    /* ------------------------------------------------------------ booking widget */

    function initBookingWidget() {
        const widget = $('[data-booking-widget]');
        if (!widget) return;

        const unitPrice = Number(widget.dataset.price);
        const childRate = Number(widget.dataset.childRate || 0.6);
        const maxGuests = Number(widget.dataset.maxGuests || 99);

        const adults = $('[data-count="adults"]', widget);
        const children = $('[data-count="children"]', widget);
        const totalEl = $('[data-booking-total]', widget);
        const breakdownEl = $('[data-booking-breakdown]', widget);
        const warnEl = $('[data-booking-warning]', widget);

        const recalc = () => {
            const a = Number(adults?.value || 1);
            const c = Number(children?.value || 0);
            const extras = $$('[data-extra]:checked', widget);
            const extrasTotal = extras.reduce((sum, el) => sum + Number(el.dataset.extraPrice), 0);
            const subtotal = unitPrice * a + unitPrice * childRate * c;

            if (breakdownEl) {
                const rows = [`<li><span>Adults × ${a}</span><span>${money(unitPrice * a)}</span></li>`];
                if (c > 0) rows.push(`<li><span>Children × ${c}</span><span>${money(unitPrice * childRate * c)}</span></li>`);
                extras.forEach((el) => rows.push(
                    `<li><span>${el.dataset.extra}</span><span>${money(el.dataset.extraPrice)}</span></li>`));
                breakdownEl.innerHTML = rows.join('');
            }

            if (totalEl) totalEl.textContent = money(subtotal + extrasTotal);

            const over = a + c > maxGuests;
            if (warnEl) {
                warnEl.hidden = !over;
                warnEl.textContent = over ? `This departure takes a maximum of ${maxGuests} guests.` : '';
            }
            $('[data-booking-submit]', widget)?.toggleAttribute('disabled', over);
        };

        // +/- steppers.
        $$('[data-step]', widget).forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const input = $(`[data-count="${btn.dataset.stepTarget}"]`, widget);
                if (!input) return;
                const next = Number(input.value || 0) + Number(btn.dataset.step);
                input.value = Math.max(Number(input.min || 0), Math.min(Number(input.max || 99), next));
                recalc();
            });
        });

        widget.addEventListener('change', (e) => {
            if (e.target.matches('[data-extra], [data-count]')) recalc();
        });

        recalc();
    }

    /* ----------------------------------------------------------- review rating */

    function initRatingPicker() {
        $$('[data-rating-picker]').forEach((picker) => {
            const input = $('input[type="hidden"]', picker);
            const stars = $$('[data-star]', picker);

            const paint = (value) => stars.forEach((s) =>
                s.classList.toggle('is-active', Number(s.dataset.star) <= value));

            stars.forEach((star) => {
                star.addEventListener('mouseenter', () => paint(Number(star.dataset.star)));
                star.addEventListener('click', () => {
                    input.value = star.dataset.star;
                    paint(Number(star.dataset.star));
                });
            });

            picker.addEventListener('mouseleave', () => paint(Number(input.value || 0)));
            paint(Number(input.value || 0));
        });
    }

    /* ---------------------------------------------------------------- accordions */

    function initAccordions() {
        $$('[data-accordion] [data-accordion-trigger]').forEach((trigger) => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const item = trigger.closest('[data-accordion-item]');
                const open = item.classList.contains('is-open');
                if (!trigger.closest('[data-accordion]').hasAttribute('data-accordion-multi')) {
                    $$('[data-accordion-item]', trigger.closest('[data-accordion]'))
                        .forEach((i) => i.classList.remove('is-open'));
                }
                item.classList.toggle('is-open', !open);
            });
        });
    }

    /* --------------------------------------------------------------- confirm/UX */

    function initConfirmations() {
        document.addEventListener('submit', (e) => {
            const message = e.target.dataset?.confirm;
            if (message && !window.confirm(message)) e.preventDefault();
        });
    }

    /* -------------------------------------------------------------------- boot */

    document.addEventListener('DOMContentLoaded', () => {
        preloader();
        headerScroll();
        backToTop();
        mobileMenu();
        initSwipers();
        initWishlist();
        initToasts();
        initPriceSlider();
        initFilterForm();
        initBookingWidget();
        initRatingPicker();
        initAccordions();
        initConfirmations();

        if (typeof WOW !== 'undefined') {
            new WOW({ mobile: false, live: false }).init();
        }
        // animate.css keeps .wow elements hidden; make sure nothing is left
        // invisible if an element never enters WOW's viewport calculation.
        setTimeout(() => $$('.wow').forEach((el) => {
            if (getComputedStyle(el).visibility === 'hidden') el.classList.add('gt-revealed');
        }), 1200);
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox]', {});
        }
    });

    window.GlobeTrek = { toast };
})();
