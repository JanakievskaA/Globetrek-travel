{{--
    One picker for the whole admin panel. Every image field opens this dialog;
    admin.js fills the grid from /admin/media and writes the chosen path back
    into the field that opened it.
--}}
<div class="adm-media" id="adm-media" hidden aria-hidden="true">
    <div class="adm-media__scrim" data-media-close></div>

    <div class="adm-media__dialog" role="dialog" aria-modal="true" aria-labelledby="adm-media-title">
        <div class="adm-media__head">
            <div>
                <h3 class="adm-media__title" id="adm-media-title">Choose an image</h3>
                <p class="adm-media__sub">Upload your own, or reuse one already on the site.</p>
            </div>
            <button type="button" class="adm-media__close" data-media-close aria-label="Close">
                <i class="icon icon-X"></i>
            </button>
        </div>

        <div class="adm-media__bar">
            <div class="adm-media__tabs" role="tablist">
                <button type="button" class="adm-media__tab is-active" data-media-tab="library" role="tab">
                    Uploads
                </button>
                <button type="button" class="adm-media__tab" data-media-tab="theme" role="tab">
                    Site images
                </button>
            </div>

            <input type="search" class="adm-media__search" data-media-search placeholder="Search images…"
                aria-label="Search images" autocomplete="off">

            <label class="adm-btn adm-media__upload">
                <input type="file" accept="image/*" data-media-file multiple hidden>
                <span>Upload</span>
            </label>
        </div>

        <div class="adm-media__body" data-media-drop>
            <div class="adm-media__grid" data-media-grid></div>
            <p class="adm-media__empty" data-media-empty hidden>Nothing here yet — upload an image to get started.</p>
            <div class="adm-media__loading" data-media-loading hidden>Loading…</div>
            <div class="adm-media__dropzone" data-media-dropzone hidden>Drop to upload</div>
        </div>

        <div class="adm-media__foot">
            <p class="adm-media__status" data-media-status></p>
            <div class="adm-media__foot-actions">
                <button type="button" class="adm-btn adm-btn--ghost" data-media-close>Cancel</button>
                <button type="button" class="adm-btn" data-media-confirm disabled>Use this image</button>
            </div>
        </div>
    </div>
</div>
