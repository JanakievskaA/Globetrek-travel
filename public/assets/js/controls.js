/**
 * GlobeTrek form controls — custom dropdowns and date fields.
 *
 * The native <select> popup can't be styled, so every single-choice select on
 * the page is paired with a listbox we own. The original element stays in the
 * DOM (invisible, but laid out) holding the name and value: forms submit as
 * before, `:required` validation can still focus and flag it, and picking an
 * option writes back and re-dispatches `change`, so the auto-submitting filter
 * bars and the booking widget keep working untouched.
 *
 * Everything here is enhancement — with JavaScript off the native controls are
 * still there and fully usable.
 *
 * Per-element hooks: `data-gt-native` leaves a field alone entirely,
 * `data-no-filter` suppresses the filter box a long list would otherwise get,
 * and `data-empty-label` sets what a blank date field reads as.
 */
(function () {
    'use strict';

    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    /* Layout/spacing utilities belong to the box that replaces the field in the
       flow; everything else is a skin and belongs on the visible trigger. */
    const LAYOUT_CLASS = /^(w-|h-|m[trblxy]?-|flex|grow|shrink|col-|order-|align-|justify-|adm-grow)/;

    const CALENDAR_ICON =
        '<svg class="gt-date__icon" viewBox="0 0 24 24" width="17" height="17" fill="none" ' +
        'stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true">' +
        '<rect x="3.25" y="5" width="17.5" height="16" rx="3"/><path d="M3.25 10h17.5M8 3v4M16 3v4"/></svg>';

    let seq = 0;
    let open = null;
    let scrimEl = null;

    /* --------------------------------------------------------------- helpers */

    function scrim(show) {
        if (!scrimEl) {
            scrimEl = document.createElement('div');
            scrimEl.className = 'gt-combo-scrim';
            scrimEl.addEventListener('click', () => open && open.close(true));
            document.body.appendChild(scrimEl);
        }
        scrimEl.classList.toggle('is-open', show);
    }

    /* -------------------------------------------------------------- combobox */

    class Combo {
        constructor(select) {
            this.select = select;
            this.id = 'gt-combo-' + ++seq;
            this.isOpen = false;
            this.active = -1;
            this.typed = '';

            const wrap = document.createElement('div');
            wrap.className = 'gt-combo';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'gt-combo__trigger';
            trigger.id = this.id + '-trigger';
            trigger.disabled = select.disabled;
            trigger.innerHTML =
                '<span class="gt-combo__value"></span>' +
                '<span class="gt-combo__caret icon icon-CaretDown" aria-hidden="true"></span>';

            select.classList.forEach((c) => (LAYOUT_CLASS.test(c) ? wrap : trigger).classList.add(c));

            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
            wrap.appendChild(trigger);

            select.setAttribute('tabindex', '-1');
            select.setAttribute('aria-hidden', 'true');

            trigger.setAttribute('role', 'combobox');
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');

            this.wrap = wrap;
            this.trigger = trigger;
            this.valueEl = trigger.firstElementChild;

            this.linkLabel();
            this.buildPanel();
            this.sync();
            this.bind();

            select.dataset.gtCombo = this.id;
        }

        /** Keep the existing <label> working: it now points at the trigger. */
        linkLabel() {
            const label = this.select.id
                ? document.querySelector('label[for="' + CSS.escape(this.select.id) + '"]')
                : this.select.closest('label');

            if (!label) {
                const aria = this.select.getAttribute('aria-label');
                if (aria) this.trigger.setAttribute('aria-label', aria);
                return;
            }

            if (!label.id) label.id = this.id + '-label';
            this.trigger.setAttribute('aria-labelledby', label.id);
            if (label.htmlFor) label.htmlFor = this.trigger.id;
        }

        buildPanel() {
            const panel = document.createElement('div');
            panel.className = 'gt-combo-panel';

            const list = document.createElement('ul');
            list.className = 'gt-combo-panel__list';
            list.id = this.id + '-list';
            list.setAttribute('role', 'listbox');
            panel.appendChild(list);

            this.trigger.setAttribute('aria-controls', list.id);

            this.panel = panel;
            this.list = list;
            this.renderOptions();

            // Scrolling sixty destinations is not a user interface — long lists
            // get a filter box, unless the field opts out (`data-no-filter`,
            // for lists like group sizes where searching is pointless).
            if (this.select.options.length > 8 && !('noFilter' in this.select.dataset)) this.buildSearch();

            document.body.appendChild(panel);
        }

        buildSearch() {
            const box = document.createElement('div');
            box.className = 'gt-combo-panel__search';
            box.innerHTML = '<span class="icon icon-search" aria-hidden="true"></span>';

            const input = document.createElement('input');
            input.type = 'text';
            input.autocomplete = 'off';
            input.placeholder = 'Search…';
            input.setAttribute('aria-label', 'Filter options');
            box.appendChild(input);

            const empty = document.createElement('p');
            empty.className = 'gt-combo-panel__empty';
            empty.textContent = 'No matches';
            empty.hidden = true;

            this.panel.insertBefore(box, this.list);
            this.panel.appendChild(empty);

            this.search = input;
            this.empty = empty;

            input.addEventListener('input', () => this.filter(input.value));
            input.addEventListener('keydown', (e) => this.onKeydown(e));
        }

        renderOptions() {
            this.options = Array.from(this.select.options);
            this.list.innerHTML = '';

            this.items = this.options.map((option, i) => {
                const li = document.createElement('li');
                li.className = 'gt-combo-panel__option';
                li.id = this.id + '-opt-' + i;
                li.dataset.index = String(i);
                li.setAttribute('role', 'option');
                li.innerHTML =
                    '<span class="gt-combo-panel__label"></span>' +
                    '<span class="gt-combo-panel__tick icon icon-check" aria-hidden="true"></span>';
                li.firstElementChild.textContent = option.textContent.trim() || option.value;

                if (option.disabled) {
                    li.classList.add('is-disabled');
                    li.setAttribute('aria-disabled', 'true');
                }

                this.list.appendChild(li);
                return li;
            });
        }

        /** Mirror the native selection onto the trigger and the listbox. */
        sync() {
            const option = this.select.options[this.select.selectedIndex];
            this.valueEl.textContent = option ? option.textContent.trim() : '';
            // An empty value is a placeholder ("Anywhere", "Any type"), not a choice.
            this.wrap.classList.toggle('is-empty', !option || option.value === '');

            this.items.forEach((li, i) => {
                const on = i === this.select.selectedIndex;
                li.classList.toggle('is-selected', on);
                li.setAttribute('aria-selected', on ? 'true' : 'false');
            });
        }

        filter(term) {
            const q = term.trim().toLowerCase();
            let visible = 0;

            this.items.forEach((li, i) => {
                const match = !q || this.options[i].textContent.toLowerCase().includes(q);
                li.hidden = !match;
                if (match) visible++;
            });

            if (this.empty) this.empty.hidden = visible > 0;

            const first = this.items.find((li) => !li.hidden);
            if (first) this.setActive(Number(first.dataset.index), true);
        }

        /* ------------------------------------------------------ open / close */

        openPanel() {
            if (this.isOpen || this.trigger.disabled) return;
            if (open) open.close(false);
            open = this;

            this.isOpen = true;
            this.sheet = window.matchMedia('(max-width: 575px)').matches;

            this.panel.classList.toggle('gt-combo-panel--sheet', this.sheet);
            this.wrap.classList.add('is-open');
            this.trigger.setAttribute('aria-expanded', 'true');

            this.position();
            this.panel.classList.add('is-open');
            if (this.sheet) scrim(true);

            // A hero slide advancing under an open menu would strand it.
            this.swiper = this.trigger.closest('.swiper') && this.trigger.closest('.swiper').swiper;
            if (this.swiper && this.swiper.autoplay) this.swiper.autoplay.stop();

            this.setActive(this.select.selectedIndex, true);

            if (this.search) {
                this.search.value = '';
                this.filter('');
                // Flush the style change first: a still-hidden element can't
                // take focus, and the browser would drop it on the floor.
                void this.panel.offsetHeight;
                if (!this.sheet) this.search.focus();
            }
        }

        close(refocus) {
            if (!this.isOpen) return;
            this.isOpen = false;
            if (open === this) open = null;

            this.panel.classList.remove('is-open');
            this.wrap.classList.remove('is-open');
            this.trigger.setAttribute('aria-expanded', 'false');
            this.list.removeAttribute('aria-activedescendant');
            scrim(false);

            if (this.swiper && this.swiper.autoplay) this.swiper.autoplay.start();
            if (refocus) this.trigger.focus();
        }

        /** Fixed positioning keeps the panel out of every `overflow: hidden`
            ancestor — the hero slider and the modals both clip. */
        position() {
            if (this.sheet) {
                this.panel.style.cssText = '';
                return;
            }

            const r = this.trigger.getBoundingClientRect();
            const gap = 8;
            const width = Math.min(Math.max(r.width, 220), window.innerWidth - 16);
            const below = window.innerHeight - r.bottom - gap - 12;
            const above = r.top - gap - 12;
            const up = below < 200 && above > below;

            this.panel.style.width = width + 'px';
            this.panel.style.left = Math.min(Math.max(8, r.left), window.innerWidth - width - 8) + 'px';
            this.panel.style.top = up ? 'auto' : r.bottom + gap + 'px';
            this.panel.style.bottom = up ? window.innerHeight - r.top + gap + 'px' : 'auto';
            this.panel.style.setProperty('--gt-combo-max', Math.max(140, Math.min(320, up ? above : below)) + 'px');
            this.panel.classList.toggle('is-up', up);

            // Keep the panel on screen even when the trigger sits right on the
            // edge — a menu hanging off the fold is worse than a shifted one.
            const height = this.panel.offsetHeight;
            if (up) {
                const bottom = Number.parseFloat(this.panel.style.bottom);
                this.panel.style.bottom = Math.min(bottom, window.innerHeight - height - 8) + 'px';
            } else {
                const top = Number.parseFloat(this.panel.style.top);
                this.panel.style.top = Math.max(8, Math.min(top, window.innerHeight - height - 8)) + 'px';
            }
        }

        /* --------------------------------------------------------- selection */

        setActive(index, scroll) {
            let li = this.items[index];
            if (!li || li.hidden || li.classList.contains('is-disabled')) {
                li = this.items.find((el) => !el.hidden && !el.classList.contains('is-disabled'));
            }
            if (!li) return;

            this.items.forEach((el) => el.classList.remove('is-active'));
            li.classList.add('is-active');
            this.active = Number(li.dataset.index);
            this.list.setAttribute('aria-activedescendant', li.id);
            if (scroll) li.scrollIntoView({ block: 'nearest' });
        }

        move(step) {
            const usable = this.items.filter((li) => !li.hidden && !li.classList.contains('is-disabled'));
            if (!usable.length) return;

            const at = usable.indexOf(this.items[this.active]);
            const next = at < 0 ? usable[step > 0 ? 0 : usable.length - 1]
                : usable[Math.max(0, Math.min(usable.length - 1, at + step))];
            this.setActive(Number(next.dataset.index), true);
        }

        choose(index) {
            const option = this.select.options[index];
            if (!option || option.disabled) return;

            if (this.select.selectedIndex !== index) {
                this.select.selectedIndex = index;
                this.sync();
                this.close(true);
                this.select.dispatchEvent(new Event('input', { bubbles: true }));
                this.select.dispatchEvent(new Event('change', { bubbles: true }));
                return;
            }

            this.close(true);
        }

        /** Jump to the option starting with what was typed, like a real select. */
        typeahead(char) {
            clearTimeout(this.typeTimer);
            this.typed += char.toLowerCase();
            this.typeTimer = setTimeout(() => (this.typed = ''), 700);

            const hit = this.items.find((li, i) =>
                !li.classList.contains('is-disabled') &&
                this.options[i].textContent.trim().toLowerCase().startsWith(this.typed));

            if (hit) this.setActive(Number(hit.dataset.index), true);
        }

        onKeydown(e) {
            const key = e.key;

            if (!this.isOpen) {
                if (key === 'ArrowDown' || key === 'ArrowUp' || key === 'Enter' || key === ' ') {
                    e.preventDefault();
                    this.openPanel();
                } else if (key.length === 1 && key !== '\t') {
                    this.openPanel();
                    if (this.search) {
                        this.search.value = key;
                        this.filter(key);
                    } else {
                        this.typeahead(key);
                    }
                    e.preventDefault();
                }
                return;
            }

            switch (key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.move(1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.move(-1);
                    break;
                case 'Home':
                    e.preventDefault();
                    this.move(-this.items.length);
                    break;
                case 'End':
                    e.preventDefault();
                    this.move(this.items.length);
                    break;
                case 'Enter':
                    e.preventDefault();
                    this.choose(this.active);
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.close(true);
                    break;
                case 'Tab':
                    this.close(false);
                    break;
                case ' ':
                    if (!this.search) {
                        e.preventDefault();
                        this.choose(this.active);
                    }
                    break;
                default:
                    if (!this.search && key.length === 1) this.typeahead(key);
            }
        }

        bind() {
            this.trigger.addEventListener('click', () => (this.isOpen ? this.close(true) : this.openPanel()));
            this.trigger.addEventListener('keydown', (e) => this.onKeydown(e));

            this.list.addEventListener('click', (e) => {
                const li = e.target.closest('.gt-combo-panel__option');
                if (li && !li.classList.contains('is-disabled')) this.choose(Number(li.dataset.index));
            });

            this.list.addEventListener('mousemove', (e) => {
                const li = e.target.closest('.gt-combo-panel__option');
                if (li && !li.classList.contains('is-disabled')) this.setActive(Number(li.dataset.index), false);
            });

            // Anything that drives the select from code — form resets, other scripts.
            this.select.addEventListener('change', () => this.sync());
            if (this.select.form) this.select.form.addEventListener('reset', () => setTimeout(() => this.sync()));

            new MutationObserver(() => {
                this.renderOptions();
                this.sync();
            }).observe(this.select, { childList: true, subtree: true, characterData: true });
        }
    }

    /* ------------------------------------------------------------ date field */

    function enhanceDate(input) {
        const wrap = document.createElement('span');
        wrap.className = 'gt-date';
        // Here the wrapper *is* the field, so it inherits the input's skin too.
        input.classList.forEach((c) => wrap.classList.add(c));

        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        const display = document.createElement('span');
        display.className = 'gt-date__display';
        display.innerHTML = '<span class="gt-date__text"></span>' + CALENDAR_ICON;
        wrap.appendChild(display);

        const text = display.firstElementChild;
        const placeholder = input.dataset.emptyLabel
            || input.getAttribute('placeholder')
            || (input.required ? 'Select a date' : 'Any date');

        const paint = () => {
            wrap.classList.toggle('is-empty', !input.value);

            if (!input.value) {
                text.textContent = placeholder;
                return;
            }

            // Parse as local time: `new Date('2026-08-12')` is UTC midnight and
            // can render as the day before.
            const [y, m, d] = input.value.split('-').map(Number);
            const date = new Date(y, m - 1, d);
            const format = { weekday: 'short', day: 'numeric', month: 'short' };
            if (y !== new Date().getFullYear()) format.year = 'numeric';
            text.textContent = date.toLocaleDateString(undefined, format);
        };

        // The whole field is the hit area, not the browser's little icon.
        wrap.addEventListener('click', () => {
            try {
                input.showPicker();
            } catch (err) {
                input.focus();
            }
        });

        input.addEventListener('input', paint);
        input.addEventListener('change', paint);
        if (input.form) input.form.addEventListener('reset', () => setTimeout(paint));

        paint();
        input.dataset.gtDate = 'on';
    }

    /* ------------------------------------------------------------------ boot */

    function enhance(root = document) {
        $$('select:not([multiple]):not([size]):not([data-gt-native]):not([data-gt-combo])', root)
            .forEach((select) => new Combo(select));

        $$('input[type="date"]:not([data-gt-native]):not([data-gt-date])', root)
            .forEach(enhanceDate);
    }

    document.addEventListener('mousedown', (e) => {
        if (!open) return;
        if (e.target.closest('.gt-combo-panel') === open.panel) return;
        if (e.target.closest('.gt-combo') === open.wrap) return;
        open.close(false);
    });

    window.addEventListener('resize', () => open && open.close(false));

    window.addEventListener('scroll', () => {
        if (!open || open.sheet) return;
        const r = open.trigger.getBoundingClientRect();
        if (r.bottom < 0 || r.top > window.innerHeight) open.close(false);
        else open.position();
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => enhance());
    } else {
        enhance();
    }

    window.GlobeTrekControls = { enhance };
})();
