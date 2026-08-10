/** Admin panel behaviour: sidebar drawer, auto-submitting filters, confirmations. */
(function () {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    function sidebar() {
        const el = $('#adm-sidebar');
        const scrim = $('.adm-scrim');
        if (!el) return;

        const setOpen = (open) => {
            el.classList.toggle('is-open', open);
            scrim?.classList.toggle('is-open', open);
        };

        $('[data-sidebar-open]')?.addEventListener('click', () => setOpen(true));
        $$('[data-sidebar-close]').forEach((b) => b.addEventListener('click', () => setOpen(false)));
        document.addEventListener('keydown', (e) => e.key === 'Escape' && setOpen(false));
    }

    /** Filter bars submit on change; free-text search waits for a pause. */
    function filters() {
        $$('form[data-auto-filter]').forEach((form) => {
            form.addEventListener('change', (e) => {
                if (e.target.matches('select, input[type="date"]')) form.requestSubmit();
            });

            const search = form.querySelector('input[type="search"]');
            if (!search) return;
            let timer;
            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => form.requestSubmit(), 450);
            });
        });
    }

    /** Inline status dropdowns post immediately. */
    function inlineForms() {
        $$('.adm-inline-form select').forEach((select) => {
            select.addEventListener('change', () => select.closest('form').requestSubmit());
        });
    }

    function confirmations() {
        document.addEventListener('submit', (e) => {
            const message = e.target.dataset?.confirm;
            if (message && !window.confirm(message)) e.preventDefault();
        });
    }

    function toasts() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.gt-toast__close')) e.target.closest('.gt-toast').remove();
        });
        $$('#gt-toast-stack .gt-toast').forEach((el) => {
            setTimeout(() => el.classList.add('is-leaving'), 5000);
            setTimeout(() => el.remove(), 5400);
        });
    }

    /* ------------------------------------------------------------ image picker */

    /**
     * One modal serves every image field. Opening it remembers which field
     * asked, so confirming just writes the chosen path back into that field's
     * hidden input — the form itself still posts a plain string.
     */
    const Picker = {
        el: null,
        field: null,
        selected: null,
        source: 'library',
        items: [],

        init() {
            this.el = $('#adm-media');
            if (!this.el) return;

            this.grid = $('[data-media-grid]', this.el);
            this.empty = $('[data-media-empty]', this.el);
            this.loading = $('[data-media-loading]', this.el);
            this.status = $('[data-media-status]', this.el);
            this.confirm = $('[data-media-confirm]', this.el);
            this.search = $('[data-media-search]', this.el);
            this.dropzone = $('[data-media-dropzone]', this.el);

            // Fields can arrive later (a repeater row added after load), so the
            // open/clear handlers are delegated from the document.
            document.addEventListener('click', (e) => {
                const opener = e.target.closest('[data-image-open]');
                if (opener) {
                    e.preventDefault();
                    this.open(opener.closest('[data-image-field]'));
                    return;
                }

                const clear = e.target.closest('[data-image-clear]');
                if (clear) {
                    e.preventDefault();
                    this.write(clear.closest('[data-image-field]'), '');
                }
            });

            $$('[data-media-close]', this.el).forEach((b) => b.addEventListener('click', () => this.close()));
            document.addEventListener('keydown', (e) => e.key === 'Escape' && this.close());

            $$('[data-media-tab]', this.el).forEach((tab) => tab.addEventListener('click', () => {
                this.source = tab.dataset.mediaTab;
                $$('[data-media-tab]', this.el).forEach((t) => t.classList.toggle('is-active', t === tab));
                this.load();
            }));

            let timer;
            this.search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => this.load(), 300);
            });

            this.grid.addEventListener('click', (e) => {
                const card = e.target.closest('[data-media-item]');
                if (!card) return;
                this.select(card.dataset.mediaItem);
            });

            this.grid.addEventListener('dblclick', (e) => {
                const card = e.target.closest('[data-media-item]');
                if (card) { this.select(card.dataset.mediaItem); this.apply(); }
            });

            this.confirm.addEventListener('click', () => this.apply());
            $('[data-media-file]', this.el).addEventListener('change', (e) => this.upload(e.target.files));

            const drop = $('[data-media-drop]', this.el);
            // Only react to a drag carrying files — dragging a thumbnail around
            // the grid is not an upload.
            const hasFiles = (e) => Array.from(e.dataTransfer?.types || []).includes('Files');

            ['dragenter', 'dragover'].forEach((type) => drop.addEventListener(type, (e) => {
                if (!hasFiles(e)) return;
                e.preventDefault();
                this.dropzone.hidden = false;
            }));
            ['dragleave', 'drop'].forEach((type) => drop.addEventListener(type, (e) => {
                if (type === 'dragleave' && drop.contains(e.relatedTarget)) return;
                this.dropzone.hidden = true;
            }));
            drop.addEventListener('drop', (e) => {
                e.preventDefault();
                this.upload(e.dataTransfer.files);
            });
        },

        open(field) {
            if (!field) return;
            this.field = field;
            this.selected = $('[data-image-value]', field)?.value || null;
            this.el.hidden = false;
            this.el.setAttribute('aria-hidden', 'false');
            document.body.classList.add('adm-no-scroll');
            this.search.value = '';
            this.load();
        },

        close() {
            if (!this.el || this.el.hidden) return;
            this.el.hidden = true;
            this.el.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('adm-no-scroll');
            this.field = null;
        },

        async load() {
            this.loading.hidden = false;
            this.grid.innerHTML = '';
            this.empty.hidden = true;

            try {
                const params = new URLSearchParams({ source: this.source, q: this.search.value.trim() });
                const res = await fetch(`/admin/media?${params}`, { headers: { Accept: 'application/json' } });
                const data = await res.json();
                this.items = data.items || [];
                this.render();
            } catch (err) {
                this.setStatus('Could not load images.', true);
            } finally {
                this.loading.hidden = true;
            }
        },

        render() {
            this.grid.innerHTML = this.items.map((item) => `
                <button type="button" class="adm-media__item ${item.path === this.selected ? 'is-selected' : ''}"
                    data-media-item="${escapeAttr(item.path)}" title="${escapeAttr(item.name)}">
                    <img src="${escapeAttr(item.url)}" alt="" loading="lazy" draggable="false">
                    <span class="adm-media__name">${escapeHtml(item.name)}</span>
                    <span class="adm-media__meta">${escapeHtml(item.meta || '')}</span>
                </button>`).join('');

            this.empty.hidden = this.items.length > 0;
            this.confirm.disabled = !this.selected;
            this.setStatus(this.selected ? this.selected : '');
        },

        select(path) {
            this.selected = path;
            $$('[data-media-item]', this.grid).forEach((card) =>
                card.classList.toggle('is-selected', card.dataset.mediaItem === path));
            this.confirm.disabled = false;
            this.setStatus(path);
        },

        apply() {
            if (!this.selected || !this.field) return;
            this.write(this.field, this.selected);
            this.close();
        },

        /** Push a path into a field and repaint its preview. */
        write(field, path) {
            const input = $('[data-image-value]', field);
            const preview = $('[data-image-preview]', field);
            const placeholder = $('[data-image-placeholder]', field);
            const clear = $('[data-image-clear]', field);
            const actionLabel = $('[data-image-action-label]', field);

            input.value = path;
            input.dispatchEvent(new Event('change', { bubbles: true }));

            if (path) {
                preview.src = `/${path.replace(/^\//, '')}`;
                preview.hidden = false;
                placeholder.hidden = true;
                clear.hidden = false;
                if (actionLabel) actionLabel.textContent = 'Change';
            } else {
                preview.removeAttribute('src');
                preview.hidden = true;
                placeholder.hidden = false;
                clear.hidden = true;
                if (actionLabel) actionLabel.textContent = 'Choose image';
            }

            field.classList.toggle('is-empty', !path);
        },

        async upload(files) {
            const list = Array.from(files || []).filter((f) => f.type.startsWith('image/'));
            if (!list.length) return;

            this.setStatus(`Uploading ${list.length} image${list.length > 1 ? 's' : ''}…`);
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            let last = null;

            for (const file of list) {
                const body = new FormData();
                body.append('file', file);

                try {
                    const res = await fetch('/admin/media', {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
                        body,
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        this.setStatus(data.message || 'Upload failed.', true);
                        continue;
                    }
                    last = data.item;
                } catch (err) {
                    this.setStatus('Upload failed.', true);
                }
            }

            if (!last) return;

            // Land on the uploads tab so the new file is visible, and preselect it.
            this.source = 'library';
            $$('[data-media-tab]', this.el).forEach((t) => t.classList.toggle('is-active', t.dataset.mediaTab === 'library'));
            this.search.value = '';
            await this.load();
            this.select(last.path);
        },

        setStatus(message, isError = false) {
            this.status.textContent = message || '';
            this.status.classList.toggle('is-error', !!isError);
        },
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const escapeAttr = escapeHtml;

    /* ---------------------------------------------------------------- repeaters */

    /** Add/remove/reorder rows for list fields (hero slides, benefit cards). */
    function repeaters() {
        $$('[data-repeater]').forEach((repeater) => {
            const list = $('[data-repeater-list]', repeater);
            const template = $('template[data-repeater-template]', repeater);
            const max = Number(repeater.dataset.repeaterMax || 12);

            const renumber = () => {
                $$('[data-repeater-row]', list).forEach((row, i) => {
                    $$('[name]', row).forEach((input) => {
                        const before = input.id;
                        input.name = input.name.replace(/\[(\d+|__index__)\]/, `[${i}]`);

                        // Ids feed the <label for>, so they have to move too or
                        // two rows would fight over the same label.
                        if (before) {
                            input.id = before.replace(/-(\d+|__index__)-/, `-${i}-`);
                            const label = row.querySelector(`label[for="${CSS.escape(before)}"]`);
                            if (label) label.htmlFor = input.id;
                        }
                    });
                    const number = $('[data-repeater-number]', row);
                    if (number) number.textContent = i + 1;
                });
                $('[data-repeater-add]', repeater).disabled = $$('[data-repeater-row]', list).length >= max;
                $('[data-repeater-empty]', repeater)?.toggleAttribute('hidden',
                    $$('[data-repeater-row]', list).length > 0);
            };

            $('[data-repeater-add]', repeater).addEventListener('click', () => {
                if ($$('[data-repeater-row]', list).length >= max) return;
                list.insertAdjacentHTML('beforeend', template.innerHTML);
                renumber();
                list.lastElementChild.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            });

            list.addEventListener('click', (e) => {
                const row = e.target.closest('[data-repeater-row]');
                if (!row) return;

                if (e.target.closest('[data-repeater-remove]')) {
                    row.remove();
                    renumber();
                    return;
                }

                const up = e.target.closest('[data-repeater-up]');
                const down = e.target.closest('[data-repeater-down]');
                if (up && row.previousElementSibling) {
                    row.parentNode.insertBefore(row, row.previousElementSibling);
                    renumber();
                } else if (down && row.nextElementSibling) {
                    row.parentNode.insertBefore(row.nextElementSibling, row);
                    renumber();
                }
            });

            renumber();
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        sidebar();
        filters();
        inlineForms();
        confirmations();
        toasts();
        Picker.init();
        repeaters();
    });
})();
